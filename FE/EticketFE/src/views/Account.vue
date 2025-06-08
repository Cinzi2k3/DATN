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
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <h3 class="fw-bold text-dark mb-0">{{ $t('Lịch sử đơn hàng') }}</h3>
                  
                  <!-- Orders per page selector -->
                  <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">{{ $t('Hiển thị') }}:</span>
                    <el-select v-model="pageSize" @change="handlePageSizeChange" size="small" style="width: 80px">
                      <el-option :value="5" label="5"></el-option>
                      <el-option :value="10" label="10"></el-option>
                      <el-option :value="20" label="20"></el-option>
                      <el-option :value="50" label="50"></el-option>
                    </el-select>
                  </div>
                </div>

                <!-- Orders table -->
                <el-table :data="paginatedOrders" v-if="filteredOrders.length" class="table table-hover mb-4">
                  <el-table-column label="STT" width="70">
                    <template #default="scope">
                      {{ (currentPage - 1) * pageSize + scope.$index + 1 }}
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

                <!-- Pagination Component -->
                <div v-if="filteredOrders.length > 0" class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
                  <!-- Orders info -->
                  <div class="text-muted small">
                    {{ $t('Hiển thị') }} {{ (currentPage - 1) * pageSize + 1 }} - {{ Math.min(currentPage * pageSize, filteredOrders.length) }} {{ $t('của') }} {{ filteredOrders.length }} {{ $t('đơn hàng') }}
                  </div>

                  <!-- Pagination controls -->
                  <div class="pagination-container">
                    <nav aria-label="Pagination">
                      <ul class="pagination pagination-modern mb-0">
                        <!-- First page -->
                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                          <button class="page-link" @click="goToPage(1)" :disabled="currentPage === 1">
                            <i class="fas fa-angle-double-left"></i>
                          </button>
                        </li>
                        
                        <!-- Previous page -->
                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                          <button class="page-link" @click="previousPage" :disabled="currentPage === 1">
                            <i class="fas fa-angle-left"></i>
                          </button>
                        </li>

                        <!-- Page numbers -->
                        <li 
                          v-for="page in visiblePages" 
                          :key="page" 
                          class="page-item" 
                          :class="{ active: page === currentPage }"
                        >
                          <button class="page-link" @click="goToPage(page)">
                            {{ page }}
                          </button>
                        </li>

                        <!-- Next page -->
                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                          <button class="page-link" @click="nextPage" :disabled="currentPage === totalPages">
                            <i class="fas fa-angle-right"></i>
                          </button>
                        </li>
                        
                        <!-- Last page -->
                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                          <button class="page-link" @click="goToPage(totalPages)" :disabled="currentPage === totalPages">
                            <i class="fas fa-angle-double-right"></i>
                          </button>
                        </li>
                      </ul>
                    </nav>
                  </div>

                  <!-- Quick page jump -->
                  <div class="d-flex align-items-center gap-2" v-if="totalPages > 10">
                    <span class="text-muted small">{{ $t('Đến trang') }}:</span>
                    <el-input 
                      v-model="jumpToPage" 
                      size="small" 
                      style="width: 70px"
                      @keyup.enter="handlePageJump"
                      type="number"
                      :min="1"
                      :max="totalPages"
                    />
                    <el-button size="small" @click="handlePageJump">{{ $t('Đi') }}</el-button>
                  </div>
                </div>

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

// Pagination variables
const currentPage = ref(1);
const pageSize = ref(10);
const jumpToPage = ref('');

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

// Filtered orders (successful payments only)
const filteredOrders = computed(() => {
  return orders.value.filter(order => order.status === 'completed');
});

// Pagination computed properties
const totalPages = computed(() => {
  return Math.ceil(filteredOrders.value.length / pageSize.value);
});

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredOrders.value.slice(start, end);
});

const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const pages = [];
  
  if (total <= 7) {
    // Show all pages if total is 7 or less
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    // Show smart pagination
    if (current <= 4) {
      // Show first 5 pages
      for (let i = 1; i <= 5; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(total);
    } else if (current >= total - 3) {
      // Show last 5 pages
      pages.push(1);
      pages.push('...');
      for (let i = total - 4; i <= total; i++) {
        pages.push(i);
      }
    } else {
      // Show middle pages
      pages.push(1);
      pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(total);
    }
  }
  
  return pages;
});

// Pagination methods
const goToPage = (page) => {
  if (page !== '...' && page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const handlePageSizeChange = (newSize) => {
  pageSize.value = newSize;
  currentPage.value = 1; // Reset to first page
};

const handlePageJump = () => {
  const page = parseInt(jumpToPage.value);
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    jumpToPage.value = '';
  }
};

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
  if (index === 'orders') {
    currentPage.value = 1; // Reset pagination when switching to orders
  }
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

/* Modern Pagination Styles */
.pagination-modern {
  gap: 0.25rem;
}

.pagination-modern .page-link {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 0.5rem 0.75rem;
  color: #6c757d;
  background-color: #fff;
  transition: all 0.2s ease-in-out;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  height: 40px;
  font-weight: 500;
}

.pagination-modern .page-link:hover {
  color: #0d6efd;
  background-color: #e7f1ff;
  border-color: #0d6efd;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(13, 110, 253, 0.15);
}

.pagination-modern .page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: #fff;
  box-shadow: 0 3px 6px rgba(13, 110, 253, 0.3);
}

.pagination-modern .page-item.disabled .page-link {
  color: #adb5bd;
  background-color: #f8f9fa;
  border-color: #dee2e6;
  cursor: not-allowed;
}

.pagination-modern .page-item.disabled .page-link:hover {
  transform: none;
  box-shadow: none;
}

/* Responsive pagination */
@media (max-width: 576px) {
  .pagination-modern .page-link {
    padding: 0.375rem 0.5rem;
    min-width: 35px;
    height: 35px;
    font-size: 0.875rem;
  }
  
  .pagination-container {
    overflow-x: auto;
    padding: 0.5rem 0;
  }
  
  .pagination-modern {
    flex-wrap: nowrap;
    min-width: max-content;
  }
}

/* Table responsive improvements */
@media (max-width: 768px) {
  .el-table {
    font-size: 0.875rem;
  }
  
  .el-table .el-table__cell {
    padding: 8px 4px;
  }
}

/* Enhanced hover effects for buttons */
.el-button {
  transition: all 0.2s ease-in-out;
}

.el-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Loading animation enhancement */
.spinner-border {
  animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
  to {
    transform: rotate(360deg);
  }
}

/* Card shadow enhancement */
.card {
  transition: box-shadow 0.3s ease-in-out;
}

.card:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Status tag animations */
.el-tag {
  transition: all 0.2s ease-in-out;
}

.el-tag:hover {
  transform: scale(1.05);
}

/* Input focus states */
.el-input__inner:focus,
.el-select .el-input__inner:focus {
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Smooth transitions for menu items */
.nav-link,
.el-menu-item {
  transition: all 0.2s ease-in-out;
}

/* Dialog enhancements */
.el-dialog {
  border-radius: 12px;
  overflow: hidden;
}

.el-dialog__header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem 2rem;
}

.el-dialog__title {
  color: white;
  font-weight: 600;
}

/* QR Code dialog special styling */
.qr-dialog .el-dialog__body {
  padding: 2rem;
  text-align: center;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

/* Page size selector styling */
.el-select .el-input {
  border-radius: 6px;
}

.el-select .el-input__inner {
  border: 1px solid #d1d5db;
  transition: all 0.2s ease-in-out;
}

.el-select .el-input__inner:hover {
  border-color: #0d6efd;
}

/* Jump to page input styling */
.pagination-jump input {
  border-radius: 6px;
  border: 1px solid #d1d5db;
  text-align: center;
}

/* Mobile responsiveness for pagination info */
@media (max-width: 767px) {
  .pagination-container .d-flex {
    flex-direction: column;
    gap: 1rem;
  }
  
  .pagination-container .text-muted {
    text-align: center;
    order: 2;
  }
  
  .pagination-container nav {
    order: 1;
  }
  
  .pagination-container .d-flex.align-items-center.gap-2 {
    order: 3;
    justify-content: center;
  }
}
</style>