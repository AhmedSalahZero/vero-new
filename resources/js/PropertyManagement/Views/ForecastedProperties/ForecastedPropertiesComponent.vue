<template>
	<div>
		<div class="row" v-if="currentActiveTab">
			<div class="col-md-12">
				<div class="kt-portlet">
					<div class="kt-portlet__body exclude">
						<div class="row">
							<div class="col-md-11">
								<div class="d-flex align-items-center">
									<h3 class="font-weight-bold form-label kt-subheader__title small-caps"> Forecasted
										Properties Investments</h3>
								</div>
							</div>
							<div class="col-md-12">
								<hr style="background-color: lightgray" />
							</div>
						</div>
						<Loading :isLoading="isLoading"></Loading>
						<div v-if="!isLoading">
							<!-- start fixed monthly repeating  -->
							<div class="col-md-12">
								<div class="overflow-scroll">
									<div v-for="(forecastedProperty, index) in model.forecastedProperties" :key="index"
										class="row main-row-style mb-4">
										<div class="col-md-1 max-w-trash">
											<div v-if="index > 0"
												class="d-flex flex-column justify-content-start align-items-start">
												<label style="visibility: hidden">Delete</label>
												<button @click="deleteRepeaterRow(index)" type="button"
													class="btn btn-danger btn-md btn-danger-style ml-2" title="Delete">
													<i class="fas exclude-icon fa-trash trash-icon"></i>
												</button>
											</div>
										</div>
										<div class="col-md-2 col">
											<Label :required="false">Category</Label>
											<Select filter v-model="forecastedProperty.category_id"
												:options="selects.categories" optionLabel="title" optionValue="id"
												placeholder="" checkmark :highlightOnSelect="false"
												class="w-full md:w-56" />
										</div>
										<div class="col-md-2 col">
											<Label :required="false">Type</Label>
											<Select filter v-model="forecastedProperty.type_id"
												:options="selects.categories.find((category) => category.id == forecastedProperty.category_id)?.types"
												optionLabel="title" optionValue="id" placeholder="" checkmark
												:highlightOnSelect="false" class="w-full md:w-56" />
										</div>
										<div class="col-md-1 col">
											<Label :required="false">Counts</Label>
											<InputNumber v-model="forecastedProperty.counts" :min="0"
												:minFractionDigits="0" :maxFractionDigits="0" suffix="" fluid />
										</div>
										<div class="col-md-1 col min-w-140">
											<Label :required="false">Acquisition Date</Label>
											<VueDatePicker v-model="forecastedProperty.acquisition_date" month-picker
												auto-apply text-input teleport="body" format="MMM-yyyy"
												:min-date="new Date(studyStartDate)"
												:start-date="new Date(studyStartDate)"></VueDatePicker>
										</div>
										<div class="col-md-1 col">
											<Label :required="false">Area</Label>
											<InputNumber v-model="forecastedProperty.area" :min="0"
												:minFractionDigits="0" :maxFractionDigits="0" suffix=" Sqm" fluid />
										</div>
										<div class="col-md-1 col">
											<Label :required="false">Sqm Price</Label>
											<InputNumber v-model="forecastedProperty.sqr_price" :min="0"
												:minFractionDigits="0" :maxFractionDigits="0" suffix=" EGP" fluid />
										</div>
										<div class="col-md-1 col">
											<Label :required="false">Total Amount</Label>
											<InputNumber :disabled="true"
												:modelValue="forecastedProperty.sqr_price * forecastedProperty.area * forecastedProperty.counts"
												:min="0" :minFractionDigits="0" :maxFractionDigits="0" suffix=" EGP"
												fluid />
										</div>
										<div class="col-md-1 col ">
											<Label :required="false">{{ $t('Due Installments') }}</Label>
											<button @click="openInstallmentModal(forecastedProperty.id)"
												class="btn btn-primary btn-md text-nowrap"
												type="button">{{ $t('Add Due Installments') }}</button>
											<!-- <button type="button" "
												class="btn btn-secondary btn-outline-hover-brand btn-icon edit-btn-class exclude-btn"
												:title="$t('Add Due Installments')">
												<i class="fa fa-money-check-alt exclude-icon default-icon-color"></i>
											</button> -->
											<div v-if="showInstallmentModalId === forecastedProperty.id"
												@click.self="closeInstallmentModal"
												class="modal collection-modal fade show"
												style="padding-right: 15px; display: block;z-index:3" aria-modal="true">
												<div class="modal-dialog modal-xl modal-dialog-centered"
													role="document">
													<div class="modal-content">
														<div class="modal-header "
															style="border-bottom: 1px solid green;">
															<h5 class="modal-title"> {{ $t('Due Installments') }}
															</h5>
															<button type="button" class="close"
																@click="closeInstallmentModal">
																<span aria-hidden="true">×</span>
															</button>
														</div>
														<div class="modal-body" style="overflow-y: scroll;height:80vh">
															<div class="row">
																<div class="col-md-3 mb-3">
																	<Label
																		:required="true">{{ $t('Delivery Date') }}</Label>
																	<VueDatePicker
																		v-model="forecastedProperty.forecastedDueInstallment.delivery_date"
																		month-picker auto-apply teleport="body" />
																</div>
																<div class="col-md-3 mb-3">
																	<Label
																		:required="true">{{ $t('Ready To Use Date') }}</Label>
																	<VueDatePicker
																		v-model="forecastedProperty.forecastedDueInstallment.ready_to_use_date"
																		month-picker auto-apply teleport="body" />
																</div>
																<!-- Installment Type Selection -->
																<div class="col-md-12 mb-4">
																	<Label
																		:required="true">{{ $t('Installment Type') }}</Label>
																	<div class="d-flex "
																		style="gap: 2rem;margin-top: 1.4rem;">
																		<div class="d-flex align-items-center mb-2">
																			<RadioButton
																				v-model="forecastedProperty.forecastedDueInstallment.installment_type"
																				inputId="regular" value="regular" />
																			<label for="regular"
																				class="ml-2 mb-4">{{ $t('Regular Installment') }}</label>
																		</div>
																		<div class="d-flex align-items-center">
																			<RadioButton
																				v-model="forecastedProperty.forecastedDueInstallment.installment_type"
																				inputId="variable" value="variable" />
																			<label for="variable"
																				class="ml-2 mb-4  ">{{ $t('Variable Installment') }}</label>
																		</div>
																	</div>
																</div>
																<!-- Regular Installment Fields -->
																<template
																	v-if="forecastedProperty.forecastedDueInstallment.installment_type === 'regular'">
																	<div class="col-md-3 mb-3">
																		<Label
																			:required="true">{{ $t('Contract Signing Payment') }}</Label>
																		<InputNumber
																			v-model="forecastedProperty.forecastedDueInstallment.signing_payment"
																			:min="0" :minFractionDigits="0"
																			:maxFractionDigits="0" :suffix="' EGP'"
																			fluid />
																	</div>
																	<div class="col-md-3 mb-3">
																		<Label :required="true">{{ $t('Date') }}</Label>
																		<VueDatePicker
																			v-model="forecastedProperty.forecastedDueInstallment.signing_payment_date"
																			month-picker auto-apply teleport="body" />
																	</div>
																	<div class="col-md-3 mb-3">
																		<Label
																			:required="true">{{ $t('Reservation Payment') }}</Label>
																		<InputNumber
																			v-model="forecastedProperty.forecastedDueInstallment.reservation_payment"
																			:min="0" :minFractionDigits="0"
																			:maxFractionDigits="0" :suffix="' EGP'"
																			fluid />
																	</div>
																	<div class="col-md-3 mb-3">
																		<Label :required="true">{{ $t('Date') }}</Label>
																		<VueDatePicker
																			v-model="forecastedProperty.forecastedDueInstallment.reservation_payment_date"
																			month-picker auto-apply teleport="body" />
																	</div>
																	<template
																		v-for="(installmentAmountItem, index) in forecastedProperty.forecastedDueInstallment.regular_installments_amounts"
																		:key="index">
																		<div class="col-md-3 mb-3 mt-4">
																			<Label
																				:required="true">{{ $t('Installment Amount') }}</Label>
																			<InputNumber
																				v-model="installmentAmountItem.amount"
																				:min="0" :minFractionDigits="0"
																				:maxFractionDigits="0" :suffix="' EGP'"
																				fluid />
																		</div>
																		<div class="col-md-2 mb-3 mt-4">
																			<Label
																				:required="true">{{ $t('Count') }}</Label>
																			<InputNumber
																				v-model="installmentAmountItem.installment_count"
																				:min="1" fluid />
																		</div>
																		<div class="col-md-3 mb-3 mt-4">
																			<Label
																				:required="true">{{ $t('Start Date') }}</Label>
																			<VueDatePicker " v-model="
																				installmentAmountItem.start_date"
																				month-picker auto-apply
																				teleport="body" />
																		</div>
																		<div class="col-md-3  mb-3 mt-4">
																			<Label
																				:required="true">{{ $t('Payment Interval') }}</Label>
																			<Select filter
																				v-model="installmentAmountItem.installment_payment_interval"
																				:options="[
																					{
																						id: 'monthly',
																						title: 'Monthly',
																					},
																					{
																						id: 'quarterly',
																						title: 'Quarterly',
																					},
																					{
																						id: 'semi-annually',
																						title: 'Semi-annually',
																					},
																				]" optionLabel="title" optionValue="id" checkmark :highlightOnSelect="false"
																				class="w-full md:w-56" />
																		</div>
																		<div class="col-md-1">
																			<Label style="visibility: hidden;"
																				:required="true">{{ $t('Delete Regular') }}</Label>
																			<button v-if="index > 0"
																				@click="deleteRegularInstallmentRow(forecastedProperty, index)"
																				type="button"
																				class="btn btn-danger btn-sm"
																				title="Delete">
																				<i
																					class="fa fa-trash exclude-icon default-icon-color"></i>
																			</button>
																		</div>
																	</template>
																	<div class="col-md-12 mb-3">
																		<Button
																			@click="addNewRegularInstallmentRow(forecastedProperty)"
																			:label="$t('Add Row')" icon="pi pi-plus"
																			class="p-button-sm p-button-success background-green  mt-4"
																			type="button" />
																	</div>
																	<!-- Optional Delivery Payments Section -->
																	<div class="col-md-12 mb-3 mt-2">
																		<div class="form-check">
																			<input
																				v-model="forecastedProperty.forecastedDueInstallment.has_annually_installments"
																				class="form-check-input" type="checkbox"
																				id="showAnnualCheck" />
																			<label
																				class="form-check-label font-size-14 font-weight-bold"
																				for="showAnnualCheck">
																				{{ $t('Add Annual Installments') }}
																			</label>
																		</div>
																	</div>
																	<template
																		v-if="forecastedProperty.forecastedDueInstallment.has_annually_installments">
																		<div class="col-md-3 mb-3">
																			<Label>{{ $t('Start Date') }}</Label>
																			<VueDatePicker
																				v-model="forecastedProperty.forecastedDueInstallment.annual_start_date"
																				month-picker auto-apply
																				teleport="body" />
																		</div>
																		<div class="col-md-3 mb-3">
																			<Label>{{ $t('Annual Amount') }}</Label>
																			<InputNumber
																				v-model="forecastedProperty.forecastedDueInstallment.annual_amount"
																				:min="0" :minFractionDigits="0"
																				:maxFractionDigits="0" :suffix="' EGP'"
																				fluid />
																		</div>
																		<div class="col-md-3 mb-3">
																			<Label>{{ $t('Annual Count') }}</Label>
																			<InputNumber
																				v-model="forecastedProperty.forecastedDueInstallment.annual_count"
																				:min="0" fluid />
																		</div>
																	</template>
																	<!-- Optional Delivery Payments Section -->
																	<div class="col-md-12 mb-3 mt-2">
																		<div class="form-check">
																			<input
																				v-model="forecastedProperty.forecastedDueInstallment.has_delivery_payments"
																				class="form-check-input" type="checkbox"
																				id="showDeliveryCheck" />
																			<label
																				class="form-check-label font-size-14 font-weight-bold"
																				for="showDeliveryCheck">
																				{{ $t('Add Delivery Payments') }}
																			</label>
																		</div>
																	</div>
																	<template
																		v-if="forecastedProperty.forecastedDueInstallment.has_delivery_payments">
																		<div class="col-md-3 mb-3">
																			<Label>{{ $t('Start Date') }}</Label>
																			<VueDatePicker
																				v-model="forecastedProperty.forecastedDueInstallment.delivery_payments_start_date"
																				month-picker auto-apply
																				teleport="body" />
																		</div>
																		<div class="col-md-3 mb-3">
																			<Label>{{ $t('Amount') }}</Label>
																			<InputNumber
																				v-model="forecastedProperty.forecastedDueInstallment.delivery_payments_amount"
																				:min="0" :minFractionDigits="0"
																				:maxFractionDigits="0" :suffix="' EGP'"
																				fluid />
																		</div>
																		<div class="col-md-3 mb-3">
																			<Label>{{ $t('Count') }}</Label>
																			<InputNumber
																				v-model="forecastedProperty.forecastedDueInstallment.delivery_payments_count"
																				:min="0" fluid />
																		</div>
																		{{ forecastedProperty.delivery_payments_payment_interval }}
																		<div class="col-md-3  mb-3">
																			<Label
																				:required="true">{{ $t('Payment Interval') }}</Label>
																			<Select filter
																				v-model="forecastedProperty.forecastedDueInstallment.delivery_payments_payment_interval"
																				:options="[
																					{
																						id: 'monthly',
																						title: 'Monthly',
																					},
																					{
																						id: 'quarterly',
																						title: 'Quarterly',
																					},
																					{
																						id: 'semi-annually',
																						title: 'Semi-annually',
																					},
																					{
																						id: 'annually',
																						title: 'Annually',
																					},
																				]" optionLabel="title" optionValue="id" checkmark :highlightOnSelect="false"
																				class="w-full md:w-56" />
																		</div>
																	</template>
																	<!-- Optional Delivery Payments Section -->
																	<div class="col-md-12 mb-3 mt-2">
																		<div class="form-check">
																			<input
																				v-model="forecastedProperty.forecastedDueInstallment.has_maintenance_payments"
																				class="form-check-input" type="checkbox"
																				id="showMaintenanceCheck" />
																			<label
																				class="form-check-label font-size-14 font-weight-bold"
																				for="showMaintenanceCheck">
																				{{ $t('Add Maintenance Payments') }}
																			</label>
																		</div>
																	</div>
																	<template
																		v-if="forecastedProperty.forecastedDueInstallment.has_maintenance_payments">
																		<div class="col-md-3 mb-3">
																			<Label>{{ $t('Start Date') }}</Label>
																			<VueDatePicker
																				v-model="forecastedProperty.forecastedDueInstallment.maintenance_payments_start_date"
																				month-picker auto-apply
																				teleport="body" />
																		</div>
																		<div class="col-md-3 mb-3">
																			<Label>{{ $t('Amount') }}</Label>
																			<InputNumber
																				v-model="forecastedProperty.forecastedDueInstallment.maintenance_payments_amount"
																				:min="0" :minFractionDigits="0"
																				:maxFractionDigits="0" :suffix="' EGP'"
																				fluid />
																		</div>
																		<div class="col-md-3 mb-3">
																			<Label>{{ $t('Count') }}</Label>
																			<InputNumber
																				v-model="forecastedProperty.forecastedDueInstallment.maintenance_payments_count"
																				:min="0" fluid />
																		</div>
																		<div class="col-md-3  mb-3">
																			<Label
																				:required="true">{{ $t('Payment Interval') }}</Label>
																			<Select filter
																				v-model="forecastedProperty.forecastedDueInstallment.maintenance_payments_payment_interval"
																				:options="[
																					{
																						id: 'monthly',
																						title: 'Monthly',
																					},
																					{
																						id: 'quarterly',
																						title: 'Quarterly',
																					},
																					{
																						id: 'semi-annually',
																						title: 'Semi-annually',
																					},
																					{
																						id: 'annually',
																						title: 'Annually',
																					},
																				]" optionLabel="title" optionValue="id" checkmark :highlightOnSelect="false"
																				class="w-full md:w-56" />
																		</div>
																	</template>
																</template>
																<!-- Variable Installment Fields -->
																<template
																	v-else-if="forecastedProperty.forecastedDueInstallment.installment_type === 'variable'">
																	<div class="col-md-12 mb-3">
																		<div
																			class="d-flex justify-content-between align-items-center mb-3">
																			<h5>{{ $t('Installment Details') }}</h5>
																			<div>
																				<label for="excelUpload"
																					class="btn text-white btn-sm btn-info">
																					<i
																						class="pi text-white pi-upload me-1 exclude-icon default-icon-color"></i>
																					{{ $t('Upload Excel') }}
																				</label>
																				<input id="excelUpload" type="file"
																					accept=".xlsx,.xls"
																					@change="handleFileUpload"
																					style="display: none" />
																			</div>
																		</div>
																		<div v-for="(variableInstallmentAmount, index) in forecastedProperty.forecastedDueInstallment.variable_installment_amounts"
																			:key="index"
																			class="row mb-3 align-items-end">
																			<div class="col-md-3">
																				<Label
																					:required="true">{{ $t('Date') }}</Label>
																				<VueDatePicker
																					v-model="variableInstallmentAmount.date"
																					month-picker auto-apply
																					teleport="body" />
																			</div>
																			<div class="col-md-3">
																				<Label
																					:required="true">{{ $t('Amount') }}</Label>
																				<InputNumber
																					v-model="variableInstallmentAmount.amount"
																					:min="0" :minFractionDigits="0"
																					:maxFractionDigits="0"
																					:suffix="' EGP'" fluid />
																			</div>
																			<div class="col-md-1">
																				<button
																					v-if="forecastedProperty.forecastedDueInstallment.variable_installment_amounts.length > 1"
																					@click="deleteVariableInstallmentRow(forecastedProperty, index)"
																					type="button"
																					class="btn btn-danger btn-sm"
																					title="Delete">
																					<i
																						class="fa fa-trash exclude-icon default-icon-color"></i>
																				</button>
																			</div>
																		</div>
																		<Button
																			@click="addVariableInstallmentRow(forecastedProperty)"
																			:label="$t('Add Row')" icon="pi pi-plus"
																			class="p-button-sm p-button-success background-green  mt-4"
																			type="button" />
																	</div>
																</template>
															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-primary"
																@click="closeInstallmentModal()">
																{{ $t('Close') }}
															</button>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-1 col ml-5">
											<Label class="text-nowrap" :required="false">Renovate Duration</Label>
											<InputNumber v-model="forecastedProperty.renovate_duration" :min="0"
												:minFractionDigits="0" :maxFractionDigits="0" suffix=" MTH" fluid />
										</div>
										<div class="col-md-1 col">
											<Label class="text-nowrap" :required="false">Renovate Cost </Label>
											<InputNumber v-model="forecastedProperty.renovate_cost" :min="0"
												:minFractionDigits="0" :maxFractionDigits="0" suffix=" EGP" fluid />
										</div>
										<div class="col-md-1 col">
											<Label class="text-nowrap" :required="false"> {{ $t('Monthly Rent') }}
											</Label>
											<InputNumber v-model="forecastedProperty.monthly_rent_amount"
												input-class="text-center" :minFractionDigits="0" :maxFractionDigits="0"
												suffix=" EGP" fluid />
										</div>
										<div class="col-md-1 col">
											<Label class="text-nowrap" :required="false">
												{{ $t('Collection Interval') }} </Label>
											<Select filter v-model="forecastedProperty.collection_interval" :options="[
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
										</div>
										<div class="col-md-1 col">
											<Label class="text-nowrap" :required="false"> {{ $t('Rent Duration') }}
											</Label>
											<InputNumber v-model="forecastedProperty.rent_duration" :min="0"
												input-class="text-center" :minFractionDigits="0" :maxFractionDigits="0"
												suffix=" Mth" fluid />
										</div>
										<div class="col-md-1 col">
											<Label class="text-nowrap" :required="false">
												{{ $t('Rent Annual Increase %') }}
											</Label>
											<InputNumber v-model="forecastedProperty.rent_annual_increase" :min="0"
												:max="100" input-class="text-center" :minFractionDigits="2"
												:maxFractionDigits="2" suffix=" %" fluid />
										</div>
									</div>
									<div class="container mt-4">
										<div class="row">
											<div class="col-md-6" style="width: 94%">
												<input @click="addNewForecastedPropertyItem()" data-repeater-create=""
													type="button" class="btn btn-primary btn-sm text-white mb-4"
													value="Add Forecasted Property" />
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- end fixed monthly repeating  -->
						</div>
						<!-- end tabs -->
					</div>
				</div>
			</div>
		</div>
		<!-- end one time expense -->
		<div class="row">
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
							class="spinner-border mr-2 spinner-border-sm mb-1"
							data-button-value="save-and-go-to-next-value" role="status" aria-hidden="true"></span>
						<span class="text-lg" data-button-value="save-and-go-to-next-value" v-html="disableSubmitBtn && model.submit_button == 'save-and-go-to-next-value'
							? 'Saving...'
							: 'Save & Go To Next'
							">
						</span>
					</button>
				</div>
			</div>
		</div>
	</div>
</template>
<script setup>
import InputNumber from 'primevue/inputnumber'
import RadioButton from 'primevue/radiobutton'
import MultiSelect from 'primevue/multiselect'
import Button from 'primevue/button'
import Select from 'primevue/select'
import Loading from '../../../components/Common/Loading.vue'
import IncreaseRateModal from '../../Views/Expenses/modals/IncreaseRateModal.vue'
// import VueLoadingTemplate from 'vue-loading-template';
import axios from 'axios'
import { onMounted, ref } from 'vue'
import Helper from '../../../Helpers/Helper'

import Label from '../../../components/Form/Label.vue'
// import TextInput from "../Form/TextInput.vue";
const isLoading = ref(true)
// modals.increaseRate.currentActive = null
const modals = ref({
	increaseRate: {
		currentActive: null,
	},
})


const showInstallmentModalId = ref(0)
const openInstallmentModal = (propertyId) => {
	showInstallmentModalId.value = propertyId
}


const handleFileUpload = async (event) => {
	const target = event.target
	const file = target.files?.[0]

	if (!file) return

	// Here you would typically parse the Excel file
	// For now, we'll show an alert
	Swal.fire({
		icon: 'info',
		title: 'File Upload',
		text: 'Excel file upload functionality will be implemented',
	})

	// Reset file input
	target.value = ''
}

const closeInstallmentModal = () => {
	showInstallmentModalId.value = 0
}
const addVariableInstallmentRow = (forecastedProperty) => {

	forecastedProperty.forecastedDueInstallment.variable_installment_amounts.push({
		date: {
			month: new Date().getMonth(),
			year: new Date().getFullYear(),
		},
		amount: 0,
	})
}
const addNewRegularInstallmentRow = (forecastedProperty) => {
	forecastedProperty.forecastedDueInstallment.regular_installments_amounts.push(...empty_rows.value.regular_installments_amounts)
}
const deleteRegularInstallmentRow = (forecastedProperty, index) => {
	forecastedProperty.forecastedDueInstallment.regular_installments_amounts.splice(index, 1)
}
const deleteVariableInstallmentRow = (forecastedProperty, index) => {
	forecastedProperty.forecastedDueInstallment.variable_installment_amounts.splice(index, 1)
}

const empty_rows = ref([])
let expenseNamesPerCategories = []
const currentActiveTab = ref('fixed_monthly_repeating_amount')


const calculatePaymentRatesTotal = (item) => {
	if (!item.payment_rate) {
		return 0
	}
	const total = item.payment_rate.reduce((sum, rate) => {
		const numericValue = parseFloat(rate) || 0
		return sum + numericValue
	}, 0)

	return total.toFixed(2)
}
const handleRateChange = (item, rowIndex, event) => {
	item.payment_rate[rowIndex] = Helper.number_unformat(
		event.target ? event.target.value : event.value, // in case of InputPercentage Field
	)

	const total = calculatePaymentRatesTotal(item)
	if (total > 100) {
		Swal.fire({
			icon: 'error',
			title: 'Oops...',
			text: 'Total Rates Exceed 100%',
		})
	}
}

const closePaymentModel = () => {
	currentActiveCollectionModal.value = null
}
const currentActiveCollectionModal = ref(null)
const collectionDueDays = Helper.getCollectionDays()
const paymentTerms = Helper.getPaymentTerms()
const disableSubmitBtn = ref(false)
const model = ref({})
const revenueStreams = ref([])
const departments = ref([])
const expenseCategories = ref([])
const increaseYearsFormatted = ref([])
let revenueCategoriesPerRevenue = []
let positionsPerDepartments = []

const studyStartDate = ref(null)
const submitUrl = ref(null)
const selects = ref({})
const getModelData = () => {
	const body = document.querySelector('body')

	const csrfToken = body.dataset.token
	const baseUrl = body.dataset.baseUrl
	const companyId = body.dataset.currentCompanyId
	const studyId = body.dataset.studyId
	const lang = body.dataset.lang
	const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/property-managements/study/${studyId}/forecasted-properties-fetch-old-data`
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
			model.value = response.data.model
			selects.value = response.data.selects
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

onMounted(() => {
	getModelData()
})

const addNewForecastedPropertyItem = (type) => {
	const emptyRow = empty_rows.value.forecastedProperties
	return model.value.forecastedProperties.push({ ...emptyRow })
}
const deleteRepeaterRow = (index) => {
	model.value.forecastedProperties.splice(index, 1)
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
			}).then(() => {
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
</script>
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
	border: 1px solid #cce2fd;
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
	padding-top: 3px;
	padding-bottom: 5px;
}

.btn-danger-style i {
	padding-right: 0 !important;
	color: white !important;
	font-size: 0.9rem !important;
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
	flex-shrink: 0;
	min-width: 140px;
}

.min-w-160 {
	min-width: 160px !important;
}

.min-w-140 {
	min-width: 140px !important;
}

.min-w-percentage {
	min-width: 126px !important;
}

* {
	min-width: 0;
}

.max-w-trash {
	max-width: 55px !important;
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

:deep(.main-row-style:nth-child(even) .p-select),
:deep(.main-row-style:nth-child(even) .p-multiselect),
:deep(.main-row-style:nth-child(even) .dp__input),
:deep(.main-row-style:nth-child(even) .p-inputtext) {
	border: 1px solid #54aaa6 !important;
}

:deep(.main-row-style:nth-child(odd) .p-multiselect),
:deep(.main-row-style:nth-child(odd) .p-select),
:deep(.main-row-style:nth-child(odd) .dp__input),
:deep(.main-row-style:nth-child(odd) .p-inputtext) {
	border: 1px solid #4d9afa !important;
}

.main-row-style {
	flex-wrap: nowrap;
}
</style>
<style scoped></style>
