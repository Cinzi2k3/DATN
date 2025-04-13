import { computed } from 'vue';
export function useTotalPrice(props) {
    const totalPrice = computed(() => {
        let total = 0;
        Object.values(props.selectedSeatsByCar).forEach((seats) => {
          seats.forEach((seat) => {
            if (seat.gia) {
              total += Number(seat.gia);
            }
          });
        });
        return total;
      });
      return {
        totalPrice,
      };
}