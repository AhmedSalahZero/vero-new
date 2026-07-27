import { computed } from 'vue';


export function useDateFormatter(dates) {
  const yearsFromDates = computed(() => {
    let result = {};
    if (!dates.value) return result;
    Object.keys(dates.value).forEach((dateAsIndex) => {
      result[dateAsIndex] = dates.value[dateAsIndex].split("'").pop();
    });
    return result;
  });


  return {
    yearsFromDates
  };
}
