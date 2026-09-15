<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $fillable = [
        'phone_number_id',
        'user_id',
        'rating',
        'comment',
        'sentiment',
        'fraud_probability',
        'category',
        'status',
        'moderated_by',
        'moderated_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'fraud_probability' => 'float',
            'moderated_at' => 'datetime',
        ];
    }

    public function phoneNumber()
    {
        return $this->belongsTo(PhoneNumber::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function mlPredictions()
    {
        return $this->hasMany(MlPrediction::class);
    }
}