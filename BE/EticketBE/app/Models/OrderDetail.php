<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id',
        'passenger_id',
        'trip_type',
        'ticket_type',
        'schedule_route',
        'departure_time',
        'arrival_time',
        'train_code',
        'train_type',
        'car-name',
        'seat_number',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
}