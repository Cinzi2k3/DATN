<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'user_id',
        'phone_number',
        'address',
        'birth_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}