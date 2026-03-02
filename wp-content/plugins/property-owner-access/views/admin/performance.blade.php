@include('partials.header')

<div class="property-manager-container">
    <h2 class="title-w-breadcrumbs"><?php _e('Performance Portal', 'propery-owner-access'); ?></h2>

    <div class="poa-breadcrumbs">
        <a href="#"><?php _e("Owner's portal", 'propery-owner-access'); ?></a> <span>&gt;</span>
        <span><?php _e('Performance Portal', 'propery-owner-access'); ?></span>
    </div>

    <form class="full-width filter-date-form" action="" method="get">
        <div class="form-wrapper rows">

            <div class="form-wrapper cols">

                <div class="form-wrapper cols">
                    <div class="form-wrapper">
                        <label for="categories" class="mt-0"><?php _e('Categories', 'propery-owner-access'); ?></label>
                        <div class="form-wrapper">
                            <select class="select2-multiple" name="categories[]" multiple="multiple" id="categories" data-placeholder="<?php _e('All', 'propery-owner-access'); ?>">
                                @if (!empty($propertyCategories))
                                    @foreach ($propertyCategories as $propertyCategoryId => $propertyCategory)
                                        <option value="{{ $propertyCategoryId }}" {{ !empty($categories) && in_array($propertyCategoryId, $categories) ? 'selected' : '' }}>
                                            {{ $propertyCategory }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                            <span class="select2-remove-selected-options"><?php _e('Remove all selected options', 'propery-owner-access'); ?></span>
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="apartments" class="mt-0"><?php _e('Apartments', 'propery-owner-access'); ?></label>
                        <div class="form-wrapper">
                            <select class="select2-multiple" name="apartments[]" multiple="multiple" id="apartments" data-placeholder="<?php _e('All', 'propery-owner-access'); ?>">
                                @if (!empty($propertiesListAll))
                                    @foreach ($propertiesListAll as $id => $name)
                                        <option value="{{ $id }}" {{ !empty($apartments) && in_array($id, $apartments) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                            <span class="select2-remove-selected-options"><?php _e('Remove all selected options', 'propery-owner-access'); ?></span>
                        </div>
                    </div>
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

                <div>
                    <input type="submit" class="btn btn-primary portal-filter-submit" value="<?php _e('Filter', 'propery-owner-access'); ?>">
                </div>

            </div>

        </div>
    </form>

    <div class="list-menu-container">
        <div class="list-menu">
            @php
                $query = $_GET;
                unset($query['page']);
                $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
                $makeTabUrl = function($dataType) use ($baseUrl, $query) {
                    return $baseUrl . '?' . http_build_query(array_merge($query, ['dataType' => $dataType]));
                };
                $currentTab = isset($_GET['dataType']) ? $_GET['dataType'] : 'reservations';
            @endphp
            <a href="{{ $makeTabUrl('reservations') }}" class="btn {{ $currentTab == 'reservations' ? 'active' : '' }}">
                <?php _e('Reservations', 'propery-owner-access'); ?>
            </a>
            <a href="{{ $makeTabUrl('expenses') }}" class="btn {{ $currentTab == 'expenses' ? 'active' : '' }}">
                <?php _e('Expenses', 'propery-owner-access'); ?>
            </a>
            <a href="{{ $makeTabUrl('transfers') }}" class="btn {{ $currentTab == 'transfers' ? 'active' : '' }}">
                <?php _e('Transfers', 'propery-owner-access'); ?>
            </a>
        </div>
    </div>

    @if ($dataType == 'reservations')
    <h3><?php _e('Reservations', 'propery-owner-access'); ?></h3>

        <div class="responsive-table">
            <table class="property-manager-table table-sort no-class-infer">
                <thead>
                    <tr>
                        <th class="th-sort data-sort"><?php _e('APT', 'propery-owner-access'); ?></th>
                        <th class="th-sort data-sort"><?php _e('Check-in', 'propery-owner-access'); ?></th>
                        <th class="th-sort data-sort"><?php _e('Check-out', 'propery-owner-access'); ?></th>
                        <th class="th-sort data-sort"><?php _e('OTA', 'propery-owner-access'); ?></th>
                        <th class="th-sort data-sort"><?php _e('No.', 'propery-owner-access'); ?></th>
                        <th class="th-sort data-sort align-right"><?php _e('Amount', 'propery-owner-access'); ?></th>
                        <th class="th-sort data-sort align-right"><?php _e('OTA Comm.', 'propery-owner-access'); ?></th>
                        <th class="th-sort data-sort align-right"><?php _e('CC Comm.', 'propery-owner-access'); ?></th>
                        <th class="th-sort data-sort align-right"><?php _e('UH Comm.', 'propery-owner-access'); ?></th>
                        <th class="th-sort data-sort align-right"><?php _e('Net', 'propery-owner-access'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($reservations) && count($reservations) > 0)
                        @foreach ($reservations as $reservation)
                            <tr>
                                <td data-sort="{{ $reservation->checkin }}">
                                    <b>
                                        @if(isset($reservation->singleProperty))
                                            {{ $reservation->singleProperty->getShortName() }}
                                        @elseif(!empty($reservation->properties) && $reservation->properties->isNotEmpty())
                                            {{ $reservation->properties->first()->getShortName() }}
                                        @else
                                            {{ '' }}
                                        @endif
                                    </b>
                                </td>
                                <td data-sort="{{ $reservation->checkin }}">
                                    {{ !empty($reservation->checkin) ? date('d/m/Y', $reservation->checkin) : '' }}</td>
                                <td data-sort="{{ $reservation->checkout }}">
                                    {{ !empty($reservation->checkout) ? date('d/m/Y', $reservation->checkout) : '' }}</td>
                                <td data-sort="{{ $reservation->getChannel()['name'] }}">
                                    {!! $reservation->getChannel()['image'] !!}
                                </td>
                                    <td data-sort="{{ $reservation->id }}">
                                    {{ !empty($reservation->id) ? $reservation->id : '' }}</td>
                                @php
                                    $propertyId = isset($reservation->singleProperty) ? $reservation->singleProperty->id : null;
                                @endphp
                                <td data-sort="{{ $reservation->getTotal($propertyId) }}" class="align-right no-wrap">R$
                                    {{ formatAmount($reservation->getTotal($propertyId)) }}</td>
                                <td data-sort="{{ $reservation->getCmms($propertyId) }}" class="align-right no-wrap">R$
                                    {{ formatAmount($reservation->getCmms($propertyId)) }}</td>
                                <td data-sort="{{ $reservation->getTransferCommission($propertyId) }}" class="align-right no-wrap">R$
                                    {{ formatAmount($reservation->getTransferCommission($propertyId)) }}</td>
                                <td data-sort="{{ $reservation->getUnhotelCommission($propertyId) }}" class="align-right no-wrap">R$
                                    {{ formatAmount($reservation->getUnhotelCommission($propertyId)) }}
                                </td>
                                <td data-sort="{{ $reservation->getTotalNet($propertyId) }}" class="align-right no-wrap">R$
                                    {{ formatAmount($reservation->getTotalNet($propertyId)) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="10" class="align-center"><?php _e('No reservations found', 'propery-owner-access'); ?>.</td>
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
    @endif

    @if ($dataType == 'expenses')
        <h3><?php _e('Expenses', 'propery-owner-access'); ?></h3>

        <div class="responsive-table">
            <table class="property-manager-table">
                <thead>
                    <tr>
                        <th><?php _e('APT', 'propery-owner-access'); ?></th>
                        <th><?php _e('Date', 'propery-owner-access'); ?></th>
                        <th><?php _e('Category', 'propery-owner-access'); ?></th>
                        <th><?php _e('Note', 'propery-owner-access'); ?></th>
                        <th class="align-right"><?php _e('Amount', 'propery-owner-access'); ?></th>
                        <th class="th-attachment"><?php _e('Attachment', 'propery-owner-access'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($expenses) && count($expenses) > 0)
                        @foreach ($expenses as $expense)
                            <tr>
                                <td><b>{{ $expense->property_name }}</b></td>
                                <td>{{ !empty($expense->date) ? date('d/m/Y', strtotime($expense->date)) : '' }}</td>
                                <td>{{ getTranslatedPoaString($expense->category->name, 'expense-category-' . $expense->category->id) }}
                                </td>
                                <td>{{ $expense->note ?? '' }}</td>
                                <td class="align-right no-wrap">R$ {{ formatAmount($expense->amount) }}</td>
                                <td class="no-wrap">
                                    @if (!empty($expense->attachment))
                                        <a href="{{ wp_upload_dir()['baseurl'] . POA_FOLDER_EXPENSES . '/' . $expense->attachment }}"
                                            download>
                                            <i class="las la-download"></i> <?php _e('Download', 'propery-owner-access'); ?>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="total"></td>
                            <td class="total"></td>
                            <td class="total"></td>
                            <td class="total"></td>
                            <td class="total align-right no-wrap">R$ {{ formatAmount($expensesTotalAmount) }}</td>
                            <td class="total"></td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="6" class="align-center"><?php _e('No expenses found', 'propery-owner-access'); ?>.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif

    @if ($dataType == 'transfers')
        <h3><?php _e('Transfers', 'propery-owner-access'); ?></h3>

        <div class="responsive-table">
            <table class="property-manager-table">
                <thead>
                    <tr>
                        <th><?php _e('APT', 'propery-owner-access'); ?></th>
                        <th><?php _e('Date', 'propery-owner-access'); ?></th>
                        <th><?php _e('Method', 'propery-owner-access'); ?></th>
                        <th><?php _e('Note', 'propery-owner-access'); ?></th>
                        <th class="align-right"><?php _e('Amount', 'propery-owner-access'); ?></th>
                        <th class="th-attachment"><?php _e('Attachment', 'propery-owner-access'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($transfers) && count($transfers) > 0)
                        @foreach ($transfers as $transfer)
                            <tr>
                                <td><b>{{ $transfer->property_name }}</b></td>
                                <td>{{ !empty($transfer->date) ? date('d/m/Y', strtotime($transfer->date)) : '' }}</td>
                                <td>{{ getTranslatedPoaString($transfer->method->name, 'transfer-method-' . $transfer->method->id) }}
                                </td>
                                <td>{{ $transfer->note ?? '' }}</td>
                                <td class="align-right no-wrap">R$ {{ formatAmount($transfer->amount) }}</td>
                                <td class="no-wrap">
                                    @if (!empty($transfer->attachment))
                                        <a href="{{ wp_upload_dir()['baseurl'] . POA_FOLDER_TRANSFERS . '/' . $transfer->attachment }}"
                                            download>
                                            <i class="las la-download"></i> <?php _e('Download', 'propery-owner-access'); ?>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="total"></td>
                            <td class="total"></td>
                            <td class="total"></td>
                            <td class="total"></td>
                            <td class="total align-right no-wrap">R$ {{ formatAmount($transfersTotalAmount) }}</td>
                            <td class="total"></td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="6" class="align-center"><?php _e('No transfers found', 'propery-owner-access'); ?>.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif

    @include('admin.partials.pagination')

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

    <div class="export-controls">
        <h3><?php _e('Export', 'propery-owner-access'); ?></h3>

        <div class="form-wrapper flex">
            <div>
                @php
                    $exportQuery = $_GET;
                    unset($exportQuery['page']); // Remove pagination
                    $baseExportUrl = home_url('/poa/' . get_locale() . '/admin/performance/export');
                @endphp
    
                <span id="exportBaseUrl" data-base-url="{{ $baseExportUrl }}" style="display:none;"></span>
                
                <a href="#" class="btn btn-primary btn-export" id="exportCsvBtn">
                    <i class="las la-download"></i>
                    <span><?php _e('Export', 'propery-owner-access'); ?></span>
                </a>
            </div>
            <label>
                <input type="checkbox" id="exportWithFilters" checked> 
                <?php _e('Export with current filters', 'propery-owner-access'); ?>
            </label>
        </div>
    </div>

    @include('partials.footer')

</div>