import { ElMessage } from "element-plus";
import axios from "axios";
import { ref } from "vue";

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
  // Trạng thái để theo dõi bước chọn vé cho vé khứ hồi
  const bookingStep = ref("departure"); // 'departure' hoặc 'return'
  const departureTrainSelected = ref(null); // Lưu thông tin tàu đã chọn cho ngày đi

  // Hàm lấy lịch trình tàu từ API
  const fetchTrainSchedules = async (fromStation, toStation, date) => {
    try {
      const response = await axios.get("/lichtrinh", {
        params: {
          gadi: fromStation,
          gaden: toStation,
          ngaydi: formatDateToYMD(date),
        },
      });
      return response.data;
    } catch (error) {
      console.error("Lỗi khi lấy lịch trình tàu:", error);
      throw error;
    }
  };

  // Hàm chính để tìm kiếm vé
  const searchTickets = async () => {
    try {
      loading.value = true;
      await new Promise((resolve) => setTimeout(resolve, 500)); // Giả lập thời gian tải

      if (!gadi.value || !gaden.value || !departureDate.value) {
        if (!gadi.value) ElMessage.error("Vui lòng chọn ga đi.");
        else if (!gaden.value) ElMessage.error("Vui lòng chọn ga đến.");
        else if (!departureDate.value) ElMessage.error("Vui lòng chọn ngày đi.");
        loading.value = false;
        return;
      }

      if (selectedTicket.value === "round-trip" && bookingStep.value === "return") {
        if (!returnDate.value) {
          ElMessage.error("Vui lòng chọn ngày về.");
          loading.value = false;
          return;
        }

        searchgadi.value = gaden.value; // Đảo chiều ga đi
        searchgaden.value = gadi.value; // Đảo chiều ga đến
        selectedDay.value = formatDateToYMD(returnDate.value); // Đồng bộ selectedDay với returnDate
        availableDays.value = generateAvailableDays(returnDate.value);

        const data = await fetchTrainSchedules(gaden.value, gadi.value, returnDate.value);
        if (data.success && data.data.length > 0) {
          trainSchedules.value = data.data;
        } else {
          ElMessage.error("Không tìm thấy chuyến tàu cho ngày về.");
          trainSchedules.value = [];
        }
        loading.value = false;
        return; // Kết thúc ở đây để không chạy logic ngày đi
      }

      updateTicketDataInStore();
      searchgadi.value = gadi.value;
      searchgaden.value = gaden.value;
      searchPerformed.value = true;

      // Xử lý theo loại vé
      if (selectedTicket.value === "one-way") {
        // Vé một chiều: Tìm kiếm lịch trình ngày đi
        availableDays.value = generateAvailableDays(departureDate.value);
        if (availableDays.value.length > 0) {
          selectedDay.value = availableDays.value[15].date;
        }

        const data = await fetchTrainSchedules(gadi.value, gaden.value, departureDate.value);
        if (data.success && data.data.length > 0) {
          trainSchedules.value = data.data;
        } else {
          ElMessage.error("Không tìm thấy chuyến tàu.");
          trainSchedules.value = [];
        }
      } else if (selectedTicket.value === "round-trip") {
        if (!returnDate.value) {
          searchPerformed.value = false;
          ElMessage.error("Vui lòng chọn ngày về.");
          loading.value = false;
          return;
        }

        // Vé khứ hồi: Bắt đầu với ngày đi
        if (bookingStep.value === "departure") {
          availableDays.value = generateAvailableDays(departureDate.value);
          if (availableDays.value.length > 0) {
            selectedDay.value = availableDays.value[15].date;
          }

          const data = await fetchTrainSchedules(gadi.value, gaden.value, departureDate.value);
          if (data.success && data.data.length > 0) {
            trainSchedules.value = data.data;
          } else {
            ElMessage.error("Không tìm thấy chuyến tàu cho ngày đi.");
            trainSchedules.value = [];
          }
        }
      }
    } catch (error) {
      ElMessage.error("Đã xảy ra lỗi khi tìm kiếm vé.");
      trainSchedules.value = [];
    } finally {
      loading.value = false;
    }
  };

  // Hàm chọn tàu cho ngày đi và chuyển sang ngày về
  const selectDepartureTrain = async (train) => {
    departureTrainSelected.value = train;
    bookingStep.value = "return"; // Chuyển sang bước chọn ngày về

    try {
      loading.value = true;
      searchgadi.value = gaden.value; // Đảo chiều ga đi
      searchgaden.value = gadi.value; // Đảo chiều ga đến

      availableDays.value = generateAvailableDays(returnDate.value);
      if (availableDays.value.length > 0) {
        selectedDay.value = availableDays.value[15].date;
      }

      const data = await fetchTrainSchedules(gaden.value, gadi.value, returnDate.value);
      if (data.success && data.data.length > 0) {
        trainSchedules.value = data.data;
      } else {
        ElMessage.error("Không tìm thấy chuyến tàu cho ngày về.");
        trainSchedules.value = [];
      }
    } catch (error) {
      ElMessage.error("Đã xảy ra lỗi khi tìm kiếm vé ngày về.");
      trainSchedules.value = [];
    } finally {
      loading.value = false;
    }
  };

  // Hàm chọn tàu cho ngày về
  const selectReturnTrain = (train) => {
    // Xử lý khi chọn xong cả hai chuyến tàu
    const bookingData = {
      departure: departureTrainSelected.value,
      return: train,
    };
    console.log("Thông tin vé đã chọn:", bookingData);
    // Có thể gọi hàm lưu vào store hoặc chuyển sang bước tiếp theo
    updateTicketDataInStore(bookingData); // Ví dụ: lưu vào store
    return bookingData
  };

  const searchReturnTicketsByDate = async (newDate) => {
    try {
      loading.value = true;
      selectedDay.value = newDate;
      returnDate.value = new Date(newDate); // Cập nhật ngày về
  
      const data = await fetchTrainSchedules(gaden.value, gadi.value, newDate);
      if (data.success && data.data.length > 0) {
        trainSchedules.value = data.data;
      } else {
        ElMessage.error("Không tìm thấy chuyến tàu cho ngày đã chọn.");
        trainSchedules.value = [];
      }
    } catch (error) {
      ElMessage.error("Đã xảy ra lỗi khi tìm kiếm vé.");
      trainSchedules.value = [];
    } finally {
      loading.value = false;
    }
  };

  return {
    searchTickets,
    bookingStep, // Trả về trạng thái để template sử dụng
    departureTrainSelected, // Trả về thông tin tàu ngày đi đã chọn
    selectDepartureTrain, // Hàm chọn tàu ngày đi
    selectReturnTrain, // Hàm chọn tàu ngày về
    searchReturnTicketsByDate
  };
}