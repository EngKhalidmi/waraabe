<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assets extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type',
        'amount',
        'description',
        'depID',
        
    ];

    // relationships with department
    public function department() {
        return $this->belongsTo(Departments::class, 'depID');

    }
}
