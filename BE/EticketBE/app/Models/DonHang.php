<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    use HasFactory;
    protected $primaryKey = 'madonhang';
    protected $table = 'donhang';
    protected $fillable = [
        'madonhang',
        'manguoidung',
        'ngaydat',
        'tongtien',
        'trangthai',
    ];
    public function NguoiDung(){
        return $this -> belongsTo(NguoiDung::class,'manguoidung');
    }
    public function Ve(){
        return $this -> belongsTo(Ve::class,'mave');
    }

}
