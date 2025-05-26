<template>
  <div>
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2>Quản lý đơn hàng</h2>
          <p class="text-muted">Quản lý và theo dõi tất cả đơn đặt vé của khách hàng</p>
        </div>
      </div>

      <!-- Order Stats -->
      <div class="row g-3">
        <div class="col-12 col-sm-6 col-md-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center">
              <i class="fas fa-shopping-cart fa-2x me-3 text-primary"></i>
              <div>
                <h5 class="card-title mb-0">{{ orders.length }}</h5>
                <p class="card-text text-muted">Tổng đơn hàng</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center">
              <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
              <div>
                <h5 class="card-title mb-0">{{ getStatusCount('completed') }}</h5>
                <p class="card-text text-muted">Đã hoàn thành</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center">
              <i class="fas fa-clock fa-2x me-3 text-warning"></i>
              <div>
                <h5 class="card-title mb-0">{{ getStatusCount('pending') }}</h5>
                <p class="card-text text-muted">Chờ xử lý</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center">
              <i class="fas fa-times-circle fa-2x me-3 text-danger"></i>
              <div>
                <h5 class="card-title mb-0">{{ getStatusCount('Đã hủy') }}</h5>
                <p class="card-text text-muted">Đã hủy</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-sm-6 col-md-3">
            <el-input
              v-model="searchTerm"
              placeholder="Tìm kiếm theo mã, tên, email..."
              prefix-icon="el-icon-search"
              clearable
              @input="currentPage = 1"
            />
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <el-select
              v-model="statusFilter"
              placeholder="Trạng thái"
              clearable
              style="width: 100%"
              @change="currentPage = 1"
            >
              <el-option label="Tất cả" value="" />
              <el-option label="Hoàn thành" value="completed" />
              <el-option label="Chờ xử lý" value="pending" />
              <el-option label="Đã hủy" value="Đã hủy" />
            </el-select>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <el-date-picker
              v-model="dateRange"
              type="daterange"
              range-separator="→"
              start-placeholder="Từ ngày"
              end-placeholder="Đến ngày"
              style="width: 100%"
              @change="currentPage = 1"
            />
          </div>
          <div class="col-12 col-sm-6 col-md-3 d-flex align-items-center">
            <el-button type="primary" plain class="w-100 me-2" @click="applyFilters">
              <i class="fas fa-filter me-1"></i> Lọc
            </el-button>
            <el-button type="info" plain @click="resetFilters">
              <i class="fas fa-redo"></i>
            </el-button>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Danh sách đơn hàng</h3>
        <el-dropdown trigger="click">
          <el-button type="primary" plain size="small">
            <i class="fas fa-columns me-1"></i> Cột hiển thị <i class="el-icon-arrow-down"></i>
          </el-button>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item v-for="col in allColumns" :key="col.prop">
                <el-checkbox v-model="col.visible">{{ col.label }}</el-checkbox>
              </el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>
      </div>
      <div class="card-body">
        <el-table
          :data="paginatedOrders"
          style="width: 100%"
          stripe
          border
          highlight-current-row
          @row-click="showOrderDetails"
          v-loading="loading"
          empty-text="Không có dữ liệu đơn hàng"
        >
          <el-table-column prop="id" label="ID" width="80" v-if="getColumnVisible('id')" sortable />
          <el-table-column prop="transaction_id" label="Mã giao dịch" width="150" v-if="getColumnVisible('transaction_id')" />
          <el-table-column prop="contact_name" label="Tên liên hệ" min-width="150" v-if="getColumnVisible('contact_name')" />
          <el-table-column prop="contact_email" label="Email" min-width="180" v-if="getColumnVisible('contact_email')" show-overflow-tooltip />
          <el-table-column prop="contact_phone" label="Số điện thoại" width="120" v-if="getColumnVisible('contact_phone')" />
          <el-table-column prop="total_amount" label="Tổng tiền" width="120" v-if="getColumnVisible('total_amount')" sortable>
            <template #default="{ row }">
              <span class="fw-bold">{{ formatCurrency(row.total_amount) }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="status" label="Trạng thái" width="120" v-if="getColumnVisible('status')">
            <template #default="{ row }">
              <el-tag :type="getStatusType(row.status)" effect="dark" size="small">
                {{ row.status === 'completed' ? 'Hoàn thành' : row.status === 'pending' ? 'Chờ xử lý' : row.status }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="created_at" label="Ngày đặt" width="150" v-if="getColumnVisible('created_at')" sortable>
            <template #default="{ row }">
              <div>
                <div><i class="fas fa-calendar me-1"></i>{{ formatDate(row.created_at) }}</div>
                <div><i class="fas fa-clock me-1"></i>{{ formatTime(row.created_at) }}</div>
              </div>
            </template>
          </el-table-column>
        </el-table>

        <!-- Pagination -->
        <div class="d-flex justify-content-end mt-3">
          <el-pagination
            background
            layout="total, sizes, prev, pager, next, jumper"
            :total="filteredOrders.length"
            :page-size="pageSize"
            :page-sizes="[10, 20, 50, 100]"
            v-model:current-page="currentPage"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </div>
    </div>

    <!-- Modal chi tiết đơn hàng -->
    <el-dialog
      v-model="dialogVisible"
      title="Chi tiết đơn hàng"
      width="80%"
      destroy-on-close
      @closed="handleDialogClosed"
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
                  <strong>Loại vé:</strong> {{ selectedOrder.ticket_type || "Vé thường" }}
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
                          <i class="fas fa-birthday-cake me-1"></i> {{ row.passenger.birthdate }}
                        </span>
                        <span v-if="row.passenger?.cccd">
                          <i class="fas fa-id-card me-1"></i> {{ row.passenger.cccd }}
                        </span>
                      </div>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column label="Loại vé" width="350">
                  <template #default="{ row }">
                    <div>
                      <div class="mb-2">{{ row.ticket_type }}</div>
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
                <el-table-column label="Loại vé" width="350">
                  <template #default="{ row }">
                    <div>
                      <div class="mb-2">{{ row.ticket_type }}</div>
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
import { ElMessage, ElMessageBox } from 'element-plus';
import axios from 'axios';

// State
const orders = ref([]);
const dialogVisible = ref(false);
const selectedOrder = ref(null);
const loading = ref(false);
const detailsLoading = ref(false);
const searchTerm = ref('');
const statusFilter = ref('');
const dateRange = ref([]);
const currentPage = ref(1);
const pageSize = ref(10);

// Columns configuration
const allColumns = ref([
  { prop: 'id', label: 'ID', visible: true },
  { prop: 'transaction_id', label: 'Mã giao dịch', visible: true },
  { prop: 'contact_name', label: 'Tên liên hệ', visible: true },
  { prop: 'contact_email', label: 'Email', visible: true },
  { prop: 'contact_phone', label: 'Số điện thoại', visible: true },
  { prop: 'total_amount', label: 'Tổng tiền', visible: true },
  { prop: 'status', label: 'Trạng thái', visible: true },
  { prop: 'created_at', label: 'Ngày tạo', visible: true },
]);

// Order history example data
const orderHistory = ref([
  { time: '05/05/2025 10:25', type: 'success', color: '#67C23A', content: 'Đơn hàng đã được tạo thành công' },
  { time: '05/05/2025 10:26', type: 'primary', color: '#409EFF', content: 'Thanh toán đã được xác nhận' },
  { time: '05/05/2025 10:30', type: 'success', color: '#67C23A', content: 'Vé đã được gửi qua email cho khách hàng' },
]);

// Computed properties
const filteredOrders = computed(() => {
  let result = [...orders.value];

  // Search filter
  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase();
    result = result.filter(order =>
      order.id.toString().includes(search) ||
      order.transaction_id.toLowerCase().includes(search) ||
      order.contact_name.toLowerCase().includes(search) ||
      order.contact_email.toLowerCase().includes(search) ||
      order.contact_phone.includes(search)
    );
  }

  // Status filter
  if (statusFilter.value) {
    result = result.filter(order => order.status === statusFilter.value);
  }

  // Date range filter
  if (dateRange.value && dateRange.value.length === 2) {
    const startDate = new Date(dateRange.value[0]);
    const endDate = new Date(dateRange.value[1]);
    endDate.setHours(23, 59, 59);

    result = result.filter(order => {
      const orderDate = new Date(order.created_at);
      return orderDate >= startDate && orderDate <= endDate;
    });
  }

  return result;
});

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredOrders.value.slice(start, end);
});

// Methods
const fetchOrders = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/admin-orders');
    if (response.data.success) {
      orders.value = response.data.orders;
    } else {
      ElMessage.error(response.data.message || 'Lấy danh sách đơn hàng thất bại');
    }
  } catch (error) {
    ElMessage.error('Lỗi khi lấy danh sách đơn hàng');
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const showOrderDetails = async (row) => {
  selectedOrder.value = row;
  dialogVisible.value = true;
};

const getStatusCount = (status) => {
  return orders.value.filter(order => order.status === status).length;
};

const getStatusType = (status) => {
  switch (status) {
    case 'completed':
      return 'success';
    case 'pending':
      return 'warning';
    case 'Đã hủy':
      return 'danger';
    default:
      return 'info';
  }
};

const getCheckinType = (checkin) => {
  switch (checkin) {
    case 'used':
      return 'success';
    case 'unused':
      return 'warning';
    case 'Đã hủy':
      return 'danger';
    default:
      return 'info';
  }
};

const getColumnVisible = (prop) => {
  return allColumns.value.find(col => col.prop === prop)?.visible ?? true;
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

const handleSizeChange = (val) => {
  pageSize.value = val;
  currentPage.value = 1;
};

const handleCurrentChange = (val) => {
  currentPage.value = val;
};

const handleDialogClosed = () => {
  selectedOrder.value = null;
  detailsLoading.value = false;
};

const applyFilters = () => {
  currentPage.value = 1;
  ElMessage.success('Đã áp dụng bộ lọc');
};

const resetFilters = () => {
  searchTerm.value = '';
  statusFilter.value = '';
  dateRange.value = [];
  currentPage.value = 1;
  ElMessage.success('Đã đặt lại bộ lọc');
};

onMounted(() => {
  fetchOrders();
});
</script>