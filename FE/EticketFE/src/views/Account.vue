<template>
  <div class="min-vh-100 bg-light">
    <div v-if="loading" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center" style="z-index: 1050;">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">{{ $t('Loading...') }}</span>
      </div>
    </div>

    <!-- Mobile Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm d-lg-none">
      <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="#">{{ $t('Tài khoản') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" :aria-label="$t('Toggle navigation')">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav mt-2">
            <li class="nav-item">
              <a class="nav-link" :class="{ 'active fw-bold': activeMenu === 'profile' }" href="#" @click.prevent="handleMenuSelect('profile')">{{ $t('Thông tin cá nhân') }}</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" :class="{ 'active fw-bold': activeMenu === 'orders' }" href="#" @click.prevent="handleMenuSelect('orders')">{{ $t('Lịch sử đơn hàng') }}</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Main Layout -->
    <div class="container py-4 py-lg-5">
      <div class="row g-4">
        <!-- Sidebar for Desktop -->
        <div class="col-lg-3 d-none d-lg-block">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h4 class="card-title fw-bold text-primary mb-4">{{ $t('Hồ sơ của tôi') }}</h4>
              <el-menu :default-active="activeMenu" @select="handleMenuSelect" class="border-0">
                <el-menu-item index="profile" class="mb-2">
                  <i class="el-icon-user me-2"></i>
                  <span>{{ $t('Thông tin cá nhân') }}</span>
                </el-menu-item>
                <el-menu-item index="orders">
                  <i class="el-icon-tickets me-2"></i>
                  <span>{{ $t('Lịch sử đơn hàng') }}</span>
                </el-menu-item>
              </el-menu>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
              <!-- Profile Section -->
              <div v-if="activeMenu === 'profile'">
                <h3 class="fw-bold text-dark mb-4">{{ $t('Thông tin cá nhân') }}</h3>
                <el-form v-if="isEditing" :model="form" label-width="120px" :rules="rules" class="row g-3">
                  <el-form-item :label="$t('Tên')" prop="name" class="col-12">
                    <el-input v-model="form.name" :placeholder="$t('Nhập họ và tên')"></el-input>
                  </el-form-item>
                  <el-form-item :label="$t('Email')" prop="email" class="col-12">
                    <el-input v-model="form.email" :placeholder="$t('Nhập email')"></el-input>
                  </el-form-item>
                  <el-form-item :label="$t('Số điện thoại')" prop="phone_number" class="col-12">
                    <el-input v-model="form.phone_number" :placeholder="$t('Nhập số điện thoại')"></el-input>
                  </el-form-item>
                  <el-form-item :label="$t('Địa chỉ')" prop="address" class="col-12">
                    <el-input v-model="form.address" :placeholder="$t('Nhập địa chỉ')"></el-input>
                  </el-form-item>
                  <el-form-item :label="$t('Ngày sinh')" prop="birth_date" required>
                    <el-date-picker v-model="form.birth_date" type="date" :placeholder="$t('Chọn ngày sinh')" format="YYYY-MM-DD" value-format="YYYY-MM-DD"></el-date-picker>
                  </el-form-item>
                  <div class="col-12 mt-4">
                    <el-button type="primary" @click="saveProfile">{{ $t('Lưu') }}</el-button>
                    <el-button @click="cancelEdit">{{ $t('Hủy') }}</el-button>
                  </div>
                </el-form>
                <div v-else class="row g-3">
                  <div class="col-12"><strong>{{ $t('Tên') }}:</strong> {{ authStore.userName }}</div>
                  <div class="col-12"><strong>{{ $t('Email') }}:</strong> {{ authStore.userEmail }}</div>
                  <div class="col-12"><strong>{{ $t('Số điện thoại') }}:</strong> {{ authStore.phoneNumber || $t('Chưa cập nhật') }}</div>
                  <div class="col-12"><strong>{{ $t('Địa chỉ') }}:</strong> {{ authStore.address || $t('Chưa cập nhật') }}</div>
                  <div class="col-12"><strong>{{ $t('Ngày sinh') }}:</strong> {{ authStore.birthDate || $t('Chưa cập nhật') }}</div>
                  <div class="col-12 mt-4">
                    <el-button type="primary" @click="editProfile">{{ $t('Chỉnh sửa') }}</el-button>
                  </div>
                </div>
              </div>

              <!-- Orders Section -->
              <div v-if="activeMenu === 'orders'">
                <h3 class="fw-bold text-dark mb-4">{{ $t('Lịch sử đơn hàng') }}</h3>
                <el-table :data="filteredOrders" v-if="filteredOrders.length" class="table table-hover">
                  <el-table-column label="STT" width="70">
                    <template #default="scope">
                      {{ scope.$index + 1 }}
                    </template>
                  </el-table-column>
                  <el-table-column prop="transaction_id" :label="$t('Mã giao dịch')" width="150" />
                  <el-table-column prop="created_at" :label="$t('Ngày đặt')" width="150" />
                  <el-table-column :label="$t('Tổng tiền')" width="120">
                    <template #default="scope">
                      {{ formatTotalPrice(scope.row.total_amount) }}
                    </template>
                  </el-table-column>
                  <el-table-column prop="status" :label="$t('Trạng thái')" width="150">
                    <template #default="scope">
                      <el-tag :type="getStatusTagType(scope.row.checkin)">
                        {{ scope.row.checkin === 'unused' ? $t('Chưa check-in') : scope.row.checkin === 'used' ? $t('Đã check-in') : $t('Thất bại') }}
                      </el-tag>
                    </template>
                  </el-table-column>
                  <el-table-column :label="$t('Mã QR')" width="100">
                    <template #default="scope">
                      <el-button type="primary" size="small" @click="viewQRCode(scope.row)" v-if="scope.row.qr_code" class="w-100">
                        {{ $t('Xem QR') }}
                      </el-button>
                      <el-tag type="info" v-else>{{ $t('Không có QR') }}</el-tag>
                    </template>
                  </el-table-column>
                  <el-table-column :label="$t('Chi tiết')" width="100">
                    <template #default="scope">
                      <el-button type="text" @click="viewOrderDetails(scope.row)">
                        <i class="fa-solid fa-eye fs-3"></i>
                      </el-button>
                    </template>
                  </el-table-column>
                </el-table>
                <p v-else class="text-muted">{{ $t('Chưa có đơn hàng nào.') }}</p>

                <!-- QR Code Dialog -->
                <el-dialog :title="$t('Mã QR vé của bạn')" v-model="qrDialogVisible" width="350px" center>
                  <div class="text-center py-4">
                    <qrcode-vue
                      v-if="selectedOrder && selectedOrder.qr_code"
                      :value="selectedOrder.qr_code"
                      :size="250"
                      level="H"
                      class="mx-auto"
                    />
                    <p class="text-muted mt-3 mb-0 small">{{ $t('Quét mã QR tại quầy check-in để xác nhận vé') }}</p>
                  </div>
                </el-dialog>

                <!-- Order Details Dialog -->
                <el-dialog :title="$t('Chi tiết đơn hàng')" v-model="dialogVisible" width="90%" class="rounded">
                  <div v-if="selectedOrder" class="row g-4">
                    <div class="col-12">
                      <h4 class="fw-bold text-dark">{{ $t('Thông tin đơn hàng') }}</h4>
                      <div class="row g-2 mt-2">
                        <div class="col-md-6"><strong>{{ $t('Mã giao dịch') }}:</strong> {{ selectedOrder.transaction_id }}</div>
                        <div class="col-md-6"><strong>{{ $t('Ngày đặt') }}:</strong> {{ selectedOrder.created_at }}</div>
                        <div class="col-md-6"><strong>{{ $t('Tổng tiền') }}:</strong> {{ formatTotalPrice(selectedOrder.total_amount) }} VND</div>
                        <div class="col-md-6"><strong>{{ $t('Trạng thái') }}:</strong> {{ selectedOrder.checkin === 'unused' ? $t('Chưa check-in') : selectedOrder.checkin === 'used' ? $t('Đã check-in') : $t('Thất bại') }}</div>
                        <div class="col-md-6"><strong>{{ $t('Loại vé') }}:</strong> {{ selectedOrder.ticket_type === 'one-way' ? $t('Một chiều') : $t('Khứ hồi') }}</div>
                        <div class="col-md-6"><strong>{{ $t('Người liên hệ') }}:</strong> {{ selectedOrder.contact_name }}</div>
                        <div class="col-md-6"><strong>{{ $t('Email') }}:</strong> {{ selectedOrder.contact_email }}</div>
                        <div class="col-md-6"><strong>{{ $t('Số điện thoại') }}:</strong> {{ selectedOrder.contact_phone }}</div>
                      </div>
                    </div>

                    <!-- Departure Tickets -->
                    <div class="col-12">
                      <h4 class="fw-bold text-dark">{{ $t('Chi tiết vé đi') }}</h4>
                      <el-table :data="selectedOrder.order_details.departure || []" class="table table-bordered mt-3">
                        <el-table-column prop="schedule_route" :label="$t('Lộ trình')" />
                        <el-table-column prop="departure_time" :label="$t('Giờ khởi hành')" />
                        <el-table-column prop="arrival_time" :label="$t('Giờ đến')" />
                        <el-table-column prop="train_code" :label="$t('Tên tàu')" width="80" />
                        <el-table-column prop="car_name" :label="$t('Tên toa')" />
                        <el-table-column prop="seat_number" :label="$t('Chỗ ngồi')" width="120"/>
                        <el-table-column prop="ticket_type" :label="$t('Loại vé')">
                          <template #default="scope">
                            {{ scope.row.ticket_type }}
                          </template>
                        </el-table-column>
                        <el-table-column :label="$t('Giá')">
                          <template #default="scope">
                            {{ formatTotalPrice(scope.row.price) }}
                          </template>
                        </el-table-column>
                      </el-table>
                    </div>

                    <!-- Return Tickets (if applicable) -->
                    <div v-if="selectedOrder.order_details.return" class="col-12">
                      <h4 class="fw-bold text-dark">{{ $t('Chi tiết vé về') }}</h4>
                      <el-table :data="selectedOrder.order_details.return" class="table table-bordered mt-3">
                        <el-table-column prop="schedule_route" :label="$t('Lộ trình')" />
                        <el-table-column prop="departure_time" :label="$t('Giờ khởi hành')" />
                        <el-table-column prop="arrival_time" :label="$t('Giờ đến')" />
                        <el-table-column prop="train_code" :label="$t('Tên tàu')" width="80"/>
                        <el-table-column prop="car_name" :label="$t('Tên toa')" />
                        <el-table-column prop="seat_number" :label="$t('Chỗ ngồi')" width="120" />
                        <el-table-column prop="ticket_type" :label="$t('Loại vé')">
                          <template #default="scope">
                            {{ scope.row.ticket_type }}
                          </template>
                        </el-table-column>
                        <el-table-column :label="$t('Giá')">
                          <template #default="scope">
                            {{ formatTotalPrice(scope.row.price) }}
                          </template>
                        </el-table-column>
                      </el-table>
                    </div>

                    <!-- Passenger Information -->
                    <div class="col-12">
                      <h4 class="fw-bold text-dark">{{ $t('Thông tin hành khách') }}</h4>
                      <el-table :data="selectedOrder.order_details.departure || []" class="table table-bordered mt-3">
                        <el-table-column :label="$t('STT')" width="100">
                          <template #default="scope">
                            {{ scope.$index + 1 }}
                          </template>
                        </el-table-column>
                        <el-table-column prop="passenger.name" :label="$t('Tên hành khách')" width="200" />
                        <el-table-column prop="passenger.birthdate" :label="$t('Ngày sinh')" width="200" />
                        <el-table-column prop="passenger.cccd" :label="$t('CCCD')" width="200" />
                      </el-table>
                    </div>
                  </div>
                </el-dialog>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ElNotification } from 'element-plus';
import { useFormatPrice } from '@/composables/useFormatprice.js';
import QrcodeVue from 'qrcode.vue';

const authStore = useAuthStore();
const router = useRouter();
const loading = ref(false);
const orders = ref([]);
const activeMenu = ref('profile');
const isEditing = ref(false);
const dialogVisible = ref(false);
const qrDialogVisible = ref(false);
const selectedOrder = ref(null);
const { formatTotalPrice } = useFormatPrice();
const form = ref({
  user_id: null,
  name: '',
  email: '',
  phone_number: '',
  address: '',
  birth_date: '',
});

const rules = {
  name: [
    { required: true, message: 'Vui lòng nhập họ và tên', trigger: 'blur' },
    { pattern: /^[a-zA-ZÀ-ỹ]+(\s[a-zA-ZÀ-ỹ]+)+$/, message: 'Nhập đầy đủ họ và tên', trigger: 'blur' },
  ],
  email: [
    { required: true, message: 'Vui lòng nhập email', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9._%+-]+@gmail\.com$/, message: 'Email không hợp lệ', trigger: 'blur' },
  ],
  phone_number: [
    { required: true, message: 'Vui lòng nhập số điện thoại', trigger: 'blur' },
    { pattern: /(84|0[3|5|7|8|9])+([0-9]{8})\b/, message: 'Số điện thoại không hợp lệ', trigger: 'blur' },
  ],
  address: [
    { required: true, message: 'Vui lòng nhập địa chỉ', trigger: 'blur' },
  ],
  birth_date: [
    { required: true, message: 'Vui lòng chọn ngày sinh', trigger: 'blur' },
  ],
};

//lấy những đơn hàng thanh toán thành công
const filteredOrders = computed(() => {
  return orders.value.filter(order => order.status === 'completed');
});

// Check login status
if (!authStore.isLoggedIn) {
  ElNotification.error('Vui lòng đăng nhập để truy cập trang này');
  router.push('/Signin');
}

// Fetch user info and orders
onMounted(async () => {
  loading.value = true;
  try {
    const userResponse = await axios.get(`/user?email`);
    if (userResponse.data.success) {
      const { id, name, email, phone_number, address, birth_date } = userResponse.data.user;
      authStore.login(id, name, email, phone_number, address, birth_date);
      form.value = { user_id: id, name, email, phone_number, address, birth_date };
    }

    const ordersResponse = await axios.get('/orders', {
      params: { user_id: authStore.userId },
    });
    orders.value = ordersResponse.data.orders || [];
  } catch (error) {
    ElNotification.error('Không thể tải thông tin tài khoản hoặc đơn hàng');
  } finally {
    loading.value = false;
  }
});

// Handle menu selection
const handleMenuSelect = (index) => {
  activeMenu.value = index;
};

// Enable edit mode
const editProfile = () => {
  isEditing.value = true;
};

// Cancel edit
const cancelEdit = () => {
  isEditing.value = false;
  form.value = {
    user_id: authStore.userId,
    name: authStore.userName,
    email: authStore.userEmail,
    phone_number: authStore.phoneNumber,
    address: authStore.address,
    birth_date: authStore.birthDate,
  };
};

// Save profile
const saveProfile = async () => {
  loading.value = true;
  try {
    if (!authStore.userId) {
      throw new Error('Không tìm thấy ID người dùng');
    }
    const response = await axios.put('/update', {
      user_id: authStore.userId,
      name: form.value.name,
      email: form.value.email,
      phone_number: form.value.phone_number,
      address: form.value.address,
      birth_date: form.value.birth_date,
    });

    const userResponse = await axios.get(`/user?email=${form.value.email}`);
    if (userResponse.data.success) {
      const { id, name, email, phone_number, address, birth_date } = userResponse.data.user;
      authStore.login(id, name, email, phone_number, address, birth_date);
      form.value = { user_id: id, name, email, phone_number, address, birth_date };
    } else {
      throw new Error('Không thể lấy thông tin người dùng mới nhất');
    }

    isEditing.value = false;
    ElNotification.success(response.data.message || 'Cập nhật thông tin thành công');
  } catch (error) {
    const errorMessage = error.response?.data?.message || error.message || 'Cập nhật thông tin thất bại';
    ElNotification.error(errorMessage);
  } finally {
    loading.value = false;
  }
};

// View QR code only
const viewQRCode = (order) => {
  selectedOrder.value = order;
  qrDialogVisible.value = true;
};

// View order details
const viewOrderDetails = (order) => {
  selectedOrder.value = order;
  dialogVisible.value = true;
};

// Get status tag type
const getStatusTagType = (checkin) => {
  switch (checkin) {
    case 'used':
      return 'success';
    case 'unused':
      return 'warning';
    default:
      return '';
  }
};
</script>

<style scoped>
@import url(@/assets/css/account.css);
</style>  