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
 repeatRight(items, dateAsIndex) {
  const value = items[dateAsIndex]
  const length = items.length|| Object.keys(items).length 
  for (let i = dateAsIndex + 1; i < length; i++) {
    items[i] = value
  }
},

}
