<?php
class ExportService 
{
    public static function generateExport($categories = null, $apartments = null, $checkinFrom = null, $checkinTo = null, $withFilters = true)
    {
        // Create a PerformanceService to work with main heavy data-functions
        $service = new PerformanceService();

        // Get shared query data - SAME logic as frontend
        $queryData = $service->buildDataQueries($categories, $apartments, $checkinFrom, $checkinTo);
        extract($queryData);

        // Initialize totals
        $reservationsTotalAmount = 0;
        $reservationsTotalCmms = 0;
        $reservationsTotalTransferCommission = 0;
        $reservationsTotalUnhotelCommission = 0;
        $reservationsTotalNet = 0;
        $expensesTotalAmount = 0;
        $transfersTotalAmount = 0;

        // Get ALL reservations (no pagination for export)
        $allReservationsForExport = $reservationsQuery->get();

        // Calculate reservation totals
        foreach ($allReservationsForExport as $reservation) {
            $reservationsTotalAmount += $reservation->getTotal();
            $reservationsTotalCmms += !empty($reservation->getCmms()) ? $reservation->getCmms() : 0;
            $reservationsTotalTransferCommission += $reservation->getTransferCommission();
            $reservationsTotalUnhotelCommission += $reservation->getUnhotelCommission();
            $reservationsTotalNet += $reservation->getTotalNet();
        }

        // Get ALL expenses (no pagination for export) - using SAME query as frontend
        $expenses = [];
        if ($expensesQuery) {
            $expenses = $expensesQuery->get();
            
            // Add property name to each expense - SAME logic as frontend
            $expenses->transform(function($expense) use ($propertyNames) {
                $expense->property_name = $propertyNames[$expense->room_id] ?? '';
                return $expense;
            });
            
            // Calculate expense totals
            $expensesTotalAmount = $expenses->sum('amount');
        }

        // Get ALL transfers (no pagination for export) - using SAME query as frontend
        $transfers = [];
        if ($transfersQuery) {
            $transfers = $transfersQuery->get();
            
            // Add property name to each transfer - SAME logic as frontend
            $transfers->transform(function($transfer) use ($propertyNames) {
                $transfer->property_name = $propertyNames[$transfer->room_id] ?? '';
                return $transfer;
            });
            
            // Calculate transfer totals
            $transfersTotalAmount = $transfers->sum('amount');
        }

        // Set CSV filename and path
        $filterSuffix = $withFilters ? 'filtered' : 'all';
        $filename = 'property_export_cronjob_' . $filterSuffix . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Create exports directory if it doesn't exist
        $uploadsDir = wp_upload_dir();
        $exportsDir = $uploadsDir['basedir'] . '/unhotel-exports/';
        if (!file_exists($exportsDir)) {
            wp_mkdir_p($exportsDir);
            
            // Create .htaccess file for security (allow only CSV files)
            $htaccessFile = $exportsDir . '.htaccess';
            $htaccessContent = "# Unhotel Exports Directory Security\n";
            $htaccessContent .= "# Only allow CSV files to be accessed\n";
            $htaccessContent .= "<Files ~ \"\.csv$\">\n";
            $htaccessContent .= "    Order allow,deny\n";
            $htaccessContent .= "    Allow from all\n";
            $htaccessContent .= "</Files>\n";
            $htaccessContent .= "<Files ~ \"^.*$\">\n";
            $htaccessContent .= "    Order deny,allow\n";
            $htaccessContent .= "    Deny from all\n";
            $htaccessContent .= "</Files>\n";
            $htaccessContent .= "<Files ~ \"\.csv$\">\n";
            $htaccessContent .= "    Order allow,deny\n";
            $htaccessContent .= "    Allow from all\n";
            $htaccessContent .= "</Files>\n";
            file_put_contents($htaccessFile, $htaccessContent);
        }
        
        $filePath = $exportsDir . $filename;
        $output = fopen($filePath, 'w');

        // Add BOM for proper UTF-8 encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");
        
        // Add title section with filter details
        fputcsv($output, ['Unhotel Performance Report (Cronjob Generated)']);
        fputcsv($output, ['Generated on', date('Y-m-d H:i:s')]);
        
        // Show filter details if withFilters is true
        if ($withFilters) {
            fputcsv($output, ['FILTERS:']);
            
            // Check-in period
            fputcsv($output, ['Check-in period:', date('d/m/Y', strtotime($checkinFrom)) . ' to ' . date('d/m/Y', strtotime($checkinTo))]);
            
            // Categories
            if (!empty($categories) && is_array($categories)) {
                fputcsv($output, ['Categories:', implode(', ', $categories)]);
            }
            
            // Apartments
            if (!empty($apartments) && is_array($apartments)) {
                // Get property names for display
                $apartmentNames = [];
                foreach ($apartments as $apartmentId) {
                    if (isset($propertyNames[$apartmentId])) {
                        $apartmentNames[] = $propertyNames[$apartmentId];
                    }
                }
                fputcsv($output, ['Apartments:', implode(', ', $apartmentNames)]);
            }
        } else {
            fputcsv($output, ['FILTERS:', 'None (showing all data)']);
            fputcsv($output, ['Date range:', date('d/m/Y', strtotime($checkinFrom)) . ' to ' . date('d/m/Y', strtotime($checkinTo))]);
        }
        
        // Empty row separator
        fputcsv($output, []);
        
        // Export Reservations
        fputcsv($output, ['=== RESERVATIONS ===']);
        
        if (!empty($allReservationsForExport) && count($allReservationsForExport) > 0) {
            fputcsv($output, ['APT', 'Check-in', 'Check-out', 'OTA', 'No.', 'Amount', 'OTA Commission', 'CC Commission', 'Unhotel Commission', 'Net']);
            
            // Process reservations to split multi-property reservations - SAME logic as frontend
            $csvReservationsTotalAmount = 0;
            $csvReservationsTotalCmms = 0;
            $csvReservationsTotalTransferCommission = 0;
            $csvReservationsTotalUnhotelCommission = 0;
            $csvReservationsTotalNet = 0;
            
            // Process each reservation into split records for CSV - SAME splitting logic as frontend
            foreach ($allReservationsForExport as $originalReservation) {
                if (!empty($originalReservation->properties) && $originalReservation->properties->count() > 1) {
                    // Process each property individually for multi-property reservations
                    foreach ($originalReservation->properties as $property) {
                        $propertyName = strtok($property->name, ' '); // Get short name
                        
                        // Get the amounts specific to this property
                        $amount = $originalReservation->getTotal($property->id);
                        $cmms = $originalReservation->getCmms($property->id);
                        $transferCommission = $originalReservation->getTransferCommission($property->id);
                        $unhotelCommission = $originalReservation->getUnhotelCommission($property->id);
                        $netAmount = $originalReservation->getTotalNet($property->id);
                        
                        // Track totals
                        $csvReservationsTotalAmount += $amount;
                        $csvReservationsTotalCmms += $cmms;
                        $csvReservationsTotalTransferCommission += $transferCommission;
                        $csvReservationsTotalUnhotelCommission += $unhotelCommission;
                        $csvReservationsTotalNet += $netAmount;
                        
                        fputcsv($output, [
                            $propertyName,
                            !empty($originalReservation->checkin) ? date('d/m/Y', $originalReservation->checkin) : '',
                            !empty($originalReservation->checkout) ? date('d/m/Y', $originalReservation->checkout) : '',
                            $originalReservation->getChannel()['name'] ?? '',
                            $originalReservation->id,
                            number_format($amount, 2, ',', '.'),
                            number_format($cmms, 2, ',', '.'),
                            number_format($transferCommission, 2, ',', '.'),
                            number_format($unhotelCommission, 2, ',', '.'),
                            number_format($netAmount, 2, ',', '.')
                        ]);
                    }
                } else {
                    // Single property reservation
                    $property = $originalReservation->properties->first();
                    if ($property) {
                        $propertyName = strtok($property->name, ' ');
                        $propertyId = $property->id;
                    } else {
                        $propertyName = '';
                        $propertyId = null;
                    }
                    
                    // Get the amounts
                    $amount = $originalReservation->getTotal($propertyId);
                    $cmms = $originalReservation->getCmms($propertyId);
                    $transferCommission = $originalReservation->getTransferCommission($propertyId);
                    $unhotelCommission = $originalReservation->getUnhotelCommission($propertyId);
                    $netAmount = $originalReservation->getTotalNet($propertyId);
                    
                    // Track totals
                    $csvReservationsTotalAmount += $amount;
                    $csvReservationsTotalCmms += $cmms;
                    $csvReservationsTotalTransferCommission += $transferCommission;
                    $csvReservationsTotalUnhotelCommission += $unhotelCommission;
                    $csvReservationsTotalNet += $netAmount;
                    
                    fputcsv($output, [
                        $propertyName,
                        !empty($originalReservation->checkin) ? date('d/m/Y', $originalReservation->checkin) : '',
                        !empty($originalReservation->checkout) ? date('d/m/Y', $originalReservation->checkout) : '',
                        $originalReservation->getChannel()['name'] ?? '',
                        $originalReservation->id,
                        number_format($amount, 2, ',', '.'),
                        number_format($cmms, 2, ',', '.'),
                        number_format($transferCommission, 2, ',', '.'),
                        number_format($unhotelCommission, 2, ',', '.'),
                        number_format($netAmount, 2, ',', '.')
                    ]);
                }
            }
            
            // Add totals row for reservations - using the calculated CSV totals
            fputcsv($output, [
                'TOTAL', '', '', '', '', 
                number_format($csvReservationsTotalAmount, 2, ',', '.'),
                number_format($csvReservationsTotalCmms, 2, ',', '.'),
                number_format($csvReservationsTotalTransferCommission, 2, ',', '.'),
                number_format($csvReservationsTotalUnhotelCommission, 2, ',', '.'),
                number_format($csvReservationsTotalNet, 2, ',', '.')
            ]);
        } else {
            fputcsv($output, ['No reservations found']);
        }
        
        // Empty row separator
        fputcsv($output, []);

        // Export Expenses
        fputcsv($output, ['=== EXPENSES ===']);
        
        if (!empty($expenses) && count($expenses) > 0) {
            fputcsv($output, ['APT', 'Date', 'Category', 'Note', 'Amount', 'Attachment']);
            
            foreach ($expenses as $expense) {
                $attachmentText = '';
                if (!empty($expense->attachment)) {
                    $attachmentText = 'Available';
                }
                
                fputcsv($output, [
                    $expense->property_name,
                    !empty($expense->date) ? date('d/m/Y', strtotime($expense->date)) : '',
                    $expense->category->name ?? '',
                    $expense->note ?? '',
                    number_format($expense->amount, 2, ',', '.'),
                    $attachmentText
                ]);
            }
            
            // Add totals row for expenses
            fputcsv($output, [
                'TOTAL', '', '', '', 
                number_format($expensesTotalAmount, 2, ',', '.'),
                ''
            ]);
        } else {
            fputcsv($output, ['No expenses found']);
        }
        
        // Empty row separator
        fputcsv($output, []);

        // Export Transfers
        fputcsv($output, ['=== TRANSFERS ===']);
        
        if (!empty($transfers) && count($transfers) > 0) {
            fputcsv($output, ['APT', 'Date', 'Method', 'Note', 'Amount', 'Attachment']);
            
            foreach ($transfers as $transfer) {
                $attachmentText = '';
                if (!empty($transfer->attachment)) {
                    $attachmentText = 'Available';
                }
                
                fputcsv($output, [
                    $transfer->property_name,
                    !empty($transfer->date) ? date('d/m/Y', strtotime($transfer->date)) : '',
                    $transfer->method->name ?? '',
                    $transfer->note ?? '',
                    number_format($transfer->amount, 2, ',', '.'),
                    $attachmentText
                ]);
            }
            
            // Add totals row for transfers
            fputcsv($output, [
                'TOTAL', '', '', '',
                number_format($transfersTotalAmount, 2, ',', '.'),
                ''
            ]);
        } else {
            fputcsv($output, ['No transfers found']);
        }

        // Add global totals information
        fputcsv($output, []);
        
        // Calculate totals (similar to what's shown at the bottom of the performance page)
        $total_due = 0;
        $expected_earnings = 0;
        
        foreach ($properties as $property) {
            [$due, ] = $service->getTotalDue($property->id);
            [$expected, ] = $service->getExpectedEarnings($property->id);
            $total_due += $due;
            $expected_earnings += $expected;
        }
        
        fputcsv($output, ['=== SUMMARY ===']);
        fputcsv($output, ['Total due', number_format($total_due, 2, ',', '.')]);
        fputcsv($output, ['Expected earnings', number_format($expected_earnings, 2, ',', '.')]);

        fclose($output);

        // Return the file path for cronjob use
        return $filePath;
    }
}