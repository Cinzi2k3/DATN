<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class TicketCategoryController extends Controller
{
    /**
     * Lấy tất cả dữ liệu từ bảng ticket_categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $categories = TicketCategory::all()->map(function ($category) {
                return [
                    'id' => $category->id,
                    'label' => $category->label,
                    'description' => $category->description,
                    'discount' => $category->discount,
                    'count' => 0, // Thêm count mặc định cho frontend
                ];
            });
            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể lấy danh sách loại vé'], 500);
        }
    }

    /**
     * Thêm một loại vé mới vào bảng ticket_categories
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
   public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'label' => 'required|string|max:100|unique:ticket_categories,label',
        'description' => 'required|string|max:255',
        'discount' => 'nullable|numeric|min:0|max:100',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    try {
        $category = TicketCategory::create([
            'label' => $request->label,
            'description' => $request->description,
            'discount' => $request->discount,
        ]);

        return response()->json([
            'id' => $category->id,
            'label' => $category->label,
            'description' => $category->description,
            'discount' => $category->discount,
            'count' => 0,
        ], 201);
    } catch (\Exception $e) {
        \Log::error('Lỗi khi thêm loại vé: ' . $e->getMessage(), [
            'request' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Không thể thêm loại vé', 'message' => $e->getMessage()], 500);
    }
}

    /**
     * Cập nhật thông tin loại vé trong bảng ticket_categories
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $category = TicketCategory::find($id);
        if (!$category) {
            return response()->json(['error' => 'Không tìm thấy loại vé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:100|unique:ticket_categories,label,' . $id . ',id',
            'description' => 'required|string|max:255',
            'discount' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $category->update([
                'label' => $request->label,
                'description' => $request->description,
                'discount' => $request->discount,
            ]);

            return response()->json([
                'id' => $category->id,
                'label' => $category->label,
                'description' => $category->description,
                'discount' => $category->discount,
                'count' => 0, // Thêm count mặc định
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể cập nhật loại vé'], 500);
        }
    }

    /**
     * Xóa một loại vé khỏi bảng ticket_categories
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $category = TicketCategory::find($id);
        if (!$category) {
            return response()->json(['error' => 'Không tìm thấy loại vé'], 404);
        }

        try {
            $category->delete();
            return response()->json(['message' => 'Xóa loại vé thành công']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể xóa loại vé'], 500);
        }
    }
}