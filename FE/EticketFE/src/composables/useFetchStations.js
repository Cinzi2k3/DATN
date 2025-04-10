import { ref } from "vue";
import axios from "axios";

export function useFetchStations() {
  const stations = ref([]); // Thêm biến stations

  const fetchStations = async () => {
    try {
      const response = await axios.get("/ga");
      if (response.data.success) {
        stations.value = response.data.data;
      }
    } catch (error) {
      console.error("Có lỗi xảy ra khi tải dữ liệu ga:", error);
    }
  };

  return { stations, fetchStations }; // Trả về stations để có thể dùng trong component
}
