<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $orders = Order::where('user_id', $request->user_id)
                ->with(['details.passenger'])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'transaction_id' => $order->transaction_id,
                        'contact_name' => $order->contact_name,
                        'contact_email' => $order->contact_email,
                        'contact_phone' => $order->contact_phone,
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                        'checkin' => $order->checkin,
                        'ticket_type' => $order->ticket_type,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'qr_code' => env('FRONTEND_URL', 'http://192.168.0.105:5173') . '/check-in?vnp_txn_ref=' . $order->transaction_id, // Thêm URL mã QR
                        'order_details' => $order->details->map(function ($detail) {
                            return [
                                'trip_type' => $detail->trip_type,
                                'ticket_type' => $detail->ticket_type,
                                'schedule_route' => $detail->schedule_route,
                                'departure_time' => $detail->departure_time instanceof \Carbon\Carbon
                                    ? $detail->departure_time->format('Y-m-d H:i')
                                    : $detail->departure_time,
                                'arrival_time' => $detail->arrival_time instanceof \Carbon\Carbon
                                    ? $detail->arrival_time->format('Y-m-d H:i')
                                    : $detail->arrival_time,
                                'train_code' => $detail->train_code,
                                'train_type' => $detail->train_type,
                                'car_name' => $detail->car_name ?? 'Không xác định',
                                'seat_number' => $detail->seat_number,
                                'price' => $detail->price,
                                'passenger' => $detail->passenger ? [
                                    'name' => $detail->passenger->name,
                                    'birthdate' => $detail->passenger->birthdate,
                                    'cccd' => $detail->passenger->cccd,
                                ] : null,
                            ];
                        })->groupBy('trip_type')->mapWithKeys(function ($group, $key) {
                            return [$key === 'departure' ? 'departure' : 'return' => $group];
                        })->toArray(),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách đơn hàng: ' . $e->getMessage(), ['user_id' => $request->user_id]);
            return response()->json([
                'success' => false,
                'message' => 'Lấy danh sách đơn hàng thất bại',
            ], 500);
        }
    }

    public function show($txnRef)
    {
        try {
            $order = Order::where('transaction_id', $txnRef)
                ->with('details.passenger')
                ->firstOrFail();

            return response()->json([
                'bookingData' => [
                    'id' => $order->id,
                    'transaction_id' => $order->transaction_id,
                    'contact_name' => $order->contact_name,
                    'contact_email' => $order->contact_email,
                    'contact_phone' => $order->contact_phone,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'checkin' => $order->checkin,
                    'ticket_type' => $order->ticket_type,
                    'qr_code' => env('FRONTEND_URL', 'http://192.168.0.105:5173') . '/check-in?vnp_txn_ref=' . $order->transaction_id, // Thêm URL mã QR
                    'details' => $order->details->map(function ($detail) {
                        return [
                            'trip_type' => $detail->trip_type,
                            'ticket_type' => $detail->ticket_type,
                            'schedule_route' => $detail->schedule_route,
                            'departureTime' => $detail->departure_time instanceof \Carbon\Carbon
                                ? $detail->departure_time->format('Y-m-d H:i')
                                : $detail->departure_time,
                            'arrivalTime' => $detail->arrival_time instanceof \Carbon\Carbon
                                ? $detail->arrival_time->format('Y-m-d H:i')
                                : $detail->arrival_time,
                            'train_code' => $detail->train_code,
                            'train_type' => $detail->train_type,
                            'car_name' => $detail->car_name ?? 'Không xác định',
                            'seat_number' => $detail->seat_number,
                            'price' => $detail->price,
                            'passenger' => $detail->passenger ? [
                                'name' => $detail->passenger->name,
                                'birthdate' => $detail->passenger->birthdate,
                                'cccd' => $detail->passenger->cccd,
                            ] : null,
                        ];
                    })->groupBy('trip_type')->mapWithKeys(function ($group, $key) {
                        return [$key === 'departure' ? 'departure' : 'return' => $group];
                    })->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy đơn hàng: ' . $e->getMessage(), ['txnRef' => $txnRef]);
            return response()->json(['error' => 'Không tìm thấy đơn hàng'], 404);
        }
    }
    public function admin(Request $request)
{
    try {
        $orders = Order::with(['details.passenger'])->latest()->get();
        return response()->json([
            'success' => true,
            'orders' => $orders->map(function ($order) {
                // Giữ nguyên logic ánh xạ dữ liệu
                return [
                    'id' => $order->id,
                    'transaction_id' => $order->transaction_id,
                    'contact_name' => $order->contact_name,
                    'contact_email' => $order->contact_email,
                    'contact_phone' => $order->contact_phone,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'checkin' => $order->checkin,
                    'ticket_type' => $order->ticket_type,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'qr_code' => env('FRONTEND_URL', 'http://192.168.0.105:5173') . '/check-in?vnp_txn_ref=' . $order->transaction_id,
                    'order_details' => $order->details->map(function ($detail) {
                        return [
                            'trip_type' => $detail->trip_type,
                            'ticket_type' => $detail->ticket_type,
                            'schedule_route' => $detail->schedule_route,
                            'departure_time' => $detail->departure_time instanceof \Carbon\Carbon
                                ? $detail->departure_time->format('Y-m-d H:i')
                                : $detail->departure_time,
                            'arrival_time' => $detail->arrival_time instanceof \Carbon\Carbon
                                ? $detail->arrival_time->format('Y-m-d H:i')
                                : $detail->arrival_time,
                            'train_code' => $detail->train_code,
                            'train_type' => $detail->train_type,
                            'car_name' => $detail->car_name ?? 'Không xác định',
                            'seat_number' => $detail->seat_number,
                            'price' => $detail->price,
                            'passenger' => $detail->passenger ? [
                                'name' => $detail->passenger->name,
                                'birthdate' => $detail->passenger->birthdate,
                                'cccd' => $detail->passenger->cccd,
                            ] : null,
                        ];
                    })->groupBy('trip_type')->mapWithKeys(function ($group, $key) {
                        return [$key === 'departure' ? 'departure' : 'return' => $group];
                    })->toArray(),
                ];
            }),
        ]);
    } catch (\Exception $e) {
        Log::error('Lỗi khi lấy danh sách đơn hàng: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lấy danh sách đơn hàng thất bại',
        ], 500);
    }
}

}