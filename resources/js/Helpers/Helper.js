export default {
	// monthly => Monthly
	capitalize(string) {
		return string.charAt(0).toUpperCase() + string.slice(1)
	},
	getFixedAssetTypes(hasMicrofinance) {

		let types = [
			{
				id: 'ffe',
				title: 'General Fixed Assets'
			},
			{
				id: 'per-employee',
				title: 'Per Employee'
			},

		]
		if (hasMicrofinance) {
			types.push({
				id: 'microfinance',
				title: 'Microfinance'
			})
		}
		console.log(hasMicrofinance)
		return types
	},
	getReplacementIntervals() {
		return [
			{
				id: 1,
				title: '1 Year'
			},
			{
				id: 2,
				title: '2 Years'
			},
			{
				id: 3,
				title: '3 Years'
			},
			{
				id: 4,
				title: '4 Years'
			},
			{
				id: 5,
				title: '5 Years'
			}
		]
	},
	getDepreciationDurations() {
		return [{
			id: 2,
			title: '2 Years'
		}, {
			id: 3,
			title: '3 Years'
		}, {
			id: 4,
			title: '4 Years'
		}, {
			id: 5,
			title: '5 Years'
		},
		{
			id: 6,
			title: '6 Years'
		},
		{
			id: 7,
			title: '7 Years'
		},
		{
			id: 8,
			title: '8 Years'
		},
		{
			id: 9,
			title: '9 Years'
		},
		{
			id: 10,
			title: '10 Years'
		},
		{
			id: 11,
			title: '11 Years'
		},
		{
			id: 12,
			title: '12 Years'
		},
		{
			id: 13,
			title: '13 Years'
		},
		{
			id: 14,
			title: '14 Years'
		},
		{
			id: 15,
			title: '15 Years'
		},
		{
			id: 16,
			title: '16 Years'
		},
		{
			id: 17,
			title: '17 Years'
		},
		{
			id: 18,
			title: '18 Years'
		},
		{
			id: 19,
			title: '19 Years'
		},
		{
			id: 20,
			title: '20 Years'
		},
		{
			id: 21,
			title: '21 Years'
		},
		{
			id: 22,
			title: '22 Years'
		},
		{
			id: 23,
			title: '23 Years'
		},
		{
			id: 24,
			title: '24 Years'
		},
		{
			id: 25,
			title: '25 Years'
		},

		]
	},
	number_format(number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
		var n = !isFinite(+number) ? 0 : +number
			, prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
			, sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep
			, dec = (typeof dec_point === 'undefined') ? '.' : dec_point
			, s = ''
			, toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec)
				return '' + Math.round(n * k) / k
			}
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.')
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || ''
			s[1] += new Array(prec - s[1].length + 1).join('0')
		}
		return s.join(dec)
	}
	,

	number_unformat(formattedNumber) {
		if (formattedNumber) {
			return parseFloat((formattedNumber + '').replace(/(<([^>]+)>)/gi, "").replace(/,/g, ""))
		}
		return 0
	},
	getPaymentTerms() {
		return [
			{
				id: "customize",
				title: "Customize",
			},
			{
				id: "cash",
				title: "Cash",
			},
			{
				id: "quarterly",
				title: "Quarterly",
			},
			{
				id: "semi-annually",
				title: "Semi Annually",
			},
			{
				id: "annually",
				title: "Annually",
			},
		]
	},

	getFixedAssetPaymentTerms() {
		return [
			{
				id: "customize",
				title: "Customize",
			},
			{
				id: "cash",
				title: "Cash",
			},

		]
	},
	getCollectionDays() {
		return [
			{
				id: 0,
				title: 0,
			},
			{
				id: 15,
				title: "15 Days",
			},
			{
				id: 30,
				title: "30 Days",
			},
			{
				id: 60,
				title: "60 Days",
			},
			{
				id: 90,
				title: "90 Days",
			},
			{
				id: 120,
				title: "120 Days",
			},
			{
				id: 150,
				title: "150 Days",
			},
			{
				id: 180,
				title: "180 Days",
			},
			{
				id: 210,
				title: "210 Days",
			},
			{
				id: 240,
				title: "240 Days",
			},
			{
				id: 270,
				title: "270 Days",
			},
			{
				id: 300,
				title: "300 Days",
			},
			{
				id: 330,
				title: "330 Days",
			},
			{
				id: 360,
				title: "360 Days",
			},
		]
	},
	getInstallmentInterest() {
		return [
			{
				id: 'monthly',
				title: 'Monthly',
			},
			{
				id: 'quarterly',
				title: 'Quarterly',
			}, {
				id: 'semi annually',
				title: 'Semi-annually',
			}
		]
	}

	, getExpenseTypes() {
		return [{
			id: 'fixed_monthly_repeating_amount',
			title: 'Fixed Monthly Amount'
		}, {
			id: 'percentage_of_sales',
			title: 'Expense As Percentage'
		}, {
			id: 'cost_per_unit',
			title: 'Cost Per Contract',
		}, {
			id: 'one_time_expense',
			title: 'One Time Expense'
		},
		{
			id: 'expense_per_employee',
			title: 'Expense Per Employee'
		}
		]
	}, getExpenseTypesForProperties() {
		return [{
			id: 'fixed_monthly_repeating_amount',
			title: 'Fixed Monthly Amount'
		}, {
			id: 'percentage_of_sales',
			title: 'Expense As Percentage'
		}, {
			id: 'cost_per_unit',
			title: 'Cost Per Sqm',
		}, {
			id: 'one_time_expense',
			title: 'One Time Expense'
		},
		{
			id: 'expense_per_employee',
			title: 'Expense Per Employee'
		}
		]
	},
	getPercentageOf() {
		return [
			{
				id: 'revenue',
				title: 'Revenue'
			},
			{
				id: 'contract',
				title: 'Contracts'
			},
			{
				id: 'outstanding',
				title: 'Outstanding'
			},
			{
				id: 'collection',
				title: 'Collection'
			}
		]
	},
	getPropertiesPercentageOf() {
		return [
			{
				id: 'revenue',
				title: 'Revenue'
			},
			{
				id: 'collection',
				title: 'Collection'
			}
		]
	}
	
	,
	loanNatures() {
		return [
			{
				id: 'fixed-at-end',
				title: 'Fixed At End'
			},
			{
				id: 'fixed-at-beginning',
				title: 'Fixed At Beginning'
			}

		]
	},
	loanTypes() {
		return [
			{
				id: 'normal',
				title: 'Normal'
			},
			{
				id: 'step-up',
				title: 'Step-up'
			},
			{
				id: 'step-down',
				title: 'Step-down'
			},
			{
				id: 'grace_period_with_capitalization',
				title: 'Grace Period With Capitalization'
			},
			{
				id: 'grace_period_without_capitalization',
				title: 'Grace Period Without Capitalization'
			},
			{
				id: 'grace_step-up_with_capitalization',
				title: 'Grace Step-up With Capitalization'
			}, {
				id: 'grace_step-up_without_capitalization',
				title: 'Grace Step-up Without Capitalization'
			}, {
				id: 'grace_step-down_with_capitalization',
				title: 'Grace Step-down With Capitalization'
			}, {
				id: 'grace_step-down_without_capitalization',
				title: 'Grace Step-down Without Capitalization'
			},

		]
	},
	getStepIntervals() {
		return [
			{
				id: 'quarterly',
				title: 'Quarterly'
			},
			{
				id: 'semi annually',
				title: 'Semi-annually'
			},
			{
				id: 'annually',
				title: 'Annually'
			}
		]
	},
	repeatRight(items, dateAsIndex, dates) {
		dateAsIndex = Number(dateAsIndex)
		const value = items[dateAsIndex]
		const lastKey = Number(Object.keys(dates).at(-1))
		for (let i = dateAsIndex + 1; i <= lastKey; i++) {
			items[i] = value
		}
	},
	calculateTableTotals(lastMonthIndexInEachYear, subItems, config) {
		if (!subItems || !lastMonthIndexInEachYear.value.length) {
			return {
				subRowTotals: {},
				totalPerColumns: {},
				totalRowTotals: { per_year: {}, total: 0 },
			}
		}

		const tableResult = {
			subRowTotals: {},
			totalPerColumns: {},
			totalRowTotals: { per_year: {}, total: 0 },
		}

		// تحديد نوع البيانات
		const isArray = Array.isArray(subItems)
		const isSimpleArray = config?.type === 'simple' // subItems[j]
		const hasNestedKey = config?.nestedKey // subItems[itemId]['loan_amounts'][j]

		// الحصول على القيم بناءً على النوع
		const getValue = (item, dateIndex) => {
			if (isSimpleArray) {
				// حالة: subItems[j]
				return parseFloat(item || 0)
			} else if (hasNestedKey) {
				// حالة: subItems[itemId]['loan_amounts'][j]
				return parseFloat(item?.[hasNestedKey]?.[dateIndex] || 0)
			} else {
				// حالة: subItems[itemId][j]
				return parseFloat(item?.[dateIndex] || 0)
			}
		}

		// إذا كانت simple array (مثل direct_factoring_transactions_projections)
		if (isSimpleArray) {
			tableResult.subRowTotals = { per_year: {}, total: 0 }

			let startIndex = 0
			for (const endDateOfYearIndex of lastMonthIndexInEachYear.value) {
				let yearSum = 0
				for (let j = startIndex; j <= endDateOfYearIndex; j++) {
					const value = getValue(subItems[j], j)
					yearSum += value
					tableResult.totalPerColumns[j] = (tableResult.totalPerColumns[j] || 0) + value
				}
				tableResult.subRowTotals.per_year[endDateOfYearIndex] = yearSum
				tableResult.subRowTotals.total += yearSum
				startIndex = endDateOfYearIndex + 1
			}
		}
		// إذا كانت array of objects
		else if (isArray) {
			subItems.forEach((item, itemIndex) => {
				tableResult.subRowTotals[itemIndex] = { per_year: {}, total: 0 }

				let startIndex = 0
				for (const endDateOfYearIndex of lastMonthIndexInEachYear.value) {
					let yearSum = 0
					for (let j = startIndex; j <= endDateOfYearIndex; j++) {
						const value = getValue(item, j)
						yearSum += value
						tableResult.totalPerColumns[j] = (tableResult.totalPerColumns[j] || 0) + value
					}
					tableResult.subRowTotals[itemIndex].per_year[endDateOfYearIndex] = yearSum
					tableResult.subRowTotals[itemIndex].total += yearSum
					startIndex = endDateOfYearIndex + 1
				}
			})
		}
		// إذا كانت object of arrays/objects
		else {
			for (const itemId in subItems) {
				tableResult.subRowTotals[itemId] = { per_year: {}, total: 0 }

				let startIndex = 0
				for (const endDateOfYearIndex of lastMonthIndexInEachYear.value) {
					let yearSum = 0
					for (let j = startIndex; j <= endDateOfYearIndex; j++) {
						const value = getValue(subItems[itemId], j)
						yearSum += value
						tableResult.totalPerColumns[j] = (tableResult.totalPerColumns[j] || 0) + value
					}
					tableResult.subRowTotals[itemId].per_year[endDateOfYearIndex] = yearSum
					tableResult.subRowTotals[itemId].total += yearSum
					startIndex = endDateOfYearIndex + 1
				}
			}
		}

		// حساب total row (نفس الكود لجميع الحالات)
		let startIndex = 0
		for (const endDateOfYearIndex of lastMonthIndexInEachYear.value) {
			let yearSum = 0
			for (let j = startIndex; j <= endDateOfYearIndex; j++) {
				yearSum += tableResult.totalPerColumns[j] || 0
			}
			tableResult.totalRowTotals.per_year[endDateOfYearIndex] = yearSum
			tableResult.totalRowTotals.total += yearSum
			startIndex = endDateOfYearIndex + 1
		}

		return tableResult
	},
	dispersementOfOptions() {
		return [
			{ id: 1, title: 'Next Month' },
			{ id: 2, title: 'Next Two Months' },
			{ id: 3, title: 'Next Three Months' }
		]
	}
	, formatDateAsFullMonthNameAndYear(dateString) {
		const date = new Date(dateString)
		return date.toLocaleDateString('en-US', {
			month: 'long',
			year: 'numeric'
		})
	}

}
