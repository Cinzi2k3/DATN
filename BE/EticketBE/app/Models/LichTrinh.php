<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichTrinh extends Model
{
    use HasFactory;
    protected $primaryKey = 'malichtrinh';
    protected $table = 'lichtrinh';
    protected $fillable = [
        'malichtrinh',
        'matau',
        'matuyenduong',
        'thoigiandi',
        'thoigianden',
    ];
public function Tau()
{
    return $this->belongsTo(Tau::class, 'matau');
}
public function TuyenDuong()
{
    return $this->belongsTo(TuyenDuong::class, 'matuyenduong');
}
public function Gia()
{
    return $this->hasOne(Gia::class, 'malichtrinh');
}
public function DatVe()
{
    return $this->hasOne(DatVe::class, 'malichtrinh');
}

}
