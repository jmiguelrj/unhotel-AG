<?php
use Corcel\Model;

class PropertyOwnerCommission extends Model {
    protected $table = 'poa_property_owners_commissions';

    // Disable timestamps
    public $timestamps = false;

    // Relatie cu tabelul poa_property_owners
    public function property_owner() {
        return $this->belongsTo(PropertyOwner::class, 'property_owner_id');
    }
}