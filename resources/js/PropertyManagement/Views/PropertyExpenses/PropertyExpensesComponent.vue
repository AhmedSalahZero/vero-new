<template>
	<div>
		<div v-if="!isLoading" class="row">
			<div class="col-md-12">
				<div class="kt-portlet">
					<div class="kt-portlet__body">
						<div class="row">
							<div class="col-md-12 mb-4">
								<div class="row row-no-padding row-col-separator-xl">
									<div class="col-md-6 col-lg-4 col-xl-4">
										<div class="kt-widget24 text-center pb-0">
											<div class="kt-widget24__details">
												<div class="kt-widget24__info">
													<h4
														class="kt-widget24__title font-size text-nowrap black-card-title-css">
														{{ $t('Rent Revenue (To Date)') }}
													</h4>
												</div>
											</div>
											<div class="kt-widget24__details">
												<span class="kt-widget24__stats kt-font-brand">
													{{ formatNumber(rentRevenueSumToDate) }} EGP </span>
											</div>
											<div class="progress progress--sm">
												<div class="progress-bar kt-bg-brand" role="progressbar"
													style="width: 100%;" aria-valuenow="100" aria-valuemin="0"
													aria-valuemax="100"></div>
											</div>
										</div>
									</div>
									<div class="col-md-6 col-lg-4 col-xl-4">
										<div class="kt-widget24 text-center pb-0">
											<div class="kt-widget24__details">
												<div class="kt-widget24__info">
													<h4
														class="kt-widget24__title font-size text-nowrap black-card-title-css">
														{{ $t('Rent Collection (To Date)') }}
													</h4>
												</div>
											</div>
											<div class="kt-widget24__details">
												<span class="kt-widget24__stats kt-font-success">
													{{ formatNumber(rentCollectionSumToDate) }} EGP </span>
											</div>
											<div class="progress progress--sm">
												<div class="progress-bar kt-bg-success" role="progressbar"
													style="width: 100%;" aria-valuenow="100" aria-valuemin="0"
													aria-valuemax="100"></div>
											</div>
										</div>
									</div>
									<div class="col-md-6 col-lg-4 col-xl-4">
										<div class="kt-widget24 text-center pb-0">
											<div class="kt-widget24__details">
												<div class="kt-widget24__info">
													<h4
														class="kt-widget24__title font-size text-nowrap black-card-title-css">
														{{ $t('Property Expenses Total') }}
													</h4>
												</div>
											</div>
											<div class="kt-widget24__details">
												<span class="kt-widget24__stats kt-font-warning">
													{{ formatNumber(totalPropertyExpenses) }} EGP </span>
											</div>
											<div class="progress progress--sm">
												<div class="progress-bar kt-bg-warning" role="progressbar"
													style="width: 100%;" aria-valuenow="100" aria-valuemin="0"
													aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="kt-portlet">
					<div class="kt-portlet__body exclude">
						<!-- start tabs -->
						<Loading :isLoading="isLoading"></Loading>
						<div v-if="!isLoading">
							<!-- Rent revenue & collection cards (latest running contract, to date) -->
							<!-- start fixed monthly repeating  -->
							<div class="col-md-12 py-3">
								<div v-for="(propertyExpense, index) in model.sub_items" :key="propertyExpense.id"
									class="row main-row-style mb-4">
									<div class="col-md-2 col">
										<Label :required="false">{{ $t('Expense Category') }}</Label>
										<Select filter v-model="propertyExpense.expense_category"
											:options="expenseCategories"
											@change="updateExpenseNamePerCategories(propertyExpense)"
											optionLabel="title" optionValue="id" placeholder="" checkmark
											:highlightOnSelect="false" class="w-full md:w-56" />
									</div>
									<div class="col-md-2 col">
										<Label :required="false">{{ $t('Expense Name') }}</Label>
										<Select filter v-model="propertyExpense.expense_name_id"
											:options="propertyExpense.filteredExpenseNamesOptions" optionLabel="title"
											optionValue="id" placeholder="" checkmark :highlightOnSelect="false"
											class="w-full md:w-56" />
									</div>
									<div class="col-md-1 col min-w-180">
										<Label :required="false">{{ $t('Date') }}</Label>
										<VueDatePicker v-model="propertyExpense.date" auto-apply text-input
											teleport="body"></VueDatePicker>
									</div>
									<div class="col-md-1 col">
										<Label :required="false">{{ $t('Amount') }}</Label>
										<InputNumber v-model="propertyExpense.amount" :min="0" :minFractionDigits="0"
											:maxFractionDigits="0" suffix=" EGP" fluid />
									</div>
									<div style="min-width:75px !important;max-width:75px"
										class="col-md-1 col d-flex justify-content-center align-items-center">
										<div class="d-flex flex-column justify-content-center align-items-center">
											<Label class="mb-2" :required="false">{{ $t('Is Paid') }} </Label>
											<Checkbox size="large" v-model="propertyExpense.is_paid" :binary="true" />
										</div>
									</div>
									<div class="col-md-1 col min-w-180">
										<Label :required="false">{{ $t('Paid Date') }}</Label>
										<VueDatePicker :disabled="!propertyExpense.is_paid"
											v-model="propertyExpense.payment_date" auto-apply text-input
											teleport="body">
										</VueDatePicker>
									</div>
									<div class="col-md-3 col min-w-485">
										<Label :required="false">{{ $t('Note') }}</Label>
										<Textarea v-model="propertyExpense.note" :rows="10" fluid />
									</div>
									<div class="max-w-trash col-md-1">
										<Label style="visibility: hidden">ddd</Label>
										<div :style="{ visibility: index == 0 ? 'hidden' : 'visible' }"
											class="d-flex flex-column justify-content-start align-items-start">
											<!-- <label style="visibility: hidden">Delete</label> -->
											<button @click="deleteRepeaterRow(index)" type="button"
												class="btn btn-danger btn-md btn-danger-style ml-2" title="Delete">
												<i class="fas exclude-icon fa-trash trash-icon"></i>
											</button>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-12" v-if="!inEditMode">
								<div class="row">
									<div class="col-md-6">
										<input @click="addNewItem" data-repeater-create="" type="button"
											class="btn btn-primary btn-sm text-white mb-4" :value="$t('Add Expense')" />
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
							v-html="disableSubmitBtn && model.submit_button == 'save' ? 'Saving...' : 'Save '">
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
							: 'Save & Go To Units'
							">
						</span>
					</button>
				</div>
			</div>
		</div>
		<div class="row mt-4">
			<div class="col-md-12">
				<div class="kt-portlet  ">
					<div class="kt-portlet__body exclude pt-0">
						<div class="tab-content kt-margin-t-20">
							<div class="tab-pane active" role="tabpanel">
								<div class="kt-portlet kt-portlet--mobile">
									<!-- Search Button -->
									<div class="kt-portlet__head kt-portlet__head--lg p-0">
										<div class="kt-portlet__head-label">
											<button @click="showSearch = true"
												class="btn btn-secondary btn-bold btn-sm mb-2">
												<i class="fa fa-search"></i> {{ $t('Search') }}
											</button>
											<button v-if="searchQuery" @click="handleClearSearch"
												class="btn btn-danger btn-bold btn-sm mb-2 ml-2">
												<i class="fa fa-times exclude-icon default-icon-color"></i>
												{{ $t('Clear') }}
											</button>
										</div>
										<div class="kt-portlet__head-toolbar" v-if="searchQuery">
											<span class="badge badge-info p-2">
												{{ $t('Searching') }}: {{ searchField }} = "{{ searchQuery }}" </span>
										</div>
									</div>
									<!-- Table -->
									<div class="kt-portlet__body p-0">
										<div class="tab-content kt-margin-t-20">
											<div class="table-responsive">
												<table class="table table-white repeater-class repeater ">
													<!-- <table class="table table-striped table-bordered table-hover table-checkable"> -->
													<thead>
														<tr>
															<th class=" header-border-down text-center"> # </th>
															<th class=" header-border-down text-center">
																{{ $t('Expense Category') }}
															</th>
															<th class="header-border-down text-center">
																{{ $t('Expense Name') }}
															</th>
															<th class="min-w-160 header-border-down text-center">
																{{ $t('Date') }}
															</th>
															<!-- <th class=" header-border-down text-center">
													{{ $t('Country') }}
												</th> -->
															<th class=" header-border-down text-center">
																{{ $t('Amount') }}
															</th>
															<th class=" header-border-down text-center">
																{{ $t('Is Paid') }}
															</th>
															<th class=" header-border-down text-center">
																{{ $t('Paid Date') }}
															</th>
															<th class=" header-border-down text-center">
																{{ $t('Note') }}
															</th>
															<th class=" header-border-down text-center">
																{{ $t('Actions') }}
															</th>
														</tr>
													</thead>
													<tbody>
														<tr v-if="paginatedRows.length === 0">
															<td colspan="9" class="text-center py-5">
																<i
																	class="fa fa-inbox fa-3x text-muted mb-3 d-block"></i>
																<p class="text-muted">{{ $t('No properties found') }}
																</p>
															</td>
														</tr>
														<tr v-for="row in paginatedRows" :key="row.id"
															data-repeater-style class="hover-row">
															<td
																class="text-center d-d-flex align-items-center justify-content-center">
																<span style="font-size:12px;"
																	class="badge  w-100px h-100px d-flex align-items-center mx-auto justify-content-center">
																	1 </span>
															</td>
															<td>
																<input :value="row.expense_category_name" disabled
																	class="form-control text-left text-capitalize"
																	type="text">
															</td>
															<td>
																<input :value="row.expense_name" disabled
																	class="form-control text-left" type="text">
															</td>
															<td>
																<input :value="row.dateFormatted" disabled
																	class="form-control text-left" type="text">
															</td>
															<td>
																<input :value="row.amount" disabled
																	class="form-control text-left" type="text">
															</td>
															<td>
																<input :value="row.is_paid" disabled
																	class="form-control text-center" type="text">
															</td>
															<td>
																<input :value="row.payment_date" disabled
																	class="form-control text-center" type="text">
															</td>
															<td>
																<input :value="row.note" disabled
																	class="form-control text-center" type="text">
															</td>
															<td class="kt-datatable__cell--left kt-datatable__cell"
																data-field="Actions">
																<span
																	style="overflow: visible; position: relative; width: 200px;display: flex;gap: 0.5rem;">
																	<SplitButton :label="$t('Actions')"
																		:model="actionItems(row)" raised text
																		severity="info">
																	</SplitButton>
																</span>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
											<!-- Pagination -->
											<div class="kt-pagination kt-pagination--brand" v-if="totalPages > 1">
												<ul class="kt-pagination__links">
													<li class="kt-pagination__link--first"
														:class="{ 'kt-pagination__link--disabled': currentPage === 1 }">
														<a @click.prevent="goToPage(1)" href="#"><i
																class="fa fa-angle-double-left"></i></a>
													</li>
													<li class="kt-pagination__link--prev"
														:class="{ 'kt-pagination__link--disabled': currentPage === 1 }">
														<a @click.prevent="goToPage(currentPage - 1)" href="#"><i
																class="fa fa-angle-left"></i></a>
													</li>
													<li v-for="page in totalPages" :key="page"
														:class="{ 'kt-pagination__link--active': page === currentPage }">
														<a @click.prevent="goToPage(page)" href="#">{{ page }}</a>
													</li>
													<li class="kt-pagination__link--next"
														:class="{ 'kt-pagination__link--disabled': currentPage === totalPages }">
														<a @click.prevent="goToPage(currentPage + 1)" href="#"><i
																class="fa fa-angle-right"></i></a>
													</li>
													<li class="kt-pagination__link--last"
														:class="{ 'kt-pagination__link--disabled': currentPage === totalPages }">
														<a @click.prevent="goToPage(totalPages)" href="#"><i
																class="fa fa-angle-double-right"></i></a>
													</li>
												</ul>
												<div class="kt-pagination__toolbar">
													<span class="pagination__desc">
														{{ $t('Showing') }} {{ ((currentPage - 1) * perPage) + 1 }} -
														{{ Math.min(currentPage * perPage, currentTabProperties.length) }}
														{{ $t('of') }} {{ currentTabProperties.length }}
													</span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<DeleteConfirmModal :show="showDeleteConfirm" :property-name="currentDeleteRow?.name"
							:is-deleting="isDeleting" @close="onCloseDeleteModal" @confirm="handleConfirmDelete" />
						<SearchModal :show="showSearch" :initial-search-field="searchField"
							:initial-search-query="searchQuery" @close="showSearch = false" @search="handleSearch" />
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script setup>
import Checkbox from 'primevue/checkbox'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import SplitButton from 'primevue/splitbutton'
import Textarea from 'primevue/textarea'
import Loading from '../../../components/Common/Loading.vue'
import DeleteConfirmModal from './Models/DeleteConfirmModal.vue'
import SearchModal from './Models/SearchModal.vue'
// import VueLoadingTemplate from 'vue-loading-template';
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

import Label from '../../../components/Form/Label.vue'
// import TextInput from "../Form/TextInput.vue";
const isLoading = ref(true)
const showSearch = ref(false)

const actionItems = (row) => [
	// {
	// 	label: 'Add Rent Contract',
	// 	command: () => {
	// 		emit('contracts', props.property.id)
	// 	}
	// },
	// {
	// 	label: 'Add Due Installments',
	// 	command: () => {
	// 		openInstallmentModal(props.property.id)
	// 	}
	// },
	// {
	// 	label: 'Add Expense',
	// 	command: () => {
	// 		emit('addPropertyExpense', props.property.id)
	// 	}
	// },
	// {
	// 	label: 'Reports',
	// 	command: () => {
	// 		// openInstallmentModal(props.property.id)
	// 	}
	// },
	{
		label: 'Edit',
		command: () => {
			handleEditRow(row)
		}
	},
	{
		separator: true
	},
	{
		label: 'Delete',
		command: () => {
			currentDeleteRow.value = row
			showDeleteConfirm.value = true
		}
	}
]
const currentDeleteRow = ref(null)
const currentEditRow = ref(null)
const inEditMode = ref(false)
const emptyRow = ref({})
const handleEditRow = (row) => {
	inEditMode.value = true
	model.value.in_edit_mode = true
	currentEditRow.value = row
	model.value.sub_items = []
	console.log(row)
	model.value.sub_items.push(row)
	updateExpenseNamePerCategories(row, row.expense_name_id)
}

const isDeleting = ref(false)
const showDeleteConfirm = ref(false)
const currentPage = ref(1)
const perPage = ref(25)
const searchQuery = ref('')
const searchField = ref('expense_name')
const filteredRows = computed(() => {
	const query = (searchQuery.value || '').trim().toLowerCase()
	const field = searchField.value
	if (!query) return rows.value
	return rows.value.filter((row) => {
		const val = row[field]
		if (val == null) return false
		if (field === 'is_paid') {
			const paid = !!val
			const matchTrue = ['1', 'true', 'yes', 'نعم'].includes(query)
			const matchFalse = ['0', 'false', 'no', 'لا'].includes(query)
			return (paid && matchTrue) || (!paid && matchFalse)
		}
		return String(val).toLowerCase().includes(query)
	})
})
const paginatedRows = computed(() => {
	const start = (currentPage.value - 1) * perPage.value
	const end = start + perPage.value
	return filteredRows.value.slice(start, end)
})
const getBaseUrl = () => {
	const body = document.querySelector('body')
	return {
		baseUrl: body.dataset.baseUrl,
		companyId: body.dataset.currentCompanyId,
		lang: body.dataset.lang,
		csrfToken: body.dataset.token,
	}
}


const deleteRow = async (rowId) => {
	const { baseUrl, companyId, lang, csrfToken } = getBaseUrl()
	const propertyId = window.location.pathname.split('/').slice(-2, -1)[0]
	const deleteUrl = `${baseUrl}/${lang}/${companyId}/property-managements/properties/${propertyId}/property-expenses/${rowId}/destroy`

	try {
		const response = await axios.delete(deleteUrl, {
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				Accept: 'application/json',
			},
		})

		const index = rows.value.findIndex((r) => r.id === rowId)
		if (index !== -1) rows.value.splice(index, 1)

		// await Swal.fire({
		// 	icon: 'success',
		// 	title: 'Success',
		// 	text: response.data.message || 'Property deleted successfully',
		// 	timer: 2000,
		// })

		return true
	} catch (error) {
		const errorMessage = error.response?.data?.message || 'Error deleting property'
		await Swal.fire({
			icon: 'error',
			title: 'Oops...',
			text: errorMessage,
		})
		return false
	}
}
const onCloseDeleteModal = () => {
	showDeleteConfirm.value = false
	currentDeleteRow.value = null
}

const handleConfirmDelete = async () => {
	const row = currentDeleteRow.value
	if (!row?.id) return
	isDeleting.value = true
	const success = await deleteRow(row.id)
	isDeleting.value = false
	if (success) {
		showDeleteConfirm.value = false
		currentDeleteRow.value = null
		selectedProperty.value = null
	}
}
const totalPages = computed(() => {
	return Math.ceil(filteredRows.value.length / perPage.value)
})

const totalPropertyExpenses = computed(() => {
	return (rows.value || []).reduce((sum, row) => sum + (Number(row.amount) || 0), 0)
})

const handleClearSearch = () => {
	searchQuery.value = ''
	searchField.value = 'expense_name'
	currentPage.value = 1
}
const handleSearch = ({ field, query }) => {
	searchField.value = field
	searchQuery.value = query
	currentPage.value = 1
}
const goToPage = (page) => {
	if (page >= 1 && page <= totalPages.value) {
		currentPage.value = page
	}
}


// modals.increaseRate.currentActive = null

let expenseNamesPerCategories = []

const updateExpenseNamePerCategories = (item, expenseNameId = null) => {
	item.expense_name_id = expenseNameId
	item.filteredExpenseNamesOptions = expenseNamesPerCategories[item.expense_category] || []
}
const disableSubmitBtn = ref(false)
const model = ref({})
const expenseCategories = ref([])
const submitUrl = ref(null)
const rows = ref([])
const rentRevenueSumToDate = ref(0)
const rentCollectionSumToDate = ref(0)

const formatNumber = (num) => {
	if (num == null || Number.isNaN(Number(num))) return '0'
	return Number(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })
}

const getModelData = () => {
	const body = document.querySelector('body')

	const csrfToken = body.dataset.token
	const baseUrl = body.dataset.baseUrl
	const companyId = body.dataset.currentCompanyId
	const propertyId = window.location.pathname.split('/').slice(-2, -1)[0]
	const lang = body.dataset.lang
	const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/property-managements/properties/${propertyId}/property-expenses-old-data`
	axios
		.get(fetchOldDataUrl, {
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				Accept: 'application/json',
			},
		})
		.then((response) => {
			model.value = response.data.model
			rows.value = response.data.rows
			emptyRow.value = response.data.emptyRow
			rentRevenueSumToDate.value = response.data.rentRevenueSumToDate ?? 0
			rentCollectionSumToDate.value = response.data.rentCollectionSumToDate ?? 0

			expenseCategories.value = response.data.expenseCategories
			expenseNamesPerCategories = response.data.expenseNamesPerCategories
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





const submitForm = (e) => {
	model.value.submit_button = e.target.getAttribute('data-button-value')
	disableSubmitBtn.value = true
	const body = document.querySelector('body')
	const csrfToken = body.dataset.token
	console.log(model.value)
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


const addNewItem = () => {
	return model.value.sub_items.push({ ...emptyRow.value })
}
const deleteRepeaterRow = (index) => {
	model.value.sub_items.splice(index, 1)
}

</script>
<style scoped>
/* Match balances form / money-card styling */
.black-card-title-css {
	color: black !important;
	font-weight: 600 !important;
	font-size: 18px !important;
}

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

.min-w-180 {
	min-width: 180px !important;
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

.w-50 {
	width: 50px !important;
}

.min-w-485 {
	min-width: 485px !important;
}
</style>
<style scoped></style>
