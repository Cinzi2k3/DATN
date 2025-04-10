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
              :key="`bed-${bed.sohieu}`" 
              class="bed-container"
              @click="toggleBedSelection(bed.sohieu)"
            >
              <div 
                class="bed" 
                :class="{ 
                  'selected': isBedSelected(bed.sohieu),
                  'unavailable': bed.trangthai === 'Đã đặt'
                }"
              >
                <div class="bed-number">{{ bed.sohieu }}</div>
                <div v-if="bed.trangthai !== 'Đã đặt'" class="bed-price">{{ formatPrice( bed.gia )}}</div>
              </div>
            </div>
            <div v-if="compartment < numCompartments" class="divider"></div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="beds && beds.length > 0" class="mt-4 text-center">
      <p>Số giường đã chọn: {{ totalSelected }} / {{ totalTickets }}</p>
      <button
        class="btn btn-primary" 
        :disabled="totalSelected !== totalTickets"
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
import { ElNotification } from 'element-plus';
import { defineProps, computed, defineEmits } from 'vue';
import { useFormatPrice } from '@/composables/useFormatprice';

const { formatPrice} = useFormatPrice();

const props = defineProps({
  car: Object,
  beds: Array,
  totalTickets: { type: Number, default: 1 },
  selectedBeds: { type: Array, default: () => [] },
  totalSelected: Number,
  ticketDetails: Array,
});

const emit = defineEmits(['update:selected-beds', 'book']);

const rows = 2;

const numCompartments = computed(() => {
  if (!props.beds || props.beds.length === 0) return 0;
  return Math.max(...props.beds.map(bed => parseInt(bed.khoang) || 0));
});

const getBedsForCompartment = (tang, khoang) => {
  return (props.beds || [])
    .filter(bed => parseInt(bed.tang) === tang && parseInt(bed.khoang) === khoang)
    .sort((a, b) => a.sohieu.localeCompare(b.sohieu));
};

const isBedSelected = (bedNumber) => {
  return props.selectedBeds.includes(bedNumber);
};

const toggleBedSelection = (bedNumber) => {
  const bed = props.beds?.find(b => b.sohieu === bedNumber);
  if (!bed || bed.trangthai === 'Đã đặt') return;

  const newSelectedBeds = [...props.selectedBeds];
  const index = newSelectedBeds.indexOf(bedNumber);

  if (index === -1) {
    if (props.totalSelected < props.totalTickets) {
      newSelectedBeds.push(bedNumber);
    } else {
      ElNotification.error(`Bạn chỉ có thể chọn tối đa ${props.totalTickets} giường trên toàn tàu!`);
      return;
    }
  } else {
    newSelectedBeds.splice(index, 1);
  }

  emit('update:selected-beds', newSelectedBeds);
};

const bookTickets = () => {
  if (props.selectedBeds.length > 0) {
    emit('book', { carType: props.car.type, beds: props.selectedBeds }); // Truyền tên toa và danh sách giường
  }
};
</script>

<style scoped>
@import url(@/assets/css/bed.css);
</style>