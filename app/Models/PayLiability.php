<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayLiability extends Model
{
    use HasFactory;
    protected $fillable = [
        'received_from',
        'amount',
        'type',
        'account',
        'date',
        'description',
        'depID',
    ];

    // department relationship
    public function department() {
        return $this->belongsTo(Departments::class, 'depID');
    }
}
