<script setup>
import axios from 'axios'
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'

const props = defineProps({
	companyId: { type: Number, required: true },
	defaultActiveTab: { type: String, default: 'cheque' },
	jsonUrl: { type: String, required: true },
	createUrl: { type: String, default: '' },
	canCreate: { type: Boolean, default: false },
	initialFilterDates: { type: Object, default: () => ({}) },
	/** field key => label (server-translated), keyed by tab — same as legacy export-money modal */
	searchFieldsByTab: { type: Object, default: () => ({}) },
	advancedFilterUi: { type: Object, default: () => ({}) },
})

/* Dashboard layout uses body data-token=csrf_token(); no meta[name=csrf-token] on many pages */
const csrf = () =>
	document.querySelector('meta[name="csrf-token"]')?.content
	|| document.body?.dataset?.token
	|| ''

/** Merged from props + JSON API (fixes empty dataset / encoding issues). */
const searchFieldsByTabState = ref({ ...props.searchFieldsByTab })
const advancedFilterUiState = ref({ ...props.advancedFilterUi })

const TABS = [
	{ key: 'cheque', label: 'Cheques In Safe', icon: 'fa-money-check-alt' },
	{ key: 'cheque-under-collection', label: 'Under Collection', icon: 'fa-clock' },
	{ key: 'cheque-collected', label: 'Collected', icon: 'fa-check-circle' },
	{ key: 'cheque-rejected', label: 'Rejected', icon: 'fa-ban' },
	{ key: 'incoming-transfer', label: 'Incoming Transfer', icon: 'fa-exchange-alt' },
	{ key: 'cash-in-safe', label: 'Cash In Safe', icon: 'fa-box-open' },
	{ key: 'cash-in-bank', label: 'Bank Deposit', icon: 'fa-university' },
]

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
const urls = ref({ create: props.createUrl })
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
		<!-- Header -->
		<div class="mr-header">
			<div class="mr-header-left">
				<div class="mr-header-icon"><i class="fas fa-hand-holding-usd"></i></div>
				<div>
					<h4 class="mr-title">Money Received</h4>
					<span class="mr-breadcrumb">Dashboard / Reports / Money Received</span>
				</div>
			</div>
			<div v-if="permissions.canCreate" class="mr-header-right">
				<a :href="urls.create" class="btn-teal"><i class="fas fa-plus-circle"></i> Add New</a>
			</div>
		</div>
		<!-- Tabs -->
		<div class="mr-tabs">
			<button v-for="tab in TABS" :key="tab.key" :class="['mr-tab', activeTab === tab.key && 'active']"
				@click="changeTab(tab.key)">
				<i :class="['fas', tab.icon]"></i> {{ tab.label }}
			</button>
		</div>
		<!-- Filter (per-tab range, same request shape as legacy Blade) -->
		<div class="mr-filter">
			<div class="mr-filter-group">
				<label>From</label>
				<input type="date" v-model="dateRanges[activeTab].startDate" class="mr-input" />
			</div>
			<div class="mr-filter-group">
				<label>To</label>
				<input type="date" v-model="dateRanges[activeTab].endDate" class="mr-input" />
			</div>
			<button class="btn-teal btn-sm" @click="applyFilter"><i class="fas fa-search"></i> Search</button>
			<button type="button" class="btn-teal btn-sm btn-teal-outline" @click="openSearchModal">
				<i class="fas fa-search"></i> {{ ui.advancedFilter || 'Advanced Filter' }}
			</button>
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
		<!-- Card -->
		<div class="mr-card">
			<!-- Loading -->
			<div v-if="loading" class="mr-empty">
				<div class="mr-spinner"></div><span>Loading…</span>
			</div>
			<!-- Empty -->
			<div v-else-if="!rows.length" class="mr-empty">
				<i class="fas fa-inbox mr-empty-icon"></i>
				<h5>No records found</h5>
				<p>Try adjusting the date range or add a new record.</p>
				<a v-if="permissions.canCreate" :href="urls.create" class="btn-teal"><i class="fas fa-plus"></i> Add
					First Record</a>
			</div>
			<!-- Table -->
			<div v-else class="table-responsive">
				<table class="mr-table">
					<thead>
						<tr>
							<th v-for="col in columns" :key="col">{{ COL_LABELS[col] || col }}</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="row in rows" :key="row.id">
							<td v-for="col in columns" :key="col + row.id">
								<!-- Status: plain text on rejected tab (Blade uses getStatusFormatted only); badge elsewhere -->
								<template v-if="col === 'status'">
									<span v-if="activeTab === 'cheque-rejected'"
										class="mr-status-plain">{{ row.status || '-' }}</span>
									<span v-else class="mr-badge"
										:style="row.due_status_color ? `color:${row.due_status_color};border-color:${row.due_status_color}` : ''">
										{{ row.status || '-' }}
									</span>
								</template>
								<!-- Actions (parity with reports/moneyReceived/index.blade.php row tools) -->
								<template v-else-if="col === 'actions'">
									<div class="mr-actions mr-actions-wrap">
										<button
											v-if="row.has_user_comment && activeTab !== 'incoming-transfer' && activeTab !== 'cheque-collected'"
											type="button" class="btn-act btn-act-msg" title="User Comment"
											@click="openCommentModal(row.user_comment)"><i
												class="fas fa-comment"></i></button>
										<button v-if="row.show_odoo_error" type="button" class="btn-act btn-act-odoo"
											title="Odoo Error"
											@click="openOdooErrorModal(row.odoo_error_message, row.resend_odoo_url)"><i
												class="fas fa-bug"></i></button>
										<button v-if="row.show_integrated" type="button" class="btn-act btn-act-int"
											title="Fully Integrated"
											@click="openIntegratedModal(row.odoo_reference_names)"><i
												class="fas fa-thumbs-up"></i></button>
										<button
											v-if="row.show_review && activeTab !== 'cheque-under-collection' && activeTab !== 'cheque-collected'"
											type="button" class="btn-act btn-act-review" title="Reviewed"
											@click="openReviewModal(row)"><i class="fas fa-check"></i></button>
										<a v-if="row.can_edit && row.edit_url" :href="row.edit_url"
											class="btn-act btn-act-teal" title="Edit"><i class="fas fa-pen"></i></a>
										<a v-if="(activeTab === 'cheque' || activeTab === 'cheque-rejected') && row.can_send_under_collection"
											href=""
											class="btn-act btn-act-uc js-can-trigger-cheque-under-collection-modal"
											data-toggle="modal"
											:data-target="'#send-to-under-collection-modal' + activeTab"
											:data-id="String(row.id)" data-type="single"
											:data-currency="row.receiving_currency || ''" :data-money-type="activeTab"
											title="Send Under Collection"><i class="fas fa-money-bill"></i></a>
										<button
											v-if="activeTab === 'cheque-under-collection' && row.can_apply_collection"
											type="button" class="btn-act btn-act-coins" title="Apply Collection"
											@click="openApplyCollection(row)"><i class="fas fa-coins"></i></button>
										<a v-if="row.can_send_to_safe && row.send_to_safe_url"
											:href="row.send_to_safe_url" class="btn-act btn-act-gold"
											title="Send In Safe"><i class="fas fa-undo"></i></a>
										<a v-if="row.can_reject && row.reject_url" :href="row.reject_url"
											class="btn-act btn-act-red" title="Rejected"><i class="fas fa-ban"></i></a>
										<a v-if="activeTab === 'cheque-collected' && row.can_send_to_under_collection && row.send_to_under_collection_url"
											:href="row.send_to_under_collection_url" class="btn-act btn-act-gold"
											title="Under Collection"><i class="fas fa-undo"></i></a>
										<button v-if="row.can_delete && row.delete_url" type="button"
											class="btn-act btn-act-red" title="Delete" @click="deleteRow(row)"><i
												class="fas fa-trash"></i></button>
									</div>
								</template>
								<!-- Normal cell -->
								<template v-else>{{ row[col] ?? '-' }}</template>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<!-- Pagination -->
			<div v-if="!loading && pagination.total > 0" class="mr-pag">
				<span class="mr-pag-info">{{ pagination.from }}–{{ pagination.to }} of {{ pagination.total }}</span>
				<div class="mr-pag-btns">
					<button :disabled="pagination.current_page === 1" @click="loadData(pagination.current_page - 1)"><i
							class="fas fa-chevron-left"></i></button>
					<button v-for="p in pages" :key="p" :class="{ active: p === pagination.current_page }"
						@click="loadData(p)">{{ p }}</button>
					<button :disabled="pagination.current_page === pagination.last_page"
						@click="loadData(pagination.current_page + 1)"><i class="fas fa-chevron-right"></i></button>
				</div>
			</div>
		</div>
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
/* ═══ Design Tokens — Dark Navy + Teal + Gold ═══ */
.mr-page {
	--bg-page: #0C1829;
	--bg-sidebar: #112240;
	--bg-card: #112240;
	--bg-card-hover: #152a4a;
	--bg-input: #0C1829;
	--teal: #00b4c8;
	--teal-dark: #0099aa;
	--teal-subtle: rgba(20, 144, 168, 0.12);
	--gold: #c9a84c;
	--gold-dark: #a6852a;
	--text-primary: #e2e8f0;
	--text-secondary: #94a3b8;
	--text-muted: #64748b;
	--border: #1490A833;
	--border-focus: #00b4c8;
	--danger: #ef4444;
	--success: #10b981;

	background: var(--bg-page);
	color: var(--text-primary);
	min-height: 100vh;
	padding: 0 0 40px;
	font-family: 'Segoe UI', system-ui, sans-serif;
	/* Native date/time/select pickers follow dark UI (Chrome / Safari / Edge) */
	color-scheme: dark;
}

/* ── Header ── */
.mr-header {
	background: linear-gradient(90deg, var(--bg-card), var(--bg-sidebar));
	border-bottom: 3px solid var(--teal);
	padding: 16px 20px;
	display: flex;
	align-items: center;
	justify-content: space-between;
}

.mr-header-left {
	display: flex;
	align-items: center;
	gap: 14px;
}

.mr-header-icon {
	width: 44px;
	height: 44px;
	border-radius: 10px;
	background: var(--teal-subtle);
	border: 1px solid var(--teal);
	display: flex;
	align-items: center;
	justify-content: center;
	color: var(--teal);
	font-size: 20px;
}

.mr-title {
	margin: 0;
	font-weight: 800;
	color: var(--text-primary);
	border-left: 4px solid var(--gold);
	padding-left: 10px;
	font-size: 1.15rem;
}

.mr-breadcrumb {
	color: var(--text-secondary);
	font-size: .8rem;
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

/* ── Tabs ── */
.mr-tabs {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
	padding: 10px 20px;
	background: var(--bg-card);
	border-bottom: 1px solid var(--border);
}

.mr-tab {
	background: var(--bg-input);
	border: 1px solid var(--border);
	border-radius: 6px;
	color: var(--text-secondary);
	padding: 7px 12px;
	font-size: .82rem;
	font-weight: 600;
	cursor: pointer;
	transition: all .2s;
	display: inline-flex;
	align-items: center;
	gap: 5px;
}

.mr-tab:hover {
	color: var(--teal);
	border-color: var(--teal);
}

.mr-tab.active {
	background: var(--teal-subtle);
	color: var(--teal);
	border-color: var(--teal);
}

/* ── Filter ── */
.mr-filter {
	display: flex;
	align-items: flex-end;
	gap: 12px;
	flex-wrap: wrap;
	padding: 12px 20px;
	background: var(--bg-card);
	border-bottom: 1px solid var(--border);
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

/* ── Card ── */
.mr-card {
	margin: 16px 20px;
	background: var(--bg-card);
	border: 1px solid var(--border);
	border-top: 3px solid var(--teal);
	border-radius: 10px;
	box-shadow: 0 4px 24px rgba(0, 0, 0, .4);
	overflow: hidden;
}

/* ── Table ── */
.mr-table {
	width: 100%;
	border-collapse: collapse;
}

.mr-table thead th {
	background: var(--bg-sidebar);
	color: var(--teal);
	font-size: .75rem;
	text-transform: uppercase;
	letter-spacing: .05em;
	padding: 12px 16px;
	white-space: nowrap;
	border-bottom: 1px solid var(--border);
	text-align: left;
}

.mr-table tbody td {
	padding: 12px 16px;
	border-bottom: 1px solid var(--border);
	color: var(--text-primary);
	font-size: .875rem;
	vertical-align: middle;
}

.mr-table tbody tr:hover {
	background: var(--teal-subtle);
	border-left: 3px solid var(--teal);
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

/* ── Action buttons ── */
.mr-actions {
	display: flex;
	gap: 5px;
}

.btn-act {
	width: 30px;
	height: 30px;
	border-radius: 6px;
	border: none;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	transition: all .15s;
	font-size: .8rem;
}

.btn-act-teal {
	background: var(--teal-subtle);
	color: var(--teal);
	border: 1px solid var(--teal);
}

.btn-act-teal:hover {
	background: var(--teal);
	color: #0C1829;
}

.btn-act-red {
	background: rgba(239, 68, 68, .15);
	color: var(--danger);
	border: 1px solid var(--danger);
}

.btn-act-red:hover {
	background: var(--danger);
	color: #fff;
}

.btn-act-gold {
	background: rgba(201, 168, 76, .15);
	color: var(--gold);
	border: 1px solid var(--gold);
}

.btn-act-gold:hover {
	background: var(--gold);
	color: #0C1829;
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

/* ── Pagination ── */
.mr-pag {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px 20px;
	border-top: 1px solid var(--border);
	flex-wrap: wrap;
	gap: 8px;
}

.mr-pag-info {
	color: var(--text-secondary);
	font-size: .82rem;
}

.mr-pag-btns {
	display: flex;
	gap: 4px;
}

.mr-pag-btns button {
	min-width: 32px;
	height: 32px;
	border: 1px solid var(--border);
	border-radius: 6px;
	background: var(--bg-input);
	color: var(--text-primary);
	font-size: .84rem;
	cursor: pointer;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	transition: all .15s;
}

.mr-pag-btns button:hover:not(:disabled) {
	border-color: var(--teal);
	color: var(--teal);
	background: var(--teal-subtle);
}

.mr-pag-btns button:disabled {
	opacity: .4;
	cursor: not-allowed;
}

.mr-pag-btns button.active {
	background: var(--teal-subtle);
	color: var(--teal);
	border-color: var(--teal);
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

.mr-actions-wrap {
	flex-wrap: wrap;
	max-width: 320px;
	gap: 4px;
}

.btn-act-msg {
	background: rgba(16, 185, 129, 0.15);
	color: var(--success);
	border: 1px solid var(--success);
}

.btn-act-msg:hover {
	background: var(--success);
	color: #0c1829;
}

.btn-act-odoo {
	background: #b91c1c;
	color: #fff;
	border: 1px solid #fecaca;
}

.btn-act-odoo:hover {
	filter: brightness(1.08);
}

.btn-act-int {
	background: rgba(59, 130, 246, 0.2);
	color: #93c5fd;
	border: 1px solid #3b82f6;
}

.btn-act-int:hover {
	background: #3b82f6;
	color: #0c1829;
}

.btn-act-review {
	background: rgba(16, 185, 129, 0.15);
	color: var(--success);
	border: 1px solid var(--success);
}

.btn-act-uc {
	background: rgba(201, 168, 76, 0.15);
	color: var(--gold);
	border: 1px solid var(--gold);
}

.btn-act-coins {
	background: rgba(16, 185, 129, 0.12);
	color: #6ee7b7;
	border: 1px solid #34d399;
}

.btn-act-coins:hover {
	background: #34d399;
	color: #0c1829;
}
</style>
