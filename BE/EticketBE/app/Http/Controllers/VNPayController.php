<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VNPayController extends Controller
{
    public function createPayment(Request $request)
    {
        $vnp_TmnCode = "KHT30JML"; // Mã website tại VNPay
        $vnp_HashSecret = "YDKXVCGC7KL9KGJ8Y83JTORK8MWXCFSV"; // Chuỗi bí mật
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = url('http://192.168.0.103:5173/payment/result');
        
        $vnp_TxnRef = time() . "-" . Str::random(8); // Mã đơn hàng
        $vnp_OrderInfo = "Thanh toán vé tàu"; // Thông tin đơn hàng
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $request->amount * 100; // Số tiền thanh toán
        $vnp_Locale = 'vn';
        $vnp_IpAddr = $request->ip();
        $vnp_BankCode = $request->bankCode ?? '';
        
        $inputData = [
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
            "vnp_TxnRef" => $vnp_TxnRef,
        ];
        
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
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        
        // Lưu thông tin đơn hàng vào database
        // Ví dụ: Order::create(['transaction_id' => $vnp_TxnRef, 'amount' => $request->amount, ...]);
        
        return response()->json(['redirectUrl' => $vnp_Url]);
    }
    
    public function returnPayment(Request $request)
    {
        $vnp_HashSecret = "YDKXVCGC7KL9KGJ8Y83JTORK8MWXCFSV"; // Chuỗi bí mật
        $inputData = $request->all();
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
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
            if ($inputData['vnp_ResponseCode'] == '00') {
                // Cập nhật trạng thái đơn hàng
                // Ví dụ: Order::where('transaction_id', $inputData['vnp_TxnRef'])->update(['status' => 'completed']);
                
                // Redirect về trang thành công
                return redirect()->to(env('FRONTEND_URL') . '/payment/success?vnp_TxnRef=' . $inputData['vnp_TxnRef']);
            } else {
                // Redirect về trang thất bại
                return redirect()->to(env('FRONTEND_URL') . '/payment/failed?vnp_ResponseCode=' . $inputData['vnp_ResponseCode']);
            }
        } else {
            // Redirect về trang lỗi
            return redirect()->to(env('FRONTEND_URL') . '/payment/error');
        }
    }
}