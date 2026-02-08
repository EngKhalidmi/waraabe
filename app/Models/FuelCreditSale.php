<?php

namespace App\Models;

use App\Models\Departments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelCreditSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_sale_id',
        'depID',
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
    public function department()
    {
        return $this->belongsTo(Departments::class, 'depID');
    }
}