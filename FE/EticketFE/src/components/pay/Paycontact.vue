<template>
  <el-card class="contact-card">
    <template #header>
      <div class="card-header">
        <h2>Thông tin liên hệ</h2>
      </div>
    </template>
    
    <el-form :model="form" label-position="top" :rules="rules" ref="contactForm">
      <el-row :gutter="20">
        <el-col :span="8">
          <el-form-item label="Họ và tên" prop="name" required>
            <el-input 
              v-model="form.name" 
              placeholder="Nguyễn Văn A"
              @change="emitContactInfo"
              ref="nameInput"   
            />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="Số điện thoại" prop="phone" required>
            <el-input 
              v-model="form.phone" 
              placeholder="0123456789"
              @change="emitContactInfo"
              ref="phoneInput"
            />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="Email" prop="email" required>
            <el-input 
              v-model="form.email" 
              placeholder="example@gmail.com"
              @change="emitContactInfo"
              ref="emailInput"
            />
          </el-form-item>
        </el-col>
      </el-row>
      
      <el-checkbox v-model="form.eticketChecked" @change="emitContactInfo">
        Xuất hóa đơn điện tử
      </el-checkbox>
    </el-form>
  </el-card>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/authStore';
// Khởi tạo refs cho các input để focus
const nameInput = ref(null);
const phoneInput = ref(null);
const emailInput = ref(null);
const contactForm = ref(null);

// Khởi tạo authStore
const authStore = useAuthStore();

// Khởi tạo emits
const emits = defineEmits(['update:contactInfo']);

// Khởi tạo form với giá trị mặc định
const form = reactive({
  name: '',
  phone: '',
  email: '',
  eticketChecked: false
});

// Quy tắc validation
const rules = {
  phone: [
    { required: true, message: 'Vui lòng nhập số điện thoại', trigger: 'blur' },
    { pattern: /(84|0[3|5|7|8|9])+([0-9]{8})\b/, message: 'Số điện thoại không hợp lệ', trigger: 'blur' }
  ],
  email: [
    { required: true, message: 'Vui lòng nhập email', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9._%+-]+@gmail\.com$/, message: 'Email không hợp lệ', trigger: 'blur' }
  ],
  name: [
    { required: true, message: 'Vui lòng nhập họ và tên', trigger: 'blur' },
    { pattern: /^[a-zA-ZÀ-ỹ]+(\s[a-zA-ZÀ-ỹ]+)+$/, message: 'Nhập đầy đủ họ và tên', trigger: 'blur' }
  ]
};

// Hàm emit thông tin liên hệ
const emitContactInfo = () => {
  emits('update:contactInfo', { ...form });
};

// Điền thông tin người dùng và focus vào trường email
onMounted(() => {
  if (authStore.isLoggedIn) {
    form.name = authStore.userName || '';
    form.email = authStore.userEmail || '';
    form.phone = authStore.phoneNumber || '';
    emitContactInfo(); // Emit ngay sau khi điền
    if (emailInput.value) {
      emailInput.value.focus(); // Focus vào trường email
    }
  } else {
    if (nameInput.value) {
      nameInput.value.focus(); // Focus vào trường name nếu chưa đăng nhập
    }
    emitContactInfo(); // Emit form rỗng
  }
});
</script>

<style scoped>
.contact-card {
  margin-bottom: 20px;
}

.card-header h2 {
  margin: 0;
  font-size: 18px;
  color: #104c8a;
}

:deep(.el-card__header) {
  padding: 15px 20px;
}

:deep(.el-form-item__label) {
  font-weight: 500;
}
</style>