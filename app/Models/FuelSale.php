<?php

namespace App\Models;

use App\Models\Departments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'shift',
        'salesman_id',
        'depID',
        'total_diesel_liters',
        'total_petrol_liters',
        'discount',
        'net_total',
        'cash_on_hand',
        'balance',
        'created_by',
    ];


    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'salesman_id');
    }
    
    public function transactions()
    {
        return $this->hasMany(FuelSaleTransaction::class);
    }
    
     public function creditSales()
    {
        return $this->hasMany(FuelCreditSale::class, 'fuel_sale_id');
    }
    
        public function payment()
{
    return $this->hasOne(FuelSalePayment::class);
}

public function department()
{
    return $this->belongsTo(Departments::class, 'depID');

    
}
}
