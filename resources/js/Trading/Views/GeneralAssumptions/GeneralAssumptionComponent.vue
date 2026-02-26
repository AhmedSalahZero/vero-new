<script setup lang="ts">
import axios from 'axios'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Swal from 'sweetalert2'
import { computed, onMounted, ref } from 'vue'
import Loading from '../../../components/Common/Loading.vue'
import Label from '../../../components/Form/Label.vue'
import Helper from '../../../Helpers/Helper'
// Tradings
const dates = ref<string[]>([])
const dispersementOfOptions = computed(() => {
	return Helper.dispersementOfOptions()
})
const toCoverCostOptions = computed(() => {
	return Helper.dispersementOfOptions()
})
const years = ref<string[]>([])
interface TableDates {
	bank_lending: number[]
	profit_assumptions: number[]
	min_cash_balance_policy: number[]
}

const hideTablesDates = ref<TableDates>({
	bank_lending: [],
	profit_assumptions: [],
	min_cash_balance_policy: [],
})
const getYearsFromDates = computed<Record<string, string>>(() => {
	let result: Record<string, string> = {}
	Object.keys(dates.value).forEach((dateAsIndex: string) => {
		result[dateAsIndex] = dates.value[dateAsIndex].split("'").pop()!
	})
	return result
})

const lastMonthIndexInEachYear = ref<number[]>([])

const hideOrExpandMyYear = (
	tableId: keyof typeof hideTablesDates.value,
	toDateAsIndex: number,
): void => {
	const index: number = lastMonthIndexInEachYear.value.indexOf(toDateAsIndex)
	const fromDateAsIndex = lastMonthIndexInEachYear.value[index - 1] + 1 || 0
	const isCurrentDateExistInArray: boolean = hideTablesDates.value[tableId].includes(toDateAsIndex)
	for (let i: number = fromDateAsIndex; i <= toDateAsIndex; i++) {
		if (isCurrentDateExistInArray) {
			hideTablesDates.value[tableId] = hideTablesDates.value[tableId].filter(
				(i: number) => !(i >= fromDateAsIndex && i <= toDateAsIndex),
			)
		} else {
			hideTablesDates.value[tableId].push(i)
		}
	}
}

const disableSubmitBtn = ref<boolean>(false)
const isLoading = ref<boolean>(true)
const submitUrl = ref<string>('')

const model = ref<{ [key: string]: any }>({
	bank_lending_margin_rates: {},
	bank_lending: {},
	employee_profit_share_rates: {},
	border_of_directors_profit_share_rates: {},
	shareholders_first_dividend_portions: {},
	shareholders_dividend_payout_ratios: {},
	//	shareholders_dividend_in_cash_or_shares: {},
})
// const getCbeLendingCorridorRates = computed<objectAsStringAndNumberInterface>(() => {
//   Object.keys(dates.value).forEach((dateAsIndex: string) => {
//     let cbeBaseRate = Number(model.value.cbe_base_lending_corridor_rates[dateAsIndex] || 0)
//     let cbeCorridorChangeRate = Number(model.value.cbe_corridor_changes_rates[dateAsIndex] || 0)
//     model.value.cbe_lending_corridor_rates[dateAsIndex] = cbeBaseRate + cbeCorridorChangeRate
//   })
//   return model.value.cbe_lending_corridor_rates
// })
interface showAndHidType {
	bank_lending: boolean
	profit_assumptions: boolean
	reserve_assumption: boolean
	min_cash_balance_policy: boolean
}
const showAndHide = ref<showAndHidType>({
	bank_lending: true,
	profit_assumptions: true,
	reserve_assumption: true,
	min_cash_balance_policy: true,
})

// methods
const logger = (variable: any) => {
	console.log(variable, 'end')
	return ''
}

const allTablesTotals = computed(() => {
	return {
		minCashBalance: Helper.calculateTableTotals(
			lastMonthIndexInEachYear,
			model.value?.min_cash_balances,
			{ type: 'simple' },
		),
	}
})
const minCashBalanceTotal = computed(() => allTablesTotals.value.minCashBalance)

const handleRepeatRight = (items: string[], dateAsIndex: number, dates: string[]) => {
	Helper.repeatRight(items, dateAsIndex, dates)
}

const getModelData = () => {
	const body = document.querySelector('body') as HTMLBodyElement
	const csrfToken = body.dataset.token
	const baseUrl = body.dataset.baseUrl
	const companyId = body.dataset.currentCompanyId
	const studyId = body.dataset.studyId
	const lang = body.dataset.lang

	const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/property-managements/study/${studyId}/general-and-reserve-assumption-old-data`
	axios
		.get(fetchOldDataUrl, {
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				Accept: 'application/json',
			},
		})
		.then((response) => {
			dates.value = response.data.dates
			years.value = response.data.years
			lastMonthIndexInEachYear.value = response.data.lastMonthIndexInEachYear
			model.value = response.data.model
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

const submitForm = (e: Event) => {
	const target = e.target as HTMLButtonElement
	if (model.value) {
		model.value.submit_button = target.getAttribute('data-button-value')
	}
	disableSubmitBtn.value = true
	const body = document.querySelector('body') as HTMLBodyElement
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
			}).then((res: Object) => {
				disableSubmitBtn.value = false
				if (response.data.redirectTo) {
					window.location.href = response.data.redirectTo
				}
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
		<div class="col-md-12" v-if="isLoading">
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
		<!-- start Profit Distribution Assumption  -->
		<div class="kt-portlet">
			<div class="kt-portlet__body">
				<div class="row">
					<div class="col-md-11">
						<div class="d-flex align-items-center">
							<h3 class="font-weight-bold form-label kt-subheader__title small-caps"> Reserve Assumption
							</h3>
						</div>
					</div>
					<div class="col-md-1">
						<div class="d-flex justify-content-end">
							<div @click="showAndHide.reserve_assumption = !showAndHide.reserve_assumption"
								class="btn show-hide-style"> Show/Hide </div>
						</div>
					</div>
					<div class="col-md-12">
						<hr style="background-color: lightgray" />
					</div>
				</div>
				<div v-show="showAndHide.reserve_assumption" class="row mt-4">
					<div class="col-md-3">
						<Label> Legal Reserve Rate % </Label>
						<InputNumber v-model="model.legal_reserve_rate" :min="0" :max="100" :minFractionDigits="2"
							:maxFractionDigits="5" suffix=" %" fluid />
					</div>
					<div class="col-md-3">
						<Label> Max Legal Reserve Rate % ( From Paid Up Capital) </Label>
						<InputNumber v-model="model.max_legal_reserve_rate" :min="0" :max="100" :minFractionDigits="2"
							:maxFractionDigits="5" suffix=" %" fluid />
					</div>
					<!-- <div class="col-md-3">
						<Label> Financial Regularity Authority Reserve (FRA %) </Label>
						<InputNumber v-model="model.financial_regulatory_authority_rate" :min="0" :max="100"
							:minFractionDigits="2" :maxFractionDigits="5" suffix=" %" fluid />
					</div>
					<div class="col-md-3">
						<Label> Max Financial Regularity Authority Reserve (FRA %) </Label>
						<InputNumber v-model="model.max_financial_regulatory_authority_rate" :min="0" :max="100"
							:minFractionDigits="2" :maxFractionDigits="5" suffix=" %" fluid />
					</div> -->
				</div>
			</div>
		</div>
		<!-- end Profit Distribution Assumption -->
		<!-- start Profit Distribution Assumption  -->
		<div class="kt-portlet">
			<div class="kt-portlet__body">
				<div class="row">
					<div class="col-md-11">
						<div class="d-flex align-items-center">
							<h3 class="font-weight-bold form-label kt-subheader__title small-caps"> Profit Distribution
								Assumption </h3>
						</div>
					</div>
					<div class="col-md-1">
						<div class="d-flex justify-content-end">
							<div @click="showAndHide.profit_assumptions = !showAndHide.profit_assumptions"
								class="btn show-hide-style"> Show/Hide </div>
						</div>
					</div>
					<div class="col-md-12">
						<hr style="background-color: lightgray" />
					</div>
				</div>
				<div v-show="showAndHide.profit_assumptions" class="row mt-4">
					<div class="col-md-12 overflow-scroll">
						<table class="table">
							<thead>
								<tr>
									<th
										class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
										Item </th>
									<template v-for="(yearFormatted, yearIndex) in years" :key="yearIndex">
										<!-- <template> -->
										<th
											class="form-label expandable-percentage-input font-weight-bold text-center align-middle header-border-down">
											<span class="text-center d-inline-block">{{ yearFormatted }} <br /> </span>
										</th>
										<!-- </template> --> <!--  start Total Yr. 2026 for example -->
										<!-- <th
                      v-if="
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
                        lastMonthIndexInEachYear.length > 1
                      "
                      class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                      <div
                        class="d-flex justify-content-center align-items-center"
                        style="gap: 10px">
                        <span class="text-center d-inline-block">
                          Yr. <br />
                          {{ getYearsFromDates[dateAsIndex] }}
                        </span>
                        <i
                          @click="hideOrExpandMyYear('profit_assumptions', dateAsIndex)"
                          :title="'Expand / Collapse'"
                          class="cursor-pointer fa fa-expand-arrows-alt text-primary exclude-icon"></i>
                      </div>
                    </th> -->
										<!--  end Total Yr. 2026 for example -->
									</template>
									<!-- start total of all years for the current row -->
									<!-- <th
                    class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                    <div
                      class="d-flex flex-column align-items-center"
                      style="gap: 10px">
                      <span class="">Total <br /> </span>
              
                    </div>
                  </th> -->
									<!-- end total of all years for the current row -->
								</tr>
							</thead>
							<tbody>
								<!-- start row -->
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'Employee Profit Share Rate'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(yearFormatted, yearIndex) in years" :key="yearIndex">
										<td>
											<div
												class="d-flex flex-column mx-auto justify-content-center min-w-percentage align-items-center">
												<InputNumber v-model="model.employee_profit_share_rates[yearIndex]"
													:min="0" :max="100" input-class="text-center min-w-percentage"
													:minFractionDigits="2" :maxFractionDigits="5" suffix=" %" fluid />
												<i @click="
													handleRepeatRight(model.employee_profit_share_rates, yearIndex, years)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
										<!--  start Total Yr. 2026 for example -->
										<!-- <td
                      v-if="
                        lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
                        lastMonthIndexInEachYear.length > 1
                      ">
                      <InputText
                        style="text-align: center"
                        :value="'-'"
                        disabled
                        fluid />
                      <i
                        style="visibility: hidden"
                        class="fa fa-ellipsis-h"></i>
                    </td> -->
										<!--  end Total Yr. 2026 for example -->
									</template>
								</tr>
								<!-- end row -->
								<!-- start row -->
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'Board Of Directors Profit Share Rates'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(yearFormatted, yearIndex) in years" :key="yearIndex">
										<td>
											<div
												class="d-flex flex-column mx-auto justify-content-center min-w-percentage align-items-center">
												<InputNumber
													v-model="model.border_of_directors_profit_share_rates[yearIndex]"
													:min="0" :max="100" input-class="text-center min-w-percentage"
													:minFractionDigits="2" :maxFractionDigits="5" suffix=" %" fluid />
												<i @click="
													handleRepeatRight(
														model.border_of_directors_profit_share_rates,
														yearIndex,
														years,
													)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
									</template>
								</tr>
								<!-- end row -->
								<!-- start row -->
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'Shareholders First Dividend Portion'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(yearFormatted, yearIndex) in years" :key="yearIndex">
										<td>
											<div
												class="d-flex flex-column mx-auto justify-content-center min-w-percentage align-items-center">
												<InputNumber
													v-model="model.shareholders_first_dividend_portions[yearIndex]"
													:min="0" :max="100" input-class="text-center min-w-percentage"
													:minFractionDigits="2" :maxFractionDigits="5" suffix=" %" fluid />
												<i @click="
													handleRepeatRight(
														model.shareholders_first_dividend_portions,
														yearIndex,
														years,
													)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
									</template>
								</tr>
								<!-- end row -->
								<!-- start row -->
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'Shareholders Dividend Payout Ratio %'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(yearFormatted, yearIndex) in years" :key="yearIndex">
										<td>
											<div
												class="d-flex flex-column mx-auto justify-content-center min-w-percentage align-items-center">
												<InputNumber
													v-model="model.shareholders_dividend_payout_ratios[yearIndex]"
													:min="0" :max="100" input-class="text-center min-w-percentage"
													:minFractionDigits="2" :maxFractionDigits="5" suffix=" %" fluid />
												<i @click="
													handleRepeatRight(
														model.shareholders_dividend_payout_ratios,
														yearIndex,
														years,
													)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
									</template>
								</tr>
								<!-- end row -->
								<!-- start row -->
								<!-- <tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'Shareholders Dividend (In Cash Or Shares)'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(yearFormatted, yearIndex) in years" :key="yearIndex">
										<td>
											<div
												class="d-flex flex-column mx-auto justify-content-center align-items-center">
												<Select filter
													v-model="model.shareholders_dividend_in_cash_or_shares[yearIndex]"
													:options="[
														{ id: 'in_cash', title: 'In Cash' },
														{ id: 'in_shares', title: 'In Shares' },
													]" optionLabel="title" optionValue="id" placeholder="" checkmark :highlightOnSelect="false" :pt="{
														label: {
															class:
																'd-flex font-weight-normal justify-content-center align-items-center',
														},
													}" class="w-200px" />
												<i @click="
													handleRepeatRight(
														model.shareholders_dividend_in_cash_or_shares,
														yearIndex,
														years,
													)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
									</template>
								</tr> -->
								<!-- end row -->
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<!-- end Profit Distribution Assumption -->
		<!-- start CBE Corridor & Banks Lending Margins & Interest Rates  -->
		<div class="kt-portlet">
			<div class="kt-portlet__body">
				<div class="row">
					<div class="col-md-11">
						<div class="d-flex align-items-center">
							<h3 class="font-weight-bold form-label kt-subheader__title small-caps"> CBE Corridor & Banks
								Lending Margins & Interest Rates </h3>
						</div>
					</div>
					<div class="col-md-1">
						<div class="d-flex justify-content-end">
							<div @click="showAndHide.bank_lending = !showAndHide.bank_lending"
								class="btn show-hide-style"> Show/Hide </div>
						</div>
					</div>
					<div class="col-md-12">
						<hr style="background-color: lightgray" />
					</div>
				</div>
				<div v-show="showAndHide.bank_lending" class="row mt-4">
					<div class="col-md-12 overflow-scroll">
						<table class="table">
							<thead>
								<tr>
									<th
										class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
										Item </th>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<template v-if="!hideTablesDates.bank_lending.includes(Number(dateAsIndex))">
											<th
												class="form-label expandable-percentage-input font-weight-bold text-center align-middle header-border-down">
												<span class="text-center d-inline-block">{{ dateFormatted }} <br />
												</span>
											</th>
										</template>
										<!--  start Total Yr. 2026 for example -->
										<th v-if="
											lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
											lastMonthIndexInEachYear.length > 1
										" class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
											<div class="d-flex justify-content-center align-items-center"
												style="gap: 10px">
												<span class="text-center d-inline-block"> Yr. <br />
													{{ getYearsFromDates[dateAsIndex] }}
												</span>
												<i @click="hideOrExpandMyYear('bank_lending', Number(dateAsIndex))"
													:title="'Expand / Collapse'"
													class="cursor-pointer fa fa-expand-arrows-alt text-primary exclude-icon"></i>
											</div>
										</th>
										<!--  end Total Yr. 2026 for example -->
									</template>
									<!-- start total of all years for the current row -->
									<!-- <th
                    class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                    <div
                      class="d-flex flex-column align-items-center"
                      style="gap: 10px">
                      <span class="">Total <br /> </span>
              
                    </div>
                  </th> -->
									<!-- end total of all years for the current row -->
								</tr>
							</thead>
							<tbody>
								<!-- <tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'CBE Lending Corridor %'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td v-if="!hideTablesDates.bank_lending.includes(Number(dateAsIndex))">
									
											<div class="d-flex flex-column align-items-center">
												<InputNumber
													v-model="model.cbe_base_lending_corridor_rates[dateAsIndex]"
													:min="0" :max="100" input-class="text-center" :minFractionDigits="2"
													:maxFractionDigits="5" suffix=" %" fluid />
												<i @click="
													handleRepeatRight(
														model.cbe_base_lending_corridor_rates,
														dateAsIndex,
														dates,
													)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
										
										<td v-if="
											lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
											lastMonthIndexInEachYear.length > 1
										">
											<InputText style="text-align: center" :value="'-'" disabled fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</td>
										
									</template>
								</tr>
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'CBE Corridor Changes %'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td v-if="!hideTablesDates.bank_lending.includes(Number(dateAsIndex))">
						
											<div class="d-flex flex-column align-items-center">
												<InputNumber v-model="model.cbe_corridor_changes_rates[dateAsIndex]"
													:min="-100" :max="100" input-class="text-center"
													:minFractionDigits="2" :maxFractionDigits="5" suffix=" %" fluid />
												<i @click="
													handleRepeatRight(model.cbe_corridor_changes_rates, dateAsIndex, dates)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
										
										<td v-if="
											lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
											lastMonthIndexInEachYear.length > 1
										">
											<InputText style="text-align: center" :value="'-'" disabled fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</td>
								
									</template>
								</tr> -->
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'CBE Lending Corridor %'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td v-if="!hideTablesDates.bank_lending.includes(Number(dateAsIndex))">
											<div class="d-flex flex-column align-items-center">
												<InputNumber v-model="model.cbe_lending_corridor_rates[dateAsIndex]"
													:min="0" :max="100" input-class="text-center" :minFractionDigits="2"
													:maxFractionDigits="5" suffix=" %" fluid />
												<i @click="
													handleRepeatRight(
														model.cbe_lending_corridor_rates,
														dateAsIndex,
														dates,
													)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
										<!--  start Total Yr. 2026 for example -->
										<td v-if="
											lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
											lastMonthIndexInEachYear.length > 1
										">
											<InputText style="text-align: center" :value="'-'" disabled fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</td>
										<!--  end Total Yr. 2026 for example -->
									</template>
								</tr>
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'MTLs Banks Lending Margin %'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td v-if="!hideTablesDates.bank_lending.includes(Number(dateAsIndex))">
											<div class="d-flex flex-column align-items-center">
												<InputNumber v-model="model.bank_lending_margin_rates[dateAsIndex]"
													:min="0" :max="100" input-class="text-center" :minFractionDigits="2"
													:maxFractionDigits="5" suffix=" %" fluid />
												<i @click="
													handleRepeatRight(
														model.bank_lending_margin_rates,
														dateAsIndex,
														dates,
													)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
										<!--  start Total Yr. 2026 for example -->
										<td v-if="
											lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
											lastMonthIndexInEachYear.length > 1
										">
											<InputText style="text-align: center" :value="'-'" disabled fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</td>
										<!--  end Total Yr. 2026 for example -->
									</template>
								</tr>
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'ODAs Banks Lending Margin Rate %'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td v-if="!hideTablesDates.bank_lending.includes(Number(dateAsIndex)) && model">
											<div class="d-flex flex-column align-items-center">
												<InputNumber v-model="model.odas_bank_lending_margin_rates[dateAsIndex]"
													:min="0" :max="100" input-class="text-center" :minFractionDigits="2"
													:maxFractionDigits="5" suffix=" %" fluid />
												<i @click="
													handleRepeatRight(
														model.odas_bank_lending_margin_rates,
														dateAsIndex,
														dates,
													)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
										<!--  start Total Yr. 2026 for example -->
										<td v-if="
											lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
											lastMonthIndexInEachYear.length > 1
										">
											<InputText style="text-align: center" :value="'-'" disabled fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</td>
										<!--  end Total Yr. 2026 for example -->
									</template>
								</tr>
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-start">
											<input :value="'Credit Interest Rate For Surplus Cash %'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td v-if="!hideTablesDates.bank_lending.includes(Number(dateAsIndex))">
											<div class="d-flex flex-column align-items-center">
												<InputNumber
													v-model="model.credit_interest_rate_for_surplus_cash[dateAsIndex]"
													:min="0" :max="100" input-class="text-center" :minFractionDigits="2"
													:maxFractionDigits="5" suffix=" %" fluid />
												<i @click="
													handleRepeatRight(
														model.credit_interest_rate_for_surplus_cash,
														dateAsIndex,
														dates,
													)
													" class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"></i>
											</div>
										</td>
										<!--  start Total Yr. 2026 for example -->
										<td v-if="
											lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
											lastMonthIndexInEachYear.length > 1
										">
											<InputText style="text-align: center" :value="'-'" disabled fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</td>
										<!--  end Total Yr. 2026 for example -->
									</template>
								</tr>
								<!-- end total row -->
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<!-- end CBE Corridor & Banks Lending Margins & Interest Rates -->
		<!-- start Minimum Cash Balance  -->
		<div class="kt-portlet">
			<div class="kt-portlet__body">
				<div class="row">
					<div class="col-md-11">
						<div class="d-flex align-items-center">
							<h3 class="font-weight-bold form-label kt-subheader__title small-caps"> Minimum Cash Balance
								Policy </h3>
						</div>
					</div>
					<div class="col-md-1">
						<div class="d-flex justify-content-end">
							<div @click="showAndHide.min_cash_balance_policy = !showAndHide.min_cash_balance_policy"
								class="btn show-hide-style"> Show/Hide </div>
						</div>
					</div>
					<div class="col-md-12">
						<hr style="background-color: lightgray" />
					</div>
				</div>
				<div v-show="showAndHide.min_cash_balance_policy" class="row mt-4">
					<div class="col-md-12 overflow-scroll">
						<table class="table">
							<thead>
								<tr>
									<th
										class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
										Item </th>
									<th
										class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
										OF </th>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<template
											v-if="!hideTablesDates.min_cash_balance_policy.includes(Number(dateAsIndex))">
											<th
												class="form-label expandable-percentage-input font-weight-bold text-center align-middle header-border-down">
												<span class="text-center d-inline-block">{{ dateFormatted }} <br />
												</span>
											</th>
										</template>
										<!--  start Total Yr. 2026 for example -->
										<th v-if="
											lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
											lastMonthIndexInEachYear.length > 1
										" class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
											<div class="d-flex justify-content-center align-items-center"
												style="gap: 10px">
												<span class="text-center d-inline-block"> Yr. <br />
													{{ getYearsFromDates[dateAsIndex] }}
												</span>
												<i @click="
													hideOrExpandMyYear('min_cash_balance_policy', Number(dateAsIndex))
													" :title="'Expand / Collapse'" class="cursor-pointer fa fa-expand-arrows-alt text-primary exclude-icon"></i>
											</div>
										</th>
										<!--  end Total Yr. 2026 for example -->
									</template>
									<!-- start total of all years for the current row -->
									<!-- <th
                    class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                    <div
                      class="d-flex flex-column align-items-center"
                      style="gap: 10px">
                      <span class="">Total <br /> </span>
              
                    </div>
                  </th> -->
									<!-- end total of all years for the current row -->
								</tr>
							</thead>
							<tbody>
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-center">
											<input :value="'As Percentage Of All Cost & Expenses'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<td>
										<div class="d-flex flex-column align-items-center">
											<Select filter v-model="model.to_cover_cost" :options="toCoverCostOptions"
												optionLabel="title" optionValue="id" placeholder="" checkmark
												:highlightOnSelect="false" :pt="{
													label: {
														class:
															'd-flex font-weight-normal justify-content-center align-items-center',
													},
												}" class="w-200px" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td
											v-if="!hideTablesDates.min_cash_balance_policy.includes(Number(dateAsIndex))">
											<div class="d-flex mx-auto flex-column min-w-percentage align-items-center">
												<InputNumber v-model="model.to_cover_cost_rates[dateAsIndex]" :min="0"
													:max="100" input-class="text-center" :minFractionDigits="2"
													:maxFractionDigits="5" suffix=" %" fluid />
												<i @click="handleRepeatRight(model.to_cover_cost_rates, dateAsIndex, dates)"
													class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
													title="Repeat Right"></i>
											</div>
										</td>
										<!--  start Total Yr. 2026 for example -->
										<td v-if="
											lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
											lastMonthIndexInEachYear.length > 1
										">
											<InputText style="text-align: center" :value="'-'" disabled fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</td>
										<!--  end Total Yr. 2026 for example -->
									</template>
								</tr>
								<tr :data-repeater-style="true">
									<td>
										<div class="d-flex flex-column align-items-center">
											<input :value="'Fixed Amount'" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<td>
										<div class="d-flex flex-column align-items-center"> - <!-- <Select
                        filter
                        v-model="model.to_cover_cost"
                        :options="toCoverCostOptions"
                        optionLabel="title"
                        optionValue="id"
                        placeholder=""
                        checkmark
                        :highlightOnSelect="false"
                        :pt="{
                          label: {
                            class:
                              'd-flex font-weight-normal justify-content-center align-items-center',
                          },
                        }"
                        class="w-200px" /> -->
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td
											v-if="!hideTablesDates.min_cash_balance_policy.includes(Number(dateAsIndex))">
											<div class="d-flex flex-column max-w-175 align-items-center">
												<InputNumber v-model="model.min_cash_balances[dateAsIndex]" :min="0"
													input-class="text-center" :minFractionDigits="0"
													:maxFractionDigits="0" suffix=" EGP" fluid />
												<i @click="handleRepeatRight(model.min_cash_balances, dateAsIndex, dates)"
													class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
													title="Repeat Right"></i>
											</div>
										</td>
										<!--  start Total Yr. 2026 for example -->
										<td v-if="
											lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
											lastMonthIndexInEachYear.length > 1
										">
											<InputText :value="minCashBalanceTotal.subRowTotals.per_year[dateAsIndex]"
												class="text-center" disabled fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</td>
										<!--  end Total Yr. 2026 for example -->
									</template>
								</tr>
								<!-- end total row -->
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<!-- end Minimum Cash Balance -->
		<div class="col-md-12">
			<div class="d-flex align-items-center justify-content-end" style="gap: 5px">
				<button v-if="!isLoading" @click="submitForm" :disabled="disableSubmitBtn" data-button-value="save"
					type="submit" class="btn text-white active-style save-form">
					<!--  -->
					<span v-if="disableSubmitBtn && model.submit_button == 'save'"
						class="spinner-border mr-2 spinner-border-sm mb-1" data-button-value="save" role="status"
						aria-hidden="true"></span>
					<span class="text-lg" data-button-value="save"
						v-html="disableSubmitBtn && model.submit_button == 'save' ? 'Saving...' : 'Save'">
					</span>
				</button>
				<button v-if="!isLoading" @click="submitForm" :disabled="disableSubmitBtn"
					data-button-value="save-and-go-to-next-value" type="submit"
					class="btn text-white active-style save-form">
					<!--  -->
					<span v-if="disableSubmitBtn && model.submit_button == 'save-and-go-to-next-value'"
						class="spinner-border mr-2 spinner-border-sm mb-1" data-button-value="save-and-go-to-next-value"
						role="status" aria-hidden="true"></span>
					<span class="text-lg" data-button-value="save-and-go-to-next-value" v-html="disableSubmitBtn && model.submit_button == 'save-and-go-to-next-value'
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

.w-200px {
	width: 200px !important;
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
