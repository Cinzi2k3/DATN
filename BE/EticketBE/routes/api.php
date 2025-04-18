<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoaiToaController;
use App\Http\Controllers\LoaiChoController;
use App\Http\Controllers\LoaiTauController;
use App\Http\Controllers\ToaController;
use App\Http\Controllers\TauController;
use App\Http\Controllers\ChoController;
use App\Http\Controllers\GaController;
use App\Http\Controllers\TuyenDuongController;
use App\Http\Controllers\LoaiVeController;
use App\Http\Controllers\LichTrinhController;
use App\Http\Controllers\PhanLoaiController;
use App\Http\Controllers\VeController;
use App\Http\Controllers\NguoiDungController;
use App\Http\Controllers\GiaController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\ThanhToanController;
use App\Http\Controllers\DatVeController;
use App\Http\Controllers\VNPayController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CheckInController;

//Đăng nhập, đăng kí
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

//Thông tin người dùng
Route::get('/user', [UserController::class, 'getUserByEmail']);

//Loại toa
Route::apiResource('loaitoa', LoaiToaController::class);

//Toa
Route::get('/toa', [ToaController::class, 'index']); // Route để lấy danh sách toa
Route::get('/chotoa', [ToaController::class, 'chotoa']);


//Loại Chỗ
Route::apiResource('loaicho', LoaiChoController::class);

//Loại tàu
Route::apiResource('loaitau', LoaiTauController::class);

//Tàu
Route::apiResource('tau', TauController::class);

//Cho
Route::apiResource('cho', ChoController::class);

//Ga
Route::apiResource('ga', GaController::class);

//Tuyến đường
Route::apiResource('tuyenduong', TuyenDuongController::class);

//Loại vé
Route::apiResource('loaive', LoaiVeController::class);

//Lịch trình
Route::apiResource('lichtrinh', LichTrinhController::class);

//Phân loại
Route::apiResource('phanloai', PhanLoaiController::class);

//Vé
Route::apiResource('ve', VeController::class);

//Người dùng
Route::apiResource('nguoidung', NguoiDungController::class);

//Gía
Route::apiResource('gia', GiaController::class);

//Đơn hàng
Route::apiResource('donhang', DonHangController::class);

//Thanh toán
Route::apiResource('thanhtoan', ThanhToanController::class);

//Đặt vé
Route::get('/datve', [DatVeController::class, 'DatVe']);

//Vnpay
Route::post('/vnpay/create', [VNPayController::class, 'createPayment']);
Route::get('/vnpay/return', [VNPayController::class, 'returnPayment']);

//order
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{txnRef}', [OrderController::class, 'show']);

//checkin
Route::post('/check-in', [CheckInController::class, 'checkIn']);