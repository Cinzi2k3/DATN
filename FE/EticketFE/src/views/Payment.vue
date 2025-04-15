<template>
  <div class="alo">
    <div class="payment">
      <div class="left">
        <Paycontact />
        <Payeticket :departure-data="departureData" :return-data="returnData" />
        <Paybutton />
      </div>
      <div class="right">
        <Payschedule
          :departure-data="departureData"
          :return-data="returnData"
        />
        <Paynote/>
      </div>
    </div>
  </div>
</template>
  
  <script setup>
import Paycontact from "@/components/pay/Paycontact.vue";
import Payeticket from "@/components/pay/Payeticket.vue";
import Payschedule from "@/components/pay/Payschedule.vue";
import Paybutton from "@/components/pay/Paybutton.vue";
import Paynote from "@/components/pay/Paynote.vue";
import { useRoute } from "vue-router";

const route = useRoute();
const bookingData = route.query.data ? JSON.parse(route.query.data) : {};

// Tách dữ liệu chuyến đi và chuyến về
const departureData = bookingData.departure || bookingData; // Dữ liệu chuyến đi
const returnData = bookingData.return || null; // Dữ liệu chuyến về (null nếu không có)
</script>
  
  <style scoped>
.alo {
  background-color: #f2f7fa;
}
.payment {
  display: flex;
  max-width: 1200px;
  margin: 0px 180px;
  gap: 15px;
}

.left {
  width: 65%;
  margin: 20px 0px;
}

.right {
  width: 35%;
  margin: 20px 0px;
}

/* Responsive adjustments for smaller screens */
@media (max-width: 1200px) {
  .payment {
    margin: 10px 40px;
  }
}

@media (max-width: 768px) {
  .payment {
    flex-direction: column;
    margin: 10px 20px;
  }

  .left,
  .right {
    width: 100%;
  }
}
</style>