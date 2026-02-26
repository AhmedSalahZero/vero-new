import axios from 'axios'
import Swal from 'sweetalert2'
import { usePropertyStore, type Property } from '../stores/propertyStore'

export function useProperty() {
	const propertyStore = usePropertyStore()
	
	const getBaseUrl = () => {
		const body = document.querySelector('body') as HTMLBodyElement
		return {
			baseUrl: body.dataset.baseUrl,
			companyId: body.dataset.currentCompanyId,
			lang: body.dataset.lang,
			csrfToken: body.dataset.token,
		}
	}
	
	const navigateToCreate = (propertyType: string = 'unit') => {
		const { baseUrl, companyId, lang } = getBaseUrl()
		window.location.href = `${baseUrl}/${lang}/${companyId}/property-managements/Tradings/create?type=${propertyType}`
	}
	
	const navigateToEdit = (propertyId: number) => {
		const { baseUrl, companyId, lang } = getBaseUrl()
		window.location.href = `${baseUrl}/${lang}/${companyId}/property-managements/Tradings/${propertyId}/edit`
	}
	const navigateToPropertyExpense = (propertyId: number) => {
		const { baseUrl, companyId, lang } = getBaseUrl()
		window.location.href = `${baseUrl}/${lang}/${companyId}/property-managements/Tradings/${propertyId}/property-expenses`
	}
	
	const navigateToContracts = (propertyId: number) => {
		const { baseUrl, companyId, lang } = getBaseUrl()
		window.location.href = `${baseUrl}/${lang}/${companyId}/property-managements/Tradings/${propertyId}/contracts`
	}
	
	const deleteProperty = async (propertyId: number): Promise<boolean> => {
		const { baseUrl, companyId, lang, csrfToken } = getBaseUrl()
		const deleteUrl = `${baseUrl}/${lang}/${companyId}/property-managements/Tradings/${propertyId}/destroy`
		
		try {
			const response = await axios.delete(deleteUrl, {
				headers: {
					'X-CSRF-TOKEN': csrfToken,
					Accept: 'application/json',
				},
			})
			
			propertyStore.removeProperty(propertyId)
			
			await Swal.fire({
				icon: 'success',
				title: 'Success',
				text: response.data.message || 'Property deleted successfully',
				timer: 2000,
			})
			
			return true
		} catch (error: any) {
			const errorMessage = error.response?.data?.message || 'Error deleting property'
			await Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: errorMessage,
			})
			return false
		}
	}
	
	const formatCurrency = (value: number): string => {
		if (!value) return '0'
		return new Intl.NumberFormat('en-US', {
			minimumFractionDigits: 0,
			maximumFractionDigits: 0,
		}).format(value)
	}
	
	const getPropertyTypeLabel = (property: Property): string => {
		const type = property.nature_id
		const labels: Record<string, string> = {
			unit: 'Unit',
			land: 'Land',
			complex: 'Complex',
			building: 'Building',
		}
	
		if((type == 'land' ||  type == 'unit') &&  property.parent_property_id != null) {
			return property.parent.name;
		}
		return labels[type] || type
	}
	
	const getPropertyTypeBadgeClass = (property: Property): string => {
		const type = property.nature_id
		const classes: Record<string, string> = {
			unit: 'badge-primary',
			land: 'badge-success',
			complex: 'badge-warning',
			building: 'badge-info',
		}
		return classes[type] || 'badge-secondary'
	}
	
	return {
		// Store
		propertyStore,
		
		// Actions
		navigateToCreate,
		navigateToEdit,
		navigateToContracts,
		navigateToPropertyExpense,
		deleteProperty,
		
		// Helpers
		formatCurrency,
		getPropertyTypeLabel,
		getPropertyTypeBadgeClass,
	}
}
