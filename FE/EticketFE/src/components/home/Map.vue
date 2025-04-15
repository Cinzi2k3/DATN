<template> 
<div class="map">
  <strong class="bd">{{ $t('Bản đồ tuyến đường sắt Việt Nam')}}</strong> 
  <p style="font-size:18px">{{ $t('Xem thông tin chi tiết về các tuyến tàu, lịch trình và điểm dừng')}}</p>
  <img src="/src/assets/img/bando.webp" alt="Map" style="width:100%"  />
</div>
</template>

<script setup>

</script>

<style scoped>
.map{
    width : 100%;
    padding: 0px 180px;
    padding-top: 30px;
}
.bd{
    font-size: 35px;
}

</style>