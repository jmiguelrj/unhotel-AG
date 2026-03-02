<?php
use Corcel\Model;

class Expense extends Model {
    protected $table = 'poa_expenses';

    // Disable timestamps
    public $timestamps = false;

    // Relatie cu tabelul wp_vikbooking_rooms
    public function property() {
        return $this->belongsTo(Property::class, 'room_id');
    }

    // Relatie cu tabelul poa_expense_categories
    public function category() {
        return $this->belongsTo(ExpenseCategory::class, 'expenses_category_id');
    }
}