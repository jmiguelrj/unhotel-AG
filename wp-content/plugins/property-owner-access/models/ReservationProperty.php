<?php

use Corcel\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ReservationProperty extends Pivot
{
    protected $table = 'vikbooking_ordersrooms';

    // Disable timestamps
    public $timestamps = false;

    // Relatie cu tabelul wp_vikbooking_rooms
    public function property()
    {
        return $this->belongsTo(Property::class, 'idroom');
    }
    
    // Relatie cu tabelul wp_vikbooking_orders
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'idorder');
    }
}
