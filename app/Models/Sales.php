<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table = 'sales'; // Make sure the table name is correctly defined
    protected $fillable = [
        'proID',
        'quantity',
        'unit',
        'price',
        'total_price',
        'sales_transaction_id',
        'depID',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'proID', 'id');
    }
    
    public function transaction()
    {
        return $this->belongsTo(SalesTransactions::class, 'sales_transaction_id', 'id');
    }


    // department relationship
    public function department() {
        return $this->belongsTo(Departments::class, 'depID', 'id');  // Define the foreign key and the related model's primary key
    }


}