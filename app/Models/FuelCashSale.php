<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelCashSale extends Model
{
    use HasFactory;
      protected $table = 'fuel_cash_sale';
    protected $fillable = [
        'fuel_sale_id',
        'customer_id',
        'product_id',
        'quantity',
        'rate',
        'total',
        'description',
        'status',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * A credit sale belongs to a fuel sale
     */
    public function fuelSale()
    {
        return $this->belongsTo(FuelSale::class, 'fuel_sale_id');
    }

    /**
     * A credit sale belongs to a customer
     */
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}