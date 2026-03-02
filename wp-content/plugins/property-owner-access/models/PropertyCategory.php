<?php

use Corcel\Model;
use Carbon\Carbon;

class PropertyCategory extends Model
{
    protected $table = 'vikbooking_categories';

    // Disable timestamps
    public $timestamps = false;
}
