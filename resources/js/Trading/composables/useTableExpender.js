export function useTableExpender(lastMonthIndexInEachYear, hideTablesDates) {

	const hideOrExpandMyYear = (tableId, toDateAsIndex) => {
		const index = lastMonthIndexInEachYear.value.indexOf(toDateAsIndex)
		const fromDateAsIndex = lastMonthIndexInEachYear.value[index - 1] + 1 || 0
		console.log(typeof (toDateAsIndex))
		const isCurrentDateExistInArray = hideTablesDates.value[tableId].includes(toDateAsIndex)
		for (let i = fromDateAsIndex; i <= toDateAsIndex; i++) {
			if (isCurrentDateExistInArray) {
				console.log(isCurrentDateExistInArray)
				hideTablesDates.value[tableId] = hideTablesDates.value[tableId].filter(
					(i) => !(i >= fromDateAsIndex && i <= toDateAsIndex),
				)
			} else {
				hideTablesDates.value[tableId].push(i)
			}
		}
	}
	const hideOrExpandMyYearWithIndex = (tableId, toDateAsIndex, rowIndex) => {
		const index = lastMonthIndexInEachYear.value.indexOf(toDateAsIndex)
		const fromDateAsIndex = lastMonthIndexInEachYear.value[index - 1] + 1 || 0
		const isCurrentDateExistInArray = hideTablesDates.value[tableId][rowIndex].includes(toDateAsIndex)
		for (let i = fromDateAsIndex; i <= toDateAsIndex; i++) {
			if (isCurrentDateExistInArray) {
				hideTablesDates.value[tableId][rowIndex] = hideTablesDates.value[tableId][rowIndex].filter(
					(i) => !(i >= fromDateAsIndex && i <= toDateAsIndex),
				)
			} else {
				hideTablesDates.value[tableId][rowIndex].push(i)
			}
		}
	}



	return {
		hideOrExpandMyYear,
		hideOrExpandMyYearWithIndex
	}
}
