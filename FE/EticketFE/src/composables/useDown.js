import { ref } from "vue";

export function useDown() {
  const isOpen = ref(false); // Khai báo biến trạng thái đóng/mở dropdown

  const toggleDropdown = () => {
    isOpen.value = !isOpen.value; // Đảo trạng thái dropdown
  };

  return { isOpen, toggleDropdown }; // Trả về biến và hàm điều khiển
}
