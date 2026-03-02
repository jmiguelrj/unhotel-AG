@if (!$pdf)
    @include('partials.header')
@else
    <html>

    <head>
        <title>{{ $property->name }} | {{ date('d/m/Y', strtotime($checkinFrom)) }} -
            {{ date('d/m/Y', strtotime($checkinTo)) }}</title>
        <style>
            @page {
                margin: 30px 30px 80px 30px;
            }

            body {
                font-family: Arial, Helvetica, sans-serif;
            }

            h1 {
                font-size: 22px;
                margin-bottom: 20px;
            }

            h2 {
                font-size: 20px;
                margin-bottom: 20px;
            }

            h3 {
                font-size: 18px;
                margin-top: 30px;
                margin-bottom: 20px;
            }

            h1,
            h2,
            h3 {
                text-align: center;
            }

            .property-manager-table {
                font-size: 13px;
                width: 100%;
                border: 1px solid #ddd;
                border-collapse: collapse;
            }

            .property-manager-table th,
            .property-manager-table td {
                padding: 8px;
                text-align: left;
            }

            .property-manager-table th {
                background-color: #444444;
                color: #fff;
            }

            .property-manager-table tbody tr:nth-child(even) td {
                background-color: #f9f9f9;
            }

            .property-manager-table td.total {
                border-top: 3px solid #ddd;
                background: #fff !important;
                font-weight: bold;
            }

            .no-wrap {
                white-space: nowrap;
            }

            .align-center {
                text-align: center !important;
            }

            .align-right {
                text-align: right !important;
            }

            footer {
                position: fixed;
                bottom: -50px;
                left: 0px;
                right: 0px;
                padding: 10px;
                background: #eee;
                font-size: 12px;
                opacity: 0.4;
                text-align: center;
            }
        </style>
    </head>

    <body>

        <footer>
            <?php _e('Generated on', 'propery-owner-access'); ?> {{ date('d/m/Y') }} at {{ date('H:i') }}
        </footer>
@endif


<div class="property-manager-container">
    @if (!$pdf)
        <h2><?php _e('Owners Property', 'propery-owner-access'); ?></h2>

        <a href="{{ getPoaUrl('properties') }}">&laquo; <?php _e('Go back', 'propery-owner-access'); ?></a>

        <h3><?php _e('Property', 'propery-owner-access'); ?></h3>

        <div class="profile">
            <div class="profile-avatar">
                <img src="{{ plugins_url('vikbooking/site/resources/uploads/' . $property->img) }}">
            </div>
            <div class="profile-info">
                <div>
                    <div class="profile-label"><?php _e('Title', 'propery-owner-access'); ?></div>
                    <div>{{ $property->name }}</div>
                </div>
                <div>
                    <div class="profile-label"><?php _e('Address', 'propery-owner-access'); ?></div>
                    <div>{{ $property->smalldesc }}</div>
                </div>
                <div>
                    <div class="profile-label"><?php _e('Guests', 'propery-owner-access'); ?></div>
                    <div>{{ $property->totpeople }}</div>
                </div>
                <div>
                    <div class="profile-label"><?php _e('Link', 'propery-owner-access'); ?></div>
                    <div><a href="/detalhes-do-anuncio/?roomid={{ $property->id }}"
                            target="_blank"><?php _e('View property', 'propery-owner-access'); ?></a></div>
                </div>
            </div>
        </div>
    @else
        <h1>{{ $property->name }}</h1>
    @endif

    @if (!$pdf)
        <form class="full-width filter-date-form" action="" method="get">
            <div class="form-wrapper rows">

                <div class="form-wrapper cols">

                    <div class="form-wrapper cols">
                        <div class="form-wrapper">
                            <label for="checkinFrom" class="mt-0"><?php _e('Check-in from', 'propery-owner-access'); ?></label>
                            <div class="form-wrapper">
                                <input type="date" name="checkinFrom" class="form-control" id="checkinFrom"
                                    value="{{ $checkinFrom }}" min="{{ POA_STARTING_DATE }}" required>
                            </div>
                        </div>
                        <div class="form-wrapper">
                            <label for="checkinTo" class="mt-0"><?php _e('Check-in to', 'propery-owner-access'); ?></label>
                            <div class="form-wrapper">
                                <input type="date" name="checkinTo" class="form-control" id="checkinTo"
                                    value="{{ $checkinTo }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="align-self-y-bottom">
                        <input type="submit" class="btn btn-primary" value="<?php _e('Filter', 'propery-owner-access'); ?>">
                    </div>

                </div>

            </div>
        </form>
    @else
        <h2>{{ date('d/m/Y', strtotime($checkinFrom)) }} - {{ date('d/m/Y', strtotime($checkinTo)) }}</h2>
    @endif

    <h3><?php _e('Reservations', 'propery-owner-access'); ?></h3>

    <div class="responsive-table">
        <table class="property-manager-table table-sort no-class-infer">
            <thead>
                <tr>
                    <th class="th-sort data-sort"><?php _e('Check-in', 'propery-owner-access'); ?></th>
                    <th class="th-sort data-sort"><?php _e('Check-out', 'propery-owner-access'); ?></th>
                    <th class="th-sort data-sort"><?php _e('OTA', 'propery-owner-access'); ?></th>
                    <th class="th-sort data-sort"><?php _e('No.', 'propery-owner-access'); ?></th>
                    <th class="th-sort data-sort align-right"><?php _e('Amount', 'propery-owner-access'); ?></th>
                    <th class="th-sort data-sort align-right"><?php _e('OTA Commission', 'propery-owner-access'); ?></th>
                    <th class="th-sort data-sort align-right"><?php _e('CC Commission', 'propery-owner-access'); ?></th>
                    <th class="th-sort data-sort align-right"><?php _e('Unhotel Commission', 'propery-owner-access'); ?></th>
                    <th class="th-sort data-sort align-right"><?php _e('Net', 'propery-owner-access'); ?></th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($reservations) && count($reservations) > 0)
                    @foreach ($reservations as $reservation)
                        <tr>
                            <td data-sort="{{ $reservation->checkin }}">
                                {{ !empty($reservation->checkin) ? date('d/m/Y', $reservation->checkin) : '' }}</td>
                            <td data-sort="{{ $reservation->checkout }}">
                                {{ !empty($reservation->checkout) ? date('d/m/Y', $reservation->checkout) : '' }}</td>
                            <td data-sort="{{ $reservation->getChannel()['name'] }}">
                                {!! $reservation->getChannel()['image'] !!}
                            </td>
                                <td data-sort="{{ $reservation->id }}">
                                {{ !empty($reservation->id) ? $reservation->id : '' }}</td>
                            <td data-sort="{{ $reservation->getTotal($property->id) }}" class="align-right no-wrap">R$
                                {{ formatAmount($reservation->getTotal($property->id)) }}</td>
                            <td data-sort="{{ $reservation->getCmms($property->id) }}" class="align-right no-wrap">R$
                                {{ formatAmount($reservation->getCmms($property->id)) }}</td>
                            <td data-sort="{{ $reservation->getTransferCommission($property->id) }}" class="align-right no-wrap">R$
                                {{ formatAmount($reservation->getTransferCommission($property->id)) }}</td>
                            <td data-sort="{{ $reservation->getUnhotelCommission($property->id) }}" class="align-right no-wrap">R$
                                {{ formatAmount($reservation->getUnhotelCommission($property->id)) }}
                            </td>
                            <td data-sort="{{ $reservation->getTotalNet($property->id) }}" class="align-right no-wrap">R$
                                {{ formatAmount($reservation->getTotalNet($property->id)) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="align-center"><?php _e('No reservations found', 'propery-owner-access'); ?>.</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                @if (!empty($reservations) && count($reservations) > 0)
                    <tr>
                        <td class="total"></td>
                        <td class="total"></td>
                        <td class="total"></td>
                        <td class="total"></td>
                        <td class="total align-right no-wrap">R$ {{ formatAmount($reservationsTotalAmount) }}</td>
                        <td class="total align-right no-wrap">R$ {{ formatAmount($reservationsTotalCmms) }}</td>
                        <td class="total align-right no-wrap">R$
                            {{ formatAmount($reservationsTotalTransferCommission) }}</td>
                        <td class="total align-right no-wrap">R$
                            {{ formatAmount($reservationsTotalUnhotelCommission) }}</td>
                        <td class="total align-right no-wrap">R$ {{ formatAmount($reservationsTotalNet) }}</td>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>

    <h3><?php _e('Expenses', 'propery-owner-access'); ?></h3>

    <div class="responsive-table">
        <table class="property-manager-table">
            <thead>
                <tr>
                    <th><?php _e('Date', 'propery-owner-access'); ?></th>
                    <th><?php _e('Category', 'propery-owner-access'); ?></th>
                    <th><?php _e('Note', 'propery-owner-access'); ?></th>
                    <th class="align-right"><?php _e('Amount', 'propery-owner-access'); ?></th>
                    <?php echo !$pdf ? '<th class="th-attachment">' . __('Attachment', 'propery-owner-access') . '</th>' : ''; ?>
                </tr>
            </thead>
            <tbody>
                @if (!empty($expenses) && count($expenses) > 0)
                    @foreach ($expenses as $expense)
                        <tr>
                            <td>{{ date('d/m/Y', strtotime($expense->date)) }}</td>
                            <td>{{ getTranslatedPoaString($expense->category->name, 'expense-category-' . $expense->category->id) }}
                            </td>
                            <td>{{ $expense->note ?? '' }}</td>
                            <td class="align-right no-wrap">R$ {{ formatAmount($expense->amount) }}</td>
                            @if (!$pdf)
                                <td class="no-wrap">
                                    @if (!empty($expense->attachment))
                                        <a href="{{ wp_upload_dir()['baseurl'] . POA_FOLDER_EXPENSES . '/' . $expense->attachment }}"
                                            download>
                                            <i class="las la-download"></i> <?php _e('Download', 'propery-owner-access'); ?>
                                        </a>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    <tr>
                        <td class="total"></td>
                        <td class="total"></td>
                        <td class="total"></td>
                        <td class="total align-right no-wrap">R$ {{ formatAmount($expensesTotalAmount) }}</td>
                        @if (!$pdf)
                            <td class="total"></td>
                        @endif
                    </tr>
                @else
                    <tr>
                        <td colspan="{{ !$pdf ? 5 : 4 }}" class="align-center"><?php _e('No expenses found', 'propery-owner-access'); ?>.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <h3><?php _e('Transfers', 'propery-owner-access'); ?></h3>

    <div class="responsive-table">
        <table class="property-manager-table">
            <thead>
                <tr>
                    <th><?php _e('Date', 'propery-owner-access'); ?></th>
                    <th><?php _e('Method', 'propery-owner-access'); ?></th>
                    <th><?php _e('Note', 'propery-owner-access'); ?></th>
                    <th class="align-right"><?php _e('Amount', 'propery-owner-access'); ?></th>
                    <?php echo !$pdf ? '<th class="th-attachment">' . __('Attachment', 'propery-owner-access') . '</th>' : ''; ?>
                </tr>
            </thead>
            <tbody>
                @if (!empty($transfers) && count($transfers) > 0)
                    @foreach ($transfers as $transfer)
                        <tr>
                            <td>{{ date('d/m/Y', strtotime($transfer->date)) }}</td>
                            <td>{{ getTranslatedPoaString($transfer->method->name, 'transfer-method-' . $transfer->method->id) }}
                            </td>
                            <td>{{ $transfer->note ?? '' }}</td>
                            <td class="align-right no-wrap">R$ {{ formatAmount($transfer->amount) }}</td>
                            @if (!$pdf)
                                <td class="no-wrap">
                                    @if (!empty($transfer->attachment))
                                        <a href="{{ wp_upload_dir()['baseurl'] . POA_FOLDER_TRANSFERS . '/' . $transfer->attachment }}"
                                            download>
                                            <i class="las la-download"></i> <?php _e('Download', 'propery-owner-access'); ?>
                                        </a>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    <tr>
                        <td class="total"></td>
                        <td class="total"></td>
                        <td class="total"></td>
                        <td class="total align-right no-wrap">R$ {{ formatAmount($transfersTotalAmount) }}</td>
                        @if (!$pdf)
                            <td class="total"></td>
                        @endif
                    </tr>
                @else
                    <tr>
                        <td colspan="{{ !$pdf ? 5 : 4 }}" class="align-center"><?php _e('No transfers found', 'propery-owner-access'); ?>.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if (!$pdf)
        <div class="totals mt-50">
            <div class="totals-wrapper" data-tippy-content="<?php _e('The total due amount until the end of the previous month.', 'propery-owner-access'); ?>"
                data-tippy-placement="right">
                <div class="totals-label"><?php _e('Total due', 'propery-owner-access'); ?></div>
                <div class="totals-sum count-up"><span>R$</span> <span data-no="{{ $total_due }}"><i
                            class="las la-spinner rotate"></i></span></div>
            </div>
            
            <div class="totals-wrapper" data-tippy-content="<?php _e('Expected earnings for the current month.', 'propery-owner-access'); ?>"
                data-tippy-placement="right">
                <div class="totals-label"><?php _e('Expected earnings', 'propery-owner-access'); ?></div>
                <div class="totals-sum count-up"><span>R$</span> <span data-no="{{ $expected_earnings }}"><i
                            class="las la-spinner rotate"></i></span></div>
            </div>
        </div>

        @php
            $currentPageUrl = $_SERVER['REQUEST_URI'];
            $pdfUrl = str_contains($currentPageUrl, '?') ? $currentPageUrl . '&pdf' : $currentPageUrl . '?pdf';
        @endphp

        <a href="{{ $pdfUrl }}" class="btn btn-primary btn-export"><i
                class="las la-file-download"></i><span><?php _e('Export to PDF', 'propery-owner-access'); ?></span></a>

        @include('partials.footer')
    @else
        <h3><?php _e('Total due', 'propery-owner-access'); ?>: R$ {{ formatAmount($total_due) }}</h3>
    @endif

</div>

@if ($pdf)
    </body>

    </html>
@endif
