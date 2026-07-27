import { computed } from 'vue'
export function useNewLoanFundingCalculator(model,dates,totalLoanAmounts,lastMonthIndexInEachYear) {
	

	const fundingStructureCal = computed(() => {
  if (!model.value?.equity_funding_rates || !Object.keys(dates.value).length) {
    return {
      equityFundingValues: [],
      equityTotals: { per_year: {}, total: 0 },
      newLoansFundingRates: [],
      newLoansFundingValues: [],
      newLoansTotals: { per_year: {}, total: 0 },
    }
  }

  const results = {
    equityFundingValues: [],
    equityTotals: { per_year: {}, total: 0 },
    newLoansFundingRates: [],
    newLoansFundingValues: [],
    newLoansTotals: { per_year: {}, total: 0 },
  }

  // 1️⃣ حساب القيم لكل تاريخ
  Object.keys(dates.value).forEach((dateAsIndex) => {
    const equityRate = model.value.equity_funding_rates[dateAsIndex] || 0

    const totalColumn = totalLoanAmounts.value[dateAsIndex] || 0
	
    // Equity Funding
    results.equityFundingValues[dateAsIndex] = totalColumn * (equityRate / 100)

    // New Loans Funding Rate
    results.newLoansFundingRates[dateAsIndex] = 100 - equityRate
    // New Loans Funding Value
    results.newLoansFundingValues[dateAsIndex] =
      totalColumn * (results.newLoansFundingRates[dateAsIndex] / 100)
  })

  // 2️⃣ حساب Totals per Year + Grand Total
  if (lastMonthIndexInEachYear.value.length) {
    let startIndex = 0

    for (const endDateOfYearIndex of lastMonthIndexInEachYear.value) {
      let equityYearSum = 0
      let newLoansYearSum = 0
      // حساب مجموع السنة
      for (let j = startIndex; j <= endDateOfYearIndex; j++) {
        equityYearSum += results.equityFundingValues[j] || 0
        newLoansYearSum += results.newLoansFundingValues[j] || 0
      }

      // حفظ مجموع السنة
      results.equityTotals.per_year[endDateOfYearIndex] = equityYearSum
      results.newLoansTotals.per_year[endDateOfYearIndex] = newLoansYearSum

      // إضافة للمجموع الكلي
      results.equityTotals.total += equityYearSum
      results.newLoansTotals.total += newLoansYearSum
      startIndex = endDateOfYearIndex + 1
    }
  }
  model.value.equity_funding_values = results.equityFundingValues
  model.value.new_loans_funding_rates = results.newLoansFundingRates
  model.value.new_loans_funding_values = results.newLoansFundingValues
  return results
})

// ✅ استخراج القيم
const equityFundingValues = computed(() => fundingStructureCal.value.equityFundingValues)
const equityTotals = computed(() => fundingStructureCal.value.equityTotals)
const newLoansFundingRates = computed(() => fundingStructureCal.value.newLoansFundingRates)
const newLoansFundingValues = computed(() => fundingStructureCal.value.newLoansFundingValues)
const newLoansTotals = computed(() => fundingStructureCal.value.newLoansTotals)
	
	return {
		equityFundingValues,
		equityTotals,
		newLoansFundingRates,
		newLoansFundingValues,
		newLoansTotals
	}
}
