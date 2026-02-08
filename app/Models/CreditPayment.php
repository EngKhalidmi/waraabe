<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fuel_credit_sale_id',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'payment_date',
        'received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:10,2',
        'payment_date' => 'datetime',
    ];

    /**
     * Payment method constants
     */
    const METHOD_CASH = 'Zaad';
    const METHOD_BANK_TRANSFER = 'Edahab';
    const METHOD_CHEQUE = 'Cash On Hand';
    const METHOD_MOBILE_MONEY = 'Bank Account';

    /**
     * Get the payment method options.
     *
     * @return array
     */
    public static function getPaymentMethodOptions()
    {
        return [
            self::METHOD_CASH => 'Zaad',
            self::METHOD_BANK_TRANSFER => 'Edahab',
            self::METHOD_CHEQUE => 'Cash On Hand',
            self::METHOD_MOBILE_MONEY => 'Bank Account',
        ];
    }

    /**
     * Get the fuel credit sale that owns the payment.
     */
    public function fuelCreditSale()
    {
        return $this->belongsTo(FuelCreditSale::class);
    }

    /**
     * Get the user who received the payment.
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the formatted payment date.
     *
     * @return string
     */
    public function getFormattedPaymentDateAttribute()
    {
        return $this->payment_date->format('M d, Y h:i A');
    }

    /**
     * Get the formatted payment method.
     *
     * @return string
     */
    public function getFormattedPaymentMethodAttribute()
    {
        $methods = [
            self::METHOD_CASH => 'Zaad',
            self::METHOD_BANK_TRANSFER => 'Edahab',
            self::METHOD_CHEQUE => 'Cash On Hand',
            self::METHOD_MOBILE_MONEY => 'Bank Account',
        ];

        return $methods[$this->payment_method] ?? 'Unknown';
    }
}