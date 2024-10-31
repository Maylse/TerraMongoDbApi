<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PasswordReset extends Model
{
    protected $connection = 'mongodb'; // Specify the MongoDB connection
    protected $collection = 'password_reset_tokens'; // Specify the MongoDB collection

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];
    public $timestamps = false;
}