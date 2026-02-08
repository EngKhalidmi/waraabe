<?php

namespace App\Models;

use App\Models\Departments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelCreditPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_credit_sale_id',
        'depID',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'payment_date',
        'received_by',
    ];

    /**
     * Payment belongs to a credit sale.
     */
    public function creditSale()
    {
        return $this->belongsTo(Credits::class, 'fuel_credit_sale_id');
    }

    /**
     * Payment was received by a user.
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
    public function department()
    {
        return $this->belongsTo(Departments::class, 'depID');
    }
}
