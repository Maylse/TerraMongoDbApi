<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ConsultationLog extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'consultation_logs';
    protected $fillable = [
        'consultation_request_id',
        'user_id',
        'status',
        'response_message',
        'message',       // Added fields
        'date',
        'time',
        'location',
        'rate'
    ];

    public function consultationRequest()
    {
        return $this->belongsTo(ConsultationRequest::class, 'consultation_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
