<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MlModel extends Model
{
    use HasFactory;

    public const STATUSES = ['experiment', 'active', 'retired'];

    protected $fillable = [
        'algorithm',
        'model_version',
        'dataset_version',
        'training_date',
        'metrics',
        'status',
        'model_path',
        'storage_size_kb',
    ];

    protected function casts(): array
    {
        return [
            'training_date' => 'datetime',
            'metrics' => 'array',
            'storage_size_kb' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}