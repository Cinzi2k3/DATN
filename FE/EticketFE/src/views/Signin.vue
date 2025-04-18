<template>
  <div class="container" :class="{ 'right-panel-active': isSignUp }">
    <div v-if="loading" class="loading-overlay">
      <!-- Spinner -->
      <div class="spinner"></div>
    </div>
    <!-- Sign Up Form -->
    <div class="form-container sign-up-container">
      <form @submit.prevent="signup">
        <h1>{{ $t('Tạo tài khoản')}}</h1>
        <div class="social-container">
          <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
          <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <input type="name" v-model="name" placeholder="Name" required />
        <input type="email" v-model="email" placeholder="Email" required />
        <input
          type="password"
          v-model="password"
          placeholder="Password"
          required
        />
        <button type="submit" :disabled="loading">{{ $t('Đăng ký')}}</button>
      </form>
    </div>

    <!-- Sign In Form -->
    <div class="form-container sign-in-container">
      <form @submit.prevent="signin">
        <h1>{{ $t('Đăng nhập')}}</h1>
        <div class="social-container">
          <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
          <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <input type="email" v-model="email" placeholder="Email" required />
        <input
          type="password"
          v-model="password"
          placeholder="Password"
          required
        />
        <a href="#">{{ $t('Quên mật khẩu?')}}</a>
        <button type="submit" :disabled="loading">{{ $t('Đăng nhập')}}</button>
      </form>
    </div>

    <!-- Overlay Panel for Switching -->
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <h1>{{ $t('Chào mừng bạn trở lại')}}</h1>
          <p>
            {{ $t('Để giữ liên lạc với chúng tôi, vui lòng đăng nhập bằng thông tin cá nhân của bạn')}}
          </p>
          <button class="ghost" @click="toggleSignIn">{{ $t('Đăng nhập')}}</button>
        </div>
        <div class="overlay-panel overlay-right">
          <h1>{{ $t('Chào bạn')}}</h1>
          <p>
            {{ $t('Nhập thông tin cá nhân của bạn và bắt đầu hành trình cùng chúng tôi')}}
          </p>
          <button class="ghost" @click="toggleSignUp">{{ $t('Đăng ký')}}</button>
        </div>
      </div>
    </div>
  </div>
</template>
  
<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { ElNotification } from "element-plus";
import { useAuthStore } from '@/stores/authStore';

const authStore = useAuthStore();
const name = ref("");
const email = ref("");
const password = ref("");
const router = useRouter();
const loading = ref(false); 

const signup = async () => {
  loading.value = true;
  try {
    const response = await axios.post("/register", {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password.value,
    });
    isSignUp.value = false;
    ElNotification.success("Đăng kí thành công");
  } catch (error) {
    ElNotification.error("Có lỗi xảy ra trong quá trình đăng kí");
  } finally {
    loading.value = false; 
  }
};

//đăng nhập
const signin = async () => {
  loading.value = true;
  try {
    // Gửi yêu cầu đăng nhập
    const loginResponse = await axios.post("/login", {
      email: email.value,
      password: password.value,
    });
    ElNotification.success("Đăng nhập thành công");
    // Gửi yêu cầu lấy thông tin người dùng
    const userResponse = await axios.get(`/user?email=${email.value}`);
    if (userResponse.data.success) {
      const userName = userResponse.data.user.name;
      // Lưu tên vào authStore
      authStore.login(userName);
      ElNotification.success(`Xin chào, ${userName}`);
    } else {
      ElNotification.error("Không thể lấy thông tin người dùng");
    }
    router.push("/");
  } catch (error) {
    if (error.response) {
      alert(error.response.data.message || "Thông tin đăng nhập không hợp lệ");
    } else {
      alert("Đã có lỗi xảy ra, vui lòng thử lại");
    }
  } finally {
    loading.value = false;
  }
};

// Biến theo dõi trạng thái Sign Up hay Sign In
const isSignUp = ref(false);

// Hàm chuyển sang giao diện đăng ký
const toggleSignUp = () => {
  isSignUp.value = true;
};

// Hàm chuyển sang giao diện đăng nhập
const toggleSignIn = () => {
  isSignUp.value = false;
};
</script>
  

<style scoped>
@import url(@/assets/css/signin.css);
</style>
  