<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhToan extends Model
{
    use HasFactory;
    protected $primaryKey = 'mathanhtoan';
    protected $table = 'thanhtoan';
    protected $fillable = [
        'mathanhtoan',
        'mave',
        'manguoidung',
        'sotien',
        'phuongthuc',
        'created_at'
    ];
    public function Ve(){
        return $this -> belongsTo(Ve::class, 'mave');
    }
    public function NguoiDung(){
        return $this -> belongsTo(NguoiDung::class,'manguoidung');
    }
}
