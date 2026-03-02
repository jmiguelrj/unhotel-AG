@include('partials.header')

<div class="property-manager-container">
    <h2><?php _e('Property Profile', 'propery-owner-access') ?></h2>

    @include('partials.notifications')

    <form action="" method="post" enctype="multipart/form-data" class="property-form {!! !empty($propertyOwner)  ? 'form-edit' : '' !!}">
        @method(!empty($propertyOwner) ? 'PATCH' : 'POST')
        @php wp_nonce_field( '', 'wp_nonce_field' ) @endphp
        <input type="hidden" name="action" value="wp_handle_upload">

        <div class="form-wrapper">
            <label for="room_id"><?php _e('Property profile', 'propery-owner-access') ?></label>
            <div class="form-wrapper">
                <div class="select-wrapper">
                    <select name="room_id" class="form-control" id="room_id" required>
                        <option value=""><?php _e('Select', 'propery-owner-access') ?></option>
                        @if (!empty($properties))
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}"
                                    {{ !empty($propertyOwner) && !empty($propertyOwner->room_id) && $propertyOwner->room_id == $property->id ? 'selected' : '' }}>
                                    {{ $property->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>
        <div class="form-wrapper">
            <label for="property-owner"><?php _e('Property owner', 'propery-owner-access') ?></label>
            <div class="form-wrapper">
                <div class="select-wrapper">
                    <select name="user_id" class="form-control" id="user_id" required>
                        <option value=""><?php _e('Select', 'propery-owner-access') ?></option>
                        @if (!empty($users))
                            @foreach ($users as $user)
                                <option value="{{ $user->ID }}"
                                    {{ !empty($propertyOwner) && !empty($propertyOwner->user_id) && $propertyOwner->user_id == $user->ID ? 'selected' : '' }}>
                                    {{ $user->display_name }} - {{ $user->email }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>
        <div class="form-wrapper">
            <label class="align-self-y-top"><?php _e('Unhotel commission', 'propery-owner-access') ?></label>
            <div class="form-wrapper rows manage-multiple">
                @if (!empty($propertyOwner) && !empty($propertyOwner->commissions))
                    @foreach ($propertyOwner->commissions as $index => $commission)
                        @include('partials.commissions', ['commission' => $commission, 'index' => $index, 'propertyOwner' => $propertyOwner])
                    @endforeach
                @else
                    @include('partials.commissions', ['propertyOwner' => $propertyOwner])
                @endif
            </div>
        </div>
        <div class="form-wrapper">
            <label for="contract">
                <?php _e('Property contract', 'propery-owner-access') ?>
                <?php echo ( ( !empty($propertyOwner) ) ? '<span class="info">'.__('File will be replaced', 'propery-owner-access').'</span>' : '' ); ?>
            </label>
            <div class="form-wrapper">
                <input type="file" name="contract" class="form-control" id="contract">
            </div>
        </div>
        <div class="form-wrapper">
            <label for="documents">
                <?php _e('Other documents', 'propery-owner-access') ?>
                <?php echo ( ( !empty($propertyOwner) ) ? '<span class="info">'.__('File will be replaced', 'propery-owner-access').'</span>' : '' ); ?>
            </label>
            <div class="form-wrapper">
                <input type="file" name="documents" class="form-control" id="documents">
            </div>
        </div>
        <div class="form-wrapper">
            <label for="note"><?php _e('Note', 'propery-owner-access') ?></label>
            <div class="form-wrapper">
                <textarea name="note" class="form-control" id="note">{{ !empty($propertyOwner) && !empty($propertyOwner->note) ? $propertyOwner->note : '' }}</textarea>
            </div>
        </div>
        <div class="form-wrapper">
            <input type="submit" class="btn btn-primary" value="<?php echo ( ( !empty($propertyOwner) ) ? __('Save', 'propery-owner-access') : __('Add', 'propery-owner-access') ); ?>">
        </div>
    </form>

    <div class="responsive-table">
        <table class="property-manager-table mt-50">
            <thead>
                <tr>
                    <th><?php _e('Property', 'propery-owner-access') ?></th>
                    <th class="column-owner"><?php _e('Owner', 'propery-owner-access') ?></th>
                    <th class="column-commissions"><?php _e('Unhotel commission', 'propery-owner-access') ?></th>
                    <th class="column-attachment"><?php _e('Contract', 'propery-owner-access') ?></th>
                    <th class="column-attachment"><?php _e('Documents', 'propery-owner-access') ?></th>
                    <th><?php _e('Note', 'propery-owner-access') ?></th>
                    <th class="column-actions"></th>
                </tr>
            </thead>
            <tbody>
                @if (count($propertyOwners) > 0)
                    @foreach ($propertyOwners as $propertyOwner)
                        <tr>
                            <td>{{ $propertyOwner->property->name }}</td>
                            <td class="column-owner">{{ $propertyOwner->user->display_name }}</td>
                            <td class="no-wrap">
                                @if (!empty($propertyOwner->commissions))
                                    @foreach ($propertyOwner->commissions as $commission)
                                        <ul class="commissions-list">
                                            <li>{{ $commission->percentage }}%</li>
                                            <li><i class="las la-hourglass-start"></i>
                                                {{ date('d/m/Y', strtotime($commission->date_from)) }}</li>
                                            <li><i class="las la-hourglass-end"></i>
                                                {{ date('d/m/Y', strtotime($commission->date_to)) }}</li>
                                        </ul>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if (!empty($propertyOwner->contract))
                                    <a href="{{ wp_upload_dir()['baseurl'] . POA_FOLDER_PROPERTIES . '/' . $propertyOwner->contract }}"
                                        download>
                                        <i class="las la-download"></i> <?php _e('Download', 'propery-owner-access') ?>
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if (!empty($propertyOwner->documents))
                                    <a href="{{ wp_upload_dir()['baseurl'] . POA_FOLDER_PROPERTIES . '/' . $propertyOwner->documents }}"
                                        download>
                                        <i class="las la-download"></i> <?php _e('Download', 'propery-owner-access') ?>
                                    </a>
                                @endif
                            </td>
                            <td>{{ $propertyOwner->note }}</td>
                            <td class="column-actions">
                                {{ switchToHost($propertyOwner->user->ID, $propertyOwner->property->id) }}
                                <a href="{{ getPoaUrl('admin/properties/'.$propertyOwner->id) }}" class="action-btn" title="<?php _e('Edit', 'propery-owner-access'); ?>">
                                    <i class="las la-pencil-alt"></i>
                                </a>
                                <form method="post" action="{{ getPoaUrl('admin/properties/'.$propertyOwner->id) }}"
                                    class="form-delete">
                                    @method('DELETE')
                                    <button type="submit" class="action-btn" title="<?php _e('Delete', 'propery-owner-access'); ?>">
                                        <i class="las la-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="align-center"><?php _e('No property profiles found', 'propery-owner-access') ?>.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@include('partials.footer')