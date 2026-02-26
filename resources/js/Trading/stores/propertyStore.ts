import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { Contract } from './contractStore'

export interface ContractInstallment {
	id: number
	contract_id: number
	installment_type: 'regular' | 'variable'
	installment_amount: number
	installment_date: string
	variable_installment_amounts: ContractInstallmentDetail[]
	installment_payments: ContractInstallmentPayment[]
}
export interface EmptyRow {
	regular_installments_amounts: {
		
		amount: number
		installment_count: number
		start_date: {
			month: number
			year: number
		}
		end_date: {
			month: number
			year: number
		}
		installment_payment_interval: string
	}
};
export interface ContractInstallmentDetail {
	id: number
	contract_installment_id: number
	date: {
		month: number
		year: number
	}
	amount: number
}
export interface ContractInstallmentPayment {
	id: number
	contract_installment_id: number
	payment_date: string
	payment_amount: number
}
export interface Property {
	id: number
	name: string
	code: string
	nature_id: 'unit' | 'land' | 'complex' | 'building'
	nature?: string // Nature name from backend (optional)
	country: string
	governorate: string
	city_id: number | null
	area: number
	unit_of_measurement: string
	acquisition_cost: number
	current_book_value: number
	market_value: number
	book_value_date: string
	parent_property_id: number | null,
	installments: ContractInstallment[],
	contracts: Contract[],
	units?: Property[]
}



export const usePropertyStore = defineStore('property', () => {
	// State
	const Tradings = ref<Property[]>([])
	const isLoading = ref<boolean>(false)
	const selectedType = ref<string>('all')
	const searchQuery = ref<string>('')
	const searchField = ref<string>('name')
	
	// Getters
	const allTradings = computed(() => Tradings.value)
	
	const unitTradings = computed(() => 
		Tradings.value.filter(p => p.nature_id === 'unit' && !p.parent_property_id)
	)
	
	const landTradings = computed(() => 
		Tradings.value.filter(p => p.nature_id === 'land')
	)
	
	const complexTradings = computed(() => 
		Tradings.value.filter(p => p.nature_id === 'complex')
	)
	const emptyRows = ref<EmptyRow>({} as EmptyRow);
	const buildingTradings = computed(() => 
		Tradings.value.filter(p => p.nature_id === 'building')
	)
	
	const filteredTradings = computed(() => {
		let filtered = Tradings.value
		
		// Filter by type
		if (selectedType.value !== 'all') {
			filtered = filtered.filter(p => p.nature_id === selectedType.value)
		}
		
		// Filter by search
		if (searchQuery.value) {
			const query = searchQuery.value.toLowerCase()
			filtered = filtered.filter(property => {
				const value = property[searchField.value as keyof Property]
				if (value === null || value === undefined) return false
				return String(value).toLowerCase().includes(query)
			})
		}
		
		return filtered
	})
	
	const getPropertyById = computed(() => {
		return (id: number) => Tradings.value.find(p => p.id === id)
	})
	
	// Actions
	function setTradings(newTradings: Property[]) {
		Tradings.value = newTradings
	}
	function setEmptyRow(newEmptyRows: EmptyRow) {
		emptyRows.value = newEmptyRows
	}
	function addProperty(property: Property) {
		Tradings.value.push(property)
	}
	
	
	
	function removeProperty(id: number) {
		const index = Tradings.value.findIndex(p => p.id === id)
		if (index !== -1) {
			Tradings.value.splice(index, 1)
		}
	}
	
	function setLoading(loading: boolean) {
		isLoading.value = loading
	}
	
	function setSelectedType(type: string) {
		selectedType.value = type
	}
	
	function setSearchQuery(query: string) {
		searchQuery.value = query
	}
	
	function setSearchField(field: string) {
		searchField.value = field
	}
	
	function clearSearch() {
		searchQuery.value = ''
		searchField.value = 'name'
	}
	
	function $reset() {
		Tradings.value = []
		isLoading.value = false
		selectedType.value = 'all'
		searchQuery.value = ''
		searchField.value = 'name'
	}
	
	return {
		// State
		Tradings,
		isLoading,
		selectedType,
		searchQuery,
		searchField,
		
		// Getters
		allTradings,
		unitTradings,
		landTradings,
		complexTradings,
		buildingTradings,
		filteredTradings,
		getPropertyById,
		
		// Actions
		setTradings,
		setEmptyRow,
		addProperty,

		removeProperty,
		setLoading,
		setSelectedType,
		setSearchQuery,
		setSearchField,
		clearSearch,
		$reset,
	}
})
