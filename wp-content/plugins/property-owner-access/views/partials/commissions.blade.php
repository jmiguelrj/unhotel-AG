<div class="form-wrapper cols">
    <div class="form-wrapper cols">
        <div class="form-wrapper">
            <label><?php _e('Percentage', 'propery-owner-access') ?></label>
            <div class="form-wrapper">
                <input type="number" name="percentage[]" class="form-control"
                    value="{{ $commission->percentage ?? '' }}" required>
            </div>
        </div>
        <div class="form-wrapper">
            <label><?php _e('Date from', 'propery-owner-access') ?></label>
            <div class="form-wrapper">
                <input type="date" name="date_from[]" class="form-control"
                    value="{{ !empty($commission->date_from) ? date('Y-m-d', strtotime($commission->date_from)) : date('Y-m-d') }}" required>
            </div>
        </div>
        <div class="form-wrapper">
            <label><?php _e('Date to', 'propery-owner-access') ?></label>
            <div class="form-wrapper">
                <input type="date" name="date_to[]" class="form-control"
                    value="{{ !empty($commission->date_to) ? date('Y-m-d', strtotime($commission->date_to)) : '2100-01-01' }}" required>
            </div>
        </div>
    </div>
    @php
        $isLastOrEmpty = (!empty($propertyOwner->commissions) && $index == count($propertyOwner->commissions) - 1) || empty($propertyOwner->commissions);
    @endphp
    <div class="align-self-y-middle">
        <span class="btn-action btn-action-{{ $isLastOrEmpty ? 'add' : 'remove' }}">
            <i class="las la-{{ $isLastOrEmpty ? 'plus' : 'minus' }}"></i>
        </span>
    </div>
</div>