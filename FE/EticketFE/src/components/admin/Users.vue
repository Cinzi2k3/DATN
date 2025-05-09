<template>
    <div class="users-management">
      <h2 class="mb-4">Quản lý người dùng</h2>
  
      <!-- Bảng danh sách người dùng -->
      <el-card class="users-card">
        <template #header>
          <div class="card-header">
            <i class="fas fa-users me-2"></i> Danh sách người dùng
          </div>
        </template>
        <el-table :data="paginatedUsers" style="width: 100%" border stripe v-loading="loading" @row-click="showUserDetails">
          <el-table-column prop="id" label="ID" width="40" />
          <el-table-column prop="name" label="Tên" min-width="100" />
          <el-table-column prop="email" label="Email" min-width="130" show-overflow-tooltip />
          <el-table-column prop="phone_number" label="Số điện thoại" width="120" />
          <el-table-column prop="address" label="Địa chỉ" min-width="200" show-overflow-tooltip />
          <el-table-column label="Ngày sinh" width="120">
            <template #default="{ row }">
              {{ formatDate(row.birth_date) }}
            </template>
          </el-table-column>
        </el-table>
  
        <!-- Phân trang -->
        <div class="pagination-container mt-4">
          <el-pagination
            background
            layout="total, sizes, prev, pager, next"
            :total="users.length"
            :page-size="pageSize"
            :page-sizes="[10, 20, 50]"
            v-model:current-page="currentPage"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </el-card>
  
      <!-- Modal chi tiết người dùng và đơn hàng -->
      <el-dialog
        title="Chi tiết người dùng"
        v-model="dialogVisible"
        width="73%"
        :before-close="handleClose"
      >
        <div v-if="selectedUser" v-loading="detailsLoading">
          <!-- Thông tin người dùng -->
          <h4>Thông tin cá nhân</h4>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="ID">{{ selectedUser.id }}</el-descriptions-item>
            <el-descriptions-item label="Tên">{{ selectedUser.name }}</el-descriptions-item>
            <el-descriptions-item label="Email">{{ selectedUser.email }}</el-descriptions-item>
            <el-descriptions-item label="Số điện thoại">{{ selectedUser.phone_number }}</el-descriptions-item>
            <el-descriptions-item label="Địa chỉ">{{ selectedUser.address }}</el-descriptions-item>
            <el-descriptions-item label="Ngày sinh">{{formatDate(selectedUser.birth_date) }}</el-descriptions-item>
          </el-descriptions>
  
          <!-- Danh sách đơn hàng -->
          <div v-if="userOrders.length">
            <h4 class="mt-4">Đơn hàng đã đặt</h4>
          <el-table :data="userOrders" style="width: 100%" border stripe @row-click="showOrderDetails">
            <el-table-column prop="id" label="ID" width="73" />
            <el-table-column prop="transaction_id" label="Mã giao dịch" width="150" />
            <el-table-column prop="contact_name" label="Tên liên hệ" width="150" />
            <el-table-column prop="contact_email" label="Email" width="220" />
            <el-table-column prop="contact_phone" label="Số điện thoại" width="150" />
            <el-table-column prop="total_amount" label="Tổng tiền" width="120">
              <template #default="{ row }">
                {{ formatCurrency(row.total_amount) }}
              </template>
            </el-table-column>
            <el-table-column prop="status" label="Trạng thái" width="120">
              <template #default="{ row }">
                <el-tag :type="getStatusType(row.status)" effect="dark" size="large">
                    {{ row.status === 'completed' ? 'Hoàn thành' : row.status === 'pending' ? 'Chờ xử lý' : row.status }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="Ngày tạo" width="100" >
              <template #default="{ row }">
                {{ formatTime(row.created_at) }} {{ formatDate(row.created_at) }} 
              </template>
            </el-table-column>
          </el-table>
          </div>
          <div v-else class=" mt-4">
            <h4 class="mt-4">Đơn hàng đã đặt</h4>
            <el-empty description="Không có đơn hàng nào" />
        </div>
        </div>
        <template #footer>
          <span class="dialog-footer">
            <el-button @click="dialogVisible = false">Đóng</el-button>
          </span>
        </template>
      </el-dialog>
  
      <!-- Modal chi tiết đơn hàng -->
      <el-dialog
        v-model="orderDialogVisible"
        title="Chi tiết đơn hàng"
        width="80%"
        destroy-on-close
        @closed="handleOrderClose"
      >
        <div v-loading="detailsLoading" v-if="selectedOrder" class="order-details">
          <!-- Header thông tin đơn hàng -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <el-tag :type="getCheckinType(selectedOrder.checkin)" effect="dark" size="large">
                {{ selectedOrder.checkin === 'used' ? 'Đã check-in' : selectedOrder.checkin === 'unused' ? 'Chưa check-in' : selectedOrder.checkin }}
              </el-tag>
            </div>
            <div>
              <h3>Đơn hàng #{{ selectedOrder.id }}</h3>
              <p class="text-muted">Mã giao dịch: {{ selectedOrder.transaction_id }}</p>
            </div>
            <div>
              <p><i class="fas fa-calendar me-1"></i> {{ formatDate(selectedOrder.created_at) }}</p>
              <p><i class="fas fa-clock me-1"></i> {{ formatTime(selectedOrder.created_at) }}</p>
            </div>
          </div>

          <!-- Thông tin chi tiết -->
          <div class="row g-3 mt-4">
            <!-- Thông tin liên hệ -->
            <div class="col-md-6">
              <div class="card h-100">
                <div class="card-header">
                  <i class="fas fa-user me-2"></i> Thông tin liên hệ
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <strong>Tên:</strong> {{ selectedOrder.contact_name }}
                  </div>
                  <div class="mb-3">
                    <strong>Email:</strong> {{ selectedOrder.contact_email }}
                  </div>
                  <div>
                    <strong>Số điện thoại:</strong> {{ selectedOrder.contact_phone }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Thông tin thanh toán -->
            <div class="col-md-6">
              <div class="card h-100">
                <div class="card-header">
                  <i class="fas fa-credit-card me-2"></i> Thông tin thanh toán
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <strong>Tổng tiền:</strong>
                    <span class="text-danger fw-bold">{{ formatCurrency(selectedOrder.total_amount) }}</span>
                  </div>
                  <div class="mb-3">
                    <strong>Loại vé:</strong> {{ selectedOrder.ticket_type }}
                  </div>
                  <div>
                    <strong>Thanh toán:</strong> {{ selectedOrder.payment_method || "Chuyển khoản VNPay" }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Chi tiết chuyến đi -->
          <h4 class="mt-4 mb-3">
            <i class="fas fa-route me-2"></i> Chi tiết chuyến đi
          </h4>
          <el-tabs type="border-card">
            <el-tab-pane label="Chiều đi">
              <div v-if="selectedOrder.order_details?.departure?.length">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <div class="fw-bold">
                      {{ selectedOrder.order_details.departure[0]?.schedule_route || 'Chưa có thông tin' }}
                    </div>
                    <div class="text-muted">
                      <span>
                        {{ formatTime(selectedOrder.order_details.departure[0]?.departure_time || '--:--' )}}
                        {{ formatDate(selectedOrder.order_details.departure[0]?.departure_time || '--:--' )}}
                      </span>
                      <i class="fas fa-long-arrow-alt-right mx-2"></i>
                      <span>
                        {{ formatTime(selectedOrder.order_details.departure[0]?.arrival_time || '--:--' )}}
                        {{ formatDate(selectedOrder.order_details.departure[0]?.arrival_time || '--:--' )}}
                      </span>
                    </div>
                  </div>
                  <div>
                    <div class="fw-bold">
                      <i class="fas fa-train me-1"></i> Tàu {{ selectedOrder.order_details.departure[0]?.train_code || 'N/A' }}
                    </div>
                    <div class="text-muted">
                      {{ selectedOrder.order_details.departure[0]?.train_type || 'Không xác định' }}
                    </div>
                  </div>
                </div>

                <el-table
                  :data="selectedOrder.order_details.departure"
                  style="width: 100%"
                  stripe
                  class="mt-3"
                >
                  <el-table-column label="Thông tin hành khách" min-width="250">
                    <template #default="{ row }">
                      <div>
                        <div class="fw-bold">
                          <i class="fas fa-user me-1"></i> {{ row.passenger?.name || 'Chưa có thông tin' }}
                        </div>
                        <div>
                          <span v-if="row.passenger?.birthdate" class="me-3">
                            <i class="fas fa-birthday-cake me-1"></i> {{ formatDate(row.passenger.birthdate) }}
                          </span>
                          <span v-if="row.passenger?.cccd">
                            <i class="fas fa-id-card me-1"></i> {{ row.passenger.cccd }}
                          </span>
                        </div>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column label="Toa/Ghế" width="350">
                    <template #default="{ row }">
                      <div>
                        <div class="mb-2">{{ row.car_name }}</div>
                        <div>Ghế: {{ row.seat_number }}</div>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column prop="price" label="Giá vé" width="150">
                    <template #default="{ row }">
                      <span class="text-danger fw-bold">{{ formatCurrency(row.price) }}</span>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
              <el-empty description="Không có thông tin chiều đi" v-else></el-empty>
            </el-tab-pane>

            <el-tab-pane label="Chiều về" v-if="selectedOrder.order_details?.return?.length">
              <div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <div class="fw-bold">
                      {{ selectedOrder.order_details.return[0]?.schedule_route || 'Chưa có thông tin' }}
                    </div>
                    <div class="text-muted">
                        <span>
                        {{ formatTime(selectedOrder.order_details.return[0]?.departure_time || '--:--' )}}
                        {{ formatDate(selectedOrder.order_details.return[0]?.departure_time || '--:--' )}}
                      </span>
                      <i class="fas fa-long-arrow-alt-right mx-2"></i>
                      <span>
                        {{ formatTime(selectedOrder.order_details.return[0]?.arrival_time || '--:--' )}}
                        {{ formatDate(selectedOrder.order_details.return[0]?.arrival_time || '--:--' )}}
                      </span>
                    </div>
                  </div>
                  <div>
                    <div class="fw-bold">
                      <i class="fas fa-train me-1"></i> Tàu {{ selectedOrder.order_details.return[0]?.train_code || 'N/A' }}
                    </div>
                    <div class="text-muted">
                      {{ selectedOrder.order_details.return[0]?.train_type || 'Không xác định' }}
                    </div>
                  </div>
                </div>

                <el-table
                  :data="selectedOrder.order_details.return"
                  style="width: 100%"
                  stripe
                  class="mt-3"
                >
                  <el-table-column label="Thông tin hành khách" min-width="250">
                    <template #default="{ row }">
                      <div>
                        <div class="fw-bold">
                          <i class="fas fa-user me-1"></i> {{ row.passenger?.name || 'Chưa có thông tin' }}
                        </div>
                        <div>
                          <span v-if="row.passenger?.birthdate" class="me-3">
                            <i class="fas fa-birthday-cake me-1"></i> {{ row.passenger.birthdate }}
                          </span>
                          <span v-if="row.passenger?.cccd">
                            <i class="fas fa-id-card me-1"></i> {{ row.passenger.cccd }}
                          </span>
                        </div>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column label="Toa/Ghế" width="350">
                    <template #default="{ row }">
                      <div>
                        <div class="mb-2">{{ row.car_name }}</div>
                        <div>Ghế: {{ row.seat_number }}</div>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column prop="price" label="Giá vé" width="150">
                    <template #default="{ row }">
                      <span class="text-danger fw-bold">{{ formatCurrency(row.price) }}</span>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </el-tab-pane>
          </el-tabs>

          <!-- Lịch sử đơn hàng -->
          <h4 class="mt-4 mb-3">
            <i class="fas fa-history me-2"></i> Lịch sử đơn hàng
          </h4>
          <el-timeline>
            <el-timeline-item
              v-for="(activity, index) in orderHistory"
              :key="index"
              :type="activity.type"
              :color="activity.color"
              :timestamp="activity.time"
            >
              {{ activity.content }}
            </el-timeline-item>
          </el-timeline>
        </div>
      </el-dialog>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import { ElMessage } from 'element-plus';
  import axios from 'axios';
  
  // State
  const users = ref([]);
  const loading = ref(false);
  const detailsLoading = ref(false);
  const dialogVisible = ref(false);
  const orderDialogVisible = ref(false);
  const selectedUser = ref(null);
  const userOrders = ref([]);
  const selectedOrder = ref(null);
  const currentPage = ref(1);
  const pageSize = ref(10);

  // Order history example data
  const orderHistory = ref([
    { time: '05/05/2025 10:25', type: 'success', color: '#67C23A', content: 'Đơn hàng đã được tạo thành công' },
    { time: '05/05/2025 10:26', type: 'primary', color: '#409EFF', content: 'Thanh toán đã được xác nhận' },
    { time: '05/05/2025 10:30', type: 'success', color: '#67C23A', content: 'Vé đã được gửi qua email cho khách hàng' },
  ]);
  
  // Computed
  const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    const end = start + pageSize.value;
    return users.value.slice(start, end);
  });
  
  // Methods
  const fetchUsers = async () => {
    loading.value = true;
    try {
      const response = await axios.get('/admin-users');
      if (response.data.success) {
        users.value = response.data.users;
      } else {
        ElMessage.error(response.data.message || 'Lấy danh sách người dùng thất bại');
      }
    } catch (error) {
      ElMessage.error('Lỗi khi lấy danh sách người dùng');
      console.error(error);
    } finally {
      loading.value = false;
    }
  };
  
  const fetchUserOrders = async (userId) => {
    detailsLoading.value = true;
    try {
      const response = await axios.get(`/orders?user_id=${userId}`);
      if (response.data.success) {
        userOrders.value = response.data.orders;
      } else {
        ElMessage.error(response.data.message || 'Lấy danh sách đơn hàng thất bại');
      }
    } catch (error) {
      ElMessage.error('Lỗi khi lấy danh sách đơn hàng');
      console.error(error);
    } finally {
      detailsLoading.value = false;
    }
  };
  
  const showUserDetails = (user) => {
    selectedUser.value = user;
    fetchUserOrders(user.id);
    dialogVisible.value = true;
  };
  
  const showOrderDetails = (order) => {
    selectedOrder.value = order;
    orderDialogVisible.value = true;
  };
  
  const handleClose = () => {
    dialogVisible.value = false;
    selectedUser.value = null;
    userOrders.value = [];
  };
  
  const handleOrderClose = () => {
    orderDialogVisible.value = false;
    selectedOrder.value = null;
    detailsLoading.value = false;
  };
  
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
    }).format(value);
  };

  const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  };

  const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('vi-VN', {
      hour: '2-digit',
      minute: '2-digit',
    });
  };
  
  const getStatusType = (status) => {
    switch (status) {
      case 'completed':
        return 'success';
      case 'pending':
        return 'warning';
      default:
        return 'info';
    }
  };

  const getCheckinType = (checkin) =>{
    switch (checkin){
      case 'used':
        return 'success';
      case 'unused':  
        return 'warning'; 
      default:
        return 'info';
    }
  };
  
  const handleSizeChange = (val) => {
    pageSize.value = val;
    currentPage.value = 1;
  };
  
  const handleCurrentChange = (val) => {
    currentPage.value = val;
  };
  
  onMounted(() => {
    fetchUsers();
  });
  </script>
  
  <style scoped>
  .users-management {
    max-width: 1200px;
  }
  
  .users-card .card-header {
    font-weight: 600;
    font-size: 18px;
  }
  
  .pagination-container {
    text-align: right;
  }
  </style>