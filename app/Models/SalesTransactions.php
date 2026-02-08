<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTransactions extends Model
{
    protected $table = 'sales_transactions';
    protected $fillable = [
        'customerID',
        'sub_total',
        'discount',
        'net_price',
        'paid_amount',
        'balance',
        'paid_date',
        'payment_method',
        'type',
        'depID',
        'note',
        'seller',
    ];

    // users relation
    public function sellerUser() {
        return $this->belongsTo(User::class, 'seller');
    }
    public function sales()
    {
        return $this->hasMany(Sales::class, 'sales_transaction_id', 'id');
    }

    public function customer() {
        return $this->belongsTo(Customers::class, 'customerID', 'id');
    }

// departments relation
    public function department() {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }
  
}

