<?php

namespace App\Models;

use App\Models\Departments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelSalePayment extends Model
{
    use HasFactory;
    protected $table = 'fuel_sale_payment';
    protected $fillable = [
        'fuel_sale_id',
        'depID',
        'zaad_dollar',
        'zaad_slsh',
        'edahab_dollar',
        'edahab_slsh',
        'cash_dollar',
        'cash_slsh',
        'merchant_dollar',
        'merchant_slsh',
        'payment_rate'
    ];

    public function fuelSale()
    {
        return $this->belongsTo(FuelSale::class);
    }
    public function department()
    {
        return $this->belongsTo(Departments::class, 'depID');
    }
}