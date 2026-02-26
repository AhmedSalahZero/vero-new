<script setup lang="ts">
import axios from 'axios'
import InputNumber from 'primevue/inputnumber'
import Swal from 'sweetalert2'
import Label from '../../../components/Form/Label.vue'
import { computed, onMounted, onBeforeUnmount, ref, watch, nextTick } from 'vue'
import Loading from '../../../components/Common/Loading.vue'
import Helper from '../../../Helpers/Helper'
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4themes_animated from '@amcharts/amcharts4/themes/animated'

am4core.useTheme(am4themes_animated)

// properties
const dates = ref<string[]>([]);
// const totalDueInstallments = ref<number[]>([]);
// const totalCollections = ref<number[]>([]);
const totalOfCollections = computed(() => {
	let result = 0;
	for (let dateAsIndex = 0; dateAsIndex < dates.value.length; dateAsIndex++) {
		const totalCollectionAtDate = parseFloat(model.value.cash_in.total_collections[dateAsIndex] ?? 0);
		result += totalCollectionAtDate;
	}
	return result
})
const totalOfCashIn = computed(() => {
	let result = 0;
	for (let dateAsIndex = 0; dateAsIndex < dates.value.length; dateAsIndex++) {
		const totalCashInAtDate = parseFloat(totalCashInAtDates.value[dateAsIndex] ?? 0);
		result += totalCashInAtDate
	}
	return result
})
const totalOfCashOut = computed(() => {
	let result = 0;
	for (let dateAsIndex = 0; dateAsIndex < dates.value.length; dateAsIndex++) {
		const totalCashOutAtDate = parseFloat(totalCashOutAtDates.value[dateAsIndex] ?? 0);
		result += totalCashOutAtDate
	}
	return result
})
const totalOfDueInstallments = computed(() => {
	let result = 0;
	for (let dateAsIndex = 0; dateAsIndex < dates.value.length; dateAsIndex++) {
		const totalDueInstallmentAtDate = parseFloat(model.value.cash_out.total_due_installments[dateAsIndex] ?? 0);
		result += totalDueInstallmentAtDate
	}
	return result
})
const totalOfNetCash = computed(() => {
	return totalOfCashIn.value - totalOfCashOut.value
})
const accumulatedNetCashAtDates = computed(() => {
	const result: Record<number, number> = {};
	for (let dateAsIndex = 0; dateAsIndex < dates.value.length; dateAsIndex++) {
		const previousAccumulatedNetCash = result[dateAsIndex - 1] ?? 0;
		const netAtDate = (parseFloat(totalCashInAtDates.value[dateAsIndex] ?? 0) - parseFloat(totalCashOutAtDates.value[dateAsIndex] ?? 0));
		result[dateAsIndex] = previousAccumulatedNetCash + netAtDate;
	}
	return result;
});

// Chart data for amCharts: date (category) + values
const twoLineChartData = computed(() => {
	if (!dates.value?.length) return [];
	return dates.value.map((dateStr: string, i: number) => ({
		date: dateStr,
		totalCashIn: parseFloat(totalCashInAtDates.value[i] ?? 0),
		totalCashOut: parseFloat(totalCashOutAtDates.value[i] ?? 0),
	}));
});
const accumulatedLineChartData = computed(() => {
	if (!dates.value?.length) return [];
	return dates.value.map((dateStr: string, i: number) => ({
		date: dateStr,
		value: parseFloat(accumulatedNetCashAtDates.value[i] ?? 0),
	}));
});

const chartTwoLineRef = ref<HTMLElement | null>(null);
const chartAccumulatedRef = ref<HTMLElement | null>(null);
let chartTwoLine: am4core.Sprite | null = null;
let chartAccumulated: am4core.Sprite | null = null;

const years = ref<string[]>([])
interface TableDates {
	bank_lending: number[]
	profit_assumptions: number[]
	forecast_dashboard: number[]
}

const hideTablesDates = ref<TableDates>({
	bank_lending: [],
	profit_assumptions: [],
	forecast_dashboard: [],
})
const getYearsFromDates = computed<Record<string, string>>(() => {
	let result: Record<string, string> = {}
	Object.keys(dates.value).forEach((dateAsIndex: string) => {
		result[dateAsIndex] = dates.value[dateAsIndex].split("'").pop()!
	})
	return result
})

const lastMonthIndexInEachYear = ref<number[]>([])



const disableSubmitBtn = ref<boolean>(false)
const isLoading = ref<boolean>(true)
const submitUrl = ref<string>('')
const overviewUrl = ref<string>('')
const dashboardDate = ref<string>(new Date().toISOString().slice(0, 10))

const model = ref<{ [key: string]: any }>({});
interface showAndHidType {
	bank_lending: boolean
	profit_assumptions: boolean
	reserve_assumption: boolean
	forecast_dashboard: boolean
}
const showAndHide = ref<showAndHidType>({
	bank_lending: true,
	profit_assumptions: true,
	reserve_assumption: true,
	forecast_dashboard: true,
})

// methods



const handleRepeatRight = (items: string[], dateAsIndex: number, dates: string[]) => {
	Helper.repeatRight(items, dateAsIndex, dates)
}

const getModelData = () => {
	const body = document.querySelector('body') as HTMLBodyElement
	const csrfToken = body.dataset.token
	const baseUrl = body.dataset.baseUrl
	const companyId = body.dataset.currentCompanyId
	const lang = body.dataset.lang

	overviewUrl.value = `${baseUrl}/${lang}/${companyId}/property-managements/property-dashboard`
	const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/property-managements/dashboard/cashflow-forecast-old-data`
	axios
		.get(fetchOldDataUrl, {
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				Accept: 'application/json',
			},
		})
		.then((response) => {
			dates.value = response.data.dates
			// totalDueInstallments.value = response.data.total_due_installments
			// totalCollections.value = response.data.total_collections
			model.value = response.data.model
			submitUrl.value = response.data.submitUrl
			console.log('submitUrl', submitUrl.value);
			isLoading.value = false
		})
		.catch((error) => {
			isLoading.value = false
			const errorMessage = error.response?.data?.message || 'An error occurred' + error
			Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: errorMessage,
			})
		})
}
const totalCashInAtDates = computed(() => {
	const result = {};
	for (let dateAsIndex = 0; dateAsIndex < dates.value.length; dateAsIndex++) {
		const totalCollectionAtDate = parseFloat(model.value.cash_in.total_collections[dateAsIndex] ?? 0);
		result[dateAsIndex] = totalCollectionAtDate + model.value.cash_in.sub_items.reduce((acc: number, subItem: any) => {
			return acc + parseFloat(subItem.values[dateAsIndex])
		}, 0)
	}
	return result
})
const totalCashOutAtDates = computed(() => {
	const result = {};
	for (let dateAsIndex = 0; dateAsIndex < dates.value.length; dateAsIndex++) {
		const totalDueInstallmentAtDate = parseFloat(model.value.cash_out.total_due_installments[dateAsIndex] ?? 0);
		result[dateAsIndex] = totalDueInstallmentAtDate + model.value.cash_out.sub_items.reduce((acc: number, subItem: any) => {
			return acc + parseFloat(subItem.values[dateAsIndex])
		}, 0)
	}
	console.log('totalCashOutAtDates', result);
	return result
})
const submitForm = (e: Event) => {
	const target = e.target as HTMLButtonElement
	if (model.value) {
		model.value.submit_button = target.getAttribute('data-button-value')
	}
	disableSubmitBtn.value = true
	const body = document.querySelector('body') as HTMLBodyElement
	const csrfToken = body.dataset.token
	axios
		.post(submitUrl.value, model.value, {
			headers: {
				'X-CSRF-TOKEN': csrfToken,
				Accept: 'application/json',
			},
		})
		.then((response) => {
			disableSubmitBtn.value = false
			Swal.fire({
				icon: 'success',
				title: 'Success',
				text: 'Your data has been saved',
				draggable: true,
				timer: 2000,
			}).then((res: Object) => {
				disableSubmitBtn.value = false
				if (response.data.redirectTo) {
					window.location.href = response.data.redirectTo
				}
			})
		})
		.catch((error) => {
			const errorMessage = error.response?.data?.message || 'An error occurred'
			disableSubmitBtn.value = false
			Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: errorMessage,
			})
		})
}

const addNewSubItem = (type: string) => {
	model.value[type].sub_items.push({
		name: '',
		values: dates.value.map(() => 0),
	})
}
const deleteSubItem = (type: string, index: number) => {
	model.value[type].sub_items.splice(index, 1)
}

function createCharts() {
	if (!chartTwoLineRef.value || !chartAccumulatedRef.value) return;

	// Dispose existing
	if (chartTwoLine) {
		chartTwoLine.dispose();
		chartTwoLine = null;
	}
	if (chartAccumulated) {
		chartAccumulated.dispose();
		chartAccumulated = null;
	}

	// Two-line chart: Total Cash In & Total Cash Out
	chartTwoLine = am4core.create(chartTwoLineRef.value, am4charts.XYChart);
	chartTwoLine.colors.step = 2;
	chartTwoLine.data = twoLineChartData.value;

	const catAxisTwo = chartTwoLine.xAxes.push(new am4charts.CategoryAxis());
	catAxisTwo.dataFields.category = 'date';
	catAxisTwo.renderer.grid.template.location = 0;
	catAxisTwo.renderer.minGridDistance = 30;

	chartTwoLine.yAxes.push(new am4charts.ValueAxis());

	const seriesIn = chartTwoLine.series.push(new am4charts.LineSeries());
	seriesIn.dataFields.categoryX = 'date';
	seriesIn.dataFields.valueY = 'totalCashIn';
	seriesIn.name = 'Total Cash In';
	seriesIn.strokeWidth = 2;
	seriesIn.tension = 1;
	seriesIn.tooltipText = '{name}: [bold]{valueY}[/]';
	seriesIn.defaultState.transitionDuration = 1200;
	const bulletIn = seriesIn.bullets.push(new am4charts.CircleBullet());
	bulletIn.circle.radius = 5;
	bulletIn.circle.fill = am4core.color('#fff');
	bulletIn.circle.strokeWidth = 2;

	const seriesOut = chartTwoLine.series.push(new am4charts.LineSeries());
	seriesOut.dataFields.categoryX = 'date';
	seriesOut.dataFields.valueY = 'totalCashOut';
	seriesOut.name = 'Total Cash Out';
	seriesOut.strokeWidth = 2;
	seriesOut.tension = 1;
	seriesOut.tooltipText = '{name}: [bold]{valueY}[/]';
	seriesOut.defaultState.transitionDuration = 1200;
	const bulletOut = seriesOut.bullets.push(new am4charts.CircleBullet());
	bulletOut.circle.radius = 5;
	bulletOut.circle.fill = am4core.color('#fff');
	bulletOut.circle.strokeWidth = 2;

	chartTwoLine.legend = new am4charts.Legend();
	chartTwoLine.cursor = new am4charts.XYCursor();

	// One-line chart: Accumulated Net Cash
	chartAccumulated = am4core.create(chartAccumulatedRef.value, am4charts.XYChart);
	chartAccumulated.data = accumulatedLineChartData.value;

	const catAxisAcc = chartAccumulated.xAxes.push(new am4charts.CategoryAxis());
	catAxisAcc.dataFields.category = 'date';
	catAxisAcc.renderer.grid.template.location = 0;
	catAxisAcc.renderer.minGridDistance = 30;

	chartAccumulated.yAxes.push(new am4charts.ValueAxis());

	const seriesAcc = chartAccumulated.series.push(new am4charts.LineSeries());
	seriesAcc.dataFields.categoryX = 'date';
	seriesAcc.dataFields.valueY = 'value';
	seriesAcc.name = 'Accumulated Net Cash';
	seriesAcc.strokeWidth = 2;
	seriesAcc.tension = 1;
	seriesAcc.tooltipText = '{categoryX}: [bold]{valueY}[/]';
	seriesAcc.defaultState.transitionDuration = 1200;
	const bulletAcc = seriesAcc.bullets.push(new am4charts.CircleBullet());
	bulletAcc.circle.radius = 5;
	bulletAcc.circle.fill = am4core.color('#fff');
	bulletAcc.circle.strokeWidth = 2;

	chartAccumulated.legend = new am4charts.Legend();
	chartAccumulated.cursor = new am4charts.XYCursor();
}

watch(
	() => [isLoading.value, twoLineChartData.value.length],
	() => {
		if (!isLoading.value && twoLineChartData.value.length > 0) {
			nextTick(createCharts);
		}
	},
	{ immediate: true }
);

watch([twoLineChartData, accumulatedLineChartData], () => {
	if (chartTwoLine && twoLineChartData.value.length) {
		chartTwoLine.data = twoLineChartData.value;
	}
	if (chartAccumulated && accumulatedLineChartData.value.length) {
		chartAccumulated.data = accumulatedLineChartData.value;
	}
});

onMounted(() => {
	getModelData();
});

onBeforeUnmount(() => {
	if (chartTwoLine) {
		chartTwoLine.dispose();
		chartTwoLine = null;
	}
	if (chartAccumulated) {
		chartAccumulated.dispose();
		chartAccumulated = null;
	}
});
</script>
<template>
	<div class="row">
		<div class="col-md-12" v-if="isLoading">
			<div class="kt-portlet">
				<div class="kt-portlet__body exclude">
					<div class="col-md-12">
						<Loading :isLoading="isLoading"></Loading>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div v-if="!isLoading">
		<!-- Dashboard Results row + Overview / Forecast nav tabs (same as property_dashboard.blade.php) -->
		<div class="kt-portlet">
			<div class="kt-portlet__head w-full sky-border">
				<div class="kt-portlet__head-label w-full">
					<h3 class="kt-portlet__head-title head-title text-primary w-full">
						<div class="row mb-3">
							<div class="col-md-2">
								<label class="visibility-hidden">{{ $t('Currency') }}</label>
								<h3
									class="font-weight-bold text-black form-label kt-subheader__title small-caps mr-5 text-nowrap">
									{{ $t('Dashboard Results') }}
								</h3>
							</div>
							<div class="col-md-2">
								<label class="visibility-hidden">{{ $t('Currency') }}</label>
								<div class="kt-input-icon">
									<div class="input-group date">
										<input v-model="model.dashboard_date" type="date" class="form-control"
											placeholder="Select date" />
										<div class="input-group-append">
											<span class="input-group-text">
												<i class="la la-calendar-check-o"></i>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-3 kt-align-right">
								<label class="visibility-hidden">{{ $t('Currency') }}</label>
								<div class="input-group">
									<button type="button" @click="submitForm($event)" :disabled="disableSubmitBtn"
										data-button-value="save"
										class="btn active-style save-form">{{ $t('Save') }}</button>
								</div>
							</div>
						</div>
					</h3>
				</div>
			</div>
			<div class="kt-portlet__body" style="padding-bottom:0 !important;">
				<ul style="margin-bottom:0 ;"
					class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand"
					role="tablist">
					<li class="nav-item">
						<a class="nav-link" :href="overviewUrl" role="tab">
							<i class="flaticon2-checking icon-lg"></i>
							<span style="font-size:18px !important;">{{ $t('Overview') }}</span>
						</a>
					</li>
					<li class="nav-item active">
						<span class="nav-link active" role="tab">
							<i class="flaticon2-checking icon-lg"></i>
							<span style="font-size:18px !important;">{{ $t('Forecast') }}</span>
						</span>
					</li>
				</ul>
			</div>
		</div>
		<!-- start Minimum Cash Balance  -->
		<div class="kt-portlet">
			<div class="kt-portlet__body">
				<div class="row">
					<div class="col-md-11">
						<div class="d-flex align-items-center">
							<h3 class="font-weight-bold form-label kt-subheader__title small-caps">
								{{ $t('Cashflow Forecast') }}
							</h3>
						</div>
					</div>
					<div class="col-md-1">
						<div class="d-flex justify-content-end">
							<div @click="showAndHide.forecast_dashboard = !showAndHide.forecast_dashboard"
								class="btn show-hide-style"> Show/Hide </div>
						</div>
					</div>
					<div class="col-md-12">
						<hr style="background-color: lightgray" />
					</div>
				</div>
				<div v-show="showAndHide.forecast_dashboard" class="row mt-4">
					<div class="col-md-12 overflow-scroll">
						<table class="table">
							<thead>
								<tr>
									<th class="header-border-down"></th>
									<th
										class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
										Item </th>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<th
											class="form-label expandable-percentage-input font-weight-bold text-center align-middle header-border-down">
											<span
												class="text-center d-inline-block">{{ Helper.formatDateAsFullMonthNameAndYear(dateFormatted) }}
												<br />
											</span>
										</th>
										<!--  start Total Yr. 2026 for example -->
										<!--  end Total Yr. 2026 for example -->
									</template>
									<th
										class="form-label expandable-percentage-input font-weight-bold text-center align-middle header-border-down">
										<span class="text-center d-inline-block"> {{ $t('Total') }} <br />
										</span>
									</th>
									<!-- start total of all years for the current row -->
									<!-- end total of all years for the current row -->
								</tr>
							</thead>
							<tbody>
								<!-- Total Cash In Row -->
								<tr :data-repeater-style="true">
									<td colspan="2">
										<div class="d-flex flex-column align-items-center">
											<input :value="$t('Total Cash In')" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td>
											<div class="d-flex flex-column max-w-175 align-items-center">
												<InputNumber :modelValue="totalCashInAtDates[dateAsIndex] ?? 0" :min="0"
													input-class="text-center" :minFractionDigits="0"
													:maxFractionDigits="0" :disabled="true" suffix=" EGP" fluid />
												<i @click="handleRepeatRight(model.min_cash_balances, dateAsIndex, dates)"
													class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
													style="visibility: hidden;" title="Repeat Right"></i>
											</div>
										</td>
									</template>
									<td>
										<div class="d-flex flex-column max-w-175 align-items-center">
											<InputNumber :modelValue="totalOfCashIn" :min="0" input-class="text-center"
												:minFractionDigits="0" :maxFractionDigits="0" :disabled="true"
												suffix=" EGP" fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
								</tr>
								<tr :data-repeater-style="true">
									<td></td>
									<td>
										<div class="d-flex flex-column align-items-center">
											<input :value="$t('Total Collection')" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td>
											<div class="d-flex flex-column max-w-175 align-items-center">
												<InputNumber v-model="model.cash_in.total_collections[dateAsIndex]"
													:min="0" input-class="text-center" :minFractionDigits="0"
													:maxFractionDigits="0" :disabled="true" suffix=" EGP" fluid />
												<i @click="handleRepeatRight(model.min_cash_balances, dateAsIndex, dates)"
													class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
													style="visibility: hidden;" title="Repeat Right"></i>
											</div>
										</td>
									</template>
									<td>
										<div class="d-flex flex-column max-w-175 align-items-center">
											<InputNumber :modelValue="totalOfCollections" :min="0"
												input-class="text-center" :minFractionDigits="0" :maxFractionDigits="0"
												:disabled="true" suffix=" EGP" fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
								</tr>
								<tr v-for="(subItem, subItemIndex) in model.cash_in.sub_items" :key="subItemIndex"
									:data-repeater-style="true">
									<td>
										<Label style="visibility: hidden">ddd</Label>
										<div :style="{ visibility: subItemIndex == 0 ? 'hidden' : 'visible' }"
											class="d-flex flex-column justify-content-start align-items-start">
											<!-- <label style="visibility: hidden">Delete</label> -->
											<button @click="deleteSubItem('cash_in', subItemIndex)" type="button"
												class="btn btn-danger btn-md btn-danger-style ml-2" title="Delete">
												<i class="fas exclude-icon fa-trash trash-icon"></i>
											</button>
										</div>
									</td>
									<td>
										<div class="d-flex flex-column align-items-center">
											<input v-model="subItem.name"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td>
											<div class="d-flex flex-column max-w-175 align-items-center">
												<InputNumber
													v-model="model.cash_in.sub_items[subItemIndex].values[dateAsIndex]"
													:min="0" input-class="text-center" :minFractionDigits="0"
													:maxFractionDigits="0" suffix=" EGP" fluid />
												<i @click="handleRepeatRight(model.cash_in.sub_items[subItemIndex].values, dateAsIndex, dates)"
													class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
													title="Repeat Right"></i>
											</div>
										</td>
									</template>
									<td>
										<div class="d-flex flex-column max-w-175 align-items-center">
											<!-- {{}} -->
											<InputNumber
												:modelValue="model.cash_in.sub_items[subItemIndex].values.reduce((acc: number, value: number) => acc + value, 0)"
												:min="0" input-class="text-center" :minFractionDigits="0"
												:maxFractionDigits="0" :disabled="true" suffix=" EGP" fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="row">
											<div class="col-md-6">
												<input @click="addNewSubItem('cash_in')" data-repeater-create=""
													type="button" class="btn btn-primary btn-sm text-white mb-4"
													:value="$t('Add Cash In')" />
											</div>
										</div>
									</td>
								</tr>
								<!-- end Total Cash In Row -->
								<!-- Total Cash Out Row -->
								<tr :data-repeater-style="true">
									<td colspan="2">
										<div class="d-flex flex-column align-items-center">
											<input :value="$t('Total Cash Out')" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td>
											<div class="d-flex flex-column max-w-175 align-items-center">
												<InputNumber :modelValue="totalCashOutAtDates[dateAsIndex] ?? 0"
													:min="0" input-class="text-center" :minFractionDigits="0"
													:maxFractionDigits="0" :disabled="true" suffix=" EGP" fluid />
												<i @click="handleRepeatRight(model.min_cash_balances, dateAsIndex, dates)"
													class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
													style="visibility: hidden;" title="Repeat Right"></i>
											</div>
										</td>
									</template>
									<td>
										<div class="d-flex flex-column max-w-175 align-items-center">
											<InputNumber :modelValue="totalOfCashOut" :min="0" input-class="text-center"
												:minFractionDigits="0" :maxFractionDigits="0" :disabled="true"
												suffix=" EGP" fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
								</tr>
								<tr :data-repeater-style="true">
									<td></td>
									<td>
										<div class="d-flex flex-column align-items-center">
											<input :value="$t('Total Installments')" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td>
											<div class="d-flex flex-column max-w-175 align-items-center">
												<InputNumber
													v-model="model.cash_out.total_due_installments[dateAsIndex]"
													:min="0" input-class="text-center" :minFractionDigits="0"
													:maxFractionDigits="0" :disabled="true" suffix=" EGP" fluid />
												<i @click="handleRepeatRight(model.min_cash_balances, dateAsIndex, dates)"
													class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
													style="visibility: hidden;" title="Repeat Right"></i>
											</div>
										</td>
									</template>
									<td>
										<div class="d-flex flex-column max-w-175 align-items-center">
											<InputNumber :modelValue="totalOfDueInstallments" :min="0"
												input-class="text-center" :minFractionDigits="0" :maxFractionDigits="0"
												:disabled="true" suffix=" EGP" fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
								</tr>
								<tr v-for="(subItem, subItemIndex) in model.cash_out.sub_items" :key="subItemIndex"
									:data-repeater-style="true">
									<td>
										<Label style="visibility: hidden">ddd</Label>
										<div :style="{ visibility: subItemIndex == 0 ? 'hidden' : 'visible' }"
											class="d-flex flex-column justify-content-start align-items-start">
											<!-- <label style="visibility: hidden">Delete</label> -->
											<button @click="deleteSubItem('cash_out', subItemIndex)" type="button"
												class="btn btn-danger btn-md btn-danger-style ml-2" title="Delete">
												<i class="fas exclude-icon fa-trash trash-icon"></i>
											</button>
										</div>
									</td>
									<td>
										<div class="d-flex flex-column align-items-center">
											<input v-model="subItem.name"
												class="form-control min-width-300 text-left mt-2" type="text" />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td>
											<div class="d-flex flex-column max-w-175 align-items-center">
												<InputNumber
													v-model="model.cash_out.sub_items[subItemIndex].values[dateAsIndex]"
													:min="0" input-class="text-center" :minFractionDigits="0"
													:maxFractionDigits="0" suffix=" EGP" fluid />
												<i @click="handleRepeatRight(model.cash_out.sub_items[subItemIndex].values, dateAsIndex, dates)"
													class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
													title="Repeat Right"></i>
											</div>
										</td>
									</template>
									<td>
										<div class="d-flex flex-column max-w-175 align-items-center">
											<!-- {{}} -->
											<InputNumber
												:modelValue="model.cash_out.sub_items[subItemIndex].values.reduce((acc: number, value: number) => acc + value, 0)"
												:min="0" input-class="text-center" :minFractionDigits="0"
												:maxFractionDigits="0" :disabled="true" suffix=" EGP" fluid />
											<i style="visibility: hidden" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="row">
											<div class="col-md-6">
												<input @click="addNewSubItem('cash_out')" data-repeater-create=""
													type="button" class="btn btn-primary btn-sm text-white mb-4"
													:value="$t('Add Cash Out')" />
											</div>
										</div>
									</td>
								</tr>
								<!-- end Total Cash Out Row -->
								<!-- start Net Cash  Row [Total Cash In - Total Cash Out] -->
								<tr :data-repeater-style="true">
									<td colspan="2">
										<div class="d-flex flex-column align-items-center">
											<input :value="$t('Net Cash')" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td>
											<div class="d-flex flex-column max-w-175 align-items-center">
												<InputNumber
													:modelValue="totalCashInAtDates[dateAsIndex] - totalCashOutAtDates[dateAsIndex]"
													:min="0" input-class="text-center" :minFractionDigits="0"
													:maxFractionDigits="0" :disabled="true" suffix=" EGP" fluid />
												<i style="display: none;" class="fa fa-ellipsis-h"></i>
											</div>
										</td>
									</template>
									<td>
										<div class="d-flex flex-column max-w-175 align-items-center">
											<InputNumber :modelValue="totalOfNetCash" :min="0" input-class="text-center"
												:minFractionDigits="0" :maxFractionDigits="0" :disabled="true"
												suffix=" EGP" fluid />
											<i style="display: none;" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
								</tr>
								<tr :data-repeater-style="true">
									<td colspan="2">
										<div class="d-flex flex-column align-items-center">
											<input :value="$t('Accumulated Net Cash')" :disabled="true"
												class="form-control min-width-300 text-left mt-2" type="text" />
										</div>
									</td>
									<template v-for="(dateFormatted, dateAsIndex) in dates" :key="dateAsIndex">
										<td>
											<div class="d-flex flex-column max-w-175 align-items-center">
												<InputNumber :modelValue="accumulatedNetCashAtDates[dateAsIndex]"
													:min="0" input-class="text-center" :minFractionDigits="0"
													:maxFractionDigits="0" :disabled="true" suffix=" EGP" fluid />
												<i style="display: none;" class="fa fa-ellipsis-h"></i>
											</div>
										</td>
									</template>
									<td>
										<div class="d-flex flex-column max-w-175 align-items-center">
											<InputNumber :modelValue="'-'" :min="0" input-class="text-center"
												:minFractionDigits="0" :maxFractionDigits="0" :disabled="true"
												suffix=" EGP" fluid />
											<i style="display: none;" class="fa fa-ellipsis-h"></i>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="kt-portlet">
			<div class="kt-portlet__body">
				<div class="row">
					<div class="col-md-6">
						<h4 class="font-weight-bold text-primary mb-3">Total Cash In vs Total Cash Out</h4>
						<div ref="chartTwoLineRef" class="chart-container-two-line"></div>
					</div>
					<div class="col-md-6">
						<h4 class="font-weight-bold text-primary mb-3">Accumulated Net Cash</h4>
						<div ref="chartAccumulatedRef" class="chart-container-accumulated"></div>
					</div>
				</div>
			</div>
		</div>
		<!-- end Net Cash Row -->
		<!-- start Accumulated Net Cash Sub Items Row -->
		<!-- start Accumulated Net Cash Row -->
		<div class="col-md-12">
			<div class="d-flex align-items-center justify-content-end" style="gap: 5px">
				<button v-if="!isLoading" @click="submitForm" :disabled="disableSubmitBtn" data-button-value="save"
					type="submit" class="btn text-white active-style save-form">
					<!--  -->
					<span v-if="disableSubmitBtn && model.submit_button == 'save'"
						class="spinner-border mr-2 spinner-border-sm mb-1" data-button-value="save" role="status"
						aria-hidden="true"></span>
					<span class="text-lg" data-button-value="save"
						v-html="disableSubmitBtn && model.submit_button == 'save' ? 'Saving...' : 'Save'">
					</span>
				</button>
				<button v-if="!isLoading" @click="submitForm" :disabled="disableSubmitBtn"
					data-button-value="save-and-go-to-next-value" type="submit"
					class="btn text-white active-style save-form">
					<!--  -->
					<span v-if="disableSubmitBtn && model.submit_button == 'save-and-go-to-next-value'"
						class="spinner-border mr-2 spinner-border-sm mb-1" data-button-value="save-and-go-to-next-value"
						role="status" aria-hidden="true"></span>
					<span class="text-lg" data-button-value="save-and-go-to-next-value" v-html="disableSubmitBtn && model.submit_button == 'save-and-go-to-next-value'
						? 'Saving...'
						: 'Save & Go To Next'
						">
					</span>
				</button>
			</div>
		</div>
	</div>
</template>
<style scoped>
.chart-container-two-line,
.chart-container-accumulated {
	width: 100%;
	min-height: 350px;
	height: 400px;
}

.max-w-70px {
	max-width: 70px !important;
}

.max-w-185px {
	max-width: 185px !important;
}

.max-w-200px {
	max-width: 200px !important;
}

.w-200px {
	width: 200px !important;
}

.max-w-530px {
	max-width: 530px !important;
}

.input-border {
	border: 1px solid #6babef;
}

/* Fix z-index for PrimeVue Select dropdown inside modal */
:deep(.p-select-overlay) {
	z-index: 1060 !important;
}

/* Alternative: Fix for all PrimeVue overlays inside modals */
.modal {
	z-index: 990 !important;
}

.modal-body {
	max-height: 800px;
}

:deep(.p-skeleton) {
	background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
	background-size: 200% 100%;
	animation: loading 1.5s ease-in-out infinite;
}

@keyframes loading {
	0% {
		background-position: 200% 0;
	}

	100% {
		background-position: -200% 0;
	}
}

.btn-div {
	padding: 0 !important;
	width: 30px !important;
	height: 30px !important;
}

.btn-div span {
	font-size: 20px !important;
	cursor: pointer;
}

.trash_icon {
	width: 30px;
	height: 30px;
	display: flex;
	justify-content: center;
	align-items: center;
	cursor: pointer;
}

:deep(.p-component, .dp__input) {
	height: 38px !important;
}

:deep(.p-select) {
	border: 1px solid #4d9afa;
}

/* Or target the input more specifically */
:deep(.dp__input) {
	height: 38px !important;
}

:deep(.p-select-label) {
	display: flex;
	align-items: center;
}

:deep(.p-multiselect-label-container) {
	display: flex;
	align-items: center;
}

.btn-danger-style {
	padding-right: 9px;
	padding-left: 9px;
	padding-top: 13px;
	padding-bottom: 13px;
}

.btn-danger-style i {
	padding-right: 0 !important;
	color: white !important;
	font-size: 0.9rem !important;
}

:deep(.p-select-label.p-placeholder),
:deep(.p-select-label) {
	color: black !important;
}

:deep(.p-multiselect-label) {
	color: black !important;
}

:deep(.p-select:not(.p-disabled).p-focus) {
	border-color: #4d9afa;
}

:deep(.p-select) {
	border-color: #4d9afa !important;
}

.max-w-150 {
	width: 150px !important;
	min-width: 150px !important;
	max-width: 150px !important;
}

.max-w-175 {
	width: 175px !important;
	min-width: 175px !important;
	max-width: 175px !important;
}

.col {
	flex-shrink: 1;
	min-width: 0;
}

.min-w-160 {
	min-width: 160px !important;
}

.min-w-140 {
	min-width: 140px !important;
}

.min-w-percentage {
	width: 110px !important;
}

:deep(.p-inputnumber) {
	min-width: 75px !important;
}

.sky-border {
	border-bottom: 1.5px solid blue !important;
}
</style>
