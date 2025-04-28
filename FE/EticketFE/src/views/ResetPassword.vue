<template>
  <div class="reset-password-container">
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
    </div>
    <div class="form-container">
      <h1>{{ $t('Đặt lại mật khẩu') }}</h1>
      
      <!-- Step 1: Enter Email -->
      <form v-if="!showCodeInput" @submit.prevent="requestResetCode" style="text-align: center;">
        <input 
          type="email" 
          v-model="email" 
          :placeholder="$t('Email')" 
          required 
        />
        <button type="submit" :disabled="loading">
          {{ $t('Gửi mã xác nhận') }}
        </button>
      </form>

      <!-- Step 2: Enter Code and New Password -->
      <form v-else @submit.prevent="resetPassword" style="text-align: center;">
        <input 
          type="text" 
          v-model="code" 
          :placeholder="$t('Mã xác nhận')" 
          required 
        />
        <input 
          type="password" 
          v-model="newPassword" 
          :placeholder="$t('Mật khẩu mới')" 
          required 
        />
        <input 
          type="password" 
          v-model="newPasswordConfirmation" 
          :placeholder="$t('Xác nhận mật khẩu mới')" 
          required 
        />
        <button type="submit" :disabled="loading">
          {{ $t('Đặt lại mật khẩu') }}
        </button>
      </form>

      <button class="back-button" @click="goBack">
        {{ $t('Quay lại đăng nhập') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { ElNotification } from 'element-plus';

const email = ref('');
const code = ref('');
const newPassword = ref('');
const newPasswordConfirmation = ref('');
const showCodeInput = ref(false);
const loading = ref(false);
const router = useRouter();

const requestResetCode = async () => {
  loading.value = true;
  try {
    const response = await axios.post('/password/email', {
      email: email.value
    });
    
    if (response.data.success) {
      showCodeInput.value = true;
      ElNotification.success(response.data.message);
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi gửi mã xác nhận';
    ElNotification.error(errorMessage);
  } finally {
    loading.value = false;
  }
};

const resetPassword = async () => {
  loading.value = true;
  try {
    const response = await axios.post('/password/reset', {
      email: email.value,
      code: code.value,
      new_password: newPassword.value,
      new_password_confirmation: newPasswordConfirmation.value
    });
    
    if (response.data.success) {
      ElNotification.success(response.data.message);
      router.push('/Signin');
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi đặt lại mật khẩu';
    ElNotification.error(errorMessage);
  } finally {
    loading.value = false;
  }
};

const goBack = () => {
  router.push('/Signin');
};
</script>

<style scoped>
.reset-password-container {
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.form-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

h1 {
  margin-bottom: 20px;
  color: #333;
}

input {
  width: 21rem;
  padding: 10px;
  margin: 10px 0;
  border: 1px solid #ddd;
  border-radius: 5px;
}

button {
  width: 21rem;
  padding: 10px;
  margin: 10px 0;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:disabled {
  background: #cccccc;
  cursor: not-allowed;
}

.back-button {
  background: transparent;
  color: #007bff;
  width: 21rem;
  border: 1px solid #007bff;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #007bff;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
