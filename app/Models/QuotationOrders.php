<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationOrders extends Model
{
    use HasFactory;
    // Table
    protected $table = 'quotation_orders';
    protected $fillable = [
        'transID',
        'proID',
        'qty',
        'unit',
        'price',
        'total',
        'depID',
    ];

    // products
    public function pro() {
        return $this->belongsTo(Products::class, 'proID', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(SalesTransactions::class, 'transID', 'id');
    }

    // relation with department
    public function department() {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }
}
