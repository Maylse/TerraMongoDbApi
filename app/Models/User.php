<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; // Import the MongoDB model
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Model // Extend the MongoDB Model
{
    use HasFactory, Notifiable, HasApiTokens;

    // The MongoDB collection name (optional; default is plural of the model name)
    protected $connection = 'mongodb'; 
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'user_type', 
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Ensure passwords are hashed
    ];


     public function landExpert()
    {
        return $this->hasOne(LandExpert::class, 'user_id')->onDelete('cascade'); // Cascade delete
    }


    public function surveyor()
    {
        return $this->hasOne(Surveyor::class, 'user_id')->onDelete('cascade'); // Cascade delete
    }
    public function finder()
    {
        return $this->hasOne(Finder::class, 'user_id')->onDelete('cascade'); // Cascade delete
    }
}
