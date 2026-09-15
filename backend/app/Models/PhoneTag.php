<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number_id',
        'tag_id',
        'user_id',
        'status',
    ];

    public function phoneNumber()
    {
        return $this->belongsTo(PhoneNumber::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}