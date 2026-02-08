<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $fillable = [
        'sales_id',
        'sales_transaction_id',
        'quantity',
        'reason',
        'proID',
        'refund_amount',
        'return_date',
    ];

    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    public function transaction()
    {
        return $this->belongsTo(SalesTransactions::class, 'sales_transaction_id');
    }
}
