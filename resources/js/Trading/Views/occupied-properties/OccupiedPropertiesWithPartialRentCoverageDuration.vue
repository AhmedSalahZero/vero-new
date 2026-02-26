<script setup lang="ts">
import axios from 'axios'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Swal from 'sweetalert2'
import { computed, onMounted, ref } from 'vue'
import Loading from '../../../components/Common/Loading.vue'
import Helper from '../../../Helpers/Helper'
import { useTableExpender } from '../../composables/useTableExpender'
import Label from '../../../components/Form/Label.vue'
// Tradings
const dates = ref<string[]>([])

const isActiveModal = ref<object | null>(null)
const years = ref<string[]>([])
interface TableDates {
	rent_revenues: number[]
}

const hideTablesDates = ref<TableDates>({
	rent_revenues: [],
})
const getYearsFromDates = computed<Record<string, string>>(() => {
	let result: Record<string, string> = {}
	Object.keys(dates.value).forEach((dateAsIndex: string) => {
		result[dateAsIndex] = dates.value[dateAsIndex].split("'").pop()!
	})
	return result
})

const lastMonthIndexInEachYear = ref<number[]>([])
const { hideOrExpandMyYear } = useTableExpender(lastMonthIndexInEachYear, hideTablesDates)


const disableSubmitBtn = ref<boolean>(false)
const isLoading = ref<boolean>(true)
const submitUrl = ref<string>('')

const model = ref<{ [key: string]: any }>({


})

interface showAndHidType {

	rent_revenues: boolean
}
const showAndHide = ref<showAndHidType>({

	rent_revenues: true,
})

// methods
const logger = (variable: any) => {
	console.log(variable, 'end')
	return ''
}



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

	const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/property-managements/study/${studyId}/occupied-Tradings-with-partial-rent-coverage-duration-old-data`
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
		<!-- start Minimum Cash Balance  -->
		<div class="kt-portlet">
			<div class="kt-portlet__body">
				<div class="row">
					<div class="col-md-11">
						<div class="d-flex align-items-center">
							<h3 class="font-weight-bold form-label kt-subheader__title small-caps">
								{{ $t('Partial Rent Coverage Duration & Vacant Tradings') }}
							</h3>
						</div>
					</div>
					<div class="col-md-1">
						<div class="d-flex justify-content-end">
							<div @click="showAndHide.rent_revenues = !showAndHide.rent_revenues"
								class="btn show-hide-style"> {{ $t('Show/Hide') }} </div>
						</div>
					</div>
					<div class="col-md-12">
						<hr style="background-color: lightgray" />
					</div>
				</div>
				<div v-show="showAndHide.rent_revenues" class="row mt-4">
					<div class="col-md-12 overflow-scroll">
						<table class="table">
							<thead>
								<tr>
									<th
										class="form-label fixed-column position-sticky font-weight-bold text-center align-middle header-border-down ">
										{{ $t('Name') }}
									</th>
									<th
										class="form-label font-weight-bold text-center align-middle header-border-down ">
										{{ $t('Category') }}
									</th>
									<th v-html="$t('Type')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
									<th v-html="$t('Tenant <br> Name')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
									<th v-html="$t('Contract <br>End Date')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
									<th v-html="$t('Monthly <br> Rent')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
									<th
										class="form-label font-weight-bold text-center align-middle header-border-down ">
										{{ $t('Financial') }}
									</th>
									<th
										class="form-label font-weight-bold text-center align-middle header-border-down ">
										{{ $t('Proposed Action') }}
									</th>
									<!-- <th v-html="$t('Renovate <br> Start Date')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th> -->
									<th v-html="$t('Renovate <br> Duration')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
									<th v-html="$t('Renovate <br> Cost')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
									<th v-html="$t('Renewal <br> Increase %')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
									<th v-html="$t('New <br> Rent')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
									<th v-html="$t('Renewal <br> Duration')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
									<th v-html="$t('Collection <br> Interval')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
									<th v-html="$t('Renewal Annual <br> Increase %')"
										class="form-label font-weight-bold text-center align-middle header-border-down ">
									</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(property, propertyIndex) in model.Tradings" :data-repeater-style="true">
									<td class="fixed-column">
										<div class="d-flex  flex-column align-items-center min-w-300">
											<input :value="property.name" :disabled="true"
												class="form-control text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<td>
										<div class="d-flex flex-column align-items-center min-w-200">
											<input :value="property.category_name" :disabled="true"
												class="form-control  text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<td>
										<div class="d-flex flex-column align-items-center min-w-200">
											<input :value="property.type_name" :disabled="true"
												class="form-control  text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<td>
										<div class="d-flex flex-column align-items-center min-w-250">
											<input :value="property.contract.tenant_name" :disabled="true"
												class="form-control text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<!-- <td>
										<div class="d-flex flex-column align-items-center">
											<input :value="property.contract.contract_start_date" :disabled="true"
												class="form-control  text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td> -->
									<td>
										<div class="d-flex flex-column align-items-center min-w-120">
											<input :value="property.contract.contract_end_date" :disabled="true"
												class="form-control  text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<td>
										<div class="d-flex w-auto flex-column align-items-center min-w-100">
											<input :value="Helper.number_format(property.contract.monthly_rent)"
												:disabled="true" class="form-control text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<!-- <td>
										<div class="d-flex flex-column align-items-center min-w-160">
											<input :value="Helper.capitalize(property.contract.collection_interval)"
												:disabled="true" class="form-control  text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td> -->
									<td>
										<div class="d-flex flex-column align-items-center justify-center">
											<button class="btn btn-primary btn-md text-nowrap " type="button"
												@click="isActiveModal = property">{{ $t('Details') }}
												<i class="fa fa-eye"></i>
											</button>
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
										<div v-if="isActiveModal == property" @click.self="isActiveModal = null"
											class="modal   fade show" id="fullWidthModal"
											style="display: block; padding-right: 15px; background: rgba(0,0,0,0.5);"
											tabindex="-1" role="dialog" aria-modal="true">
											<div class="modal-dialog modal-full-width  modal-dialog-centered modal-dialog-scrollable"
												role="document">
												<div class="modal-content " v-if="property">
													<div class="modal-header header-border">
														<h5 class="modal-title d-flex align-items-center gap-2">
															<i class="fa fa-building"></i> {{ property.name }}
														</h5>
														<button type="button" class="close"
															@click="isActiveModal = null">
															<span aria-hidden="true">×</span>
														</button>
													</div>
													<div class="modal-body">
														<div class="row">
															<div class="col-md-12 mb-4">
																<div class="table-responsive">
																	<table class="table">
																		<thead class="">
																			<tr>
																				<th
																					class="header-border-down first-column-th-class text-center">
																					{{ $t('Name') }}
																				</th>
																				<template
																					v-for="(dateFormatted, dateAsIndex) in dates"
																					:key="dateAsIndex">
																					<template
																						v-if="!hideTablesDates.rent_revenues.includes(Number(dateAsIndex))">
																						<th
																							class="form-label expandable-percentage-input font-weight-bold text-center align-middle header-border-down">
																							<span
																								class="text-center d-inline-block">{{ dateFormatted }}
																								<br />
																							</span>
																						</th>
																					</template>
																					<!--  start Total Yr. 2026 for example -->
																					<th v-if="
																						lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
																						lastMonthIndexInEachYear.length > 0
																					" class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
																						<div class="d-flex justify-content-center align-items-center"
																							style="gap: 10px">
																							<span
																								class="text-center d-inline-block">
																								{{ $t('Yr.') }} <br />
																								{{ getYearsFromDates[dateAsIndex] }}
																							</span>
																							<i @click="
																								hideOrExpandMyYear('rent_revenues', Number(dateAsIndex))
																								" :title="'Expand / Collapse'" class="cursor-pointer fa fa-expand-arrows-alt text-primary exclude-icon"></i>
																						</div>
																					</th>
																					<!--  end Total Yr. 2026 for example -->
																				</template>
																			</tr>
																		</thead>
																		<tbody>
																			<tr data-repeater-style
																				:key="'revenue_contract-' + property.contract.id">
																				<td>
																					<InputText :value="$t('Revenue')"
																						:disabled="true"
																						class="text-left min-w-300"
																						fluid />
																					<i style="visibility: hidden"
																						class="fa fa-ellipsis-h"></i>
																				</td>
																				<template
																					v-for="(dateFormatted, dateAsIndex) in dates"
																					:key="dateAsIndex">
																					<td
																						v-if="!hideTablesDates.rent_revenues.includes(Number(dateAsIndex))">
																						<div
																							class="d-flex flex-column min-w-160  align-items-center">
																							<InputNumber
																								v-model="property.contract.revenue_contract[dateAsIndex]"
																								:min="0"
																								:disabled="true"
																								input-class="text-center"
																								:minFractionDigits="0"
																								:maxFractionDigits="0"
																								suffix=" EGP" fluid />
																							<i @click="handleRepeatRight(property.contract.revenue_contract, dateAsIndex, dates)"
																								class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
																								title="Repeat Right"
																								style="visibility: hidden"></i>
																						</div>
																					</td>
																					<!--  start Total Yr. 2026 for example -->
																					<td v-if="
																						lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
																						lastMonthIndexInEachYear.length > 0
																					">
																						<!-- <InputText :value="0" class="text-center" disabled fluid /> -->
																						<!-- :value="minCashBalanceTotal.subRowTotals.per_year[dateAsIndex] || 0" -->
																						<InputText class="text-center"
																							:value="Helper.number_format(Helper.calculateTableTotals(
																								ref(lastMonthIndexInEachYear),
																								property.contract?.revenue_contract || [],
																								{ type: 'simple' },
																							).subRowTotals.per_year[dateAsIndex] || 0)" disabled fluid />
																						<i style="visibility: hidden"
																							class="fa fa-ellipsis-h"></i>
																					</td>
																					<!--  end Total Yr. 2026 for example -->
																				</template>
																			</tr>
																			<tr data-repeater-style
																				:key="'collection_contract-' + property.contract.id">
																				<td>
																					<InputText :value="$t('Collection')"
																						:disabled="true"
																						class="text-left min-w-300"
																						fluid />
																					<i style="visibility: hidden"
																						class="fa fa-ellipsis-h"></i>
																				</td>
																				<template
																					v-for="(dateFormatted, dateAsIndex) in dates"
																					:key="dateAsIndex">
																					<td
																						v-if="!hideTablesDates.rent_revenues.includes(Number(dateAsIndex))">
																						<div
																							class="d-flex flex-column min-w-160  align-items-center">
																							<InputNumber
																								v-model="property.contract.collection_contract[dateAsIndex]"
																								:min="0"
																								:disabled="true"
																								input-class="text-center"
																								:minFractionDigits="0"
																								:maxFractionDigits="0"
																								suffix=" EGP" fluid />
																							<i @click="handleRepeatRight(property.contract.collection_contract, dateAsIndex, dates)"
																								class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
																								title="Repeat Right"
																								style="visibility: hidden"></i>
																						</div>
																					</td>
																					<!--  start Total Yr. 2026 for example -->
																					<td v-if="
																						lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
																						lastMonthIndexInEachYear.length > 0
																					">
																						<!-- <InputText :value="0" class="text-center" disabled fluid /> -->
																						<!-- :value="minCashBalanceTotal.subRowTotals.per_year[dateAsIndex] || 0" -->
																						<InputText class="text-center"
																							:value="Helper.number_format(Helper.calculateTableTotals(
																								ref(lastMonthIndexInEachYear),
																								property.contract?.collection_contract || [],
																								{ type: 'simple' },
																							).subRowTotals.per_year[dateAsIndex] || 0)" disabled fluid />
																						<i style="visibility: hidden"
																							class="fa fa-ellipsis-h"></i>
																					</td>
																					<!--  end Total Yr. 2026 for example -->
																				</template>
																			</tr>
																			<tr data-repeater-style
																				v-if="property.due_installment && property.due_installment.length"
																				:key="'due_installment-' + property.id">
																				<td>
																					<InputText
																						:value="$t('Due Installments')"
																						:disabled="true"
																						class="text-left min-w-300"
																						fluid />
																					<i style="visibility: hidden"
																						class="fa fa-ellipsis-h"></i>
																				</td>
																				<template
																					v-for="(dateFormatted, dateAsIndex) in dates"
																					:key="dateAsIndex">
																					<td
																						v-if="!hideTablesDates.rent_revenues.includes(Number(dateAsIndex))">
																						<div
																							class="d-flex flex-column min-w-160  align-items-center">
																							<InputNumber
																								v-model="property.due_installment[dateAsIndex]"
																								:min="0"
																								:disabled="true"
																								input-class="text-center"
																								:minFractionDigits="0"
																								:maxFractionDigits="0"
																								suffix=" EGP" fluid />
																							<i @click="handleRepeatRight(property.due_installment, dateAsIndex, dates)"
																								class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
																								title="Repeat Right"
																								style="visibility: hidden"></i>
																						</div>
																					</td>
																					<!--  start Total Yr. 2026 for example -->
																					<td v-if="
																						lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
																						lastMonthIndexInEachYear.length > 0
																					">
																						<!-- <InputText :value="0" class="text-center" disabled fluid /> -->
																						<!-- :value="minCashBalanceTotal.subRowTotals.per_year[dateAsIndex] || 0" -->
																						<InputText class="text-center"
																							:value="Helper.number_format(Helper.calculateTableTotals(
																								ref(lastMonthIndexInEachYear),
																								property.due_installment || [],
																								{ type: 'simple' },
																							).subRowTotals.per_year[dateAsIndex] || 0)" disabled fluid />
																						<i style="visibility: hidden"
																							class="fa fa-ellipsis-h"></i>
																					</td>
																					<!--  end Total Yr. 2026 for example -->
																				</template>
																			</tr>
																		</tbody>
																	</table>
																</div>
															</div>
														</div>
													</div>
													<div class="modal-footer">
														<button type="button" class="btn btn-secondary"
															@click="isActiveModal = null">
															<i class="fa fa-times exclude-icon default-icon-color"></i>
															{{ $t('Close') }}
														</button>
													</div>
												</div>
											</div>
										</div>
									</td>
									<td>
										<div class="d-flex flex-column align-items-center min-w-200">
											<Select :options="[
												{
													id: 1,
													title: $t('Renew')
												},
												{
													id: 2,
													title: $t('Renovate & Rent')
												}
											]" filter v-model="property.propertyContractPartialRentRenewal.renewal_type" optionLabel="title"
												optionValue="id" placeholder="" checkmark :highlightOnSelect="false"
												class="w-full md:w-56" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<td ">
										<div class=" d-flex flex-column min-w-100 align-items-center">
										<InputNumber
											:disabled="property.propertyContractPartialRentRenewal.renewal_type == 1"
											v-model="property.propertyContractPartialRentRenewal.renovate_duration"
											:min="0" input-class="text-center" :minFractionDigits="0"
											:maxFractionDigits="0" suffix=" MTH" fluid />
										<i class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
											title="Repeat Right" style="visibility: hidden"></i>
					</div>
					</td>
					<td>
						<div class="d-flex flex-column min-w-100  align-items-center">
							<InputNumber v-model="property.propertyContractPartialRentRenewal.renovate_cost"
								:disabled="property.propertyContractPartialRentRenewal.renewal_type == 1" :min="0"
								input-class="text-center" :minFractionDigits="0" :maxFractionDigits="0" suffix=" EGP"
								fluid />
							<i class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"
								style="visibility: hidden"></i>
						</div>
					</td>
					<td>
						<div class="d-flex flex-column min-w-percentage  align-items-center">
							<InputNumber v-model="property.propertyContractPartialRentRenewal.renewal_increase_rate"
								:min="0" :max="100" input-class="text-center" :minFractionDigits="2"
								:maxFractionDigits="2" suffix=" %" fluid />
							<i class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"
								style="visibility: hidden"></i>
						</div>
					</td>
					<td>
						<div class="d-flex w-auto flex-column align-items-center min-w-100">
							<input v-model="property.propertyContractPartialRentRenewal.new_rent_amount"
								class="form-control text-left mt-2" type="text" />
							<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
						</div>
					</td>
					<td>
						<div class="d-flex flex-column min-w-100  align-items-center">
							<InputNumber v-model="property.propertyContractPartialRentRenewal.renewal_duration" :min="0"
								input-class="text-center" :minFractionDigits="0" :maxFractionDigits="0" suffix=" Mth"
								fluid />
							<i class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"
								style="visibility: hidden"></i>
						</div>
					</td>
					<td>
						<div class="d-flex flex-column align-items-center min-w-160">
							<Select filter v-model="property.propertyContractPartialRentRenewal.collection_interval"
								:options="[
									{
										id: 'monthly',
										title: $t('Monthly')
									},
									{
										id: 'quarterly',
										title: $t('Quarterly')
									},
									{
										id: 'semi-annually',
										title: $t('Semi-Annually')
									},
									{
										id: 'annually',
										title: $t('Annually')
									}
								]" optionLabel="title" optionValue="id" checkmark :highlightOnSelect="false" class="w-full md:w-56" />
							<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
						</div>
					</td>
					<td>
						<div class="d-flex flex-column min-w-percentage  align-items-center">
							<InputNumber v-model="property.propertyContractPartialRentRenewal.renewal_annual_increase"
								:min="0" :max="100" input-class="text-center" :minFractionDigits="2"
								:maxFractionDigits="2" suffix=" %" fluid />
							<i class="fa fa-ellipsis-h row-repeater-icon cursor-pointer" title="Repeat Right"
								style="visibility: hidden"></i>
						</div>
					</td>
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
	<!-- <div class="col-md-12">
			<div class="d-flex align-items-center justify-content-end" style="gap: 5px">
				<button v-if="!isLoading" @click="submitForm" :disabled="disableSubmitBtn" data-button-value="save"
					type="submit" class="btn text-white active-style save-form">
		
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
		 -->
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

.min-w-100 {
	min-width: 100px !important;
}

.min-w-120 {
	min-width: 120px !important;
}

.min-w-160 {
	min-width: 160px !important;
}

.min-w-200 {
	min-width: 200px !important;
}

.min-w-250 {
	min-width: 250px !important;
}

.min-w-300 {
	min-width: 300px !important;
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

.w-80vh {
	width: 80vh !important;
}

.modal-full-width {
	width: 95%;
	max-width: 100%;
	margin: auto;
}

.modal-full-width .modal-content {

	/* Optional: Makes the content full height too */
	border: 0;
	border-radius: 0;
}


.fixed-column {
	position: sticky;
	left: -15px;
	background: #f8f8f8;
	z-index: 1;
}
</style>
