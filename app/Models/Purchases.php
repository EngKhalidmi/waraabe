<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchases extends Model
{
    use HasFactory;
    protected $fillable = [
        'proID',
        'transID',
        'quantity',
        'unit_cost',
        'add_cost',
        'remaining',
        'total_cost',
        'supplier',
        'depID',
    ];

    // products
    public function pro() {
        return $this->belongsTo(Products::class, 'proID', 'id');
    }
    public function trans() {
        return $this->belongsTo(PurchaseTransactions::class, 'transID', 'id');
    }

    // departments relation
    public function department() {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }
}
