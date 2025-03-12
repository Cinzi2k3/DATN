<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LoaiTau;
use Illuminate\Http\Request;

class LoaiTauController extends Controller
{
    /**
     * Lấy danh sách tất cả Loại Tàu.
     */
    public function index()
    {
        $loaiTaus = LoaiTau::all();
        return response()->json($loaiTaus, 200);
    }

    /**
     * Tạo mới một Loại Tàu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenloaitau' => 'required|string|max:255',
        ]);

        $loaiTau = LoaiTau::create([
            'tenloaitau' => $validated['tenloaitau'],
        ]);

        return response()->json([
            'message' => 'Tạo mới Loại Tàu thành công!',
            'data' => $loaiTau,
        ], 201);
    }

    /**
     * Lấy thông tin chi tiết một Loại Tàu theo ID.
     */
    public function show($id)
    {
        $loaiTau = LoaiTau::find($id);

        if (!$loaiTau) {
            return response()->json(['message' => 'Không tìm thấy Loại Tàu!'], 404);
        }

        return response()->json($loaiTau, 200);
    }

    /**
     * Cập nhật thông tin Loại Tàu.
     */
    public function update(Request $request, $id)
    {
        $loaiTau = LoaiTau::find($id);

        if (!$loaiTau) {
            return response()->json(['message' => 'Không tìm thấy Loại Tàu!'], 404);
        }

        $validated = $request->validate([
            'tenloaitau' => 'required|string|max:255',
        ]);

        $loaiTau->update([
            'tenloaitau' => $validated['tenloaitau'],
        ]);

        return response()->json([
            'message' => 'Cập nhật Loại Tàu thành công!',
            'data' => $loaiTau,
        ], 200);
    }

    /**
     * Xóa một Loại Tàu.
     */
    public function destroy($id)
    {
        $loaiTau = LoaiTau::find($id);

        if (!$loaiTau) {
            return response()->json(['message' => 'Không tìm thấy Loại Tàu!'], 404);
        }

        $loaiTau->delete();

        return response()->json(['message' => 'Xóa Loại Tàu thành công!'], 200);
    }
}
