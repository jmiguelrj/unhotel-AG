<?php
use Corcel\Model;
use Corcel\Model\User;

class PropertyOwner extends Model {
    protected $table = 'poa_property_owners';

    // Disable timestamps
    public $timestamps = false;

    // Relatie cu tabelul vikbooking_ordersrooms
    public function property() {
        return $this->belongsTo(Property::class, 'room_id');
    }

    // Relatie cu tabelul wp_users
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relatie cu tabelul poa_property_owners_commissions
    public function commissions() {
        return $this->hasMany(PropertyOwnerCommission::class, 'property_owner_id');
    }
}