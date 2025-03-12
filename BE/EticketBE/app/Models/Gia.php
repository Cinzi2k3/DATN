<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gia extends Model
{
    use HasFactory;
    protected $primaryKey = 'magia';
    protected $table = 'gia';
    protected $fillable = [
        'magia',
        'matoa',
        'malichtrinh',
        'maphanloai',
        'maloaive',
        'gia',
    ];
    public function Toa()
    {
        return $this->belongsTo(Toa::class, 'matoa');
    }
    public function LichTrinh()
    {
        return $this->belongsTo(LichTrinh::class, 'malichtrinh');
    }
    public function PhanLoai()
    {
        return $this->belongsTo(PhanLoai::class, 'maphanloai');
    }
    public function LoaiVe()
    {
        return $this->belongsTo(LoaiVe::class, 'maloaive');
    }
}
