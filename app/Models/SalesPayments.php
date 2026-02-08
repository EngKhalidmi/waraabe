<?php

namespace App\Models;

use App\Models\FuelSale;
use App\Models\Products;
use App\Models\FuelSaleTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'proID',
        'cash_littres',
        'credit_littres',
        'selling_littres',
        'total_cash',
        'transaction_id',
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(FuelSale::class);
    }

    public function transaction()
    {
        return $this->belongsTo(FuelSaleTransaction::class);
    }
    public function product()
    {
        return $this->belongsTo(Products::class, 'proID', 'id');
    }
}
