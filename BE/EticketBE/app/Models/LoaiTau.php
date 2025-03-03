<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaiTau extends Model
{
    use HasFactory;
    protected $primarykey = 'maloaitau';
    protected $table = 'loaitau';
    protected $fillable = [
        'maloaitau',
        'tenloai',
    ];
}
