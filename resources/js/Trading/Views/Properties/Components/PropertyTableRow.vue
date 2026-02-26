<script setup lang="ts">
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import RadioButton from 'primevue/radiobutton';
import Select from 'primevue/select';
import SplitButton from 'primevue/splitbutton';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import Label from '../../../../components/Form/Label.vue';
import { useProperty } from '../../../composables/useProperty';
import type { Property } from '../../../stores/propertyStore';

const items = [
	{
		label: 'Add Rent Contract',
		command: () => {
			emit('contracts', props.property.id)
		}
	},
	{
		label: 'Add Due Installments',
		command: () => {
			openInstallmentModal(props.property.id)
		}
	},
	{
		label: 'Add Expense',
		command: () => {
			emit('addPropertyExpense', props.property.id)
		}
	},
	{
		label: 'Reports',
		command: () => {
			openReport(props.property)
		}
	},
	{
		label: 'Edit',
		command: () => {
			emit('edit', props.property.id)
		}
	},
	{
		separator: true
	},
	{
		label: 'Delete',
		command: () => {
			emit('delete', props.property.id)
		}
	}
];


import axios from 'axios';
interface Props {
	property: Property,
	currentActiveContract: string,
	emptyRows: EmptyRow,
}


const saveInstallment = (property: Property) => {

	const body = document.querySelector('body') as HTMLBodyElement
	const companyId = body.dataset.currentCompanyId
	const lang = body.dataset.lang
	const baseUrl = body.dataset.baseUrl

	axios.post(`${baseUrl}/${lang}/${companyId}/property-managements/Tradings/${property.id}/due-installments`, property)
		.then((response) => {
			showInstallmentModalId.value = 0
		})
		.catch((error) => {
			console.log(error)
		})
}

const props = defineProps<Props>()
const emit = defineEmits(['view', 'edit', 'delete', 'contracts', 'addPropertyExpense'])
const isActiveModal = ref<Property | null>(null)
const hideTablesDates = ref<TableDates>({
	rent_revenues: [],
})
const { formatCurrency, getPropertyTypeBadgeClass, getPropertyTypeLabel } = useProperty()

// Helper to check if property is unit/land type
const isUnitOrLand = () => {
	const type = props.property.nature_id
	return type === 'unit' || type === 'land'
}
const showInstallmentModalId = ref<number>(0);
const openInstallmentModal = (propertyId: number) => {
	showInstallmentModalId.value = propertyId
}
const openReport = (property: Property) => {
	isActiveModal.value = property
}

const handleFileUpload = async (event: Event) => {
	const target = event.target as HTMLInputElement
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
const addVariableInstallmentRow = (property: Property) => {

	property.dueInstallment.variable_installment_amounts.push({
		date: {
			month: new Date().getMonth(),
			year: new Date().getFullYear(),
		},
		amount: 0,
	})
}
const addNewRegularInstallmentRow = (property: Property) => {
	//	console.log('d', window.emptyRows.regular_installments_amounts)
	property.dueInstallment.regular_installments_amounts.push(...window.emptyRows.regular_installments_amounts)
}
const deleteRegularInstallmentRow = (property: Property, index: number) => {
	property.dueInstallment.regular_installments_amounts.splice(index, 1)
}
const deleteVariableInstallmentRow = (property: Property, index: number) => {
	property.dueInstallment.variable_installment_amounts.splice(index, 1)
}
</script>
<template>
	<tr data-repeater-style class="hover-row">
		<td class="text-center d-d-flex align-items-center justify-content-center">
			<span style="font-size:12px;"
				class="badge  w-100px h-100px d-flex align-items-center mx-auto justify-content-center"
				:class="getPropertyTypeBadgeClass(property)">
				{{ getPropertyTypeLabel(property) }}
			</span>
		</td>
		<td>
			<input :value="property.name" disabled class="form-control text-left" type="text">
		</td>
		<td>
			<input :value="property.code" disabled class="form-control text-left" type="text">
		</td>
		<!-- <td>
			<input :value="property.country" disabled class="form-control text-left" type="text">
		</td> -->
		<td>
			<input :value="property.categoryName" disabled class="form-control text-left" type="text">
		</td>
		<td>
			<input :value="property.typeName" disabled class="form-control text-left" type="text">
		</td>
		<td v-if="isUnitOrLand()">
			<input :value="property.status" disabled class="form-control text-center" type="text">
		</td>
		<td v-else>
			<span class="badge badge-info h-100px d-d-flex w-100 align-items-center justify-content-center">
				{{ property.units?.length || 0 }} {{ $t('Units') }}
			</span>
		</td>
		<td v-if="isUnitOrLand()">
			<input :value="formatCurrency(property.acquisition_cost) + ' EGP'" disabled class="form-control text-center"
				type="text">
		</td>
		<td v-else>
			<input
				:value="formatCurrency(property.units?.reduce((sum, u) => sum + Number(u.acquisition_cost), 0) || 0) + ' EGP'"
				disabled class="form-control text-center" type="text">
		</td>
		<td v-if="isUnitOrLand()">
			<input :value="formatCurrency(property.latest_market_value) + ' EGP'" disabled
				class="form-control text-center" type="text">
		</td>
		<td v-else>
			<input
				:value="formatCurrency(property.units?.reduce((sum, u) => sum + Number(u.latest_market_value), 0) || 0) + ' EGP'"
				disabled class="form-control text-center" type="text">
		</td>
		<td class="kt-datatable__cell--left kt-datatable__cell" data-field="Actions">
			<span style="overflow: visible; position: relative; width: 200px;display: flex;gap: 0.5rem;">
				<button type="button" @click="emit('view', property.id)" style="border: 1px solid green !important;"
					class="btn btn-success btn-outline-hover-success btn-icon success-btn-class exclude-btn"
					:title="$t('View Details')">
					<i class="fa fa-eye exclude-icon  default-icon-color"></i>
				</button>
				<SplitButton v-if="currentActiveContract !== 'all' && isUnitOrLand()" :label="$t('Actions')"
					:model="items" raised text severity="info">
				</SplitButton>
				<div v-if="isActiveModal == property" @click.self="isActiveModal = null" class="modal   fade show"
					id="fullWidthModal" style="display: block; padding-right: 15px; background: rgba(0,0,0,0.5);"
					tabindex="-1" role="dialog" aria-modal="true">
					<div class="modal-dialog modal-full-width  modal-dialog-centered modal-dialog-scrollable"
						role="document">
						<div class="modal-content " v-if="property">
							<div class="modal-header header-border">
								<h5 class="modal-title d-flex align-items-center gap-2">
									<i class="fa fa-building"></i> {{ property.name }}
								</h5>
								<button type="button" class="close" @click="isActiveModal = null">
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
														<template v-for="(dateFormatted, dateAsIndex) in property.dates"
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
															<!--  end Total Yr. 2026 for example -->
														</template>
													</tr>
												</thead>
												<tbody>
													<tr data-repeater-style v-if="property.contract"
														:key="'revenue_contract-' + property.contract.id">
														<td>
															<InputText :value="$t('Revenue')" :disabled="true"
																class="text-left min-w-300" fluid />
															<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
														</td>
														<template v-for="(dateFormatted, dateAsIndex) in property.dates"
															:key="dateAsIndex">
															<td
																v-if="!hideTablesDates.rent_revenues.includes(Number(dateAsIndex))">
																<div
																	class="d-flex flex-column min-w-160  align-items-center">
																	<InputNumber
																		v-model="property.contract.revenue_contract[dateAsIndex]"
																		:min="0" :disabled="true"
																		input-class="text-center" :minFractionDigits="0"
																		:maxFractionDigits="0" suffix=" EGP" fluid />
																	<i class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
																		title="Repeat Right"
																		style="visibility: hidden"></i>
																</div>
															</td>
															<!--  start Total Yr. 2026 for example -->
															<!--  end Total Yr. 2026 for example -->
														</template>
													</tr>
													<tr v-if="property.contract" data-repeater-style
														:key="'collection_contract-' + property.contract.id">
														<td>
															<InputText :value="$t('Collection')" :disabled="true"
																class="text-left min-w-300" fluid />
															<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
														</td>
														<template v-for="(dateFormatted, dateAsIndex) in property.dates"
															:key="dateAsIndex">
															<td
																v-if="!hideTablesDates.rent_revenues.includes(Number(dateAsIndex))">
																<div
																	class="d-flex flex-column min-w-160  align-items-center">
																	<InputNumber
																		v-model="property.contract.collection_contract[dateAsIndex]"
																		:min="0" :disabled="true"
																		input-class="text-center" :minFractionDigits="0"
																		:maxFractionDigits="0" suffix=" EGP" fluid />
																	<i class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
																		title="Repeat Right"
																		style="visibility: hidden"></i>
																</div>
															</td>
															<!--  start Total Yr. 2026 for example -->
															<!--  end Total Yr. 2026 for example -->
														</template>
													</tr>
													<tr data-repeater-style
														v-if="property.due_installment && property.due_installment.length"
														:key="'dueInstallment-' + property.id">
														<td>
															<InputText :value="$t('Due Installments')" :disabled="true"
																class="text-left min-w-300" fluid />
															<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
														</td>
														<template v-for="(dateFormatted, dateAsIndex) in property.dates"
															:key="dateAsIndex">
															<td
																v-if="!hideTablesDates.rent_revenues.includes(Number(dateAsIndex))">
																<div
																	class="d-flex flex-column min-w-160  align-items-center">
																	<InputNumber
																		v-model="property.due_installment[dateAsIndex]"
																		:min="0" :disabled="true"
																		input-class="text-center" :minFractionDigits="0"
																		:maxFractionDigits="0" suffix=" EGP" fluid />
																	<i class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
																		title="Repeat Right"
																		style="visibility: hidden"></i>
																</div>
															</td>
															<!--  start Total Yr. 2026 for example -->
															<!--  end Total Yr. 2026 for example -->
														</template>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
								</div>
								<!-- <div >
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex justify-content-center align-items-center"> No Contract
												Found </div>
										</div>
									</div>
								</div> -->
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" @click="isActiveModal = null">
									<i class="fa fa-times exclude-icon default-icon-color"></i>
									{{ $t('Close') }}
								</button>
							</div>
						</div>
					</div>
				</div>
				<div v-if="showInstallmentModalId === property.id" @click.self="closeInstallmentModal"
					class="modal collection-modal fade show" style="padding-right: 15px; display: block;z-index:3"
					aria-modal="true">
					<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header " style="border-bottom: 1px solid green;">
								<h5 class="modal-title"> {{ $t('Due Installments') }} - {{ property.name }}
								</h5>
								<button type="button" class="close" @click="closeInstallmentModal">
									<span aria-hidden="true">×</span>
								</button>
							</div>
							<div class="modal-body" style="overflow-y: scroll;height:80vh">
								<div class="row">
									<div class="col-md-3 mb-3">
										<Label :required="true">{{ $t('Delivery Date') }}</Label>
										<VueDatePicker v-model="property.dueInstallment.delivery_date" month-picker
											auto-apply teleport="body" />
									</div>
									<div class="col-md-3 mb-3">
										<Label :required="true">{{ $t('Ready To Use Date') }}</Label>
										<VueDatePicker v-model="property.dueInstallment.ready_to_use_date" month-picker
											auto-apply teleport="body" />
									</div>
									<!-- Installment Type Selection -->
									<div class="col-md-12 mb-4">
										<Label :required="true">{{ $t('Installment Type') }}</Label>
										<div class="d-flex " style="gap: 2rem;margin-top: 1.4rem;">
											<div class="d-flex align-items-center mb-2">
												<RadioButton v-model="property.dueInstallment.installment_type"
													inputId="regular" value="regular" />
												<label for="regular"
													class="ml-2 mb-0">{{ $t('Regular Installment') }}</label>
											</div>
											<div class="d-flex align-items-center">
												<RadioButton v-model="property.dueInstallment.installment_type"
													inputId="variable" value="variable" />
												<label for="variable"
													class="ml-2 mb-0  ">{{ $t('Variable Installment') }}</label>
											</div>
										</div>
									</div>
									<!-- Regular Installment Fields -->
									<template v-if="property.dueInstallment.installment_type === 'regular'">
										<div class="col-md-3 mb-3">
											<Label :required="true">{{ $t('Contract Signing Payment') }}</Label>
											<InputNumber v-model="property.dueInstallment.signing_payment" :min="0"
												:minFractionDigits="0" :maxFractionDigits="0" :suffix="' EGP'" fluid />
										</div>
										<div class="col-md-3 mb-3">
											<Label :required="true">{{ $t('Date') }}</Label>
											<VueDatePicker v-model="property.dueInstallment.signing_payment_date"
												month-picker auto-apply teleport="body" />
										</div>
										<div class="col-md-3 mb-3">
											<Label :required="true">{{ $t('Reservation Payment') }}</Label>
											<InputNumber v-model="property.dueInstallment.reservation_payment" :min="0"
												:minFractionDigits="0" :maxFractionDigits="0" :suffix="' EGP'" fluid />
										</div>
										<div class="col-md-3 mb-3">
											<Label :required="true">{{ $t('Date') }}</Label>
											<VueDatePicker v-model="property.dueInstallment.reservation_payment_date"
												month-picker auto-apply teleport="body" />
										</div>
										<template
											v-for="(installmentAmountItem, index) in property.dueInstallment.regular_installments_amounts"
											:key="index">
											<div class="col-md-3 mb-3 mt-4">
												<Label :required="true">{{ $t('Installment Amount') }}</Label>
												<InputNumber v-model="installmentAmountItem.amount" :min="0"
													:minFractionDigits="0" :maxFractionDigits="0" :suffix="' EGP'"
													fluid />
											</div>
											<div class="col-md-2 mb-3 mt-4">
												<Label :required="true">{{ $t('Count') }}</Label>
												<InputNumber v-model="installmentAmountItem.installment_count" :min="1"
													fluid />
											</div>
											<div class="col-md-3 mb-3 mt-4">
												<Label :required="true">{{ $t('Start Date') }}</Label>
												<VueDatePicker " v-model="installmentAmountItem.start_date"
													month-picker auto-apply teleport="body" />
											</div>
											<div class="col-md-3  mb-3 mt-4">
												<Label :required="true">{{ $t('Payment Interval') }}</Label>
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
													]" optionLabel="title" optionValue="id" checkmark :highlightOnSelect="false" class="w-full md:w-56" />
											</div>
											<div class="col-md-1">
												<Label style="visibility: hidden;"
													:required="true">{{ $t('Delete Regular') }}</Label>
												<button v-if="index > 0"
													@click="deleteRegularInstallmentRow(property, index)" type="button"
													class="btn btn-danger btn-sm" title="Delete">
													<i class="fa fa-trash exclude-icon default-icon-color"></i>
												</button>
											</div>
										</template>
										<div class="col-md-12 mb-3">
											<Button @click="addNewRegularInstallmentRow(property)"
												:label="$t('Add Row')" icon="pi pi-plus"
												class="p-button-sm p-button-success background-green  mt-4"
												type="button" />
										</div>
										<!-- Optional Delivery Payments Section -->
										<div class="col-md-12 mb-3 mt-2">
											<div class="form-check">
												<input v-model="property.dueInstallment.has_annually_installments"
													class="form-check-input" type="checkbox" id="showAnnualCheck" />
												<label class="form-check-label font-size-14 font-weight-bold"
													for="showAnnualCheck">
													{{ $t('Add Annual Installments') }}
												</label>
											</div>
										</div>
										<template v-if="property.dueInstallment.has_annually_installments">
											<!-- <div class="col-md-12 mb-2">
												<h5>{{ $t('Annual Installments') }}</h5>
											</div> -->
											<div class="col-md-3 mb-3">
												<Label>{{ $t('Start Date') }}</Label>
												<VueDatePicker v-model="property.dueInstallment.annual_start_date"
													month-picker auto-apply teleport="body" />
											</div>
											<div class="col-md-3 mb-3">
												<Label>{{ $t('Annual Amount') }}</Label>
												<InputNumber v-model="property.dueInstallment.annual_amount" :min="0"
													:minFractionDigits="0" :maxFractionDigits="0" :suffix="' EGP'"
													fluid />
											</div>
											<div class="col-md-3 mb-3">
												<Label>{{ $t('Annual Count') }}</Label>
												<InputNumber v-model="property.dueInstallment.annual_count" :min="0"
													fluid />
											</div>
										</template>
										<!-- Optional Delivery Payments Section -->
										<div class="col-md-12 mb-3 mt-2">
											<div class="form-check">
												<input v-model="property.dueInstallment.has_delivery_payments"
													class="form-check-input" type="checkbox" id="showDeliveryCheck" />
												<label class="form-check-label font-size-14 font-weight-bold"
													for="showDeliveryCheck">
													{{ $t('Add Delivery Payments') }}
												</label>
											</div>
										</div>
										<template v-if="property.dueInstallment.has_delivery_payments">
											<div class="col-md-3 mb-3">
												<Label>{{ $t('Start Date') }}</Label>
												<VueDatePicker
													v-model="property.dueInstallment.delivery_payments_start_date"
													month-picker auto-apply teleport="body" />
											</div>
											<div class="col-md-3 mb-3">
												<Label>{{ $t('Amount') }}</Label>
												<InputNumber v-model="property.dueInstallment.delivery_payments_amount"
													:min="0" :minFractionDigits="0" :maxFractionDigits="0"
													:suffix="' EGP'" fluid />
											</div>
											<div class="col-md-3 mb-3">
												<Label>{{ $t('Count') }}</Label>
												<InputNumber v-model="property.dueInstallment.delivery_payments_count"
													:min="0" fluid />
											</div>
											{{ property.delivery_payments_payment_interval }}
											<div class="col-md-3  mb-3">
												<Label :required="true">{{ $t('Payment Interval') }}</Label>
												<Select filter
													v-model="property.dueInstallment.delivery_payments_payment_interval"
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
													]" optionLabel="title" optionValue="id" checkmark :highlightOnSelect="false" class="w-full md:w-56" />
											</div>
										</template>
										<!-- Optional Delivery Payments Section -->
										<div class="col-md-12 mb-3 mt-2">
											<div class="form-check">
												<input v-model="property.dueInstallment.has_maintenance_payments"
													class="form-check-input" type="checkbox"
													id="showMaintenanceCheck" />
												<label class="form-check-label font-size-14 font-weight-bold"
													for="showMaintenanceCheck">
													{{ $t('Add Maintenance Payments') }}
												</label>
											</div>
										</div>
										<template v-if="property.dueInstallment.has_maintenance_payments">
											<div class="col-md-3 mb-3">
												<Label>{{ $t('Start Date') }}</Label>
												<VueDatePicker
													v-model="property.dueInstallment.maintenance_payments_start_date"
													month-picker auto-apply teleport="body" />
											</div>
											<div class="col-md-3 mb-3">
												<Label>{{ $t('Amount') }}</Label>
												<InputNumber
													v-model="property.dueInstallment.maintenance_payments_amount"
													:min="0" :minFractionDigits="0" :maxFractionDigits="0"
													:suffix="' EGP'" fluid />
											</div>
											<div class="col-md-3 mb-3">
												<Label>{{ $t('Count') }}</Label>
												<InputNumber
													v-model="property.dueInstallment.maintenance_payments_count"
													:min="0" fluid />
											</div>
											<div class="col-md-3  mb-3">
												<Label :required="true">{{ $t('Payment Interval') }}</Label>
												<Select filter
													v-model="property.dueInstallment.maintenance_payments_payment_interval"
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
													]" optionLabel="title" optionValue="id" checkmark :highlightOnSelect="false" class="w-full md:w-56" />
											</div>
										</template>
									</template>
									<!-- Variable Installment Fields -->
									<template v-else-if="property.dueInstallment.installment_type === 'variable'">
										<div class="col-md-12 mb-3">
											<div class="d-flex justify-content-between align-items-center mb-3">
												<h5>{{ $t('Installment Details') }}</h5>
												<div>
													<label for="excelUpload" class="btn text-white btn-sm btn-info">
														<i
															class="pi text-white pi-upload me-1 exclude-icon default-icon-color"></i>
														{{ $t('Upload Excel') }}
													</label>
													<input id="excelUpload" type="file" accept=".xlsx,.xls"
														@change="handleFileUpload" style="display: none" />
												</div>
											</div>
											<div v-for="(variableInstallmentAmount, index) in property.dueInstallment.variable_installment_amounts"
												:key="index" class="row mb-3 align-items-end">
												<div class="col-md-3">
													<Label :required="true">{{ $t('Date') }}</Label>
													<VueDatePicker v-model="variableInstallmentAmount.date" month-picker
														auto-apply teleport="body" />
												</div>
												<div class="col-md-3">
													<Label :required="true">{{ $t('Amount') }}</Label>
													<InputNumber v-model="variableInstallmentAmount.amount" :min="0"
														:minFractionDigits="0" :maxFractionDigits="0" :suffix="' EGP'"
														fluid />
												</div>
												<div class="col-md-1">
													<button
														v-if="property.dueInstallment.variable_installment_amounts.length > 1"
														@click="deleteVariableInstallmentRow(property, index)"
														type="button" class="btn btn-danger btn-sm" title="Delete">
														<i class="fa fa-trash exclude-icon default-icon-color"></i>
													</button>
												</div>
											</div>
											<Button @click="addVariableInstallmentRow(property)" :label="$t('Add Row')"
												icon="pi pi-plus"
												class="p-button-sm p-button-success background-green  mt-4"
												type="button" />
										</div>
									</template>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-primary" @click="saveInstallment(property)">
									{{ $t('Save') }}
								</button>
							</div>
						</div>
					</div>
				</div>
				<!-- <button v-if="currentActiveContract !== 'all'" type="button" @click="openInstallmentModal(property.id)"
					class="btn btn-secondary btn-outline-hover-brand btn-icon edit-btn-class exclude-btn"
					:title="$t('Add Due Installments')">
					<i class="fa fa-money-check-alt exclude-icon default-icon-color"></i>
				</button> -->
				<!-- <button v-if="currentActiveContract !== 'all'" type="button" "
					class=" btn btn-secondary btn-outline-hover-brand btn-icon edit-btn-class exclude-btn" :title="$t('Reports')">
					<i class="fa fa-book exclude-icon default-icon-color"></i>
				</button> -->
				<!-- <button v-if="currentActiveContract !== 'all'" type="button" @click="emit('delete', property.id)"
					class="btn delete-btn-class btn-secondary btn-outline-hover-danger btn-icon exclude-btn"
					:title="$t('Delete')">
					<i class="fa fa-trash-alt exclude-icon default-icon-color"></i>
				</button> -->
			</span>
		</td>
	</tr>
</template>
<style scoped>
.hover-row:hover {
	background-color: #f8f9fa;
}

.text-success {
	color: #28a745 !important;
}

.font-weight-bold {
	font-weight: 600 !important;
}

.w-100px {
	width: 100px !important;
}

.h-100px {
	height: 30px !important;
}

.background-green {
	background-color: green !important;
}

.background-green:hover {
	background-color: white !important;
	color: green !important;
}

.font-size-14 {
	font-size: 15px !important;
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

.min-w-160 {
	min-width: 160px !important;
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
</style>
