<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capital extends Model
{
    use HasFactory;
    protected $table = 'capital';
    protected $fillable = [
        'owner_name',
        'capital_amount',
    ];
}
