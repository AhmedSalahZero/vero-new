export default {
	number_format(number, decimals, dec_point, thousands_sep) {
            // Strip all characters but numerical ones.
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number
                , prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
                , sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep
                , dec = (typeof dec_point === 'undefined') ? '.' : dec_point
                , s = ''
                , toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }
        ,
        
        number_unformat(formattedNumber)  {
            if (formattedNumber ) {
                return parseFloat((formattedNumber + '').replace(/(<([^>]+)>)/gi, "").replace(/,/g, ""));
            }
            return 0;
        },
        getPaymentTerms()
	{
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
];
	},
	
	
	getCollectionDays(){
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
	getInstallmentInterest(){
		return [
			{
				id: 'monthly',
				title: 'Monthly',
			},
			{
				id: 'quarterly',
				title: 'Quarterly',
			},{
				id: 'semi annually',
				title: 'Semi-annually',
			}
		];
	}

	,getExpenseTypes(){
		return [{
			id:'fixed_monthly_repeating_amount',
			title:'Fixed Monthly Amount'
		},{
			id:'percentage_of_sales',
			title:'Expense As Percentage'
		},{
			id:'cost_per_unit',
			title:'Cost Per Contract',
		} ,{
			id:'one_time_expense',
			title:'One Time Expense'
		},
	{
			id:'expense_per_employee',
			title:'Expense Per Employee'
		}
	]
	}	,
	getPercentageOf(){
		return [
			{
				id:'revenue',
				title:'Revenue'
			},
			{
				id:'contract',
				title:'Contracts'
			},
			{
				id:'outstanding',
				title:'Outstanding'
			},
			{
				id:'collection',
				title:'Collection'
			}
		];
	}
	,
	loanNatures()
	{
		return [
			{
				id:'fixed-at-end',
				title:'Fixed At End'
			},
			{
				id:'fixed-at-beginning',
				title:'Fixed At Beginning'
			}
			
		]
	},
	loanTypes(){
		return [
			{
				id:'normal',
				title:'Normal'
			},
			{
				id:'step-up',
				title:'Step-up'
			},
			{
				id:'step-down',
				title:'Step-down'
			},
			{
				id:'grace_period_with_capitalization',
				title:'Grace Period With Capitalization'
			},
			{
				id:'grace_period_without_capitalization',
				title:'Grace Period Without Capitalization'
			},
			{
				id:'grace_step-up_with_capitalization',
				title:'Grace Step-up With Capitalization'
			},{
				id:'grace_step-up_without_capitalization',
				title:'Grace Step-up Without Capitalization'
			},{
				id:'grace_step-down_with_capitalization',
				title:'Grace Step-down With Capitalization'
			},{
				id:'grace_step-down_without_capitalization',
				title:'Grace Step-down Without Capitalization'
			},
			
		]
	}, 
	getStepIntervals()
	{
		return [
			{
				id:'quarterly',
				title:'Quarterly'
			},
			{
				id:'semi annually',
				title:'Semi-annually'
			},
			{
				id:'annually',
				title:'Annually'
			}
		]
	},
 repeatRight(items, dateAsIndex,dates) {
  const value = items[dateAsIndex]
  const length = dates.length|| Object.keys(dates).length 
  for (let i = dateAsIndex + 1; i < length; i++) {
    items[i] = value
  }
},
 calculateTableTotals(lastMonthIndexInEachYear,subItems, config) {
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
}


}
