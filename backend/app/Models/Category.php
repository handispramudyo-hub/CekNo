<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'risk_weight',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'risk_weight' => 'integer',
        ];
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }
}