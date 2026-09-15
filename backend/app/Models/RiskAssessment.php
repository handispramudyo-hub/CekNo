<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
    use HasFactory;

    public const LEVELS = ['low', 'caution', 'risky', 'high'];

    protected $fillable = [
        'phone_number_id',
        'model_version',
        'xgboost_probability',
        'indobert_probability',
        'community_score',
        'rule_score',
        'final_score',
        'risk_level',
        'explanation',
        'factors',
    ];

    protected function casts(): array
    {
        return [
            'xgboost_probability' => 'float',
            'indobert_probability' => 'float',
            'community_score' => 'float',
            'rule_score' => 'float',
            'final_score' => 'integer',
            'factors' => 'array',
        ];
    }

    public function phoneNumber()
    {
        return $this->belongsTo(PhoneNumber::class);
    }
}