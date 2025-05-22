<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <div class="sidebar" :class="{ 'collapsed': isCollapsed }">
      <div class="brand">
        <img src="@/assets/img/logo.png" alt="Logo" class="brand-logo" />
      </div>
      
      <el-menu
        :default-active="$route.path"
        router
        :collapse="isCollapsed"
        class="sidebar-menu"
        background-color="#001529"
        text-color="#ffffff"
        active-text-color="#409EFF">
        <el-menu-item index="/admin/dashboard">
          <el-icon><i class="fas fa-tachometer-alt"></i></el-icon>
          <template #title>Dashboard</template>
        </el-menu-item>
        <el-menu-item index="/admin/orders">
          <el-icon><i class="fas fa-shopping-cart"></i></el-icon>
          <template #title>Đơn hàng</template>
        </el-menu-item>
        <el-menu-item index="/admin/status">
          <el-icon><i class="fas fa-chair"></i></el-icon>
          <template #title>Giá chỗ</template>
        </el-menu-item>
        <el-menu-item index="/admin/support">
          <el-icon><i class="fas fa-headset"></i></el-icon>
          <template #title>Hỗ trợ khách hàng</template>
        </el-menu-item>
        <el-menu-item index="/admin/users">
          <el-icon><i class="fas fa-users"></i></el-icon>
          <template #title>Người dùng</template>
        </el-menu-item>
        <el-menu-item index="/admin/reports">
          <el-icon><i class="fas fa-chart-bar"></i></el-icon>
          <template #title>Thống kê</template>
        </el-menu-item>
        <el-menu-item index="/admin/settings">
          <el-icon><i class="fas fa-cogs"></i></el-icon>
          <template #title>Cấu hình</template>
        </el-menu-item>
      </el-menu>
    </div>

    <!-- Main Content -->
    <div class="main-content" :class="{ 'expanded': isCollapsed }">
      <!-- Header -->
      <header class="header">
        <div class="header-left">
          <el-button 
            type="text" 
            @click="toggleSidebar" 
            class="toggle-btn">
            <i class="fas fa-bars"></i>
          </el-button>
          <div class="breadcrumb mb-0" >
            <el-breadcrumb separator="/">
              <el-breadcrumb-item :to="{ path: '/admin' }">Admin</el-breadcrumb-item>
              <el-breadcrumb-item>{{ currentPage }}</el-breadcrumb-item>
            </el-breadcrumb>
          </div>
        </div>
        
        <div class="header-right">
          <el-dropdown trigger="click" class="user-dropdown">
            <div class="user-info">
              <i class="fa-solid fa-user-tie"></i>
              <span class="user-name">Admin User</span>
              <i class="el-icon-arrow-down"></i>
            </div>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item>
                  <i class="fas fa-user-circle me-2"></i> Thông tin cá nhân
                </el-dropdown-item>
                <el-dropdown-item>
                  <i class="fas fa-cog me-2"></i> Cài đặt
                </el-dropdown-item>
                <el-dropdown-item divided>
                  <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
          
          <el-tooltip content="Thông báo" placement="bottom">
            <el-badge :value="3" class="notification-badge">
              <el-button type="text" class="icon-button">
                <i class="fas fa-bell"></i>
              </el-button>
            </el-badge>
          </el-tooltip>
        </div>
      </header>

      <!-- Page Content -->
      <div class="page-content">
        <div class="container-fluid">
          <RouterView />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'

const isCollapsed = ref(false)
const route = useRoute()

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}

const currentPage = computed(() => {
  const path = route.path
  if (path.includes('dashboard')) return 'Dashboard'
  if (path.includes('orders')) return 'Đơn hàng'
  if (path.includes('status')) return 'Trạng thái ghế'
  if (path.includes('support')) return 'Hỗ trợ khách hàng'
  if (path.includes('users')) return 'Người dùng'
  if (path.includes('reports')) return 'Báo cáo'
  if (path.includes('settings')) return 'Cấu hình'
  return 'Dashboard'
})
</script>

<style scoped>
/* Layout */
.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f0f2f5;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background-color: #001529;
  transition: all 0.3s;
  box-shadow: 2px 0 6px rgba(0, 21, 41, 0.35);
  z-index: 1000;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
}

.sidebar.collapsed {
  width: 80px;
}

.brand {
  height: 100px;
  display: flex;
  align-items: center;
  padding: 0 16px;
  color: white;
  overflow: hidden;
  background-color: #002140;
}

.brand-logo {
  width: 200px;
  height: 80px;
  margin-right: 12px;
}

.brand h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  white-space: nowrap;
}

.sidebar-menu {
  border-right: none !important;
}

/* Main Content */
.main-content {
  flex: 1;
  margin-left: 260px;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
}

.main-content.expanded {
  margin-left: 80px;
}

/* Header */
.header {
  height: 64px;
  background-color: white;
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  position: relative;
}

.header-left {
  display: flex;
  align-items: center;
}

.toggle-btn {
  font-size: 18px;
  padding: 0;
  margin-right: 16px;
}

.header-right {
  display: flex;
  align-items: center;
}

.user-dropdown {
  margin-left: 16px;
}

.user-info {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-right: 8px;
}

.user-name {
  font-size: 14px;
  margin-right: 4px;
}

.notification-badge {
  margin-left: 16px;
}

.icon-button {
  font-size: 18px;
}

/* Page Content */
.page-content {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
}

/* Responsive */
@media (max-width: 992px) {
  .sidebar {
    transform: translateX(-100%);
  }
  
  .sidebar.collapsed {
    transform: translateX(0);
    width: 260px;
  }
  
  .main-content {
    margin-left: 0;
  }
  
  .main-content.expanded {
    margin-left: 0;
  }
}
</style>