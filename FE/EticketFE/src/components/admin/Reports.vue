<template>
  <div class="statistics-container container">
    <h2>Thống kê doanh thu</h2>

    <!-- Tabs chọn loại thống kê -->
    <el-tabs v-model="activeTab" @tab-change="setTab" class="mb-4">
      <el-tab-pane label="Theo mã lịch trình" name="schedule_route"></el-tab-pane>
      <el-tab-pane label="Theo người dùng" name="user"></el-tab-pane>
      <el-tab-pane label="Theo thời gian" name="time"></el-tab-pane>
    </el-tabs>

    <!-- Form lọc -->
    <div class="filter-form row g-3 mb-4">
      <div v-if="activeTab === 'schedule_route'" class="col-md-6">
        <el-form :model="filters" label-position="top" @submit.prevent>
          <el-form-item label="Mã lịch trình">
            <el-input
              v-model.number="filters.malichtrinh"
              @keyup.enter="fetchStatistics"
              type="number"
              placeholder="Nhập mã lịch trình (ví dụ: 1 hoặc 2)"
              clearable
            />
          </el-form-item>
          <el-form-item >
            <el-button type="primary" @click="fetchStatistics"  >Lọc</el-button>
          </el-form-item>
        </el-form>
      </div>

      <div v-if="activeTab === 'user'" class="col-md-6">
        <el-form :model="filters" label-position="top" @submit.prevent>
          <el-form-item label="ID Người dùng">
            <el-input
              v-model.number="filters.user_id"
              @keyup.enter="fetchStatistics"
              type="number"
              placeholder="Nhập ID người dùng (ví dụ: 123)"
              clearable
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="fetchStatistics">Lọc</el-button>
          </el-form-item>
        </el-form>
      </div>

      <div v-if="activeTab === 'time'" class="col-md-12">
        <el-form :model="filters" label-position="top" class="row g-3" @submit.prevent>
          <el-form-item label="Từ ngày" class="col-md-4" @keyup.enter="fetchStatistics">
            <el-date-picker
              v-model="filters.start_date"
              type="date"
              placeholder="Chọn ngày bắt đầu"
              format="YYYY-MM-DD"
              value-format="YYYY-MM-DD"
              clearable
            />
          </el-form-item>
          <el-form-item label="Đến ngày" class="col-md-4" @keyup.enter="fetchStatistics">
            <el-date-picker
              v-model="filters.end_date"
              type="date"             
              placeholder="Chọn ngày kết thúc"
              format="YYYY-MM-DD"
              value-format="YYYY-MM-DD"
              clearable
            />
          </el-form-item>
          <el-form-item label="Nhóm theo" class="col-md-4">
            <el-select v-model="filters.group_by" placeholder="Chọn nhóm" clearable @keyup.enter="fetchStatistics">
              <el-option label="Tuần" value="week" />
              <el-option label="Tháng" value="month" />
              <el-option label="Năm" value="year" />
            </el-select>
          </el-form-item>
          <el-form-item class="col-md-12">
            <el-button type="primary" @click="fetchStatistics" >Lọc</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>

    <!-- Bảng dữ liệu -->
    <el-table
      v-if="statistics.length"
      :data="statistics"
      stripe
      style="width: 100%"
      class="mb-4"
    >
      <el-table-column type="index" label="STT" width="80" />
      <el-table-column
        :label="activeTab === 'schedule_route' ? 'Mã lịch trình' : activeTab === 'user' ? 'Tên người dùng' : 'Thời gian'"
        prop="label"
      />
      <el-table-column label="Doanh thu (VND)" prop="revenue">
        <template #default="{ row }">
          {{ formatCurrency(row.revenue) }}
        </template>
      </el-table-column>
    </el-table>
    <el-empty v-else description="Không có dữ liệu để hiển thị" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { ElMessage } from 'element-plus';

// Tab hiện tại
const activeTab = ref('schedule_route');

// Bộ lọc
const filters = ref({
  type: 'schedule_route',
  malichtrinh: null,
  user_id: null,
  start_date: '',
  end_date: '',
  group_by: 'month',
});

// Dữ liệu thống kê
const statistics = ref([]);

// Chuyển tab
const setTab = (tab) => {
  activeTab.value = tab;
  filters.value.type = tab;
  statistics.value = [];
  // Reset bộ lọc
  filters.value.malichtrinh = null;
  filters.value.user_id = null;
  filters.value.start_date = '';
  filters.value.end_date = '';
  filters.value.group_by = 'month';
};

// Lấy dữ liệu thống kê
const fetchStatistics = async () => {
  try {
    const response = await axios.get('/statistics', {
      params: filters.value,
    });

    if (response.data.success) {
      statistics.value = response.data.statistics;
    } else {
      ElMessage.error(response.data.message);
    }
  } catch (error) {
    console.error('Lỗi khi lấy dữ liệu thống kê:', error);
    ElMessage.error('Không thể lấy dữ liệu thống kê.');
  }
};

// Định dạng tiền tệ
const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(value);
};
</script>

<style scoped>
.statistics-container {
  padding: 20px;
  background-color: #f8f9fa;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

h2 {
  color: #343a40;
  font-weight: 600;
}

.filter-form {
  background-color: #ffffff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

:deep(.el-tabs__nav-wrap) {
  background-color: #ffffff;
  padding: 10px;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

:deep(.el-tabs__item) {
  font-size: 16px;
  font-weight: 500;
}

:deep(.el-table) {
  border-radius: 8px;
  overflow: hidden;
}

:deep(.el-table th) {
  background-color: #007bff;
  color: #ffffff;
  font-weight: 600;
}

:deep(.el-table tr) {
  transition: background-color 0.2s;
}

:deep(.el-table tr:hover) {
  background-color: #e9ecef;
}
</style>