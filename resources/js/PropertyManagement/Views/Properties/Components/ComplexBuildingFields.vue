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
		filteredUnitOfMeasurements: any[]
		categories: any[],

	}
}

const props = defineProps<Props>()
const emit = defineEmits(['update:modelValue'])

const model = computed({
	get: () => props.modelValue,
	set: (value) => emit('update:modelValue', value)
})

const showTaxesModalForUnit = ref<number | null>(null)
const showMarketValuesModalForUnit = ref<number | null>(null)

// Units repeater methods
const addUnitRow = () => {
	model.value.units.push({ ...model.value.empty_rows.units })
}

const deleteUnitRow = (index: number) => {
	model.value.units.splice(index, 1)
}

// Taxes methods for units
const addTaxRow = (unitIndex: number) => {
	model.value.units[unitIndex].tax_rates.push({ ...model.value.empty_rows.tax_rates })
}

const deleteTaxRow = (unitIndex: number, taxIndex: number) => {
	model.value.units[unitIndex].tax_rates.splice(taxIndex, 1)
}

const openTaxesModal = (unitIndex: number) => {
	showTaxesModalForUnit.value = unitIndex
}

const closeTaxesModal = () => {
	showTaxesModalForUnit.value = null
}

// Market Values methods for units
const addMarketValueRow = (unitIndex: number) => {
	model.value.units[unitIndex].market_values.push({ ...model.value.empty_rows.market_values })
}

const deleteMarketValueRow = (unitIndex: number, marketValueIndex: number) => {
	model.value.units[unitIndex].market_values.splice(marketValueIndex, 1)
}

const openMarketValuesModal = (unitIndex: number) => {
	showMarketValuesModalForUnit.value = unitIndex
}

const closeMarketValuesModal = () => {
	showMarketValuesModalForUnit.value = null
}
</script>
<template>
	<div>
		<div class="row">
			<div class="col-md-12">
				<div class="d-flex align-items-center mb-3">
					<h4 class="font-weight-bold form-label kt-subheader__title small-caps">
						{{ $t('Units') }}
					</h4>
				</div>
				<hr style="background-color: lightgray" />
			</div>
		</div>
		<!-- Units Repeater -->
		<div class="customize-elements">
			<div v-for="(unit, unitIndex) in model.units" :key="unitIndex" class="unit-row mb-4 p-3"
				style="border: 1px solid #e0e0e0; border-radius: 5px">
				<div class="row mb-2">
					<div class="col-md-11">
						<h5 class="mb-0">{{ $t('Unit') }} {{ Number(unitIndex) + 1 }}</h5>
					</div>
					<div class="col-md-1 text-right">
						<button v-if="model.units.length > 1" @click="deleteUnitRow(Number(unitIndex))" type="button"
							class="btn btn-danger btn-sm btn-danger-style" :title="$t('Delete')">
							<i class="fas exclude-icon fa-trash trash-icon"></i>
						</button>
					</div>
				</div>
				<div class="row">
					<div class="col-md-2">
						<Label :required="true"> {{ $t('Name') }} </Label>
						<InputText v-model="unit.name" fluid />
					</div>
					<div class="col-md-2">
						<Label :required="true"> {{ $t('Code') }} </Label>
						<InputText v-model="unit.code" fluid />
					</div>
					<div class="col-md-2">
						<Label :required="true">
							{{ $t('Location') }}
						</Label>
						<InputText v-model="unit.location" placeholder="Enter Location" fluid />
					</div>
					<div class="col-md-2">
						<Label :required="true">
							{{ $t('Category') }}
						</Label>
						<Select filter v-model="unit.category_id" :options="selects.categories" optionLabel="title"
							optionValue="id" checkmark :highlightOnSelect="false" class="w-full md:w-56" />
					</div>
					<div class="col-md-2">
						<Label :required="true">
							{{ $t('Type') }}
						</Label>
						<Select filter v-model="unit.type_id"
							:options="selects.categories.find((category: any) => category.id === unit.category_id)?.types"
							optionLabel="title" optionValue="id" checkmark :highlightOnSelect="false"
							class="w-full md:w-56" />
					</div>
					<div class="col-md-2">
						<Label :required="true"> {{ $t('Area') }} </Label>
						<InputNumber v-model="unit.area" :min="0" :minFractionDigits="0" :maxFractionDigits="2" fluid />
					</div>
					<div class="col-md-2 mt-4">
						<Label :required="true">
							{{ $t('Unit Of Measurement') }}
						</Label>
						<Select filter v-model="unit.unit_of_measurement" :options="selects.filteredUnitOfMeasurements"
							optionLabel="title" optionValue="id" placeholder="" checkmark :highlightOnSelect="false"
							class="w-full md:w-56" />
					</div>
					<div class="col-md-2 mt-4">
						<Label :required="true"> {{ $t('Acquisition Cost') }} </Label>
						<InputNumber v-model="unit.acquisition_cost" :min="0" :minFractionDigits="0"
							:maxFractionDigits="0" suffix=" EGP" fluid />
					</div>
					<div class="col-md-2 mt-4 ">
						<Label :required="true">{{ $t('Acquisition Date') }}</Label>
						<VueDatePicker :max-date="new Date()" v-model="unit.acquisition_date" month-picker auto-apply
							format="MMM-yyyy">
						</VueDatePicker>
					</div>
					<div class="col-md-2 mt-4">
						<Label :required="true"> {{ $t('Book Value') }} </Label>
						<InputNumber v-model="unit.current_book_value" :min="0" :minFractionDigits="0"
							:maxFractionDigits="0" suffix=" EGP" fluid />
					</div>
					<div class="col-md-2 mt-4">
						<Label :required="true"> {{ $t('Book Value Date') }} </Label>
						<VueDatePicker :min-date="unit.acquisition_date" :max-date="new Date()"
							v-model="unit.book_value_date" month-picker auto-apply format="MMM-yyyy">
						</VueDatePicker>
					</div>
					<div class="col-md-2 mt-4">
						<Label :required="true"> {{ $t('Monthly Depreciation') }} </Label>
						<InputNumber v-model="unit.month_depreciation" :min="0" :minFractionDigits="0"
							:maxFractionDigits="0" suffix=" EGP" fluid />
					</div>
					<div class="col-md-2 mt-4">
						<Label :required="true"> {{ $t('Depreciation Duration (MTH)') }} </Label>
						<InputNumber v-model="unit.duration_in_months" :min="0" :minFractionDigits="0"
							:maxFractionDigits="0" suffix=" MTH" fluid />
					</div>
					<div class="col-md-1 ">
						<Label style="visibility:hidden" :required="true"> {{ $t('Depreciation Duration (MTH)') }}
						</Label>
						<button @click="openMarketValuesModal(Number(unitIndex))"
							class="btn btn-info btn-sm text-nowrap" type="button">
							{{ $t('Market Values') }}
						</button>
					</div>
					<div class="col-md-1 ">
						<Label style="visibility:hidden" :required="true"> {{ $t('Depreciation Duration (MTH)') }}
						</Label>
						<button @click="openTaxesModal(Number(unitIndex))" class="btn btn-info btn-sm text-nowrap"
							type="button">
							{{ $t('Tax Rates') }}
						</button>
					</div>
					<!-- Taxes Modal for Unit -->
					<div v-if="showTaxesModalForUnit === unitIndex" @click.self="closeTaxesModal"
						class="modal collection-modal fade show" style="padding-right: 15px; display: block"
						aria-modal="true">
						<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header " style="border-bottom: 1px solid green;">
									<h5 class="modal-title">{{ $t('Tax Rates') }} - {{ $t('Unit') }}
										{{ Number(unitIndex) + 1 }}
									</h5>
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
												<tr v-for="(tax, taxIndex) in model.units[unitIndex].tax_rates"
													:key="taxIndex">
													<td class="text-center">
														<button
															v-if="model.units[Number(unitIndex)].tax_rates.length > 1"
															@click="deleteTaxRow(Number(unitIndex), Number(taxIndex))"
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
											<button @click="addTaxRow(Number(unitIndex))" type="button"
												class="btn btn-primary btn-sm text-white">
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
					<!-- end Modal for Unit -->
					<!-- Market Values Modal for Unit -->
					<div v-if="showMarketValuesModalForUnit === unitIndex" @click.self="closeMarketValuesModal"
						class="modal collection-modal fade show" style="padding-right: 15px; display: block"
						aria-modal="true">
						<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header " style="border-bottom: 1px solid green;">
									<h5 class="modal-title">{{ $t('Market Values') }} - {{ $t('Unit') }}
										{{ unitIndex + 1 }}
									</h5>
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
												<tr v-for="(marketValue, marketValueIndex) in model.units[unitIndex].market_values"
													:key="marketValueIndex">
													<td class="text-center">
														<button v-if="Number(marketValueIndex) > 0"
															@click="deleteMarketValueRow(Number(unitIndex), Number(marketValueIndex))"
															type="button" class="btn btn-danger btn-md btn-danger-style"
															title="Delete">
															<i class="fas exclude-icon fa-trash trash-icon"></i>
														</button>
													</td>
													<td>
														<div>
															<InputNumber v-model="marketValue.value"
																:minFractionDigits="2" :maxFractionDigits="2"
																:step="100" :min="0" mode="decimal" showButtons
																suffix=" EGP" fluid />
														</div>
													</td>
													<td>
														<div class="">
															<VueDatePicker v-model="marketValue.date" month-picker
																auto-apply teleport="body" format="MMM-yyyy">
															</VueDatePicker>
														</div>
													</td>
												</tr>
											</tbody>
										</table>
										<div class="mt-3">
											<button @click="addMarketValueRow(Number(unitIndex))" type="button"
												class="btn btn-primary btn-sm text-white">
												<i
													class="fas fa-plus exclude-icon mr-2"></i>{{ $t('Add Market Value') }}
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
				<div class="row mt-3">
					<!-- end Market Values Modal for Unit -->
				</div>
			</div>
			<div class="row mt-3">
				<div class="col-md-12">
					<button @click="addUnitRow" type="button" class="btn btn-primary btn-sm text-white">
						<i class="fas fa-plus exclude-icon mr-2"></i>{{ $t('Add Unit') }}
					</button>
				</div>
			</div>
		</div>
	</div>
</template>
<style scoped>
.min-w-140 {
	min-width: 140px !important;
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

.unit-row {
	transition: all 0.3s ease;
}

.unit-row:hover {
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

:deep(.p-inputtext) {
	border: 1px solid #4d9afa !important;
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
