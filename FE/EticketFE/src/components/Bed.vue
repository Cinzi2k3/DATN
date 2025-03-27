<template>
  <div class="container">
    <h3 class="text-center mb-4">{{ car.type }}</h3>
    <div class="car-container">
      <div v-if="!beds" class="text-center">
        <p>Đang tải dữ liệu giường...</p>
      </div>
      <div v-else-if="beds.length === 0" class="text-center">
        <p>Không có dữ liệu giường để hiển thị.</p>
      </div>
      <div v-else class="car mb-5">
        <!-- Nhãn khoang -->
        <div class="compartment-labels row">
          <div class="col-2"></div>
          <div v-for="compartment in numCompartments" :key="`compartment-${compartment}`" class="col compartment-label">
            Khoang {{ compartment }}
          </div>
        </div>
        <!-- Sơ đồ giường -->
        <div v-for="tier in rows" :key="`tier-${tier}`" class="bed-row row align-items-center">
          <div class="col-2 row-label">Tầng {{ rows + 1 - tier }}</div>
          <div v-for="compartment in numCompartments" :key="`compartment-${tier}-${compartment}`" class="col compartment d-flex align-items-center">
            <div 
              v-for="bed in getBedsForCompartment(rows + 1 - tier, compartment)" 
              :key="`bed-${bed.macho}`" 
              class="bed-container"
              @click="toggleBedSelection(bed.macho)"
            >
              <div 
                class="bed" 
                :class="{ 
                  'selected': isBedSelected(bed.macho),
                  'unavailable': bed.trangthai === 'Đã đặt'
                }"
              >
                <div class="bed-number">{{ bed.sohieu }}</div>
                <div v-if="bed.trangthai !== 'Đã đặt'" class="bed-price">{{ bed.gia || (bed.tang === '1' ? '712k' : '670k') }}</div>
              </div>
            </div>
            <div v-if="compartment < numCompartments" class="divider"></div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="seats && seats.length > 0" class="mt-4 text-center">
      <p>Số ghế đã chọn: {{ selectedSeats.length }} / {{ totalTickets }}</p>
      <button
        class="btn btn-primary" 
        :disabled="selectedSeats.length === 0 || selectedSeats.length > totalTickets"
        @click="bookTickets"
      >
        Đặt vé
      </button>
    </div>

    <div class="legend mt-4 d-flex justify-content-center">
      <div class="d-flex align-items-center me-4">
        <div class="beds available"></div>
        <span class="ms-2">Giường trống</span>
      </div>
      <div class="d-flex align-items-center me-4">
        <div class="beds selected"></div>
        <span class="ms-2">Giường đang chọn </span>
      </div>
      <div class="d-flex align-items-center">
        <div class="beds unavailable"></div>
        <span class="ms-2">Giường đã đặt</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, computed } from 'vue';

const props = defineProps({
  car: Object,
  beds: Array, // Danh sách giường từ API với tang và khoang
  totalTickets:{
    type: Number,
    default: 1
  }
});

const selectedBeds = ref([]);

// Số tầng cố định
const rows = 2;

// Tính số khoang tối đa từ dữ liệu
const numCompartments = computed(() => {
  if (!props.beds || props.beds.length === 0) return 0;
  return Math.max(...props.beds.map(bed => parseInt(bed.khoang))); // Lấy số khoang lớn nhất
});

// Lấy danh sách giường cho từng tầng và khoang
const getBedsForCompartment = (tang, khoang) => {
  return props.beds
    .filter(bed => parseInt(bed.tang) === tang && parseInt(bed.khoang) === khoang)
    .sort((a, b) => a.sohieu.localeCompare(b.sohieu)); // Sắp xếp theo sohieu nếu cần
};

// Kiểm tra giường có được chọn không
const isBedSelected = (macho) => {
  return selectedBeds.value.includes(macho);
};

// Chọn/bỏ chọn giường
const toggleBedSelection = (macho) => {
  const bed = props.beds.find(b => b.macho === macho);
  if (!bed || bed.trangthai === 'Đã đặt') return;
  
  const index = selectedBeds.value.indexOf(macho);
  if (index === -1) {
    selectedBeds.value.push(macho);
  } else {
    selectedBeds.value.splice(index, 1);
  }
};
</script>

<style scoped>
@import url(@/assets/css/bed.css);

</style>