<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'sku_code',
        'name',
        'quantity',
        'actual_price',
        'selling_price',
        'type',
        'unit',
        'status',
        'info',
        'supplier',
        'depID',
    ];

    // department relationship
    public function department() {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }
}
