<template>
  <div class="alo">
    <div class="payment">
      <div class="left">
        <Paycontact @update:contact-info="updateContactInfo" />
        <Payeticket 
          :departure-data="departureData" 
          :return-data="returnData"
          @update:passenger-info="updatePassengerInfo"
        />
        <VnpayPayment 
          :departure-data="departureData" 
          :return-data="returnData"
          :contact-info="contactInfo"
          :passenger-info="passengerInfo"
        />
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
import Paynote from "@/components/pay/Paynote.vue";
import VnpayPayment from "@/components/pay/VnpayPayment.vue";
import { useRoute } from "vue-router";
import { ref } from "vue";

const route = useRoute();
const bookingData = route.query.data ? JSON.parse(route.query.data) : {};

// Tách dữ liệu chuyến đi và chuyến về
const departureData = bookingData.departure || bookingData; // Dữ liệu chuyến đi
const returnData = bookingData.return || null; // Dữ liệu chuyến về (null nếu không có)

// Lưu trữ thông tin liên hệ và hành khách
const contactInfo = ref({});
const passengerInfo = ref([]);

// Cập nhật thông tin liên hệ từ component con
const updateContactInfo = (info) => {
  contactInfo.value = info;
};

// Cập nhật thông tin hành khách từ component con
const updatePassengerInfo = (info) => {
  passengerInfo.value = info;
};


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