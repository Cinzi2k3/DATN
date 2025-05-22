<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'transaction_id',
        'user_id',
        'ticket_type',
        'contact_name',
        'contact_email',
        'contact_phone',
        'total_amount',
        'status',
        'checkin',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }
}