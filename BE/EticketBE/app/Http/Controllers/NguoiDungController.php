<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Validator;

class NguoiDungController extends Controller
{
    // Lấy danh sách người dùng
    public function index()
    {
        $nguoiDungs = NguoiDung::with('Ve')->get(); // Lấy tất cả người dùng và các vé liên quan

        return response()->json([
            'success' => true,
            'data' => $nguoiDungs
        ], 200);
    }

    // Lấy thông tin người dùng theo ID
    public function show($id)
    {
        $nguoiDung = NguoiDung::with('Ve')->find($id);

        if (!$nguoiDung) {
            return response()->json(['message' => 'Người dùng không tồn tại!'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $nguoiDung
        ], 200);
    }

    // Thêm mới người dùng
    public function store(Request $request)
    {
        // Validate dữ liệu nhập vào
        $validator = Validator::make($request->all(), [
            'userid' => 'required|unique:nguoidung,userid',
            'diachi' => 'required|string',
            'ngaysinh' => 'required|date',
            'sdt' => 'required|digits:10',
            'gioitinh' => 'required|in:Nam,Nữ',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Tạo mới người dùng
        $nguoiDung = NguoiDung::create([
            'userid' => $request->userid,
            'diachi' => $request->diachi,
            'ngaysinh' => $request->ngaysinh,
            'sdt' => $request->sdt,
            'gioitinh' => $request->gioitinh,
        ]);

        return response()->json([
            'success' => true,
            'data' => $nguoiDung
        ], 201);
    }

    // Cập nhật thông tin người dùng
    public function update(Request $request, $id)
    {
        $nguoiDung = NguoiDung::find($id);

        if (!$nguoiDung) {
            return response()->json(['message' => 'Người dùng không tồn tại!'], 404);
        }

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'userid' => 'sometimes|required|unique:nguoidung,userid,' . $id,
            'diachi' => 'sometimes|required|string',
            'ngaysinh' => 'sometimes|required|date',
            'sdt' => 'sometimes|required|digits:10',
            'gioitinh' => 'sometimes|required|in:Nam,Nữ',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cập nhật người dùng
        $nguoiDung->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $nguoiDung
        ], 200);
    }

    // Xóa người dùng
    public function destroy($id)
    {
        $nguoiDung = NguoiDung::find($id);

        if (!$nguoiDung) {
            return response()->json(['message' => 'Người dùng không tồn tại!'], 404);
        }

        $nguoiDung->delete();

        return response()->json(['message' => 'Người dùng đã được xóa!'], 200);
    }
}
