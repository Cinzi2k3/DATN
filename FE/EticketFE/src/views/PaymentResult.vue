<template>
  <div class="payment-result">
    <div class="result-container">
      <el-card class="result-card" :body-style="{ padding: '0px' }">
        <!-- Thanh toán thành công -->
        <div v-if="paymentStatus === 'success'" class="success-result">
          <div class="header-section bg-success">
            <div class="icon-wrapper success">
              <el-icon class="icon-large"><Check /></el-icon>
            </div>
            <h1 class="mb-0">Thanh toán thành công!</h1>
          </div>
          
          <div class="result-content py-4 px-4">
            <div class="order-summary">
              <div class="alert alert-light border mb-4">
                <div class="row">
                  <div class="col-md-6">
                    <p class="mb-1"><strong>Tên người đặt:</strong> {{ bookingData.contact_name }}</p>
                    <p class="mb-1"><strong>Số điện thoại:</strong> {{ bookingData.contact_phone }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ bookingData.contact_email }}</p>
                  </div>
                  <div class="col-md-6">
                    <p class="mb-1"><strong>Ngày đặt:</strong> {{ getCurrentDate() }}</p>
                    <p class="mb-1"><strong>Loại vé:</strong> {{ bookingData.ticket_type === 'one-way' ? 'Một chiều' : 'Khứ hồi' }}</p>
                    <p class="mb-1"><strong>Phương thức thanh toán:</strong> VNPay</p>
                  </div>
                </div>
              </div>
              
              <el-alert
                type="info"
                :closable="false"
                show-icon>
                <template #title>
                  <span class="fw-bold">Thông báo</span>
                </template>
                <p class="mb-0">Vé điện tử sẽ được gửi đến email của bạn trong vòng 15 phút.</p>
              </el-alert>
            </div>

            <div class="ticket-info mt-4" v-if="bookingData">
              <div class="section-header">
                <el-divider content-position="left">
                  <el-icon class="me-2"><Ticket /></el-icon>Thông tin vé
                </el-divider>
              </div>

              <!-- Vé một chiều -->
              <el-collapse v-model="activeCollapse" class="custom-collapse mb-3" v-if="departureData?.length">
                <el-collapse-item name="departure">
                  <template #title>
                    <div class="d-flex align-items-center">
                      <el-icon class="me-2 text-primary fs-5"><Right /></el-icon>
                      <span class="fw-bold fs-5">Vé đi</span>
                    </div>
                  </template>
                  <div class="card border-0 shadow-sm mb-3" v-for="(detail, index) in departureData" :key="`departure-${index}`">
                    <div class="card-body">
                      <div class="ticket-header mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                          <div class="route-info">
                            <h5 class="mb-1">{{ getFromToStations(detail.schedule_route) }}</h5>
                            <p class="text-muted mb-0 mt-2">{{ formatDate(detail.departureTime) }} → {{ formatDate(detail.arrivalTime) }}</p>
                          </div>
                          <div class="time-info text-end">
                            <div class="d-flex align-items-center justify-content-end">
                              <span class="fw-bold me-2">{{ getTime(detail.departureTime) }}</span>
                              <div class="route-line mx-2">
                                <el-icon class="mx-1"><Right /></el-icon>
                              </div>
                              <span class="fw-bold ms-2">{{ getTime(detail.arrivalTime) }}</span>
                            </div>
                            <div class="mt-1">
                              <i class="far fa-clock me-1"></i>
                              <span class="small">{{ calculateDuration(detail.departureTime, detail.arrivalTime) }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="row ticket-grid">
                        <div class="col-md-6">
                          <p><el-icon class="me-2 text-muted"><Van /></el-icon><strong>Mã tàu:</strong> {{ detail.train_code }}</p>
                          <p><el-icon class="me-2 text-muted"><InfoFilled /></el-icon><strong>Loại tàu:</strong> {{ detail.train_type }}</p>
                          <p><el-icon class="me-2 text-muted"><SuitcaseLine /></el-icon><strong>Toa:</strong> {{ detail.car_name }}</p>
                          <p><el-icon class="me-2 text-muted"><OfficeBuilding /></el-icon><strong>Số ghế:</strong> {{ detail.seat_number }}</p>
                        </div>
                        <div class="col-md-6">
                          <p><el-icon class="me-2 text-muted"><Money /></el-icon><strong>Giá vé:</strong> {{ formatTotalPrice(detail.price) }}</p>
                          <p><el-icon class="me-2 text-muted"><User /></el-icon><strong>Hành khách:</strong> {{ detail.passenger?.name || 'Không có thông tin' }}</p>
                          <p><el-icon class="me-2 text-muted"><Document /></el-icon><strong>CCCD/Passport:</strong> {{ detail.passenger?.cccd || 'N/A' }}</p>
                          <p><el-icon class="me-2 text-muted"><Discount /></el-icon><strong>Loại vé:</strong> {{ detail.ticket_type }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </el-collapse-item>
              </el-collapse>

              <!-- Vé khứ hồi -->
              <el-collapse v-model="activeCollapse" class="custom-collapse mb-3" v-if="returnData?.length">
                <el-collapse-item name="return">
                  <template #title>
                    <div class="d-flex align-items-center">
                      <el-icon class="me-2 text-success fs-5"><Back /></el-icon>
                      <span class="fw-bold fs-5">Vé về</span>
                    </div>
                  </template>
                  <div class="card border-0 shadow-sm mb-3" v-for="(detail, index) in returnData" :key="`return-${index}`">
                    <div class="card-body">
                      <div class="ticket-header mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                          <div class="route-info">
                            <h5 class="mb-1">{{ getFromToStations(detail.schedule_route) }}</h5>
                            <p class="text-muted mb-0 mt-2">{{ formatDate(detail.departureTime) }} → {{ formatDate(detail.arrivalTime) }}</p>
                          </div>
                          <div class="time-info text-end">
                            <div class="d-flex align-items-center justify-content-end">
                              <span class="fw-bold me-2">{{ getTime(detail.departureTime) }}</span>
                              <div class="route-line mx-2">
                                <el-icon class="mx-1"><Right /></el-icon>
                              </div>
                              <span class="fw-bold ms-2">{{ getTime(detail.arrivalTime) }}</span>
                            </div>
                            <div class="mt-1">
                              <i class="far fa-clock me-1"></i>
                              <span class="small">{{ calculateDuration(detail.departureTime, detail.arrivalTime) }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="row ticket-grid">
                        <div class="col-md-6">
                          <p><el-icon class="me-2 text-muted"><Van /></el-icon><strong>Mã tàu:</strong> {{ detail.train_code }}</p>
                          <p><el-icon class="me-2 text-muted"><InfoFilled /></el-icon><strong>Loại tàu:</strong> {{ detail.train_type }}</p>
                          <p><el-icon class="me-2 text-muted"><SuitcaseLine /></el-icon><strong>Toa:</strong> {{ detail.car_name }}</p>
                          <p><el-icon class="me-2 text-muted"><OfficeBuilding /></el-icon><strong>Số ghế:</strong> {{ detail.seat_number }}</p>
                        </div>
                        <div class="col-md-6">
                          <p><el-icon class="me-2 text-muted"><Money /></el-icon><strong>Giá vé:</strong> {{ formatTotalPrice(detail.price) }}</p>
                          <p><el-icon class="me-2 text-muted"><User /></el-icon><strong>Hành khách:</strong> {{ detail.passenger?.name || 'Không có thông tin' }}</p>
                          <p><el-icon class="me-2 text-muted"><Document /></el-icon><strong>CCCD/Passport:</strong> {{ detail.passenger?.cccd || 'N/A' }}</p>
                          <p><el-icon class="me-2 text-muted"><Discount /></el-icon><strong>Loại vé:</strong> {{ detail.ticket_type }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </el-collapse-item>
              </el-collapse>

              <!-- Tổng tiền -->
              <div class="total-section mt-4">
                <div class="card border-0 shadow-sm">
                  <div class="card-body">
                    <h5 class="mb-3 border-bottom pb-2">Chi tiết thanh toán</h5>
                    <div class="d-flex justify-content-between mb-2">
                      <span>Tổng tiền vé:</span>
                      <span>{{ formatTotalPrice(bookingData.total_amount) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                      <span>Phí dịch vụ:</span>
                      <span>Chưa có</span>
                    </div>
                    <div class="d-flex justify-content-between">
                      <span>Giảm giá:</span>
                      <span class="text-success">Chưa có</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="fw-bold fs-5">Tổng thanh toán:</span>
                      <span class="fs-4 fw-bold">{{ formatTotalPrice(bookingData.total_amount) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Mã QR -->
              <div class="qr-code-section mt-4 text-center">
                <h5>Mã QR vé của bạn</h5>
                <qrcode-vue
                  v-if="qrCodeData"
                  :value="qrCodeData"
                  :size="200"
                  level="H"
                  class="qr-code"
                />
                <p class="text-muted mt-2">Quét mã QR tại quầy check-in để xác nhận vé.</p>
              </div>
            </div>
            <div v-else class="alert alert-warning mt-4">
              <el-icon class="me-2"><WarningFilled /></el-icon>
              Không có thông tin vé.
            </div>

            <div class="actions mt-4 text-center">
              <el-button type="primary" round size="large" @click="goToHome">
                <el-icon class="me-2"><HomeFilled /></el-icon>Về trang chủ
              </el-button>
            </div>
          </div>
        </div>

        <!-- Thanh toán thất bại -->
        <div v-else-if="paymentStatus === 'failed'" class="failed-result">
          <div class="header-section bg-danger">
            <div class="icon-wrapper failed">
              <el-icon class="icon-large"><Close /></el-icon>
            </div>
            <h1 class="mb-0">Thanh toán thất bại</h1>
          </div>
          
          <div class="result-content py-4 px-4 text-center">
            <el-alert
              type="error"
              :closable="false"
              show-icon
              class="mb-4">
              <template #title>
                <span class="fw-bold">Rất tiếc, thanh toán của bạn không thành công.</span>
              </template>
              <p class="mb-0">Mã lỗi: {{ errorCode }}</p>
            </el-alert>
            
            <div class="actions mt-4">
              <el-button type="danger" round size="large" @click="tryAgain">
                <el-icon class="me-2"><RefreshRight /></el-icon>Thử lại
              </el-button>
              <el-button round size="large" @click="goToHome">
                <el-icon class="me-2"><HomeFilled /></el-icon>Về trang chủ
              </el-button>
            </div>
          </div>
        </div>

        <!-- Đang xử lý -->
        <div v-else class="processing-result">
          <div class="header-section bg-primary">
            <div class="icon-wrapper processing">
              <el-progress type="circle" :percentage="100" status="processing"></el-progress>
            </div>
            <h1 class="mb-0">Đang xử lý thanh toán</h1>
          </div>
          
          <div class="result-content py-4 px-4 text-center">
            <el-alert
              type="info"
              :closable="false"
              show-icon>
              <template #title>
                <span class="fw-bold">Vui lòng đợi trong khi chúng tôi xác nhận thanh toán của bạn.</span>
              </template>
            </el-alert>
          </div>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useFormatPrice } from '@/composables/useFormatprice';
import QrcodeVue from 'qrcode.vue';
import axios from 'axios';
import {
  Check, Close, Clock, HomeFilled, Ticket, User, Money,
  InfoFilled, LocationInformation, Right, Back, RefreshRight, WarningFilled,
  Van, SuitcaseLine, OfficeBuilding, View, Document, Discount
} from '@element-plus/icons-vue';

const route = useRoute();
const router = useRouter();
const { formatTotalPrice } = useFormatPrice();

const paymentStatus = ref('processing');
const transactionId = ref('');
const errorCode = ref('');
const bookingData = ref(null);
const activeCollapse = ref(['departure', 'return']);
const qrCodeData = ref('');

const departureData = computed(() => {
  if (bookingData.value?.details) {
    return bookingData.value.details.departure || [];
  }
  return [];
});

const returnData = computed(() => {
  if (bookingData.value?.details) {
    return bookingData.value.details.return || [];
  }
  return [];
});

// Trong PaymentResult.vue, sửa qrCodeData
onMounted(async () => {
  const vnpTxnRef = route.query.vnp_TxnRef;
  const vnpResponseCode = route.query.vnp_ResponseCode;

  if (!vnpTxnRef) {
    paymentStatus.value = 'failed';
    errorCode.value = 'Không tìm thấy mã giao dịch';
    return;
  }

  transactionId.value = vnpTxnRef;

  if (vnpResponseCode === '00') {
    paymentStatus.value = 'success';

    try {
      const response = await axios.get(`/orders/${vnpTxnRef}`);
      console.log('API response:', response.data);
      bookingData.value = response.data.bookingData;

      // Tạo mã QR chứa URL
      if (bookingData.value) {
        qrCodeData.value = `http://172.20.10.6:5173/check-in?vnp_txn_ref=${vnpTxnRef}`;
      }
    } catch (error) {
      console.error('Lỗi khi lấy dữ liệu đơn hàng:', error.response?.data || error.message);
      paymentStatus.value = 'failed';
      errorCode.value = error.response?.data?.error || 'Lỗi tải dữ liệu đơn hàng';
    }
  } else {
    paymentStatus.value = 'failed';
    errorCode.value = vnpResponseCode || 'Lỗi không xác định';
  }
});

const goToHome = () => {
  router.push('/');
};

const tryAgain = () => {
  router.go(-1);
};

// Hàm xử lý dữ liệu
const getFromToStations = (route) => {
  if (!route) return 'N/A';
  const stations = route.split(' - ');
  return stations.length === 2 ? stations.join(' → ') : route;
};

const formatDate = (dateTime) => {
  if (!dateTime) return 'N/A';
  const date = new Date(dateTime);
  return date.toLocaleDateString('vi-VN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

const getTime = (dateTime) => {
  if (!dateTime) return 'N/A';
  const date = new Date(dateTime);
  return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

const calculateDuration = (start, end) => {
  if (!start || !end) return 'N/A';
  
  const startTime = new Date(start);
  const endTime = new Date(end);
  const diff = endTime - startTime;
  
  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  
  return `${hours} giờ ${minutes} phút`;
};

const getCurrentDate = () => {
  return new Date().toLocaleDateString('vi-VN', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<style>
@import url(@/assets/css/paymentresult.css);

.qr-code-section {
  margin-top: 20px;
}

.qr-code {
  display: inline-block;
}
</style>