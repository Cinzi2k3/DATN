
import { ElMessage } from "element-plus";
import axios from "axios";

export function useSearchTickets(
  loading,
  gadi,
  gaden,
  departureDate,
  returnDate,
  selectedTicket,
  searchgadi,
  searchgaden,
  generateAvailableDays,
  availableDays,
  selectedDay,
  searchPerformed,
  trainSchedules,
  formatDateToYMD,
  updateTicketDataInStore
) {
  
  // Hàm chính để tìm kiếm vé
  const searchTickets = async () => {
    try {
      loading.value = true;
      await new Promise((resolve) => setTimeout(resolve, 500)); // Giả lập thời gian tải

      if (gadi.value && gaden.value && departureDate.value) {
        updateTicketDataInStore();
        // Cập nhật giá trị của ga đi và ga đến
        searchgadi.value = gadi.value;
        searchgaden.value = gaden.value;
        searchPerformed.value = true;

        // Kiểm tra loại vé
        if (selectedTicket.value === "one-way" && departureDate.value) {
          // Tạo danh sách các ngày có vé cho vé một chiều
          availableDays.value = generateAvailableDays(departureDate.value);
        } else if (
          selectedTicket.value === "round-trip" &&
          departureDate.value &&
          returnDate.value
        ) {
          // Tạo danh sách các ngày có vé cho vé khứ hồi
          availableDays.value = generateAvailableDays(departureDate.value);
        } else if (!returnDate.value && selectedTicket.value === "round-trip") {
          // Thông báo nếu chưa chọn ngày về khi đặt vé khứ hồi
          searchPerformed.value = false;
          ElMessage.error("Vui lòng chọn ngày về.");
          loading.value = false;
          return;
        }

        // Chọn một ngày mặc định nếu có danh sách ngày khả dụng
        if (availableDays.value.length > 0) {
          selectedDay.value = availableDays.value[15].date;
        }
        
        // Gửi yêu cầu API để lấy lịch trình tàu
        const response = await axios.get("/lichtrinh", {
          params: {
            gadi: gadi.value,
            gaden: gaden.value,
            ngaydi: formatDateToYMD(departureDate.value),
          },
        });
        
        if (response.data.success && response.data.data.length > 0) {
          trainSchedules.value = response.data.data; // Lưu danh sách lịch trình vào biến trainSchedules
        } else {
          ElMessage.error("Không tìm thấy chuyến tàu.");
          trainSchedules.value = [];
        }
      } else {
        // Kiểm tra nếu thiếu thông tin nhập vào
        if (!gadi.value) {
          ElMessage.error("Vui lòng chọn ga đi.");
        } else if (!gaden.value) {
          ElMessage.error("Vui lòng chọn ga đến.");
        } else if (!departureDate.value) {
          ElMessage.error("Vui lòng chọn ngày đi.");
        }
      }
    } catch (error) {
      console.error("Lỗi khi tìm kiếm vé:", error);
      ElMessage.error("Đã xảy ra lỗi khi tìm kiếm vé.");
    } finally {
      loading.value = false;
    }
  };

  return {
    searchTickets
  };
}
