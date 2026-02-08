<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsLogs extends Model
{
    use HasFactory;
    protected $table ='analytics_logs';

    protected $fillable = [
        'user_id',
        'module_name',
        'action',
    ];


    // Currency
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
