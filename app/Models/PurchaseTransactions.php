<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransactions extends Model
{
    protected $table = 'returned_credits';
    protected $fillable = [
        'customerID',
        'subTotal',
        'discount',
        'net_price',
        'add_cost',
        'paidAmount',
        'balance',
        'date',
        'payMethod',
        'reference',
        'type',
        'depID',
        'purchased',
    ];
    // user relationship
    public function user() {
        return $this->belongsTo(User::class, 'purchased', 'id'); // Assuming 'userID' is the foreign key in the 'users' table
    }
    public function purchase()
    {
        return $this->hasMany(Purchases::class, 'transID', 'id');
    }

    public function customer() {
        return $this->belongsTo(Suppliers::class, 'customerID', 'id');
    }

    // department relationship
    public function department() {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }
}


