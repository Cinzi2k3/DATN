<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NguoiDung extends Model
{
    use HasFactory;
    protected $primarykey = 'manguoidung';
    protected $table = 'nguoidung';
    protected $fillable = [
        'manguoidung',
        'ten',
        'email',
        'matkhau',
        'sdt',
    ];
}
