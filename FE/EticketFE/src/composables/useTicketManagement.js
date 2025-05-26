import { ref, computed } from "vue";
import { ElNotification } from "element-plus";
import { useSearchStore } from "../stores/searchStore";
import axios from "axios";

export function useTicketManagement() {
  const searchStore = useSearchStore();
  const isOpen = ref(false);
  const loading = ref(false);
  const gadi = ref(searchStore.gadi);
  const gaden = ref(searchStore.gaden);
  const departureDate = ref(searchStore.departureDate);
  const returnDate = ref(searchStore.returnDate);
  const totalTickets = ref(searchStore.totalTickets);
  const tempTotalTickets = ref(searchStore.totalTickets);
  const searchgadi = ref(null);
  const searchgaden = ref(null);
  const selectedDay = ref(null);
  const trainSchedules = ref([]);
  const searchPerformed = ref(false);
  const selectedTicket = ref(searchStore.selectedTicket);
  const ticketCategories = ref([]);

  const ticketDetails = computed(() => {
    const details = searchStore.searchData.ticketDetails;
    return Array.isArray(details) ? details : [];
  });

  let isCategoriesLoaded = false;

  const loadTicketCategories = async () => {
    if (isCategoriesLoaded) {
      restoreCountsFromStore();
      return;
    }
    try {
      loading.value = true;
      const response = await axios.get(`/ticket-categories`);
      ticketCategories.value = response.data.map(category => {
        if (!category.id || !category.label) {
          console.warn("Dữ liệu loại vé không hợp lệ:", category);
        }
        const storedDetail = ticketDetails.value.find(
          detail => detail && detail.type === category.label
        );
        return {
          ...category,
          count: storedDetail ? storedDetail.count : (category.label === "Người lớn" ? 1 : 0),
        };
      });
      updateTicketDataInStore();
      isCategoriesLoaded = true;
    } catch (error) {
      console.error("Lỗi khi tải loại vé:", error.message);
      ElNotification.error(`Không thể tải danh sách loại vé: ${error.message}`);
    } finally {
      loading.value = false;
    }
  };

  const restoreCountsFromStore = () => {
    const details = ticketDetails.value;
    ticketCategories.value.forEach(category => {
      const matchingDetail = details.find(
        detail => detail && detail.type === category.label
      );
      category.count = matchingDetail ? matchingDetail.count : (category.label === "Người lớn" ? 1 : 0);
    });
    tempTotalTickets.value = totalTickets.value = ticketCategories.value.reduce(
      (sum, category) => sum + (category.count || 0),
      0
    );
    console.log("Khôi phục counts:", ticketCategories.value);
    updateTicketDataInStore();
  };

  const syncTicketCounts = () => {
    console.log("syncTicketCounts triggered, ticketDetails:", ticketDetails.value);
    const details = Array.isArray(ticketDetails.value) ? ticketDetails.value : [];
    ticketCategories.value.forEach((category) => {
      const matchingDetail = details.find(
        (detail) => detail && detail.type === category.label
      );
      if (matchingDetail) {
        category.count = matchingDetail.count;
      }
    });
    tempTotalTickets.value = totalTickets.value = ticketCategories.value.reduce(
      (sum, category) => sum + (category.count || 0),
      0
    );
    console.log("Total tickets:", totalTickets.value);
    updateTicketDataInStore();
  };

  const updateTicketDataInStore = () => {
    // Kiểm tra logic Trẻ em phải đi cùng người lớn
    const childCategory = ticketCategories.value.find(cat => cat.label === "Trẻ en");
    const adultCategory = ticketCategories.value.find(cat => cat.label === "Người lớn");
    if (childCategory && childCategory.count > 0 && (!adultCategory || adultCategory.count === 0)) {
      ElNotification.error("Trẻ em phải đi cùng với người lớn");
      childCategory.count = 0;
      tempTotalTickets.value = totalTickets.value = ticketCategories.value.reduce(
        (sum, category) => sum + (category.count || 0),
        0
      );
    }

    const updatedDetails = ticketCategories.value.map((category) => ({
      type: category.label,
      count: category.count,
      discount: category.discount || 0, // Thêm discount
    }));
    totalTickets.value = tempTotalTickets.value;
    searchStore.setSearchData({
      ...searchStore.searchData,
      gadi: gadi.value,
      gaden: gaden.value,
      departureDate: departureDate.value,
      returnDate: returnDate.value,
      selectedTicket: selectedTicket.value,
      ticketDetails: updatedDetails,
      totalTickets: totalTickets.value,
    });
    console.log("Dữ liệu store đã cập nhật:", searchStore.searchData);
  };

const increment = async (label) => {
  console.log("Label được truyền:", label); // Debug label
  if (!isCategoriesLoaded) {
    await loadTicketCategories();
  }
  if (ticketCategories.value.length === 0) {
    console.error("Danh sách loại vé rỗng");
    ElNotification.error("Chưa tải được danh sách loại vé");
    return;
  }
  const category = ticketCategories.value.find((item) => item.label === label);
  if (!category) {
    console.error(`Không tìm thấy danh mục với label: ${label}`);
    ElNotification.error(`Loại vé "${label}" không tồn tại`);
    return;
  }
  if (category.label === "Trẻ em") {
    const adultCategory = ticketCategories.value.find(
      (item) => item.label === "Người lớn"
    );
    if (adultCategory && adultCategory.count === 0) {
      ElNotification.error("Trẻ em phải đi cùng với người lớn");
      return;
    }
  }
  category.count++;
  calculateTempTotal();
  updateTicketDataInStore();
};

  const decrement = (label) => {
    const category = ticketCategories.value.find((item) => item.label === label);
    if (category.label === "Người lớn") {
      const childCategory = ticketCategories.value.find(
        (item) => item.label === "Trẻ em"
      );
      if (childCategory && category.count === 1 && childCategory.count > 0) {
        ElNotification.error("Trẻ em phải đi cùng với người lớn");
        return;
      }
    }
    if (category && category.count > 0) {
      category.count--;
      calculateTempTotal();
      updateTicketDataInStore();
    }
  };

  const calculateTempTotal = () => {
    tempTotalTickets.value = ticketCategories.value.reduce(
      (sum, category) => sum + (category.count || 0),
      0
    );
  };

  const addTicketCategory = async (newCategory) => {
    try {
      loading.value = true;
      const response = await axios.post(`/ticket-categories`, newCategory);
      ticketCategories.value.push({ ...response.data, count: 0 });
      updateTicketDataInStore();
    } catch (error) {
      console.error("Lỗi khi thêm loại vé:", error.message);
    } finally {
      loading.value = false;
    }
  };

  const updateCategory = async (id, updatedCategory) => {
    try {
      loading.value = true;
      await axios.put(`/ticket-categories/${id}`, updatedCategory);
      const index = ticketCategories.value.findIndex((cat) => cat.id === id);
      if (index !== -1) {
        ticketCategories.value[index] = { ...ticketCategories.value[index], ...updatedCategory, count: ticketCategories.value[index].count };
        updateTicketDataInStore();
      }
    } catch (error) {
      console.error("Lỗi khi cập nhật loại vé:", error.message);
    } finally {
      loading.value = false;
    }
  };

  const deleteCategory = async (id) => {
    try {
      loading.value = true;
      await axios.delete(`/ticket-categories/${id}`);
      ticketCategories.value = ticketCategories.value.filter((cat) => cat.id !== id);
      updateTicketDataInStore();
    } catch (error) {
      console.error("Lỗi khi xóa loại vé:", error.message);
    } finally {
      loading.value = false;
    }
  };

  return {
    isOpen,
    loading,
    gadi,
    gaden,
    departureDate,
    returnDate,
    totalTickets,
    tempTotalTickets,
    searchgadi,
    searchgaden,
    selectedDay,
    trainSchedules,
    searchPerformed,
    selectedTicket,
    ticketCategories,
    ticketDetails,
    syncTicketCounts,
    updateTicketDataInStore,
    increment,
    decrement,
    calculateTempTotal,
    loadTicketCategories,
    addTicketCategory,
    updateCategory,
    deleteCategory,
  };
}