<script setup lang="ts">
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { computed, ref } from 'vue'
import Label from '../../../../components/Form/Label.vue'


interface Props {
	modelValue: any
	selects: {
		filteredUnitOfMeasurements: any[],
		categories: any[],
		types: any[]

	}
}

const props = defineProps<Props>()
const emit = defineEmits(['update:modelValue'])

const model = computed({
	get: () => props.modelValue,
	set: (value) => emit('update:modelValue', value)
})

const showTaxesModal = ref<boolean>(false)
const showMarketValuesModal = ref<boolean>(false)

// Taxes repeater methods
const addTaxRow = () => {
	model.value.tax_rates.push({ ...model.value.empty_rows.tax_rates })
}

const deleteTaxRow = (index: number) => {
	model.value.tax_rates.splice(index, 1)
}

// Market Values repeater methods
const addMarketValueRow = () => {
	model.value.market_values.push({ ...model.value.empty_rows.market_values })
}

const deleteMarketValueRow = (index: number) => {

	model.value.market_values.splice(index, 1)

}

const openMarketValuesModal = () => {
	showMarketValuesModal.value = true
}

const closeMarketValuesModal = () => {
	showMarketValuesModal.value = false
}
const openTaxesModal = () => {
	showTaxesModal.value = true
}

const closeTaxesModal = () => {
	showTaxesModal.value = false
}
</script>
<template>
	<div>
		<div class="row">
			<div class="col-md-2">
				<Label :required="true"> {{ $t('Location') }} </Label>
				<InputText v-model="model.location" placeholder="Enter Location" fluid />
			</div>
			<div class="col-md-2">
				<Label :required="true">
					{{ $t('Category') }}
				</Label>
				<Select filter v-model="model.category_id" :options="selects.categories" optionLabel="title"
					optionValue="id" checkmark :highlightOnSelect="false" class="w-full md:w-56" />
			</div>
			<div class="col-md-2">
				<Label :required="true">
					{{ $t('Type') }}
				</Label>
				<Select filter v-model="model.type_id" :options="selects.types" optionLabel="title" optionValue="id"
					checkmark :highlightOnSelect="false" class="w-full md:w-56" />
			</div>
			<div class="col-md-2">
				<Label :required="true"> {{ $t('Area') }} </Label>
				<InputNumber v-model="model.area" :min="0" :minFractionDigits="0" :maxFractionDigits="2" fluid />
			</div>
			<div class="col-md-2">
				<Label :required="true">
					{{ $t('Unit Of Measurement') }}
				</Label>
				<Select filter v-model="model.unit_of_measurement" :options="selects.filteredUnitOfMeasurements"
					optionLabel="title" optionValue="id" placeholder="" checkmark :highlightOnSelect="false"
					class="w-full md:w-56" />
			</div>
			<div class="col-md-2 ">
				<Label :required="true"> {{ $t('Acquisition Cost') }} </Label>
				<InputNumber v-model="model.acquisition_cost" :min="0" :minFractionDigits="0" :maxFractionDigits="0"
					suffix=" EGP" fluid />
			</div>
			<div class="col-md-2 mt-4">
				<Label :required="true">{{ $t('Acquisition Date') }}</Label>
				<VueDatePicker :max-date="new Date()" v-model="model.acquisition_date" month-picker auto-apply
					format="MMM-yyyy">
				</VueDatePicker>
			</div>
			<div class="col-md-2 mt-4">
				<Label :required="true"> {{ $t('Book Value') }} </Label>
				<InputNumber v-model="model.current_book_value" :min="0" :minFractionDigits="0" :maxFractionDigits="0"
					suffix=" EGP" fluid />
			</div>
			<div class="col-md-2 mt-4">
				<Label :required="true"> {{ $t('Book Value Date') }} </Label>
				<VueDatePicker :min-date="model.acquisition_date" :max-date="new Date()" v-model="model.book_value_date"
					month-picker auto-apply format="MMM-yyyy">
				</VueDatePicker>
			</div>
			<div class="col-md-2 mt-4">
				<Label :required="true"> {{ $t('Monthly Depreciation') }} </Label>
				<InputNumber :disabled="model.nature_id === 'land'" v-model="model.month_depreciation" :min="0"
					:minFractionDigits="0" :maxFractionDigits="0" suffix=" EGP" fluid />
			</div>
			<div class="col-md-2 mt-4">
				<Label :required="true"> {{ $t('Depreciation Duration (MTH)') }} </Label>
				<InputNumber :disabled="model.nature_id === 'land'" v-model="model.duration_in_months" :min="0"
					:minFractionDigits="0" :maxFractionDigits="0" suffix=" MTH" fluid />
			</div>
			<!-- <div class="col-md-2">
				<Label :required="true">
					{{ $t('Usage Status') }}
				</Label>
				<Select filter v-model="model.usage_status_id" :options="selects.usageStatus" optionLabel="title"
					optionValue="id" placeholder="" checkmark :highlightOnSelect="false" class="w-full md:w-56" />
			</div> -->
			<div class="col-md-1 mt-4">
				<Label :required="true"> {{ $t('Market Values') }} </Label>
				<button @click="openMarketValuesModal" class="btn btn-primary btn-md text-nowrap" type="button">
					{{ $t('Market Values') }}
				</button>
			</div>
			<div class="col-md-1 mt-4">
				<Label :required="true">
					{{ $t('Taxes Rates') }}
				</Label>
				<button @click="openTaxesModal" class="btn btn-primary btn-md text-nowrap" type="button">
					{{ $t('Tax Rates') }}
				</button>
			</div>
		</div>
		<!-- Taxes Modal -->
		<div v-if="showTaxesModal" @click.self="closeTaxesModal" class="modal collection-modal fade show"
			style="padding-right: 15px; display: block" aria-modal="true">
			<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{{ $t('Tax Rates') }}</h5>
						<button type="button" class="close" @click="closeTaxesModal">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="customize-elements">
							<table class="table exclude-table">
								<thead>
									<tr>
										<th class="text-center" style="width: 50px;"></th>
										<th class="text-center text-nowrap">Tax Rate %</th>
										<th class="text-center">Date</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(tax, index) in model.tax_rates" :key="index">
										<td class="text-center">
											<button v-if="Number(index) > 0" @click="deleteTaxRow(Number(index))"
												type="button" class="btn btn-danger btn-md btn-danger-style"
												title="Delete">
												<i class="fas exclude-icon fa-trash trash-icon"></i>
											</button>
										</td>
										<td>
											<div>
												<InputNumber v-model="tax.rate" :minFractionDigits="2"
													:maxFractionDigits="2" :step="0.25" :min="0" :max="100"
													mode="decimal" showButtons suffix=" %" fluid />
											</div>
										</td>
										<td>
											<div class="">
												<VueDatePicker v-model="tax.date" month-picker auto-apply
													teleport="body" format="MMM-yyyy"></VueDatePicker>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
							<div class="mt-3">
								<button @click="addTaxRow" type="button" class="btn btn-primary btn-sm text-white">
									<i class="fas fa-plus exclude-icon mr-2"></i>{{ $t('Add Tax Rate') }}
								</button>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" @click="closeTaxesModal">
							{{ $t('Save') }}
						</button>
					</div>
				</div>
			</div>
		</div>
		<!-- Market Values Modal -->
		<div v-if="showMarketValuesModal" @click.self="closeMarketValuesModal" class="modal collection-modal fade show"
			style="padding-right: 15px; display: block" aria-modal="true">
			<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{{ $t('Market Values') }}</h5>
						<button type="button" class="close" @click="closeMarketValuesModal">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="customize-elements">
							<table class="table exclude-table">
								<thead>
									<tr>
										<th class="text-center" style="width: 50px;"></th>
										<th class="text-center text-nowrap">Value (EGP)</th>
										<th class="text-center">Date</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(marketValue, index) in model.market_values" :key="index">
										<td class="text-center">
											<button v-if="Number(index) > 0"
												@click="deleteMarketValueRow(Number(index))" type="button"
												class="btn btn-danger btn-md btn-danger-style" title="Delete">
												<i class="fas exclude-icon fa-trash trash-icon"></i>
											</button>
										</td>
										<td>
											<div>
												<InputNumber v-model="marketValue.value" :minFractionDigits="2"
													:maxFractionDigits="2" :step="100" :min="0" mode="decimal"
													showButtons suffix=" EGP" fluid />
											</div>
										</td>
										<td>
											<div class="">
												<VueDatePicker v-model="marketValue.date" month-picker auto-apply
													teleport="body" format="MMM-yyyy"></VueDatePicker>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
							<div class="mt-3">
								<button @click="addMarketValueRow" type="button"
									class="btn btn-primary btn-sm text-white">
									<i class="fas fa-plus exclude-icon mr-2"></i>{{ $t('Add Market Value') }}
								</button>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" @click="closeMarketValuesModal">
							{{ $t('Save') }}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<style scoped>
.min-w-140 {
	min-width: 140px !important;
}

:deep(.p-inputtext) {
	border: 1px solid #4d9afa !important;
}

:deep(.dp__input) {
	border: 1px solid #4d9afa !important;
}

.col {
	flex-shrink: 1;
	min-width: 0;
}

:deep(.p-component, .dp__input) {
	height: 38px !important;
}

:deep(.p-select) {
	border: 1px solid #4d9afa;
}

:deep(.dp__input) {
	height: 38px !important;
}

:deep(.p-select-label) {
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

:deep(.p-select:not(.p-disabled).p-focus) {
	border-color: #4d9afa;
}

:deep(.p-select) {
	border-color: #4d9afa !important;
}

:deep(.p-inputnumber) {
	min-width: 75px !important;
}

/* Fix z-index for PrimeVue Select dropdown inside modal */
:deep(.p-select-overlay) {
	z-index: 1060 !important;
}

.modal {
	z-index: 990 !important;
}

.modal-body {
	max-height: 800px;
}

:deep(.dp__input) {
	border: 1px solid #4d9afa !important;
}

.modal-content {
	border: 1px solid #4d9afa !important;
}

.customize-elements,
.customize-elements th {
	border: none !important;
}
</style>
