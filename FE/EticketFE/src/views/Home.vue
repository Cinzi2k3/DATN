<template>
  <div class="nav-bar">
    <Navbar />
  </div>
  <div class="slide">
    <Slide />
    <div id="slide" class="etickethome">
      <Etickethome/>
    </div>
  </div>
  <div id="muc" class="Muc">
    <Muc />
  </div>
  <div id="map" class="Map">
    <Map />
  </div>
  <div id="tintuc" class="Tintuc">
    <Tintuc />
  </div>
  <div id="camnang" class="Camnang">
    <Camnang />
  </div>
  <div id="review" class="Review">
    <Review />
  </div>
  <div>
    <Tauchay />
  </div>
</template>

<script setup>
import Navbar from "@/components/home/Navbar.vue";
import Slide from "@/components/home/Slide.vue";
import Muc from "@/components/home/Muc.vue";
import Map from "@/components/home/Map.vue";
import Tintuc from "@/components/home/Tintuc.vue";
import Camnang from "@/components/home/Camnang.vue";
import Review from "@/components/home/Review.vue";
import Tauchay from "@/components/home/Tauchay.vue";
import Eticket from "@/components/booketicket/Eticket.vue";
import Etickethome from "@/components/home/Etickethome.vue";

</script>

<style scoped>
.nav-bar{
  background-color: white;
  padding: 2px 160px;
}
.slide{
  position: relative;
}
.Muc{
  background-color: white;
  padding: 5px 180px;
}
.Map{
  background-color:#ecf0f5 ;

}
.Tintuc{
  background-color: #ecf0f5;
  padding: 0px 180px;
  padding-top: 30px ;
}
.Camnang{
  background-color: #ecf0f5;
  padding: 0px 180px;
  padding-top: 30px ;
}
.Review{
  padding: 0px 180px;
  padding-top: 30px ;
}
.etickethome{
  position: absolute;
    z-index:1;
    bottom: 50px;
    margin-left: 180px;

}
</style>