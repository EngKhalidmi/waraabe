<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceTrans extends Model
{
    use HasFactory;

    protected $table ='accounting_transaction';

    // Adding Table Coloumns
   protected $fillable = [ 'depitAcc', 'depitAmount', 'creditAcc', 'creditAmount', 'date', 'formType', 'user', 'info', 'action', 'depID', ];

    // Adding Relationships
    public function users()
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    // departments relation
    public function departments()
    {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }
}
