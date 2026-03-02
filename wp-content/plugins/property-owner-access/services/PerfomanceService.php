<?php
class PerformanceService {
	
	public function buildDataQueries($categories = null, $apartments = null, $checkinFrom = null, $checkinTo = null)
    {
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

        // Build reservations query starting from Reservation model, joining Property
        $reservationsQuery = Reservation::with(['properties', 'channelFees', 'ownerCommissions'])
            ->where('status', 'confirmed')
            ->whereBetween('checkin', [$checkinFromTimestamp, $checkinToTimestamp]);

        // Filter by property category if provided
        if (!empty($categories) && is_array($categories)) {
            $reservationsQuery->whereHas('properties', function ($query) use ($categories) {
                $query->where(function ($q) use ($categories) {
                    foreach ($categories as $category) {
                        // $q->orWhere('name', 'LIKE', '%' . $category . '%');
                        $q->whereRaw("idcat REGEXP CONCAT('(^|;)', ?, '(;|$)')", [(string)$category]);
                    }
                });
            });
        }

        // Add filter by apartments if provided
        if (!empty($apartments) && is_array($apartments) && count($apartments) > 0) {
            $reservationsQuery->whereHas('properties', function ($query) use ($apartments) {
                $query->whereIn('vikbooking_rooms.id', $apartments);
            });
        }

        // // sql include bindings for debugging - corcel
        // // Debug SQL with bindings (Corcel/Eloquent for WordPress)
        // $sql = $reservationsQuery->toSql();
        // $bindings = $reservationsQuery->getBindings();
        // // Simple replacement for debugging (not perfect for all cases)
        // foreach ($bindings as $binding) {
        //     $binding = is_numeric($binding) ? $binding : "'".addslashes($binding)."'";
        //     $sql = preg_replace('/\?/', $binding, $sql, 1);
        // }
        // var_dump($sql);
        // exit();

        // Get reservations to extract property IDs (for frontend we'll paginate this later)
        $allReservations = $reservationsQuery->get();

        // Extract properties collection from reservations
        $properties = $allReservations->flatMap(function($reservation) {
            return $reservation->properties;
        })->unique('id');

        // Add property name mapping
        $propertyNames = $properties->mapWithKeys(function($property) {
            return [$property->id => strtok($property->name, ' ')];
        });

        // Collect all property IDs
        $propertyIds = $properties->pluck('id')->toArray();

        // Build expenses query
        $expensesQuery = null;
        if (!empty($propertyIds)) {
            $expensesQuery = Expense::whereIn('room_id', $propertyIds)
                ->whereBetween('date', [$checkinFrom, $checkinTo])
                ->where('owner', 1)
                ->orderBy('date', 'desc');
        }

        // Build transfers query  
        $transfersQuery = null;
        if (!empty($propertyIds)) {
            $transfersQuery = Transfer::whereIn('room_id', $propertyIds)
                ->whereBetween('date', [$checkinFrom, $checkinTo])
                ->orderBy('date', 'desc');
        }

        return [
            'reservationsQuery' => $reservationsQuery,
            'expensesQuery' => $expensesQuery,
            'transfersQuery' => $transfersQuery,
            'allReservations' => $allReservations,
            'properties' => $properties,
            'propertyNames' => $propertyNames,
            'propertyIds' => $propertyIds,
            'checkinFrom' => $checkinFrom,
            'checkinTo' => $checkinTo,
            'checkinFromTimestamp' => $checkinFromTimestamp,
            'checkinToTimestamp' => $checkinToTimestamp
        ];
    }

	public function getTotalDue($propertyId)
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

    public function getExpectedEarnings($propertyId)
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