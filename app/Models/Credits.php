<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credits extends Model
{
    use HasFactory;
    protected $table = 'credits';
    protected $fillable = [
        'customerID',
        'amount',
        'pbalance',
        'current',
        'discount',
        'type',
        'date',
        'payment_method',
        'depID',
        'seller'
    ];

    // Customer method
    public function customer() {
        return $this->belongsTo(Customers::class, 'customerID', 'id');
    }

    // department Relation
    public function department() {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }

public function sellerUser()
{
    return $this->belongsTo(User::class, 'seller');
}

}
