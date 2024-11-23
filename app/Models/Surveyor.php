<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Surveyor extends Model
{
    use HasFactory;

   // Specify the MongoDB connection name if necessary
   protected $connection = 'mongodb';

   // Specify the collection name if it's not the plural of the model name
   protected $collection = 'surveyors'; // Optional, usually not required if the collection is named "users"

    protected $fillable = [
        'user_id', 
        'license_number',
        'certification_id', 
        //'pricing'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
