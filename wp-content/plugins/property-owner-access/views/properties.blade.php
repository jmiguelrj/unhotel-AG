@include('partials.header')

<div class="property-manager-container">
    <h2><?php _e('Owners Dashboard', 'propery-owner-access') ?></h2>

    <h3><?php _e('Profile', 'propery-owner-access') ?></h3>

    <div class="profile">
        <div class="profile-avatar">
            {!! $user_avatar !!}
        </div>
        <div class="profile-info">
            <div>
                <div class="profile-label"><?php _e('Name', 'propery-owner-access') ?></div>
                <div>{{ $user->display_name }}</div>
            </div>
            <div>
                <div class="profile-label"><?php _e('E-mail', 'propery-owner-access') ?></div>
                <div>{{ $user->email }}</div>
            </div>
        </div>
    </div>

    <h3><?php _e('Properties', 'propery-owner-access') ?></h3>
  
    <div class="owner-properties">
        @if ( count($properties) > 0 )
            @foreach ( $properties as $property )
                <div class="owner-property">
                    <a href="{{ getPoaUrl('properties/'.$property->id) }}"></a>
                    <img src="{{ plugins_url('vikbooking/site/resources/uploads/'.$property->img) }}">
                    <div class="property-title">
                        {{ $property->name }}
                    </div>
                </div>
            @endforeach
        @else
            <?php _e('User does not have properties', 'propery-owner-access') ?>.
        @endif

    </div>

    <h3><?php _e('Statistics', 'propery-owner-access') ?></h3>

    <div class="totals">
        <div class="totals-wrapper">
            <div class="totals-label"><?php _e('Total properties', 'propery-owner-access') ?></div>
            <div class="totals-sum count-up"><span data-no="{{ count($properties) }}" data-not-amount><i class="las la-spinner rotate"></i></span></div>
        </div>
        <div class="totals-wrapper">
            <div class="totals-label"><?php _e('Total reservations', 'propery-owner-access') ?></div>
            <div class="totals-sum count-up"><span data-no="{{ $total_reservations }}" data-not-amount><i class="las la-spinner rotate"></i></span></div>
        </div>
        <div class="totals-wrapper">
            <div class="totals-label"><?php _e('Total earnings', 'propery-owner-access') ?></div>
            <div class="totals-sum count-up"><span>R$</span> <span data-no="{{ $total_earnings }}"><i class="las la-spinner rotate"></i></span></div>
        </div>
    </div>
</div>

@include('partials.footer')