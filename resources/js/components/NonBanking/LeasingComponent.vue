<script setup>
import axios from 'axios'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { computed, onMounted, ref } from 'vue'
import Helper from '../../Helpers/Helper'

// properties
const dates = ref([])
const hideTablesDates = ref({
  loansTableRef: [],
  fundingTableRef: [],
})

const getYearsFromDates = computed(() => {
  return dates.value.map((dateFormatted) => {
    return dateFormatted.split('`').pop()
  })
})

const lastMonthIndexInEachYear = ref([])

const hideOrExpandMyYear = (tableId, toDateAsIndex) => {
  const index = lastMonthIndexInEachYear.value.indexOf(toDateAsIndex)
  const fromDateAsIndex = lastMonthIndexInEachYear.value[index - 1] + 1 || 0
  const isCurrentDateExistInArray = hideTablesDates.value[tableId].includes(toDateAsIndex)
  for (let i = fromDateAsIndex; i <= toDateAsIndex; i++) {
    if (isCurrentDateExistInArray) {
      hideTablesDates.value[tableId] = hideTablesDates.value[tableId].filter(
        (i) => !(i >= fromDateAsIndex && i <= toDateAsIndex),
      )
    } else {
      hideTablesDates.value[tableId].push(i)
    }
  }
}

const allTablesTotals = computed(() => {
  const tables = {
    loanAmounts: model.value?.loan_amounts?.sub_items,
    //  anotherTable: model.value?.another_table?.sub_items, // 👈 جدول جديد
  }

  const results = {}

  for (const [tableName, subItems] of Object.entries(tables)) {
    if (!subItems || !lastMonthIndexInEachYear.value.length) {
      results[tableName] = {
        subRowTotals: {},
        totalPerColumns: {},
        totalRowTotals: { per_year: {}, total: 0 },
      }
      continue
    }

    const tableResult = {
      subRowTotals: {},
      totalPerColumns: {},
      totalRowTotals: { per_year: {}, total: 0 },
    }

    // نفس الحساب للجدول الحالي
    for (const itemId in subItems) {
      tableResult.subRowTotals[itemId] = { per_year: {}, total: 0 }

      let startIndex = 0
      for (const endDateOfYearIndex of lastMonthIndexInEachYear.value) {
        let yearSum = 0
        for (let j = startIndex; j <= endDateOfYearIndex; j++) {
          const value = parseFloat(subItems[itemId][j] || 0)
          yearSum += value
          tableResult.totalPerColumns[j] = (tableResult.totalPerColumns[j] || 0) + value
        }
        tableResult.subRowTotals[itemId].per_year[endDateOfYearIndex] = yearSum
        tableResult.subRowTotals[itemId].total += yearSum
        startIndex = endDateOfYearIndex + 1
      }
    }

    // حساب total row
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
    results[tableName] = tableResult
  }
  return results
})

// ✅ استخراج لكل جدول
const loanAmountsTotals = computed(() => allTablesTotals.value.loanAmounts || {})
// const anotherTableTotals = computed(() => allTablesTotals.value.anotherTable || {})

// للاستخدام في Template
const subRowTotals = computed(() => loanAmountsTotals.value.subRowTotals)
const totalPerColumns = computed(() => loanAmountsTotals.value.totalPerColumns)
const totalRowTotals = computed(() => loanAmountsTotals.value.totalRowTotals)

const fundingStructureCal = computed(() => {
  if (!model.value?.equity_funding_rates || !dates.value.length) {
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
  dates.value.forEach((dateFormatted, dateAsIndex) => {
    const equityRate = model.value.equity_funding_rates[dateAsIndex] || 0
    const totalColumn = totalPerColumns.value[dateAsIndex] || 0
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

// // ✅ حساب موحد واحد بدلاً من 3 computed منفصلة
// const allTotals = computed(() => {
//   const subItems = model.value?.loan_amounts?.sub_items
//   if (!subItems || !lastMonthIndexInEachYear.value.length) {
//     return {
//       subRowTotals: {},
//       totalPerColumns: {},
//       totalRowTotals: { per_year: {}, total: 0 },
//     }
//   }

//   const result = {
//     subRowTotals: {},
//     totalPerColumns: {},
//     totalRowTotals: { per_year: {}, total: 0 },
//   }

//   // 1️⃣ حساب totals لكل row + columns في نفس الوقت
//   for (const itemId in subItems) {
//     result.subRowTotals[itemId] = {
//       per_year: {},
//       total: 0,
//     }

//     let startIndex = 0
//     for (const endDateOfYearIndex of lastMonthIndexInEachYear.value) {
//       let yearSum = 0

//       // حساب السنة الحالية + إضافة للـ columns
//       for (let j = startIndex; j <= endDateOfYearIndex; j++) {
//         const value = parseFloat(subItems[itemId][j] || 0)
//         yearSum += value

//         // إضافة للـ column total
//         result.totalPerColumns[j] = (result.totalPerColumns[j] || 0) + value
//       }

//       result.subRowTotals[itemId].per_year[endDateOfYearIndex] = yearSum
//       result.subRowTotals[itemId].total += yearSum

//       startIndex = endDateOfYearIndex + 1
//     }
//   }

//   // 2️⃣ حساب total row من columns (بدون loop ثاني!)
//   let startIndex = 0
//   for (const endDateOfYearIndex of lastMonthIndexInEachYear.value) {
//     let yearSum = 0
//     for (let j = startIndex; j <= endDateOfYearIndex; j++) {
//       yearSum += result.totalPerColumns[j] || 0
//     }
//     result.totalRowTotals.per_year[endDateOfYearIndex] = yearSum
//     result.totalRowTotals.total += yearSum
//     startIndex = endDateOfYearIndex + 1
//   }

//   return result
// })

// // ✅ استخراج القيم من computed واحد
// const subRowTotals = computed(() => allTotals.value.subRowTotals)
// const totalPerColumns = computed(() => allTotals.value.totalPerColumns)
// const totalRowTotals = computed(() => allTotals.value.totalRowTotals)

const disableSubmitBtn = ref(false)
const enableEdit = ref(true)
const hasEnteredRevenueStreamBreakdown = ref(false)
const isLoading = ref(true)
const studyStartDate = ref(null)
const submitUrl = ref(null)
const selectOptions = ref({})
const model = ref(null)
const showAndHide = ref({
  leasingRevenueStreamBreakdown: true,
  loanAmounts: true,
  feesRates: true,
  fundingStructure: true,
})

// methods
const logger = (variable) => {
  console.log(variable, 'end')
  return ''
}

const handleRepeatRight = (items, dateAsIndex) => {
  Helper.repeatRight(items, dateAsIndex)
}

const addNewItem = (type) => {
  const emptyRow = model.value[type].empty_row
  return model.value[type].sub_items.push({ ...emptyRow })
}

const deleteRepeaterRow = (index, type) => {
  model.value[type].sub_items.splice(index, 1)
}

const getModelData = () => {
  const body = document.querySelector('body')
  const csrfToken = body.dataset.token
  const baseUrl = body.dataset.baseUrl
  const companyId = body.dataset.currentCompanyId
  const studyId = body.dataset.studyId
  const lang = body.dataset.lang

  const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/non-banking-financial-services/study/${studyId}/leasing-fetch-old-data`
  axios
    .get(fetchOldDataUrl, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
    })
    .then((response) => {
      studyStartDate.value = response.data.studyStartDate
      hasEnteredRevenueStreamBreakdown.value = response.data.hasEnteredRevenueStreamBreakdown
      dates.value = response.data.dates
      lastMonthIndexInEachYear.value = response.data.lastMonthIndexInEachYear
      enableEdit.value = !response.data.hasEnteredRevenueStreamBreakdown
      model.value = response.data.model
      selectOptions.value = response.data.selectOptions
      submitUrl.value = response.data.submitUrl
      isLoading.value = false
    })
    .catch((error) => {
      isLoading.value = false
      const errorMessage = error.response?.data?.message || 'An error occurred'
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: errorMessage,
      })
    })
}

const submitForm = (e) => {
  model.value.submit_button = e.target.getAttribute('data-button-value')
  disableSubmitBtn.value = true
  const body = document.querySelector('body')
  const csrfToken = body.dataset.token
  axios
    .post(submitUrl.value, model.value, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
    })
    .then((response) => {
      disableSubmitBtn.value = false
      window.location.href = response.data.redirectTo
    })
    .catch((error) => {
      const errorMessage = error.response?.data?.message || 'An error occurred'
      disableSubmitBtn.value = false
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: errorMessage,
      })
    })
}

onMounted(() => {
  getModelData()
})
</script>

<template>
  <div v-if="!isLoading">
    <!-- start Leasing Revenue Stream -->
    <div class="kt-portlet">
      <div class="kt-portlet__body">
        <div class="row">
          <div class="col-md-11">
            <div class="d-flex align-items-center">
              <h3 class="font-weight-bold form-label kt-subheader__title small-caps">
                Leasing Revenue Stream
              </h3>
            </div>
          </div>
          <div class="col-md-1">
            <div class="d-flex justify-content-end">
              <div
                @click="
                  showAndHide.leasingRevenueStreamBreakdown =
                    !showAndHide.leasingRevenueStreamBreakdown
                "
                class="btn show-hide-style">
                Show/Hide
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <hr style="background-color: lightgray" />
          </div>
        </div>

        <div
          v-if="showAndHide.leasingRevenueStreamBreakdown"
          class="row mt-4">
          <div class="col-md-12">
            <table class="table">
              <thead>
                <tr>
                  <th
                    class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class-16">
                    <div class="d-flex align-items-center justify-content-center">
                      <span
                        >Leasing <br />
                        Category</span
                      >
                    </div>
                  </th>

                  <th
                    class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class-13">
                    <span class="text-left d-inline-block"
                      >Loan <br />
                      Nature</span
                    >
                  </th>
                  <th
                    class="form-label font-weight-bold text-center align-middle loan-type-class header-border-down">
                    <span class="text-left d-inline-block"
                      >Loan <br />
                      Type</span
                    >
                  </th>

                  <th
                    class="form-label font-weight-bold text-center align-middle rate-class header-border-down">
                    <span class="text-left d-inline-block"
                      >Tenor <br />
                      Months</span
                    >
                  </th>

                  <th
                    class="form-label font-weight-bold text-center align-middle rate-class header-border-down">
                    <span class="text-left d-inline-block"
                      >Grace <br />
                      Period</span
                    >
                  </th>

                  <th
                    class="form-label font-weight-bold text-center align-middle rate-class header-border-down">
                    <span class="text-left d-inline-block min-w-percentage">Spread <br />Rate</span>
                  </th>

                  <th
                    class="form-label font-weight-bold text-center align-middle interval-class header-border-down">
                    <span class="text-left d-inline-block"
                      >Installment <br />
                      Interval</span
                    >
                  </th>

                  <th
                    class="form-label font-weight-bold text-center align-middle rate-class header-border-down">
                    <span class="text-left d-inline-block min-w-percentage"
                      >Step <br />Rate (+/-)</span
                    >
                  </th>

                  <th
                    class="form-label font-weight-bold text-center align-middle interval-class header-border-down">
                    <span class="text-left d-inline-block">Step <br />Interval</span>
                  </th>

                  <th
                    class="form-label font-weight-bold text-center align-middle col-md-1 action-class">
                    <div class="d-flex align-items-center justify-content-center">
                      <span class="">×</span>
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(leasingRevenueStreamBreakdownItem, index) in model
                    .leasingRevenueStreamBreakdown.sub_items"
                  :key="index"
                  :data-repeater-style="index + 1">
                  <td>
                    <Select
                      :disabled="!enableEdit"
                      filter
                      v-model="leasingRevenueStreamBreakdownItem.category_id"
                      :options="selectOptions.leasingCategories"
                      optionLabel="title"
                      optionValue="id"
                      placeholder=""
                      checkmark
                      :highlightOnSelect="false"
                      class="w-full md:w-56" />
                  </td>

                  <td>
                    <Select
                      :disabled="!enableEdit"
                      filter
                      v-model="leasingRevenueStreamBreakdownItem.loan_nature"
                      :options="Helper.loanNatures()"
                      optionLabel="title"
                      optionValue="id"
                      placeholder=""
                      checkmark
                      :highlightOnSelect="false"
                      class="w-full md:w-56" />
                  </td>
                  <td>
                    <Select
                      :disabled="!enableEdit"
                      filter
                      v-model="leasingRevenueStreamBreakdownItem.loan_type"
                      :options="Helper.loanTypes()"
                      optionLabel="title"
                      optionValue="id"
                      placeholder=""
                      checkmark
                      :highlightOnSelect="false"
                      class="w-full md:w-56" />
                  </td>

                  <td>
                    <InputNumber
                      :disabled="!enableEdit"
                      v-model="leasingRevenueStreamBreakdownItem.tenor"
                      :min="1"
                      input-class="text-center"
                      :minFractionDigits="0"
                      :maxFractionDigits="0"
                      fluid />
                  </td>
                  <td>
                    <InputNumber
                      :disabled="
                        !enableEdit ||
                        !leasingRevenueStreamBreakdownItem.loan_type.includes('grace')
                      "
                      v-model="leasingRevenueStreamBreakdownItem.grace_period"
                      :min="0"
                      input-class="text-center"
                      :minFractionDigits="0"
                      :maxFractionDigits="0"
                      fluid />
                  </td>
                  <td>
                    <InputNumber
                      :disabled="!enableEdit"
                      :maxFractionDigits="2"
                      :step="0.25"
                      :min="0"
                      input-class="text-center"
                      :max="100"
                      mode="decimal"
                      showButtons
                      v-model="leasingRevenueStreamBreakdownItem.margin_rate"
                      suffix=" %"
                      fluid />
                  </td>

                  <td>
                    <Select
                      :disabled="!enableEdit"
                      filter
                      v-model="leasingRevenueStreamBreakdownItem.installment_interval"
                      :options="Helper.getInstallmentInterest()"
                      optionLabel="title"
                      optionValue="id"
                      placeholder=""
                      checkmark
                      :highlightOnSelect="false"
                      class="w-full md:w-56" />
                  </td>

                  <td>
                    <InputNumber
                      :disabled="
                        !enableEdit || !leasingRevenueStreamBreakdownItem.loan_type.includes('step')
                      "
                      :maxFractionDigits="2"
                      :step="0.25"
                      :min="0"
                      input-class="text-center"
                      :max="100"
                      mode="decimal"
                      showButtons
                      v-model="leasingRevenueStreamBreakdownItem.step_rate"
                      suffix=" %"
                      fluid />
                  </td>
                  <td>
                    <Select
                      :disabled="!enableEdit"
                      filter
                      v-model="leasingRevenueStreamBreakdownItem.step_interval"
                      :options="Helper.getStepIntervals()"
                      optionLabel="title"
                      optionValue="id"
                      placeholder=""
                      checkmark
                      :highlightOnSelect="false"
                      class="w-full md:w-56" />
                  </td>

                  <td class="text-center">
                    <button
                      :disabled="!enableEdit"
                      @click="deleteRepeaterRow(index, 'leasingRevenueStreamBreakdown')"
                      type="button"
                      class="btn btn-danger btn-md btn-danger-style ml-2"
                      title="Delete">
                      <i class="fas exclude-icon fa-trash trash-icon"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="row">
              <div class="col-md-6">
                <input
                  :disabled="!enableEdit"
                  @click="addNewItem('leasingRevenueStreamBreakdown')"
                  type="button"
                  class="btn btn-primary btn-sm text-white"
                  value="Add New" />
              </div>
              <div class="col-md-6">
                <div
                  class="d-flex justify-content-end"
                  style="gap: 5px">
                  <input
                    v-if="!isLoading"
                    @click="submitForm"
                    :disabled="disableSubmitBtn || !enableEdit"
                    data-save-and-continue="1"
                    type="submit"
                    data-button-value="save-categories"
                    class="btn text-white active-style save-form"
                    value="Save" />
                  <input
                    v-if="!isLoading"
                    :disabled="disableSubmitBtn"
                    data-save-and-continue="1"
                    type="submit"
                    class="btn text-white active-style save-form"
                    @click="enableEdit = !enableEdit"
                    :value="enableEdit ? 'Disable Edit' : 'Enable Edit'" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- end Leasing Revenue Stream -->
    <!-- start Leasing Revenue Projection By Category -->
    <div
      class="kt-portlet"
      v-if="hasEnteredRevenueStreamBreakdown">
      <div class="kt-portlet__body">
        <div class="row">
          <div class="col-md-11">
            <div class="d-flex align-items-center">
              <h3 class="font-weight-bold form-label kt-subheader__title small-caps">
                Leasing Revenue Projection By Category [you can use the three dots to repeat within
                the same year]
              </h3>
            </div>
          </div>
          <div class="col-md-1">
            <div class="d-flex justify-content-end">
              <div
                @click="showAndHide.loanAmounts = !showAndHide.loanAmounts"
                class="btn show-hide-style">
                Show/Hide
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <hr style="background-color: lightgray" />
          </div>
        </div>

        <div
          v-if="showAndHide.loanAmounts"
          class="row mt-4">
          <div class="col-md-12 overflow-scroll">
            <table class="table">
              <thead>
                <tr>
                  <th
                    class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
                    Item
                  </th>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <template v-if="!hideTablesDates.loansTableRef.includes(dateAsIndex)">
                      <th
                        class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                        <span class="text-left d-inline-block">{{ dateFormatted }}</span>
                      </th>
                    </template>
                    <!--  start Total Yr. 2026 for example -->
                    <th
                      v-if="lastMonthIndexInEachYear.includes(dateAsIndex)"
                      class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                      <div
                        class="d-flex align-items-center"
                        style="gap: 10px">
                        <span class="text-left d-inline-block"
                          >Total Yr. <br />
                          {{ getYearsFromDates[dateAsIndex] }}
                        </span>
                        <i
                          @click="hideOrExpandMyYear('loansTableRef', dateAsIndex)"
                          title="Expand / Collapse"
                          class="cursor-pointer fa fa-expand-arrows-alt text-primary exclude-icon"></i>
                      </div>
                    </th>
                    <!--  end Total Yr. 2026 for example -->
                  </template>
                  <!-- start total of all years for the current row -->
                  <th
                    class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                    <div
                      class="d-flex flex-column align-items-center"
                      style="gap: 10px">
                      <span class="">Total <br /> </span>
                      <!-- <i
                        class="cursor-pointer fa fa-expand-arrows-alt text-primary exclude-icon"
                        style="visibility: hidden"></i> -->
                    </div>
                  </th>
                  <!-- end total of all years for the current row -->
                </tr>
              </thead>
              <tbody>
                <template
                  v-for="(leasingRevenueStreamBreakdownItem, index) in model
                    .leasingRevenueStreamBreakdown.sub_items"
                  :key="index">
                  <tr
                    v-if="model.loan_amounts.sub_items[leasingRevenueStreamBreakdownItem.id]"
                    :data-repeater-style="index + 1">
                    <td>
                      <div class="d-flex flex-column align-items-start">
                        <input
                          :value="model.loan_amounts.names[leasingRevenueStreamBreakdownItem.id]"
                          disabled=""
                          class="form-control min-width-hover-300 text-left mt-2"
                          type="text" />

                        <i
                          style="visibility: hidden"
                          class="fa fa-ellipsis-h"></i>
                      </div>
                    </td>
                    <template
                      v-for="(dateFormatted, dateAsIndex) in dates"
                      :key="dateAsIndex">
                      <td v-if="!hideTablesDates.loansTableRef.includes(dateAsIndex)">
                        <!-- {{ logRender(leasingRevenueStreamBreakdownItem.id, dateAsIndex) }} -->
                        <div class="d-flex flex-column align-items-center">
                          <InputNumber
                            v-model="
                              model.loan_amounts.sub_items[leasingRevenueStreamBreakdownItem.id][
                                dateAsIndex
                              ]
                            "
                            :min="0"
                            input-class="text-center"
                            :minFractionDigits="0"
                            :maxFractionDigits="2"
                            suffix=" EGP"
                            fluid />
                          <i
                            @click="
                              handleRepeatRight(
                                model.loan_amounts.sub_items[leasingRevenueStreamBreakdownItem.id],
                                dateAsIndex,
                              )
                            "
                            class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                            title="Repeat Right"></i>
                        </div>
                      </td>
                      <!--  start Total Yr. 2026 for example -->
                      <td v-if="lastMonthIndexInEachYear.includes(dateAsIndex)">
                        <InputNumber
                          v-model="
                            subRowTotals[leasingRevenueStreamBreakdownItem.id]['per_year'][
                              dateAsIndex
                            ]
                          "
                          :min="0"
                          input-class="text-center"
                          :minFractionDigits="0"
                          :maxFractionDigits="2"
                          suffix=" EGP"
                          disabled
                          fluid />
                        <i
                          style="visibility: hidden"
                          class="fa fa-ellipsis-h"></i>
                      </td>
                      <!--  end Total Yr. 2026 for example -->
                    </template>

                    <td>
                      <InputNumber
                        v-model="subRowTotals[leasingRevenueStreamBreakdownItem.id]['total']"
                        :min="0"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="2"
                        suffix=" EGP"
                        disabled
                        fluid />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </td>
                  </tr>
                </template>

                <!-- start total row -->
                <tr :data-repeater-style="Object.keys(model.loan_amounts.sub_items).length">
                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <input
                        :value="'Total'"
                        disabled=""
                        class="form-control min-width-hover-300 text-left mt-2"
                        type="text" />
                    </div>
                  </td>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <td v-if="!hideTablesDates.loansTableRef.includes(dateAsIndex)">
                      <div class="d-flex flex-column align-items-center mt-2">
                        <InputNumber
                          v-model="totalPerColumns[dateAsIndex]"
                          :min="0"
                          input-class="text-center"
                          disabled
                          :minFractionDigits="0"
                          :maxFractionDigits="2"
                          suffix=" EGP"
                          fluid />
                      </div>
                    </td>
                    <td v-if="lastMonthIndexInEachYear.includes(dateAsIndex)">
                      <InputNumber
                        v-model="totalRowTotals['per_year'][dateAsIndex]"
                        :min="0"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="2"
                        suffix=" EGP"
                        disabled
                        fluid />
                      <!-- <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i> -->
                    </td>
                  </template>

                  <td>
                    <InputNumber
                      v-model="totalRowTotals['total']"
                      :min="0"
                      :minFractionDigits="0"
                      :maxFractionDigits="2"
                      input-class="text-center"
                      suffix=" EGP"
                      disabled
                      fluid />
                    <!-- <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i> -->
                  </td>
                </tr>

                <!-- end total row -->
              </tbody>
            </table>

            <!-- <div class="row">
              <div class="col-md-6">
                <input
                  :disabled="!enableEdit"
                  @click="addNewItem('leasingRevenueStreamBreakdown')"
                  type="button"
                  class="btn btn-primary btn-sm text-white"
                  value="Add New" />
              </div>
              <div class="col-md-6">
                <div
                  class="d-flex justify-content-end"
                  style="gap: 5px">
                  <input
                    v-if="!isLoading"
                    @click="submitForm"
                    :disabled="disableSubmitBtn || !enableEdit"
                    data-save-and-continue="1"
                    type="submit"
                    class="btn text-white active-style save-form"
                    value="Save" />
                  <input
                    v-if="!isLoading"
                    :disabled="disableSubmitBtn"
                    data-save-and-continue="1"
                    type="submit"
                    class="btn text-white active-style save-form"
                    @click="enableEdit = !enableEdit"
                    :value="enableEdit ? 'Disable Edit' : 'Enable Edit'" />
                </div>
              </div>
            </div> -->
          </div>
        </div>
      </div>
    </div>
    <!-- end Leasing Revenue Projection By Category -->

    <!-- start Administration Fees Rate & ECL Rate -->
    <div
      class="kt-portlet"
      v-if="hasEnteredRevenueStreamBreakdown">
      <div class="kt-portlet__body">
        <div class="row">
          <div class="col-md-11">
            <div class="d-flex align-items-center">
              <h3 class="font-weight-bold form-label kt-subheader__title small-caps">
                Administration Fees Rate & ECL Rate
              </h3>
            </div>
          </div>
          <div class="col-md-1">
            <div class="d-flex justify-content-end">
              <div
                @click="showAndHide.feesRates = !showAndHide.feesRates"
                class="btn show-hide-style">
                Show/Hide
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <hr style="background-color: lightgray" />
          </div>
        </div>

        <div
          v-if="showAndHide.feesRates"
          class="row mt-4">
          <div class="col-md-12 overflow-scroll">
            <table class="table">
              <thead>
                <tr>
                  <th
                    class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
                    Item
                  </th>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <th
                      class="form-label font-weight-bold text-center align-middle header-border-down">
                      <span class="text-left d-inline-block">{{ dateFormatted }}</span>
                    </th>
                  </template>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-if="model.admin_fees"
                  :data-repeater-style="1">
                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <input
                        :value="'Administration Fees Rate'"
                        disabled=""
                        class="form-control min-width-hover-300 text-left mt-2"
                        type="text" />

                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <td>
                      <div class="d-flex flex-column align-items-center mt-2">
                        <InputNumber
                          v-model="model.admin_fees[dateAsIndex]"
                          :min="0"
                          input-class="text-center"
                          :minFractionDigits="2"
                          suffix=" %"
                          fluid />
                        <i
                          @click="handleRepeatRight(model.admin_fees, dateAsIndex)"
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i>
                      </div>
                    </td>
                  </template>
                </tr>

                <tr
                  v-if="model.admin_fees"
                  :data-repeater-style="1">
                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <input
                        :value="'Expected Credit Loss Rate (ECL %)'"
                        disabled=""
                        class="form-control min-width-hover-300 text-left mt-2"
                        type="text" />

                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <td>
                      <div class="d-flex flex-column align-items-center mt-2">
                        <InputNumber
                          v-model="model.ecl_rates[dateAsIndex]"
                          :min="0"
                          input-class="text-center"
                          :minFractionDigits="2"
                          suffix=" %"
                          fluid />
                        <i
                          @click="handleRepeatRight(model.ecl_rates, dateAsIndex)"
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i>
                      </div>
                    </td>
                  </template>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- end Administration Fees Rate & ECL Rate -->

    <!-- start Leasing New Portfolio Funding Structure -->
    <div
      class="kt-portlet"
      v-if="hasEnteredRevenueStreamBreakdown">
      <div class="kt-portlet__body">
        <div class="row">
          <div class="col-md-11">
            <div class="d-flex align-items-center">
              <h3 class="font-weight-bold form-label kt-subheader__title small-caps">
                Leasing New Portfolio Funding Structure
              </h3>
            </div>
          </div>
          <div class="col-md-1">
            <div class="d-flex justify-content-end">
              <div
                @click="showAndHide.fundingStructure = !showAndHide.fundingStructure"
                class="btn show-hide-style">
                Show/Hide
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <hr style="background-color: lightgray" />
          </div>
        </div>

        <div
          v-if="showAndHide.fundingStructure"
          class="row mt-4">
          <div class="col-md-12 overflow-scroll">
            <table class="table">
              <thead>
                <tr>
                  <th
                    class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
                    Item
                  </th>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <template v-if="!hideTablesDates.fundingTableRef.includes(dateAsIndex)">
                      <th
                        class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                        <span class="text-left d-inline-block">{{ dateFormatted }}</span>
                      </th>
                    </template>
                    <!--  start Total Yr. 2026 for example -->
                    <th
                      v-if="lastMonthIndexInEachYear.includes(dateAsIndex)"
                      class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                      <div
                        class="d-flex align-items-center"
                        style="gap: 10px">
                        <span class="text-left d-inline-block"
                          >Total Yr. <br />
                          {{ getYearsFromDates[dateAsIndex] }}
                        </span>
                        <i
                          @click="hideOrExpandMyYear('fundingTableRef', dateAsIndex)"
                          title="Expand / Collapse"
                          class="cursor-pointer fa fa-expand-arrows-alt text-primary exclude-icon"></i>
                      </div>
                    </th>
                    <!--  end Total Yr. 2026 for example -->
                  </template>
                  <!-- start total of all years for the current row -->
                  <th
                    class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                    <div
                      class="d-flex flex-column align-items-center"
                      style="gap: 10px">
                      <span class="">Total <br /> </span>
                      <!-- <i
                        class="cursor-pointer fa fa-expand-arrows-alt text-primary exclude-icon"
                        style="visibility: hidden"></i> -->
                    </div>
                  </th>
                  <!-- end total of all years for the current row -->
                </tr>
              </thead>
              <tbody>
                <!-- start equity funding rate -->
                <tr :data-repeater-style="1">
                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <input
                        :value="'Equity Funding Rate (%)'"
                        disabled=""
                        class="form-control min-width-hover-300 text-left mt-2"
                        type="text" />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <td v-if="!hideTablesDates.fundingTableRef.includes(dateAsIndex)">
                      <!-- {{ logRender(leasingRevenueStreamBreakdownItem.id, dateAsIndex) }} -->
                      <div
                        class="d-flex min-w-percentage mx-auto mt-2 text-center flex-column align-items-center">
                        <InputNumber
                          v-model="model.equity_funding_rates[dateAsIndex]"
                          :min="0"
                          :max="100"
                          input-class="text-center"
                          :minFractionDigits="2"
                          suffix=" %"
                          fluid />
                        <i
                          @click="handleRepeatRight(model.equity_funding_rates, dateAsIndex)"
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i>
                      </div>
                    </td>
                    <!--  start Total Yr. 2026 for example -->
                    <td v-if="lastMonthIndexInEachYear.includes(dateAsIndex)">
                      <InputText
                        :value="'-'"
                        :pt="{
                          root: { class: 'text-center' },
                        }"
                        disabled
                        fluid />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </td>
                    <!--  end Total Yr. 2026 for example -->
                  </template>

                  <td>
                    <InputNumber
                      :min="0"
                      :minFractionDigits="0"
                      :maxFractionDigits="2"
                      suffix=" EGP"
                      disabled
                      fluid />
                    <i
                      style="visibility: hidden"
                      class="fa fa-ellipsis-h"></i>
                  </td>
                </tr>
                <!-- end Equity Funding rates -->
                <!-- start Equity Funding Value -->
                <tr :data-repeater-style="2">
                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <input
                        :value="'Equity Funding Value'"
                        disabled=""
                        class="form-control min-width-hover-300 text-left mt-3"
                        type="text" />

                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <td v-if="!hideTablesDates.fundingTableRef.includes(dateAsIndex)">
                      <div class="d-flex flex-column align-items-center">
                        <InputNumber
                          v-model="equityFundingValues[dateAsIndex]"
                          :min="0"
                          disabled
                          input-class="text-center"
                          :minFractionDigits="0"
                          :maxFractionDigits="2"
                          suffix=" EGP"
                          fluid />
                        <!-- <i
                          @click="
                            handleRepeatRight(
                              model.loan_amounts.sub_items[leasingRevenueStreamBreakdownItem.id],
                              dateAsIndex,
                            )
                          "
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i> -->
                      </div>
                    </td>
                    <!--  start Total Yr. 2026 for example -->
                    <td v-if="lastMonthIndexInEachYear.includes(dateAsIndex)">
                      <InputNumber
                        :min="0"
                        input-class="text-center"
                        :modelValue="equityTotals.per_year[dateAsIndex]"
                        :minFractionDigits="0"
                        :maxFractionDigits="2"
                        suffix=" EGP"
                        disabled
                        fluid />
                      <!-- <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i> -->
                    </td>
                    <!--  end Total Yr. 2026 for example -->
                  </template>
                  <!-- Start Grand Total -->
                  <td>
                    <InputNumber
                      :min="0"
                      input-class="text-center"
                      :modelValue="equityTotals.total"
                      :minFractionDigits="0"
                      :maxFractionDigits="2"
                      suffix=" EGP"
                      disabled
                      fluid />
                    <!-- <i
                      style="visibility: hidden"
                      class="fa fa-ellipsis-h"></i> -->
                  </td>
                  <!-- End Grand Total -->
                </tr>

                <!-- end equity funding values -->

                <!-- start new loan funding rate -->
                <tr :data-repeater-style="3">
                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <input
                        :value="'New Loans Funding Rate (%)'"
                        disabled=""
                        class="form-control min-width-hover-300 text-left mt-3"
                        type="text" />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <td v-if="!hideTablesDates.fundingTableRef.includes(dateAsIndex)">
                      <!-- {{ logRender(leasingRevenueStreamBreakdownItem.id, dateAsIndex) }} -->
                      <div
                        class="d-flex min-w-percentage mx-auto mt-3 text-center flex-column align-items-center">
                        <InputNumber
                          v-model="newLoansFundingRates[dateAsIndex]"
                          :min="0"
                          disabled
                          input-class="text-center"
                          :minFractionDigits="2"
                          :maxFractionDigits="2"
                          suffix=" %"
                          fluid />
                        <i
                          @click="handleRepeatRight(model.equity_funding_rates, dateAsIndex)"
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i>
                      </div>
                    </td>
                    <!--  start Total Yr. 2026 for example -->
                    <td v-if="lastMonthIndexInEachYear.includes(dateAsIndex)">
                      <InputText
                        :value="'-'"
                        :pt="{
                          root: { class: 'text-center' },
                        }"
                        disabled
                        fluid />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </td>
                    <!--  end Total Yr. 2026 for example -->
                  </template>

                  <td>
                    <InputNumber
                      :min="0"
                      :minFractionDigits="0"
                      :maxFractionDigits="2"
                      suffix=" EGP"
                      disabled
                      fluid />
                    <i
                      style="visibility: hidden"
                      class="fa fa-ellipsis-h"></i>
                  </td>
                </tr>
                <!-- end new loan Funding rates -->
                <!-- start new loan Funding Value -->
                <tr :data-repeater-style="4">
                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <input
                        :value="'New Loans Funding Value'"
                        disabled=""
                        class="form-control min-width-hover-300 text-left mt-3"
                        type="text" />

                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <td v-if="!hideTablesDates.fundingTableRef.includes(dateAsIndex)">
                      <div class="d-flex flex-column align-items-center mt-3">
                        <InputNumber
                          v-model="newLoansFundingValues[dateAsIndex]"
                          :min="0"
                          disabled
                          input-class="text-center"
                          :minFractionDigits="0"
                          :maxFractionDigits="2"
                          suffix=" EGP"
                          fluid />
                        <i
                          style="visibility: hidden"
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i>
                      </div>
                    </td>
                    <!--  start Total Yr. 2026 for example -->
                    <td v-if="lastMonthIndexInEachYear.includes(dateAsIndex)">
                      <InputNumber
                        :min="0"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="2"
                        :modelValue="newLoansTotals.per_year[dateAsIndex]"
                        suffix=" EGP"
                        disabled
                        fluid />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </td>
                    <!--  end Total Yr. 2026 for example -->
                  </template>
                  <!-- start Grand Total -->

                  <td>
                    <InputNumber
                      :min="0"
                      :v-model="newLoansTotals.total"
                      input-class="text-center"
                      :minFractionDigits="0"
                      :maxFractionDigits="2"
                      suffix=" EGP"
                      disabled
                      fluid />
                    <i
                      style="visibility: hidden"
                      class="fa fa-ellipsis-h"></i>
                  </td>
                  <!-- end Grand Total -->
                </tr>

                <!-- end new loan funding values -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- end Leasing New Portfolio Funding Structure -->

    <div class="col-md-12">
      <div
        class="d-flex justify-content-end"
        style="gap: 5px">
        <input
          v-if="!isLoading"
          @click="submitForm"
          :disabled="disableSubmitBtn"
          data-save-and-continue="1"
          type="submit"
          data-button-value="save-and-go-to-next-value"
          class="btn text-white active-style save-form"
          value="Save & Go To Next" />
      </div>
    </div>
  </div>
</template>
<style scoped>
.max-w-70px {
  max-width: 70px !important;
}

.max-w-185px {
  max-width: 185px !important;
}

.max-w-200px {
  max-width: 200px !important;
}

.max-w-530px {
  max-width: 530px !important;
}

.input-border {
  border: 1px solid #6babef;
}

/* Fix z-index for PrimeVue Select dropdown inside modal */
:deep(.p-select-overlay) {
  z-index: 1060 !important;
}

/* Alternative: Fix for all PrimeVue overlays inside modals */
.modal {
  z-index: 990 !important;
}

.modal-body {
  max-height: 800px;
}

:deep(.p-skeleton) {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s ease-in-out infinite;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

.btn-div {
  padding: 0 !important;
  width: 30px !important;
  height: 30px !important;
}

.btn-div span {
  font-size: 20px !important;
  cursor: pointer;
}

.trash_icon {
  width: 30px;
  height: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
}

:deep(.p-component, .dp__input) {
  height: 38px !important;
}

:deep(.p-select) {
  border: 1px solid #4d9afa;
}

/* Or target the input more specifically */
:deep(.dp__input) {
  height: 38px !important;
}

:deep(.p-select-label) {
  display: flex;
  align-items: center;
}
:deep(.p-multiselect-label-container) {
  display: flex;
  align-items: center;
}

.btn-danger-style {
  height: 30px !important;
}

.btn-danger-style i {
  padding-right: 0 !important;
  color: white !important;
}

:deep(.p-select-label.p-placeholder),
:deep(.p-select-label) {
  color: black !important;
}
:deep(.p-multiselect-label) {
  color: black !important;
}
:deep(.p-select:not(.p-disabled).p-focus) {
  border-color: #4d9afa;
}

:deep(.p-select) {
  border-color: #4d9afa !important;
}
.max-w-150 {
  width: 150px !important;
  min-width: 150px !important;
  max-width: 150px !important;
}

.max-w-175 {
  width: 175px !important;
  min-width: 175px !important;
  max-width: 175px !important;
}
.col {
  flex-shrink: 1;
  min-width: 0;
}
.min-w-160 {
  min-width: 160px !important;
}
.min-w-140 {
  min-width: 140px !important;
}
.min-w-percentage {
  width: 110px !important;
}
</style>
