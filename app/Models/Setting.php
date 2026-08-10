<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'company_address',
        'phone1',
        'phone2',
        'zaad',
        'edahab',
    ];

    public static function getSettings()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'company_name'    => 'WARAABE FUEL STATIONS',
                'company_address' => 'Kaalinta Shiidaalka Waraabe • Berbera Somaliland',
                'phone1'          => '+252 63 7044460',
                'phone2'          => '+252 63 4445566',
                'zaad'            => '51234',
                'edahab'          => '61234',
            ]
        );
    }
}
