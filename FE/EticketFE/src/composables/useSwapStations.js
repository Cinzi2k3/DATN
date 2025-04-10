
export function useSwapStations (gadi, gaden) {
    const swapStations = () => {
        const temp = gadi.value;
        gadi.value = gaden.value;
        gaden.value = temp;
      };

      return {swapStations}
}