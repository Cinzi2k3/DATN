<template>
  <div class="admin-ticket-management">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2>Quản lý loại vé</h2>
        </div>
      </div>

    <!-- Add Form -->
    <div class="form-container">
      <el-card class="form-card">
        <el-form 
          :model="newCategory" 
          :rules="rules" 
          ref="categoryForm" 
          class="simple-form"
        >
          <div class="form-row">
            <el-form-item prop="label">
              <el-input 
                v-model="newCategory.label" 
                placeholder="Tên loại vé"
                size="large"
                class="clean-input"
              />
            </el-form-item>
            
            <el-form-item prop="description">
              <el-input 
                v-model="newCategory.description" 
                placeholder="Mô tả"
                size="large"
                class="clean-input"
              />
            </el-form-item>
            
            <el-form-item prop="discount">
              <el-input-number 
                v-model="newCategory.discount" 
                :min="0" 
                :max="100" 
                placeholder="% Giảm giá"
                size="large"
                class="clean-input"
              />
            </el-form-item>
            
            <el-form-item>
              <el-button 
                type="primary" 
                @click="submitCategory"
                size="large"
                class="add-btn"
              >
                Thêm
              </el-button>
            </el-form-item>
          </div>
        </el-form>
      </el-card>
    </div>

    <!-- Table -->
    <div class="table-container">
      <el-card class="table-card">
        <el-table 
          :data="ticketCategories" 
          class="clean-table"
          empty-text="Chưa có dữ liệu"
        >
          <el-table-column prop="label" label="Loại vé" min-width="200">
            <template #default="scope">
              <span class="category-name">{{ scope.row.label }}</span>
            </template>
          </el-table-column>
          
          <el-table-column prop="description" label="Mô tả" min-width="250">
            <template #default="scope">
              <span class="description">{{ scope.row.description || '-' }}</span>
            </template>
          </el-table-column>
          
          <el-table-column prop="discount" label="Giảm giá" width="120" align="center">
            <template #default="scope">
              <span class="discount-value" v-if="scope.row.discount">
                {{ scope.row.discount }}%
              </span>
              <span class="no-discount" v-else>-</span>
            </template>
          </el-table-column>
          
          <el-table-column label="Thao tác" width="120" align="center">
            <template #default="scope">
              <div class="actions">
                <el-button 
                  type="text" 
                  @click="editCategory(scope.row)"
                  class="edit-btn"
                >
                  Sửa
                </el-button>
                <el-button 
                  type="text" 
                  @click="deleteCategoryAction(scope.row.id)"
                  class="delete-btn"
                >
                  Xóa
                </el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </el-card>
    </div>

    <!-- Edit Dialog -->
    <el-dialog 
      title="Chỉnh sửa loại vé" 
      v-model="editDialogVisible" 
      width="400px"
      class="edit-dialog"
    >
      <el-form 
        :model="editCategoryData" 
        :rules="rules" 
        ref="editCategoryForm"
        class="dialog-form"
      >
        <el-form-item label="Tên loại vé" prop="label">
          <el-input 
            v-model="editCategoryData.label"
            size="large"
            class="clean-input"
          />
        </el-form-item>
        
        <el-form-item label="Mô tả" prop="description">
          <el-input 
            v-model="editCategoryData.description"
            size="large"
            style="margin-left: 30px;"
          />
        </el-form-item>
        
        <el-form-item label="Giảm giá (%)" prop="discount">
          <el-input-number 
            v-model="editCategoryData.discount" 
            :min="0" 
            :max="100"
            size="large"
            class="clean-input"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>
      
      <template #footer>
        <div class="dialog-actions">
          <el-button @click="editDialogVisible = false">Hủy</el-button>
          <el-button type="primary" @click="saveEditedCategory">Lưu</el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { ElNotification, ElMessageBox } from "element-plus";
import { useTicketManagement } from "@/composables/useTicketManagement";

const { ticketCategories, loadTicketCategories, addTicketCategory, updateCategory, deleteCategory } = useTicketManagement();

const newCategory = ref({
  label: "",
  description: "",
  discount: null,
});

const editCategoryData = ref({});
const editDialogVisible = ref(false);

const rules = {
  label: [{ required: true, message: "Vui lòng nhập tên loại vé", trigger: "blur" }],
  description: [{ required: true, message: "Vui lòng nhập mô tả", trigger: "blur" }],
};

onMounted(() => {
  loadTicketCategories();
});

const submitCategory = async () => {
  try {
    await addTicketCategory(newCategory.value);
    newCategory.value = { label: "", description: "", discount: null };
    ElNotification({
      title: 'Thành công',
      message: 'Thêm loại vé thành công!',
      type: 'success',
    });
  } catch (error) {
    ElNotification({
      title: 'Lỗi',
      message: 'Có lỗi xảy ra',
      type: 'error',
    });
  }
};

const editCategory = (category) => {
  editCategoryData.value = { ...category };
  editDialogVisible.value = true;
};

const saveEditedCategory = async () => {
  try {
    await updateCategory(editCategoryData.value.id, {
      label: editCategoryData.value.label,
      description: editCategoryData.value.description,
      discount: editCategoryData.value.discount,
    });
    editDialogVisible.value = false;
    ElNotification({
      title: 'Thành công',
      message: 'Cập nhật thành công!',
      type: 'success',
    });
  } catch (error) {
    ElNotification({
      title: 'Lỗi',
      message: 'Có lỗi xảy ra',
      type: 'error',
    });
  }
};

const deleteCategoryAction = async (id) => {
  try {
    await ElMessageBox.confirm(
      'Bạn có chắc chắn muốn xóa?',
      'Xác nhận',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning',
      }
    );
    await deleteCategory(id);
    ElNotification({
      title: 'Thành công',
      message: 'Xóa thành công!',
      type: 'success',
    });
  } catch (error) {
    // User cancelled
  }
};
</script>

<style scoped>
.admin-ticket-management {
  max-width: 1220px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Header */
.header-section {
  margin-bottom: 40px;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 300;
  color: #2c3e50;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.page-title i {
  color: #3498db;
}

/* Form Container */
.form-container {
  margin-bottom: 20px;
}

.form-card {
  border: none;
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
  border-radius: 12px;
}

.simple-form {
  padding: 10px;
}

.form-row {
  display: grid;
  grid-template-columns: 2fr 2fr 1fr auto;
  gap: 20px;
  align-items: end;
}

.clean-input {
  width: 100%;
}

.add-btn {
  height: 48px;
  padding: 0 30px;
  border-radius: 8px;
  font-weight: 500;
  letter-spacing: 0.5px;
}

/* Table */
.table-container {
  margin-bottom: 40px;
}

.table-card {
  border: none;
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
  border-radius: 12px;
}

.clean-table {
  border-radius: 8px;
}

.category-name {
  font-weight: 600;
  color: #2c3e50;
}

.description {
  color: #7f8c8d;
}

.discount-value {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.no-discount {
  color: #bdc3c7;
}

.actions {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.edit-btn {
  color: #f39c12;
  font-weight: 500;
}

.edit-btn:hover {
  color: #e67e22;
}

.delete-btn {
  color: #e74c3c;
  font-weight: 500;
}

.delete-btn:hover {
  color: #c0392b;
}

/* Dialog */
.edit-dialog {
  border-radius: 12px;
}

.dialog-form {
  padding: 20px 0;
}

.dialog-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

/* Element Plus Overrides */
:deep(.el-card__body) {
  padding: 30px;
}

:deep(.el-input__wrapper) {
  border-radius: 8px;
  border: 2px solid #ecf0f1;
  transition: all 0.3s ease;
  box-shadow: none;
}

:deep(.el-input__wrapper:hover) {
  border-color: #bdc3c7;
}

:deep(.el-input__wrapper.is-focus) {
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

:deep(.el-input__inner) {
  font-size: 15px;
  height: 48px;
  line-height: 48px;
}

:deep(.el-input-number) {
  width: 100%;
}

:deep(.el-input-number .el-input__wrapper) {
  padding-right: 50px;
}

:deep(.el-button--primary) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  transition: all 0.3s ease;
}

:deep(.el-button--primary:hover) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

:deep(.el-table) {
  border-radius: 8px;
  overflow: hidden;
}

:deep(.el-table th.el-table__cell) {
  background: #f8f9fa;
  border-bottom: 1px solid #ecf0f1;
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
}

:deep(.el-table td.el-table__cell) {
  border-bottom: 1px solid #f8f9fa;
  padding: 20px 0;
}

:deep(.el-table__body tr:hover > td) {
  background-color: #f8f9fa;
}

:deep(.el-dialog) {
  border-radius: 12px;
}

:deep(.el-dialog__header) {
  padding: 24px 24px 0;
}

:deep(.el-dialog__title) {
  font-size: 20px;
  font-weight: 600;
  color: #2c3e50;
}

:deep(.el-dialog__body) {
  padding: 0 24px;
}

:deep(.el-dialog__footer) {
  padding: 24px;
}

:deep(.el-form-item__label) {
  font-weight: 500;
  color: #2c3e50;
  margin-bottom: 8px;
}

/* Responsive */
@media (max-width: 768px) {
  .admin-ticket-management {
    padding: 20px 10px;
  }
  
  .page-title {
    font-size: 2rem;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  :deep(.el-card__body) {
    padding: 20px;
  }
}
</style>