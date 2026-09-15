<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'spam',
        'fraud',
        'phishing',
        'telemarketing',
        'loan',
        'harassment',
        'other',
    ];

    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $fillable = [
        'phone_number_id',
        'user_id',
        'category_id',
        'category',
        'description',
        'evidence',
        'evidence_type',
        'description_hash',
        'status',
        'moderated_by',
        'moderated_at',
    ];

    protected function casts(): array
    {
        return [
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

    public function categoryModel()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}