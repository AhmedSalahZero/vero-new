import axios from 'axios'
import Swal from 'sweetalert2'

import { useContractStore, type Contract } from '../stores/contractStore'

export function useContract() {
	const contractStore = useContractStore()
	const getBaseUrl = () => {
		const body = document.querySelector('body') as HTMLBodyElement
		return {
			baseUrl: body.dataset.baseUrl,
			companyId: body.dataset.currentCompanyId,
			lang: body.dataset.lang,
			csrfToken: body.dataset.token,
		}
	}
	
	const navigateToCreate = () => {
		const { baseUrl, companyId, lang } = getBaseUrl()
		const propertyId = window.location.pathname.split('/').slice(-2, -1)[0]
		window.location.href = `${baseUrl}/${lang}/${companyId}/property-managements/Tradings/${propertyId}/contracts/create`
	}
	const navigateToTradings = () => {
		const { baseUrl, companyId, lang } = getBaseUrl()
		window.location.href = `${baseUrl}/${lang}/${companyId}/property-managements/Tradings`
	}	
	
	const navigateToEdit = (contractId: number) => {
		const { baseUrl, companyId, lang } = getBaseUrl()
		const propertyId = window.location.pathname.split('/').slice(-2, -1)[0]
		window.location.href = `${baseUrl}/${lang}/${companyId}/property-managements/Tradings/${propertyId}/contracts/${contractId}/edit`
	}
	
	const deleteContract = async (contractId: number): Promise<boolean> => {
		const { baseUrl, companyId, lang, csrfToken } = getBaseUrl()
		const propertyId = window.location.pathname.split('/').slice(-2, -1)[0]
		const deleteUrl = `${baseUrl}/${lang}/${companyId}/property-managements/Tradings/${propertyId}/contracts/${contractId}/destroy`
		
		try {
			const response = await axios.delete(deleteUrl, {
				headers: {
					'X-CSRF-TOKEN': csrfToken,
					Accept: 'application/json',
				},
			})
			
			contractStore.remove(contractId)
			
			await Swal.fire({
				icon: 'success',
				title: 'Success',
				text: response.data.message || 'Contract deleted successfully',
				timer: 2000,
			})
			
			return true
		} catch (error: any) {
			const errorMessage = error.response?.data?.message || 'Error deleting contract'
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
	
	const getContractTypeLabel = (contract: Contract): string => {
		const type = contract.status
		const labels: Record<string, string> = {
			running: 'Running',
			finished: 'Finished',
			expired: 'Expired',
		}
		return labels[type] || type
	}
	
	const getContractTypeBadgeClass = (contract: Contract): string => {
		const type = contract.status
		const classes: Record<string, string> = {
			running: 'badge-primary',
			finished: 'badge-success',
			expired: 'badge-warning',
		}
		return classes[type] || 'badge-secondary'
	}
	
	return {
		// Store
		contractStore,
		
		// Actions
		navigateToCreate,
		navigateToTradings,
		navigateToEdit,
		deleteContract,
		
		// Helpers
		formatCurrency,
		getContractTypeLabel,
		getContractTypeBadgeClass,
	}
}
