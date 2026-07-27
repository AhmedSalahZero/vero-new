<script setup lang="ts">
import { computed } from 'vue'
import { useProperty } from '../../../../composables/useProperty'
import type { Property } from '../../../../stores/propertyStore'

interface Props {
	property: Property | null
	show: boolean
}

const props = defineProps<Props>()
const emit = defineEmits(['close', 'edit', 'delete'])

const { formatCurrency, getPropertyTypeLabel, getPropertyTypeBadgeClass } = useProperty()

const hasUnits = computed(() => {
	if (!props.property) return false
	const type = props.property.nature_id
	return (type === 'complex' || type === 'building') && props.property.units && props.property.units.length > 0
})

const isUnitOrLand = computed(() => {
	if (!props.property) return false
	const type = props.property.nature_id
	return type === 'unit' || type === 'land'
})
</script>
<template>
	<div v-if="show" @click.self="emit('close')" class="modal fade show"
		style="display: block; padding-right: 15px; background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
		aria-modal="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
			<div class="modal-content" v-if="property">
				<div class="modal-header header-border">
					<h5 class="modal-title d-flex align-items-center gap-2">
						<i class="fa fa-building"></i>
						{{ property.name }}
						<span class="badge ml-2" :class="getPropertyTypeBadgeClass(property)">
							{{ getPropertyTypeLabel(property) }}
						</span>
					</h5>
					<button type="button" class="close" @click="emit('close')">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<!-- Basic Information -->
						<div class="col-md-12 mb-4">
							<h6 class="font-weight-bold text-primary mb-3">
								<i class="fa fa-info-circle"></i> {{ $t('Basic Information') }}
							</h6>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="font-weight-bold">{{ $t('Code') }}</label>
										<p class="form-control-plaintext text-black font-weight-bold">
											{{ property.code }}
										</p>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="font-weight-bold">{{ $t('Type') }}</label>
										<p class="form-control-plaintext text-black">
											<span class="badge" :class="getPropertyTypeBadgeClass(property)">
												{{ getPropertyTypeLabel(property) }}
											</span>
										</p>
									</div>
								</div>
								<!-- <div class="col-md-3">
									<div class="form-group">
										<label class="font-weight-bold">{{ $t('Country') }}</label>
										<p class="form-control-plaintext text-black font-weight-bold">{{ property.country || '-' }}
										</p>
									</div>
								</div> -->
								<div class="col-md-3">
									<div class="form-group">
										<label class="font-weight-bold">{{ $t('Governorate') }}</label>
										<p class="form-control-plaintext text-black font-weight-bold">
											{{ property.governorate || '-' }}
										</p>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="font-weight-bold">{{ $t('Province') }}</label>
										<p class="form-control-plaintext text-black font-weight-bold">
											{{ property.city || '-' }}
										</p>
									</div>
								</div>
							</div>
						</div>
						<!-- Financial Information (for Unit/Land) -->
						<div v-if="isUnitOrLand" class="col-md-12 mb-4">
							<h6 class="font-weight-bold text-success mb-3">
								<i class="fa fa-dollar-sign"></i> {{ $t('Financial Information') }}
							</h6>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="font-weight-bold">{{ $t('Area') }}</label>
										<p class="form-control-plaintext text-black">{{ property.area }}
											{{ property.unit_of_measurement }}
										</p>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="font-weight-bold">{{ $t('Acquisition Cost') }}</label>
										<p class="form-control-plaintext text-black">
											{{ formatCurrency(property.acquisition_cost) }} EGP
										</p>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="font-weight-bold">{{ $t('Book Value') }}</label>
										<p class="form-control-plaintext text-black">
											{{ formatCurrency(property.current_book_value) }} EGP
										</p>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="font-weight-bold">{{ $t('Book Value Date') }}</label>
										<p class="form-control-plaintext text-black">
											{{ property.book_value_date }}
										</p>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="font-weight-bold">{{ $t('Current Market Value') }}</label>
										<p class="form-control-plaintext text-black text-success font-weight-bold">
											{{ formatCurrency(Number(property.latest_market_value)) }} EGP
										</p>
									</div>
								</div>
							</div>
						</div>
						<!-- Units Information (for Complex/Building) -->
						<div v-if="hasUnits" class="col-md-12 mb-4">
							<h6 class="font-weight-bold text-warning mb-3">
								<i class="fa fa-th"></i> {{ $t('Units') }} ({{ property.units!.length }})
							</h6>
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover table-checkable">
									<thead class="thead-light">
										<tr>
											<th class="header-border-down  text-center">
												{{ $t('Name') }}
											</th>
											<th class="header-border-down  text-center">
												{{ $t('Code') }}
											</th>
											<th class="header-border-down  text-center">
												{{ $t('Area') }}
											</th>
											<th class="header-border-down  text-center">
												{{ $t('Acquisition Cost') }}
											</th>
											<th class="header-border-down  text-center">
												{{ $t('Current Value') }}
											</th>
											<th class="header-border-down  text-center">
												{{ $t('Book Value Date') }}
											</th>
											<th class="header-border-down  text-center">
												{{ $t('Market Value') }}
											</th>
										</tr>
									</thead>
									<tbody>
										<tr data-repeater-style v-for="unit in property.units" :key="unit.id">
											<td>{{ unit.name }}</td>
											<td><span class="badge badge-primary">{{ unit.code }}</span></td>
											<td>{{ unit.area }} {{ unit.unit_of_measurement }}</td>
											<td>{{ formatCurrency(unit.acquisition_cost) }} EGP</td>
											<td>{{ formatCurrency(unit.current_book_value) }} EGP</td>
											<td>{{ unit.book_value_date }}</td>
											<td class="text-success font-weight-bold">
												{{ formatCurrency(unit.latest_market_value) }} EGP
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" @click="emit('close')">
						<i class="fa fa-times exclude-icon default-icon-color"></i> {{ $t('Close') }}
					</button>
					<button v-if="property" type="button" class="btn btn-primary" @click="emit('edit', property.id)">
						<i class="fa fa-edit exclude-icon default-icon-color"></i> {{ $t('Edit') }}
					</button>
					<button v-if="property" type="button" class="btn btn-danger" @click="emit('delete', property.id)">
						<i class="fa fa-trash exclude-icon default-icon-color"></i> {{ $t('Delete') }}
					</button>
				</div>
			</div>
			<div v-else class="modal-content">
				<div class="modal-body text-center py-5">
					<p class="text-muted">{{ $t('Loading property details...') }}</p>
				</div>
			</div>
		</div>
	</div>
</template>
<style scoped>
.header-border {
	border-bottom: 2px solid #007bff;
}

.modal-body {
	max-height: 70vh;
	overflow-y: auto;
}

.form-control-plaintext text-black {
	padding-left: 0;
	border: none;
	background: transparent;
}

.gap-2 {
	gap: 0.5rem;
}

.table-sm td,
.table-sm th {
	padding: 0.5rem;
	font-size: 0.875rem;
}
</style>
