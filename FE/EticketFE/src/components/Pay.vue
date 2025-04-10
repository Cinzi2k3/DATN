<template>
  <div>
    <h1>Thanh toán</h1>
    <p>Mã tàu: {{ bookingData.trainCode }}</p>
    <p>Tên tàu: {{ bookingData.traintau }}</p>
    <p>Giờ đi:{{ bookingData.departureTime }}</p>
    <p>Giờ đến:{{ bookingData.arrivalTime }}</p>
    <p>Điểm đi: {{ bookingData.searchgadi }}</p>
    <p>Điểm đến: {{ bookingData.searchgaden }}</p>
    <p>Ngày đi: {{ bookingData.selectedDay }}</p>
    <p>Thông tin vé: {{ bookingData.ticketDetails }}</p>
    <p>Ghế và giường đã chọn theo toa:</p>
<ul>
  <li v-for="(seatsOrBeds, carType) in bookingData.selectedSeatsByCar" :key="carType">
    <strong>{{ carType }}</strong>: {{ seatsOrBeds.join(', ') }}
  </li>
</ul>
    <p>Tổng số vé: {{ bookingData.totalTickets }}</p>
    <!-- Thêm logic thanh toán tại đây -->
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

// Lấy dữ liệu từ query
const bookingData = route.query.data ? JSON.parse(route.query.data) : {};

console.log('Received booking data in Pay:', bookingData);
</script>

<style scoped>

</style>
