<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $table = 'subcategory';

    protected $fillable = [
        'name',
        'category_id',
        'depID',
    ];

    public function department()
    {
        return $this->belongsTo(Departments::class, 'depID', 'id');
    }
}
