@if ( !empty($notifications) )
    @foreach ($notifications as $notification)
        <div class="notification {{ $notification['type'] }} {{ isset($notification['sticky']) ? 'sticky' : '' }} ">
            {{ $notification['message'] }}
        </div>
    @endforeach
@endif