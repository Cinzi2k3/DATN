<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Passenger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VNPayController extends Controller
{
    public function createPayment(Request $request)
    {
        try {
            Log::info('VNPay createPayment request:', $request->all());

            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
                $vnp_Returnurl = "http://192.168.0.105:8000/api/vnpay/return";
                $vnp_TmnCode = "KHT30JML";
                $vnp_HashSecret = "YDKXVCGC7KL9KGJ8Y83JTORK8MWXCFSV";

            // Tạo mã đơn hàng
            $vnp_TxnRef = time() . "-" . Str::random(8);

            // Lưu thông tin đơn hàng
            $order = new Order();
            $order->user_id = $request->user_id;
            $order->transaction_id = $vnp_TxnRef;
            $order->contact_name = $request->contact['name'] ?? 'Unknown';
            $order->contact_email = $request->contact['email'] ?? 'Unknown';
            $order->contact_phone = $request->contact['phone'] ?? 'Unknown';
            $order->total_amount = $request->amount ?? 0;
            $order->status = 'pending';
            $order->ticket_type = ($request->return && !empty($request->return['searchgadi']) && !empty($request->return['searchgaden'])) ? 'round-trip' : 'one-way';
            $order->save();
            Log::info('Order saved:', ['order_id' => $order->id, 'transaction_id' => $order->transaction_id]);

            // Lưu thông tin hành khách
            $passengerIds = [];
            foreach ($request->passengers as $passenger) {
                $passengerRecord = new Passenger();
                $passengerRecord->order_id = $order->id;
                $passengerRecord->name = $passenger['name'] ?? 'Unknown';
                $passengerRecord->birthdate = $passenger['year'] . '-' . $passenger['month'] . '-' . $passenger['day'] ?? null;
                $passengerRecord->cccd = $passenger['idNumber'] ?? null;
                $passengerRecord->save();
                $passengerIds[] = $passengerRecord->id;
            }

            // Hàm lưu chi tiết vé 
            $saveOrderDetails = function ($tripType, $tripData, $orderId, $passengerIds, $passengers) {
                $passengerIndex = 0;
                foreach ($tripData['selectedSeatsByCar'] as $carName => $seatAssignments) {
                    foreach ($seatAssignments as $seat) {
                        if ($passengerIndex >= count($passengers)) break;

                        $passenger = $passengers[$passengerIndex];

                        // Lấy macho từ bảng cho
                        $seatInfo = DB::table('cho')
                            ->where('sohieu', $seat['sohieu'])
                            ->where('matoa', $tripData['matoa'])
                            ->first();

                        if (!$seatInfo) {
                            throw new \Exception("Ghế {$seat['sohieu']} không tồn tại trong toa {$tripData['matoa']}.");
                        }

                        $orderDetail = new OrderDetail();
                        $orderDetail->order_id = $orderId;
                        $orderDetail->passenger_id = $passengerIds[$passengerIndex];
                        $orderDetail->trip_type = $tripType;
                        $orderDetail->ticket_type = $passenger['ticket_type'] ?? ($seat['ticketType'] === 'Người lớn' ? 'adult' : 'child');
                        $orderDetail->schedule_route = ($tripData['searchgadi'] ?? 'Unknown') . ' - ' . ($tripData['searchgaden'] ?? 'Unknown');
                        $orderDetail->car_name = $carName;
                        $orderDetail->departure_time = Carbon::createFromFormat('Y-m-d H:i', $tripData['selectedDay'] . ' ' . $tripData['departureTime'])->toDateTimeString();
                        $orderDetail->arrival_time = Carbon::createFromFormat('Y-m-d H:i', $tripData['arrivalDate'] . ' ' . $tripData['arrivalTime'])->toDateTimeString();
                        $orderDetail->train_code = $tripData['trainCode'] ?? 'Unknown';
                        $orderDetail->train_type = $tripData['traintau'] ?? 'Unknown';
                        $orderDetail->seat_number = $seat['sohieu'] ?? 'Unknown';
                        $orderDetail->price = $seat['gia'] ?? ($request->amount / count($passengers));
                        $orderDetail->save();
                        Log::info("OrderDetail ($tripType) saved:", ['detail_id' => $orderDetail->id, 'car_name' => $carName]);

                        $passengerIndex++;
                    }
                }
            };

            // Lưu chi tiết vé đi
            $saveOrderDetails('departure', $request->departure, $order->id, $passengerIds, $request->passengers, $vnp_TxnRef);

            // Lưu chi tiết vé về (nếu có)
            if ($request->return && !empty($request->return['searchgadi']) && !empty($request->return['searchgaden'])) {
                $saveOrderDetails('return', $request->return, $order->id, $passengerIds, $request->passengers, $vnp_TxnRef);
            }

            // Tạo dữ liệu gửi đến VNPay
            $vnp_OrderInfo = "Thanh toán vé tàu";
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $request->amount * 100;
            $vnp_Locale = 'vn';
            $vnp_BankCode = $request->bankCode ?? '';
            $vnp_IpAddr = $request->ip();

            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef
            );

            if (!empty($vnp_BankCode)) {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            $returnData = array(
                'code' => '00',
                'message' => 'success',
                'data' => $vnp_Url
            );

            return response()->json($returnData);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo thanh toán VNPay: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'code' => '99',
                'message' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }
    private function mapCarNameToMatoa($carName)
    {
        $toaName = explode(':', $carName)[0]; // Lấy "Toa 2" từ "Toa 2 :Toa ghế ngồi"
        $toaName = trim($toaName); // Loại bỏ khoảng trắng thừa

        // Tìm matoa trong bảng toa dựa trên tentoa
        $toa = DB::table('toa')
            ->where('tentoa', $toaName)
            ->select('matoa')
            ->first();

        if ($toa) {
            return $toa->matoa;
        }

        Log::error('Cannot find matoa for car_name in toa table', [
            'car_name' => $carName,
            'toa_name' => $toaName
        ]);
        return null;
    }

    public function returnPayment(Request $request)
    {
        $vnp_HashSecret = "YDKXVCGC7KL9KGJ8Y83JTORK8MWXCFSV";
        $inputData = $request->all();

        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = "";
        $i = 0;

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            $order = Order::where('transaction_id', $inputData['vnp_TxnRef'])->first();

            if ($order) {
                if ($inputData['vnp_ResponseCode'] == '00') {
                    // Thanh toán thành công, cập nhật trạng thái ghế thành dadat
                    $orderDetails = OrderDetail::where('order_id', $order->id)->get();
                    foreach ($orderDetails as $detail) {
                        $matoa = $this->mapCarNameToMatoa($detail->car_name);
                        $seatInfo = DB::table('cho')
                            ->where('sohieu', $detail->seat_number)
                            ->where('matoa', $matoa)
                            ->first();

                        if ($seatInfo) {
                            DB::table('datve')
                                ->where('macho', $seatInfo->macho)
                                ->update([
                                    'trangthai' => 'dadat',
                                    'thoihan_giu' => null,
                                ]);
                        }
                    }

                    $order->status = 'completed';
                    $order->save();
                    $redirectUrl = env('FRONTEND_URL') . '/payment/result?vnp_TxnRef=' . $inputData['vnp_TxnRef'] . '&vnp_ResponseCode=' . $inputData['vnp_ResponseCode'];
                } else {
                    // Thanh toán thất bại, cập nhật trạng thái ghế về controng
                    $orderDetails = OrderDetail::where('order_id', $order->id)->get();
                    foreach ($orderDetails as $detail) {
                        $seatInfo = DB::table('cho')
                            ->where('sohieu', $detail->seat_number)
                            ->first();

                        if ($seatInfo) {
                            DB::table('datve')
                                ->where('macho', $seatInfo->macho)
                                ->update([
                                    'trangthai' => 'controng',
                                    'thoihan_giu' => null,
                                ]);
                        }
                    }

                    $order->status = 'failed';
                    $order->save();
                    $redirectUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/payment/result?vnp_ResponseCode=' . ($inputData['vnp_ResponseCode'] ?? '99') . '&status=failed';
                }
                return redirect()->to($redirectUrl);
            } else {
                return redirect()->to(env('FRONTEND_URL', 'http://localhost:5173') . '/payment/error');
            }
        } else {
            return redirect()->to(env('FRONTEND_URL', 'http://localhost:5173') . '/payment/error');
        }
    }
}