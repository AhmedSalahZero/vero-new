<script setup lang="ts">
interface Props {
	show: boolean
	isDeleting?: boolean
}

defineProps<Props>()
const emit = defineEmits(['close', 'confirm'])
</script>
<template>
	<div v-if="show" @click.self="emit('close')" class="modal fade show"
		style="display: block; padding-right: 15px; background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
		aria-modal="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header  text-white">
					<h5 class="modal-title">
						<i class="fa fa-exclamation-triangle "></i> {{ $t('Confirm Delete') }}
					</h5>
					<button type="button" class="close text-white" @click="emit('close')">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body text-center py-4">
					<!-- <i class="fa fa-trash-alt fa-3x text-danger mb-3"></i> -->
					<h5 class="mb-3 text-left">{{ $t('Do You Want To Delete This Item ?') }}</h5>
					<!-- <p v-if="propertyName" class="text-muted">
						{{ $t('Property') }}: <strong>{{ propertyName }}</strong>
					</p>
					<p class="text-warning small">
						<i class="fa fa-info-circle"></i>
						{{ $t('This action cannot be undone') }}
					</p> -->
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" @click="emit('close')" :disabled="isDeleting">
						<i class="fa fa-times"></i> {{ $t('Cancel') }}
					</button>
					<button type="button" class="btn btn-danger" @click="emit('confirm')" :disabled="isDeleting">
						<span v-if="isDeleting" class="spinner-border spinner-border-sm mr-2"></span>
						<i v-else class="fa fa-trash exclude-icon default-icon-color"></i>
						{{ isDeleting ? $t('Deleting...') : $t('Confirm Delete') }}
					</button>
				</div>
			</div>
		</div>
	</div>
</template>
<style scoped>
.modal-header.bg-danger {
	background-color: #dc3545 !important;
}
</style>
