<?php

namespace App\Models;

use App\Models\Departments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OpeningInventory extends Model
{
    protected $table = 'opening_inventory';
    protected $fillable = [
        'product_id',
        'depID',
        'opening_quantity',
        'opening_date',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'depID');
    }
}