<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consent_version',
        'scope',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}