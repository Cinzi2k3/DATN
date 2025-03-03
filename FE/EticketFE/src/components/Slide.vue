<template>
    <div class="slideshow-container">
      <div
        v-for="(slide, index) in slides"
        :key="index"
        :class="['slide', { active: index === currentIndex }]"
      >
        <img :src="slide.src" alt="Slide Image" />
      </div>
    </div>
  </template>
  
  <script lang="ts" setup>
  import {ref, onMounted} from "vue";
  
  const slides = ref([
    { src: "/src/assets/img/slide1.jpg" },
    { src: "/src/assets/img/slide2.jpg" },
    { src: "/src/assets/img/slide4.jpg" },
  ])
  const currentIndex = ref(0);
  const nextSlide = () => {
    currentIndex.value = (currentIndex.value + 1) % slides.value.length;
  }
  const preveSlide = () => {
    currentIndex.value = (currentIndex.value -1 + slides.value.length) % slides.value.length;
  }
  const startSlideShow = () =>{
    setInterval(nextSlide , 5000)
  }
  
  onMounted(() =>{
  startSlideShow();
  })
  </script>
  
  <style scoped>
  @import url('@/assets/css/slide.css');
  </style>
  