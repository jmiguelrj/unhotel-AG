<?php

use Corcel\Model;
use Carbon\Carbon;

class Transfer extends Model
{
    protected $table = 'poa_transfers';

    // Disable timestamps
    public $timestamps = false;

    // Relatie cu tabelul wp_vikbooking_rooms
    public function property()
    {
        return $this->belongsTo(Property::class, 'room_id');
    }

    // Relatie cu tabelul poa_transfer_methods
    public function method()
    {
        return $this->belongsTo(TransferMethod::class, 'transfer_method_id');
    }
}
