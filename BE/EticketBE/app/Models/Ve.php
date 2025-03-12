<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ve extends Model
{
    use HasFactory;

    protected $primaryKey = 'mave';
    protected $table = 've';
    protected $fillable = [
        'mave',
        'maloaive',
        'malichtrinh',
        'manguoidung',
        'macho',
        'thoihan',
        'trangthai'
    ];

    // Quan hệ với bảng LoaiVe
    public function LoaiVe()
    {
        return $this->belongsTo(LoaiVe::class, 'maloaive');
    }

    // Quan hệ với bảng LichTrinh
    public function LichTrinh()
    {
        return $this->belongsTo(LichTrinh::class, 'malichtrinh');
    }

    // Quan hệ với bảng NguoiDung
    public function NguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'manguoidung');
    }

    // Quan hệ với bảng Cho
    public function Cho()
    {
        return $this->belongsTo(Cho::class, 'macho');
    }
}
