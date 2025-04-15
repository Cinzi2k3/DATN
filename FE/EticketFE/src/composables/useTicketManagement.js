import { ref, computed, watch } from "vue";
import { ElNotification } from "element-plus";
import { useSearchStore } from "../stores/searchStore";

export function useTicketManagement() {
  const searchStore = useSearchStore();
  const isOpen = ref(false);
  const loading = ref(false);
  const gadi = ref(searchStore.gadi);
  const gaden = ref(searchStore.gaden);
  const departureDate = ref(searchStore.departureDate);
  const returnDate = ref(searchStore.returnDate);
  const totalTickets = ref(searchStore.totalTickets); // Giá trị chính thức, chỉ cập nhật khi tìm kiếm
  const tempTotalTickets = ref(searchStore.totalTickets); // Giá trị tạm thời để hiển thị khi tăng/giảm
  const searchgadi = ref(null);
  const searchgaden = ref(null);
  const selectedDay = ref(null);
  const trainSchedules = ref([]);
  const searchPerformed = ref(false);
  const selectedTicket = ref(searchStore.selectedTicket);

  const ticketCategories = ref([
    {
      id: "adult",
      label: "Người lớn",
      description: "Từ 11 tuổi trở lên",
      count: 1,
      discount: null,
    },
    {
      id: "child",
      label: "Trẻ em",
      description: "6 - 10 tuổi",
      count: 0,
      discount: 25,
    },
  ]);

  const ticketDetails = computed(() => searchStore.searchData.ticketDetails);

  const syncTicketCounts = () => {
    ticketCategories.value.forEach((category) => {
      const matchingDetail = ticketDetails.value.find(
        (detail) => detail.type === category.label
      );
      if (matchingDetail) {
        category.count = matchingDetail.count;
      }
    });
    tempTotalTickets.value = totalTickets.value = ticketCategories.value.reduce(
      (sum, category) => sum + category.count,
      0
    ); // Đồng bộ cả hai khi khởi tạo
  };

  const updateTicketDataInStore = () => {
    const updatedDetails = ticketCategories.value.map((category) => ({
      type: category.label,
      count: category.count,
    }));
    totalTickets.value = tempTotalTickets.value; // Cập nhật totalTickets chính thức khi tìm kiếm
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
    console.log("Updated store data:", searchStore.searchData);
  };

  const increment = (id) => {
    const category = ticketCategories.value.find((item) => item.id === id);
    if (id === "child") {
      const adultCategory = ticketCategories.value.find(
        (item) => item.id === "adult"
      );
      if (adultCategory.count === 0) {
        ElNotification.error("Trẻ em phải đi cùng với người lớn");
        return;
      }
    }
    if (category) {
      category.count++;
      calculateTempTotal(); // Chỉ cập nhật tempTotalTickets
    }
  };

  const decrement = (id) => {
    const category = ticketCategories.value.find((item) => item.id === id);
    if (id === "adult") {
      const childCategory = ticketCategories.value.find(
        (item) => item.id === "child"
      );
      if (category.count === 1 && childCategory.count > 0) {
        ElNotification.error("Trẻ em phải đi cùng với người lớn");
        return;
      }
    }
    if (category && category.count > 0) {
      category.count--;
      calculateTempTotal(); // Chỉ cập nhật tempTotalTickets
    }
  };

  const calculateTempTotal = () => {
    tempTotalTickets.value = ticketCategories.value.reduce(
      (sum, category) => sum + category.count,
      0
    );
  };

  return {
    isOpen,
    loading,
    gadi,
    gaden,
    departureDate,
    returnDate,
    totalTickets, // Giá trị chính thức truyền vào TrainInfoCard
    tempTotalTickets, // Giá trị tạm thời hiển thị trong dropdown
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
  };
}