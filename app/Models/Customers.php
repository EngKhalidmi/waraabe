<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    use HasFactory;
    protected $table ='customers';
    protected $fillable = [
        'customer_name',
        'phone',
        'address',
        'serial', 
        'balance', 
        'depID',
        'sex',
        'age',
        'birthDate',
        'description'
    ];

    // departments relationship
    public function department() {
        return $this->belongsTo(Departments::class, 'depID');
    }
    
    public function patientAssessments()
    {
        return $this->hasMany(PatientAssessment::class, 'ptID');
    }
    
    public function labs() {
        return $this->hasMany(Labs::class, 'pdID', 'id'); // Adjust keys as needed
    }

}
