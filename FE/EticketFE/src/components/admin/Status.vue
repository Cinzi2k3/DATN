```vue
<template>
  <div>
    <h2 class="mb-4 text-2xl font-bold">Quản lý giá chỗ</h2>

    <!-- Thông báo -->
    <el-alert
      v-if="message"
      :title="message"
      :type="messageType"
      show-icon
      class="mb-4"
      @close="message = ''"
    />

    <div class="row">
      <!-- Cây tàu và toa -->
      <div class="col-md-4">
        <el-card class="box-card">
          <template #header>
            <div class="card-header">
              <span>Danh sách tàu và toa</span>
            </div>
          </template>
          <el-tree
            v-if="treeData.length"
            :data="treeData"
            :props="treeProps"
            @node-click="handleNodeClick"
            highlight-current
            node-key="id"
            default-expand-all
          />
          <el-empty v-else description="Không có tàu hoặc toa để hiển thị" />
        </el-card>
      </div>

      <!-- Bảng chỗ -->
      <div class="col-md-8">
        <el-card v-if="selectedToa" class="box-card">
          <template #header>
            <div class="card-header">
              <span>Chỗ trong toa: {{ selectedToa.label }}</span>
            </div>
          </template>
          <el-table
            :data="choList"
            stripe
            style="width: 100%"
            class="table table-bordered"
          >
            <el-table-column prop="macho" label="Mã chỗ" width="100" />
            <el-table-column prop="sohieu" label="Số hiệu" width="100" />
            <el-table-column  label="Tầng" width="80">
              <template #default="{ row }">
                <span v-if="row.tang">{{ row.tang }}</span>
                <span v-else>0</span>
              </template>
            </el-table-column>
            <el-table-column prop="khoang" label="Khoang" width="80">
                <template #default="{ row }">
                    <span v-if="row.khoang">{{ row.khoang }}</span>
                    <span v-else>0</span>
                </template>
            </el-table-column>
            <el-table-column label="Giá (VNĐ)" width="200">
              <template #default="{ row }">
                <el-input-number
                  v-if="row.isEditing"
                  v-model="row.gia"
                  :min="0"
                  size="small"
                  class="w-full"
                  @keyup.enter="updatePrice(row)"
                />
                <span v-else>{{ row.gia }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Hành động" width="130">
              <template #default="{ row }">
                <div v-if="!row.isEditing">
                  <el-button
                    type="primary"
                    size="small"
                    @click="row.isEditing = true"
                  >
                    Sửa
                  </el-button>
                </div>
                <div v-else class="d-flex gap-2">
                  <el-button
                    type="success"
                    size="small"
                    @click="updatePrice(row)"
                  >
                    Lưu
                  </el-button>
                  <el-button
                    type="default"
                    size="small"
                    @click="row.isEditing = false"
                  >
                    Hủy
                  </el-button>
                </div>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
        <el-empty v-else description="Vui lòng chọn một toa để xem chỗ" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const treeData = ref([]);
const choList = ref([]);
const selectedToa = ref(null);
const message = ref('');
const messageType = ref('');

const treeProps = {
  label: 'label',
  children: 'children',
};

// Lấy danh sách tàu, toa, và chỗ từ API
const fetchData = async () => {
  try {
    const response = await axios.get('/cho');
    const choData = response.data.data;

    console.log('Dữ liệu từ API:', choData); // Log dữ liệu thô

    if (!choData || !Array.isArray(choData)) {
      message.value = 'Dữ liệu API không hợp lệ!';
      messageType.value = 'error';
      return;
    }

    // Nhóm dữ liệu theo tentau và tentoa
    const tauMap = new Map();

    choData.forEach((cho, index) => {
      // Kiểm tra các trường cần thiết
      if (!cho.tentau || !cho.tentoa) {
        console.warn(`Bản ghi ${index} thiếu tentau hoặc tentoa:`, cho);
        return;
      }

      const tauKey = cho.tentau; // Sử dụng tentau làm khóa cho tàu
      const toaKey = `${cho.tentau}-${cho.tentoa}`; // Kết hợp tentau-tentoa để đảm bảo toa duy nhất

      // Tạo node tàu nếu chưa tồn tại
      if (!tauMap.has(tauKey)) {
        tauMap.set(tauKey, {
          id: `tau-${tauKey}`,
          label: cho.tentau,
          tentau: cho.tentau,
          children: new Map(),
        });
      }

      // Tạo node toa nếu chưa tồn tại
      const toaMap = tauMap.get(tauKey).children;
      if (!toaMap.has(toaKey)) {
        toaMap.set(toaKey, {
          id: `toa-${toaKey}`,
          label: cho.tentoa,
          tentau: cho.tentau,
          tentoa: cho.tentoa,
          children: [],
        });
      }
    });

    // Chuyển Map thành mảng cho cây
    treeData.value = Array.from(tauMap.values()).map(tau => ({
      ...tau,
      children: Array.from(tau.children.values()),
    }));

    console.log('Cấu trúc cây:', treeData.value); // Log cấu trúc cây

    if (treeData.value.length === 0) {
      message.value = 'Không tìm thấy tàu hoặc toa!';
      messageType.value = 'warning';
    }
  } catch (error) {
    console.error('Lỗi khi tải dữ liệu:', error);
    message.value = 'Lỗi khi tải dữ liệu từ server!';
    messageType.value = 'error';
  }
};

// Xử lý khi nhấp vào node trong cây
const handleNodeClick = async (node) => {
  if (node.tentoa) { // Nếu node là toa
    selectedToa.value = node;
    try {
      const response = await axios.get('/cho');
      choList.value = response.data.data
        .filter(cho => cho.tentoa === node.tentoa && cho.tentau === node.tentau)
        .map(cho => ({
          ...cho,
          isEditing: false,
        }));
      console.log('Danh sách chỗ:', choList.value); // Log danh sách chỗ
      if (choList.value.length === 0) {
        message.value = 'Không có chỗ trong toa này!';
        messageType.value = 'warning';
      }
    } catch (error) {
      console.error('Lỗi khi tải danh sách chỗ:', error);
      message.value = 'Lỗi khi tải danh sách chỗ!';
      messageType.value = 'error';
    }
  } else {
    selectedToa.value = null;
    choList.value = [];
  }
};

// Cập nhật giá chỗ
const updatePrice = async (cho) => {
  try {
    const response = await axios.put(`/cho/${cho.macho}`, {
      gia: parseFloat(cho.gia) || 0,
    });
    cho.isEditing = false;
    message.value = 'Cập nhật giá thành công!';
    messageType.value = 'success';
    Object.assign(cho, response.data.data);
  } catch (error) {
    console.error('Lỗi khi cập nhật giá:', error);
    message.value = 'Lỗi khi cập nhật giá!';
    messageType.value = 'error';
  }
};

// Tải dữ liệu khi component được mount
onMounted(fetchData);
</script>

<style scoped>
.container {
  max-width: 1400px;
  margin: 0 auto;
}
.table-bordered th,
.table-bordered td {
  border: 1px solid #dee2e6;
}
.d-flex {
  display: flex;
}
.gap-2 {
  gap: 0.5rem;
}
.box-card {
  margin-bottom: 20px;
}
</style>
```