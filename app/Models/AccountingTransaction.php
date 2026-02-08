<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingTransaction extends Model
{
    use HasFactory;
    protected $table = 'accounting_transaction';
    protected $fillable = [
        'date',
        'account',
        'debit',
        'credit',
        'depID',
    ];
}
