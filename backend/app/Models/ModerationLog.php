<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModerationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'target_type',
        'target_id',
        'action',
        'reason',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}