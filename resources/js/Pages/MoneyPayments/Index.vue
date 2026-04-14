<script setup>
/**
 * Inertia page migrated from resources/views/reports/moneyPayments/index.blade.php
 * Pass serialized rows, paginators, routes, and permissions from the controller.
 */
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, nextTick } from 'vue'

const props = defineProps({
	company: { type: Object, required: true },
	lang: { type: String, default: 'en' },
	csrfToken: { type: String, required: true },
	/** Pre-built URLs (e.g. route(...)) for all named routes used in the legacy Blade */
	routes: { type: Object, required: true },
	suppliersFormatted: { type: Object, default: () => ({}) },
	financialInstitutionBanks: { type: Array, default: () => [] },
	accountTypes: { type: Array, default: () => [] },
	banks: { type: Array, default: () => [] },
	selectedBanks: { type: Array, default: () => [] },
	filterDates: { type: Object, default: () => ({}) },
	payableChequesTableSearchFields: { type: Object, required: true },
	outgoingTransferTableSearchFields: { type: Object, required: true },
	payableCashTableSearchFields: { type: Object, required: true },
	payableCheques: { type: Object, required: true },
	outgoingTransfer: { type: Object, required: true },
	cashPayments: { type: Object, required: true },
	canCreateSupplierPayment: { type: Boolean, default: false },
	canUpdateSupplierPayment: { type: Boolean, default: false },
	canDeleteSupplierPayment: { type: Boolean, default: false },
	/** Active tab query: payable_cheque | outgoing-transfer | cash_payment */
	activeTab: { type: String, default: '' },
	/** Same as App\Models\MoneyPayment::* */
	moneyPayment: { type: Object, required: true },
	/** Request query for filters (field, value, from, to) */
	requestQuery: { type: Object, default: () => ({}) },
	/** now()->format('m/d/Y') for ajax default date */
	nowFormattedForPicker: { type: String, default: '' },
	/** getCurrencies() map for opening-balance cheque modal */
	getCurrencies: { type: Object, default: () => ({}) },
})

const MP = props.moneyPayment

function loadScript(src) {
	return new Promise((resolve, reject) => {
		const s = document.createElement('script')
		s.src = src
		s.onload = resolve
		s.onerror = reject
		document.body.appendChild(s)
	})
}

function bindIndexScripts() {
	const $ = window.jQuery || window.$
	if (!$) return

	$(document).on('click', '.js-can-trigger-cheque-under-collection-modal', function (e) {
		e.preventDefault()
		const moneyType = $(this).attr('data-money-type')
		const type = $(this).attr('data-type')
		$('#single-or-multi' + moneyType).val(type)
		if (type == 'single') {
			$('#current-single-item' + moneyType).val($(this).attr('data-id'))
			$('#current-currency' + moneyType).val($(this).attr('data-currency'))
			$('input[name="actual_payment_date"]').val($(this).attr('data-due-date'))
		} else {
			$('input[name="actual_payment_date"]').val(props.nowFormattedForPicker)
		}
	})

	$(document).on('submit', '.ajax-send-cheques-to-collection', function (e) {
		e.preventDefault()
		const url = $(this).attr('action')
		const moneyType = $(this).attr('data-money-type')
		const type = $('#single-or-multi' + moneyType).val()
		const singleId = parseInt($('#current-single-item' + moneyType).val())
		let checked = []
		$('.js-send-to-collection[data-money-type="' + moneyType + '"]:checked').each(function (index, element) {
			checked.push(parseInt($(element).val()))
		})
		const checkedItems = type == 'multi' ? checked : [singleId]
		let form = document.getElementById('ajax-send-cheques-to-collection-id' + moneyType)
		let formData = new FormData(form)
		formData.append('cheques', checkedItems)
		$.ajax({
			cache: false,
			contentType: false,
			processData: false,
			url: url,
			data: formData,
			type: 'post',
		})
			.then(function (res) {
				if (res.status === false) {
					window.Swal.fire({
						text: res.msg,
						icon: 'error',
						timer: 2000,
					}).then(function () {
						window.location.href = res.pageLink
					})
				} else {
					window.Swal.fire({
						text: 'Done',
						icon: 'success',
						timer: 2000,
					}).then(function () {
						window.location.href = res.pageLink
					})
				}
			})
			.catch((res) => {
				let title = window.__moneyPaymentIndexI18n?.error || 'Error !'
				let message = window.__moneyPaymentIndexI18n?.wrong || 'Something went Wrong'
				if (res.responseJSON && res.responseJSON.errors) {
					message = res.responseJSON.errors[Object.keys(res.responseJSON.errors)[0]][0]
				}
				window.Swal.fire({
					icon: 'error',
					title: title,
					text: message,
				})
			})
	})

	$(document).on('click', '.js-close-modal', function () {
		$(this).closest('.modal').modal('hide')
	})
	$(document).on('click', '#js-drawee-bank', function (e) {
		e.preventDefault()
		$('#js-choose-bank-id').modal('show')
	})
	$(document).on('click', '#js-append-bank-name-if-not-exist', function () {
		const receivingBank = document.getElementById('js-drawee-bank').parentElement
		const newBankId = $('#js-bank-names').val()
		const newBankName = $('#js-bank-names option:selected').attr('data-name')
		const isBankExist = $(receivingBank).find('select.js-drawl-bank').find('option[value="' + newBankId + '"]').length
		if (!isBankExist) {
			const option = '<option selected value="' + newBankId + '">' + newBankName + '</option>'
			$('#js-drawee-bank').parent().find('select.js-drawl-bank').append(option)
		}
		$('#js-choose-bank-id').modal('hide')
	})

	$(document).on('change', '.js-search-modal', function () {
		const searchFieldName = $(this).val()
		const popupType = $(this).attr('data-type')
		const modal = $(this).closest('.modal')
		if (searchFieldName === 'due_date') {
			$('.data-type-span').html('[ ' + (window.__moneyPaymentIndexI18n?.dueDate || 'Due Date') + ' ]')
			modal.find(modal).find('.search-field').val('').trigger('change').prop('disabled', true)
		} else if (searchFieldName == 'receiving_date') {
			$(modal).find('.search-field').val('').trigger('change').prop('disabled', true)
			modal.find('.data-type-span').html('[ ' + (window.__moneyPaymentIndexI18n?.paymentDate || 'Payment Date') + ' ]')
		} else if (searchFieldName == 'delivery_date') {
			$(modal).find('.search-field').val('').trigger('change').prop('disabled', true)
			modal.find('.data-type-span').html('[ ' + (window.__moneyPaymentIndexI18n?.paymentDate || 'Payment Date') + ' ]')
		} else {
			$(modal).find('.search-field').prop('disabled', false)
		}
	})
	$(function () {
		$('.js-search-modal').trigger('change')
	})

	$(document).on('show.bs.modal', '.editable-opening-balance-cheque', function () {
		$(this).find('select.select-for-currency-temp').addClass('select-for-currency').trigger('change')
	})
}

onMounted(async () => {
	window.__moneyPaymentIndexI18n = window.__moneyPaymentIndexI18n || {}
	try {
		if (!document.querySelector('script[src*="bootstrap-datepicker.min.js"]')) {
			await loadScript('/assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')
		}
		if (!document.querySelector('script[src*="bootstrap-datepicker.init.js"]')) {
			await loadScript('/assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js')
		}
		if (!document.querySelector('script[src*="widgets/bootstrap-datepicker.js"]')) {
			await loadScript('/assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js')
		}
		if (!document.querySelector('script[src*="bootstrap-select.js"]')) {
			await loadScript('/assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js')
		}
		if (!document.querySelector('script[src*="widgets/bootstrap-select.js"]')) {
			await loadScript('/assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js')
		}
		if (!document.querySelector('script[src*="jquery.repeater/src/lib.js"]')) {
			await loadScript('/assets/vendors/general/jquery.repeater/src/lib.js')
		}
		if (!document.querySelector('script[src*="jquery.input.js"]')) {
			await loadScript('/assets/vendors/general/jquery.repeater/src/jquery.input.js')
		}
		if (!document.querySelector('script[src*="repeater.js"]')) {
			await loadScript('/assets/vendors/general/jquery.repeater/src/repeater.js')
		}
		if (!document.querySelector('script[src*="form-repeater.js"]')) {
			await loadScript('/assets/js/demo1/pages/crud/forms/widgets/form-repeater.js')
		}
		if (!document.querySelector('script[src*="money-receive.js"]')) {
			await loadScript('/custom/money-receive.js')
		}
	} catch (_) {
		/* assets may already be on the layout */
	}
	nextTick(() => {
		const $ = window.jQuery || window.$
		if ($) {
			$(document).find('.datepicker-input').datepicker({
				dateFormat: 'mm-dd-yy',
				autoclose: true,
			})
		}
		bindIndexScripts()
	})
})

function tabActive(type) {
	if (!props.activeTab || props.activeTab === '') {
		return type === MP.PAYABLE_CHEQUE
	}
	return props.activeTab === type
}

</script>
<template>
	<div>

		<Head title="Money Payment Form" />
		<div class="mp-page kt-portlet kt-portlet--tabs">
			<div class="mp-header kt-portlet__head">
				<div class="kt-portlet__head-toolbar justify-content-between flex-grow-1">
					<ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand"
						role="tablist">
						<li class="nav-item">
							<a onclick="return false" class="nav-link" :class="{ active: tabActive(MP.PAYABLE_CHEQUE) }"
								data-toggle="tab" :href="'#' + MP.PAYABLE_CHEQUE" role="tab">
								<i class="fa fa-money-check-alt"></i> Payable Cheques </a>
						</li>
						<li class="nav-item">
							<a onclick="return false" class="nav-link"
								:class="{ active: tabActive(MP.OUTGOING_TRANSFER) }" data-toggle="tab"
								:href="'#' + MP.OUTGOING_TRANSFER" role="tab">
								<i class="fa fa-money-check-alt"></i> Outgoing Transfer </a>
						</li>
						<li class="nav-item">
							<a onclick="return false" class="nav-link" :class="{ active: tabActive(MP.CASH_PAYMENT) }"
								data-toggle="tab" :href="'#' + MP.CASH_PAYMENT" role="tab">
								<i class="fa fa-money-check-alt"></i> Cash Payment </a>
						</li>
					</ul>
					<div v-if="canCreateSupplierPayment" class="flex-tabs">
						<Link :href="routes.createMoneyPayment"
							class="btn btn-sm active-style btn-icon-sm align-self-center">
							<i class="fas fa-plus"></i> Money Payment
						</Link>
						<Link :href="routes.createMoneyPaymentDownPayment"
							class="btn btn-sm active-style btn-icon-sm align-self-center">
							<i class="fas fa-plus"></i> Down Payment
						</Link>
					</div>
				</div>
			</div>
			<div class="kt-portlet__body">
				<div class="tab-content kt-margin-t-20">
					<!-- Payable Cheques -->
					<div class="tab-pane" :class="{ active: tabActive(MP.PAYABLE_CHEQUE) }" :id="MP.PAYABLE_CHEQUE"
						role="tabpanel">
						<div class="kt-portlet kt-portlet--mobile mp-card">
							<div class="kt-portlet__head kt-portlet__head--lg p-0">
								<div class="kt-portlet__head-label ml-4" style="flex: 2.5">
									<span class="kt-portlet__head-icon">
										<i
											class="kt-font-secondary text-main-color btn-outline-hover-danger fa fa-layer-group"></i>
									</span>
									<h3 style="font-size: 20px !important"
										class="kt-portlet__head-title text-main-color text-nowrap">Payable Cheques</h3>
									<form class="w-full flex-2" method="get" :action="routes.viewMoneyPayment">
										<input type="hidden" name="active" :value="MP.PAYABLE_CHEQUE" />
										<div class="row align-items-center">
											<div class="col-md-3 d-flex align-items-center"
												style="margin-left: 5rem !important">
												<label :for="'startDate_' + MP.PAYABLE_CHEQUE"
													class="text-nowrap mr-3">Start Date</label>
												<input :id="'startDate_' + MP.PAYABLE_CHEQUE" type="date"
													class="form-control" :name="'startDate[' + MP.PAYABLE_CHEQUE + ']'"
													:value="filterDates[MP.PAYABLE_CHEQUE]?.startDate || ''" />
											</div>
											<div class="col-md-3 d-flex align-items-center">
												<label :for="'endDate_' + MP.PAYABLE_CHEQUE"
													class="text-nowrap mr-3">End Date</label>
												<input :id="'endDate_' + MP.PAYABLE_CHEQUE" type="date"
													class="form-control" :name="'endDate[' + MP.PAYABLE_CHEQUE + ']'"
													:value="filterDates[MP.PAYABLE_CHEQUE]?.endDate || ''" />
											</div>
											<div class="col-md-2 d-flex justify-content-center"
												style="margin-left: 2rem !important">
												<label for="button"></label>
												<button style="width: 70px !important; font-size: 1rem !important"
													type="submit"
													class="btn block form-control btn-primary btn-sm">Submit</button>
											</div>
										</div>
									</form>
								</div>
								<div class="kt-portlet__head-toolbar" style="flex: 1 !important">
									<div class="kt-portlet__head-wrapper">
										<div class="kt-portlet__head-actions"> &nbsp; <a
												:data-money-type="MP.PAYABLE_CHEQUE" data-type="multi"
												data-toggle="modal"
												:data-target="'#send-to-under-collection-modal' + MP.PAYABLE_CHEQUE"
												:id="'js-send-to-under-collection-trigger' + MP.PAYABLE_CHEQUE"
												:href="routes.createMoneyReceive"
												title="Please Select More Than One Cheque"
												class="btn active-style btn-icon-sm js-can-trigger-cheque-under-collection-modal disabled">
												<i class="fas fa-book"></i> Create Batch Mark As Paid </a>
											<a data-type="multi" data-toggle="modal"
												:data-target="'#search-money-modal-' + MP.PAYABLE_CHEQUE"
												id="js-search-money-received" href="#" title="Search Money Payments"
												class="btn active-style btn-icon-sm">
												<i class="fas fa-search"></i> Advanced Filter </a>
											<div class="modal fade" :id="'search-money-modal-' + MP.PAYABLE_CHEQUE"
												tabindex="-1" role="dialog" aria-hidden="true">
												<div class="modal-dialog modal-xl" role="document">
													<div class="modal-content">
														<div class="modal-header">
															<h5 class="modal-title">Filter Form</h5>
															<button type="button" class="close" data-dismiss="modal"
																aria-label="Close">
																<span aria-hidden="true">&times;</span>
															</button>
														</div>
														<div class="modal-body">
															<form :action="routes.viewMoneyPayment" class="row"
																method="get">
																<input name="active" type="hidden"
																	:value="MP.PAYABLE_CHEQUE" />
																<div class="form-group col-4">
																	<label class="label">Field Name</label>
																	<select
																		:id="'js-search-modal-name-' + MP.PAYABLE_CHEQUE"
																		:data-type="MP.PAYABLE_CHEQUE"
																		class="form-control js-search-modal"
																		name="field">
																		<option
																			v-for="(label, name) in payableChequesTableSearchFields"
																			:key="name" :value="name"
																			:selected="requestQuery.field === name">
																			{{ label }}
																		</option>
																	</select>
																</div>
																<div class="form-group col-4">
																	<label class="label">Search Text</label>
																	<input name="value" type="text"
																		:value="requestQuery.value || ''"
																		placeholder="Search Text"
																		class="form-control search-field" />
																</div>
																<div class="form-group col-2">
																	<label class="label">From <span
																			class="data-type-span">[ Receiving Date
																			]</span></label>
																	<input name="from" type="date"
																		:value="requestQuery.from || ''"
																		class="form-control" />
																</div>
																<div class="form-group col-2">
																	<label class="label">To <span
																			class="data-type-span">[ Receiving Date
																			]</span></label>
																	<input name="to" type="date"
																		:value="requestQuery.to || ''"
																		class="form-control" />
																</div>
																<div class="modal-footer">
																	<button type="submit"
																		:href="routes.viewMoneyReceive"
																		id="js-search-id"
																		class="btn btn-primary">Search</button>
																	<button href="#" id="reset-search-id" type="button"
																		class="btn btn-primary">Reset</button>
																</div>
															</form>
														</div>
													</div>
												</div>
											</div>
											<div class="modal fade"
												:id="'send-to-under-collection-modal' + MP.PAYABLE_CHEQUE" tabindex="-1"
												role="dialog" aria-hidden="true">
												<div class="modal-dialog modal-dialog-centered" role="document">
													<div class="modal-content">
														<form :data-money-type="MP.PAYABLE_CHEQUE"
															:id="'ajax-send-cheques-to-collection-id' + MP.PAYABLE_CHEQUE"
															class="ajax-send-cheques-to-collection"
															:action="routes.payableChequeMarkAsPaid" method="post">
															<input type="hidden"
																:id="'single-or-multi' + MP.PAYABLE_CHEQUE"
																value="single" />
															<input type="hidden"
																:id="'current-single-item' + MP.PAYABLE_CHEQUE"
																value="0" />
															<input type="hidden"
																:id="'current-currency' + MP.PAYABLE_CHEQUE"
																class="current-currency" value="" />
															<input type="hidden" name="_token" :value="csrfToken" />
															<div class="modal-header">
																<h5 class="modal-title">Do You Want To Mark This Cheque
																	/ Cheques As Paid ?</h5>
																<button type="button" class="close" aria-label="Close">
																	<span aria-hidden="true">&times;</span>
																</button>
															</div>
															<div class="modal-body">
																<div class="row mb-3">
																	<div class="col-md-12">
																		<label>Actual Payment Date</label>
																		<div class="kt-input-icon">
																			<div class="input-group date">
																				<input required type="text"
																					name="actual_payment_date"
																					class="form-control" readonly
																					placeholder="Select date"
																					id="kt_datepicker_2" />
																				<div class="input-group-append">
																					<span class="input-group-text">
																						<i
																							class="la la-calendar-check-o"></i>
																					</span>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
															<div class="modal-footer">
																<button type="button" class="btn btn-secondary"
																	data-dismiss="modal">Close</button>
																<button type="submit"
																	class="btn btn-success">Confirm</button>
															</div>
														</form>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="kt-portlet__body">
								<table
									class="table  table-striped- table-bordered table-hover table-checkable text-center kt_table_1 mp-table">
									<thead>
										<tr class="table-standard-color">
											<th class="align-middle">Select</th>
											<th class="align-middle">Type</th>
											<th class="align-middle bank-max-width">Status</th>
											<th class="align-middle bank-max-width">Supplier Name</th>
											<th class="align-middle" v-html="'Payment <br> Date'"></th>
											<th class="align-middle" v-html="'Cheque<br>Number'"></th>
											<th class="align-middle" v-html="'Cheque<br>Amount'"></th>
											<th class="align-middle">Currency</th>
											<th class="align-middle bank-max-width ">Payment Bank</th>
											<th class="align-middle bank-max-width">Account Type</th>
											<th class="align-middle">Account No</th>
											<th class="align-middle" v-html="'Due<br>Date'"></th>
											<th class="align-middle" v-html="'Due <br> After Days'"></th>
											<th class="align-middle" v-html="'Status'"></th>
											<th class="align-middle">Control</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="row in payableCheques.data" :key="row.id">
											<td>
												<input style="max-height:25px;" :id="'cash-send-to-collection' + row.id"
													type="checkbox" name="second_to_collection[]" :value="row.id"
													:data-money-type="MP.PAYABLE_CHEQUE"
													class="form-control checkbox js-send-to-collection" />
											</td>
											<td :class="row.payableChequeStatus === 'paid'
													? 'bank-max-width exclude-td font-weight-bold text-success color-green '
													: 'bank-max-width '
												">
												{{ row.payableChequeStatusFormatted }}
											</td>
											<td class="bank-max-width">{{ row.moneyTypeFormatted }}</td>
											<td class="bank-max-width">{{ row.supplierName }}</td>
											<td class="text-nowrap">{{ row.deliveryDateFormatted }}</td>
											<td>{{ row.chequeNumber }}</td>
											<td>{{ row.paidAmountFormatted }}</td>
											<td class="text-transform" :data-currency="row.currency">
												{{ row.currencyToPaymentCurrencyFormatted }}</td>
											<td class="bank-max-width ">{{ row.deliveryBankName }}</td>
											<td class="bank-max-width">{{ row.accountTypeName }}</td>
											<td class="text-nowrap">{{ row.accountNumber }}</td>
											<td class="text-nowrap">{{ row.dueDateFormatted }}</td>
											<td>{{ row.dueAfterDays }}</td>
											<td class="font-weight-bold bank-max-width"
												:style="{ color: row.dueStatusColor + '!important' }">
												<template v-if="row.payableChequeStatus === 'paid'">-</template>
												<template v-else>{{ row.dueStatusText }}</template>
											</td>
											<td class="kt-datatable__cell--left kt-datatable__cell" data-field="Actions"
												data-autohide-disabled="false">
												<span style="overflow: visible; position: relative; width: 110px">
													<template v-if="row.hasComment">
														<a :data-toggle="'modal'"
															:data-target="'#user-comment-' + row.id" type="button"
															class="btn btn-secondary btn-outline-hover-success btn-icon"
															title="User Comment" href="#"><i
																class="fa fa-comment"></i></a>
														<div class="modal fade" :id="'user-comment-' + row.id"
															tabindex="-1" role="dialog" aria-hidden="true">
															<div class="modal-dialog modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form action="#" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<div class="modal-header">
																			<h5 class="modal-title">User Comment</h5>
																			<button type="button" class="close"
																				data-dismiss="modal" aria-label="Close">
																				<span aria-hidden="true">&times;</span>
																			</button>
																		</div>
																		<div class="modal-body">
																			<h2 class="text-wrap"
																				:class="row.userCommentIsArabic ? 'text-right' : 'text-left'">
																				{{ row.userComment }}</h2>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-secondary"
																				data-dismiss="modal">Close</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
													<template v-if="row.showOdooError">
														<a :data-toggle="'modal'" :data-target="'#odoo-model-' + row.id"
															type="button" class="btn btn-icon bg-red text-white"
															title="Odoo Error" href="#"><i class="fa fa-bug"></i></a>
														<div class="modal fade" :id="'odoo-model-' + row.id"
															tabindex="-1" role="dialog" aria-hidden="true">
															<div class="modal-dialog modal-xl modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form :action="row.resendOdooUrl" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<div class="modal-header">
																			<h5 class="modal-title">Odoo Error</h5>
																			<button type="button" class="close"
																				data-dismiss="modal" aria-label="Close">
																				<span aria-hidden="true">&times;</span>
																			</button>
																		</div>
																		<div class="modal-body">
																			<h2 class="text-wrap"
																				:class="row.odooErrorIsArabic ? 'text-right' : 'text-left'">
																				{{ row.odooError }}</h2>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-secondary"
																				data-dismiss="modal">Close</button>
																			<button type="submit"
																				class="btn btn-success">Resend</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
													<template v-if="row.showIntegratedModal">
														<a :data-toggle="'modal'"
															:data-target="'#fully-integrated-id-' + row.id"
															type="button" class="btn btn-primary btn-icon"
															title="Fully Integrated" href="#"><i
																class="fa fa-thumbs-up"></i></a>
														<div class="modal fade" :id="'fully-integrated-id-' + row.id"
															tabindex="-1" role="dialog" aria-hidden="true">
															<div class="modal-dialog modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form action="#" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<div class="modal-header blue">
																			<h5 class="modal-title text-blue">Odoo
																				References</h5>
																			<button type="button" class="close"
																				data-dismiss="modal" aria-label="Close">
																				<span aria-hidden="true">&times;</span>
																			</button>
																		</div>
																		<div class="modal-body">
																			<ul class="list-unstyled">
																				<li v-for="(ref, ri) in row.odooReferenceNames"
																					:key="ri" class="mb-3 text-left">
																					{{ ref }}</li>
																			</ul>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-primary"
																				data-dismiss="modal">Close</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
													<template v-if="canUpdateSupplierPayment">
														<template v-if="row.showReviewModal">
															<a :data-toggle="'modal'"
																:data-target="'#review-id-' + row.id" type="button"
																class="btn btn-secondary btn-outline-hover-success btn-icon"
																title="Reviewed" href="#"><i
																	class="fa fa-check"></i></a>
															<div class="modal fade" :id="'review-id-' + row.id"
																tabindex="-1" role="dialog" aria-hidden="true">
																<div class="modal-dialog modal-dialog-centered"
																	role="document">
																	<div class="modal-content">
																		<form :action="row.confirmedReviewUrl"
																			method="post">
																			<input type="hidden" name="_token"
																				:value="csrfToken" />
																			<input type="hidden" name="model_name"
																				:value="row.modelNameForReview" />
																			<input type="hidden" name="table_name"
																				:value="row.tableName" />
																			<div class="modal-header">
																				<h5 class="modal-title">Mark This As
																					Reviewed ?</h5>
																				<button type="button" class="close"
																					data-dismiss="modal"
																					aria-label="Close">
																					<span
																						aria-hidden="true">&times;</span>
																				</button>
																			</div>
																			<div class="modal-footer">
																				<button type="button"
																					class="btn btn-secondary"
																					data-dismiss="modal">Close</button>
																				<button type="submit"
																					class="btn btn-success">Confirm</button>
																			</div>
																		</form>
																	</div>
																</div>
															</div>
														</template>
														<template v-if="!row.isOpenBalance">
															<Link :href="row.editUrl" type="button"
																class="btn btn-secondary btn-outline-hover-brand btn-icon"
																title="Edit Cheque"><i class="fa fa-pen-alt"></i></Link>
														</template>
														<template v-else>
															<a :data-toggle="'modal'"
																:data-target="'#edit-opening-cheques' + row.id"
																type="button"
																class="btn btn-secondary btn-outline-hover-brand btn-icon"
																title="Edit Cheque" href="#"><i
																	class="fa fa-pen-alt exclude-icon default-icon-color"></i></a>
															<div class="modal closest-parent-class editable-opening-balance-cheque fade"
																:id="'edit-opening-cheques' + row.id" tabindex="-1"
																role="dialog" aria-hidden="true">
																<div class="modal-dialog modal-xl modal-dialog-centered"
																	role="document">
																	<div class="modal-content">
																		<form
																			:action="row.updateOpeningPayableChequeUrl"
																			method="post">
																			<input type="hidden" name="_token"
																				:value="csrfToken" />
																			<input type="hidden" />
																			<div class="modal-header">
																				<h5 class="modal-title">Opening Payable
																					Cheque Edit</h5>
																				<button type="button" class="close"
																					aria-label="Close">
																					<span
																						aria-hidden="true">&times;</span>
																				</button>
																			</div>
																			<div class="modal-body">
																				<div class="row mb-3">
																					<div class="col-md-4">
																						<label>Supplier Name</label>
																						<div class="kt-input-icon">
																							<div
																								class="input-group date">
																								<select
																									name="supplier_id"
																									class="form-control mb-1 select select2-select customer_name_class repeater-select"
																									data-live-search="true"
																									data-add-new="0"
																									data-all="0"
																									data-filter-type="create">
																									<option
																										v-for="(optLabel, optVal) in suppliersFormatted"
																										:key="optVal"
																										:value="optVal"
																										:selected="String(optVal) === String(row.supplierId)">
																										{{ optLabel }}
																									</option>
																								</select>
																							</div>
																						</div>
																					</div>
																					<div class="col-md-1">
																						<label>Currency</label>
																						<div class="kt-input-icon">
																							<div
																								class="input-group date">
																								<select name="currency"
																									class="form-control select-for-currency-temp ajax-get-invoice-numbers"
																									js-when-change-trigger-change-account-type>
																									<option
																										v-for="(cLabel, cVal) in getCurrencies"
																										:key="cVal"
																										:value="cVal"
																										:selected="String((row.openingChequeCurrencySelected ?? row.currency) || 'EGP') === String(cVal)">
																										{{ cLabel }}
																									</option>
																								</select>
																							</div>
																						</div>
																					</div>
																					<div class="col-md-1">
																						<label>FX Rate</label>
																						<div
																							class="kt-input-icon width-15">
																							<div class="input-group">
																								<input
																									name="exchange_rate"
																									type="numeric"
																									class="form-control"
																									:value="row.exchangeRate" />
																							</div>
																						</div>
																					</div>
																					<div class="col-md-2">
																						<label>Due Date</label>
																						<div class="kt-input-icon">
																							<div
																								class="input-group date">
																								<input type="text"
																									name="due_date"
																									class="datepicker-input date-input form-control recalc-end-date start-date"
																									:value="row.openingDueDateFormatted" />
																							</div>
																						</div>
																					</div>
																					<div class="col-md-2">
																						<label>Amount</label>
																						<div
																							class="kt-input-icon width-15">
																							<div class="input-group">
																								<input
																									name="paid_amount"
																									type="text"
																									class="form-control"
																									:value="row.paidAmountFormattedRaw" />
																							</div>
																						</div>
																					</div>
																					<div class="col-md-2">
																						<label>Cheque No.</label>
																						<div
																							class="kt-input-icon width-15">
																							<div class="input-group">
																								<input
																									name="cheque_number"
																									type="text"
																									class="form-control"
																									:value="row.chequeNumber" />
																							</div>
																						</div>
																					</div>
																					<div class="col-md-6 mb-3 mt-3">
																						<label>Drawal Bank <span
																								class="text-danger required-label">*</span></label>
																						<div class="kt-input-icon">
																							<div
																								class="input-group date">
																								<select
																									js-when-change-trigger-change-account-type
																									data-financial-institution-id
																									required
																									name="drawl_bank_id"
																									class="form-control js-drawl-bank">
																									<option
																										v-for="(fib, fi) in financialInstitutionBanks"
																										:key="fi"
																										:value="fib.id"
																										:selected="fib.id === row.draweeBankId">
																										{{ fib.name }}
																									</option>
																								</select>
																							</div>
																						</div>
																					</div>
																					<div class="col-md-3 mt-3">
																						<label>Account Type <span
																								class="text-danger required-label">*</span></label>
																						<div class="kt-input-icon">
																							<div
																								class="input-group date">
																								<select
																									name="account_type"
																									class="form-control js-update-account-number-based-on-account-type">
																									<option value=""
																										selected>Select
																									</option>
																									<option
																										v-for="(at, ai) in accountTypes"
																										:key="ai"
																										:value="at.id"
																										:selected="at.id === row.payableChequeAccountTypeId">
																										{{ at.name }}
																									</option>
																								</select>
																							</div>
																						</div>
																					</div>
																					<div class="col-md-3 mt-3">
																						<label>Account Number <span
																								class="text-danger required-label">*</span></label>
																						<div class="kt-input-icon">
																							<div
																								class="input-group date">
																								<select
																									:data-current-selected="row.accountNumber"
																									name="account_number"
																									class="form-control js-account-number">
																									<option value=""
																										selected>Select
																									</option>
																								</select>
																							</div>
																						</div>
																					</div>
																				</div>
																			</div>
																			<div class="modal-footer">
																				<button type="button"
																					class="btn btn-secondary"
																					data-dismiss="modal">Close</button>
																				<button type="submit"
																					class="btn btn-success">Confirm</button>
																			</div>
																		</form>
																	</div>
																</div>
															</div>
														</template>
														<template v-if="row.isPayableChequeDue">
															<a :data-id="row.id" data-type="single"
																:data-currency="row.currency"
																:data-due-date="row.chequeActualPaymentDateFormatted"
																:data-money-type="MP.PAYABLE_CHEQUE" data-toggle="modal"
																:data-target="'#send-to-under-collection-modal' + MP.PAYABLE_CHEQUE"
																type="button"
																class="btn js-can-trigger-cheque-under-collection-modal btn-secondary btn-outline-hover-primary btn-icon"
																href=""><i class="fa fa-money-bill"></i></a>
														</template>
													</template>
													<template v-if="!row.isOpenBalance && canDeleteSupplierPayment">
														<a data-toggle="modal"
															:data-target="'#delete-cheque-id-' + row.id" type="button"
															class="btn btn-secondary btn-outline-hover-danger btn-icon"
															title="Delete" href="#"><i class="fa fa-trash-alt"></i></a>
														<div class="modal fade" :id="'delete-cheque-id-' + row.id"
															tabindex="-1" role="dialog"
															aria-labelledby="exampleModalCenterTitle"
															aria-hidden="true">
															<div class="modal-dialog modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form
																		onsubmit="this.querySelector('button[type=submit]').disabled = true;"
																		:action="row.deleteUrl" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<input type="hidden" name="_method"
																			value="delete" />
																		<div class="modal-header">
																			<h5 class="modal-title"
																				id="exampleModalLongTitle">Do You Want
																				To Delete This Item ?</h5>
																			<button type="button" class="close"
																				data-dismiss="modal" aria-label="Close">
																				<span aria-hidden="true">&times;</span>
																			</button>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-secondary"
																				data-dismiss="modal">Close</button>
																			<button type="submit"
																				class="btn btn-danger">Confirm
																				Delete</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
												</span>
											</td>
										</tr>
									</tbody>
								</table>
								<nav v-if="payableCheques.links && payableCheques.links.length" class="mp-pagination"
									aria-label="Pagination">
									<template v-for="(link, li) in payableCheques.links" :key="li">
										<a v-if="link.url" class="page-link" :href="link.url" v-html="link.label"></a>
										<span v-else class="page-link disabled" v-html="link.label"></span>
									</template>
								</nav>
							</div>
						</div>
					</div>
					<!-- Outgoing Transfer — structure mirrors Blade; toolbar + table shortened in file size: copy Payable pattern -->
					<div class="tab-pane" :class="{ active: tabActive(MP.OUTGOING_TRANSFER) }"
						:id="MP.OUTGOING_TRANSFER" role="tabpanel">
						<div class="kt-portlet kt-portlet--mobile mp-card">
							<div class="kt-portlet__head kt-portlet__head--lg p-0">
								<div class="kt-portlet__head-label ml-4" style="flex: 2.5">
									<span class="kt-portlet__head-icon">
										<i
											class="kt-font-secondary text-main-color btn-outline-hover-danger fa fa-layer-group"></i>
									</span>
									<h3 style="font-size: 20px !important"
										class="kt-portlet__head-title text-main-color text-nowrap"> Outgoing Transfer
									</h3>
									<form class="w-full flex-2" method="get" :action="routes.viewMoneyPayment">
										<input type="hidden" name="active" :value="MP.OUTGOING_TRANSFER" />
										<div class="row align-items-center">
											<div class="col-md-3 d-flex align-items-center"
												style="margin-left: 5rem !important">
												<label class="text-nowrap mr-3">Start Date</label>
												<input type="date" class="form-control"
													:name="'startDate[' + MP.OUTGOING_TRANSFER + ']'"
													:value="filterDates[MP.OUTGOING_TRANSFER]?.startDate || ''" />
											</div>
											<div class="col-md-3 d-flex align-items-center">
												<label class="text-nowrap mr-3">End Date</label>
												<input type="date" class="form-control"
													:name="'endDate[' + MP.OUTGOING_TRANSFER + ']'"
													:value="filterDates[MP.OUTGOING_TRANSFER]?.endDate || ''" />
											</div>
											<div class="col-md-2 d-flex justify-content-center"
												style="margin-left: 2rem !important">
												<button style="width: 70px !important; font-size: 1rem !important"
													type="submit"
													class="btn block form-control btn-primary btn-sm">Submit</button>
											</div>
										</div>
									</form>
								</div>
								<div class="kt-portlet__head-toolbar" style="flex: 1 !important">
									<div class="kt-portlet__head-wrapper">
										<div class="kt-portlet__head-actions"> &nbsp; <a
												:data-money-type="MP.OUTGOING_TRANSFER" data-type="multi"
												data-toggle="modal"
												:data-target="'#send-to-under-collection-modal' + MP.OUTGOING_TRANSFER"
												:id="'js-send-to-under-collection-trigger' + MP.OUTGOING_TRANSFER"
												:href="routes.createMoneyReceive"
												title="Please Select More Than One Cheque"
												class="btn active-style btn-icon-sm js-can-trigger-cheque-under-collection-modal disabled">
												<i class="fas fa-book"></i> Create Batch Mark As Paid </a>
											<a data-type="multi" data-toggle="modal"
												:data-target="'#search-money-modal-' + MP.OUTGOING_TRANSFER" href="#"
												class="btn active-style btn-icon-sm">
												<i class="fas fa-search"></i> Advanced Filter </a>
											<div class="modal fade" :id="'search-money-modal-' + MP.OUTGOING_TRANSFER"
												tabindex="-1" role="dialog" aria-hidden="true">
												<div class="modal-dialog modal-xl" role="document">
													<div class="modal-content">
														<div class="modal-header">
															<h5 class="modal-title">Filter Form</h5>
															<button type="button" class="close" data-dismiss="modal"
																aria-label="Close"><span
																	aria-hidden="true">&times;</span></button>
														</div>
														<div class="modal-body">
															<form :action="routes.viewMoneyPayment" class="row"
																method="get">
																<input name="active" type="hidden"
																	:value="MP.OUTGOING_TRANSFER" />
																<div class="form-group col-4">
																	<label class="label">Field Name</label>
																	<select :data-type="MP.OUTGOING_TRANSFER"
																		class="form-control js-search-modal"
																		name="field">
																		<option
																			v-for="(label, name) in outgoingTransferTableSearchFields"
																			:key="name" :value="name"
																			:selected="requestQuery.field === name">
																			{{ label }}</option>
																	</select>
																</div>
																<div class="form-group col-4">
																	<label class="label">Search Text</label>
																	<input name="value" type="text"
																		:value="requestQuery.value || ''"
																		class="form-control search-field" />
																</div>
																<div class="form-group col-2">
																	<label class="label">From <span
																			class="data-type-span">[ Receiving Date
																			]</span></label>
																	<input name="from" type="date"
																		:value="requestQuery.from || ''"
																		class="form-control" />
																</div>
																<div class="form-group col-2">
																	<label class="label">To <span
																			class="data-type-span">[ Receiving Date
																			]</span></label>
																	<input name="to" type="date"
																		:value="requestQuery.to || ''"
																		class="form-control" />
																</div>
																<div class="modal-footer">
																	<button type="submit"
																		class="btn btn-primary">Search</button>
																	<button id="reset-search-id" type="button"
																		class="btn btn-primary">Reset</button>
																</div>
															</form>
														</div>
													</div>
												</div>
											</div>
											<div class="modal fade"
												:id="'send-to-under-collection-modal' + MP.OUTGOING_TRANSFER"
												tabindex="-1" role="dialog" aria-hidden="true">
												<div class="modal-dialog modal-dialog-centered" role="document">
													<div class="modal-content">
														<form :data-money-type="MP.OUTGOING_TRANSFER"
															:id="'ajax-send-cheques-to-collection-id' + MP.OUTGOING_TRANSFER"
															class="ajax-send-cheques-to-collection"
															:action="routes.outgoingTransferMarkAsPaid" method="post">
															<input type="hidden"
																:id="'single-or-multi' + MP.OUTGOING_TRANSFER"
																value="single" />
															<input type="hidden"
																:id="'current-single-item' + MP.OUTGOING_TRANSFER"
																value="0" />
															<input type="hidden"
																:id="'current-currency' + MP.OUTGOING_TRANSFER"
																class="current-currency" value="" />
															<input type="hidden" name="_token" :value="csrfToken" />
															<div class="modal-header">
																<h5 class="modal-title">Do You Want To Mark This
																	Outcoming Transfer/s As Paid ?</h5>
																<button type="button" class="close"
																	aria-label="Close"><span
																		aria-hidden="true">&times;</span></button>
															</div>
															<div class="modal-body">
																<div class="row mb-3">
																	<div class="col-md-12">
																		<label>Actual Payment Date</label>
																		<div class="kt-input-icon">
																			<div class="input-group date">
																				<input required type="text"
																					name="actual_payment_date"
																					class="form-control" readonly
																					placeholder="Select date" />
																				<div class="input-group-append">
																					<span class="input-group-text"><i
																							class="la la-calendar-check-o"></i></span>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
															<div class="modal-footer">
																<button type="button" class="btn btn-secondary"
																	data-dismiss="modal">Close</button>
																<button type="submit"
																	class="btn btn-success">Confirm</button>
															</div>
														</form>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="kt-portlet__body">
								<table
									class="table  table-striped- table-bordered table-hover table-checkable text-center kt_table_1 mp-table">
									<thead>
										<tr class="table-standard-color">
											<th class="bank-max-width">Status</th>
											<th class="bank-max-width">Supplier Name</th>
											<th>Payment Date</th>
											<th class="bank-max-width">Payment Bank</th>
											<th>Transfer Amount</th>
											<th>Currency</th>
											<th class="bank-max-width">Account Type</th>
											<th>Account Number</th>
											<th>Control</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="row in outgoingTransfer.data" :key="row.id">
											<td class="bank-max-width">{{ row.moneyTypeFormatted }}</td>
											<td class="bank-max-width">{{ row.supplierName }}</td>
											<td class="text-nowrap">{{ row.deliveryDateFormatted }}</td>
											<td class="bank-max-width">{{ row.outgoingTransferDeliveryBankName }}</td>
											<td>{{ row.paidAmountFormatted }}</td>
											<td :data-currency="row.currency">
												{{ row.currencyToPaymentCurrencyFormatted }}</td>
											<td class="bank-max-width">{{ row.outgoingTransferAccountTypeName }}</td>
											<td>{{ row.outgoingTransferAccountNumber }}</td>
											<td class="kt-datatable__cell--left kt-datatable__cell" data-field="Actions"
												data-autohide-disabled="false">
												<span style="overflow: visible; position: relative; width: 110px">
													<template v-if="row.hasComment">
														<a :data-toggle="'modal'"
															:data-target="'#user-comment-ot-' + row.id" type="button"
															class="btn btn-secondary btn-outline-hover-success btn-icon"
															href="#"><i class="fa fa-comment"></i></a>
														<div class="modal fade" :id="'user-comment-ot-' + row.id"
															tabindex="-1" role="dialog" aria-hidden="true">
															<div class="modal-dialog modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form action="#" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<div class="modal-header">
																			<h5 class="modal-title">User Comment</h5>
																			<button type="button" class="close"
																				data-dismiss="modal"
																				aria-label="Close"><span
																					aria-hidden="true">&times;</span></button>
																		</div>
																		<div class="modal-body">
																			<h2 class="text-wrap"
																				:class="row.userCommentIsArabic ? 'text-right' : 'text-left'">
																				{{ row.userComment }}</h2>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-secondary"
																				data-dismiss="modal">Close</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
													<template v-if="row.showOdooError">
														<a :data-toggle="'modal'"
															:data-target="'#odoo-model-ot-' + row.id" type="button"
															class="btn btn-icon bg-red text-white" href="#"><i
																class="fa fa-bug"></i></a>
														<div class="modal fade" :id="'odoo-model-ot-' + row.id"
															tabindex="-1" role="dialog" aria-hidden="true">
															<div class="modal-dialog modal-xl modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form :action="row.resendOdooUrl" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<div class="modal-header">
																			<h5 class="modal-title">Odoo Error</h5>
																			<button type="button" class="close"
																				data-dismiss="modal"
																				aria-label="Close"><span
																					aria-hidden="true">&times;</span></button>
																		</div>
																		<div class="modal-body">
																			<h2 class="text-wrap"
																				:class="row.odooErrorIsArabic ? 'text-right' : 'text-left'">
																				{{ row.odooError }}</h2>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-secondary"
																				data-dismiss="modal">Close</button>
																			<button type="submit"
																				class="btn btn-success">Resend</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
													<template v-if="row.showIntegratedModal">
														<a :data-toggle="'modal'"
															:data-target="'#fully-integrated-ot-' + row.id"
															type="button" class="btn btn-primary btn-icon" href="#"><i
																class="fa fa-thumbs-up"></i></a>
														<div class="modal fade" :id="'fully-integrated-ot-' + row.id"
															tabindex="-1" role="dialog" aria-hidden="true">
															<div class="modal-dialog modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form action="#" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<div class="modal-header blue">
																			<h5 class="modal-title text-blue">Odoo
																				References</h5>
																			<button type="button" class="close"
																				data-dismiss="modal"
																				aria-label="Close"><span
																					aria-hidden="true">&times;</span></button>
																		</div>
																		<div class="modal-body">
																			<ul class="list-unstyled">
																				<li v-for="(ref, ri) in row.odooReferenceNames"
																					:key="ri" class="mb-3 text-left">
																					{{ ref }}</li>
																			</ul>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-primary"
																				data-dismiss="modal">Close</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
													<template v-if="!row.isOpenBalance && canUpdateSupplierPayment">
														<template v-if="row.showReviewModal">
															<a :data-toggle="'modal'"
																:data-target="'#review-ot-' + row.id" type="button"
																class="btn btn-secondary btn-outline-hover-success btn-icon"
																href="#"><i class="fa fa-check"></i></a>
															<div class="modal fade" :id="'review-ot-' + row.id"
																tabindex="-1" role="dialog" aria-hidden="true">
																<div class="modal-dialog modal-dialog-centered"
																	role="document">
																	<div class="modal-content">
																		<form :action="row.confirmedReviewUrl"
																			method="post">
																			<input type="hidden" name="_token"
																				:value="csrfToken" />
																			<input type="hidden" name="model_name"
																				:value="row.modelNameForReview" />
																			<input type="hidden" name="table_name"
																				:value="row.tableName" />
																			<div class="modal-header">
																				<h5 class="modal-title">Mark This As
																					Reviewed ?</h5>
																				<button type="button" class="close"
																					data-dismiss="modal"
																					aria-label="Close"><span
																						aria-hidden="true">&times;</span></button>
																			</div>
																			<div class="modal-footer">
																				<button type="button"
																					class="btn btn-secondary"
																					data-dismiss="modal">Close</button>
																				<button type="submit"
																					class="btn btn-success">Confirm</button>
																			</div>
																		</form>
																	</div>
																</div>
															</div>
														</template>
														<Link :href="row.editUrl" type="button"
															class="btn btn-secondary btn-outline-hover-brand btn-icon"
															title="Edit"><i class="fa fa-pen-alt"></i></Link>
													</template>
													<template v-if="!row.isOpenBalance && canDeleteSupplierPayment">
														<a data-toggle="modal"
															:data-target="'#delete-transfer-id-' + row.id" type="button"
															class="btn btn-secondary btn-outline-hover-danger btn-icon"
															title="Delete" href="#"><i class="fa fa-trash-alt"></i></a>
														<div class="modal fade" :id="'delete-transfer-id-' + row.id"
															tabindex="-1" role="dialog"
															aria-labelledby="exampleModalCenterTitle"
															aria-hidden="true">
															<div class="modal-dialog modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form
																		onsubmit="this.querySelector('button[type=submit]').disabled = true;"
																		:action="row.deleteUrl" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<input type="hidden" name="_method"
																			value="delete" />
																		<div class="modal-header">
																			<h5 class="modal-title"
																				id="exampleModalLongTitle">Do You Want
																				To Delete This Item ?</h5>
																			<button type="button" class="close"
																				data-dismiss="modal"
																				aria-label="Close"><span
																					aria-hidden="true">&times;</span></button>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-secondary"
																				data-dismiss="modal">Close</button>
																			<button type="submit"
																				class="btn btn-danger">Confirm
																				Delete</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
												</span>
											</td>
										</tr>
									</tbody>
								</table>
								<nav v-if="outgoingTransfer.links && outgoingTransfer.links.length"
									class="mp-pagination" aria-label="Pagination">
									<template v-for="(link, li) in outgoingTransfer.links" :key="'ot-' + li">
										<a v-if="link.url" class="page-link" :href="link.url" v-html="link.label"></a>
										<span v-else class="page-link disabled" v-html="link.label"></span>
									</template>
								</nav>
							</div>
						</div>
					</div>
					<!-- Cash Payment -->
					<div class="tab-pane" :class="{ active: tabActive(MP.CASH_PAYMENT) }" :id="MP.CASH_PAYMENT"
						role="tabpanel">
						<div class="kt-portlet kt-portlet--mobile mp-card">
							<div class="kt-portlet__head kt-portlet__head--lg p-0">
								<div class="kt-portlet__head-label ml-4" style="flex: 2.5">
									<span class="kt-portlet__head-icon">
										<i
											class="kt-font-secondary text-main-color btn-outline-hover-danger fa fa-layer-group"></i>
									</span>
									<h3 style="font-size: 20px !important"
										class="kt-portlet__head-title text-main-color text-nowrap">Cash Payment</h3>
									<form class="w-full flex-2" method="get" :action="routes.viewMoneyPayment">
										<input type="hidden" name="active" :value="MP.CASH_PAYMENT" />
										<div class="row align-items-center">
											<div class="col-md-3 d-flex align-items-center"
												style="margin-left: 5rem !important">
												<label class="text-nowrap mr-3">Start Date</label>
												<input type="date" class="form-control"
													:name="'startDate[' + MP.CASH_PAYMENT + ']'"
													:value="filterDates[MP.CASH_PAYMENT]?.startDate || ''" />
											</div>
											<div class="col-md-3 d-flex align-items-center">
												<label class="text-nowrap mr-3">End Date</label>
												<input type="date" class="form-control"
													:name="'endDate[' + MP.CASH_PAYMENT + ']'"
													:value="filterDates[MP.CASH_PAYMENT]?.endDate || ''" />
											</div>
											<div class="col-md-2 d-flex justify-content-center"
												style="margin-left: 2rem !important">
												<button style="width: 70px !important; font-size: 1rem !important"
													type="submit"
													class="btn block form-control btn-primary btn-sm">Submit</button>
											</div>
										</div>
									</form>
								</div>
								<div class="kt-portlet__head-toolbar" style="flex: 1 !important">
									<div class="kt-portlet__head-wrapper">
										<div class="kt-portlet__head-actions"> &nbsp; <a data-type="multi"
												data-toggle="modal"
												:data-target="'#search-money-modal-' + MP.CASH_PAYMENT" href="#"
												class="btn active-style btn-icon-sm">
												<i class="fas fa-search"></i> Advanced Filter </a>
											<div class="modal fade" :id="'search-money-modal-' + MP.CASH_PAYMENT"
												tabindex="-1" role="dialog" aria-hidden="true">
												<div class="modal-dialog modal-xl" role="document">
													<div class="modal-content">
														<div class="modal-header">
															<h5 class="modal-title">Filter Form</h5>
															<button type="button" class="close" data-dismiss="modal"
																aria-label="Close"><span
																	aria-hidden="true">&times;</span></button>
														</div>
														<div class="modal-body">
															<form :action="routes.viewMoneyPayment" class="row"
																method="get">
																<input name="active" type="hidden"
																	:value="MP.CASH_PAYMENT" />
																<div class="form-group col-4">
																	<label class="label">Field Name</label>
																	<select :data-type="MP.CASH_PAYMENT"
																		class="form-control js-search-modal"
																		name="field">
																		<option
																			v-for="(label, name) in payableCashTableSearchFields"
																			:key="name" :value="name"
																			:selected="requestQuery.field === name">
																			{{ label }}</option>
																	</select>
																</div>
																<div class="form-group col-4">
																	<label class="label">Search Text</label>
																	<input name="value" type="text"
																		:value="requestQuery.value || ''"
																		class="form-control search-field" />
																</div>
																<div class="form-group col-2">
																	<label class="label">From <span
																			class="data-type-span">[ Receiving Date
																			]</span></label>
																	<input name="from" type="date"
																		:value="requestQuery.from || ''"
																		class="form-control" />
																</div>
																<div class="form-group col-2">
																	<label class="label">To <span
																			class="data-type-span">[ Receiving Date
																			]</span></label>
																	<input name="to" type="date"
																		:value="requestQuery.to || ''"
																		class="form-control" />
																</div>
																<div class="modal-footer">
																	<button type="submit"
																		class="btn btn-primary">Search</button>
																	<button type="button"
																		class="btn btn-primary">Reset</button>
																</div>
															</form>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="kt-portlet__body">
								<table
									class="table  table-striped- table-bordered table-hover table-checkable text-center kt_table_1 mp-table">
									<thead>
										<tr class="table-standard-color">
											<th>Type</th>
											<th class="bank-max-width">Partner Name</th>
											<th>Payment Date</th>
											<th>Branch</th>
											<th>Payment Amount</th>
											<th>Currency</th>
											<th>Receipt Number</th>
											<th>Control</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="row in cashPayments.data" :key="row.id">
											<td class="bank-max-width">{{ row.moneyTypeFormatted }}</td>
											<td class="bank-max-width">{{ row.supplierName }}</td>
											<td class="text-nowrap">{{ row.deliveryDateFormatted }}</td>
											<td>{{ row.cashPaymentBranchName }}</td>
											<td>{{ row.paidAmountFormatted }}</td>
											<td :data-currency="row.currency">
												{{ row.currencyToPaymentCurrencyFormatted }}</td>
											<td>{{ row.cashPaymentReceiptNumber }}</td>
											<td class="kt-datatable__cell--left kt-datatable__cell" data-field="Actions"
												data-autohide-disabled="false">
												<span style="overflow: visible; position: relative; width: 110px">
													<template v-if="row.hasComment">
														<a :data-toggle="'modal'"
															:data-target="'#user-comment-cp-' + row.id" type="button"
															class="btn btn-secondary btn-outline-hover-success btn-icon"
															href="#"><i class="fa fa-comment"></i></a>
														<div class="modal fade" :id="'user-comment-cp-' + row.id"
															tabindex="-1" role="dialog" aria-hidden="true">
															<div class="modal-dialog modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form action="#" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<div class="modal-header">
																			<h5 class="modal-title">User Comment</h5>
																			<button type="button" class="close"
																				data-dismiss="modal"
																				aria-label="Close"><span
																					aria-hidden="true">&times;</span></button>
																		</div>
																		<div class="modal-body">
																			<h2 class="text-wrap"
																				:class="row.userCommentIsArabic ? 'text-right' : 'text-left'">
																				{{ row.userComment }}</h2>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-secondary"
																				data-dismiss="modal">Close</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
													<template v-if="row.showOdooError">
														<a :data-toggle="'modal'"
															:data-target="'#odoo-model-cp-' + row.id" type="button"
															class="btn btn-icon bg-red text-white" href="#"><i
																class="fa fa-bug"></i></a>
														<div class="modal fade" :id="'odoo-model-cp-' + row.id"
															tabindex="-1" role="dialog" aria-hidden="true">
															<div class="modal-dialog modal-xl modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form :action="row.resendOdooUrl" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<div class="modal-header">
																			<h5 class="modal-title">Odoo Error</h5>
																			<button type="button" class="close"
																				data-dismiss="modal"
																				aria-label="Close"><span
																					aria-hidden="true">&times;</span></button>
																		</div>
																		<div class="modal-body">
																			<h2 class="text-wrap"
																				:class="row.odooErrorIsArabic ? 'text-right' : 'text-left'">
																				{{ row.odooError }}</h2>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-secondary"
																				data-dismiss="modal">Close</button>
																			<button type="submit"
																				class="btn btn-success">Resend</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
													<template v-if="row.showIntegratedModal">
														<a :data-toggle="'modal'"
															:data-target="'#fully-integrated-cp-' + row.id"
															type="button" class="btn btn-primary btn-icon" href="#"><i
																class="fa fa-thumbs-up"></i></a>
														<div class="modal fade" :id="'fully-integrated-cp-' + row.id"
															tabindex="-1" role="dialog" aria-hidden="true">
															<div class="modal-dialog modal-dialog-centered"
																role="document">
																<div class="modal-content">
																	<form action="#" method="post">
																		<input type="hidden" name="_token"
																			:value="csrfToken" />
																		<div class="modal-header blue">
																			<h5 class="modal-title text-blue">Odoo
																				References</h5>
																			<button type="button" class="close"
																				data-dismiss="modal"
																				aria-label="Close"><span
																					aria-hidden="true">&times;</span></button>
																		</div>
																		<div class="modal-body">
																			<ul class="list-unstyled">
																				<li v-for="(ref, ri) in row.odooReferenceNames"
																					:key="ri" class="mb-3 text-left">
																					{{ ref }}</li>
																			</ul>
																		</div>
																		<div class="modal-footer">
																			<button type="button"
																				class="btn btn-primary"
																				data-dismiss="modal">Close</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
													</template>
													<template v-if="!row.isOpenBalance && canUpdateSupplierPayment">
														<template v-if="row.showReviewModal">
															<a :data-toggle="'modal'"
																:data-target="'#review-cp-' + row.id" type="button"
																class="btn btn-secondary btn-outline-hover-success btn-icon"
																href="#"><i class="fa fa-check"></i></a>
															<div class="modal fade" :id="'review-cp-' + row.id"
																tabindex="-1" role="dialog" aria-hidden="true">
																<div class="modal-dialog modal-dialog-centered"
																	role="document">
																	<div class="modal-content">
																		<form :action="row.confirmedReviewUrl"
																			method="post">
																			<input type="hidden" name="_token"
																				:value="csrfToken" />
																			<input type="hidden" name="model_name"
																				:value="row.modelNameForReview" />
																			<input type="hidden" name="table_name"
																				:value="row.tableName" />
																			<div class="modal-header">
																				<h5 class="modal-title">Mark This As
																					Reviewed ?</h5>
																				<button type="button" class="close"
																					data-dismiss="modal"
																					aria-label="Close"><span
																						aria-hidden="true">&times;</span></button>
																			</div>
																			<div class="modal-footer">
																				<button type="button"
																					class="btn btn-secondary"
																					data-dismiss="modal">Close</button>
																				<button type="submit"
																					class="btn btn-success">Confirm</button>
																			</div>
																		</form>
																	</div>
																</div>
															</div>
														</template>
														<Link :href="row.editUrl" type="button"
															class="btn btn-secondary btn-outline-hover-brand btn-icon"
															title="Edit"><i class="fa fa-pen-alt"></i></Link>
													</template>
													<template v-if="!row.isOpenBalance">
														<template v-if="canDeleteSupplierPayment">
															<a data-toggle="modal"
																:data-target="'#delete-transfer-id-' + row.id"
																type="button"
																class="btn btn-secondary btn-outline-hover-danger btn-icon"
																title="Delete" href="#"><i
																	class="fa fa-trash-alt"></i></a>
															<div class="modal fade" :id="'delete-transfer-id-' + row.id"
																tabindex="-1" role="dialog"
																aria-labelledby="exampleModalCenterTitle"
																aria-hidden="true">
																<div class="modal-dialog modal-dialog-centered"
																	role="document">
																	<div class="modal-content">
																		<form
																			onsubmit="this.querySelector('button[type=submit]').disabled = true;"
																			:action="row.deleteUrl" method="post">
																			<input type="hidden" name="_token"
																				:value="csrfToken" />
																			<input type="hidden" name="_method"
																				value="delete" />
																			<div class="modal-header">
																				<h5 class="modal-title"
																					id="exampleModalLongTitle">Do You
																					Want To Delete This Item ?</h5>
																				<button type="button" class="close"
																					data-dismiss="modal"
																					aria-label="Close"><span
																						aria-hidden="true">&times;</span></button>
																			</div>
																			<div class="modal-footer">
																				<button type="button"
																					class="btn btn-secondary"
																					data-dismiss="modal">Close</button>
																				<button type="submit"
																					class="btn btn-danger">Confirm
																					Delete</button>
																			</div>
																		</form>
																	</div>
																</div>
															</div>
														</template>
													</template>
												</span>
											</td>
										</tr>
									</tbody>
								</table>
								<nav v-if="cashPayments.links && cashPayments.links.length" class="mp-pagination"
									aria-label="Pagination">
									<template v-for="(link, li) in cashPayments.links" :key="'cp-' + li">
										<a v-if="link.url" class="page-link" :href="link.url" v-html="link.label"></a>
										<span v-else class="page-link disabled" v-html="link.label"></span>
									</template>
								</nav>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<style scoped>
:root {
	--bg-page: #0c1829;
	--bg-card: #112240;
	--bg-card-hover: #162d54;
	--bg-input: #0c1829;
	--border: #1490a833;
	--border-solid: #1490a8;
	--border-focus: #1490a8;
	--teal: #1490a8;
	--teal-dark: #0f7a90;
	--teal-subtle: #112240cc;
	--gold: #c9a84c;
	--gold-dark: #a6852a;
	--gold-subtle: rgba(201, 168, 76, 0.1);
	--text-primary: #e2e8f0;
	--text-secondary: white;
	--text-muted: #64748b;
	--danger: #ef4444;
	--success: #10b981;
	--warning: #f59e0b;
}

.mp-page {
	background: var(--bg-page);
	color: var(--text-primary);
	border-radius: 10px;
	border: 1px solid var(--border);
	box-shadow: 0 4px 24px rgba(0, 0, 0, 0.5);
	border-top: 3px solid var(--teal);
}

.mp-header {
	background: var(--bg-card) !important;
	border-bottom: 3px solid var(--teal) !important;
}

.mp-header .nav-link {
	color: var(--text-secondary) !important;
	font-weight: 600;
}

.mp-header .nav-link.active {
	color: var(--teal) !important;
	border-bottom-color: var(--gold) !important;
}

.mp-card {
	background: var(--bg-card) !important;
	border: 1px solid var(--border) !important;
	border-radius: 10px !important;
	box-shadow: 0 4px 24px rgba(0, 0, 0, 0.5) !important;
	border-top: 3px solid var(--teal) !important;
	overflow: visible !important;
}

.mp-card .kt-portlet__head {
	background: var(--bg-card-hover) !important;
	border-bottom: 1px solid var(--border) !important;
}

.mp-card .kt-portlet__head-title,
.mp-card h3.kt-portlet__head-title {
	color: var(--teal) !important;
	border-left: 4px solid var(--gold);
	padding-left: 0.5rem;
}

.mp-table {
	background: var(--bg-card) !important;
}

.mp-table thead tr {
	background: #0c1829 !important;
}

.mp-table thead th {
	color: var(--teal) !important;
	text-transform: uppercase;
	font-size: 0.75rem;
	padding: 12px 16px !important;
	border-color: var(--border) !important;
}

.mp-table tbody td {
	color: var(--text-primary) !important;
	border: 1px solid var(--border) !important;
	padding: 12px 16px !important;
	vertical-align: middle !important;
}

.mp-table tbody tr:hover {
	background: var(--teal-subtle) !important;
	box-shadow: inset 3px 0 0 var(--teal);
}

.mp-page :deep(.form-control),
.mp-page :deep(select.form-control),
.mp-page :deep(input.form-control) {
	background: var(--bg-input) !important;
	border: 1px solid var(--border) !important;
	border-radius: 6px !important;
	color: var(--text-primary) !important;
}

.mp-page :deep(.form-control:focus) {
	border-color: var(--border-focus) !important;
	box-shadow: 0 0 0 3px rgba(20, 144, 168, 0.2) !important;
}

.mp-page :deep(label),
.mp-page :deep(.label) {
	color: var(--text-secondary) !important;
	font-weight: 600 !important;
	font-size: 0.85rem !important;
	text-transform: uppercase;
}

.mp-page :deep(.required-label),
.mp-page :deep(.text-danger.required-label) {
	color: var(--gold) !important;
}

.mp-page :deep(.btn-primary),
.mp-page :deep(.btn-success) {
	background: var(--teal) !important;
	color: #0c1829 !important;
	border-radius: 6px !important;
	font-weight: 600 !important;
	border: none !important;
	transition: all 0.2s ease !important;
}

.mp-page :deep(.btn-primary:hover),
.mp-page :deep(.btn-success:hover) {
	background: var(--teal-dark) !important;
	box-shadow: inset 3px 0 0 var(--gold);
}

.mp-page :deep(.btn-secondary) {
	background: transparent !important;
	color: var(--teal) !important;
	border: 1px solid var(--teal) !important;
	border-radius: 6px !important;
	font-weight: 600 !important;
	transition: all 0.2s ease !important;
}

.mp-page :deep(.btn-secondary:hover) {
	background: var(--teal-subtle) !important;
}

.mp-page :deep(.btn-danger) {
	background: var(--danger) !important;
	color: #fff !important;
	border-radius: 6px !important;
	font-weight: 600 !important;
}

.mp-page :deep(.active-style) {
	background: var(--gold) !important;
	color: #0c1829 !important;
	border-radius: 6px !important;
	font-weight: 600 !important;
	border: none !important;
}

.mp-page :deep(.active-style:hover) {
	background: var(--gold-dark) !important;
}

.mp-page :deep(.color-green),
.mp-page :deep(td.color-green) {
	background: rgba(16, 185, 129, 0.15) !important;
	color: var(--success) !important;
}

.mp-pagination :deep(.page-link) {
	background: var(--bg-card) !important;
	border: 1px solid var(--border) !important;
	color: var(--teal) !important;
	margin: 0 2px;
	border-radius: 6px !important;
}

.mp-pagination :deep(.page-link.disabled) {
	color: var(--text-muted) !important;
}

th:not(.bank-max-width),
td:not(.bank-max-width) {
	text-wrap: nowrap !important;
}

.bank-max-width {
	max-width: 200px !important;
}

input[type='checkbox'] {
	cursor: pointer;
}

.datepicker {
	background: var(--bg-card) !important;
	color: var(--text-primary) !important;
	border: 1px solid var(--border) !important;
}

.datepicker td.active {
	background: var(--teal) !important;
}

.datepicker td.today {
	border: 1px solid var(--gold) !important;
}

.datepicker th {
	color: var(--text-secondary) !important;
}

.select2-container--default .select2-selection--single {
	background: var(--bg-input) !important;
	border: 1px solid var(--border) !important;
	color: var(--text-primary) !important;
}

.select2-dropdown {
	background: var(--bg-card) !important;
	border: 1px solid var(--border) !important;
}

.select2-container--default .select2-results__option--highlighted {
	background: var(--teal) !important;
}

.select2-container--default .select2-selection__rendered {
	color: var(--text-primary) !important;
}

.modal-content {
	background: var(--bg-card) !important;
	border: 1px solid var(--border) !important;
}

.modal-header {
	background: var(--bg-card-hover) !important;
	border-bottom: 1px solid var(--border) !important;
}

.modal-footer {
	border-top: 1px solid var(--border) !important;
}

.modal-header.blue {
	border-bottom-color: var(--border) !important;
}
</style>
