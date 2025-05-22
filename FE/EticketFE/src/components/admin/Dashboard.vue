<template>
  <div class="dashboard-container">
    <!-- Thống kê tổng quan -->
    <div class="stats-section" v-loading="loading.stats">
      <el-row :gutter="20">
        <el-col :xs="24" :sm="12" :md="6">
          <el-card class="stats-card revenue">
            <div class="stats-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stats-info">
              <p class="stats-title">Doanh thu</p>
              <h3 class="stats-value">{{ formatCurrency(stats.revenue) }}</h3>
              <p class="stats-trend" :class="{ up: stats.revenueTrend >= 0, down: stats.revenueTrend < 0 }">
                <i :class="stats.revenueTrend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                {{ Math.abs(stats.revenueTrend) }}% <span>so với {{ previousRangeText }}</span>
              </p>
            </div>
          </el-card>
        </el-col>
        <el-col :xs="24" :sm="12" :md="6">
          <el-card class="stats-card orders">
            <div class="stats-icon">
              <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stats-info">
              <p class="stats-title">Đơn hàng</p>
              <h3 class="stats-value">{{ stats.orders }}</h3>
              <p class="stats-trend" :class="{ up: stats.ordersTrend >= 0, down: stats.ordersTrend < 0 }">
                <i :class="stats.ordersTrend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                {{ Math.abs(stats.ordersTrend) }}% <span>so với {{ previousRangeText }}</span>
              </p>
            </div>
          </el-card>
        </el-col>
        <el-col :xs="24" :sm="12" :md="6">
          <el-card class="stats-card users">
            <div class="stats-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="stats-info">
              <p class="stats-title">Người dùng mới</p>
              <h3 class="stats-value">{{ stats.newUsers }}</h3>
              <p class="stats-trend" :class="{ up: stats.newUsersTrend >= 0, down: stats.newUsersTrend < 0 }">
                <i :class="stats.newUsersTrend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                {{ Math.abs(stats.newUsersTrend) }}% <span>so với {{ previousRangeText }}</span>
              </p>
            </div>
          </el-card>
        </el-col>
        <el-col :xs="24" :sm="12" :md="6">
          <el-card class="stats-card occupancy">
            <div class="stats-icon">
              <i class="fas fa-chair"></i>
            </div>
            <div class="stats-info">
              <p class="stats-title">Tỷ lệ lấp đầy</p>
              <h3 class="stats-value">{{ stats.occupancyRate }}%</h3>
              <p class="stats-trend" :class="{ up: stats.occupancyTrend >= 0, down: stats.occupancyTrend < 0 }">
                <i :class="stats.occupancyTrend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                {{ Math.abs(stats.occupancyTrend) }}% <span>so với {{ previousRangeText }}</span>
              </p>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- Biểu đồ và Bảng -->
    <div class="charts-section" v-loading="loading.charts">
      <el-row :gutter="20">
        <el-col :xs="24" :lg="16">
          <el-card class="chart-card">
            <template #header>
              <div class="card-header">
                <h3>Doanh thu theo thời gian</h3>
                <el-radio-group v-model="revenueTimeRange" size="small" @change="fetchAllData">
                  <el-radio-button label="week">Tuần</el-radio-button>
                  <el-radio-button label="month">Tháng</el-radio-button>
                  <el-radio-button label="year">Năm</el-radio-button>
                </el-radio-group>
              </div>
            </template>
            <div class="chart-container" ref="revenueChartContainer">
              <revenue-chart :chart-data="revenueData" :height="350" v-if="revenueData.labels?.length" />
              <p v-else>Không có dữ liệu doanh thu</p>
            </div>
          </el-card>
        </el-col>
        <el-col :xs="24" :lg="8">
          <el-card class="chart-card">
            <template #header>
              <div class="card-header">
                <h3>Tỷ lệ lấp đầy</h3>
                <el-button type="text">
                  <i class="fas fa-ellipsis-v"></i>
                </el-button>
              </div>
            </template>
            <div class="occupancy-chart">
              <occupancy-pie-chart :chart-data="occupancyData" :height="280" v-if="occupancyData.labels?.length" />
              <p v-else>Không có dữ liệu tỷ lệ lấp đầy</p>
              <div class="chart-legend">
                <div class="legend-item">
                  <div class="legend-color bg-booked"></div>
                  <span>Đã đặt</span>
                </div>
                <div class="legend-item">
                  <div class="legend-color bg-available"></div>
                  <span>Còn trống</span>
                </div>
                <div class="legend-item">
                  <div class="legend-color bg-maintenance"></div>
                  <span>Bảo trì</span>
                </div>
              </div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- Đơn hàng gần đây -->
    <el-row :gutter="20" class="activity-section" v-loading="loading.orders">
      <el-col :xs="24">
        <el-card class="recent-orders">
          <template #header>
            <div class="card-header">
              <h3>Đơn hàng gần đây</h3>
              <el-button type="primary" size="small" plain>Xem tất cả</el-button>
            </div>
          </template>
          <el-table :data="recentOrders" style="width: 100%" :border="false">
            <el-table-column prop="id" label="Mã đơn hàng" width="220" />
            <el-table-column prop="customer" label="Khách hàng" width="180"/>
            <el-table-column prop="date" label="Ngày đặt" width="130" />
            <el-table-column prop="email" label="Email" width="250" />
            <el-table-column prop="phone" label="Sdt" width="120" />
            <el-table-column prop="amount" label="Số tiền" width="110" />
            <el-table-column prop="status" label="Trạng thái" width="120">
              <template #default="scope">
                <el-tag :type="getOrderStatusType(scope.row.status)" effect="dark">
                  {{ scope.row.status === 'Hoàn thành' ? 'Hoàn thành' : scope.row.status === 'Chờ xử lý' ? 'Chờ xử lý' : 'Đã hủy' }}
                </el-tag>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import { ElMessage } from 'element-plus';
import RevenueChart from '@/components/RevenueChart.vue';
import OccupancyPieChart from '@/components/OccupancyPieChart.vue';

// State
const revenueTimeRange = ref('month');
const revenueData = ref({ labels: [], datasets: [] });
const occupancyData = ref({ labels: [], datasets: [] });
const recentOrders = ref([]);
const stats = ref({
  revenue: 0,
  orders: 0,
  newUsers: 0,
  occupancyRate: 0,
  revenueTrend: 0,
  ordersTrend: 0,
  newUsersTrend: 0,
  occupancyTrend: 0,
});
const loading = ref({
  stats: false,
  charts: false,
  orders: false,
});
const error = ref(null);

// Computed
const previousRangeText = computed(() => {
  return revenueTimeRange.value === 'week' ? 'tuần trước' :
         revenueTimeRange.value === 'month' ? 'tháng trước' : 'năm trước';
});

// Methods
const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const getOrderStatusType = (status) => {
  if (status === 'Hoàn thành') return 'success';
  if (status === 'Chờ xử lý') return 'warning';
  return '';
};

const fetchStats = async () => {
  loading.value.stats = true;
  try {
    const response = await axios.get(`/dashboard/stats`, {
      params: { range: revenueTimeRange.value },
    });
    stats.value = response.data;
  } catch (err) {
    error.value = 'Không thể tải thống kê';
  } finally {
    loading.value.stats = false;
  }
};

const fetchRevenue = async () => {
  loading.value.charts = true;
  try {
    const response = await axios.get(`/dashboard/revenue`, {
      params: { range: revenueTimeRange.value },
    });
    console.log('Revenue Data:', response.data); // Debug
    revenueData.value = response.data;
  } catch (err) {
    error.value = 'Không thể tải dữ liệu doanh thu';
  } finally {
    loading.value.charts = false;
  }
};

const fetchOccupancy = async () => {
  loading.value.charts = true;
  try {
    const response = await axios.get(`/dashboard/occupancy`, {
      params: { range: revenueTimeRange.value },
    });
    console.log('Occupancy Data:', response.data); // Debug
    occupancyData.value = response.data;
  } catch (err) {
    error.value = 'Không thể tải dữ liệu tỷ lệ lấp đầy';
  } finally {
    loading.value.charts = false;
  }
};

const fetchRecentOrders = async () => {
  loading.value.orders = true;
  try {
    const response = await axios.get(`/dashboard/recent-orders`);
    console.log('Recent Orders:', response.data);
    recentOrders.value = response.data;
  } catch (err) {
    error.value = 'Không thể tải đơn hàng gần đây';
  } finally {
    loading.value.orders = false;
  }
};


const fetchAllData = async () => {
  await Promise.all([
    fetchStats(),
    fetchRevenue(),
    fetchOccupancy(),
    fetchRecentOrders(),
  ]);
};

// Hiển thị thông báo lỗi
watch(error, (newError) => {
  if (newError) ElMessage.error(newError);
});

// Debug dữ liệu biểu đồ
watch(revenueData, (newData) => {
  console.log('Updated Revenue Data:', newData);
});
watch(occupancyData, (newData) => {
  console.log('Updated Occupancy Data:', newData);
});

// Fetch all data on mount
onMounted(fetchAllData);
</script>

<style scoped>
@import url(@/assets/css/dashboard.css);

.chart-container,
.occupancy-chart {
  width: 100%;
  min-height: 350px;
  position: relative;
}

</style>