<script setup lang="ts">
import Swal from 'sweetalert2'
import { computed, onMounted, ref } from 'vue'
import Loading from '../../../components/Common/Loading.vue'
import { useContract } from '../../composables/useContract'
import type { Contract } from '../../stores/contractStore'
import { useContractStore } from '../../stores/contractStore'
import DeleteConfirmModal from '../Tradings/Components/Modals/DeleteConfirmModal.vue'
import SearchModal from '../Tradings/Components/Modals/SearchModal.vue'
import ContractTableRow from './Components/ContractTableRow.vue'

// State Management
const contractStore = useContractStore()
const {
	navigateToCreate,
	navigateToEdit,
	navigateToTradings,
	deleteContract: deleteContractAction
} = useContract()

// Local State
const activeTab = ref<string>('running')
const showContractDetails = ref<boolean>(false)
const showDeleteConfirm = ref<boolean>(false)
const showSearch = ref<boolean>(false)
const selectedContract = ref<Contract | null>(null)
const isDeleting = ref<boolean>(false)

// Pagination
const currentPage = ref<number>(1)
const perPage = ref<number>(25)

// Computed
const currentTabContracts = computed(() => {
	contractStore.setSelectedType(activeTab.value)
	let filtered = contractStore.filteredContracts
	// Don't show child units
	// if (activeTab.value === 'all') {
	// 	filtered = filtered.filter(c => true)
	// } else {
	filtered = filtered.filter(c => c.status === activeTab.value)
	// }
	// console.log(filtered)

	return filtered
})

const paginatedContracts = computed(() => {
	const start = (currentPage.value - 1) * perPage.value
	const end = start + perPage.value
	return currentTabContracts.value.slice(start, end)
})

const totalPages = computed(() => {
	return Math.ceil(currentTabContracts.value.length / perPage.value)
})

const contractsCounts = computed(() => {
	return {
		// all: contractStore.allContracts.length,
		running: contractStore.runningContracts.length,
		finished: contractStore.finishedContracts.length,
		expired: contractStore.expiredContracts.length,
	}
})

// Methods
const handleTabChange = (tab: string) => {
	activeTab.value = tab
	currentPage.value = 1
}

const handleCreateContract = () => {
	navigateToCreate()
}
const handleBackToTradings = () => {
	navigateToTradings()
}
const handleViewContract = (contractId: number) => {
	// console.log('handleViewContract called with ID:', contractId)
	// console.log('Available contracts:', contractStore.contracts.length)

	const contract = contractStore.contracts.find(p => p.id === contractId)
	// console.log('Found contract:', contract)

	if (contract) {
		selectedContract.value = contract
		showContractDetails.value = true
		console.log('Modal should show now. showContractDetails:', showContractDetails.value)
		console.log('selectedContract:', selectedContract.value)
	} else {
		console.error('Contract not found:', contractId)
		Swal.fire({
			icon: 'error',
			title: 'Error',
			text: `Contract with ID ${contractId} not found`,
		})
	}
}

const handleEditContract = (contractId: number) => {
	navigateToEdit(contractId)
}



const handleConfirmDelete = (contractId: number) => {
	const contract = contractStore.contracts.find(p => p.id === contractId)
	if (contract) {
		selectedContract.value = contract
		showDeleteConfirm.value = true
	} else {
		console.error('Contract not found:', contractId)
	}
}

const handleDeleteContract = async () => {
	if (!selectedContract.value) return

	isDeleting.value = true
	const success = await deleteContractAction(selectedContract.value.id)
	isDeleting.value = false

	if (success) {
		showDeleteConfirm.value = false
		selectedContract.value = null
	}
}

const handleSearch = ({ field, query }: { field: string; query: string }) => {
	contractStore.setSearchField(field)
	contractStore.setSearchQuery(query)
	currentPage.value = 1
}

const handleClearSearch = () => {
	contractStore.clearSearch()
	currentPage.value = 1
}

const goToPage = (page: number) => {
	if (page >= 1 && page <= totalPages.value) {
		currentPage.value = page
	}
}

onMounted(() => {
	// Get contracts from blade template
	const contractsData = (window as any).contractsData
	if (contractsData) {
		// No processing needed - nature_id comes from backend
		contractStore.setContracts(contractsData)
		contractStore.setLoading(false)
	}
})
</script>
<template>
	<div class="row">
		<div class="col-md-12" v-if="contractStore.isLoading">
			<div class="kt-portlet">
				<div class="kt-portlet__body exclude">
					<div class="col-md-12">
						<Loading :isLoading="contractStore.isLoading"></Loading>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div v-if="!contractStore.isLoading" class="kt-portlet kt-portlet--tabs">
		<div class="kt-portlet__head">
			<div class="kt-portlet__head-toolbar justify-content-between flex-grow-1">
				<ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand"
					role="tablist">
					<!-- <li class="nav-item">
						<a class="nav-link" :class="{ active: activeTab === 'all' }"
							@click.prevent="handleTabChange('all')" href="javascript:void(0)" role="tab">
							<i class="fa fa-th-large"></i> {{ $t('All Contracts') }}
							<span class="badge badge-pill badge-secondary ml-1">{{ contractsCounts.all }}</span>
						</a>
					</li> -->
					<li class="nav-item">
						<a class="nav-link" :class="{ active: activeTab === 'running' }"
							@click.prevent="handleTabChange('running')" href="javascript:void(0)" role="tab">
							<i class="fa fa-light fa-business-time"></i> {{ $t('Running') }}
							<span class="badge badge-pill badge-primary ml-1">{{ contractsCounts.running }}</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" :class="{ active: activeTab === 'finished' }"
							@click.prevent="handleTabChange('finished')" href="javascript:void(0)" role="tab">
							<!-- <i class="far fa-hourglass"></i> -->
							<i class="far fa-hourglass "></i> {{ $t('Finished') }}
							<span class="badge badge-pill badge-success ml-1">{{ contractsCounts.finished }}</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" :class="{ active: activeTab === 'expired' }"
							@click.prevent="handleTabChange('expired')" href="javascript:void(0)" role="tab">
							<i class="far fa-light fa-times-circle"></i> {{ $t('Terminated') }}
							<span class="badge badge-pill badge-warning ml-1">{{ contractsCounts.expired }}</span>
						</a>
					</li>
				</ul>
				<div class="flex-tabs">
					<button @click="handleBackToTradings"
						class="btn btn-2-bg bg-white-hover new-study-item rounded btn-icon-sm align-self-center">
						<i class="fas fa-arrow-left white-icon exclude-icon"></i>
						{{ $t('Back To Tradings') }}
					</button>
					<button @click="handleCreateContract"
						class="btn btn-2-bg bg-white-hover new-study-item rounded btn-icon-sm align-self-center">
						<i class="fas fa-plus white-icon exclude-icon"></i>
						{{ $t('New Contract') }}
					</button>
				</div>
			</div>
		</div>
		<div class="kt-portlet__body pt-0 ">
			<div class="tab-content kt-margin-t-20">
				<div class="tab-pane active" role="tabpanel">
					<div class="kt-portlet kt-portlet--mobile">
						<!-- Search Button -->
						<div class="kt-portlet__head kt-portlet__head--lg p-0">
							<div class="kt-portlet__head-label">
								<button @click="showSearch = true" class="btn btn-secondary btn-bold btn-sm mb-2">
									<i class="fa fa-search"></i> {{ $t('Search') }}
								</button>
								<button v-if="contractStore.searchQuery" @click="handleClearSearch"
									class="btn btn-danger btn-bold btn-sm mb-2 ml-2">
									<i class="fa fa-times exclude-icon default-icon-color"></i> {{ $t('Clear') }}
								</button>
							</div>
							<div class="kt-portlet__head-toolbar" v-if="contractStore.searchQuery">
								<span class="badge badge-info p-2">
									{{ $t('Searching') }}: {{ contractStore.searchField }} = "{{
										contractStore.searchQuery }}" </span>
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
												<th class=" header-border-down text-center">
													{{ $t('Status') }}
												</th>
												<th class=" header-border-down text-center">
													{{ $t('Tenant Name') }}
												</th>
												<th class="header-border-down text-center">
													{{ $t('Start Date') }}
												</th>
												<th class=" header-border-down text-center">
													{{ $t('End Date') }}
												</th>
												<!-- <th class="header-border-down text-center">
													{{ $t('Country') }}
												</th> -->
												<th class=" header-border-down text-center">
													{{ $t('Monthly Rent') }}
												</th>
												<th class=" header-border-down text-center">
													{{ $t('Collection Currency') }}
												</th>
												<th class=" header-border-down text-center">
													{{ $t('Collection Interval') }}
												</th>
												<th class="interval-class header-border-down text-center">
													{{ $t('Actions') }}
												</th>
											</tr>
										</thead>
										<tbody>
											<tr v-if="paginatedContracts.length === 0">
												<td colspan="9" class="text-center py-5">
													<i class="fa fa-inbox fa-3x text-muted mb-3 d-block"></i>
													<p class="text-muted">{{ $t('No contracts found') }}</p>
												</td>
											</tr>
											<ContractTableRow v-for="contract in paginatedContracts" :key="contract.id"
												:contract="contract" @view="handleViewContract"
												@edit="handleEditContract" @delete="handleConfirmDelete" />
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
											{{ Math.min(currentPage * perPage, currentTabContracts.length) }}
											{{ $t('of') }} {{ currentTabContracts.length }}
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Modals -->
	<!-- <ContractDetailsModal :show="showContractDetails" :contract="selectedContract" @close="showContractDetails = false"
		@edit="handleEditContract" @delete="handleConfirmDelete" /> -->
	<DeleteConfirmModal :show="showDeleteConfirm" :contract-name="selectedContract?.name" :is-deleting="isDeleting"
		@close="showDeleteConfirm = false" @confirm="handleDeleteContract" />
	<SearchModal :show="showSearch" :initial-search-field="contractStore.searchField"
		:initial-search-query="contractStore.searchQuery" @close="showSearch = false" @search="handleSearch" />
</template>
<style scoped>
.bg-white-hover:hover {
	color: white !important;
}

.new-study-item i {
	color: #055dac !important;
}

.new-study-item:hover i {
	color: white !important;
}

.nav-tabs .nav-link {
	cursor: pointer;
	transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
	background-color: #f8f9fa;
}

.nav-tabs .nav-link.active {
	border-bottom: 3px solid #5867dd !important;
}

.badge-pill {
	padding: 0.25rem 0.5rem;
	font-size: 0.75rem;
}

.kt-pagination {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 1rem 0;
}

.kt-pagination__links {
	display: flex;
	list-style: none;
	padding: 0;
	margin: 0;
	gap: 0.25rem;
}

.kt-pagination__links li {
	display: inline-block;
}

.kt-pagination__links li a {
	display: block;
	padding: 0.5rem 0.75rem;
	border: 1px solid #e2e5ec;
	background: white;
	color: #6c7293;
	text-decoration: none;
	cursor: pointer;
	transition: all 0.2s ease;
}

.kt-pagination__links li a:hover {
	background: #f4f5f8;
	border-color: #5867dd;
}

.kt-pagination__link--active a {
	background: #5867dd !important;
	color: white !important;
	border-color: #5867dd !important;
}

.kt-pagination__link--disabled a {
	opacity: 0.5;
	cursor: not-allowed;
	pointer-events: none;
}

.table-responsive {
	overflow-x: auto;
}

.modal.show {
	display: block !important;
}

.header-border {
	border-bottom: 2px solid #007bff;
}

.header-border-down {
	border-bottom: 1px solid #007bff;
}
</style>
