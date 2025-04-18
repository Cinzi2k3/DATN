<template>
    <div class="check-in-page container mt-5">
      <h2 class="text-center mb-4">Check-in vé tàu</h2>
      <el-form :model="form" ref="formRef" :rules="rules" @submit.prevent="submitForm">
        <el-form-item label="Mã giao dịch (vnp_TxnRef)" prop="vnp_txn_ref">
          <el-input v-model="form.vnp_txn_ref" placeholder="Nhập mã giao dịch từ mã QR" />
        </el-form-item>
        <div class="text-center">
          <el-button type="primary" round size="large" native-type="submit">
            <el-icon class="me-2"><Check /></el-icon>Xác nhận check-in
          </el-button>
        </div>
      </el-form>
      <div v-if="result" class="mt-4">
        <el-alert
          :type="result.success ? 'success' : 'error'"
          :title="result.message"
          :closable="false"
          show-icon
          class="mb-4"
        >
          <div v-if="result.data">
            <p><strong>Mã giao dịch:</strong> {{ result.data.transaction_id }}</p>
            <p><strong>Tên người đặt:</strong> {{ result.data.contact_name }}</p>
            <p><strong>Trạng thái:</strong> {{ result.data.checkin === 'used' ? 'Đã check-in' : 'Chưa check-in' }}</p>
            <el-button type="success" round size="large" @click="printTicket">
          <el-icon class="me-2"><Printer /></el-icon>In vé
        </el-button>
          </div>
        </el-alert>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { useRoute } from 'vue-router';
  import axios from 'axios';
  import { ElMessage } from 'element-plus';
  import { Check } from '@element-plus/icons-vue';
  
  const route = useRoute();
  const form = ref({ vnp_txn_ref: '' });
  const formRef = ref(null);
  const result = ref(null);
  
  const rules = {
    vnp_txn_ref: [
      { required: true, message: 'Vui lòng nhập mã giao dịch', trigger: 'blur' }
    ]
  };
  
  onMounted(() => {
    // Nếu quét mã QR mở URL với query vnp_txn_ref
    if (route.query.vnp_txn_ref) {
      form.value.vnp_txn_ref = route.query.vnp_txn_ref;
      submitForm();
    }
  });
  
  const submitForm = async () => {
    try {
      const response = await axios.post('/check-in', { vnp_txn_ref: form.value.vnp_txn_ref });
      result.value = response.data;
  
      if (response.data.success) {
        ElMessage.success('Check-in thành công! Vé đã được đánh dấu là đã sử dụng.');
      } else {
        ElMessage.error(response.data.message);
      }
    } catch (error) {
      result.value = { success: false, message: 'Lỗi khi check-in: ' + (error.response?.data?.message || error.message) };
      ElMessage.error('Lỗi khi check-in vé.');
    }
  };

  const printTicket = () => {
  // Tạo cửa sổ in mới
  const printWindow = window.open('', '_blank');
  const orderData = result.value.data;
  console.log('order', orderData);

  // Kiểm tra dữ liệu vé
  if (!orderData) {
    ElMessage.error('Không có dữ liệu vé để in!');
    printWindow.close();
    return;
  }

  // Xử lý thông tin chuyến đi và chuyến về
  const departureTrips = orderData.details?.departure || [];
  const returnTrips = orderData.details?.return || [];
  
  const firstDepartureTrip = departureTrips[0] || {};
  const firstReturnTrip = returnTrips[0] || {};
  
  const isRoundTrip = orderData.ticket_type === 'round-trip' && returnTrips.length > 0;
  
  // Định dạng tiền tệ VND
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' })
      .format(amount)
      .replace('₫', 'VNĐ');
  };

  // HTML và CSS cho giao diện in vé
  const printContent = `
    <html>
      <head>
        <title>In Vé Tàu</title>
        <style>
          body {
            font-family: Arial, sans-serif;
            margin: 20px;
            text-align: center;
            color: #333;
          }
          .ticket-container {
            max-width: 500px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
          }
          .ticket-header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #0066cc;
          }
          .ticket-info, .trip-section, .passenger-section {
            text-align: left;
            margin-bottom: 20px;
            font-size: 16px;
          }
          .ticket-info p, .trip-section p, .passenger-section p {
            margin: 5px 0;
          }
          .ticket-info strong, .trip-section strong, .passenger-section strong {
            display: inline-block;
            width: 150px;
            font-weight: bold;
          }
          .trip-section, .passenger-section {
            border-top: 1px dashed #666;
            padding-top: 15px;
          }
          .trip-section h3, .passenger-section h3 {
            font-size: 18px;
            margin: 10px 0;
            color: #333;
          }
          .footer {
            font-size: 14px;
            color: #666;
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
          }
          .divider {
            border-top: 1px solid #000;
            margin: 15px 0;
          }
          .price-info {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
          }
        </style>
      </head>
      <body>
        <div class="ticket-container">
          <div class="ticket-header">VÉ TÀU KHÁCH ĐƯỜNG SẮT VIỆT NAM</div>
          
          <div class="ticket-info">
            <p><strong>Mã giao dịch:</strong> ${orderData.transaction_id || 'N/A'}</p>
            <p><strong>Ngày check-in:</strong> ${new Date().toLocaleDateString('vi-VN')}</p>
          </div>
          
          <div class="divider"></div>
          
          <div class="passenger-section">
            <h3>Thông tin liên hệ</h3>
            <p><strong>Họ và tên:</strong> ${orderData.contact_name || 'N/A'}</p>
            <p><strong>Số điện thoại:</strong> ${orderData.contact_phone || 'N/A'}</p>
            <p><strong>Email:</strong> ${orderData.contact_email || 'N/A'}</p>
          </div>
          
          <div class="passenger-section">
            <h3>Thông tin hành khách</h3>
            <p><strong>Họ và tên:</strong> ${firstDepartureTrip.passenger?.name || 'N/A'}</p>
            <p><strong>Ngày sinh:</strong> ${firstDepartureTrip.passenger?.birthdate || 'N/A'}</p>
            <p><strong>CCCD/CMND:</strong> ${firstDepartureTrip.passenger?.cccd || 'N/A'}</p>
          </div>
          
          <div class="trip-section">
            <h3>Thông tin chuyến đi</h3>
            <p><strong>Tuyến đường:</strong> ${firstDepartureTrip.schedule_route || 'N/A'}</p>
            <p><strong>Mã tàu:</strong> ${firstDepartureTrip.train_code || 'N/A'}</p>
            <p><strong>Loại tàu:</strong> ${firstDepartureTrip.train_type || 'N/A'}</p>
            <p><strong>Khởi hành:</strong> ${firstDepartureTrip.departureTime || 'N/A'}</p>
            <p><strong>Đến nơi:</strong> ${firstDepartureTrip.arrivalTime || 'N/A'}</p>
            <p><strong>Loại vé:</strong> ${firstDepartureTrip.ticket_type === 'adult' ? 'Người lớn' : 'Trẻ em'}</p>
            <p><strong>Toa:</strong> ${firstDepartureTrip.car_name || 'N/A'}</p>
            <p><strong>Số ghế:</strong> ${firstDepartureTrip.seat_number || 'N/A'}</p>
            <p><strong>Giá vé:</strong> ${formatCurrency(firstDepartureTrip.price || 0)}</p>
          </div>
          
          ${isRoundTrip ? `
          <div class="trip-section">
            <h3>Thông tin chuyến về</h3>
            <p><strong>Tuyến đường:</strong> ${firstReturnTrip.schedule_route || 'N/A'}</p>
            <p><strong>Mã tàu:</strong> ${firstReturnTrip.train_code || 'N/A'}</p>
            <p><strong>Loại tàu:</strong> ${firstReturnTrip.train_type || 'N/A'}</p>
            <p><strong>Khởi hành:</strong> ${firstReturnTrip.departureTime || 'N/A'}</p>
            <p><strong>Đến nơi:</strong> ${firstReturnTrip.arrivalTime || 'N/A'}</p>
            <p><strong>Loại vé:</strong> ${firstDepartureTrip.ticket_type === 'adult' ? 'Người lớn' : 'Trẻ em'}</p>
            <p><strong>Toa:</strong> ${firstReturnTrip.car_name || 'N/A'}</p>
            <p><strong>Số ghế:</strong> ${firstReturnTrip.seat_number || 'N/A'}</p>
            <p><strong>Giá vé:</strong> ${formatCurrency(firstReturnTrip.price || 0)}</p>
          </div>
          ` : ''}
          
          <div class="divider"></div>
          
          <div class="price-info">
            <p><strong>Tổng tiền:</strong> ${formatCurrency(orderData.total_amount || 0)}</p>
          </div>
          
          <div class="divider"></div>
          
          <div class="footer">
            <p>Cảm ơn quý khách đã sử dụng dịch vụ của Công Ty Đường Sắt Việt Nam!</p>
            <p>Vui lòng đem theo vé và CCCD/CMND khi lên tàu.</p>
          </div>
        </div>
      </body>
    </html>
  `;

  // Ghi nội dung vào cửa sổ in
  printWindow.document.write(printContent);
  printWindow.document.close();

  // Tự động in và đóng cửa sổ
  printWindow.onload = () => {
    printWindow.print();
    printWindow.onafterprint = () => {
      printWindow.close();
    };
  };
};
  </script>
  
  <style scoped>
  .check-in-page {
    max-width: 600px;
    margin: 0 auto;
  }
  </style>