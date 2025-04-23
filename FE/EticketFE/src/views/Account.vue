<template>
  <div class="account-container">
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
    </div>

    <!-- Header cho mobile (dropdown menu) -->
    <div class="mobile-header">
      <el-dropdown @command="handleMenuSelect" trigger="click">
        <el-button type="primary" class="dropdown-button">
          {{ activeMenu === 'profile' ? 'Thông tin cá nhân' : 'Lịch sử đơn hàng' }}
          <i class="el-icon-arrow-down el-icon--right"></i>
        </el-button>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item command="profile">Thông tin cá nhân</el-dropdown-item>
            <el-dropdown-item command="orders">Lịch sử đơn hàng</el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>

    <el-row :gutter="20" class="main-layout">
      <!-- Sidebar cho desktop -->
      <el-col :span="sidebarSpan" :xs="0" :sm="6" :md="6" :lg="6" class="sidebar-col">
        <div class="sidebar">
          <h2 class="sidebar-title">Hồ sơ của tôi</h2>
          <el-menu :default-active="activeMenu" class="sidebar-menu" @select="handleMenuSelect">
            <el-menu-item index="profile">
              <i class="el-icon-user"></i>
              <span>Thông tin cá nhân</span>
            </el-menu-item>
            <el-menu-item index="orders">
              <i class="el-icon-tickets"></i>
              <span>Lịch sử đơn hàng</span>
            </el-menu-item>
          </el-menu>
        </div>
      </el-col>

      <!-- Nội dung chính -->
      <el-col :span="contentSpan" :xs="24" :sm="18" :md="18" :lg="18">
        <div class="main-content">
          <!-- Nội dung Thông tin cá nhân -->
          <div v-if="activeMenu === 'profile'" class="content-section">
            <h2>Thông tin cá nhân</h2>
            <el-form v-if="isEditing" :model="form" label-width="120px" :rules="rules">
              <el-form-item label="Tên" prop="name" required >
                <el-input v-model="form.name"></el-input>
              </el-form-item>
              <el-form-item label="Email" prop="email" required>
                <el-input v-model="form.email"></el-input>
              </el-form-item>
              <el-form-item label="Số điện thoại" prop="phone_number" required>
                <el-input v-model="form.phone_number"></el-input>
              </el-form-item>
              <el-form-item label="Địa chỉ" prop="address" required>
                <el-input v-model="form.address"></el-input>
              </el-form-item>
              <el-form-item label="Ngày sinh" prop="birth_date" required>
                <el-date-picker v-model="form.birth_date" type="date" placeholder="Chọn ngày sinh" format="YYYY-MM-DD" value-format="YYYY-MM-DD"></el-date-picker>
              </el-form-item>
              <el-form-item>
                <el-button type="primary" @click="saveProfile">Lưu</el-button>
                <el-button @click="cancelEdit">Hủy</el-button>
              </el-form-item>
            </el-form>
            <div v-else>
              <p><strong>Tên:</strong> {{ authStore.userName }}</p>
              <p><strong>Email:</strong> {{ authStore.userEmail }}</p>
              <p><strong>Số điện thoại:</strong> {{ authStore.phoneNumber || 'Chưa cập nhật' }}</p>
              <p><strong>Địa chỉ:</strong> {{ authStore.address || 'Chưa cập nhật' }}</p>
              <p><strong>Ngày sinh:</strong> {{ authStore.birthDate || 'Chưa cập nhật' }}</p>
              <el-button type="primary" @click="editProfile">Chỉnh sửa</el-button>
            </div>
          </div>

          <!-- Nội dung Lịch sử đơn hàng -->
          <div v-if="activeMenu === 'orders'" class="content-section">
            <h2>Lịch sử đơn hàng</h2>
            <el-table :data="orders" style="width: 100%" v-if="orders.length">
              <el-table-column prop="transaction_id" label="Mã đơn hàng" width="150" />
              <el-table-column prop="created_at" label="Ngày đặt" width="150" />
              <el-table-column  label="Tổng tiền" width="120" >
                <template #default="scope">
                  {{ formatTotalPrice(scope.row.total_amount) }}
                </template>
              </el-table-column>
              <el-table-column prop="status" label="Trạng thái" width="120">
                <template #default="scope">
                  <el-tag :type="getStatusTagType(scope.row.status)">
                    {{ scope.row.status === 'pending' ? 'Đang xử lý' : scope.row.status === 'completed' ? 'Hoàn tất' : 'Thất bại' }}
                  </el-tag>
                </template>
              </el-table-column>
              <el-table-column label="Chi tiết" width="100">
                <template #default="scope">
                  <el-button type="text" @click="viewOrderDetails(scope.row)">Xem chi tiết</el-button>
                </template>
              </el-table-column>
            </el-table>
            <p v-else>Chưa có đơn hàng nào.</p>

            <!-- Dialog hiển thị chi tiết đơn hàng -->
            <el-dialog title="Chi tiết đơn hàng" v-model="dialogVisible" width="70%">
              <div v-if="selectedOrder">
                <h3>Thông tin đơn hàng</h3>
                <p><strong>Mã đơn hàng:</strong> {{ selectedOrder.transaction_id }}</p>
                <p><strong>Ngày đặt:</strong> {{ selectedOrder.created_at }}</p>
                <p><strong>Tổng tiền:</strong> {{ formatTotalPrice(selectedOrder.total_amount) }} VND</p>
                <p><strong>Trạng thái:</strong> {{ selectedOrder.status === 'pending' ? 'Đang xử lý' : selectedOrder.status === 'completed' ? 'Hoàn tất' : 'Thất bại' }}</p>
                <p><strong>Loại vé:</strong> {{ selectedOrder.ticket_type === 'one-way' ? 'Một chiều' : 'Khứ hồi' }}</p>
                <p><strong>Người liên hệ:</strong> {{ selectedOrder.contact_name }}</p>
                <p><strong>Email:</strong> {{ selectedOrder.contact_email }}</p>
                <p><strong>Số điện thoại:</strong> {{ selectedOrder.contact_phone }}</p>

                <h3>Chi tiết vé đi</h3>
                <el-table :data="selectedOrder.order_details.departure || []" style="width: 100%">
                  <el-table-column prop="schedule_route" label="Lộ trình" />
                  <el-table-column prop="departure_time" label="Giờ khởi hành" />
                  <el-table-column prop="arrival_time" label="Giờ đến" />
                  <el-table-column prop="train_code" label="Tên tàu" />
                  <el-table-column prop="car_name" label="Tên toa" />
                  <el-table-column prop="seat_number" label="Số ghế" />
                  <el-table-column prop="ticket_type" label="Loại vé">
                    <template #default="scope">
                      {{ scope.row.ticket_type === 'adult' ? 'Người lớn' : 'Trẻ em' }}
                    </template>
                  </el-table-column>
                  <el-table-column label="Giá" >
                    <template #default="scope">
                      {{ formatTotalPrice(scope.row.price) }}
                    </template>
                  </el-table-column>
                </el-table>

                <h3 v-if="selectedOrder.order_details.return">Chi tiết vé về</h3>
                <el-table v-if="selectedOrder.order_details.return" :data="selectedOrder.order_details.return" style="width: 100%">
                  <el-table-column prop="schedule_route" label="Lộ trình" />
                  <el-table-column prop="departure_time" label="Giờ khởi hành" />
                  <el-table-column prop="arrival_time" label="Giờ đến" />
                  <el-table-column prop="train_code" label="Mã tàu" />
                  <el-table-column prop="car_name" label="Tên toa" />
                  <el-table-column prop="seat_number" label="Số ghế" />
                  <el-table-column prop="ticket_type" label="Loại vé">
                    <template #default="scope">
                      {{ scope.row.ticket_type === 'adult' ? 'Người lớn' : 'Trẻ em' }}
                    </template>
                  </el-table-column>
                  <el-table-column prop="price" label="Giá" />
                </el-table>

                <h3>Thông tin hành khách</h3>
                <el-table :data="selectedOrder.order_details.departure || []" style="width: 100%">
                  <el-table-column prop="passenger.name" label="Tên hành khách" />
                  <el-table-column prop="passenger.birthdate" label="Ngày sinh" />
                  <el-table-column prop="passenger.cccd" label="CCCD" />
                </el-table>
              </div>
            </el-dialog>
          </div>
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ElNotification } from 'element-plus';
import {useFormatPrice} from '@/composables/useFormatprice.js'; // Giả sử bạn đã định nghĩa useFormatPrice trong một file khác

const authStore = useAuthStore();
const router = useRouter();
const loading = ref(false);
const orders = ref([]);
const activeMenu = ref('profile');
const sidebarSpan = ref(6);
const contentSpan = ref(18);
const isEditing = ref(false);
const dialogVisible = ref(false);
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
  name :[
    { required: true, message: 'Vui lòng nhập họ và tên', trigger: 'blur' },
    { pattern: /^[a-zA-ZÀ-ỹ]+(\s[a-zA-ZÀ-ỹ]+)+$/, message: 'Nhập đầy đủ họ và tên', trigger: 'blur' },
  ],
  email: [
    { required: true, message: 'Vui lòng nhập email', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9._%+-]+@gmail\.com$/, message: 'Email không hợp lệ', trigger: 'blur' },
  ],
  phone_number: [
    { required: true, message: 'Vui lòng nhập số điện thoại', trigger: 'blur' },
    { pattern: /(84|0[3|5|7|8|9])+([0-9]{8})\b/, message: 'Số điện thoại không hợp lệ', trigger: 'blur' }
  ],
  address: [
    { required: true, message: 'Vui lòng nhập địa chỉ', trigger: 'blur' },
  ],
  birt_hdate: [
    { required: true, message: 'Vui lòng chọn ngày sinh', trigger: 'blur' },
  ],
}

// Kiểm tra trạng thái đăng nhập
if (!authStore.isLoggedIn) {
  ElNotification.error('Vui lòng đăng nhập để truy cập trang này');
  router.push('/Signin');
}

// Lấy thông tin người dùng và lịch sử mua hàng
onMounted(async () => {
  loading.value = true;
  try {
    const userResponse = await axios.get(`/user?email=${localStorage.getItem('userEmail')}`);
    if (userResponse.data.success) {
      const { id, name, email, phone_number, address, birth_date } = userResponse.data.user;
      authStore.login(id, name, email, phone_number, address, birth_date);
      form.value = { user_id: id, name, email, phone_number, address, birth_date };
    }

    // Lấy danh sách đơn hàng
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

// Hàm xử lý khi chọn menu
const handleMenuSelect = (index) => {
  activeMenu.value = index;
};

// Hàm kích hoạt chế độ chỉnh sửa
const editProfile = () => {
  isEditing.value = true;
};

// Hàm hủy chỉnh sửa
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

// Hàm lưu thông tin
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

    // Lấy thông tin người dùng mới nhất từ server
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

// Hàm xem chi tiết đơn hàng
const viewOrderDetails = (order) => {
  selectedOrder.value = order;
  dialogVisible.value = true;
};

// Hàm xác định loại tag cho trạng thái
const getStatusTagType = (status) => {
  switch (status) {
    case 'completed':
      return 'success';
    case 'pending':
      return 'warning';
    case 'failed':
      return 'danger';
    default:
      return '';
  }
};
</script>

<style scoped>
@import url(@/assets/css/account.css);
</style>