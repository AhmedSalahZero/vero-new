import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export interface Contract {
	id: number
	property_id: number
	tenant_id: number
	tenant_nature: string
	tenant_type: string
	monthly_rent: number
	variable_from_tenant_revenues_percentage: number
	min_amount: number
	contract_currency: string
	collection_currency: string
	contract_start_date: string
	contract_end_date: string
	collection_interval: string
	insurance_months_count: number
	insurance_amount: number
	status: 'running' | 'finished' | 'expired'
	finished_date: string,
	annually_increase_rate: number,
	company_id: number,
	
}



export const useContractStore = defineStore('contract', () => {
	// State
	const contracts = ref<Contract[]>([])
	const isLoading = ref<boolean>(false)
	const selectedContractType = ref<string>('all')
	const searchQuery = ref<string>('')
	const searchField = ref<string>('name')
	
	// Getters
	const allContracts = computed(() => contracts.value)
	
	const runningContracts = computed(() => 
		contracts.value.filter(p => p.status === 'running' )
	)
	
	const finishedContracts = computed(() => 
		contracts.value.filter(p => p.status === 'finished' )
	)
	
	const expiredContracts = computed(() => {
		return contracts.value.filter(p => p.status === 'expired' )
		
	}
	)
	
	
	const filteredContracts = computed(() => {
		let filtered = contracts.value
		
	
		
		// Filter by search
		if (searchQuery.value) {
			const query = searchQuery.value.toLowerCase()
			filtered = filtered.filter(property => {
				const value = property[searchField.value as keyof Contract]
				if (value === null || value === undefined) return false
				return String(value).toLowerCase().includes(query)
			})
		}
		
		return filtered
	})

	
	// Actions
	function setContracts(newContracts: Contract[]) {
		contracts.value = newContracts
	}
	function setLoading(loading: boolean) {
		isLoading.value = loading
	}
	
	function setSelectedType(type: string) {
		selectedContractType.value = type
	}
	
	function setSearchQuery(query: string) {
		searchQuery.value = query
	}
	function remove(id: number) {
		const index = contracts.value.findIndex(p => p.id === id)
		if (index !== -1) {
			contracts.value.splice(index, 1)
		}
	}
	function setSearchField(field: string) {
		searchField.value = field
	}
	
	function clearSearch() {
		searchQuery.value = ''
		searchField.value = 'name'
	}
	
	function $reset() {
		contracts.value = []
		isLoading.value = false
		selectedContractType.value = 'all'
		searchQuery.value = ''
		searchField.value = 'name'
	}
	
	return {
		// State
		contracts,
		isLoading,
		selectedContractType,
		searchQuery,
		searchField,
		
		// Getters
		allContracts,
		runningContracts,
		finishedContracts,
		expiredContracts,
		filteredContracts,
	
		
		// Actions
		remove,
		setContracts,
		setLoading,
		setSelectedType,
		setSearchQuery,
		setSearchField,
		clearSearch,
		$reset,
	}
})
