<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class LandExpert extends Model
{
    use HasFactory;

       // Specify the MongoDB connection name if necessary
       protected $connection = 'mongodb';

       // Specify the collection name if it's not the plural of the model name
       protected $collection = 'land_experts'; // Optional, usually not required if the collection is named "users"

       protected $fillable = [
        'certification_id',
        'license_number',
        //'pricing',
        'user_id', // Assuming a foreign key to relate back to the User model
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
