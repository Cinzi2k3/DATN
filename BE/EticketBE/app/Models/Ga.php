<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ga extends Model
{
    use HasFactory;

    protected $primaryKey = 'maga';
    protected $table = 'ga';

    protected $fillable = [
        'maga',
        'tenga',
        'diachi',
    ];

    // Quan hệ: Ga là ga đi trong tuyến đường
    public function TuyenDuongDi()
    {
        return $this->hasMany(TuyenDuong::class, 'gadi', 'maga');
    }

    // Quan hệ: Ga là ga đến trong tuyến đường
    public function TuyenDuongDen()
    {
        return $this->hasMany(TuyenDuong::class, 'gaden', 'maga');
    }
}
