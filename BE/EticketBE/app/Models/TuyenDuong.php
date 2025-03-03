<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TuyenDuong extends Model
{
    use HasFactory;
    protected $primarykey = 'matuyenduong';
    protected $table = 'tuyenduong';
    protected $fillable = [
        'matuyenduong',
        'magadi',
        'magaden',
        'khoangcach',
    ];
}
