@include('partials.header')

<div class="property-manager-container">
    <h2><a href="{{ getPoaUrl('admin/expenses/') }}"><?php _e('Expenses Sheet', 'propery-owner-access') ?></a></h2>

    @include('partials.notifications')

    <form class="full-width {{ !empty($expense) ? 'form-edit' : '' }}" action="" method="post"
        enctype="multipart/form-data">
        @method(!empty($expense) ? 'PATCH' : 'POST')
        @php wp_nonce_field( '', 'wp_nonce_field' ) @endphp
        <input type="hidden" name="action" value="wp_handle_upload">

        <div class="form-wrapper rows">
            <div class="form-wrapper cols cols-equal-heights expenses-transfers-form">
                <div class="form-wrapper cols">
                    <div class="form-wrapper">
                        <label for="date"><?php _e('Date', 'propery-owner-access') ?></label>
                        <div class="form-wrapper">
                            <input type="date" name="date" class="form-control" id="date"
                                value="{{ !empty($expense) && !empty($expense->date) ? date('Y-m-d', strtotime($expense->date)) : '' }}"
                                required>
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="room_id"><?php _e('Property', 'propery-owner-access') ?></label>
                        <div class="form-wrapper">
                            <div class="select-wrapper">
                                <select name="room_id" class="form-control" id="room_id" required>
                                    <option value=""><?php _e('Select', 'propery-owner-access') ?></option>
                                    @if (!empty($properties))
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}"
                                                {{ !empty($expense) && !empty($expense->room_id) && $expense->room_id == $property->id ? 'selected' : '' }}>
                                                {{ $property->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="expenses_category_id"><?php _e('Category', 'propery-owner-access') ?></label>
                        <div class="form-wrapper">
                            <div class="select-wrapper">
                                <select name="expenses_category_id" class="form-control" id="expenses_category_id"
                                    required>
                                    <option value=""><?php _e('Select', 'propery-owner-access') ?></option>
                                    @if (!empty($categories))
                                        @foreach ($categories as $category)
                                            @if ( !empty($category['subcategories']) )
                                                <optgroup label="{{ getTranslatedPoaString($category['name'], 'expense-category-'.$category['id']) }}">
                                                    @foreach ($category['subcategories'] as $subcategory)
                                                        <option value="{{ $subcategory['id'] }}"
                                                            {{ !empty($expense) && !empty($subcategory['id']) && $expense->expenses_category_id == $subcategory['id'] ? 'selected' : '' }}>
                                                            {{ getTranslatedPoaString($subcategory['name'], 'expense-category-'.$subcategory['id']) }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @else
                                                <optgroup label="{{ getTranslatedPoaString($category['name'], 'expense-category-'.$category['id']) }}">
                                                    <option value="{{ $category['id'] }}"
                                                        {{ !empty($expense) && !empty($expense->expenses_category_id) && $expense->expenses_category_id == $category['id'] ? 'selected' : '' }}>
                                                        {{ getTranslatedPoaString($category['name'], 'expense-category-'.$category['id']) }}
                                                    </option>
                                                </optgroup>
                                            @endif
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
                                value="{{ !empty($expense) && !empty($expense->note) ? $expense->note : '' }}">
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="amount"><?php _e('Amount', 'propery-owner-access') ?></label>
                        <div class="form-wrapper">
                            <input type="number" step="any" name="amount" class="form-control" id="amount"
                                value="{{ !empty($expense) && !empty($expense->amount) ? $expense->amount : '' }}"
                                required>
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="attachment">
                            <?php _e('Attachment', 'propery-owner-access') ?>
                            {!! !empty($expense) ? '<span class="info">File will be replaced</span>' : '' !!}
                        </label>
                        <div class="form-wrapper">
                            <input type="file" name="attachment" class="form-control" id="attachment">
                        </div>
                    </div>
                    <div class="form-wrapper no-flex">
                        <label for="owner"><?php _e('Owner', 'propery-owner-access') ?></label>
                        <div class="form-wrapper">
                            <div class="checkbox-wrapper">
                                <div class="checkbox">
                                    <input type="checkbox" name="owner" value="1" class="form-check-input"
                                        id="owner"
                                        {{ !empty($expense) && !empty($expense->owner) && $expense->owner == 1 ? 'checked' : '' }}
                                        {{ !isset($expense) ? 'checked' : '' }}>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="align-self-y-bottom">
                    <input type="submit" class="btn btn-primary" value="<?php _e(!empty($expense) ? 'Save' : 'Add', 'propery-owner-access'); ?>">
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
                    <th><?php _e('Category', 'propery-owner-access') ?></th>
                    <th><?php _e('Note', 'propery-owner-access') ?></th>
                    <th class="align-right"><?php _e('Amount', 'propery-owner-access') ?></th>
                    <th class="column-attachment"><?php _e('Attachment', 'propery-owner-access') ?></th>
                    <th class="no-wrap align-center"><?php _e('Owner', 'propery-owner-access') ?></th>
                    <th class="column-actions"></th>
                </tr>
            </thead>
            <tbody>
                @if (count($expenses) > 0)
                    @foreach ($expenses as $expense)
                        <tr>
                            <td>{{ date('d/m/Y', strtotime($expense->date)) }}</td>
                            <td>{{ $expense->property->name }}</td>
                            <td>{{ !empty($expense->category) ? getTranslatedPoaString($expense->category->name, 'expense-category-'.$expense->category->id) : '' }}</td>
                            <td>{{ $expense->note ?? '' }}</td>
                            <td class="align-right no-wrap">R$ {{ formatAmount($expense->amount) }}</td>
                            <td class="no-wrap">
                                @if (!empty($expense->attachment))
                                    <a href="{{ wp_upload_dir()['baseurl'] . POA_FOLDER_EXPENSES . '/' . $expense->attachment }}"
                                        download>
                                        <i class="las la-download"></i> <?php _e('Download', 'propery-owner-access') ?>
                                    </a>
                                @endif
                            </td>
                            <td class="align-center">
                                <i
                                    class="las la-check-circle owner-status {{ $expense->owner == 0 ? 'owner-status-2' : '' }}"></i>
                            </td>
                            <td class="column-actions">
                                <a href="{{ getPoaUrl('admin/expenses/'.$expense->id) }}" class="action-btn">
                                    <i class="las la-pencil-alt"></i>
                                </a>
                                <form method="post" action="{{ getPoaUrl('admin/expenses/'.$expense->id) }}"
                                    class="form-delete">
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
                        <td colspan="8" class="align-center"><?php _e('No expenses found', 'propery-owner-access') ?>.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    @include('admin.partials.pagination', ['paginatedData' => $expenses])
</div>

@include('partials.footer')
