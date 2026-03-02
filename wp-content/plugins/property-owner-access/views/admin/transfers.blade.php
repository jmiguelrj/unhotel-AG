@include('partials.header')

<div class="property-manager-container">
    <h2><a href="{{ getPoaUrl('admin/transfers') }}"><?php _e('Transfers', 'propery-owner-access') ?></a></h2>

    @include('partials.notifications')

    <form class="full-width {{ !empty($transfer)  ? 'form-edit' : '' }}" action="" method="post" enctype="multipart/form-data">
        @method(!empty($transfer) ? 'PATCH' : 'POST')
        @php wp_nonce_field( '', 'wp_nonce_field' ) @endphp
        <input type="hidden" name="action" value="wp_handle_upload">

        <div class="form-wrapper rows">
            <div class="form-wrapper cols expenses-transfers-form">
                <div class="form-wrapper cols cols-equal-heights">
                    <div class="form-wrapper">
                        <label for="date"><?php _e('Date', 'propery-owner-access') ?></label>
                        <div class="form-wrapper">
                            <input type="date" name="date" class="form-control" id="date"
                                value="{{ !empty($transfer) && !empty($transfer->date) ? date('Y-m-d', strtotime($transfer->date)) : '' }}"
                                required>
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="room_id"><?php _e('Property', 'propery-owner-access') ?></label>
                        <div class="form-wrapper">
                            <div class="select-wrapper">
                                <select name="room_id" class="form-control" id="room_id" required>
                                    <option value=""><?php _e('Select', 'propery-owner-access') ?></option>
                                    @if ( !empty($properties) )
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}"
                                                {{ !empty($transfer) && !empty($transfer->room_id) && $transfer->room_id == $property->id ? 'selected' : '' }}>
                                                {{ $property->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="transfer_method_id"><?php _e('Method', 'propery-owner-access') ?></label>
                        <div class="form-wrapper">
                            <div class="select-wrapper">
                                <select name="transfer_method_id" class="form-control" id="transfer_method_id"
                                    required>
                                    <option value=""><?php _e('Select', 'propery-owner-access') ?></option>
                                    @if ( !empty($methods) )
                                        @foreach ($methods as $method)
                                            <option value="{{ $method->id }}"
                                                {{ !empty($transfer) && !empty($transfer->transfer_method_id) && $transfer->transfer_method_id == $method->id ? 'selected' : '' }}>
                                                {{ getTranslatedPoaString($method->name, 'transfer-method-'.$method->id) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="note"><?php _e('Note', 'propery-owner-access') ?></label>
                        <div class="form-wrapper">
                            <input type="text" name="note" class="form-control" id="note"
                                value="{{ !empty($transfer) && !empty($transfer->note) ? $transfer->note : '' }}">
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="amount"><?php _e('Amount', 'propery-owner-access') ?></label>
                        <div class="form-wrapper">
                            <input type="number" step="any" name="amount" class="form-control" id="amount"
                                value="{{ !empty($transfer) && !empty($transfer->amount) ? $transfer->amount : '' }}"
                                required>
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="attachment">
                            <?php _e('Attachment', 'propery-owner-access') ?>
                            <?php echo ( ( !empty($transfer) ) ? '<span class="info">'.__('File will be replaced', 'propery-owner-access').'</span>' : '' ); ?>
                        </label>
                        <div class="form-wrapper">
                            <input type="file" name="attachment" class="form-control" id="attachment">
                        </div>
                    </div>
                </div>

                <div class="align-self-y-bottom">
                    <input type="submit" class="btn btn-primary" value="<?php _e(!empty($transfer) ? 'Save' : 'Add', 'propery-owner-access') ?>">
                </div>
            </div>
        </div>
    </form>

    <div class="responsive-table">
        <table class="property-manager-table">
            <thead>
                <tr>
                    <th class="column-date"><?php _e('Date', 'propery-owner-access') ?></th>
                    <th><?php _e('Property', 'propery-owner-access') ?></th>
                    <th><?php _e('Method', 'propery-owner-access') ?></th>
                    <th><?php _e('Note', 'propery-owner-access') ?></th>
                    <th class="align-right"><?php _e('Amount', 'propery-owner-access') ?></th>
                    <th class="column-attachment"><?php _e('Attachment', 'propery-owner-access') ?></th>
                    <th class="column-actions"></th>
                </tr>
            </thead>
            <tbody>
                @if (count($transfers) > 0)
                    @foreach ($transfers as $transfer)
                        <tr>
                            <td>{{ date('d/m/Y', strtotime($transfer->date)) }}</td>
                            <td>{{ $transfer->property->name }}</td>
                            <td>{{ getTranslatedPoaString($transfer->method->name, 'transfer-method-'.$transfer->method->id) }}</td>
                            <td>{{ $transfer->note ?? '' }}</td>
                            <td class="align-right no-wrap">R$ {{ formatAmount($transfer->amount) }}</td>
                            <td class="no-wrap">
                                @if ( !empty($transfer->attachment) )
                                    <a href="{{ wp_upload_dir()['baseurl'].POA_FOLDER_TRANSFERS.'/'.$transfer->attachment }}" download>
                                        <i class="las la-download"></i> <?php _e('Download', 'propery-owner-access') ?>
                                    </a>
                                @endif
                            </td>
                            <td class="column-actions">
                                <a href="{{ getPoaUrl('admin/transfers/'.$transfer->id) }}" class="action-btn">
                                    <i class="las la-pencil-alt"></i>
                                </a>
                                <form method="post" action="poa/admin/transfers/{{ $transfer->id }}" class="form-delete">
                                    @method('DELETE')
                                    <button type="submit" class="action-btn">
                                        <i class="las la-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="align-center"><?php _e('No transfers found', 'propery-owner-access') ?>.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginatedData' => $transfers])

</div>

@include('partials.footer')
