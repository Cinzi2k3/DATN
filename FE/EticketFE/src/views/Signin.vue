```vue
<template>
  <div class="container" :class="{ 'right-panel-active': isSignUp }">
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
    </div>
    <!-- Sign Up Form -->
    <div class="form-container sign-up-container">
      <form @submit.prevent="signup">
        <h1>{{ $t('Tạo tài khoản') }}</h1>
        <div class="social-container">
          <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
          <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <input type="text" v-model="name" :placeholder="$t('Họ và tên')" required />
        <input type="email" v-model="email" :placeholder="$t('Email')" required />
        <input type="password" v-model="password" :placeholder="$t('Mật khẩu')" required />
        <input type="password" v-model="passwordConfirmation" :placeholder="$t('Xác nhận mật khẩu')" required />
        <button type="submit" :disabled="loading">{{ $t('Đăng ký') }}</button>
      </form>
    </div>

    <!-- Sign In Form -->
    <div class="form-container sign-in-container">
      <form @submit.prevent="signin">
        <h1>{{ $t('Đăng nhập') }}</h1>
        <div class="social-container">
          <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
          <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <input type="email" v-model="email" :placeholder="$t('Email')" required />
        <input type="password" v-model="password" :placeholder="$t('Mật khẩu')" required />
        <router-link to="/reset-password">{{ $t('Quên mật khẩu?') }}</router-link>
        <button type="submit" :disabled="loading">{{ $t('Đăng nhập') }}</button>
      </form>
    </div>

    <!-- Overlay Panel for Switching -->
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <h1>{{ $t('Chào mừng bạn trở lại') }}</h1>
          <p>{{ $t('Để giữ liên lạc với chúng tôi, vui lòng đăng nhập bằng thông tin cá nhân của bạn') }}</p>
          <button class="ghost" @click="toggleSignIn">{{ $t('Đăng nhập') }}</button>
        </div>
        <div class="overlay-panel overlay-right">
          <h1>{{ $t('Chào bạn') }}</h1>
          <p>{{ $t('Nhập thông tin cá nhân của bạn và bắt đầu hành trình cùng chúng tôi') }}</p>
          <button class="ghost" @click="toggleSignUp">{{ $t('Đăng ký') }}</button>
        </div>
      </div>
    </div>

    <!-- Verification Code Dialog -->
    <el-dialog title="Xác nhận email" v-model="showVerificationDialog" width="400px" center>
      <el-form :model="verificationForm" :rules="verificationRules" ref="verificationFormRef" label-width="120px">
        <el-form-item label="Mã xác nhận" prop="code">
          <el-input v-model="verificationForm.code" placeholder="Nhập mã xác nhận" maxlength="6"></el-input>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showVerificationDialog = false">Hủy</el-button>
        <el-button type="primary" @click="verifyCode">Xác nhận</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { ElNotification } from 'element-plus';
import { useAuthStore } from '@/stores/authStore';

const authStore = useAuthStore();
const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const router = useRouter();
const loading = ref(false);
const isSignUp = ref(false);
const showVerificationDialog = ref(false);
const verificationForm = ref({ email: '', code: '' });
const verificationFormRef = ref(null);

const verificationRules = {
  code: [
    { required: true, message: 'Vui lòng nhập mã xác nhận', trigger: 'blur' },
    { pattern: /^\d{6}$/, message: 'Mã xác nhận phải là 6 chữ số', trigger: 'blur' },
  ],
};

const signup = async () => {
  if (password.value !== passwordConfirmation.value) {
    ElNotification.error('Mật khẩu xác nhận không khớp');
    return;
  }

  loading.value = true;
  try {
    const response = await axios.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });
    ElNotification.success(response.data.message);
    verificationForm.value.email = email.value;
    showVerificationDialog.value = true;
    name.value = '';
    email.value = '';
    password.value = '';
    passwordConfirmation.value = '';
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra trong quá trình đăng ký';
    ElNotification.error(errorMessage);
  } finally {
    loading.value = false;
  }
};

const verifyCode = async () => {
  if (!verificationFormRef.value) return;
  try {
    await verificationFormRef.value.validate();
    loading.value = true;
    const response = await axios.post('/verify-code', {
      email: verificationForm.value.email,
      code: verificationForm.value.code,
    });
    ElNotification.success(response.data.message);
    showVerificationDialog.value = false;
    isSignUp.value = false;
    verificationFormRef.value.resetFields();
    verificationForm.value.email = '';
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Xác nhận mã thất bại';
    ElNotification.error(errorMessage);
  } finally {
    loading.value = false;
  }
};

const signin = async () => {
  loading.value = true;
  try {
    const loginResponse = await axios.post('/login', {
      email: email.value,
      password: password.value,
    });
    const userResponse = await axios.get(`/user?email=${email.value}`);
    if (userResponse.data.success) {
      const { id, name, email, phone_number, address, birth_date } = userResponse.data.user;
      authStore.login(id, name, email, phone_number, address, birth_date);
      ElNotification.success(`Xin chào, ${name}`);
      router.push('/');
    } else {
      ElNotification.error('Thông tin tài khoản hoặc mật khẩu không chính xác');
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Thông tin tài khoản hoặc mật khẩu không chính xác';
    ElNotification.error(errorMessage);
  } finally {
    loading.value = false;
  }
};

const toggleSignUp = () => {
  isSignUp.value = true;
};

const toggleSignIn = () => {
  isSignUp.value = false;
};
</script>

<style scoped>
@import url(@/assets/css/signin.css);
</style>