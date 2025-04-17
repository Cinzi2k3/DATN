<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'transaction_id',
        'ticket_type',
        'contact_name',
        'contact_email',
        'contact_phone',
        'total_amount',
        'status',
    ];

    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }
}