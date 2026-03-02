<?php
use Corcel\Model;

class Property extends Model {
    protected $table = 'vikbooking_rooms';

    // Disable timestamps
    public $timestamps = false;

    // Relatie cu tabelul vikbooking_orders (rezervari)
    public function reservations() {
        return $this->belongsToMany(Reservation::class, 'vikbooking_ordersrooms', 'idroom', 'idorder')
            ->using(ReservationProperty::class)
            ->withPivot([
                'room_cost',
                'cust_cost',
        ]);
    }
    // Relatie cu tabelul poa_expenses (cheltuieli)
    public function expenses() {
        return $this->hasMany(Expense::class, 'room_id');
    }
    // Relatie cu tabelul poa_transfers (cheltuieli)
    public function transfers() {
        return $this->hasMany(Transfer::class, 'room_id');
    }
    // Functie de scoatere a numelui proprietatii prescurtat, primul cuvant doar
    public function getShortName() {
        return strtok($this->name, ' ');
    }
}