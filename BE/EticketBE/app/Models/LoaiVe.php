<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaiVe extends Model
{
    use HasFactory;
    protected $primaryKey = 'maloaive';
    protected $table = 'loaive';
    protected $fillable = [
        'maloaive',
        'tenloaive',
        'hesogia'
    ];
    public function Gia()
{
    return $this->hasMany(Gia::class, 'maloaive');
}
public function Ve()
{
    return $this->hasMany(Ve::class, 'maloaive');
}

}
