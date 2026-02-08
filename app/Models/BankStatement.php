<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankStatement extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'type',
        'description',
        'depID',
        'check_no',
        'date',
    ];

    // department relationship
    public function department() {
        return $this->belongsTo(Departments::class, 'depID');
    }
}
