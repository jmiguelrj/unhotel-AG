<?php

use Corcel\Model\User;
use Dompdf\Dompdf;
use Dompdf\Options;

class PropertiesController extends Controller
{

    function __construct()
    {
        parent::__construct();
    }

    public function list()
    {
        $properties = PropertyOwner::where('user_id', get_current_user_id())
            ->with('property')
            ->get()
            ->pluck('property');

        $user = User::find(get_current_user_id());
        $userHomeyAvatar = getHomeyUserProfilePicture($user->ID);
        $propertiesIds = $properties->pluck('id');
        $userReservations = Reservation::
            with(['properties','channelFees','ownerCommissions'])
            ->where('status', 'confirmed')
            ->where('checkin', '>=', strtotime(POA_STARTING_DATE))
            ->whereHas('properties', function ($query) use ($propertiesIds) {
                $query->whereIn('idroom', $propertiesIds);
            });
            
        // Calculate total earnings
        $total_reservations_list = $userReservations
            ->where('checkin', '<=', strtotime(date('Y-m-d')))
            ->get();
        $total_earnings = 0;
        foreach($total_reservations_list as $reservation) {
            $total_earnings += $reservation->getTotalNet();
        }
        $total_reservations = $userReservations->count();
        echo $this->blade->run("properties", [
            'properties' => $properties,
            'user' => $user,
            'user_avatar' => ( ( !empty($userHomeyAvatar) ) ? $userHomeyAvatar : get_avatar($user->ID)),
            'total_reservations' => $total_reservations,
            'total_earnings' => number_format($total_earnings, 2, '.', ','),
        ]);
    }

    public function detail($propertyId, $checkinFrom = null, $checkinTo = null, $pdf = false)
    {
        // Check if user is authorized
        checkOwnership($propertyId);

        // Set default dates if not provided
        if (empty($checkinFrom) || empty($checkinTo)) {
            $date = new DateTime();
            $checkinTo = $date->modify('last day of this month')->format('Y-m-d');
            $checkinFrom = $date->modify('first day of this month')->format('Y-m-d');
        }

        // If checkin date is before POA_STARTING_DATE, set it to POA_STARTING_DATE
        if ($checkinFrom < POA_STARTING_DATE) {
            $checkinFrom = POA_STARTING_DATE;
        }

        $checkinFromTimestamp = strtotime($checkinFrom);
        $checkinToTimestamp = strtotime($checkinTo . '+1 day -1 second');
        $reservationsTotalAmount = 0;
        $reservationsTotalCmms = 0;
        $reservationsTotalTransferCommission = 0;
        $reservationsTotalUnhotelCommission = 0;
        $reservationsTotalNet = 0;
        $expensesTotalAmount = 0;
        $transfersTotalAmount = 0;

        // Redirect if property does not exist
        $property = Property::find($propertyId);
        if (!$property) {
            $this->redirectTo404();
        }

        // Reservations
        $reservations = $property->reservations()
            ->with(['properties','channelFees','ownerCommissions'])
            ->where('status', 'confirmed')
            ->whereBetween('checkin', [$checkinFromTimestamp, $checkinToTimestamp])
            ->orderBy('checkin', 'desc')
            ->get();

        // Calculate totals
        if(!empty($reservations)) {
            foreach($reservations as $reservation) {
                $reservationsTotalAmount += $reservation->getTotal($propertyId);
                $reservationsTotalCmms += !empty($reservation->getCmms($propertyId)) ? $reservation->getCmms($propertyId) : 0;
                $reservationsTotalTransferCommission += $reservation->getTransferCommission($propertyId);
                $reservationsTotalUnhotelCommission += $reservation->getUnhotelCommission($propertyId);
                $reservationsTotalNet += $reservation->getTotalNet($propertyId);
            }
        }

        // Expenses
        $expenses = Expense::where('room_id', $propertyId)
            ->whereBetween('date', [$checkinFrom, $checkinTo])
            ->where('owner', 1)
            ->orderBy('date', 'desc')
            ->get();
        // Calculate totals
        if(!empty($expenses)) {
            foreach ($expenses as $expense) {
                $expensesTotalAmount += !empty($expense->amount) ? $expense->amount : 0;
            }
        }

        // Transfers
        $transfers = Transfer::where('room_id', $propertyId)
            ->whereBetween('date', [$checkinFrom, $checkinTo])
            ->orderBy('date', 'desc')
            ->get();
        // Calculate totals
        if(!empty($transfers)) {
            foreach ($transfers as $transfer) {
                $transfersTotalAmount += !empty($transfer->amount) ? $transfer->amount : 0;
            }
        }

        // Total Due
        [$total_due, $total_due_debug] = $this->getTotalDue($propertyId);
        // Expected earnings
        [$expected_earnings, $expected_earnings_debug] = $this->getExpectedEarnings($propertyId);

        $html = $this->blade->run("property", [
            'property' => $property,
            'checkinFrom' => $checkinFrom,
            'checkinTo' => $checkinTo,
            'reservations' => $reservations,
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
            'total_due_debug' => $total_due_debug,
            'expected_earnings' => $expected_earnings,
            'expected_earnings_debug' => $expected_earnings_debug,
            'pdf' => $pdf
        ]);

        // Output HTML or PDF
        if( !$pdf ) {
            echo $html;
        } else {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->render();
            $dompdf->stream('report-'.date('YmdHi').'.pdf');
            exit();
        }
    }

    private function getTotalDue($propertyId)
    {
        $total_due = $income = 0;
        $property = Property::find($propertyId);
        $reservations = $property->reservations()
            ->with(['properties','channelFees','ownerCommissions'])
            ->where('status', 'confirmed')
            ->whereBetween('checkin', [strtotime(POA_STARTING_DATE), strtotime('last day of last month 23:59:59')])
            ->get();
        foreach ($reservations as $reservation) {
            $income += $reservation->getTotalNet($propertyId);
        }
        $expenses = $property->expenses()
            ->whereBetween('date', [date('Y-m-d H:i:s', strtotime(POA_STARTING_DATE)), date('Y-m-d H:i:s', strtotime('last day of last month 23:59:59'))])
            ->where('owner', 1)
            ->sum('amount');
        $transfers = $property->transfers()
            ->whereBetween('date', [date('Y-m-d H:i:s', strtotime(POA_STARTING_DATE)), date('Y-m-d H:i:s', strtotime('last day of last month 23:59:59'))])
            ->sum('amount');
        $total_due = $income - $expenses - $transfers;
        $debug = [
            'income' => $income,
            'expenses' => $expenses,
            'transfers' => $transfers
        ];
        return [$total_due, $debug];
    }

    private function getExpectedEarnings($propertyId)
    {
        $total_due = $income = 0;
        $property = Property::find($propertyId);
        $reservations = $property->reservations()
            ->with(['properties','channelFees','ownerCommissions'])
            ->where('status', 'confirmed')
            ->whereBetween('checkin', [strtotime(POA_STARTING_DATE), strtotime('last day of this month 23:59:59')])
            ->get();
        foreach ($reservations as $reservation) {
            $income += $reservation->getTotalNet($propertyId);
        }
        $expenses = $property->expenses()
            ->whereBetween('date', [date('Y-m-d H:i:s', strtotime(POA_STARTING_DATE)), date('Y-m-d', strtotime('last day of this month 23:59:59'))])
            ->where('owner', 1)
            ->sum('amount');
        $transfers = $property->transfers()
            ->where('date', '<=', date('Y-m-d H:i:s', strtotime('last day of last month 23:59:59')))
            ->sum('amount');
        $total_due = $income - $expenses - $transfers;
        $debug = [
            'income' => $income,
            'expenses' => $expenses,
            'transfers' => $transfers
        ];
        return [$total_due, $debug];
    }
}
