<script setup>
import axios from 'axios'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { computed, onMounted, ref } from 'vue'
import Helper from '../../../Helpers/Helper'
import { useNewLoanFundingCalculator } from '../../composables/useNewLoanFundingCalculator'
import { useTableExpender } from '../../composables/useTableExpender'
import Loading from '../../../components/Common/Loading.vue'

// properties
const dates = ref([])

const hideTablesDates = ref({
  ijaraMortgageRevenueProjectionByCategoryRef: [],
  ijaraMortgageBreakdownsRef: [],
  fundingTableRef: [],
})

const getYearsFromDates = computed(() => {
  let result = {}
  Object.keys(dates.value).forEach((dateAsIndex) => {
    result[dateAsIndex] = dates.value[dateAsIndex].split("'").pop()
  })
  return result
})

const lastMonthIndexInEachYear = ref([])
const { hideOrExpandMyYear } = useTableExpender(lastMonthIndexInEachYear, hideTablesDates)
const emits = defineEmits(['loanAmountChanged'])
const loanAmounts = computed(() => {
  const results = {}

  // نخرج المراجع خارج الحلقات لسرعة الوصول (Caching references)
  const breakdowns = model.value?.ijaraMortgageBreakdowns
  const projections =
    model.value?.ijaraMortgageRevenueProjectionByCategory?.ijara_mortgage_transactions_projections
  const dateKeys = Object.keys(dates.value)

  Object.keys(breakdowns ? breakdowns : {}).forEach((index) => {
    const categoryResults = {}
    const percentagePayload = breakdowns[index].percentage_payload || []
    dateKeys.forEach((dateAsIndex) => {
      const currentValue = projections[dateAsIndex] || 0
      const currentPercentage = percentagePayload[dateAsIndex] || 0
      categoryResults[dateAsIndex] = (currentPercentage / 100) * currentValue
    })
    results[index] = categoryResults
    model.value.ijaraMortgageBreakdowns[index].loan_amounts = categoryResults
  })
  //   emits('loanAmountChanged', results)
  return results
})
const updateLoanAmount = (breakdownIndex, dateAsIndex, newLoanAmount) => {
  // الحصول على القيمة الإجمالية من الـ projection
  const projectionValue =
    model.value?.ijaraMortgageRevenueProjectionByCategory
      ?.ijara_mortgage_transactions_projections?.[dateAsIndex] || 0

  if (projectionValue === 0) {
    model.value.ijaraMortgageBreakdowns[breakdownIndex].percentage_payload[dateAsIndex] = 0
    return
  }

  // حساب النسبة الجديدة من الـ loan amount
  const newPercentage = (newLoanAmount / projectionValue) * 100

  // تحديث النسبة
  model.value.ijaraMortgageBreakdowns[breakdownIndex].percentage_payload[dateAsIndex] = Math.min(
    100,
    Math.max(0, newPercentage),
  ) // التأكد أن النسبة بين 0 و 100
}
const allTablesTotals = computed(() => {
  return {
    // حالة 1: array of objects مع nested key
    ijaraMortgageBreakdownTotals: Helper.calculateTableTotals(
      lastMonthIndexInEachYear,
      model.value?.ijaraMortgageBreakdowns,
      {
        nestedKey: 'loan_amounts',
      },
    ),
    ijaraMortgageProjectTotals: Helper.calculateTableTotals(
      lastMonthIndexInEachYear,
      model.value?.ijaraMortgageRevenueProjectionByCategory
        ?.ijara_mortgage_transactions_projections,
      { type: 'simple' },
    ),
    netDisbursementTotals: Helper.calculateTableTotals(
      lastMonthIndexInEachYear,
      model.value?.netDisbursements,
      { type: 'simple' },
    ),
  }
})
const ijaraMortgageBreakdownTotals = computed(
  () => allTablesTotals.value.ijaraMortgageBreakdownTotals,
)
const ijaraMortgageProjectTotals = computed(() => allTablesTotals.value.ijaraMortgageProjectTotals)

// const fundingStructureCal = computed(() => {
//   if (!model.value?.equity_funding_rates || !Object.keys(dates.value).length) {
//     return {
//       equityFundingValues: [],
//       equityTotals: { per_year: {}, total: 0 },
//       newLoansFundingRates: [],
//       newLoansFundingValues: [],
//       newLoansTotals: { per_year: {}, total: 0 },
//     }
//   }

//   const results = {
//     equityFundingValues: [],
//     equityTotals: { per_year: {}, total: 0 },
//     newLoansFundingRates: [],
//     newLoansFundingValues: [],
//     newLoansTotals: { per_year: {}, total: 0 },
//   }

//   // 1️⃣ حساب القيم لكل تاريخ
//   Object.keys(dates.value).forEach((dateFormatted, dateAsIndex) => {
//     const equityRate = model.value.equity_funding_rates[dateAsIndex] || 0
//     const totalColumn =
//       model.value.ijaraMortgageRevenueProjectionByCategory.ijara_mortgage_transactions_projections[
//         dateAsIndex
//       ] || 0
//     // const totalColumn = totalPerColumns.value[dateAsIndex] || 0
//     // Equity Funding
//     results.equityFundingValues[dateAsIndex] = totalColumn * (equityRate / 100)

//     // New Loans Funding Rate
//     results.newLoansFundingRates[dateAsIndex] = 100 - equityRate
//     // New Loans Funding Value
//     results.newLoansFundingValues[dateAsIndex] =
//       totalColumn * (results.newLoansFundingRates[dateAsIndex] / 100)
//   })

//   // 2️⃣ حساب Totals per Year + Grand Total
//   if (lastMonthIndexInEachYear.value.length) {
//     let startIndex = 0

//     for (const endDateOfYearIndex of lastMonthIndexInEachYear.value) {
//       let equityYearSum = 0
//       let newLoansYearSum = 0
//       // حساب مجموع السنة
//       for (let j = startIndex; j <= endDateOfYearIndex; j++) {
//         equityYearSum += results.equityFundingValues[j] || 0
//         newLoansYearSum += results.newLoansFundingValues[j] || 0
//       }

//       // حفظ مجموع السنة
//       results.equityTotals.per_year[endDateOfYearIndex] = equityYearSum
//       results.newLoansTotals.per_year[endDateOfYearIndex] = newLoansYearSum

//       // إضافة للمجموع الكلي
//       results.equityTotals.total += equityYearSum
//       results.newLoansTotals.total += newLoansYearSum

//       startIndex = endDateOfYearIndex + 1
//     }
//   }
//   model.value.equity_funding_values = results.equityFundingValues
//   model.value.new_loans_funding_rates = results.newLoansFundingRates
//   model.value.new_loans_funding_values = results.newLoansFundingValues
//   return results
// })

// // ✅ استخراج القيم
// const equityFundingValues = computed(() => fundingStructureCal.value.equityFundingValues)
// const equityTotals = computed(() => fundingStructureCal.value.equityTotals)
// const newLoansFundingRates = computed(() => fundingStructureCal.value.newLoansFundingRates)
// const newLoansFundingValues = computed(() => fundingStructureCal.value.newLoansFundingValues)
// const newLoansTotals = computed(() => fundingStructureCal.value.newLoansTotals)
const model = ref(null)
const totalLoanAmounts = computed(
  () =>
    model.value?.ijaraMortgageRevenueProjectionByCategory?.ijara_mortgage_transactions_projections,
)
const {
  equityFundingValues,
  equityTotals,
  newLoansFundingRates,
  newLoansFundingValues,
  newLoansTotals,
} = useNewLoanFundingCalculator(model, dates, totalLoanAmounts, lastMonthIndexInEachYear)

const disableSubmitBtn = ref(false)
const isLoading = ref(true)
const studyStartDate = ref(null)
const submitUrl = ref(null)
const selectOptions = ref({})

const showAndHide = ref({
  ijaraMortgageProjects: true,
  ijaraMortgageBreakdown: true,
  feesRates: true,
  fundingStructure: true,
})

// methods
const logger = (variable) => {
  console.log(variable, 'end')
  return ''
}

const handleRepeatRight = (items, dateAsIndex, dates) => {
  Helper.repeatRight(items, dateAsIndex, dates)
}

const addNewItem = (type) => {
  const emptyRow = empty_rows.value[type]
  return model.value[type].push({ ...emptyRow })
}

const deleteRepeaterRow = (index, type) => {
  model.value[type].splice(index, 1)
}
const empty_rows = ref([])
const getModelData = () => {
  const body = document.querySelector('body')
  const csrfToken = body.dataset.token
  const baseUrl = body.dataset.baseUrl
  const companyId = body.dataset.currentCompanyId
  const studyId = body.dataset.studyId
  const lang = body.dataset.lang

  const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/non-banking-financial-services/study/${studyId}/ijara-mortgage-fetch-old-data`
  axios
    .get(fetchOldDataUrl, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
    })
    .then((response) => {
      studyStartDate.value = response.data.studyStartDate
      empty_rows.value = response.data.empty_rows
      dates.value = response.data.dates
      lastMonthIndexInEachYear.value = response.data.lastMonthIndexInEachYear
      model.value = response.data.model
      selectOptions.value = response.data.selectOptions
      submitUrl.value = response.data.submitUrl
      isLoading.value = false
    })
    .catch((error) => {
      isLoading.value = false
      const errorMessage = error.response?.data?.message || 'An error occurred' + error
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: errorMessage,
      })
    })
}
// watch(loanAmounts, (newValue, oldValue) => {
//   console.log('changed')
//   console.log(newValue, oldValue)
// })
const submitForm = (e) => {
  model.value.submit_button = e.target.getAttribute('data-button-value')
  console.log(e.target)
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
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Your data has been saved',
        draggable: true,
        timer: 2000,
      }).then((res) => {
        disableSubmitBtn.value = false
        window.location.href = response.data.redirectTo
      })
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
  <div class="row">
    <div
      class="col-md-12"
      v-if="isLoading">
      <div class="kt-portlet">
        <div class="kt-portlet__body exclude">
          <div class="col-md-12">
            <Loading :isLoading="isLoading"></Loading>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-if="!isLoading">
    <!-- start Leasing Revenue Projection By Category -->
    <div class="kt-portlet">
      <div class="kt-portlet__body">
        <div class="row">
          <div class="col-md-11">
            <div class="d-flex align-items-center">
              <h3 class="font-weight-bold form-label kt-subheader__title small-caps">
                Ijara Mortgage Revenue Projection By Category [you can use the three dots to repeat
                within the same year]
              </h3>
            </div>
          </div>
          <div class="col-md-1">
            <div class="d-flex justify-content-end">
              <div
                @click="showAndHide.ijaraMortgageProjects = !showAndHide.ijaraMortgageProjects"
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
          v-show="showAndHide.ijaraMortgageProjects"
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
                    <template
                      v-if="
                        !hideTablesDates.ijaraMortgageRevenueProjectionByCategoryRef.includes(
                          Number(dateAsIndex),
                        )
                      ">
                      <th
                        class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                        <span class="text-left d-inline-block">{{ dateFormatted }}</span>
                      </th>
                    </template>
                    <!--  start Total Yr. 2026 for example -->
                    <th
                      v-if="
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
                        lastMonthIndexInEachYear.length > 1
                      "
                      class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                      <div
                        class="d-flex align-items-center"
                        style="gap: 10px">
                        <span class="text-left d-inline-block"
                          >Total Yr. <br />
                          {{ getYearsFromDates[dateAsIndex] }}
                        </span>
                        <i
                          @click="
                            hideOrExpandMyYear(
                              'ijaraMortgageRevenueProjectionByCategoryRef',
                              Number(dateAsIndex),
                            )
                          "
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
                <tr
                  v-if="!isLoading"
                  :data-repeater-style="true">
                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <input
                        :value="'Ijara Mortgage Projection'"
                        disabled=""
                        class="form-control min-width-300 text-left mt-2"
                        type="text" />

                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <td
                      v-if="
                        !hideTablesDates.ijaraMortgageRevenueProjectionByCategoryRef.includes(
                          Number(dateAsIndex),
                        )
                      ">
                      <!-- {{ logRender(leasingRevenueStreamBreakdownItem.id, dateAsIndex) }} -->
                      <div class="d-flex flex-column align-items-center">
                        <InputNumber
                          v-model="
                            model.ijaraMortgageRevenueProjectionByCategory
                              .ijara_mortgage_transactions_projections[dateAsIndex]
                          "
                          :min="0"
                          input-class="text-center"
                          :minFractionDigits="0"
                          :maxFractionDigits="0"
                          suffix=" EGP"
                          fluid />
                        <i
                          @click="
                            handleRepeatRight(
                              model.ijaraMortgageRevenueProjectionByCategory
                                .ijara_mortgage_transactions_projections,
                              dateAsIndex,
                              dates,
                            )
                          "
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i>
                      </div>
                    </td>
                    <!--  start Total Yr. 2026 for example -->
                    <td
                      v-if="
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
                        lastMonthIndexInEachYear.length > 1
                      ">
                      <InputNumber
                        v-model="ijaraMortgageProjectTotals.subRowTotals['per_year'][dateAsIndex]"
                        :min="0"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
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
                      v-model="ijaraMortgageProjectTotals.subRowTotals['total']"
                      :min="0"
                      input-class="text-center"
                      :minFractionDigits="0"
                      :maxFractionDigits="0"
                      suffix=" EGP"
                      disabled
                      fluid />
                    <i
                      style="visibility: hidden"
                      class="fa fa-ellipsis-h"></i>
                  </td>
                </tr>

                <!-- end total row -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- end Leasing Revenue Projection By Category -->

    <!-- start ijara mortgage Breakdown -->
    <div class="kt-portlet">
      <div class="kt-portlet__body">
        <div class="row">
          <div class="col-md-11">
            <div class="d-flex align-items-center">
              <h3 class="font-weight-bold form-label kt-subheader__title small-caps">
                ijara mortgage Breakdown
              </h3>
            </div>
          </div>
          <div class="col-md-1">
            <div class="d-flex justify-content-end">
              <div
                @click="showAndHide.ijaraMortgageBreakdown = !showAndHide.ijaraMortgageBreakdown"
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
          v-show="showAndHide.ijaraMortgageBreakdown"
          class="row mt-4">
          <div class="col-md-12 overflow-scroll">
            <table class="table">
              <thead>
                <tr>
                  <th
                    class="form-label font-weight-bold text-center align-middle col-md-1 action-class">
                    <div class="d-flex align-items-center justify-content-center">
                      <span class="">×</span>
                    </div>
                  </th>

                  <th
                    class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
                    Category
                  </th>
                  <th
                    class="form-label font-weight-bold text-center align-middle header-border-down">
                    Tenor <br />
                    (Months)
                  </th>
                  <th
                    class="form-label font-weight-bold text-center align-middle header-border-down">
                    Grace <br />
                    Period
                  </th>
                  <th
                    class="form-label font-weight-bold text-center align-middle header-border-down">
                    Spread <br />
                    Rate
                  </th>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <template
                      v-if="
                        !hideTablesDates.ijaraMortgageBreakdownsRef.includes(Number(dateAsIndex))
                      ">
                      <th
                        class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                        <span class="text-left d-inline-block">{{ dateFormatted }}</span>
                      </th>
                    </template>
                    <!--  start Total Yr. 2026 for example -->
                    <th
                      v-if="
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
                        lastMonthIndexInEachYear.length > 1
                      "
                      class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                      <div
                        class="d-flex align-items-center"
                        style="gap: 10px">
                        <span class="text-left d-inline-block"
                          >Total Yr. <br />
                          {{ getYearsFromDates[dateAsIndex] }}
                        </span>
                        <i
                          @click="
                            hideOrExpandMyYear('ijaraMortgageBreakdownsRef', Number(dateAsIndex))
                          "
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
                  v-for="(ijaraMortgageBreakdownItem, index) in model.ijaraMortgageBreakdowns"
                  :key="index">
                  <tr
                    v-if="!isLoading"
                    :data-repeater-style="index + 1">
                    <td class="text-center">
                      <button
                        @click="deleteRepeaterRow(index, 'ijaraMortgageBreakdowns')"
                        type="button"
                        class="btn btn-danger btn-md btn-danger-style ml-2"
                        title="Delete">
                        <i class="fas exclude-icon fa-trash trash-icon"></i>
                      </button>
                    </td>

                    <td>
                      <div
                        class="d-flex flex-column align-items-start"
                        style="gap: 5px">
                        <Select
                          filter
                          v-model="ijaraMortgageBreakdownItem.installment_interval"
                          :options="selectOptions.installmentIntervals"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />

                        <input
                          :value="'ijara Mortgage Projection'"
                          disabled=""
                          class="form-control min-width-300 text-left mt-2"
                          type="text" />
                      </div>
                    </td>
                    <td>
                      <InputNumber
                        v-model="ijaraMortgageBreakdownItem.tenor"
                        :min="ijaraMortgageBreakdownItem.grace_period + 1"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
                        suffix=" Mth"
                        fluid />
                    </td>
                    <td>
                      <InputNumber
                        v-model="ijaraMortgageBreakdownItem.grace_period"
                        :min="0"
                        :max="ijaraMortgageBreakdownItem.tenor - 1"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
                        suffix=" Mth"
                        fluid />
                    </td>
                    <td>
                      <InputNumber
                        v-model="ijaraMortgageBreakdownItem.margin_rate"
                        :min="0"
                        input-class="text-center"
                        :minFractionDigits="2"
                        suffix=" %"
                        fluid />
                    </td>
                    <template
                      v-for="(dateFormatted, dateAsIndex) in dates"
                      :key="dateAsIndex">
                      <td
                        v-if="
                          !hideTablesDates.ijaraMortgageBreakdownsRef.includes(Number(dateAsIndex))
                        ">
                        <!-- {{ logRender(leasingRevenueStreamBreakdownItem.id, dateAsIndex) }} -->
                        <div
                          class="d-flex flex-column align-items-center"
                          style="gap: 10px">
                          <div class="min-w-percentage d-flex align-items-center flex-column">
                            <InputNumber
                              v-model="ijaraMortgageBreakdownItem.percentage_payload[dateAsIndex]"
                              :min="0"
                              input-class="text-center"
                              :minFractionDigits="2"
                              suffix=" %"
                              fluid />
                            <i
                              @click="
                                handleRepeatRight(
                                  ijaraMortgageBreakdownItem.percentage_payload,
                                  dateAsIndex,
                                  dates,
                                )
                              "
                              class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                              title="Repeat Right"></i>
                          </div>

                          <InputNumber
                            :model-value="loanAmounts[index][dateAsIndex]"
                            @update:model-value="
                              (newValue) => updateLoanAmount(index, dateAsIndex, newValue)
                            "
                            :min="0"
                            input-class="text-center"
                            :minFractionDigits="0"
                            :maxFractionDigits="0"
                            suffix=" EGP"
                            fluid />
                        </div>
                      </td>
                      <!--  start Total Yr. 2026 for example -->
                      <td
                        v-if="
                          lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
                          lastMonthIndexInEachYear.length > 1
                        ">
                        <InputNumber
                          v-model="
                            ijaraMortgageBreakdownTotals.subRowTotals[index]['per_year'][
                              dateAsIndex
                            ]
                          "
                          :min="0"
                          input-class="text-center"
                          :minFractionDigits="0"
                          :maxFractionDigits="0"
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
                        v-model="ijaraMortgageBreakdownTotals.subRowTotals[index]['total']"
                        :min="0"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
                        suffix=" EGP"
                        disabled
                        fluid />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </td>
                  </tr>
                </template>

                <!-- end total row -->
              </tbody>
            </table>

            <div class="d-flex mb-3">
              <div class="col-md-6">
                <input
                  @click="addNewItem('ijaraMortgageBreakdowns')"
                  type="button"
                  class="btn btn-primary btn-sm text-white"
                  value="Add New" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- end ijara mortgage Breakdown -->

    <!-- start Administration Fees Rate & ECL Rate -->
    <div class="kt-portlet">
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
          v-show="showAndHide.feesRates"
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
                  v-if="model.admin_fees_rates"
                  :data-repeater-style="true">
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
                          v-model="model.admin_fees_rates[dateAsIndex]"
                          :min="0"
                          input-class="text-center"
                          :minFractionDigits="2"
                          suffix=" %"
                          fluid />
                        <i
                          @click="handleRepeatRight(model.admin_fees_rates, dateAsIndex, dates)"
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i>
                      </div>
                    </td>
                  </template>
                </tr>

                <tr
                  v-if="model.admin_fees_rates"
                  :data-repeater-style="true">
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
                          @click="handleRepeatRight(model.ecl_rates, dateAsIndex, dates)"
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

    <!-- start New Portfolio Funding Structure -->
    <div class="kt-portlet">
      <div class="kt-portlet__body">
        <div class="row">
          <div class="col-md-11">
            <div class="d-flex align-items-center">
              <h3 class="font-weight-bold form-label kt-subheader__title small-caps">
                Factoring New Portfolio Funding Structure
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
          v-show="showAndHide.fundingStructure"
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
                    <template v-if="!hideTablesDates.fundingTableRef.includes(Number(dateAsIndex))">
                      <th
                        class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                        <span class="text-left d-inline-block">{{ dateFormatted }}</span>
                      </th>
                    </template>
                    <!--  start Total Yr. 2026 for example -->
                    <th
                      v-if="
                        lastMonthIndexInEachYear.length > 1 &&
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex))
                      "
                      class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                      <div
                        class="d-flex align-items-center"
                        style="gap: 10px">
                        <span class="text-left d-inline-block"
                          >Total Yr. <br />
                          {{ getYearsFromDates[dateAsIndex] }}
                        </span>
                        <i
                          @click="hideOrExpandMyYear('fundingTableRef', Number(dateAsIndex))"
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
                <tr :data-repeater-style="true">
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
                    <td v-if="!hideTablesDates.fundingTableRef.includes(Number(dateAsIndex))">
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
                          @click="handleRepeatRight(model.equity_funding_rates, dateAsIndex, dates)"
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i>
                      </div>
                    </td>
                    <!--  start Total Yr. 2026 for example -->
                    <td
                      v-if="
                        lastMonthIndexInEachYear.length > 1 &&
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex))
                      ">
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
                      :maxFractionDigits="0"
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
                <tr :data-repeater-style="true">
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
                    <td v-if="!hideTablesDates.fundingTableRef.includes(Number(dateAsIndex))">
                      <div class="d-flex flex-column align-items-center">
                        <InputNumber
                          v-model="equityFundingValues[dateAsIndex]"
                          :min="0"
                          disabled
                          input-class="text-center"
                          :minFractionDigits="0"
                          :maxFractionDigits="0"
                          suffix=" EGP"
                          fluid />
                        <!-- <i
                          @click="
                            handleRepeatRight(
                              model.loan_amounts.sub_items[leasingRevenueStreamBreakdownItem.id],
                              Number(dateAsIndex),
                            )
                          "
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i> -->
                      </div>
                    </td>
                    <!--  start Total Yr. 2026 for example -->
                    <td
                      v-if="
                        lastMonthIndexInEachYear.length > 1 &&
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex))
                      ">
                      <InputNumber
                        :min="0"
                        input-class="text-center"
                        :modelValue="equityTotals.per_year[dateAsIndex]"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
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
                      :maxFractionDigits="0"
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
                <tr :data-repeater-style="true">
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
                    <td v-if="!hideTablesDates.fundingTableRef.includes(Number(dateAsIndex))">
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
                          @click="handleRepeatRight(model.equity_funding_rates, dateAsIndex, dates)"
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"
                          style="visibility: hidden"></i>
                      </div>
                    </td>
                    <!--  start Total Yr. 2026 for example -->
                    <td
                      v-if="
                        lastMonthIndexInEachYear.length > 1 &&
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex))
                      ">
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
                      :maxFractionDigits="0"
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
                <tr :data-repeater-style="true">
                  <td>
                    <div class="d-flex flex-column align-items-start mt-3">
                      <input
                        :value="'New Loans Funding Value'"
                        disabled=""
                        class="form-control min-width-hover-300 text-left"
                        type="text" />

                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>
                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <td v-if="!hideTablesDates.fundingTableRef.includes(Number(dateAsIndex))">
                      <div class="d-flex flex-column align-items-center mt-3">
                        <InputNumber
                          v-model="newLoansFundingValues[dateAsIndex]"
                          :min="0"
                          disabled
                          input-class="text-center"
                          :minFractionDigits="0"
                          :maxFractionDigits="0"
                          suffix=" EGP"
                          fluid />
                        <i
                          style="visibility: hidden"
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i>
                      </div>
                    </td>
                    <!--  start Total Yr. 2026 for example -->
                    <td
                      v-if="
                        lastMonthIndexInEachYear.length > 1 &&
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex))
                      ">
                      <InputNumber
                        :min="0"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
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
                    <div class="mt-3">
                      <InputNumber
                        :min="0"
                        v-model="newLoansTotals.total"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
                        suffix=" EGP"
                        disabled
                        fluid />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
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
        class="d-flex align-items-center justify-content-end"
        style="gap: 5px">
        <button
          v-if="!isLoading"
          @click="submitForm"
          :disabled="disableSubmitBtn"
          data-button-value="save"
          type="submit"
          class="btn text-white active-style save-form">
          <!--  -->
          <span
            v-if="disableSubmitBtn && model.submit_button == 'save'"
            class="spinner-border mr-2 spinner-border-sm mb-1"
            data-button-value="save"
            role="status"
            aria-hidden="true"></span>
          <span
            class="text-lg"
            data-button-value="save"
            v-html="disableSubmitBtn && model.submit_button == 'save' ? 'Saving...' : 'Save'">
          </span>
        </button>

        <button
          v-if="!isLoading"
          @click="submitForm"
          :disabled="disableSubmitBtn"
          data-button-value="save-and-go-to-next-value"
          type="submit"
          class="btn text-white active-style save-form">
          <!--  -->
          <span
            v-if="disableSubmitBtn && model.submit_button == 'save-and-go-to-next-value'"
            class="spinner-border mr-2 spinner-border-sm mb-1"
            data-button-value="save-and-go-to-next-value"
            role="status"
            aria-hidden="true"></span>
          <span
            class="text-lg"
            data-button-value="save-and-go-to-next-value"
            v-html="
              disableSubmitBtn && model.submit_button == 'save-and-go-to-next-value'
                ? 'Saving...'
                : 'Save & Go To Next'
            ">
          </span>
        </button>
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
  padding-right: 9px;
  padding-left: 9px;
  padding-top: 13px;
  padding-bottom: 13px;
}

.btn-danger-style i {
  padding-right: 0 !important;
  color: white !important;
  font-size: 0.9rem !important;
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
:deep(.p-inputnumber) {
  min-width: 75px !important;
}
</style>
