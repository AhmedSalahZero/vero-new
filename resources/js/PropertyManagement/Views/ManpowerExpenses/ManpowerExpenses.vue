<script setup>
import axios from 'axios'
import InputNumber from 'primevue/inputnumber'
import { computed, onMounted, ref } from 'vue'
import Loading from '../../../components/Common/Loading.vue'
import Helper from '../../../Helpers/Helper'
import { useDateFormatter } from '../../composables/useDateFormatter'
import { useTableExpender } from '../../composables/useTableExpender'

// properties
const dates = ref([])

const hideTablesDates = ref({
  departments: [],
})
const { yearsFromDates: getYearsFromDates } = useDateFormatter(dates)

const lastMonthIndexInEachYear = ref([])

const { hideOrExpandMyYearWithIndex } = useTableExpender(lastMonthIndexInEachYear, hideTablesDates)

const disableSubmitBtn = ref(false)
const isLoading = ref(true)
const submitUrl = ref(null)

const model = ref(null)
const showAndHide = ref({
  departments: [],
})
const allTablesTotals = computed(() => {
  return {
    // حالة 1: array of objects مع nested key
    manpowerHiringTotals: Helper.calculateTableTotals(
      lastMonthIndexInEachYear,
      model.value?.manpowers,
      {
        nestedKey: 'hiring_counts',
      },
    ),
  }
})
const manpowerHiringTotals = computed(() => allTablesTotals.value.manpowerHiringTotals)

// methods
const logger = (variable) => {
  console.log(variable, 'end')
  return ''
}

const handleRepeatRight = (items, dateAsIndex, dates) => {
  Helper.repeatRight(items, dateAsIndex, dates)
}
const departmentWithPositions = ref([])

const getModelData = () => {
  const body = document.querySelector('body')
  const csrfToken = body.dataset.token
  const baseUrl = body.dataset.baseUrl
  const companyId = body.dataset.currentCompanyId
  const studyId = body.dataset.studyId
  const lang = body.dataset.lang

  const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/property-managements/study/${studyId}/manpower-expenses-fetch-old-data`
  axios
    .get(fetchOldDataUrl, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
    })
    .then((response) => {
      dates.value = response.data.dates
      lastMonthIndexInEachYear.value = response.data.lastMonthIndexInEachYear
      model.value = response.data.model
      departmentWithPositions.value = response.data.departmentWithPositions
      Object.keys(response.data.departmentWithPositions).forEach((key) => {
        hideTablesDates.value.departments[key] = []
        showAndHide.value.departments[key] = true
      })

      submitUrl.value = response.data.submitUrl
      isLoading.value = false
    })
    .catch((error) => {
      console.log(error)
      isLoading.value = false
      const errorMessage = error.response?.data?.message || 'An error occurred' + error
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
    <!-- start manpower expenses  -->
    <div
      class="kt-portlet"
      v-for="(department, index) in departmentWithPositions"
      :key="index">
      <div class="kt-portlet__body">
        <div class="row">
          <div class="col-md-11">
            <div class="d-flex align-items-center">
              <h3 class="font-weight-bold form-label kt-subheader__title small-caps">
                {{ department.name }} - {{ department.expense_name }}
              </h3>
            </div>
          </div>
          <div class="col-md-1">
            <div class="d-flex justify-content-end">
              <div
                @click="showAndHide.departments[index] = !showAndHide.departments[index]"
                class="btn show-hide-style">
                Show/Hide
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <hr style="background-color: lightgray" />
          </div>
        </div>

        <!-- <pre>
			{{ department.id }}
		</pre
        > -->
        <div
          v-show="showAndHide.departments[index]"
          class="row mt-4">
          <div class="col-md-12 overflow-scroll">
            <table class="table">
              <thead>
                <tr>
                  <th
                    class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
                    Position
                  </th>

                  <th
                    class="form-label expandable-percentage-input font-weight-bold text-center align-middle header-border-down">
                    <span class="text-center d-inline-block"
                      >Existing <br />
                      Count
                    </span>
                  </th>

                  <th
                    class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                    <span class="text-center d-inline-block"
                      >Monthly Net <br />
                      Salary
                    </span>
                  </th>

                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <template
                      v-if="!hideTablesDates.departments[index].includes(Number(dateAsIndex))">
                      <th
                        class="form-label expandable-percentage-input font-weight-bold text-center align-middle header-border-down">
                        <span class="text-center d-inline-block"
                          >{{ dateFormatted }} <br />
                          Hiring#
                        </span>
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
                        class="d-flex justify-content-center align-items-center"
                        style="gap: 10px">
                        <span class="text-center d-inline-block"
                          >Total Yr. <br />
                          {{ getYearsFromDates[dateAsIndex] }}
                        </span>
                        <i
                          @click="
                            hideOrExpandMyYearWithIndex('departments', Number(dateAsIndex), index)
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
              <tbody v-if="!isLoading">
                <tr
                  v-for="(position, positionIndex) in department.positions"
                  :key="positionIndex"
                  :data-repeater-style="positionIndex + 1">
                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <input
                        :value="position.name"
                        disabled=""
                        class="form-control min-width-300 text-left mt-2"
                        type="text" />

                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>

                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <InputNumber
                        v-model="model.manpowers[position.id].existing_count"
                        :min="0"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
                        suffix=""
                        fluid />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>

                  <td>
                    <div class="d-flex flex-column align-items-start">
                      <InputNumber
                        v-model="model.manpowers[position.id].monthly_net_salary"
                        :min="0"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
                        suffix=" EGP"
                        fluid />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </div>
                  </td>

                  <template
                    v-for="(dateFormatted, dateAsIndex) in dates"
                    :key="dateAsIndex">
                    <td v-if="!hideTablesDates.departments[index].includes(Number(dateAsIndex))">
                      <!-- {{ logRender(leasingRevenueStreamBreakdownItem.id, dateAsIndex) }} -->
                      <div class="d-flex flex-column align-items-center">
                        <InputNumber
                          v-model="model.manpowers[position.id].hiring_counts[dateAsIndex]"
                          :min="0"
                          input-class="text-center"
                          :minFractionDigits="0"
                          :maxFractionDigits="0"
                          suffix=""
                          fluid />
                        <i
                          @click="
                            handleRepeatRight(
                              model.manpowers[position.id].hiring_counts,
                              dateAsIndex,
                              dates,
                            )
                          "
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i>
                      </div>
                    </td>
                    <!--  start Total Yr. 2026 for example -->
                    <!-- {{ logger(manpowerHiringTotals.subRowTotals) }} -->
                    <td
                      v-if="
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
                        lastMonthIndexInEachYear.length > 1
                      ">
                      <InputNumber
                        v-model="
                          manpowerHiringTotals.subRowTotals[position.id]['per_year'][dateAsIndex]
                        "
                        :min="0"
                        input-class="text-center"
                        :minFractionDigits="0"
                        :maxFractionDigits="2"
                        suffix=""
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
                      v-model="manpowerHiringTotals.subRowTotals[position.id]['total']"
                      :min="0"
                      input-class="text-center"
                      :minFractionDigits="0"
                      :maxFractionDigits="2"
                      suffix=""
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
    <!-- end manpower expenses -->

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
