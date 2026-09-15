<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number_id',
        'guest_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function phoneNumber()
    {
        return $this->belongsTo(PhoneNumber::class);
    }
}