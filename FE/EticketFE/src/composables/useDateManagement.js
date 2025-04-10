import { ref, computed, watch } from "vue";
import { ElMessage } from "element-plus";
import { useLanguage } from "@/composables/useLanguage";

export function useDateManagement(departureDate, returnDate, searchTickets) {
  const { t, locale } = useLanguage();
  const availableDays = ref([]);
  const currentPage = ref(0);
  const selectedDay = ref(null);

  // Định dạng ngày thành YYYY-MM-DD
  const formatDateToYMD = (date) => {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  };

  // Tạo danh sách ngày có sẵn xung quanh một ngày trung tâm
  const generateAvailableDays = (centerDate) => {
    const days = [];
    const weekdays = [
      t("Chủ nhật"),
      t("Thứ hai"),
      t("Thứ ba"),
      t("Thứ tư"),
      t("Thứ năm"),
      t("Thứ sáu"),
      t("Thứ bảy"),
    ];
    const start = new Date(centerDate);
    const end = new Date(centerDate);

    // Lùi lại 15 ngày
    start.setDate(start.getDate() - 15);
    // Tiến lên 15 ngày
    end.setDate(end.getDate() + 15);

    while (start <= end) {
      const formattedDate = formatDateToYMD(start);
      days.push({
        date: formattedDate,
        weekday: weekdays[start.getDay()],
      });
      start.setDate(start.getDate() + 1);
    }

    return days;
  };

  // Cập nhật danh sách ngày có sẵn khi ngôn ngữ thay đổi
  watch(locale, () => {
    if (departureDate.value) {
      availableDays.value = generateAvailableDays(departureDate.value);
    }
  });

  // Kiểm tra tính hợp lệ của ngày về
  const isValidReturnDate = (depDate, retDate) => {
    return new Date(retDate) > new Date(depDate);
  };

  // Xử lý khi ngày đi thay đổi
  const onDepartureDateChange = (date) => {
    departureDate.value = date;
    
    // Kiểm tra nếu ngày về không hợp lệ
    if (returnDate.value && !isValidReturnDate(date, returnDate.value)) {
      ElMessage.error("Ngày đi không được lớn hơn hoặc bằng ngày về.");
      returnDate.value = null; // Đặt lại ngày về
    }

    // Cập nhật danh sách ngày có sẵn
    availableDays.value = generateAvailableDays(departureDate.value);

    // Đặt currentPage sao cho ngày được chọn nằm ở giữa danh sách hiển thị
    const selectedIndex = availableDays.value.findIndex((day) => day.date === formatDateToYMD(date));
    currentPage.value = Math.max(0, selectedIndex - 3);
  };

  // Xử lý khi ngày về thay đổi
  const onReturnDateChange = (date) => {
    if (departureDate.value && !isValidReturnDate(departureDate.value, date)) {
      ElMessage.error("Ngày về phải lớn hơn ngày đi.");
      returnDate.value = null; // Đặt lại ngày về
    } else {
      returnDate.value = date; // Cập nhật ngày về nếu hợp lệ
    }
  };

  // Xử lý khi chọn một ngày trong lịch
  const onDaySelected = async (newSelectedDay) => {
    departureDate.value = new Date(newSelectedDay);
    selectedDay.value = newSelectedDay;
    await searchTickets();
  };

  const returnSelected = async (newSelectedDay, searchReturnTicketsByDate) => {
    returnDate.value = new Date(newSelectedDay);
    selectedDay.value = newSelectedDay;
    await searchReturnTicketsByDate(newSelectedDay); // Tìm kiếm lại lịch trình cho ngày về
  };

  // Chuyển sang trang tiếp theo của danh sách ngày
  const nextday = () => {
    if (currentPage.value + 7 < availableDays.value.length) {
      currentPage.value++;
    }
  };

  // Quay lại trang trước của danh sách ngày
  const prevday = () => {
    if (currentPage.value > 0) {
      currentPage.value--;
    }
  };

  // Tính toán danh sách ngày hiển thị
  const displayedDays = computed(() => {
    // Lấy 7 ngày từ vị trí currentPage
    return availableDays.value.slice(currentPage.value, currentPage.value + 8);
  });

  return {
    availableDays,
    currentPage,
    selectedDay,
    returnSelected,
    formatDateToYMD,
    generateAvailableDays,
    isValidReturnDate,
    onDepartureDateChange,
    onReturnDateChange,
    onDaySelected,
    nextday,
    prevday,
    displayedDays,

  };
}
