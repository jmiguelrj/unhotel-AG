<?php
use Corcel\Model;

class TransferMethod extends Model {
    protected $table = 'poa_transfer_methods';

    // Disable timestamps
    public $timestamps = false;

    // Relatie cu tabelul poa_transfers
    public function transfers() {
        return $this->hasMany(Transfer::class, 'poa_transfers');
    }
}