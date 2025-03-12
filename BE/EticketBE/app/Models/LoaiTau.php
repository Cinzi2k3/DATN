<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaiTau extends Model
{
    use HasFactory;
    protected $primaryKey = 'maloaitau';
    protected $table = 'loaitau';
    protected $fillable = [
        'maloaitau',
        'tenloaitau',
    ];
    public function Tau(){
        return $this ->hasMany(Tau::class,'maloaitau');
    }
}
