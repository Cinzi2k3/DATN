<template>
  <div class="nav-container">
    <div
      class="nav-item"
      v-for="(item, index) in translatedItems"
      :key="index"
    >
      <a href="#" class="nav-link" @click.prevent="scrollToSection(index)">{{ item }}</a>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();  

const keys = [
  "TRANG CHỦ",
  "MUA VÉ",
  "BẢN ĐỒ ĐƯỜNG SẮT",
  "TIN TỨC",
  "CẨM NANG",
  "ĐÁNH GIÁ",
  "LIÊN HỆ",
];

const translatedItems = computed(() => keys.map((key) => t(key)));

const sectionIds = [
  "home",
  "slide",
  "map",
  "tintuc",
  "camnang",
  "review",
  "footer"
];

const scrollToSection = (index) => {
  const sectionId = sectionIds[index];
  const element = document.getElementById(sectionId);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>

<style scoped>
@import url(@/assets/css/navbar.css);
</style>