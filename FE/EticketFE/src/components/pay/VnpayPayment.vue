<template>
    <el-card class="vnpay-card">
      <template #header>
        <div class="card-header">
          <h2>Phương thức thanh toán</h2>
        </div>
      </template>
      
      <div class="payment-methods">
        <el-radio-group v-model="paymentMethod" class="payment-radio-group">
          <el-radio label="vnpay" border>
            <div class="payment-option">
              <span>Thanh toán qua VNPay</span>
            </div>
          </el-radio>
        </el-radio-group>
      </div>
      
      <div class="vnpay-banks">
        <h3>Chọn ngân hàng thanh toán (tùy chọn)</h3>
        <div class="bank-list">
          <el-radio-group v-model="selectedBank" class="bank-radio-group">
            <el-radio label="" border>VNPay</el-radio>
            <el-radio label="NCB" border>NCB</el-radio>
            <el-radio label="BIDV" border>BIDV</el-radio>
            <el-radio label="VCB" border>Vietcombank</el-radio>
            <el-radio label="TCB" border>Techcombank</el-radio>
          </el-radio-group>
        </div>
      </div>
      
      <div class="payment-actions">
        <el-button @click="gotoback">
      <el-icon><ArrowLeft /></el-icon> Quay lại
    </el-button>
        <el-button type="primary" :loading="loading" @click="processPayment">
          Thanh toán {{ formatTotalPrice(totalPrice) }}
          <el-icon class="el-icon--right"><ArrowRight /></el-icon>
        </el-button>
      </div>
    </el-card>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue';
  import { ArrowRight, ArrowLeft } from '@element-plus/icons-vue';
  import { useFormatPrice } from '@/composables/useFormatprice';
  import axios from 'axios';
  
  const props = defineProps({
    departureData: { 
      type: Object, 
      required: true 
    },
    returnData: { 
      type: Object, 
      default: null 
    },
    contactInfo: {
      type: Object,
      required: true
    },
    passengerInfo: {
      type: Array,
      required: true
    }
  });

  const gotoback = () => {
  window.history.back();
};
  
  const { formatTotalPrice } = useFormatPrice();
  const paymentMethod = ref('vnpay');
  const selectedBank = ref('');
  const loading = ref(false);
  
  const totalPrice = computed(() => {
    const departurePrice = props.departureData?.totalPrice || 0;
    const returnPrice = props.returnData?.totalPrice || 0;
    return departurePrice + returnPrice;
  });
  
  const processPayment = async () => {
    if (paymentMethod.value === 'vnpay') {
      try {
        loading.value = true;
        
        // Chuẩn bị dữ liệu đặt vé
        const bookingData = {
          departure: props.departureData,
          return: props.returnData,
          contact: props.contactInfo,
          passengers: props.passengerInfo,
          amount: totalPrice.value,
          bankCode: selectedBank.value
        };
        
        // Gọi API để tạo đơn hàng và lấy URL thanh toán
        const response = await axios.post('/api/vnpay/create', bookingData);
        
        // Chuyển hướng đến trang thanh toán VNPay
        if (response.data && response.data.redirectUrl) {
          window.location.href = response.data.redirectUrl;
        }
      } catch (error) {
        console.error('Lỗi khi xử lý thanh toán:', error);
        ElMessage.error('Đã xảy ra lỗi khi xử lý thanh toán. Vui lòng thử lại sau.');
      } finally {
        loading.value = false;
      }
    } else {
      // Xử lý thanh toán COD
      // Implement logic here...
    }
  };
  </script>
  
  <style scoped>
  .vnpay-card {
    margin-bottom: 20px;
  }
  
  .card-header h2 {
    margin: 0;
    font-size: 18px;
    color: #104c8a;
  }
  
  .payment-methods {
    margin-bottom: 20px;
  }
  
  .payment-radio-group {
    display: flex;
    gap: 10px;
    width: 100%;
  }
  
  .payment-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px;
  }
  
  .payment-logo {
    max-height: 30px;
  }
  
  .vnpay-banks {
    margin-top: 20px;
    margin-bottom: 20px;
  }
  
  .bank-list {
    margin-top: 10px;
  }
  
  .bank-radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  
  .payment-actions {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
  }
  </style>