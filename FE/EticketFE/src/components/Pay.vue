<template>
  <div>
    <h1>Thanh toán</h1>

    <!-- Hiển thị thông tin chuyến đi -->
    <h2>Chuyến đi</h2>
    <p><strong>Mã tàu:</strong> {{ departureData.trainCode }}</p>
    <p><strong>Tên tàu:</strong> {{ departureData.traintau }}</p>
    <p><strong>Giờ đi:</strong> {{ departureData.departureTime }}</p>
    <p><strong>Giờ đến:</strong> {{ departureData.arrivalTime }}</p>
    <p><strong>Điểm đi:</strong> {{ departureData.searchgadi }}</p>
    <p><strong>Điểm đến:</strong> {{ departureData.searchgaden }}</p>
    <p><strong>Ngày đi:</strong> {{ departureData.selectedDay }}</p>
    <p><strong>Tổng số vé:</strong> {{ departureData.totalTickets }}</p>
    <p><strong>Thông tin vé:</strong> {{ departureData.ticketDetails }}</p>
    <p><strong>Gía : {{ departureData.totalPrice }}</strong></p>
    <div>
      <p><strong>Ghế và giường đã chọn theo toa:</strong></p>
  <ul>
    <li v-for="(seatsOrBeds, carType) in departureData.selectedSeatsByCar" :key="carType">
      <strong>{{ carType }}:</strong>
      <span v-if="seatsOrBeds.length > 0">
        <ul>
          <li v-for="seatOrBed in seatsOrBeds" :key="seatOrBed.sohieu">
            {{ seatOrBed.sohieu }} - {{ seatOrBed.ticketType }} {{ seatOrBed.ticketNumber }}: 
            <strong>{{ seatOrBed.gia }}</strong>
          </li>
        </ul>
      </span>
      <span v-else>Chưa chọn ghế/giường</span>
    </li>
  </ul>
  </div>

    <!-- Hiển thị thông tin chuyến về nếu có -->
    <div v-if="returnData">
      <h2>Chuyến về</h2>
      <p><strong>Mã tàu:</strong> {{ returnData.trainCode }}</p>
      <p><strong>Tên tàu:</strong> {{ returnData.traintau }}</p>
      <p><strong>Giờ đi:</strong> {{ returnData.departureTime }}</p>
      <p><strong>Giờ đến:</strong> {{ returnData.arrivalTime }}</p>
      <p><strong>Điểm đi:</strong> {{ returnData.searchgadi }}</p>
      <p><strong>Điểm đến:</strong> {{ returnData.searchgaden }}</p>
      <p><strong>Ngày đi:</strong> {{ returnData.selectedDay }}</p>
      <p><strong>Tổng số vé:</strong> {{ returnData.totalTickets }}</p>
      <p><strong>Thông tin vé:</strong> {{ returnData.ticketDetails }}</p>
      <p><strong>Ghế và giường đã chọn theo toa:</strong></p>
      <div>
    <p><strong>Ghế và giường đã chọn theo toa:</strong></p>
    <ul>
      <li v-for="(seatsOrBeds, carType) in returnData.selectedSeatsByCar" :key="carType">
        <strong>{{ carType }}:</strong>
        <span v-if="seatsOrBeds.length > 0">
          {{ formatSeatsOrBeds(seatsOrBeds) }}
        </span>
      </li>
    </ul>
  </div>
    </div>

    <!-- Thêm logic thanh toán tại đây -->
    <button @click="proceedToPayment">Tiến hành thanh toán</button>
  </div>
</template>

<script setup>
import { useRoute } from "vue-router";
const formatSeatsOrBeds = (seatsOrBeds) => {
  return seatsOrBeds
    .map((item) => `${item.sohieu} (${item.ticketType} ${item.ticketNumber})`)
    .join(", ");
};
const route = useRoute();

// Lấy dữ liệu từ query
const bookingData = route.query.data ? JSON.parse(route.query.data) : {};

// Tách dữ liệu chuyến đi và chuyến về
const departureData = bookingData.departure || bookingData; // Dữ liệu chuyến đi (hoặc dữ liệu chính nếu là vé một chiều)
const returnData = bookingData.return || null; // Dữ liệu chuyến về (null nếu không có)



// Hàm xử lý thanh toán (ví dụ)
const proceedToPayment = () => {
  console.log("Processing payment with data:", { departureData, returnData });
  // Thêm logic thanh toán thực tế tại đây, ví dụ: gọi API thanh toán
  alert("Thanh toán thành công!"); // Ví dụ thông báo
};

console.log("Received booking data in Pay:", { departureData, returnData });
</script>

<style scoped>

</style>