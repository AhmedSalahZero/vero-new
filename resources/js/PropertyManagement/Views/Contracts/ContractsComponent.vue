<script setup lang="ts">

import '@vuepic/vue-datepicker/dist/main.css'
import axios from 'axios'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import RadioButton from 'primevue/radiobutton'
import Select from 'primevue/select'
import Swal from 'sweetalert2'
import { computed, onMounted, ref } from 'vue'
import Loading from '../../../components/Common/Loading.vue'
import Label from '../../../components/Form/Label.vue'

// Props from window
const propertyId = (window as any).propertyId
const contractId = (window as any).contractId
const updateCollectionCurrency = (currency: string) => {
	console.log(currency)
	model.value.collection_currency = currency
}
// State
const disableSubmitBtn = ref<boolean>(false)
const isLoading = ref<boolean>(true)
const submitUrl = ref<string>('')
const showInstallmentDialog = ref<boolean>(false)
const currencies = ref<any[]>([
	{ id: 'EGP', title: 'EGP' },
	{ id: 'USD', title: 'USD' },
	{ id: 'EUR', title: 'EUR' },
	{ id: 'SAR', title: 'SAR' },
	{ id: 'AED', title: 'AED' },
	{ id: 'GBP', title: 'GBP' },
])
// Show/Hide state
interface showAndHidType {
	header: boolean
}
const showAndHide = ref<showAndHidType>({
	header: true,
})

// Selects data
const selects = ref<{
	tenantTypes: any[]
	collectionIntervals: any[]
	installmentTypes: any[]
}>({
	tenantTypes: [],
	collectionIntervals: [],
	installmentTypes: [],
})

// Model
const model = ref<{ [key: string]: any }>({
	property_id: propertyId,
	tenant_name: '',
	tenant_type: 'individual',
	monthly_rent: 0,
	contract_start_date: {
		month: new Date().getMonth(),
		year: new Date().getFullYear(),
	},
	contract_end_date: {
		month: new Date().getMonth(),
		year: new Date().getFullYear(),
	},
	collection_interval: 'monthly',
	insurance_months_count: 0,
	annually_increase_rate: null,
	collection_policy: null,
	installment: {
		installment_type: 'regular',
		installment_amount: 0,
		start_date: {
			month: new Date().getMonth(),
			year: new Date().getFullYear(),
		},
		end_date: {
			month: new Date().getMonth(),
			year: new Date().getFullYear(),
		},
		installment_count: 0,
		annual_start_date: null,
		annual_amount: 0,
		annual_count: 0,
		installment_details: [],
	},
})

// Computed
const insuranceAmount = computed(() => {
	return (model.value.insurance_months_count || 0) * (model.value.monthly_rent || 0)
})

const showAnnualSection = ref(false)

// Methods

const getModelData = () => {
	const body = document.querySelector('body') as HTMLBodyElement
	const companyId = body.dataset.currentCompanyId
	const fetchOldDataUrl = `/en/${companyId}/property-managements/properties/${propertyId}/contracts/old-data`

	const csrfToken = body.dataset.token

	const params: any = {}
	if (contractId) {
		params.contract_id = contractId
	}

	axios
		.get(fetchOldDataUrl, {
			params,
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				Accept: 'application/json',
			},
		})
		.then((response) => {
			selects.value = response.data.selects
			submitUrl.value = response.data.submitUrl
			model.value = { ...model.value, ...response.data.model }
			currencies.value = response.data.currencies
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

	const method = contractId ? 'put' : 'post'

	axios({
		method: method,
		url: submitUrl.value,
		data: model.value,
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
				text: response.data.message || 'Your data has been saved',
				draggable: true,
				timer: 2000,
			}).then(() => {
				if (response.data.redirect) {
					window.location.href = response.data.redirect
				}
			})
		})
		.catch((error) => {
			disableSubmitBtn.value = false
			console.log(error)
			let errorMessage = error.response?.data?.message || 'An error occurred'

			// Show validation errors if present
			if (error.response?.data?.errors) {
				const errors = error.response.data.errors
				errorMessage = Object.values(errors).flat().join('<br>')
			}

			Swal.fire({
				icon: 'error',
				title: 'Oops...',
				html: errorMessage,
			})
		})
}

// const openInstallmentDialog = () => {
// 	showInstallmentDialog.value = true
// }

// const closeInstallmentDialog = () => {
// 	showInstallmentDialog.value = false
// }

// const addVariableInstallmentRow = () => {
// 	model.value.installment.installment_details.push({
// 		date: {
// 			month: new Date().getMonth(),
// 			year: new Date().getFullYear(),
// 		},
// 		amount: 0,
// 	})
// }



// const handleFileUpload = async (event: Event) => {
// 	const target = event.target as HTMLInputElement
// 	const file = target.files?.[0]

// 	if (!file) return

// 	// Here you would typically parse the Excel file
// 	// For now, we'll show an alert
// 	Swal.fire({
// 		icon: 'info',
// 		title: 'File Upload',
// 		text: 'Excel file upload functionality will be implemented',
// 	})

// 	// Reset file input
// 	target.value = ''
// }

// const formatDate = (dateObj: any) => {
// 	if (!dateObj) return ''
// 	const monthNames = [
// 		'Jan',
// 		'Feb',
// 		'Mar',
// 		'Apr',
// 		'May',
// 		'Jun',
// 		'Jul',
// 		'Aug',
// 		'Sep',
// 		'Oct',
// 		'Nov',
// 		'Dec',
// 	]
// 	return `${monthNames[dateObj.month]} ${dateObj.year}`
// }

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
		<!-- start Contract Section -->
		<div class="kt-portlet">
			<div class="kt-portlet__body">
				<div class="row">
					<div class="col-md-11">
						<div class="d-flex align-items-center">
							<h3 class="font-weight-bold form-label kt-subheader__title small-caps">
								{{ $t('Contract Information') }}
							</h3>
						</div>
					</div>
					<div class="col-md-1">
						<div class="d-flex justify-content-end">
							<div @click="showAndHide.header = !showAndHide.header" class="btn show-hide-style">
								{{ $t('Show/Hide') }}
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<hr style="background-color: lightgray" />
					</div>
				</div>
				<div v-show="showAndHide.header" class="row mt-4">
					<div class="col-md-2">
						<Label :required="true">{{ $t('Tenant Type') }}</Label>
						<Select filter v-model="model.tenant_type" :options="selects.tenantTypes" optionLabel="label"
							optionValue="value" checkmark :highlightOnSelect="false" class="w-full md:w-56" />
					</div>
					<!-- Tenant Name -->
					<div class="col-md-2">
						<Label :required="true">{{ $t('Tenant Name') }}</Label>
						<Select filter v-model="model.tenant_id"
							:options="selects.tenantTypes.find(tenantType => tenantType.value == model.tenant_type).tenants"
							optionLabel="label" optionValue="value" checkmark :highlightOnSelect="false"
							class="w-full md:w-56" />
					</div>
					<!-- Tenant Type -->
					<!-- Contract Start Date -->
					<div class="col-md-2 col min-w-140">
						<Label :required="true">{{ $t('Contract Start Date') }}</Label>
						<VueDatePicker mode="date" v-model="model.contract_start_date" auto-apply format="dddd-MMM-yyyy"
							no-time :enable-time-picker="false">
						</VueDatePicker>
					</div>
					<div class="col-md-2 col min-w-140">
						<Label :required="true">{{ $t('Contract End Date') }}</Label>
						<VueDatePicker :min-date="model.contract_start_date" mode="date"
							v-model="model.contract_end_date" auto-apply format="dddd-MMM-yyyy" no-time
							:enable-time-picker="false">
						</VueDatePicker>
					</div>
					<!-- Contract End Date -->
					<!-- <div class="col-md-2 col min-w-140">
						<Label :required="true">{{ $t('Contract End Date') }}</Label>
						<VueDatePicker :min-date="model.contract_start_date
							? new Date(model.contract_start_date.year, model.contract_start_date.month)
							: null
							" v-model="model.contract_end_date" month-picker auto-apply format="MMM-yyyy">
						</VueDatePicker>
					</div> -->
					<!-- Tenant Type -->
					<div class="col-md-2">
						<Label :required="true">{{ $t('Contract Currency') }}</Label>
						<Select @change="updateCollectionCurrency($event.value)" filter
							v-model="model.contract_currency" :options="currencies" optionLabel="title" optionValue="id"
							checkmark :highlightOnSelect="false" class="w-full md:w-56" />
					</div>
					<!-- Monthly Rent -->
					<div class="col-md-2">
						<Label :required="true">{{ $t('Monthly Rent') }}</Label>
						<InputNumber v-model="model.monthly_rent" :min="0" :minFractionDigits="0" :maxFractionDigits="0"
							:suffix="' ' + model.contract_currency" fluid />
					</div>
					<!-- Monthly Rent -->
					<div class="col-md-2 mt-4">
						<Label :required="true">{{ $t('Variable From Tenant Revenues %') }}</Label>
						<InputNumber v-model="model.variable_from_tenant_revenues_percentage" :min="0"
							:minFractionDigits="2" :maxFractionDigits="2" suffix=" %" fluid />
					</div>
					<!-- Monthly Rent -->
					<div class="col-md-2 mt-4">
						<Label :required="true">{{ $t('Min Amount') }}</Label>
						<InputNumber v-model="model.min_amount" :min="0" :minFractionDigits="0" :maxFractionDigits="0"
							:suffix="' ' + model.contract_currency" fluid />
					</div>
					<!-- Collection Currency -->
					<div class="col-md-2 mt-4">
						<Label :required="true">{{ $t('Collection Currency') }}</Label>
						<Select filter v-model="model.collection_currency" :options="currencies" optionLabel="title"
							optionValue="id" checkmark :highlightOnSelect="false" class="w-full md:w-56" />
					</div>
					<!-- Collection Interval -->
					<div class="col-md-2 mt-4">
						<Label :required="true">{{ $t('Collection Interval') }}</Label>
						<Select filter v-model="model.collection_interval" :options="selects.collectionIntervals"
							optionLabel="label" optionValue="value" checkmark :highlightOnSelect="false"
							class="w-full md:w-56" />
					</div>
					<!-- Insurance Months Count -->
					<div class="col-md-2 mt-4">
						<Label :required="true">{{ $t('Insurance Months Count') }}</Label>
						<InputNumber v-model="model.insurance_months_count" :min="0" :minFractionDigits="0"
							:maxFractionDigits="0" suffix=" MTH" fluid />
					</div>
					<!-- Insurance Amount (Readonly) -->
					<div class="col-md-2 mt-4">
						<Label>{{ $t('Insurance Amount') }}</Label>
						<InputNumber :model-value="insuranceAmount" :min="0" :minFractionDigits="0"
							:maxFractionDigits="0" :suffix="' ' + model.contract_currency" disabled fluid />
					</div>
					<!-- Annually Increase Rate (Optional) -->
					<div class="col-md-2 mt-4">
						<Label>{{ $t('Annually Increase Rate') }} (%)</Label>
						<InputNumber v-model="model.annually_increase_rate" :min="0" :max="100" :minFractionDigits="2"
							:maxFractionDigits="2" suffix=" %" fluid />
					</div>
					<!-- Installment Button -->
					<!-- <div class="col-md-2 mt-4">
						<Label :required="false">{{ $t('Installments') }}</Label>
						<Button @click="openInstallmentDialog" :label="$t('Configure')" icon="pi pi-cog"
							class="p-button-info w-full" />
					</div> -->
				</div>
			</div>
		</div>
		<!-- end Contract Section -->
		<!-- Submit Buttons -->
		<div class="col-md-12">
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
						: 'Save & Go To Index'
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

:deep(.p-dialog) {
	z-index: 1050 !important;
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

.max-w-trash {
	max-width: 50px !important;
}

:deep(.dp__input) {
	border: 1px solid #4d9afa !important;
}

:deep(.p-inputtext) {
	border: 1px solid #4d9afa !important;
}

:deep(.dp__input) {
	border: 1px solid #4d9afa !important;
}
</style>
