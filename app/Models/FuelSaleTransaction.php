<?php

namespace App\Models;

use App\Models\Departments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelSaleTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_sale_id',
        'depID',
        'dphase',
        'product_id',
        'previous_reading',
        'current_reading',
        'liters',
        'rate',
        'total',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship with FuelSale
     */
    public function fuelSale()
    {
        return $this->belongsTo(FuelSale::class, 'fuel_sale_id');
    }

    /**
     * Relationship with Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * Relationship with User (cashier)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function department()
    {
        return $this->belongsTo(Departments::class, 'depID');
    }
}
