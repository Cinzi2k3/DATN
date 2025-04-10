<template>
    <el-dialog
      :model-value="visible"
      @close="closeDialog"
      @update:visible="updateVisible"
      @opened="setInitialScrollPosition"
      width="80%"
    >
      <div class="train-booking-header">
        <div class="route-container">
          <div class="route-info">
            <span>{{ searchgadi }} → {{ searchgaden }} | {{ selectedDay }}</span>
          </div>
          <h2 class="train-title">{{ traintau }} {{ trainCode }}</h2>
        </div>
        <div class="car-selection-wrapper">
          <div class="car-selection" ref="carSelectionRef">
            <div class="car-options">
              <div
                v-for="(car, index) in carOptions"
                :key="car.id"
                class="car-option"
                :class="{ active: selectedCar === index }"
                @click="selectCar(index)"
              >
                <div class="car-type">{{ car.type }}</div>
                <div class="seat-info">{{ car.seatInfo }}</div>
              </div>
              <img
                class="image-head"
                src="https://res.ivivu.com/train/images/trainlist/head-train-desktop.svg"
                alt="Train head"
              />
            </div>
          </div>
        </div>
      </div>
      <keep-alive>
        <Seat
          v-if="currentCarType === 'Toa ghế ngồi'"
          :car="carOptions[selectedCar] || {}"
          :seats="seatData?.cho || []"
          :total-tickets="totalTickets"
          :ticket-details="ticketDetails"
          :selected-seats="selectedSeatsByCar[carOptions[selectedCar]?.type] || []"
          :total-selected="totalSelectedSeats"
          @update:selected-seats="updateSelectedSeats(carOptions[selectedCar]?.type, $event)"
          @book="handleBook"
        />
        <Bed
          v-else-if="currentCarType === 'Toa giường nằm'"
          :car="carOptions[selectedCar] || {}"
          :beds="seatData?.cho || []"
          :total-tickets="totalTickets"
          :ticket-details="ticketDetails"
          :selected-beds="selectedSeatsByCar[carOptions[selectedCar]?.type] || []"
          :total-selected="totalSelectedSeats"
          @update:selected-beds="updateSelectedSeats(carOptions[selectedCar]?.type, $event)"
          @book="handleBook"
        />
      </keep-alive>
    </el-dialog>
  </template>
  
  <script setup>
  import { ref, computed, onMounted, watch, nextTick } from 'vue';
  import axios from 'axios';
  import Seat from '@/components/Seat.vue';
  import Bed from '@/components/Bed.vue';
  import { useRouter } from 'vue-router';
  
  // Refs
  const carOptions = ref([]);
  const selectedCar = ref(0);
  const carSelectionRef = ref(null);
  const currentCarType = ref('');
  const seatData = ref(null);
  const selectedSeatsByCar = ref({});
  const isFetching = ref(false); // Thêm để kiểm soát trạng thái fetch
  const router = useRouter();
  
  // Props và Emits
  const props = defineProps({
    visible: Boolean,
    trainCode: String,
    traintau: String,
    searchgadi: String,
    searchgaden: String,
    selectedDay: String,
    totalTickets: { type: Number, default: 1 },
    ticketDetails: Array,
    departureTime: String,
    arrivalTime: String,
    malichtrinh: String,
  });
  const emits = defineEmits(['update:visible']);
  
  // Computed
  const totalSelectedSeats = computed(() =>
    Object.values(selectedSeatsByCar.value).reduce((total, seats) => total + (seats?.length || 0), 0)
  );
  
  // Methods
  const closeDialog = () => {
    selectedSeatsByCar.value = {};
    emits('update:visible', false);
  };
  const updateVisible = (value) => emits('update:visible', value);
  
  const fetchTrainCars = async () => {
    console.log('Fetching with trainCode:', props.trainCode, 'malichtrinh:', props.malichtrinh);
    isFetching.value = true;
    try {
      if (!props.malichtrinh) {
        console.error('malichtrinh is not provided!');
        return;
      }
      // Reset dữ liệu từ API
      carOptions.value = [];
      seatData.value = null;
  
      const { data } = await axios.get(`/chotoa?trainCode=${props.trainCode}&malichtrinh=${props.malichtrinh}`);
      console.log('API response:', data);
  
      if (data.success && Array.isArray(data.data)) {
        carOptions.value = data.data
          .map((car) => ({
            id: car.matoa,
            tentau: car.tentau,
            type: `${car.tentoa}: ${car.tenloaitoa}`,
            seatInfo: `Còn ${car.sochocon} chỗ trống`,
            tenloaitoa: car.tenloaitoa,
            cho: car.cho || [],
          }))
          .sort((a, b) => b.id - a.id);
  
        updateSelectedSeatsBasedOnNewData();
  
        const toa1Index = carOptions.value.findIndex((car) => car.type.includes('Toa 1:'));
        selectedCar.value = toa1Index !== -1 ? toa1Index : 0;
        selectCar(selectedCar.value);
  
        // Đợi DOM cập nhật trước khi scroll
        await nextTick();
        setInitialScrollPosition();
      } else {
        console.error('Invalid API data:', data);
      }
    } catch (error) {
      console.error('API fetch error:', error);
    } finally {
      isFetching.value = false;
    }
  };
  
  const updateSelectedSeatsBasedOnNewData = () => {
    const newCarTypes = carOptions.value.map(car => car.type);
    Object.keys(selectedSeatsByCar.value).forEach(carType => {
      if (!newCarTypes.includes(carType)) {
        delete selectedSeatsByCar.value[carType];
      } else {
        const car = carOptions.value.find(c => c.type === carType);
        if (car && car.cho) {
          selectedSeatsByCar.value[carType] = selectedSeatsByCar.value[carType].filter(seatId => {
            const seat = car.cho.find(s => s.macho === seatId);
            return seat && seat.trangthai === 'controng';
          });
        }
      }
    });
  };
  
  const selectCar = (index) => {
    selectedCar.value = index;
    const car = carOptions.value[index];
    if (car) {
      currentCarType.value = car.tenloaitoa;
      seatData.value = car;
      selectedSeatsByCar.value[car.type] = selectedSeatsByCar.value[car.type] || [];
    }
  };
  
  const setInitialScrollPosition = () => {
    if (!carSelectionRef.value) return; // Kiểm tra ref có tồn tại không
    const container = carSelectionRef.value;
    const imageHead = container.querySelector('.image-head');
    if (imageHead) {
      container.scrollLeft = imageHead.offsetLeft - container.offsetWidth + imageHead.offsetWidth;
    } else {
      container.scrollLeft = container.scrollWidth;
    }
  };
  
  const updateSelectedSeats = (carType, newSelected) => {
    if (carType) selectedSeatsByCar.value[carType] = newSelected;
  };
  
  const handleBook = (selectedSeats) => {
    const bookingData = {
      trainCode: props.trainCode,
      traintau: props.traintau,
      searchgadi: props.searchgadi,
      searchgaden: props.searchgaden,
      selectedDay: props.selectedDay,
      ticketDetails: props.ticketDetails,
      selectedSeatsByCar: selectedSeatsByCar.value,
      totalTickets: props.totalTickets,
      departureTime: props.departureTime,
      arrivalTime: props.arrivalTime,
    };
    console.log('Booking data:', bookingData);
    router.push({
      name: 'Payment',
      query: { data: JSON.stringify(bookingData) },
    });
    closeDialog();
  };
  
  // Lifecycle và Watch
  onMounted(() => {
    fetchTrainCars();
  });
  
  watch(
    () => props.malichtrinh,
    (newMalichtrinh, oldMalichtrinh) => {
      if (newMalichtrinh !== oldMalichtrinh) {
        console.log('malichtrinh changed:', newMalichtrinh);
        fetchTrainCars();
      }
    }
  );
  </script>
  
  <style scoped>
  @import url('@/assets/css/boking.css');
  </style>