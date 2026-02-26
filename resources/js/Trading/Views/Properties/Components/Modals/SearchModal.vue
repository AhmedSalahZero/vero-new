<script setup lang="ts">
import { ref, watch } from 'vue';

interface Props {
	show: boolean
	initialSearchField?: string
	initialSearchQuery?: string
}

const props = withDefaults(defineProps<Props>(), {
	initialSearchField: 'name',
	initialSearchQuery: '',
})

const emit = defineEmits(['close', 'search'])

const searchField = ref(props.initialSearchField)
const searchQuery = ref(props.initialSearchQuery)

watch(() => props.show, (newVal) => {
	if (newVal) {
		searchField.value = props.initialSearchField
		searchQuery.value = props.initialSearchQuery
	}
})

const handleSearch = () => {
	emit('search', {
		field: searchField.value,
		query: searchQuery.value,
	})
	emit('close')
}
</script>
<template>
	<div v-if="show" @click.self="emit('close')" class="modal fade show"
		style="display: block; padding-right: 15px; background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
		aria-modal="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="fa fa-search"></i> {{ $t('Search Tradings') }}
					</h5>
					<button type="button" class="close" @click="emit('close')">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="font-weight-bold">
							<i class="fa fa-filter"></i> {{ $t('Search Field') }}
						</label>
						<select v-model="searchField" class="form-control">
							<option value="name">{{ $t('Name') }}</option>
							<option value="code">{{ $t('Code') }}</option>
							<option value="country">{{ $t('Country') }}</option>
							<option value="governorate">{{ $t('Governorate') }}</option>
							<option value="nature_id">{{ $t('Nature') }}</option>
						</select>
					</div>
					<div class="form-group">
						<label class="font-weight-bold">
							<i class="fa fa-keyboard"></i> {{ $t('Search Value') }}
						</label>
						<input v-model="searchQuery" type="text" class="form-control"
							:placeholder="$t('Enter search term')" @keyup.enter="handleSearch">
						<small class="form-text text-muted">
							{{ $t('Press Enter or click Search button') }}
						</small>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" @click="emit('close')">
						<i class="fa fa-times exclude-icon default-icon-color"></i> {{ $t('Close') }}
					</button>
					<button type="button" class="btn btn-primary" @click="handleSearch">
						<i class="fa fa-search exclude-icon default-icon-color"></i> {{ $t('Search') }}
					</button>
				</div>
			</div>
		</div>
	</div>
</template>
