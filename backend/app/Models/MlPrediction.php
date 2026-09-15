<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MlPrediction extends Model
{
    use HasFactory;

    public const MODEL_TYPES = ['xgboost', 'indobert'];

    protected $fillable = [
        'phone_number_id',
        'review_id',
        'model_type',
        'model_version',
        'input_features',
        'prediction',
        'confidence',
    ];

    protected function casts(): array
    {
        return [
            'input_features' => 'array',
            'prediction' => 'array',
            'confidence' => 'float',
        ];
    }

    public function phoneNumber()
    {
        return $this->belongsTo(PhoneNumber::class);
    }

    public function review()
    {
        return $this->belongsTo(Review::class);
    }
}