<template>
  <el-card class="contact-card">
    <template #header>
      <div class="card-header">
        <h2>Thông tin liên hệ</h2>
      </div>
    </template>
    
    <el-form :model="form" label-position="top">
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="Số điện thoại" required>
            <el-input 
              v-model="form.phone" 
              placeholder="0123456789"
              @change="emitContactInfo"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="Email" required>
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
import { reactive, watch } from 'vue';

const emits = defineEmits(['update:contactInfo']);

const form = reactive({
  phone: '',
  email: '',
  eticketChecked: false
});

const emitContactInfo = () => {
  emits('update:contactInfo', { ...form });
};

// Emit initial empty form
emitContactInfo();
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