<template>
  <div class="support-manager">
    <h2>Quản Lý Hỗ Trợ Khách Hàng</h2>
    <el-table :data="supportRequests" style="width: 100%">
      <el-table-column prop="id" label="ID" width="50" />
      <el-table-column prop="email" label="Email" width="230" />
      <el-table-column prop="reason" label="Lý Do Hỗ Trợ" />
      <el-table-column prop="status" label="Trạng Thái" width="150">
        <template #default="{ row }">
          <el-select v-model="row.status" @change="updateStatus(row)">
            <el-option value="pending" label="Đang chờ" />
            <el-option value="processing" label="Đang xử lý" />
            <el-option value="resolved" label="Đã giải quyết" />
          </el-select>
        </template>
      </el-table-column>
      <el-table-column prop="admin_note" label="Ghi Chú ">
        <template #default="{ row }">
          <el-input v-model="row.admin_note" @blur="updateStatus(row)" />
        </template>
      </el-table-column>
      <el-table-column label="Ngày Tạo" width="180" >
        <template #default="{ row }">
          <span>{{ formatTime(row.created_at) }} {{ formatDate(row.created_at) }} </span>
        </template>
        </el-table-column>
    </el-table>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { ElMessage } from "element-plus";

const supportRequests = ref([]);

const fetchSupportRequests = async () => {
  try {
    const response = await axios.get("/support-requests", {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("admin_token")}`,
      },
    });
    supportRequests.value = response.data;
  } catch (error) {
    ElMessage.error("Lỗi khi lấy danh sách yêu cầu: " + (error.response?.data?.message || "Vui lòng thử lại"));
  }
};

const updateStatus = async (row) => {
  try {
    const response = await axios.put(`/support-requests/${row.id}`, {
      status: row.status,
      admin_note: row.admin_note,
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("admin_token")}`,
      },
    });
    ElMessage.success(response.data.message || "Cập nhật thành công");
  } catch (error) {
    ElMessage.error("Lỗi khi cập nhật yêu cầu: " + (error.response?.data?.message || "Vui lòng thử lại"));
  }
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

onMounted(() => {
  fetchSupportRequests();
});
</script>

<style scoped>
.support-manager {
  padding: 20px;
}
h2 {
  margin-bottom: 20px;
}
</style>