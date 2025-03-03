<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaiToa extends Model
{
    use HasFactory;
    protected $primarykey = 'maloaitoa';
    protected $table = 'loaitoa';
    protected $fillable = [
        'maloaitoa',
        'tenloaitoa',
    ];
}
