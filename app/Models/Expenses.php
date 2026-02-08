<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;
    // table
    protected $table = 'expenses';
    protected $fillable = [
        'amount',
        'type',
        'payment_account',
        'date',
        'description',
        'depID',
        'salesman_id',
    ];

    // relation with department
    public function department() {
        return $this->belongsTo(Departments::class, 'depID');
    }
    // relation with salesman
    public function salesman() {
        return $this->belongsTo(Salesman::class, 'salesman_id');
    }
    
}
