<template>
  <el-card class="contact-card">
    <template #header>
      <div class="card-header">
        <h2>Thông tin liên hệ</h2>
      </div>
    </template>
    
    <el-form :model="form" label-position="top"  :rules="rules"  >
      <el-row :gutter="20">
        <el-col :span="8">
          <el-form-item label="Họ và tên" prop="name" required>
            <el-input 
              v-model="form.name" 
              placeholder="Nguyễn Văn A"
              @change="emitContactInfo"
              ref="input"   
            />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="Số điện thoại" prop="phone" required>
            <el-input 
              v-model="form.phone" 
              placeholder="0123456789"
              @change="emitContactInfo"
            />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="Email" prop="email" required>
            <el-input 
              v-model="form.email" 
              placeholder="example@gmail.com"
              @change="emitContactInfo"
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

const input = ref(null);

const emits = defineEmits(['update:contactInfo']);

const form = reactive({
  name: '',
  phone: '',
  email: '',
  eticketChecked: false
});

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
  { pattern: /^[a-zA-ZÀ-ỹ]+(\s[a-zA-ZÀ-ỹ]+)+$/, message: 'Nhập đầy đủ họ và tên', trigger: 'blur'}
]

};

const emitContactInfo = () => {
  emits('update:contactInfo', { ...form });
};

// Emit initial empty form
emitContactInfo();

onMounted(() => {
input.value.focus();
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