<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'ticket_categories';
    public $timestamps = false; // Tắt tự động xử lý timestamps
    protected $keyType = 'int';
    protected $fillable = [ 'label', 'description', 'discount'];
}