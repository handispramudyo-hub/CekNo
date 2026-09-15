<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'country_code',
        'normalized_number',
        'risk_score',
        'risk_level',
        'total_reports',
        'total_reviews',
        'total_tags',
        'search_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'risk_score' => 'integer',
            'total_reports' => 'integer',
            'total_reviews' => 'integer',
            'total_tags' => 'integer',
            'search_count' => 'integer',
        ];
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function phoneTags()
    {
        return $this->hasMany(PhoneTag::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'phone_tags')
            ->wherePivot('status', 'approved');
    }

    public function riskAssessments()
    {
        return $this->hasMany(RiskAssessment::class);
    }

    public function mlPredictions()
    {
        return $this->hasMany(MlPrediction::class);
    }

    public function searchHistories()
    {
        return $this->hasMany(SearchHistory::class);
    }

    public function latestRiskAssessment()
    {
        return $this->hasOne(RiskAssessment::class)->latestOfMany();
    }
}