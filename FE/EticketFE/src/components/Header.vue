<template>
  <div class="container">
    <el-row :gutter="20">
      <el-col :span="5">
        <a href="/">
          <img src="@/assets/img/logo.png" style="height: 75px;margin-top:2px" />
        </a>
      </el-col>
      <el-col :span="8">
        <div class="evo-search-form headerSearch">
          <div class="banner-marquee" shadow="hover">
            <div
              class="scrolling-text"
              :style="{ transform: `translateX(${offset}px)` }"
              @mouseover="pauseScrolling"
              @mouseleave="resumeScrolling"
            >
              <h2 class="h2-title-xx">
                <el-link style="top: -5px">
                  {{
                    $t(
                      'Đơn vị hỗ trợ hình thức thanh toán quét QR phí thanh toán là 0 đồng, ngoài hình thức thanh toán này quý khách lựa chọn thanh toán thẻ visa, ứng dụng ví sẽ mất phí do cổng thanh toán onepay thu, phí thanh toán sẽ không nằm trong giá vé, chúng tôi miễn trừ trách nhiệm về phí này. Giá vé hiển thị đã bao gồm phí dịch vụ vận hành hệ thống và thuế giá trị gia tăng từ 8%-10%. Đơn vị chúng tôi cam kết minh bạch giá cả và những khoản phí mà đơn vị thu, không có ẩn giá đến bước cuối mới hiển thị! Xin cảm ơn!'
                    )
                  }}
                </el-link>
              </h2>
            </div>
          </div>
        </div>
      </el-col>
      <el-col :span="11">
        <el-row style="top: 22px">
          <el-col :span="6">
            <el-dropdown class="dropdown">
              <div style="display: flex; color: white">
                <i class="fa-solid fa-user"></i>
                <p>{{ $t("Tài khoản") }}</p>
              </div>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item>{{ $t("Đăng nhập") }}</el-dropdown-item>
                  <el-dropdown-item>{{ $t("Đăng ký") }}</el-dropdown-item>
                  <el-dropdown-item>{{ $t("Quên mật khẩu") }}</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </el-col>
          <el-col :span="7">
            <el-button class="phone" type="text">
              <el-icon><Phone /></el-icon>
              <span>1900.59.99.97</span>
            </el-button>
          </el-col>
          <el-col :span="3">
            <div class="vien">
              <img
                src="@/assets/img/vi.png"
                alt="Vietnamese"
                @click="changeLanguage('vi')"
                style="cursor: pointer; width: 30px;height: 35x;margin-top: 4px;"
              />
              <img
                src="@/assets/img/en.png"
                alt="English"
                @click="changeLanguage('en')"
                style="cursor: pointer; width: 29px;height: 27px;margin-top: 5px;"
              />
            </div>
          </el-col>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { Phone } from "@element-plus/icons-vue";
import { ref, onMounted, onUnmounted } from "vue";
import { useI18n } from "vue-i18n";

const offset = ref(0); // Vị trí của dòng chữ
let scrollInterval = null; // Lưu interval để dừng/tái khởi động
const speed = 1; // Tốc độ cuộn

// Khởi chạy cuộn
const startScrolling = () => {
  scrollInterval = setInterval(() => {
    offset.value -= speed;
    if (offset.value < -2000) offset.value = 400; // Reset vị trí khi ra ngoài
  }, 16);
};

// Tạm dừng cuộn
const stopScrolling = () => clearInterval(scrollInterval);

// Khi di chuột vào, dừng cuộn
const pauseScrolling = () => stopScrolling();

// Khi rời chuột, tiếp tục cuộn
const resumeScrolling = () => startScrolling();

// Lifecycle hooks
onMounted(() => startScrolling());
onUnmounted(() => stopScrolling());


const { locale } = useI18n(); // Lấy locale từ i18n

// Hàm thay đổi ngôn ngữ
const changeLanguage = (lang) => {
  locale.value = lang; // Đặt giá trị ngôn ngữ
};
</script>

<style scoped>
@import url(@/assets/css/header.css);
</style>
