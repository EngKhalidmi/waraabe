<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountPayables extends Model
{
    use HasFactory;
    protected $fillable = [
        'received_from',
        'depID',
        'amount',
        'discount',
        'pbalance',
        'current',
        'type',
        'transaction_type',
        'account',
        'date',
        'user',
        'description',
       
    ];

    //  relation with Departments table
    public function department() {
        return $this->belongsTo(Departments::class, 'depID');  // assuming 'depID' is the foreign key in Departments table
    }

    // userRelation with Users table
    public function user() {
        return $this->belongsTo(Users::class, 'user');  // assuming 'user' is the foreign key in Users table
    }

    // suppliersRelation with Suppliers table
    public function supplier() {
        return $this->belongsTo(Suppliers::class, 'received_from');  // assuming'received_from' is the foreign key in Suppliers table
    }
}
