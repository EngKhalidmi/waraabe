<?php
namespace App\Models;


use App\Models\Departments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Salesman extends Model
{
    use HasFactory;
    protected $table ='salesman';
    protected $fillable = [
        'full_name',
        'phone',
        'balance', 
        'type', 
        'sex',
        'age',
        'depID',
    ];
    // department relationship
    public function department() {
        return $this->belongsTo(Departments::class, 'depID');
        }

}
