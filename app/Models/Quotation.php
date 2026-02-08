<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $table = 'quotation'; // Make sure the table name is correctly defined
    protected $fillable = [
        'customer',
        'phone',
        'sub_total',
        'discount',
        'net_price',
        'date',
        'info',
        'depID',
    ];

    public function orders()
    {
        return $this->hasMany(QuotationOrders::class, 'transID', 'id');
    }

    // department relationship
    public function department() {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }
}


