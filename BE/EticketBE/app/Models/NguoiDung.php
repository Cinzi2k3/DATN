<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NguoiDung extends Model
{
    use HasFactory;
    protected $primaryKey = 'manguoidung';
    protected $table = 'nguoidung';
    protected $fillable = [
        'manguoidung',
        'userid',
        'diachi',
        'ngaysinh',
        'sdt',
        'gioitinh'
    ];
    public function Ve()
    {
        return $this->hasMany(Ve::class, 'manguoidung');
    }
}
