<script setup lang="ts">
import axios from 'axios'
import Select from 'primevue/select'
import Swal from 'sweetalert2'
import { onMounted, ref, watch } from 'vue'
import Loading from '../../../components/Common/Loading.vue'
import Label from '../../../components/Form/Label.vue'
import CommonPropertyFields from './Components/CommonPropertyFields.vue'
import ComplexBuildingFields from './Components/ComplexBuildingFields.vue'
import UnitFields from './Components/UnitFields.vue'

// State
const disableSubmitBtn = ref<boolean>(false)
const isLoading = ref<boolean>(true)
const submitUrl = ref<string>('')
const isInitialLoad = ref<boolean>(true) // Flag to prevent watch from clearing values on load

// Selects data

const selects = ref<{
	natures: any[]
	categories: any[]
	ownerships: any[]
	unitOfMeasurements: any[],
	filteredUnitOfMeasurements: any[]
	usageStatus: any[]
	countries: any[]
	types: any[]
}>({
	natures: [],
	categories: [],
	ownerships: [],
	unitOfMeasurements: [],
	filteredUnitOfMeasurements: [],
	usageStatus: [],
	countries: [],
	types: [],
})

// Model
const model = ref<{ [key: string]: any }>({})


watch(() => model.value.nature_id, (newVal) => {
	selects.value.filteredUnitOfMeasurements = selects.value.unitOfMeasurements
	if (newVal === 'land') {
		model.value.month_depreciation = 0
		model.value.duration_in_months = 0
	}
	if (newVal !== 'land') {
		selects.value.filteredUnitOfMeasurements = selects.value.unitOfMeasurements.filter((unit) => {
			return unit.id === 'sqm'
		})
		model.value.unit_of_measurement = 'sqm'
		model.value.units.forEach((unit) => {
			unit.unit_of_measurement = 'sqm'
		})
	}
}, { deep: true, immediate: false })
// Property type constants
type PropertyType = 'unit' | 'land' | 'complex' | 'building'

// Get current property type from nature_id
const getCurrentPropertyType = (): PropertyType => {
	return (model.value.nature_id as PropertyType) || 'unit'
}

const propertyTypes = ref([
	{ id: 'unit', title: 'Unit' },
	{ id: 'building', title: 'Building' },
	{ id: 'complex', title: 'Complex' },
	{ id: 'land', title: 'Land' },
])



// Show/Hide state
interface showAndHidType {
	header: boolean
}
const showAndHide = ref<showAndHidType>({
	header: true,
})

// Methods
const getModelData = () => {
	const body = document.querySelector('body') as HTMLBodyElement
	const csrfToken = body.dataset.token
	const baseUrl = body.dataset.baseUrl
	const companyId = body.dataset.currentCompanyId
	const lang = body.dataset.lang

	// Get property ID from URL if editing
	//	const urlParams = new URLSearchParams(window.location.search)
	const propertyId = window.location.pathname.includes('/edit')
		? window.location.pathname.split('/').slice(-2, -1)[0]
		: null

	// Get property type from URL or default to 'unit'
	const urlParams = new URLSearchParams(window.location.search)
	const propertyType = urlParams.get('type') || 'unit'

	const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/property-managements/properties/properties-old-data${propertyId ? '?property_id=' + propertyId : '?type=' + propertyType}`
	console.log(fetchOldDataUrl)
	axios
		.get(fetchOldDataUrl, {
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				Accept: 'application/json',
			},
		})
		.then((response) => {
			// First set selects so governorates and provinces are available
			selects.value = response.data.selects
			submitUrl.value = response.data.submitUrl

			// Then set model data
			model.value = response.data.model
			model.value.empty_rows = response.data.empty_rows

			// Determine initial type
			let initialType: PropertyType = 'unit'

			// If creating new property and type is in URL, use that
			if (!propertyId && propertyType) {
				initialType = propertyType as PropertyType
				model.value.nature_id = initialType
			} else {
				// For existing properties, get type from nature_id
				initialType = getCurrentPropertyType()
			}

			// Initialize fields based on current type


			isLoading.value = false

			// Allow watch functions to work after initial load
			setTimeout(() => {
				isInitialLoad.value = false
			}, 100)
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

	// Determine HTTP method based on URL
	const method = submitUrl.value.includes('/update') ? 'put' : 'post'

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
				if (response.data.redirectTo) {
					window.location.href = response.data.redirectTo
				}
			})
		})
		.catch((error) => {
			const errorMessage = error.response?.data?.message || 'An error occurred'
			const errors = error.response?.data?.errors

			disableSubmitBtn.value = false

			// Show specific validation errors if available
			if (errors) {
				let errorText = errorMessage + '\n\n'
				for (const [field, messages] of Object.entries(errors)) {
					errorText += (messages as string[]).join('\n') + '\n'
				}
				Swal.fire({
					icon: 'error',
					title: 'Oops...',
					html: errorText.replace(/\n/g, '<br>'),
				})
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Oops...',
					text: errorMessage,
				})
			}
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
		<!-- start Properties Section -->
		<div class="kt-portlet">
			<div class="kt-portlet__body">
				<div class="row">
					<div class="col-md-10">
						<div class="d-flex align-items-center">
							<h3 class="font-weight-bold form-label kt-subheader__title small-caps">
								{{ $t('Properties') }}
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
				<div v-show="showAndHide.header">
					<!-- Property Type Selector -->
					<div class="row mt-4">
						<div class="col-md-2">
							<Label :required="true">
								{{ $t('Property Nature') }}
							</Label>
							<Select filter v-model="model.nature_id" :options="propertyTypes" optionLabel="title"
								optionValue="id" placeholder="" checkmark :highlightOnSelect="false"
								class="w-full md:w-56" />
						</div>
					</div>
					<!-- Common Fields -->
					<div class="mt-4">
						<CommonPropertyFields v-model="model" :selects="{
							natures: selects.natures,
							ownerships: selects.ownerships,
							countries: selects.countries,


						}" :isInitialLoad="isInitialLoad" />
					</div>
					<!-- Unit/Land Specific Fields -->
					<div v-if="model.nature_id === 'unit' || model.nature_id === 'land'" class="mt-4">
						<UnitFields v-model="model" :selects="{
							filteredUnitOfMeasurements: selects.filteredUnitOfMeasurements,
							categories: selects.categories,
							types: model.category_id ? selects.categories.find((category: any) => category.id === model.category_id)?.types : [],
						}" />
					</div>
					<!-- Complex/Building Specific Fields -->
					<div v-if="model.nature_id === 'complex' || model.nature_id === 'building'" class="mt-4">
						<ComplexBuildingFields v-model="model" :selects="{
							filteredUnitOfMeasurements: selects.filteredUnitOfMeasurements,
							categories: selects.categories,

						}" />
					</div>
				</div>
			</div>
		</div>
		<!-- end Properties Section -->
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

.customize-elements,
.customize-elements th {
	border: none !important;
}
</style>
