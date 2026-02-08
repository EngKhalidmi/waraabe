<?php

namespace App\Models;

use App\Models\Departments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesmanPayment extends Model
{
    use HasFactory;
    protected $table = 'salesman_payment';
    protected $fillable = [
        'salesman_id',
        'pbalance',
        'current',
        'discount',
        'paid_amount',
        'date',
        'payment_method',
        'depID',
    ];

    // Customer method
    public function salesman() {
        return $this->belongsTo(Salesman::class, 'salesman_id', 'id');
    }
    // departments relation
    public function department() {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }

}
