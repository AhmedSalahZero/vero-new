<script setup>
import axios from 'axios'
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'

const props = defineProps({
	/** Matches layout body data-lang / Blade $lang for with-two-dates margins */
	appLang: { type: String, default: '' },
	companyId: { type: Number, required: true },
	defaultActiveTab: { type: String, default: 'cheque' },
	jsonUrl: { type: String, required: true },
	createUrl: { type: String, default: '' },
	/** Same as Blade: create.money.receive?type=down-payment */
	createDownPaymentUrl: { type: String, default: '' },
	canCreate: { type: Boolean, default: false },
	initialFilterDates: { type: Object, default: () => ({}) },
	/** field key => label (server-translated), keyed by tab — same as legacy export-money modal */
	searchFieldsByTab: { type: Object, default: () => ({}) },
	advancedFilterUi: { type: Object, default: () => ({}) },
	/** Tab key => translated title (same as Blade index nav + table section title) */
	tabTitles: { type: Object, default: () => ({}) },
})

/* Dashboard layout uses body data-token=csrf_token(); no meta[name=csrf-token] on many pages */
const csrf = () =>
	document.querySelector('meta[name="csrf-token"]')?.content
	|| document.body?.dataset?.token
	|| ''

/** Merged from props + JSON API (fixes empty dataset / encoding issues). */
const searchFieldsByTabState = ref({ ...props.searchFieldsByTab })
const advancedFilterUiState = ref({ ...props.advancedFilterUi })

/** Tab keys + icons; visible titles come from server tabTitles (__()) */
const TABS = [
	{ key: 'cheque', label: 'Cheques In Safe', icon: 'fa-money-check-alt' },
	{ key: 'cheque-under-collection', label: 'Cheques Under Collection', icon: 'fa-money-check-alt' },
	{ key: 'cheque-collected', label: 'Collected Cheques', icon: 'fa-money-check-alt' },
	{ key: 'cheque-rejected', label: 'Rejected Cheques', icon: 'fa-money-check-alt' },
	{ key: 'incoming-transfer', label: 'Incoming Transfer', icon: 'fa-money-check-alt' },
	{ key: 'cash-in-safe', label: 'Cash In Safe', icon: 'fa-money-check-alt' },
	{ key: 'cash-in-bank', label: 'Bank Deposit', icon: 'fa-money-check-alt' },
]

const tabTitles = ref({ ...props.tabTitles })
watch(
	() => props.tabTitles,
	(v) => {
		tabTitles.value = { ...(v || {}) }
	},
	{ deep: true },
)

/* Same column keys & order as reports/moneyReceived/index.blade.php per tab (Vue: no batch checkbox column — single send via actions). */
const TAB_COLS = {
	'cheque': ['type', 'customer_name', 'receiving_date', 'cheque_number', 'amount', 'currency', 'drawee_bank', 'due_date', 'due_after_days', 'status', 'actions'],
	'cheque-under-collection': ['type', 'customer_name', 'cheque_number', 'amount', 'deposit_date', 'drawl_bank', 'account_type', 'account_number', 'due_date', 'clearance_days', 'expected_collection_date', 'status', 'actions'],
	'cheque-collected': ['type', 'customer_name', 'cheque_number', 'amount', 'due_date', 'deposit_date', 'drawl_bank', 'account_type', 'account_number', 'actual_collection_date', 'actions'],
	'cheque-rejected': ['type', 'customer_name', 'receiving_date', 'cheque_number', 'amount', 'currency', 'drawee_bank', 'due_date', 'status', 'actions'],
	'incoming-transfer': ['type', 'customer_name', 'receiving_date', 'receiving_bank', 'amount', 'currency', 'account_type', 'account_number', 'actions'],
	'cash-in-safe': ['type', 'customer_name', 'receiving_date', 'branch', 'amount', 'currency', 'receipt_number', 'actions'],
	'cash-in-bank': ['type', 'customer_name', 'receiving_date', 'receiving_bank', 'amount', 'currency', 'account_type', 'account_number', 'actions'],
}

const COL_LABELS = {
	type: 'Type', customer_name: 'Customer', receiving_date: 'Receiving Date', cheque_number: 'Cheque Number',
	amount: 'Amount', currency: 'Currency', drawee_bank: 'Drawee Bank', due_date: 'Due Date',
	due_after_days: 'Due After Days', status: 'Status', actions: 'Control', deposit_date: 'Deposit Date',
	drawl_bank: 'Drawal Bank', account_type: 'Account Type', account_number: 'Account Number',
	clearance_days: 'Clearance Days', expected_collection_date: 'Cheque Expected Collection Date', actual_collection_date: 'Cheque Actual Collection Date',
	receiving_bank: 'Receiving Bank', branch: 'Branch', receipt_number: 'Receipt Number',
}

function sliceDate(v) {
	if (v == null || v === '') return ''
	return String(v).slice(0, 10)
}

const dateRanges = reactive({})

/** Same keys/order as MoneyReceived::getAllTypes() — used for request params & date state. */
const ALL_MONEY_TYPES = [
	'cash-in-safe',
	'cash-in-bank',
	'incoming-transfer',
	'cheque',
	'cheque-under-collection',
	'cheque-rejected',
	'cheque-collected',
]

function ensureAllDateKeys() {
	for (const k of ALL_MONEY_TYPES) {
		if (!dateRanges[k]) dateRanges[k] = { startDate: '', endDate: '' }
	}
}

function mergeFilterDates(src) {
	ensureAllDateKeys()
	if (!src || typeof src !== 'object') return
	for (const k of Object.keys(src)) {
		if (!dateRanges[k]) dateRanges[k] = { startDate: '', endDate: '' }
		if (src[k]?.startDate !== undefined) dateRanges[k].startDate = sliceDate(src[k].startDate)
		if (src[k]?.endDate !== undefined) dateRanges[k].endDate = sliceDate(src[k].endDate)
	}
}
mergeFilterDates(props.initialFilterDates)

watch(
	() => props.initialFilterDates,
	(v) => mergeFilterDates(v),
	{ deep: true }
)

const loading = ref(false)
const activeTab = ref(props.defaultActiveTab)
const rows = ref([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const permissions = ref({ canCreate: props.canCreate })
const urls = ref({
	create: props.createUrl,
	create_down_payment: props.createDownPaymentUrl || '',
})
const searchModalOpen = ref(false)

/** Per-tab advanced filter state (legacy had one modal per tab) */
const advSearchByTab = reactive({})
function advForTab(tabKey) {
	if (!advSearchByTab[tabKey]) {
		advSearchByTab[tabKey] = { field: '', value: '', from: '', to: '' }
	}
	return advSearchByTab[tabKey]
}

const ui = computed(() => advancedFilterUiState.value || {})

const currentSearchFieldEntries = computed(() => {
	const raw = searchFieldsByTabState.value?.[activeTab.value]
	if (!raw || typeof raw !== 'object') return []
	return Object.entries(raw)
})

const searchTextDisabled = computed(() => {
	const f = advForTab(activeTab.value).field
	return f === 'due_date' || f === 'receiving_date' || f === 'deposit_date'
})

const dateRangeHint = computed(() => {
	const f = advForTab(activeTab.value).field
	const u = ui.value
	if (f === 'due_date') return u.dataTypeDue || '[ Due Date ]'
	if (f === 'deposit_date') return u.dataTypeDeposit || '[ Deposit Date ]'
	if (f === 'receiving_date') return u.dataTypeReceiving || '[ Receiving Date ]'
	return u.dataTypeReceiving || '[ Receiving Date ]'
})

function onAdvFieldChange() {
	const a = advForTab(activeTab.value)
	if (searchTextDisabled.value) a.value = ''
}

function buildAdvSearchParams() {
	const a = advForTab(activeTab.value)
	const p = {}
	if (a.field) p.field = a.field
	if (a.value !== '' && a.value != null) p.value = a.value
	if (a.from) p.from = a.from
	if (a.to) p.to = a.to
	return p
}

function openSearchModal() {
	const t = activeTab.value
	advForTab(t)
	const entries = currentSearchFieldEntries.value
	if (entries.length && !advForTab(t).field) {
		advForTab(t).field = entries[0][0]
	}
	onAdvFieldChange()
	searchModalOpen.value = true
}

function closeSearchModal() {
	searchModalOpen.value = false
}

function submitAdvancedSearch() {
	closeSearchModal()
	loadData(1)
}

function resetAdvancedSearch() {
	const t = activeTab.value
	advSearchByTab[t] = { field: '', value: '', from: '', to: '' }
	closeSearchModal()
	loadData(1)
}

watch(activeTab, (t) => {
	if (!dateRanges[t]) dateRanges[t] = { startDate: '', endDate: '' }
}, { immediate: true })

const columns = computed(() => TAB_COLS[activeTab.value] || TAB_COLS.cheque)

const currentTabTitle = computed(() => {
	const k = activeTab.value
	return tabTitles.value[k] || TABS.find((t) => t.key === k)?.label || ''
})

function tabNavLabel(tab) {
	return tabTitles.value[tab.key] || tab.label
}

const isRtl = computed(() => props.appLang === 'ar')

/** Same inline margins as components/table-title/with-two-dates.blade.php */
const dateFormBlockStyle = computed(() =>
	isRtl.value ? { marginRight: '5rem' } : { marginLeft: '5rem' },
)
const dateFirstColStyle = computed(() =>
	isRtl.value ? { marginLeft: '5rem' } : { marginRight: '5rem' },
)
const dateSubmitColStyle = computed(() =>
	isRtl.value ? { marginRight: '2rem' } : { marginLeft: '2rem' },
)

const pages = computed(() => {
	const { current_page: c, last_page: l } = pagination.value
	const p = []
	for (let i = Math.max(1, c - 2); i <= Math.min(l, c + 2); i++) p.push(i)
	return p
})

function buildNestedDateParams() {
	ensureAllDateKeys()
	const t = activeTab.value
	const sd = String(dateRanges[t]?.startDate ?? '').trim()
	const ed = String(dateRanges[t]?.endDate ?? '').trim()
	/*
	 * Legacy Blade: each tab uses <x-table-title.with-two-dates> — one form posts only
	 * startDate[that-type] / endDate[that-type] + active. Other money types are absent from the query,
	 * so Laravel applies the per-type default window for each missing key.
	 * Sending all types (after mergeFilterDates) broke parity and could narrow under-collection incorrectly.
	 */
	if (sd === '' && ed === '') return {}
	return {
		startDate: { [t]: sd },
		endDate: { [t]: ed },
	}
}

const loadData = async (page = 1) => {
	loading.value = true
	try {
		const { data } = await axios.get(props.jsonUrl, {
			params: {
				active: activeTab.value,
				page,
				...buildNestedDateParams(),
				...buildAdvSearchParams(),
			},
		})
		rows.value = data.rows || []
		pagination.value = { current_page: 1, last_page: 1, total: 0, from: 0, to: 0, ...data.pagination }
		if (data.filterDates) mergeFilterDates(data.filterDates)
		if (data.permissions) permissions.value = { ...permissions.value, ...data.permissions }
		if (data.urls) urls.value = { ...urls.value, ...data.urls }
		if (data.searchFieldsByTab && typeof data.searchFieldsByTab === 'object') {
			searchFieldsByTabState.value = { ...searchFieldsByTabState.value, ...data.searchFieldsByTab }
		}
		if (data.advancedFilterUi && typeof data.advancedFilterUi === 'object') {
			advancedFilterUiState.value = { ...advancedFilterUiState.value, ...data.advancedFilterUi }
		}
		if (data.tabTitles && typeof data.tabTitles === 'object') {
			tabTitles.value = { ...tabTitles.value, ...data.tabTitles }
		}
	} catch (e) { console.error('loadData error:', e) }
	finally { loading.value = false }
}

/* ── Row action modals (parity with reports/_user_*_modal Blade includes) ── */
const commentModal = reactive({ open: false, text: '' })
const odooErrorModal = reactive({ open: false, message: '', postUrl: '' })
const integratedModal = reactive({ open: false, refs: [] })
const reviewModal = reactive({ open: false, postUrl: '', modelName: '', tableName: '' })
const applyCollectionModal = reactive({
	open: false,
	postUrl: '',
	customerName: '',
	chequeNumber: '',
	amount: '',
	dueDate: '',
	actualCollectionDate: '',
})

function openCommentModal(text) {
	commentModal.text = text || ''
	commentModal.open = true
}
function openOdooErrorModal(message, postUrl) {
	odooErrorModal.message = message || ''
	odooErrorModal.postUrl = postUrl || ''
	odooErrorModal.open = true
}
function submitOdooResend() {
	const f = document.createElement('form')
	f.method = 'post'
	f.action = odooErrorModal.postUrl
	const t = document.createElement('input')
	t.type = 'hidden'
	t.name = '_token'
	t.value = csrf()
	f.appendChild(t)
	document.body.appendChild(f)
	f.submit()
}
function openIntegratedModal(refs) {
	integratedModal.refs = Array.isArray(refs) ? refs : []
	integratedModal.open = true
}
function openReviewModal(row) {
	reviewModal.postUrl = row.review_post_url || ''
	reviewModal.modelName = row.review_model_name || ''
	reviewModal.tableName = row.review_table_name || ''
	reviewModal.open = true
}
function submitReviewForm() {
	const f = document.createElement('form')
	f.method = 'post'
	f.action = reviewModal.postUrl
	const t = document.createElement('input')
	t.type = 'hidden'
	t.name = '_token'
	t.value = csrf()
	f.appendChild(t)
	const m = document.createElement('input')
	m.type = 'hidden'
	m.name = 'model_name'
	m.value = reviewModal.modelName
	f.appendChild(m)
	const tb = document.createElement('input')
	tb.type = 'hidden'
	tb.name = 'table_name'
	tb.value = reviewModal.tableName
	f.appendChild(tb)
	document.body.appendChild(f)
	f.submit()
}

function openApplyCollection(row) {
	applyCollectionModal.postUrl = row.apply_collection_post_url || ''
	applyCollectionModal.customerName = row.customer_name || ''
	applyCollectionModal.chequeNumber = row.cheque_number || ''
	applyCollectionModal.amount = row.amount || ''
	applyCollectionModal.dueDate = row.due_date || ''
	const today = new Date().toISOString().slice(0, 10)
	applyCollectionModal.actualCollectionDate = today
	applyCollectionModal.open = true
}

async function submitApplyCollection() {
	if (!applyCollectionModal.postUrl || !applyCollectionModal.actualCollectionDate) return
	try {
		const fd = new FormData()
		fd.append('_token', csrf())
		fd.append('actual_collection_date', applyCollectionModal.actualCollectionDate)
		await axios.post(applyCollectionModal.postUrl, fd)
		applyCollectionModal.open = false
		await loadData(pagination.value.current_page)
	} catch {
		alert('Apply collection failed')
	}
}

const changeTab = (t) => { activeTab.value = t; loadData(1) }
const applyFilter = () => loadData(1)
const deleteRow = async (row) => {
	if (!confirm('Are you sure you want to delete this record?')) return
	try {
		const fd = new FormData()
		fd.append('_token', csrf())
		fd.append('_method', 'DELETE')
		await axios.post(row.delete_url, fd)
		loadData(pagination.value.current_page)
	} catch { alert('Delete failed') }
}

/** Called from index-vue.blade.php jQuery after POST send-to-collection succeeds (Bootstrap modal cannot refresh Vue alone). */
async function reloadFromSendToCollectionJQuery (nextTab) {
	if (nextTab) activeTab.value = nextTab
	await loadData(1)
}

onMounted(() => {
	ensureAllDateKeys()
	loadData(1)
	window.moneyReceivedVueReloadAfterSendToCollection = reloadFromSendToCollectionJQuery
})
onUnmounted(() => {
	delete window.moneyReceivedVueReloadAfterSendToCollection
})
</script>
<template>
	<div class="mr-page">
		<!-- Shell matches reports/moneyPayments/index + moneyReceived/index (Metronic portlets + nav-tabs) -->
		<div class="kt-portlet kt-portlet--tabs">
			<div class="kt-portlet__head">
				<div class="kt-portlet__head-toolbar justify-content-between flex-grow-1">
					<ul
						class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand"
						role="tablist">
						<li v-for="tab in TABS" :key="tab.key" class="nav-item">
							<a href="#" role="tab" class="nav-link" :class="{ active: activeTab === tab.key }"
								@click.prevent="changeTab(tab.key)">
								<i class="fa" :class="tab.icon"></i> {{ tabNavLabel(tab) }}
							</a>
						</li>
					</ul>
					<div v-if="permissions.canCreate" class="flex-tabs">
						<a :href="urls.create" class="btn btn-sm active-style btn-icon-sm align-self-center">
							<i class="fas fa-plus"></i>
							{{ ui.indexCreateMoneyReceived || 'Money Received' }}
						</a>
						<a v-if="urls.create_down_payment" :href="urls.create_down_payment"
							class="btn btn-sm active-style btn-icon-sm align-self-center">
							<i class="fas fa-plus"></i>
							{{ ui.indexDownPayment || 'Down Payment' }}
						</a>
					</div>
				</div>
			</div>
			<div class="kt-portlet__body">
				<div class="tab-content kt-margin-t-20">
					<div class="tab-pane active" role="tabpanel">
						<div class="kt-portlet kt-portlet--mobile">
							<!-- Mirror components/table-title/with-two-dates.blade.php (title + period row + slot toolbar) -->
							<div class="kt-portlet__head kt-portlet__head--lg p-0">
								<div class="kt-portlet__head-label ml-4" style="flex:2.5;">
									<span class="kt-portlet__head-icon">
										<i
											class="kt-font-secondary  text-main-color btn-outline-hover-danger fa fa-layer-group"></i>
									</span>
									<h3 style="font-size:20px !important;"
										class="kt-portlet__head-title text-main-color text-nowrap">
										{{ currentTabTitle }}
									</h3>
									<div class="w-full flex-2" :style="dateFormBlockStyle">
										<div class="row align-items-center">
											<div class="col-md-3 d-flex align-items-center" :style="dateFirstColStyle">
												<label :for="'vue_startDate_' + activeTab" class="text-nowrap mr-3">{{
													ui.startDate || 'Start Date' }}</label>
												<input :id="'vue_startDate_' + activeTab"
													v-model="dateRanges[activeTab].startDate" type="date"
													class="form-control" />
											</div>
											<div class="col-md-3 d-flex align-items-center">
												<label :for="'vue_endDate_' + activeTab" class="text-nowrap mr-3">{{
													ui.endDate || 'End Date' }}</label>
												<input :id="'vue_endDate_' + activeTab"
													v-model="dateRanges[activeTab].endDate" type="date"
													class="form-control" />
											</div>
											<div class="col-md-2 d-flex justify-content-center" :style="dateSubmitColStyle">
												<label for="vue_mr_period_submit" class="mb-0"></label>
												<button id="vue_mr_period_submit" type="button"
													class="btn block form-control btn-primary btn-sm"
													style="width:70px !important;font-size:1rem !important;"
													@click="applyFilter">
													{{ ui.submit || 'Submit' }}
												</button>
											</div>
										</div>
									</div>
								</div>
								<div class="kt-portlet__head-toolbar" style="flex:1 !important;">
									<div class="kt-portlet__head-wrapper">
										<div class="kt-portlet__head-actions">
											<button type="button" class="btn active-style btn-icon-sm"
												@click="openSearchModal">
												<i class="fas fa-search"></i>
												{{ ui.advancedFilter || 'Advanced Filter' }}
											</button>
										</div>
									</div>
								</div>
							</div>
							<div class="kt-portlet__body">
								<div v-if="loading" class="mr-empty py-5">
									<div class="mr-spinner"></div><span>Loading…</span>
								</div>
								<div v-else-if="!rows.length" class="mr-empty py-5">
									<i class="fas fa-inbox mr-empty-icon"></i>
									<h5>No records found</h5>
									<p>Try adjusting the date range or add a new record.</p>
									<a v-if="permissions.canCreate" :href="urls.create"
										class="btn btn-sm active-style btn-icon-sm">
										<i class="fas fa-plus"></i> {{ ui.indexCreateMoneyReceived || 'Money Received' }}
									</a>
								</div>
								<template v-else>
									<div class="table-responsive">
										<table
											class="table table-striped- table-bordered table-hover table-checkable text-center kt_table_1">
											<thead>
												<tr class="table-standard-color">
													<th v-for="col in columns" :key="col" class="align-middle">
														{{ COL_LABELS[col] || col }}
													</th>
												</tr>
											</thead>
											<tbody>
												<tr v-for="row in rows" :key="row.id">
													<td v-for="col in columns" :key="col + row.id" class="align-middle">
														<template v-if="col === 'status'">
															<span v-if="activeTab === 'cheque-rejected'"
																class="mr-status-plain">{{ row.status || '-' }}</span>
															<span v-else class="mr-badge"
																:style="row.due_status_color ? `color:${row.due_status_color};border-color:${row.due_status_color}` : ''">
																{{ row.status || '-' }}
															</span>
														</template>
														<template v-else-if="col === 'actions'">
															<span
																style="overflow: visible; position: relative; min-width: 110px; max-width: 420px; display: inline-block;">
																<span
																	class="d-flex flex-wrap align-items-center justify-content-center">
																	<button
																		v-if="row.has_user_comment && activeTab !== 'incoming-transfer' && activeTab !== 'cheque-collected'"
																		type="button"
																		class="btn btn-secondary btn-outline-hover-brand btn-icon btn-sm"
																		title="User Comment"
																		@click="openCommentModal(row.user_comment)"><i
																			class="fa fa-comment"></i></button>
																	<button v-if="row.show_odoo_error" type="button"
																		class="btn btn-secondary btn-outline-hover-danger btn-icon btn-sm ml-1"
																		title="Odoo Error"
																		@click="openOdooErrorModal(row.odoo_error_message, row.resend_odoo_url)"><i
																			class="fa fa-bug"></i></button>
																	<button v-if="row.show_integrated" type="button"
																		class="btn btn-secondary btn-outline-hover-success btn-icon btn-sm ml-1"
																		title="Fully Integrated"
																		@click="openIntegratedModal(row.odoo_reference_names)"><i
																			class="fa fa-thumbs-up"></i></button>
																	<button
																		v-if="row.show_review && activeTab !== 'cheque-under-collection' && activeTab !== 'cheque-collected'"
																		type="button"
																		class="btn btn-secondary btn-outline-hover-success btn-icon btn-sm ml-1"
																		title="Reviewed" @click="openReviewModal(row)"><i
																			class="fa fa-check"></i></button>
																	<a v-if="row.can_edit && row.edit_url"
																		:href="row.edit_url"
																		class="btn btn-secondary btn-outline-hover-brand btn-icon btn-sm ml-1"
																		title="Edit"><i class="fa fa-pen-alt"></i></a>
																	<a v-if="(activeTab === 'cheque' || activeTab === 'cheque-rejected') && row.can_send_under_collection"
																		href=""
																		class="btn btn-secondary btn-outline-hover-primary btn-icon btn-sm ml-1 js-can-trigger-cheque-under-collection-modal"
																		data-toggle="modal"
																		:data-target="'#send-to-under-collection-modal' + activeTab"
																		:data-id="String(row.id)" data-type="single"
																		:data-currency="row.receiving_currency || ''"
																		:data-money-type="activeTab"
																		title="Send Under Collection"><i
																			class="fa fa-money-bill"></i></a>
																	<button
																		v-if="activeTab === 'cheque-under-collection' && row.can_apply_collection"
																		type="button"
																		class="btn btn-secondary btn-outline-hover-primary btn-icon btn-sm ml-1"
																		title="Apply Collection"
																		@click="openApplyCollection(row)"><i
																			class="fa fa-coins"></i></button>
																	<a v-if="row.can_send_to_safe && row.send_to_safe_url"
																		:href="row.send_to_safe_url"
																		class="btn btn-secondary btn-outline-hover-brand btn-icon btn-sm ml-1"
																		title="Send In Safe"><i
																			class="fa fa-undo"></i></a>
																	<a v-if="row.can_reject && row.reject_url"
																		:href="row.reject_url"
																		class="btn btn-secondary btn-outline-hover-danger btn-icon btn-sm ml-1"
																		title="Rejected"><i class="fa fa-ban"></i></a>
																	<a v-if="activeTab === 'cheque-collected' && row.can_send_to_under_collection && row.send_to_under_collection_url"
																		:href="row.send_to_under_collection_url"
																		class="btn btn-secondary btn-outline-hover-primary btn-icon btn-sm ml-1"
																		title="Under Collection"><i
																			class="fa fa-undo"></i></a>
																	<button v-if="row.can_delete && row.delete_url"
																		type="button"
																		class="btn btn-secondary btn-outline-hover-danger btn-icon btn-sm ml-1"
																		title="Delete" @click="deleteRow(row)"><i
																			class="fa fa-trash-alt"></i></button>
																</span>
															</span>
														</template>
														<template v-else>{{ row[col] ?? '-' }}</template>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
									<nav v-if="pagination.total > 0"
										class="d-flex align-items-center justify-content-between flex-wrap mt-3 px-1"
										aria-label="Pagination">
										<span class="text-muted small mb-2 mb-sm-0">{{ pagination.from }}–{{ pagination.to }}
											of {{ pagination.total }}</span>
										<ul class="pagination pagination-sm mb-0">
											<li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
												<a class="page-link" href="#"
													@click.prevent="pagination.current_page > 1 && loadData(pagination.current_page - 1)"><i
														class="fa fa-chevron-left"></i></a>
											</li>
											<li v-for="p in pages" :key="p" class="page-item"
												:class="{ active: p === pagination.current_page }">
												<a class="page-link" href="#" @click.prevent="loadData(p)">{{ p }}</a>
											</li>
											<li class="page-item"
												:class="{ disabled: pagination.current_page === pagination.last_page }">
												<a class="page-link" href="#"
													@click.prevent="pagination.current_page < pagination.last_page && loadData(pagination.current_page + 1)"><i
														class="fa fa-chevron-right"></i></a>
											</li>
										</ul>
									</nav>
								</template>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Advanced filter modal (same fields as components/export-money.blade.php) -->
		<Teleport to="body">
			<div v-if="searchModalOpen" class="mr-modal-backdrop" @click.self="closeSearchModal">
				<div class="mr-modal" role="dialog" aria-modal="true">
					<div class="mr-modal-header">
						<h5 class="mr-modal-title">{{ ui.filterForm || 'Filter Form' }}</h5>
						<button type="button" class="mr-modal-close" aria-label="Close"
							@click="closeSearchModal">&times;</button>
					</div>
					<div class="mr-modal-body">
						<p v-if="!currentSearchFieldEntries.length" class="mr-modal-warn">No filter fields for this tab.
							Save the page and try again, or contact support.</p>
						<div class="mr-modal-grid">
							<div class="mr-filter-group">
								<label>{{ ui.fieldName || 'Field Name' }}</label>
								<select v-model="advForTab(activeTab).field" class="mr-input mr-input-wide"
									@change="onAdvFieldChange">
									<option v-for="([k, label]) in currentSearchFieldEntries" :key="k" :value="k">
										{{ label }}</option>
								</select>
							</div>
							<div class="mr-filter-group">
								<label>{{ ui.searchText || 'Search Text' }}</label>
								<input v-model="advForTab(activeTab).value" type="text" class="mr-input mr-input-wide"
									:disabled="searchTextDisabled" />
							</div>
							<div class="mr-filter-group">
								<label>{{ ui.from || 'From' }} <span class="mr-hint">{{ dateRangeHint }}</span></label>
								<input v-model="advForTab(activeTab).from" type="date" class="mr-input mr-input-wide" />
							</div>
							<div class="mr-filter-group">
								<label>{{ ui.to || 'To' }} <span class="mr-hint">{{ dateRangeHint }}</span></label>
								<input v-model="advForTab(activeTab).to" type="date" class="mr-input mr-input-wide" />
							</div>
						</div>
					</div>
					<div class="mr-modal-footer">
						<button type="button" class="btn-teal btn-sm"
							@click="submitAdvancedSearch">{{ ui.search || 'Search' }}</button>
						<button type="button" class="btn-teal btn-sm btn-teal-muted"
							@click="resetAdvancedSearch">{{ ui.reset || 'Reset' }}</button>
					</div>
				</div>
			</div>
		</Teleport>
		<!-- Row-level modals (same behaviour as reports/_user_*_modal includes) -->
		<Teleport to="body">
			<div v-if="commentModal.open" class="mr-modal-backdrop" @click.self="commentModal.open = false">
				<div class="mr-modal mr-modal-sm" role="dialog">
					<div class="mr-modal-header">
						<h5 class="mr-modal-title">User Comment</h5>
						<button type="button" class="mr-modal-close" aria-label="Close"
							@click="commentModal.open = false">&times;</button>
					</div>
					<div class="mr-modal-body">
						<p class="mr-modal-text">{{ commentModal.text }}</p>
					</div>
					<div class="mr-modal-footer">
						<button type="button" class="btn-teal btn-sm btn-teal-muted"
							@click="commentModal.open = false">Close</button>
					</div>
				</div>
			</div>
		</Teleport>
		<Teleport to="body">
			<div v-if="odooErrorModal.open" class="mr-modal-backdrop" @click.self="odooErrorModal.open = false">
				<div class="mr-modal" role="dialog">
					<div class="mr-modal-header">
						<h5 class="mr-modal-title">Odoo Error</h5>
						<button type="button" class="mr-modal-close" aria-label="Close"
							@click="odooErrorModal.open = false">&times;</button>
					</div>
					<div class="mr-modal-body">
						<p class="mr-modal-text">{{ odooErrorModal.message }}</p>
					</div>
					<div class="mr-modal-footer">
						<button type="button" class="btn-teal btn-sm btn-teal-muted"
							@click="odooErrorModal.open = false">Close</button>
						<button type="button" class="btn-teal btn-sm" @click="submitOdooResend">Resend</button>
					</div>
				</div>
			</div>
		</Teleport>
		<Teleport to="body">
			<div v-if="integratedModal.open" class="mr-modal-backdrop" @click.self="integratedModal.open = false">
				<div class="mr-modal mr-modal-sm" role="dialog">
					<div class="mr-modal-header">
						<h5 class="mr-modal-title">Odoo References</h5>
						<button type="button" class="mr-modal-close" aria-label="Close"
							@click="integratedModal.open = false">&times;</button>
					</div>
					<div class="mr-modal-body">
						<ul class="mr-modal-list">
							<li v-for="(r, i) in integratedModal.refs" :key="i">{{ r }}</li>
						</ul>
					</div>
					<div class="mr-modal-footer">
						<button type="button" class="btn-teal btn-sm"
							@click="integratedModal.open = false">Close</button>
					</div>
				</div>
			</div>
		</Teleport>
		<Teleport to="body">
			<div v-if="reviewModal.open" class="mr-modal-backdrop" @click.self="reviewModal.open = false">
				<div class="mr-modal mr-modal-sm" role="dialog">
					<div class="mr-modal-header">
						<h5 class="mr-modal-title">Mark This As Reviewed ?</h5>
						<button type="button" class="mr-modal-close" aria-label="Close"
							@click="reviewModal.open = false">&times;</button>
					</div>
					<div class="mr-modal-footer">
						<button type="button" class="btn-teal btn-sm btn-teal-muted"
							@click="reviewModal.open = false">Close</button>
						<button type="button" class="btn-teal btn-sm" @click="submitReviewForm">Confirm</button>
					</div>
				</div>
			</div>
		</Teleport>
		<Teleport to="body">
			<div v-if="applyCollectionModal.open" class="mr-modal-backdrop"
				@click.self="applyCollectionModal.open = false">
				<div class="mr-modal" role="dialog">
					<div class="mr-modal-header">
						<h5 class="mr-modal-title">Apply Collection</h5>
						<button type="button" class="mr-modal-close" aria-label="Close"
							@click="applyCollectionModal.open = false">&times;</button>
					</div>
					<div class="mr-modal-body">
						<div class="mr-modal-grid">
							<div class="mr-filter-group">
								<label>Customer</label>
								<input type="text" class="mr-input mr-input-wide"
									:value="applyCollectionModal.customerName" readonly disabled />
							</div>
							<div class="mr-filter-group">
								<label>Cheque Number</label>
								<input type="text" class="mr-input mr-input-wide"
									:value="applyCollectionModal.chequeNumber" readonly disabled />
							</div>
							<div class="mr-filter-group">
								<label>Amount</label>
								<input type="text" class="mr-input mr-input-wide" :value="applyCollectionModal.amount"
									readonly disabled />
							</div>
							<div class="mr-filter-group">
								<label>Due Date</label>
								<input type="text" class="mr-input mr-input-wide" :value="applyCollectionModal.dueDate"
									readonly disabled />
							</div>
							<div class="mr-filter-group">
								<label>Collection Date</label>
								<input v-model="applyCollectionModal.actualCollectionDate" type="date"
									class="mr-input mr-input-wide" />
							</div>
						</div>
					</div>
					<div class="mr-modal-footer">
						<button type="button" class="btn-teal btn-sm btn-teal-muted"
							@click="applyCollectionModal.open = false">Close</button>
						<button type="button" class="btn-teal btn-sm" @click="submitApplyCollection">Confirm</button>
					</div>
				</div>
			</div>
		</Teleport>
	</div>
</template>
<style scoped>
/* Tokens for modals / small components (Metronic + money-flow-dark handle the index shell) */
.mr-page {
	--teal: #00b4c8;
	--teal-dark: #0099aa;
	--teal-subtle: rgba(20, 144, 168, 0.12);
	--gold: #c9a84c;
	--text-primary: #e2e8f0;
	--text-secondary: #94a3b8;
	--text-muted: #64748b;
	--border: #1490A833;
	--border-focus: #00b4c8;
	--danger: #ef4444;
	--success: #10b981;
	--bg-input: #0C1829;

	background: transparent;
	color: inherit;
	min-height: 0;
	padding: 0;
	color-scheme: dark;
}

/* ── Buttons ── */
.btn-teal {
	background: var(--teal);
	color: #0C1829;
	border: none;
	border-radius: 6px;
	font-weight: 600;
	padding: 8px 16px;
	cursor: pointer;
	text-decoration: none;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	transition: all .2s;
}

.btn-teal:hover {
	background: var(--teal-dark);
	border-left: 3px solid var(--gold);
	color: #0C1829;
}

.btn-sm {
	padding: 6px 12px;
	font-size: .85rem;
}

.mr-filter-group {
	display: flex;
	flex-direction: column;
	gap: 3px;
}

.mr-filter-group label {
	color: var(--text-secondary);
	font-size: .78rem;
	font-weight: 600;
	text-transform: uppercase;
}

.mr-input {
	background: var(--bg-input);
	border: 1px solid var(--border);
	border-radius: 6px;
	color: var(--text-primary);
	padding: 8px 12px;
	font-size: 0.875rem;
	width: 160px;
	min-height: 40px;
	box-sizing: border-box;
	line-height: 1.35;
}

.mr-input:focus {
	border-color: var(--border-focus);
	box-shadow: 0 0 0 3px rgba(0, 180, 200, 0.2);
	outline: none;
}

/* Native date: icon visible on dark background */
.mr-input[type='date']::-webkit-calendar-picker-indicator {
	filter: invert(0.88) brightness(1.15);
	opacity: 0.95;
	cursor: pointer;
	padding: 2px;
}

.mr-input[type='date']::-webkit-datetime-edit-fields-wrapper {
	padding: 0;
}

/* Native select: custom chevron, consistent height */
select.mr-input {
	appearance: none;
	-webkit-appearance: none;
	-moz-appearance: none;
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2300b4c8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
	background-repeat: no-repeat;
	background-position: right 10px center;
	background-size: 12px;
	padding-right: 34px;
	cursor: pointer;
}

select.mr-input option {
	background: #112240;
	color: #e2e8f0;
}

/* ── Badges ── */
.mr-badge {
	display: inline-block;
	padding: 2px 8px;
	border-radius: 999px;
	font-size: .75rem;
	font-weight: 700;
	border: 1px solid var(--gold);
	color: var(--gold);
	background: rgba(201, 168, 76, .15);
}

.mr-status-plain {
	font-weight: 600;
	color: var(--text-primary);
}

/* ── Empty / Loading ── */
.mr-empty {
	text-align: center;
	padding: 48px 20px;
}

.mr-empty-icon {
	font-size: 48px;
	color: var(--teal);
	margin-bottom: 12px;
	display: block;
}

.mr-empty h5 {
	color: var(--text-primary);
	margin-bottom: 4px;
}

.mr-empty p {
	color: var(--text-secondary);
	margin-bottom: 16px;
}

.mr-spinner {
	width: 36px;
	height: 36px;
	border: 3px solid var(--border);
	border-top-color: var(--teal);
	border-radius: 50%;
	margin: 0 auto 12px;
	animation: spin .6s linear infinite;
}

@keyframes spin {
	to {
		transform: rotate(360deg);
	}
}

/* Advanced filter modal (legacy export-money popup) */
.btn-teal-outline {
	background: transparent;
	color: var(--teal);
	border: 1px solid var(--teal);
}

.btn-teal-outline:hover {
	background: var(--teal-subtle);
}

.btn-teal-muted {
	background: var(--bg-input);
	color: var(--text-secondary);
	border: 1px solid var(--border);
}

.btn-teal-muted:hover {
	border-color: var(--teal);
	color: var(--teal);
}

/* Teleported to <body>: do not rely on .mr-page CSS variables — use explicit colors */
.mr-modal-backdrop {
	position: fixed;
	inset: 0;
	z-index: 1050;
	background: rgba(6, 12, 22, 0.88);
	backdrop-filter: blur(8px);
	-webkit-backdrop-filter: blur(8px);
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 16px;
}

.mr-modal {
	width: 100%;
	max-width: 960px;
	background: #112240;
	color: #e2e8f0;
	border: 1px solid rgba(20, 144, 168, 0.28);
	border-top: 3px solid #00b4c8;
	border-radius: 10px;
	box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55);
	max-height: 90vh;
	overflow: auto;
}

.mr-modal-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 14px 18px;
	border-bottom: 1px solid var(--border);
}

.mr-modal-title {
	margin: 0;
	font-size: 1.05rem;
	font-weight: 700;
	color: #00b4c8;
}

.mr-modal-close {
	background: none;
	border: none;
	color: var(--text-secondary);
	font-size: 1.5rem;
	line-height: 1;
	cursor: pointer;
	padding: 0 4px;
}

.mr-modal-close:hover {
	color: var(--text-primary);
}

.mr-modal-body {
	padding: 18px;
	color: #e2e8f0;
}

.mr-modal-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
	gap: 14px;
}

.mr-input-wide {
	width: 100%;
	min-width: 0;
}

.mr-hint {
	font-size: 0.72rem;
	color: var(--gold);
	font-weight: 600;
}

.mr-modal-footer {
	display: flex;
	gap: 10px;
	justify-content: flex-end;
	padding: 12px 18px 16px;
	border-top: 1px solid var(--border);
}

.mr-modal-sm {
	max-width: 520px;
}

.mr-modal-warn {
	color: var(--gold);
	font-size: 0.88rem;
	margin: 0 0 12px;
}

.mr-modal-text {
	white-space: pre-wrap;
	word-break: break-word;
	margin: 0;
	line-height: 1.45;
	color: #e2e8f0;
}

.mr-modal-list {
	margin: 0;
	padding-left: 1.1rem;
	line-height: 1.6;
}

</style>
