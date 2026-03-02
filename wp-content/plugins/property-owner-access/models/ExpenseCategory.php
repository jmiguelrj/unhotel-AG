<?php
use Corcel\Model;

class ExpenseCategory extends Model {
    protected $table = 'poa_expense_categories';

    // Disable timestamps
    public $timestamps = false;

    // Relatie cu tabelul poa_expenses
    public function expenses() {
        return $this->hasMany(Expense::class, 'poa_expenses');
    }
}