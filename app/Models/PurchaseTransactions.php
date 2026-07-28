<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransactions extends Model
{
    use HasFactory;

    protected $table = 'returned_credits';

    protected $fillable = [
        'customerID',   // supplier id
        'subTotal',
        'discount',
        'net_price',
        'add_cost',
        'paidAmount',
        'balance',
        'date',
        'payMethod',
        'reference',
        'type',
        'depID',
        'purchased',    // user id
    ];

    /* =========================
       SUPPLIER (IMPORTANT FIX)
    ========================== */
    public function supplier()
    {
        return $this->belongsTo(Suppliers::class, 'customerID', 'id');
    }

    /* =========================
       PURCHASED BY (USER)
    ========================== */
    public function purchasedByUser()
    {
        return $this->belongsTo(User::class, 'purchased', 'id');
    }

    /* =========================
       DEPARTMENT
    ========================== */
    public function department()
    {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }

    /* =========================
       PURCHASE ITEMS (if needed)
    ========================== */
    public function purchases()
    {
        return $this->hasMany(Purchases::class, 'transID', 'id');
    }
}
