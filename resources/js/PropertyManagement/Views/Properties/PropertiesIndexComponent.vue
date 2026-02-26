<script setup lang="ts">
import Swal from 'sweetalert2'
import { computed, onMounted, ref } from 'vue'
import Loading from '../../../components/Common/Loading.vue'
import { useProperty } from '../../composables/useProperty'
import type { Property } from '../../stores/propertyStore'

import { usePropertyStore } from '../../stores/propertyStore'
import DeleteConfirmModal from './Components/Modals/DeleteConfirmModal.vue'
import PropertyDetailsModal from './Components/Modals/PropertyDetailsModal.vue'
import SearchModal from './Components/Modals/SearchModal.vue'
import PropertyTableRow from './Components/PropertyTableRow.vue'

// State Management
const propertyStore = usePropertyStore()
const {
	navigateToCreate,
	navigateToEdit,
	navigateToPropertyExpense,
	navigateToContracts,
	deleteProperty: deletePropertyAction
} = useProperty()

// Local State
const activeTab = ref<string>('all')
const showPropertyDetails = ref<boolean>(false)
const showDeleteConfirm = ref<boolean>(false)
const showSearch = ref<boolean>(false)
const selectedProperty = ref<Property | null>(null)
const isDeleting = ref<boolean>(false)


// Pagination
const currentPage = ref<number>(1)
const perPage = ref<number>(25)

// Computed
const currentTabProperties = computed(() => {
	propertyStore.setSelectedType(activeTab.value)
	let filtered = propertyStore.filteredProperties

	// Don't show child units
	if (activeTab.value === 'all') {
		filtered = filtered.filter(p => !p.parent_property_id)
	}

	return filtered
})

const paginatedProperties = computed(() => {
	const start = (currentPage.value - 1) * perPage.value
	const end = start + perPage.value
	return currentTabProperties.value.slice(start, end)
})

const totalPages = computed(() => {
	return Math.ceil(currentTabProperties.value.length / perPage.value)
})

const propertiesCounts = computed(() => {
	return {
		all: propertyStore.allProperties.filter(p => !p.parent_property_id).length,
		unit: propertyStore.unitProperties.length,
		land: propertyStore.landProperties.length,
		complex: propertyStore.complexProperties.length,
		building: propertyStore.buildingProperties.length,
	}
})

// Methods
const handleTabChange = (tab: string) => {
	activeTab.value = tab
	currentPage.value = 1
}

const handleCreateProperty = () => {
	const type = activeTab.value === 'all' ? 'unit' : activeTab.value
	navigateToCreate(type)
}

const handleViewProperty = (propertyId: number) => {
	// console.log('handleViewProperty called with ID:', propertyId)
	// console.log('Available properties:', propertyStore.properties.length)

	const property = propertyStore.properties.find(p => p.id === propertyId)

	if (property) {
		selectedProperty.value = property
		showPropertyDetails.value = true
		console.log('Modal should show now. showPropertyDetails:', showPropertyDetails.value)
		console.log('selectedProperty:', selectedProperty.value)
	} else {
		console.error('Property not found:', propertyId)
		Swal.fire({
			icon: 'error',
			title: 'Error',
			text: `Property with ID ${propertyId} not found`,
		})
	}
}

const handleEditProperty = (propertyId: number) => {
	navigateToEdit(propertyId)
}
const handleAddPropertyExpense = (propertyId: number) => {
	navigateToPropertyExpense(propertyId)
}

const handleContractsProperty = (propertyId: number) => {
	navigateToContracts(propertyId)
}

const handleConfirmDelete = (propertyId: number) => {
	const property = propertyStore.properties.find(p => p.id === propertyId)
	if (property) {
		selectedProperty.value = property
		showDeleteConfirm.value = true
	} else {
		console.error('Property not found:', propertyId)
	}
}

const handleDeleteProperty = async () => {
	if (!selectedProperty.value) return

	isDeleting.value = true
	const success = await deletePropertyAction(selectedProperty.value.id)
	isDeleting.value = false

	if (success) {
		showDeleteConfirm.value = false
		selectedProperty.value = null
	}
}

const handleSearch = ({ field, query }: { field: string; query: string }) => {
	propertyStore.setSearchField(field)
	propertyStore.setSearchQuery(query)
	currentPage.value = 1
}

const handleClearSearch = () => {
	propertyStore.clearSearch()
	currentPage.value = 1
}

const goToPage = (page: number) => {
	if (page >= 1 && page <= totalPages.value) {
		currentPage.value = page
	}
}
const lastMonthIndexInEachYear = ref<number[]>([])
onMounted(() => {
	// Get properties from blade template
	const propertiesData = (window as any).propertiesData
	const emptyRows = (window as any).emptyRowsData
	if (propertiesData) {
		propertyStore.setProperties(propertiesData as Property[])
		propertyStore.setEmptyRow(emptyRows as EmptyRow)
		propertyStore.setLoading(false)
	}
})
</script>
<template>
	<div class="row">
		<div class="col-md-12" v-if="propertyStore.isLoading">
			<div class="kt-portlet">
				<div class="kt-portlet__body exclude">
					<div class="col-md-12">
						<Loading :isLoading="propertyStore.isLoading"></Loading>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div v-if="!propertyStore.isLoading" class="kt-portlet kt-portlet--tabs">
		<div class="kt-portlet__head">
			<div class="kt-portlet__head-toolbar justify-content-between flex-grow-1">
				<ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand"
					role="tablist">
					<li class="nav-item">
						<a class="nav-link" :class="{ active: activeTab === 'all' }"
							@click.prevent="handleTabChange('all')" href="javascript:void(0)" role="tab">
							<i class="fa fa-th-large"></i> {{ $t('All Properties') }}
							<span class="badge badge-pill badge-secondary ml-1">{{ propertiesCounts.all }}</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" :class="{ active: activeTab === 'unit' }"
							@click.prevent="handleTabChange('unit')" href="javascript:void(0)" role="tab">
							<i class="fa fa-home"></i> {{ $t('Units') }}
							<span class="badge badge-pill badge-primary ml-1">{{ propertiesCounts.unit }}</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" :class="{ active: activeTab === 'land' }"
							@click.prevent="handleTabChange('land')" href="javascript:void(0)" role="tab">
							<i class="fa fa-map"></i> {{ $t('Lands') }}
							<span class="badge badge-pill badge-success ml-1">{{ propertiesCounts.land }}</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" :class="{ active: activeTab === 'complex' }"
							@click.prevent="handleTabChange('complex')" href="javascript:void(0)" role="tab">
							<i class="fa fa-building"></i> {{ $t('Complexes') }}
							<span class="badge badge-pill badge-warning ml-1">{{ propertiesCounts.complex }}</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" :class="{ active: activeTab === 'building' }"
							@click.prevent="handleTabChange('building')" href="javascript:void(0)" role="tab">
							<i class="fa fa-city"></i> {{ $t('Buildings') }}
							<span class="badge badge-pill badge-info ml-1">{{ propertiesCounts.building }}</span>
						</a>
					</li>
				</ul>
				<div class="flex-tabs">
					<button @click="handleCreateProperty"
						class="btn btn-2-bg bg-white-hover new-study-item rounded btn-icon-sm align-self-center">
						<i class="fas fa-plus white-icon exclude-icon"></i>
						{{ $t('New Property') }}
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
								<button v-if="propertyStore.searchQuery" @click="handleClearSearch"
									class="btn btn-danger btn-bold btn-sm mb-2 ml-2">
									<i class="fa fa-times exclude-icon default-icon-color"></i> {{ $t('Clear') }}
								</button>
							</div>
							<div class="kt-portlet__head-toolbar" v-if="propertyStore.searchQuery">
								<span class="badge badge-info p-2">
									{{ $t('Searching') }}: {{ propertyStore.searchField }} = "{{
										propertyStore.searchQuery }}" </span>
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
												<th class="interval-class header-border-down text-center">
													{{ $t('Nature') }}
												</th>
												<th class="header-border-down text-center">
													{{ $t('Name') }}
												</th>
												<th class="min-w-160 header-border-down text-center">
													{{ $t('Code') }}
												</th>
												<!-- <th class="interval-class header-border-down text-center">
													{{ $t('Country') }}
												</th> -->
												<th class="interval-class header-border-down text-center">
													{{ $t('Category') }}
												</th>
												<th class="interval-class header-border-down text-center">
													{{ $t('Type') }}
												</th>
												<th v-html="activeTab == 'unit' || activeTab == 'land' ? $t('Status') : $t('Units')"
													class="interval-class header-border-down text-center">
												</th>
												<th class="interval-class header-border-down text-center">
													{{ $t('Acquisition Cost') }}
												</th>
												<th class="interval-class header-border-down text-center">
													{{ $t('Current Market Value') }}
												</th>
												<th class="interval-class header-border-down text-center">
													{{ $t('Actions') }}
												</th>
											</tr>
										</thead>
										<tbody>
											<tr v-if="paginatedProperties.length === 0">
												<td colspan="9" class="text-center py-5">
													<i class="fa fa-inbox fa-3x text-muted mb-3 d-block"></i>
													<p class="text-muted">{{ $t('No properties found') }}</p>
												</td>
											</tr>
											<PropertyTableRow :model="property" :currentActiveContract="activeTab"
												v-for="property in paginatedProperties" :key="property.id"
												:property="property" :emptyRows="propertyStore.emptyRows"
												@view="handleViewProperty" @edit="handleEditProperty"
												@addPropertyExpense="handleAddPropertyExpense"
												@delete="handleConfirmDelete" @contracts="handleContractsProperty" />
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
		</div>
	</div>
	<!-- Modals -->
	<PropertyDetailsModal :show="showPropertyDetails" :property="selectedProperty" @close="showPropertyDetails = false"
		@edit="handleEditProperty" @delete="handleConfirmDelete" />
	<DeleteConfirmModal :show="showDeleteConfirm" :property-name="selectedProperty?.name" :is-deleting="isDeleting"
		@close="showDeleteConfirm = false" @confirm="handleDeleteProperty" />
	<SearchModal :show="showSearch" :initial-search-field="propertyStore.searchField"
		:initial-search-query="propertyStore.searchQuery" @close="showSearch = false" @search="handleSearch" />
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

.min-w-160 {
	min-width: 160px !important;
}
</style>
