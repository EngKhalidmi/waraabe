<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BadProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'proID',
        'depID',
        'quantity',
        'reason',
        'reported_date', // Optional if used
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'proID', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }
}
