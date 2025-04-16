<template>
    <div class="payment-result">
      <div class="result-container">
        <el-card class="result-card">
          <div v-if="paymentStatus === 'success'" class="success-result">
            <div class="icon-wrapper success">
              <i class="el-icon-check-circle"></i>
            </div>
            <h1>Thanh toán thành công!</h1>
            <p>Cảm ơn bạn đã đặt vé. Mã đơn hàng của bạn là: {{ transactionId }}</p>
            <p>Vé điện tử sẽ được gửi đến email của bạn trong vòng 15 phút.</p>
            
            <div class="ticket-info">
              <h3>Thông tin vé</h3>
              <div class="ticket-details">
                <p><strong>Tuyến đường:</strong> {{ departureData?.searchgadi }} - {{ departureData?.searchgaden }}</p>
                <p><strong>Thời gian:</strong> {{ departureData?.departureTime }} - {{ departureData?.selectedDay }}</p>
                <p><strong>Mã tàu:</strong> {{ departureData?.trainCode }}</p>
                <p><strong>Tổng tiền:</strong> {{ formatTotalPrice(totalPrice) }}</p>
              </div>
            </div>
            
            <div class="actions">
              <el-button type="primary" @click="goToHome">Về trang chủ</el-button>
              <el-button @click="printTicket">In vé</el-button>
            </div>
          </div>
          
          <div v-else-if="paymentStatus === 'failed'" class="failed-result">
            <div class="icon-wrapper failed">
              <i class="el-icon-close-circle"></i>
            </div>
            <h1>Thanh toán thất bại</h1>
            <p>Rất tiếc, thanh toán của bạn không thành công.</p>
            <p>Mã lỗi: {{ errorCode }}</p>
            
            <div class="actions">
              <el-button type="primary" @click="tryAgain">Thử lại</el-button>
              <el-button @click="goToHome">Về trang chủ</el-button>
            </div>
          </div>
          
          <div v-else class="processing-result">
            <div class="icon-wrapper processing">
              <i class="el-icon-loading"></i>
            </div>
            <h1>Đang xử lý thanh toán</h1>
            <p>Vui lòng đợi trong khi chúng tôi xác nhận thanh toán của bạn.</p>
          </div>
        </el-card>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, computed } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useFormatPrice } from '@/composables/useFormatprice';
  import axios from 'axios';
  
  const route = useRoute();
  const router = useRouter();
  const { formatTotalPrice } = useFormatPrice();
  
  // Trạng thái thanh toán
  const paymentStatus = ref('processing'); // 'processing', 'success', 'failed'
  const transactionId = ref('');
  const errorCode = ref('');
  
  // Dữ liệu đặt vé
  const bookingData = ref(null);
  const departureData = computed(() => {
    if (bookingData.value) {
      return bookingData.value.departure || bookingData.value;
    }
    return null;
  });
  const returnData = computed(() => {
    if (bookingData.value) {
      return bookingData.value.return || null;
    }
    return null;
  });
  
  // Tổng tiền
  const totalPrice = computed(() => {
    if (!departureData.value) return 0;
    const departurePrice = departureData.value.totalPrice || 0;
    const returnPrice = returnData.value?.totalPrice || 0;
    return departurePrice + returnPrice;
  });
  
  onMounted(async () => {
    // Lấy dữ liệu từ query params
    const vnpTxnRef = route.query.vnp_TxnRef;
    const vnpResponseCode = route.query.vnp_ResponseCode;
    
    if (vnpTxnRef) {
      transactionId.value = vnpTxnRef;
      
      if (vnpResponseCode === '00') {
        // Thanh toán thành công
        paymentStatus.value = 'success';
        
        try {
          // Gọi API để lấy chi tiết đơn hàng
          const response = await axios.get(`/api/orders/${vnpTxnRef}`);
          bookingData.value = response.data.bookingData;
        } catch (error) {
          console.error('Lỗi khi lấy dữ liệu đơn hàng:', error);
        }
      } else {
        // Thanh toán thất bại
        paymentStatus.value = 'failed';
        errorCode.value = vnpResponseCode;
      }
    } else {
      // Không có mã giao dịch
      paymentStatus.value = 'failed';
      errorCode.value = 'Không tìm thấy mã giao dịch';
    }
  });
  
  // Các phương thức
  const goToHome = () => {
    router.push('/');
  };
  
  const tryAgain = () => {
    router.go(-1); // Quay lại trang thanh toán
  };
  
  const printTicket = () => {
    window.print();
  };
  </script>
  
  <style scoped>
  .payment-result {
    background-color: #f2f7fa;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 0;
  }
  
  .result-container {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
  }
  
  .result-card {
    padding: 20px;
  }
  
  .icon-wrapper {
    font-size: 64px;
    margin-bottom: 20px;
    text-align: center;
  }
  
  .success-result, .failed-result, .processing-result {
    text-align: center;
    padding: 20px 0;
  }
  
  .success .el-icon-check-circle {
    color: #67c23a;
  }
  
  .failed .el-icon-close-circle {
    color: #f56c6c;
  }
  
  .processing .el-icon-loading {
    color: #409eff;
  }
  
  h1 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #303133;
  }
  
  p {
    color: #606266;
    margin-bottom: 10px;
  }
  
  .ticket-info {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin: 30px 0;
    text-align: left;
  }
  
  .ticket-info h3 {
    margin-top: 0;
    color: #104c8a;
    font-size: 18px;
    margin-bottom: 15px;
  }
  
  .ticket-details p {
    margin-bottom: 8px;
  }
  
  .actions {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 15px;
  }
  
  @media print {
    .actions {
      display: none;
    }
  }
  </style>