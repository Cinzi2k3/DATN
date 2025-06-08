<template>
  <div class="container">
    <h2 class="mb-4 text-2xl font-bold">Quản lý giá chỗ</h2>

    <!-- Form chọn tuyến đường và mã lịch trình -->
    <el-card class="box-card mb-4">
      <template #header>
        <div class="card-header">
          <span>Chọn tuyến đường và lịch trình</span>
        </div>
      </template>
      <el-form :inline="true" class="mb-4">
        <el-form-item label="Tuyến đường">
          <el-select
            v-model="selectedTuyenDuong"
            placeholder="Chọn tuyến đường"
            clearable
            @change="handleTuyenDuongChange"
            :disabled="loadingLichTrinh"
            style="width: 400px"
          >
            <el-option
              v-for="item in tuyenDuongList"
              :key="`${item.gadi}-${item.gaden}`"
              :label="`${item.gadi} → ${item.gaden}`"
              :value="`${item.gadi}-${item.gaden}`"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Mã lịch trình" v-if="selectedTuyenDuong">
          <el-select
            v-model="selectedLichTrinh"
            placeholder="Chọn mã lịch trình"
            clearable
            @change="handleLichTrinhChange"
            :disabled="loadingLichTrinh"
            style="width: 200px"
          >
            <el-option
              v-for="item in filteredLichTrinhList"
              :key="item.malichtrinh"
              :label="`${item.tentau} (${item.ngaydi} - ${item.ngayden} - ${item.giodi} → ${item.gioden})`"
              :value="item.malichtrinh"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <el-alert
        v-if="message"
        :title="message"
        :type="messageType"
        show-icon
        class="mb-4"
        @close="message = ''"
      />
    </el-card>

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
        <el-card v-if="selectedToa && selectedLichTrinh" class="box-card">
          <template #header>
            <div class="card-header">
              <span>Chỗ trong toa: {{ selectedToa.label }} (Lịch trình: {{ getLichTrinhLabel }})</span>
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
            <el-table-column label="Tầng" width="80">
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
        <el-empty v-else description="Vui lòng chọn tuyến đường, mã lịch trình và toa để xem chỗ" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const treeData = ref([]);
const choList = ref([]);
const selectedToa = ref(null);
const selectedTuyenDuong = ref(null);
const selectedLichTrinh = ref(null);
const tuyenDuongList = ref([]);
const lichTrinhList = ref([]);
const message = ref('');
const messageType = ref('');
const loadingLichTrinh = ref(false);

const treeProps = {
  label: 'label',
  children: 'children',
};

// Lọc danh sách mã lịch trình theo tuyến đường đã chọn
const filteredLichTrinhList = computed(() => {
  if (!selectedTuyenDuong.value) return [];
  const [gadi, gaden] = selectedTuyenDuong.value.split('-');
  return lichTrinhList.value.filter(
    item => item.gadi === gadi && item.gaden === gaden
  );
});

// Tính toán nhãn lịch trình cho tiêu đề bảng chỗ
const getLichTrinhLabel = computed(() => {
  const lichTrinh = lichTrinhList.value.find(
    item => item.malichtrinh === selectedLichTrinh.value
  );
  return lichTrinh
    ? `${lichTrinh.malichtrinh} - ${lichTrinh.gadi} → ${lichTrinh.gaden} (${lichTrinh.ngaydi} - ${lichTrinh.ngayden})`
    : '';
});

// Lấy danh sách lịch trình và nhóm thành tuyến đường
const fetchLichTrinh = async () => {
  try {
    loadingLichTrinh.value = true;
    const response = await axios.get('/lichtrinh');
    lichTrinhList.value = response.data.data || [];

    // Nhóm lịch trình theo gadi và gaden
    const tuyenDuongMap = new Map();
    lichTrinhList.value.forEach(item => {
      const key = `${item.gadi}-${item.gaden}`;
      if (!tuyenDuongMap.has(key)) {
        tuyenDuongMap.set(key, {
          gadi: item.gadi,
          gaden: item.gaden,
          malichtrinh: [],
        });
      }
      tuyenDuongMap.get(key).malichtrinh.push(item.malichtrinh);
    });

    tuyenDuongList.value = Array.from(tuyenDuongMap.values());
    if (tuyenDuongList.value.length === 0) {
      message.value = 'Không tìm thấy tuyến đường!';
      messageType.value = 'warning';
    }
  } catch (error) {
    console.error('Lỗi khi tải danh sách lịch trình:', error);
    message.value = 'Lỗi khi tải danh sách lịch trình!';
    messageType.value = 'error';
  } finally {
    loadingLichTrinh.value = false;
  }
};

// Lấy danh sách tàu và toa theo mã lịch trình
const fetchData = async () => {
  if (!selectedLichTrinh.value) {
    treeData.value = [];
    selectedToa.value = null;
    choList.value = [];
    return;
  }

  try {
    console.log('Gửi yêu cầu API fetchData:', { malichtrinh: selectedLichTrinh.value });
    const response = await axios.get(`/cho?malichtrinh=${selectedLichTrinh.value}`);
    const choData = response.data.data;

    console.log('Dữ liệu từ API fetchData:', choData);

    if (!choData || !Array.isArray(choData)) {
      message.value = 'Dữ liệu API không hợp lệ!';
      messageType.value = 'error';
      return;
    }

    // Nhóm dữ liệu theo tentau và tentoa
    const tauMap = new Map();

    choData.forEach((cho, index) => {
      if (!cho.tentau || !cho.tentoa || !cho.malichtrinh) {
        console.warn(`Bản ghi ${index} thiếu tentau, tentoa hoặc malichtrinh:`, cho);
        return;
      }

      if (cho.malichtrinh !== selectedLichTrinh.value) {
        return; // Chỉ lấy dữ liệu thuộc mã lịch trình đã chọn
      }

      const tauKey = cho.tentau;
      const toaKey = `${cho.tentau}-${cho.tentoa}`;

      if (!tauMap.has(tauKey)) {
        tauMap.set(tauKey, {
          id: `tau-${tauKey}`,
          label: cho.tentau,
          tentau: cho.tentau,
          children: new Map(),
        });
      }

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

    treeData.value = Array.from(tauMap.values()).map(tau => ({
      ...tau,
      children: Array.from(tau.children.values()),
    }));

    console.log('Cấu trúc cây:', treeData.value);

    if (treeData.value.length === 0) {
      message.value = 'Không tìm thấy tàu hoặc toa cho lịch trình này!';
      messageType.value = 'warning';
    }
  } catch (error) {
    console.error('Lỗi khi tải dữ liệu:', error);
    message.value = 'Lỗi khi tải dữ liệu từ server!';
    messageType.value = 'error';
  }
};

// Xử lý khi chọn tuyến đường
const handleTuyenDuongChange = () => {
  selectedLichTrinh.value = null;
  selectedToa.value = null;
  choList.value = [];
  treeData.value = [];
};

// Xử lý khi chọn mã lịch trình
const handleLichTrinhChange = () => {
  selectedToa.value = null;
  choList.value = [];
  fetchData();
};

// Xử lý khi nhấp vào node trong cây
const handleNodeClick = async (node) => {
  if (node.tentoa && selectedLichTrinh.value) {
    selectedToa.value = node;
    try {
      console.log('Gửi yêu cầu API handleNodeClick:', {
        malichtrinh: selectedLichTrinh.value,
        tentau: node.tentau,
        tentoa: node.tentoa,
      });
      const response = await axios.get(
        `/cho?malichtrinh=${selectedLichTrinh.value}&tentau=${node.tentau}&tentoa=${node.tentoa}`
      );
      const choData = response.data.data;
      console.log('Dữ liệu chỗ từ API:', choData);

      // Lọc lại phía client để đảm bảo chỉ lấy chỗ đúng malichtrinh, tentau, tentoa
      choList.value = choData
        .filter(
          cho =>
            cho.malichtrinh === selectedLichTrinh.value &&
            cho.tentau === node.tentau &&
            cho.tentoa === node.tentoa
        )
        .map(cho => ({
          ...cho,
          isEditing: false,
        }));

      console.log('Danh sách chỗ sau lọc:', choList.value);
      if (choList.value.length === 0) {
        message.value = `Không có chỗ trong toa ${node.tentoa} của tàu ${node.tentau} cho lịch trình ${selectedLichTrinh.value}!`;
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
  if (!selectedLichTrinh.value) {
    message.value = 'Vui lòng chọn mã lịch trình trước khi cập nhật giá!';
    messageType.value = 'error';
    return;
  }

  try {
    console.log('Gửi yêu cầu cập nhật giá:', {
      macho: cho.macho,
      gia: parseFloat(cho.gia) || 0,
      malichtrinh: selectedLichTrinh.value,
    });
    const response = await axios.put(`/cho/${cho.macho}`, {
      gia: parseFloat(cho.gia) || 0,
      malichtrinh: selectedLichTrinh.value,
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
onMounted(() => {
  fetchLichTrinh();
});
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