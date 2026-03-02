<?php

use Corcel\Model\User;
use Dompdf\Dompdf;
use Dompdf\Options;

class PerformancePortalController extends Controller
{
	protected PerformanceService $service;
	
	public function __construct()
    {
        parent::__construct();
        $this->service = new PerformanceService();
    }
	
    public function list($dataType = 'reservations', $categories = null, $apartments = null, $checkinFrom = null, $checkinTo = null)
    {
        // Get shared query data
        $queryData = $this->service->buildDataQueries($categories, $apartments, $checkinFrom, $checkinTo);
        extract($queryData);

        $rowsPerPage = 100;
        $reservationsTotalAmount = 0;
        $reservationsTotalCmms = 0;
        $reservationsTotalTransferCommission = 0;
        $reservationsTotalUnhotelCommission = 0;
        $reservationsTotalNet = 0;
        $expenses = [];
        $transfers = [];
        $expensesTotalAmount = 0;
        $transfersTotalAmount = 0;

        // Get all VikBooking categories
        $propertyCategories = PropertyCategory::all();
        if ($propertyCategories && !$propertyCategories->isEmpty()) {
            $propertyCategories = collect($propertyCategories)->mapWithKeys(function($category) {
                // Remove leading '#' from name if present
                return [$category['id'] => ltrim($category['name'], '#')];
            })->toArray();
        } else {
            $propertyCategories = [];
        }

        // Get all properties as an array name - id
        $propertiesListAll = Property::all()->mapWithKeys(function($property) {
            // Get the first word from the property name
            $firstWord = strtok($property->name, ' ');
            return [$property->id => $firstWord];
        });

        // Get current page from request
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get paginated reservations for frontend  
        $originalReservations = $reservationsQuery->paginate($rowsPerPage, ['*'], 'page', $currentPage);

        // Process reservations to split multi-property reservations
        $splitReservations = [];
        $reservationCounter = 0;
        
        foreach ($originalReservations as $originalReservation) {
            if (!empty($originalReservation->properties) && $originalReservation->properties->count() > 1) {
                // For multi-property reservations, create a clone for each property
                foreach ($originalReservation->properties as $property) {
                    // Clone the reservation
                    $clonedReservation = clone $originalReservation;
                    
                    // Store the original reservation for reference
                    $clonedReservation->originalReservation = $originalReservation;
                    
                    // Store the single property
                    $clonedReservation->singleProperty = $property;
                    
                    // Add to split reservations array
                    $splitReservations[] = $clonedReservation;
                    $reservationCounter++;
                }
            } else {
                // Single property reservation - just add to the array
                if (!empty($originalReservation->properties) && $originalReservation->properties->isNotEmpty()) {
                    $originalReservation->singleProperty = $originalReservation->properties->first();
                }
                $splitReservations[] = $originalReservation;
                $reservationCounter++;
            }
        }

        // Calculate total split reservations count for proper pagination
        $totalSplitReservations = 0;
        foreach ($allReservations as $reservation) {
            if (!empty($reservation->properties) && $reservation->properties->count() > 1) {
                $totalSplitReservations += $reservation->properties->count();
            } else {
                $totalSplitReservations++;
            }
        }

        // Create a new paginator with the split reservations
        $currentUrl = strtok($_SERVER["REQUEST_URI"], '?');
        $reservations = new \Illuminate\Pagination\LengthAwarePaginator(
            $splitReservations,
            $totalSplitReservations, // Total items across all pages
            $rowsPerPage,
            $currentPage,
            [
                'path' => $currentUrl,
                'pageName' => 'page',
            ]
        );

        // Append current query parameters to pagination links
        $reservations->appends($_GET);

        // Calculate totals from the split reservations
        if(!empty($reservations)) {
            foreach($reservations as $reservation) {
                $propertyId = isset($reservation->singleProperty) ? $reservation->singleProperty->id : null;
                
                $reservationsTotalAmount += $reservation->getTotal($propertyId);
                $reservationsTotalCmms += !empty($reservation->getCmms($propertyId)) ? $reservation->getCmms($propertyId) : 0;
                $reservationsTotalTransferCommission += $reservation->getTransferCommission($propertyId);
                $reservationsTotalUnhotelCommission += $reservation->getUnhotelCommission($propertyId);
                $reservationsTotalNet += $reservation->getTotalNet($propertyId);
            }
        }

        // Expenses
        if ($dataType == 'expenses' && $expensesQuery) {
            // Paginate expenses
            $expenses = $expensesQuery->paginate($rowsPerPage, ['*'], 'page', $currentPage);
            
            // Append current query parameters to pagination links
            $expenses->appends($_GET);

            // Add property name to each expense
            $expenses->getCollection()->transform(function($expense) use ($propertyNames) {
                // Add property name to expense
                $expense->property_name = $propertyNames[$expense->room_id] ?? '';
                return $expense;
            });

            // Calculate totals
            $expensesTotalAmount = $expenses->sum('amount');
        }

        // Transfers
        if ($dataType == 'transfers' && $transfersQuery) {
            // Paginate transfers
            $transfers = $transfersQuery->paginate($rowsPerPage, ['*'], 'page', $currentPage);
            
            // Append current query parameters to pagination links
            $transfers->appends($_GET);

            // Add property name to each transfer
            $transfers->getCollection()->transform(function($transfer) use ($propertyNames) {
                // Add property name to transfer
                $transfer->property_name = $propertyNames[$transfer->room_id] ?? '';
                return $transfer;
            });

            // Calculate totals
            $transfersTotalAmount = $transfers->sum('amount');
        }

        // Total Due and Expected Earnings for all properties
        $total_due = 0;
        $expected_earnings = 0;

        foreach ($properties as $property) {
            [$due, ] = $this->service->getTotalDue($property->id);
            [$expected, ] = $this->service->getExpectedEarnings($property->id);
            $total_due += $due;
            $expected_earnings += $expected;
        }

        // Render the performance view
        $html = $this->blade->run("admin.performance", [
            'dataType' => $dataType,
            'categories' => $categories,
            'apartments' => $apartments,
            'checkinFrom' => $checkinFrom,
            'checkinTo' => $checkinTo,
            'propertyCategories' => $propertyCategories,
            'reservations' => $reservations,
            'propertiesListAll' => $propertiesListAll,
            'reservationsTotalAmount' => number_format($reservationsTotalAmount, 2, '.', ','),
            'reservationsTotalCmms' => number_format($reservationsTotalCmms, 2, '.', ','),
            'reservationsTotalTransferCommission' => number_format($reservationsTotalTransferCommission, 2, '.', ','),
            'reservationsTotalUnhotelCommission' => number_format($reservationsTotalUnhotelCommission, 2, '.', ','),
            'reservationsTotalNet' => number_format($reservationsTotalNet, 2, '.', ','),
            'expenses' => $expenses,
            'expensesTotalAmount' => number_format($expensesTotalAmount, 2, '.', ','),
            'transfers' => $transfers,
            'transfersTotalAmount' => number_format($transfersTotalAmount, 2, '.', ','),
            'total_due' => $total_due,
            'expected_earnings' => $expected_earnings,
        ]);

        echo $html;
    }
}
