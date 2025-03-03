<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaiVe extends Model
{
    use HasFactory;
    protected $primarykey = 'maloaive';
    protected $table = 'loaive';
    protected $fillable = [
        'maloaive',
        'tenloaive',
    ];
}
