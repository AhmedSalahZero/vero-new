<script setup lang="ts">
import Select from 'primevue/select'
import InputText from 'primevue/inputtext'
import { computed } from 'vue'
import Label from '../../../../components/Form/Label.vue'

interface Props {
	modelValue: any
	selects: {
		natures: any[]
		ownerships: any[]
		countries: any[]
		categories: any[]
	}
	isInitialLoad?: boolean
}

const props = defineProps<Props>()
const emit = defineEmits(['update:modelValue'])

const model = computed({
	get: () => props.modelValue,
	set: (value) => emit('update:modelValue', value)
})

const selectedCountry = computed(() => {
	return props.selects.countries.find((c: any) => c.id === model.value.country_id)
})

const governorates = computed(() => {
	return selectedCountry.value?.governorates || []
})

const selectedGovernorate = computed(() => {
	return governorates.value.find((g: any) => g.id === model.value.governorate_id)
})

const cities = computed(() => {
	return selectedGovernorate.value?.cities || []
})

// Watch for country change to reset governorate and city
const onCountryChange = () => {
	if (!props.isInitialLoad) {
		model.value.governorate_id = null
		model.value.city_id = null
	}
}

// Watch for governorate change to reset city
const onGovernorateChange = () => {
	if (!props.isInitialLoad) {
		model.value.city_id = null
	}
}
</script>
<template>
	<div class="row">
		<div class="col-md-2">
			<Label :required="true"> {{ $t('Name') }} </Label>
			<InputText v-model="model.name" fluid />
		</div>
		<div class="col-md-2">
			<Label :required="true">
				{{ $t('Ownership') }}
			</Label>
			<Select filter v-model="model.ownership_id" :options="selects.ownerships" optionLabel="title"
				optionValue="id" placeholder="" checkmark :highlightOnSelect="false" class="w-full md:w-56" />
		</div>
		<div class="col-md-2">
			<Label :required="true">
				{{ $t('Code (Unique)') }}
			</Label>
			<InputText v-model="model.code" fluid />
		</div>
		<div class="col-md-2">
			<Label :required="true">
				{{ $t('Country') }}
			</Label>
			<Select filter v-model="model.country_id" :options="selects.countries" optionLabel="title" optionValue="id"
				checkmark :highlightOnSelect="false" class="w-full md:w-56" @change="onCountryChange" />
		</div>
		<div class="col-md-2">
			<Label :required="true">
				{{ $t('Governorate') }}
			</Label>
			<Select filter v-model="model.governorate_id" :options="governorates" optionLabel="title" optionValue="id"
				checkmark :highlightOnSelect="false" class="w-full md:w-56" @change="onGovernorateChange" />
		</div>
		<div class="col-md-2">
			<Label :required="false">
				{{ $t('Province') }}
			</Label>
			<Select filter v-model="model.city_id" :options="cities" optionLabel="title" optionValue="id" checkmark
				:highlightOnSelect="false" class="w-full md:w-56" />
		</div>
	</div>
</template>
<style scoped>
:deep(.p-select) {
	border: 1px solid #4d9afa;
}

:deep(.p-select-label.p-placeholder),
:deep(.p-select-label) {
	color: black !important;
}

:deep(.p-select:not(.p-disabled).p-focus) {
	border-color: #4d9afa;
}

:deep(.p-component) {
	height: 38px !important;
}

:deep(.p-select-label) {
	display: flex;
	align-items: center;
}

:deep(.p-inputtext) {
	border: 1px solid #4d9afa !important;
}

:deep(.dp__input) {
	border: 1px solid #4d9afa !important;
}
</style>
