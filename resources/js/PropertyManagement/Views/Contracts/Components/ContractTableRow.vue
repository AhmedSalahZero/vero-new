<script setup lang="ts">
import { useContract } from '../../../composables/useContract';
import type { Contract } from '../../../stores/contractStore';

interface Props {
	contract: Contract
}

const props = defineProps<Props>()
const emit = defineEmits(['view', 'edit', 'delete', 'contracts'])

const { formatCurrency, getContractTypeBadgeClass, getContractTypeLabel } = useContract()

const isRunning = () => {
	const type = props.contract.status
	return type === 'running'
}

const isFinished = () => {
	const type = props.contract.status
	return type === 'finished'
}

const isExpired = () => {
	const type = props.contract.status
	return type === 'expired'
}
</script>
<template>
	<tr data-repeater-style class="hover-row">
		<td class="text-center d-flex align-items-center justify-content-center">
			<span class="badge w-100px h-100px d-flex align-items-center justify-content-center"
				:class="getContractTypeBadgeClass(contract)">
				{{ getContractTypeLabel(contract) }}
			</span>
		</td>
		<td>
			<input :value="contract.tenant_name" disabled class="form-control text-left" type="text">
		</td>
		<td>
			<input :value="contract.contract_start_date" disabled class="form-control text-center" type="text">
		</td>
		<td>
			<input :value="contract.contract_end_date" disabled class="form-control text-center" type="text">
		</td>
		<td>
			<input :value="contract.monthly_rent + ' ' + contract.contract_currency" disabled
				class="form-control text-left" type="text">
		</td>
		<td>
			<input :value="contract.collection_currency" disabled class="form-control text-left" type="text">
		</td>
		<td>
			<input :value="contract.collection_interval" disabled class="form-control text-left" type="text">
		</td>
		<td class="kt-datatable__cell--left kt-datatable__cell" data-field="Actions">
			<span style="overflow: visible; position: relative; width: 200px;display: flex;gap: 0.5rem;">
				<button type="button" @click="emit('view', contract.id)"
					class="btn btn-info btn-outline-hover-info btn-icon edit-btn-class exclude-btn"
					:title="$t('View Details')">
					<i class="fa fa-eye exclude-icon default-icon-color"></i>
				</button>
				<button type="button" @click="emit('edit', contract.id)"
					class="btn btn-secondary btn-outline-hover-brand btn-icon edit-btn-class exclude-btn"
					:title="$t('Edit')">
					<i class="fa fa-pen-alt exclude-icon default-icon-color"></i>
				</button>
				<button type="button" @click="emit('contracts', contract.id)"
					class="btn btn-warning btn-outline-hover-warning btn-icon contract-btn-class exclude-btn"
					:title="$t('Add Rent Contract')">
					<i class="fa fa-file-contract exclude-icon default-icon-color"></i>
				</button>
				<button type="button" @click="emit('delete', contract.id)"
					class="btn delete-btn-class btn-secondary btn-outline-hover-danger btn-icon exclude-btn"
					:title="$t('Delete')">
					<i class="fa fa-trash-alt exclude-icon default-icon-color"></i>
				</button>
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
</style>
