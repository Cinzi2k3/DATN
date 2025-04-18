<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'vnp_txn_ref' => 'required|string|exists:orders,transaction_id'
        ]);

        // Lấy order với các quan hệ cần thiết
        $order = Order::where('transaction_id', $request->vnp_txn_ref)
            ->with(['details.passenger'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin vé!',
            ], 404);
        }

        // Kiểm tra trạng thái vé
        if ($order->checkin === 'used') {
            return response()->json([
                'success' => false,
                'message' => 'Vé này đã được sử dụng!',
                'data' => $this->formatOrderData($order)
            ], 400);
        }

        // Cập nhật trạng thái thành đã sử dụng
        $order->update(['checkin' => 'used']);

        return response()->json([
            'success' => true,
            'message' => 'Check-in thành công!',
            'data' => $this->formatOrderData($order)
        ]);
    }

    /**
     * Định dạng dữ liệu order để hiển thị
     */
    private function formatOrderData(Order $order): array
    {
        $formattedDetails = [
            'departure' => [],
            'return' => []
        ];

        // Phân loại vé theo chiều đi/về
        foreach ($order->details as $detail) {
            $ticketData = [
                'arrivalTime' => $detail->arrival_time,
                'departureTime' => $detail->departure_time,
                'schedule_route' => $detail->schedule_route,
                'train_code' => $detail->train_code,
                'train_type' => $detail->train_type,
                'car_name' => $detail->car_name,
                'seat_number' => $detail->seat_number,
                'price' => $detail->price,
                'ticket_type' => $detail->ticket_type,
                'passenger' => [
                    'name' => $detail->passenger->name,
                    'birthdate' => $detail->passenger->birthdate,
                    'cccd' => $detail->passenger->cccd
                ],
                'trip_type' => $detail->trip_type
            ];

            if ($detail->trip_type === 'departure') {
                $formattedDetails['departure'][] = $ticketData;
            } else {
                $formattedDetails['return'][] = $ticketData;
            }
        }

        // Tạo dữ liệu trả về
        return [
            'id' => $order->id,
            'transaction_id' => $order->transaction_id,
            'contact_name' => $order->contact_name,
            'contact_email' => $order->contact_email,
            'contact_phone' => $order->contact_phone,
            'ticket_type' => $order->ticket_type,
            'status' => $order->status,
            'checkin' => $order->checkin,
            'total_amount' => $order->total_amount,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'details' => $formattedDetails
        ];
    }
}