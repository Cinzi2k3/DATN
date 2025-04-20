<template>
  <div>
    <el-card class="passenger-card">
      <template #header>
        <div class="card-header">
          <h2>Thông tin hành khách</h2>
        </div>
      </template>

      <div>
        <div v-for="(ticket, index) in tickets" :key="index" class="passenger-section">
          <h3 class="passenger-type">
            {{ ticket.departure?.ticketType === 'Người lớn' ? 'Người lớn (Từ 11 tuổi trở lên)' : 'Trẻ em (Từ 6-10 tuổi)' }}
          </h3>

          <el-row gutter="0" class="pb-3">
            <!-- Chiều đi -->
            <el-col :span="10" class="departure-return">
              <el-row class="p-2">
                <el-col :span="12">
                  <p class="seat-label">Chiều đi</p>
                  <p class="seat-value">{{ ticket.departure ? getCarNumber(ticket.departure.carType) : 'N/A' }}</p>
                </el-col>
                <el-col :span="12" class="text-end">
                  <p class="seat-label">{{ ticket.departure ? getCarType(ticket.departure.carType) : 'N/A' }}</p>
                  <p class="seat-value">
                    {{ ticket.departure ? `${ticket.departure.seat} - ${formatTotalPrice(ticket.departure.price)}` : 'N/A' }}
                  </p>
                </el-col>
              </el-row>
            </el-col>
            <div style="width: 30px;"></div>
            <!-- Chiều về -->
            <el-col v-if="ticket.return" :span="10" class="departure-return">
              <el-row class="p-2">
                <el-col :span="10">
                  <p class="seat-label">Chiều về</p>
                  <p class="seat-value">{{ ticket.return ? getCarNumber(ticket.return.carType) : 'N/A' }}</p>
                </el-col>
                <el-col :span="12" class="text-end">
                  <p class="seat-label">{{ ticket.return ? getCarType(ticket.return.carType) : 'N/A' }}</p>
                  <p class="seat-value">
                    {{ ticket.return ? `${ticket.return.seat} - ${formatTotalPrice(ticket.return.price)}` : 'N/A' }}
                  </p>
                </el-col>
              </el-row>
            </el-col>
          </el-row>

          <!-- Thông tin hành khách -->
          <el-form :model="form[index]" label-position="top" :rules="rules">
            <el-row :gutter="20">
              <el-col :span="8">
                <el-form-item label="Họ và tên" prop="name" required>
                  <el-input 
                    v-model="form[index].name" 
                    placeholder="Vd: Nguyễn Văn Nam"
                    @change="emitPassengerInfo"
                  />
                </el-form-item>
              </el-col>

              <el-col :span="8">
                <el-form-item label="Ngày sinh" required>
                  <el-row :gutter="8">
                    <el-col :span="8">
                      <el-form-item prop="day">
                        <el-input v-model="form[index].day" placeholder="DD" />
                      </el-form-item>
                    </el-col>
                    <el-col :span="8">
                      <el-form-item prop="month">
                        <el-input v-model="form[index].month" placeholder="MM" />
                      </el-form-item>
                    </el-col>
                    <el-col :span="8">
                      <el-form-item prop="year">
                        <el-input v-model="form[index].year" placeholder="YYYY" />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-form-item>
              </el-col>

              <el-col :span="8">
                <el-form-item prop="idNumber">
                    <div class="label-with-icon">
                      CCCD / Passport
                      <el-tooltip content="Thông tin được yêu cầu bởi đường sắt Việt Nam" placement="top">
                        <el-icon><InfoFilled /></el-icon>
                      </el-tooltip>
                    </div>
                  <el-input 
                    v-model="form[index].idNumber" 
                    placeholder="Nhập CCCD hoặc Passport"
                    @change="emitPassengerInfo"
                  />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { reactive, computed, watch } from 'vue';
import { InfoFilled } from '@element-plus/icons-vue';
import { useFormatPrice } from '@/composables/useFormatprice';

const emits = defineEmits(['update:passengerInfo']);
const { formatTotalPrice } = useFormatPrice();

// Nhận dữ liệu từ props
const props = defineProps({
  departureData: {
    type: Object,
    required: true,
  },
  returnData: {
    type: Object,
    default: null,
  },
});

const rules = {
  name: [
  { required: true, message: 'Vui lòng nhập họ và tên', trigger: 'blur' },
  { pattern: /^[a-zA-ZÀ-ỹ]+(\s[a-zA-ZÀ-ỹ]+)+$/, message: 'Nhập đầy đủ họ và tên', trigger: 'blur'}
 ],
  day: [
    { required: true, message: 'Nhập ngày', trigger: 'blur' },
    { pattern: /^(0?[1-9]|[12][0-9]|3[01])$/, message: 'Ngày không hợp lệ', trigger: 'blur' }
  ],
  month: [
    { required: true, message: 'Nhập tháng', trigger: 'blur' },
    { pattern: /^(0?[1-9]|1[0-2])$/, message: 'Tháng không hợp lệ', trigger: 'blur' }
  ],
  year: [
    { required: true, message: 'Nhập năm', trigger: 'blur' },
    { pattern: /^(19|20)\d{2}$/, message: 'Năm không hợp lệ', trigger: 'blur' }
  ],
  idNumber: [
    { required: true, message: 'Vui lòng nhập CCCD/Passport', trigger: 'blur' },
    { min: 9, message: 'CCCD/Passport phải có ít nhất 9 ký tự', trigger: 'blur' }
  ]
};

// Tạo danh sách vé kết hợp từ departureData và returnData
const tickets = computed(() => {
  const ticketList = [];

  // Lấy tất cả ghế từ departureData
  let departureSeats = [];
  for (const carType in props.departureData.selectedSeatsByCar) {
    const seats = props.departureData.selectedSeatsByCar[carType];
    if (Array.isArray(seats)) {
      departureSeats = departureSeats.concat(
        seats.map((seat) => ({
          carType,
          seat: seat.sohieu,
          price: seat.gia,
          ticketType: seat.ticketType,
        }))
      );
    }
  }

  // Lấy tất cả ghế từ returnData
  let returnSeats = [];
  if (props.returnData && props.returnData.selectedSeatsByCar) {
    for (const carType in props.returnData.selectedSeatsByCar) {
      const seats = props.returnData.selectedSeatsByCar[carType];
      if (Array.isArray(seats)) {
        returnSeats = returnSeats.concat(
          seats.map((seat) => ({
            carType,
            seat: seat.sohieu,
            price: seat.gia,
            ticketType: seat.ticketType,
          }))
        );
      }
    }
  }

  // Ghép thông tin ghế "Chiều đi" và "Chiều về"
  const maxTickets = Math.max(departureSeats.length, returnSeats.length);
  for (let i = 0; i < maxTickets; i++) {
    const ticket = {
      departure: departureSeats[i] || null,
      return: returnSeats[i] || null,
    };
    ticketList.push(ticket);
  }

  return ticketList;
});

// Chuyển cartype thành số toa
const getCarNumber = (carType) => {
  if (typeof carType === 'string' && carType.includes(':')) {
    return carType.split(':')[0].trim();
  }
  return carType;
};

// Chuyển cartype thành loại toa
const getCarType = (carType) => {
  if (typeof carType === 'string' && carType.includes(':')) {
    return carType.split(':')[1].trim();
  }
};

// Khởi tạo form cho từng vé
const form = reactive(
  tickets.value.map(() => ({
    name: '',
    day: '',
    month: '',
    year: '',
    idNumber: '',
  }))
);

// Emit thông tin hành khách khi có thay đổi
const emitPassengerInfo = () => {
  const passengerInfo = form.map((passenger, index) => ({
    ...passenger,
    ticketInfo: tickets.value[index]
  }));
  emits('update:passengerInfo', passengerInfo);
};

// Emit thông tin ban đầu
emitPassengerInfo();
</script>

<style scoped>
.passenger-card {
  margin-bottom: 20px;
}

.card-header h2 {
  margin: 0;
  font-size: 18px;
  color: #104c8a;
}

.passenger-type {
  font-weight: 500;
  margin-bottom: 16px;
}

.seat-label {
  color: #718096;
  margin-bottom: 4px;
  font-size: 14px;
}

.seat-value {
  font-size: 15px;
  font-weight: 500;
  margin-bottom: 3px;
}

.label-with-icon {
  display: flex;
  align-items: center;
  gap: 5px;
}

.label-with-icon .el-icon {
  font-size: 14px;
  color: #909399;
}

.departure-return {
  background-color: #f9f9fa;
  border-radius: 5px;
}
</style>