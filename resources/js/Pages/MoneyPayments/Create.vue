<script setup>
/**
 * Inertia page migrated from resources/views/reports/moneyPayments/form.blade.php
 * Supply the same data the Blade view received; URLs belong in `routes` (from route()).
 */
import { Head } from '@inertiajs/vue3'
import { computed, onMounted, nextTick, ref, watch } from 'vue'

const props = defineProps({
	company: { type: Object, required: true },
	csrfToken: { type: String, required: true },
	routes: { type: Object, required: true },
	/** Store / update form action URL */
	formAction: { type: String, required: true },
	model: { type: Object, default: null },
	singleModel: { type: [Number, String], default: 0 },
	invoiceNumber: { type: String, default: '' },
	/** Serialized list: { id, name, selected } */
	invoiceCurrencies: { type: Array, default: () => [] },
	/** getCurrencies() — { id, name, selected } */
	paymentCurrencies: { type: Array, default: () => [] },
	partnerTypes: { type: Array, default: () => [] },
	suppliers: { type: Array, default: () => [] },
	selectedBranches: { type: Array, default: () => [] },
	financialInstitutionBanks: { type: Array, default: () => [] },
	accountTypes: { type: Array, default: () => [] },
	clientsWithContracts: { type: Array, default: () => [] },
	moneyPayment: { type: Object, required: true },
	inEditMode: { type: [String, Number], default: 0 },
	showSettlementBlock: { type: Boolean, default: true },
	selectedCurrencyExtraClass: { type: String, default: '' },
	warningMessage: { type: String, default: '' },
})

const MP = props.moneyPayment

const isEdit = computed(() => props.model !== null && props.model !== undefined)

const userComment = ref(props.model?.userComment ?? '')
watch(
	() => props.model?.userComment,
	(v) => {
		if (v !== undefined) userComment.value = v ?? ''
	},
)

function loadScript(src) {
	return new Promise((resolve, reject) => {
		if (document.querySelector(`script[src="${src}"]`)) {
			resolve()
			return
		}
		const s = document.createElement('script')
		s.src = src
		s.onload = resolve
		s.onerror = reject
		document.body.appendChild(s)
	})
}

function bindFormScripts() {
	const $ = window.jQuery || window.$
	if (!$) return

	$('#type')
		.off('change.mpCreate')
		.on('change.mpCreate', function () {
			const parent = $(this).closest('.js-section-parent')
			parent.find('select#delivery_branch_id').val()
			const type = $(this).val()
			$('.js-section-parent').addClass('hidden')
			if (type) {
				$('#' + type).removeClass('hidden')
			}
		})
	$('#type').trigger('change')

	function getBranchFromCurrency() {
		const branchQuery = $('select#branch-id')
		const currentFromBranchId = branchQuery.attr('data-current-selected')
		const currencyName = $('select#receiving-currency-id').val()
		$.ajax({
			url: props.routes.getBranchBasedOnCurrency,
			data: { currencyName },
			success: function (res) {
				var branchOptions = ''
				for (var branchName in res.branches) {
					var branchId = res.branches[branchName]
					var selected = branchId == currentFromBranchId ? 'selected' : ''
					branchOptions += `<option value="${branchId}" ${selected} >${branchName}</option>`
				}
				branchQuery.empty().append(branchOptions)
				branchQuery.trigger('change')
			},
		})
	}
	getBranchFromCurrency()
	$(document).off('change.mpCreateRecv', 'select#receiving-currency-id').on('change.mpCreateRecv', 'select#receiving-currency-id', getBranchFromCurrency)

	$(document)
		.off('change.mpCreateBranch', 'select#branch-id')
		.on('change.mpCreateBranch', 'select#branch-id', function () {
			const branchId = $('select#branch-id').val()
			const currencyName = $('select#receiving-currency-id').val()
			const modelId = $('#js-money-payment-id').val()
			const modelType = 'MoneyPayment'
			const balanceDate = $('.balance-date').val()
			if (branchId != '-1') {
				$.ajax({
					url: props.routes.getCurrentEndBalanceOfCashInSafeStatement,
					data: { branchId, currencyName, modelId, modelType, balanceDate },
					success: function (res) {
						const endBalance = res.end_balance
						$('.cash-balance-js').val(window.number_format(endBalance))
					},
				})
			}
		})

	$(function () {
		$('#type').trigger('change')
	})

	$(document)
		.off('change.mpCreateCurr', 'select.currency-class')
		.on('change.mpCreateCurr', 'select.currency-class', function () {
			const invoiceCurrency = $('select#invoice-currency-id').val()
			const receivingCurrency = $('select#receiving-currency-id').val()
			const moneyType = $('select#type').val()
			$('.main-amount-class').closest('.closest-parent').find('.currency-span').html(' [ ' + receivingCurrency + ' ]')
			$('.amount-after-exchange-rate-class').closest('.closest-parent').find('.currency-span').html(' [ ' + invoiceCurrency + ' ]')
			const partnerType = $('select#partner_type').val()
			if (partnerType && partnerType != 'is_supplier') {
				$('.show-only-when-invoice-currency-not-equal-receiving-currency').addClass('hidden')
				return
			}
			if (invoiceCurrency != receivingCurrency && invoiceCurrency && receivingCurrency) {
				$('.show-only-when-invoice-currency-not-equal-receiving-currency').removeClass('hidden')
			} else {
				$('.show-only-when-invoice-currency-not-equal-receiving-currency').addClass('hidden')
			}
			if (receivingCurrency != invoiceCurrency) {
				$('#remaining-settlement-taking-js').closest('.closest-parent').removeClass('visibility-hidden')
				$('#remaining-settlement-taking-js').closest('.closest-parent').find('.taking-currency-span').html('[ ' + receivingCurrency + ' ]')
			} else {
				$('#remaining-settlement-taking-js').closest('.closest-parent').addClass('visibility-hidden')
			}
		})

	$(document)
		.off('change.mpCreateRecalc', '.recalculate-amount-class')
		.on('change.mpCreateRecalc', '.recalculate-amount-class', function () {
			const moneyType = $(this).attr('data-type')
			const amount = window.number_unformat($('.main-amount-class[data-type="' + moneyType + '"]').val())
			const exchangeRate = window.number_unformat($('.exchange-rate-class[data-type="' + moneyType + '"]').val())
			const amountAfterExchangeRate = window.roundToTwo(amount / exchangeRate, 2)
			$('.amount-after-exchange-rate-class[data-type="' + moneyType + '"]')
				.val(amountAfterExchangeRate)
				.trigger('change')
			$('.js-settlement-amount:eq(0)').trigger('change')
		})

	$(document)
		.off('change.mpCreateAcc', 'select[when-change-trigger-account-type-change]')
		.on('change.mpCreateAcc', 'select[when-change-trigger-account-type-change]', function () {
			$('select.js-update-account-number-based-on-account-type').trigger('change')
		})

	$(document)
		.off('change.mpCreateBal', '.balance-date')
		.on('change.mpCreateBal', '.balance-date', function () {
			$('select.js-account-number').trigger('change')
			$('select#branch-id,select#receiving-currency-id').trigger('change')
		})

	$(document)
		.off('change.mpCreateAccNum', '.js-account-number')
		.on('change.mpCreateAccNum', '.js-account-number', function () {
			const parent = $(this).closest('.js-section-parent')
			const financialInstitutionId = parent.find('select.financial-institution-id').val()
			const accountNumber = $(this).val()
			const accountType = parent.find('select.js-update-account-number-based-on-account-type').val()
			const modelId = $('#js-money-payment-id').val()
			const modelType = 'MoneyPayment'
			const balanceDate = $('.balance-date').val()
			$.ajax({
				url: props.routes.updateBalanceAndNetBalanceBasedOnAccountNumber,
				data: { accountNumber, accountType, financialInstitutionId, modelType, modelId, balanceDate },
				type: 'get',
				success: function (res) {
					if (res.balance_date) {
						$(parent).find('.balance-date-js').html('[ ' + res.balance_date + ' ]')
					}
					if (res.net_balance_date) {
						$(parent).find('.net-balance-date-js').html('[ ' + res.net_balance_date + ' ]')
					}
					$(parent).find('.net-balance-js').val(window.number_format(res.net_balance))
					$(parent).find('.balance-js').val(window.number_format(res.balance))
				},
			})
		})

	$(function () {
		$('select.currency-class').trigger('change')
		$('.recalculate-amount-class').trigger('change')
	})

	$(document)
		.off('change.mpCreateSuppCust', 'select.suppliers-or-customers-js')
		.on('change.mpCreateSuppCust', 'select.suppliers-or-customers-js', function () {
			const parent = $(this).closest('tr')
			const partnerId = parseInt($(this).val())
			const model = $('#model_type').val()
			let inEditMode = String(props.inEditMode ?? 0)
			$.ajax({
				url: props.routes.getContractsForCustomerOrSupplier,
				data: { partnerId, model, inEditMode },
				type: 'get',
				success: function (res) {
					let contracts = ''
					const currentSelected = $(parent).find('select.contracts-js').data('current-selected')
					for (var contract of res.contracts) {
						contracts += `<option ${currentSelected == contract.id ? 'selected' : ''} value="${contract.id}" data-code="${contract.code}" data-amount="${contract.amount}" data-currency="${contract.currency}" >${contract.name}</option>`
					}
					parent.find('select.contracts-js').empty().append(contracts).trigger('change')
					parent.find('select.contracts-js').selectpicker('refresh')
				},
			})
		})

	$(document)
		.off('change.mpCreateContractsJs', 'select.contracts-js')
		.on('change.mpCreateContractsJs', 'select.contracts-js', function () {
			const parent = $(this).closest('tr')
			const code = $(this).find('option:selected').data('code')
			const amount = $(this).find('option:selected').data('amount')
			const currency = $(this).find('option:selected').data('currency').toUpperCase()
			$(parent).find('.contract-code').val(code)
			$(parent).find('.contract-amount').val(window.number_format(amount) + ' ' + currency)
		})

	$(document)
		.off('change.mpCreateAjaxContracts', '.ajax-update-contracts')
		.on('change.mpCreateAjaxContracts', '.ajax-update-contracts', function (e) {
			e.preventDefault()
			const supplierId = $('select.supplier-select').val()
			const currency = $('select.contract-currency').val()
			if (supplierId && currency) {
				$.ajax({
					url: props.routes.getContractsForSupplier,
					data: { supplierId, currency },
					success: function (res) {
						let options = '<option value="general-down">General Down Payment</option>'
						let selectedContractId = $('#contract-id').attr('data-current-selected')
						for (var id in res.contracts) {
							options += `<option ${selectedContractId == id ? 'selected' : ''} value="${id}">${res.contracts[id]}</option>`
						}
						$('select#contract-id').empty().append(options)
						$('select#contract-id').trigger('change')
					},
				})
			}
		})

	if (!props.singleModel && !isEdit.value) {
		$(function () {
			setTimeout(function () {
				$('select.ajax-get-invoice-numbers:eq(0)').trigger('change')
			}, 1500)
		})
		$('select#partner_type').trigger('change')
	}
}

onMounted(async () => {
	const scripts = [
		'/assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',
		'/assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js',
		'/assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js',
		'/assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js',
		'/assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js',
		'/assets/vendors/general/jquery.repeater/src/lib.js',
		'/assets/vendors/general/jquery.repeater/src/jquery.input.js',
		'/assets/vendors/general/jquery.repeater/src/repeater.js',
		'/assets/js/demo1/pages/crud/forms/widgets/form-repeater.js',
		'/custom/money-payment.js',
	]
	for (const s of scripts) {
		try {
			await loadScript(s)
		} catch (_) {
			/* may exist on layout */
		}
	}
	nextTick(() => {
		bindFormScripts()
	})
})
</script>
<template>
	<div class="mp-create-root" :data-current-company-id="company.id" :data-lang="$page?.props?.locale || 'en'">

		<Head title="Payment Form" />
		<div class="row">
			<div class="col-md-12">
				<form method="post" :action="formAction" class="kt-form kt-form--label-right">
					<input id="js-in-edit-mode" type="hidden" name="in_edit_mode" :value="isEdit ? 1 : 0" />
					<input id="js-money-payment-id" type="hidden" name="money_payment_id" :value="model?.id ?? 0" />
					<input type="hidden" name="current_cheque_id" :value="model?.payableChequeId ?? 0" />
					<input type="hidden" name="cash_id" :value="model?.cashPaymentId ?? 0" />
					<input type="hidden" id="ajax-invoice-item" :data-single-model="singleModel ? 1 : 0"
						:value="singleModel || 0" />
					<input id="js-down-payment-id" type="hidden" name="down_payment_id" :value="model?.id ?? 0" />
					<template v-if="isEdit">
						<input type="hidden" name="modelId" :value="model.id" />
						<input type="hidden" name="modelType" value="MoneyPayment" />
					</template>
					<input type="hidden" name="_token" :value="csrfToken" />
					<input v-if="isEdit" type="hidden" name="_method" value="put" />
					<div class="kt-portlet mp-card">
						<div class="kt-portlet__head">
							<div class="kt-portlet__head-label">
								<h3 class="kt-portlet__head-title head-title text-primary">Money Payment</h3>
							</div>
						</div>
						<div class="kt-portlet__body">
							<div class="form-group row">
								<div class="col-md-2">
									<label>Payment Date</label>
									<div class="kt-input-icon">
										<div class="input-group date">
											<input type="text" name="delivery_date"
												:value="model?.deliveryDateFormatted ?? ''"
												class="form-control balance-date exchange-rate-date update-exchange-rate is-date-css"
												readonly placeholder="Select date"
												id="kt_datepicker_max_date_is_today" />
											<div class="input-group-append">
												<span class="input-group-text">
													<i class="la la-calendar-check-o"></i>
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<label>Partner Type <span class="text-danger required-label">*</span></label>
									<div class="kt-input-icon">
										<div class="input-group date">
											<select required name="partner_type" id="partner_type" class="form-control">
												<option v-for="pt in partnerTypes" :key="pt.type" :value="pt.type"
													:selected="model?.partnerType === pt.type">
													{{ pt.title }}
												</option>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-1" id="invoice-currency-div-id">
									<label class="text-nowrap">Invoice Currency <span
											class="text-danger required-label">*</span></label>
									<div class="kt-input-icon">
										<div class="input-group date">
											<select id="invoice-currency-id" name="currency" :class="[
												'form-control',
												'currency-class',
												!singleModel && !isEdit ? 'invoice-currency-class' : '',
												'update-exchange-rate',
												'current-invoice-currency',
												'ajax-get-invoice-numbers',
												selectedCurrencyExtraClass || '',
											]">
												<option v-for="c in invoiceCurrencies" :key="c.id" :value="c.id"
													:selected="c.selected">
													{{ c.label }}
												</option>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<label>Name <span class="text-danger required-label">*</span></label>
									<div class="kt-input-icon">
										<div class="kt-input-icon">
											<div class="input-group date">
												<select :data-current-selected="model?.supplierName ?? ''"
													data-live-search="true" data-actions-box="true" id="supplier_name"
													name="supplier_id"
													class="form-control select2-select ajax-get-invoice-numbers ajax-update-contracts supplier-select supplier-js">
													<option value="" selected>Select</option>
													<option v-for="s in suppliers" :key="s.id" :value="s.id"
														:selected="!!s.selected || (!!singleModel && !isEdit)">
														{{ s.name }}
													</option>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-1">
									<label class="text-nowrap">Pay Currency <span
											class="text-danger required-label">*</span></label>
									<div class="kt-input-icon">
										<div class="input-group date">
											<select id="receiving-currency-id" when-change-trigger-account-type-change
												name="payment_currency"
												class="form-control contract-currency ajax-update-contracts currency-class receiving-currency-class update-exchange-rate current-currency">
												<option v-for="c in paymentCurrencies" :key="c.id" :value="c.id"
													:selected="c.selected">
													{{ c.label }}
												</option>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<label>Money Type <span class="text-danger required-label">*</span></label>
									<div class="kt-input-icon">
										<div class="input-group date">
											<select required name="type" id="type" class="form-control">
												<option value="" selected>Select</option>
												<option :value="MP.CASH_PAYMENT" :selected="model?.isCashPayment">Cash
													Payment</option>
												<option :value="MP.PAYABLE_CHEQUE" :selected="model?.isPayableCheque">
													Payable Cheques </option>
												<option :value="MP.OUTGOING_TRANSFER"
													:selected="model?.isOutgoingTransfer">Outgoing Transfer</option>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-2" data-current-selected="" id="transaction-type-parent">
									<label>Transaction <span class="text-danger required-label">*</span></label>
									<div class="kt-input-icon">
										<div class="input-group date">
											<select required name="transaction_type" id="transaction_type"
												class="form-control"></select>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- Cash Payment -->
					<div class="kt-portlet js-section-parent hidden mp-card" :id="MP.CASH_PAYMENT">
						<div class="kt-portlet__head">
							<div class="kt-portlet__head-label flex-1">
								<h3 class="kt-portlet__head-title head-title text-primary">Cash Payment Information</h3>
								<div class="flex-1 d-flex justify-content-end pt-3">
									<div class="col-md-3 mb-3">
										<label>Balance <span class="balance-date-js"></span></label>
										<div class="kt-input-icon">
											<input value="0" type="text" disabled class="form-control cash-balance-js"
												:data-type="MP.PAYABLE_CHEQUE" placeholder="Account Balance" />
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="kt-portlet__body">
							<div class="form-group">
								<div class="row">
									<div class="col-md-5 width-40">
										<label>Paying Branch <span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<div class="input-group date">
												<select id="branch-id" name="delivery_branch_id" class="form-control"
													:data-current-selected="model?.cashPaymentBranchId ?? ''">
													<option v-for="b in selectedBranches" :key="b.id" :value="b.id"
														:selected="model?.cashPaymentBranchId === b.id">
														{{ b.name }}
													</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-2 closest-parent">
										<label>Paid Amount <span class="currency-span"></span> <span
												class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input :data-current-value="model?.paidAmount ?? 0"
												data-max-cheque-value="0" type="text" :value="model?.paidAmount ?? 0"
												:name="'paid_amount[' + MP.CASH_PAYMENT + ']'"
												:class="'form-control only-greater-than-or-equal-zero-allowed js-' + MP.CASH_PAYMENT + '-paid-amount main-amount-class recalculate-amount-class'"
												:data-type="MP.CASH_PAYMENT" placeholder="Paid Amount" />
											<span class="kt-input-icon__icon kt-input-icon__icon--right col-md-6"
												tabindex="0" role="button" data-toggle="kt-popover" data-trigger="focus"
												data-html="true" data-content="Kash Vero">
												<span><i class="fa fa-question text-primary"></i></span>
											</span>
										</div>
									</div>
									<div class="col-md-3 width-12">
										<label>Receipt Number <span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input type="text" name="receipt_number"
												:value="model?.cashPaymentReceiptNumber ?? ''" class="form-control"
												placeholder="Receipt Number" />
											<span class="kt-input-icon__icon kt-input-icon__icon--right col-md-6"
												tabindex="0" role="button" data-toggle="kt-popover" data-trigger="focus"
												data-html="true" data-content="Kash Vero">
												<span><i class="fa fa-question text-primary"></i></span>
											</span>
										</div>
									</div>
									<div
										class="col-md-2 width-12 show-only-when-invoice-currency-not-equal-receiving-currency">
										<label>Exchange Rate <span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input :data-current-value="model?.exchangeRate ?? 1"
												:value="model?.exchangeRate ?? 1" placeholder="Exchange Rate"
												type="text" :name="'exchange_rate[' + MP.CASH_PAYMENT + ']'"
												class="form-control only-greater-than-or-equal-zero-allowed exchange-rate-class recalculate-amount-class"
												:data-type="MP.CASH_PAYMENT" />
										</div>
									</div>
									<div
										class="col-md-3 mt-4 show-only-when-invoice-currency-not-equal-receiving-currency hidden">
										<label>Amount In Invoice Currency <span
												class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input readonly :value="0" type="text"
												:name="'amount_in_invoice_currency[' + MP.CASH_PAYMENT + ']'"
												class="form-control amount-after-exchange-rate-class"
												:data-type="MP.CASH_PAYMENT" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- Payable Cheque -->
					<div class="kt-portlet js-section-parent hidden mp-card" :id="MP.PAYABLE_CHEQUE">
						<div class="kt-portlet__head">
							<div class="kt-portlet__head-label flex-1">
								<h3 class="kt-portlet__head-title head-title text-primary">Payable Cheque Information
								</h3>
								<div class="flex-1 d-flex justify-content-end pt-3">
									<div class="col-md-3 mb-3">
										<label>Balance <span class="balance-date-js"></span></label>
										<div class="kt-input-icon">
											<input value="0" type="text" disabled class="form-control balance-js"
												placeholder="Account Balance" />
										</div>
									</div>
									<div class="col-md-3 mb-3">
										<label>Net Balance <span class="net-balance-date-js"></span></label>
										<div class="kt-input-icon">
											<input value="0" type="text" disabled class="form-control net-balance-js"
												placeholder="Net Balance" />
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="kt-portlet__body">
							<div class="form-group">
								<div class="row">
									<div class="col-md-6 mb-3">
										<label>Payment Bank <span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<div class="input-group date">
												<select js-when-change-trigger-change-account-type
													data-financial-institution-id
													:name="'delivery_bank_id[' + MP.PAYABLE_CHEQUE + ']'"
													class="form-control financial-institution-id">
													<option v-for="(fib, i) in financialInstitutionBanks" :key="i"
														:value="fib.id"
														:selected="model?.payableChequePaymentBankId === fib.id">
														{{ fib.name }}
													</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<label>Account Type <span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<div class="input-group date">
												<select :name="'account_type[' + MP.PAYABLE_CHEQUE + ']'"
													class="form-control js-update-account-number-based-on-account-type">
													<option value="" selected>Select</option>
													<option v-for="(at, ai) in accountTypes" :key="ai" :value="at.id"
														:selected="model?.payableChequeAccountTypeId === at.id">
														{{ at.name }}
													</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-2 width-12">
										<label>Account Number <span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<div class="input-group date">
												<select :data-current-selected="model?.payableChequeAccountNumber ?? 0"
													:name="'account_number[' + MP.PAYABLE_CHEQUE + ']'"
													class="form-control js-account-number">
													<option value="" selected>Select</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-3 closest-parent">
										<label>Cheque Amount <span class="currency-span"></span> <span
												class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input data-max-cheque-value="0" :value="model?.paidAmount ?? 0"
												placeholder="Please insert the cheque amount" type="text"
												:name="'paid_amount[' + MP.PAYABLE_CHEQUE + ']'"
												:class="'form-control only-greater-than-or-equal-zero-allowed js-' + MP.PAYABLE_CHEQUE + '-paid-amount main-amount-class recalculate-amount-class'"
												:data-type="MP.PAYABLE_CHEQUE" />
										</div>
									</div>
									<div class="col-md-3">
										<label>Due Date <span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<div class="input-group date">
												<input type="text" :value="model?.payableChequeDueDateFormatted ?? ''"
													name="due_date" class="form-control is-date-css" readonly
													placeholder="Select date" id="kt_datepicker_2" />
												<div class="input-group-append">
													<span class="input-group-text">
														<i class="la la-calendar-check-o"></i>
													</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<label>Cheque Number <span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input type="text" name="cheque_number"
												:value="model?.payableChequeNumber ?? 0" class="form-control"
												placeholder="Cheque Number" />
										</div>
									</div>
									<div
										class="col-md-2 width-12 show-only-when-invoice-currency-not-equal-receiving-currency">
										<label>Exchange Rate <span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input :data-current-value="model?.exchangeRate ?? 1"
												:value="model?.exchangeRate ?? 1" placeholder="Exchange Rate"
												type="text" :name="'exchange_rate[' + MP.PAYABLE_CHEQUE + ']'"
												class="form-control only-greater-than-or-equal-zero-allowed exchange-rate-class recalculate-amount-class"
												:data-type="MP.PAYABLE_CHEQUE" />
										</div>
									</div>
									<div
										class="col-md-3 mt-4 show-only-when-invoice-currency-not-equal-receiving-currency hidden closest-parent">
										<label>Amount In Invoice Currency <span class="currency-span"></span> <span
												class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input readonly :value="0" type="text"
												:name="'amount_in_invoice_currency[' + MP.PAYABLE_CHEQUE + ']'"
												class="form-control amount-after-exchange-rate-class"
												:data-type="MP.PAYABLE_CHEQUE" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- Outgoing Transfer -->
					<div class="kt-portlet js-section-parent hidden mp-card" :id="MP.OUTGOING_TRANSFER">
						<div class="kt-portlet__head">
							<div class="kt-portlet__head-label flex-1">
								<h3 class="kt-portlet__head-title head-title text-primary">Outgoing Transfer Information
								</h3>
								<div class="flex-1 d-flex justify-content-end pt-3">
									<div class="col-md-3 mb-3">
										<label>Balance <span class="balance-date-js"></span></label>
										<div class="kt-input-icon">
											<input value="0" type="text" disabled class="form-control balance-js"
												:data-type="MP.OUTGOING_TRANSFER" placeholder="Account Balance" />
										</div>
									</div>
									<div class="col-md-3 mb-3">
										<label>Net Balance <span class="net-balance-date-js"></span></label>
										<div class="kt-input-icon">
											<input value="0" type="text" disabled class="form-control net-balance-js"
												placeholder="Net Balance" />
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="kt-portlet__body">
							<div class="form-group">
								<div class="row">
									<div class="col-md-5 width-40">
										<label><span v-html="'Payment <br> Bank'"></span> <span
												class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<div class="input-group date">
												<select js-when-change-trigger-change-account-type
													data-financial-institution-id
													:name="'delivery_bank_id[' + MP.OUTGOING_TRANSFER + ']'"
													class="form-control financial-institution-id">
													<option v-for="(fib, i) in financialInstitutionBanks"
														:key="'ot-' + i" :value="fib.id"
														:selected="model?.outgoingTransferDeliveryBankId === fib.id">
														{{ fib.name }}
													</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<label><span v-html="'Account <br> Type'"></span> <span
												class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<div class="input-group date">
												<select :name="'account_type[' + MP.OUTGOING_TRANSFER + ']'"
													class="form-control js-update-account-number-based-on-account-type">
													<option value="" selected>Select</option>
													<option v-for="(at, ai) in accountTypes" :key="'ota-' + ai"
														:value="at.id"
														:selected="model?.outgoingTransferAccountTypeId === at.id">
														{{ at.name }}
													</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-2 width-12">
										<label><span v-html="'Account <br> Number'"></span> <span
												class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<div class="input-group date">
												<select
													:data-current-selected="model?.outgoingTransferAccountNumber ?? 0"
													:name="'account_number[' + MP.OUTGOING_TRANSFER + ']'"
													class="form-control js-account-number">
													<option value="" selected>Select</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-2 max-w-15 closest-parent">
										<label><span v-html="'Outgoing <br> Transfer Amount'"></span> <span
												class="currency-span"></span>
											<span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input :data-current-value="model?.paidAmount ?? 0"
												data-max-cheque-value="0" type="text" :value="model?.paidAmount ?? 0"
												:name="'paid_amount[' + MP.OUTGOING_TRANSFER + ']'"
												:class="'form-control only-greater-than-or-equal-zero-allowed js-' + MP.OUTGOING_TRANSFER + '-paid-amount main-amount-class recalculate-amount-class'"
												:data-type="MP.OUTGOING_TRANSFER" placeholder="Insert Amount" />
										</div>
									</div>
									<div
										class="col-md-3 mt-4 show-only-when-invoice-currency-not-equal-receiving-currency">
										<label>Exchange Rate <span class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input :data-current-value="model?.exchangeRate ?? 1"
												:value="model?.exchangeRate ?? 1" placeholder="Exchange Rate"
												type="text" :name="'exchange_rate[' + MP.OUTGOING_TRANSFER + ']'"
												class="form-control only-greater-than-or-equal-zero-allowed exchange-rate-class recalculate-amount-class"
												:data-type="MP.OUTGOING_TRANSFER" />
										</div>
									</div>
									<div
										class="col-md-3 mt-4 show-only-when-invoice-currency-not-equal-receiving-currency hidden">
										<label>Amount In Invoice Currency <span
												class="text-danger required-label">*</span></label>
										<div class="kt-input-icon">
											<input readonly :value="0" type="text"
												:name="'amount_in_invoice_currency[' + MP.OUTGOING_TRANSFER + ']'"
												class="form-control amount-after-exchange-rate-class"
												:data-type="MP.OUTGOING_TRANSFER" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<template v-if="showSettlementBlock">
						<div class="kt-portlet mp-card" id="settlement-card-id">
							<div class="kt-portlet__head">
								<div class="kt-portlet__head-label">
									<h3 class="kt-portlet__head-title head-title text-primary">Settlement Information
									</h3>
								</div>
							</div>
							<div class="kt-portlet__body">
								<div class="js-append-to">
									<div class="col-md-12 js-duplicate-node"></div>
								</div>
								<div class="js-template hidden">
									<div class="col-md-12 js-duplicate-node">
										<div class="kt-margin-b-10 border-class">
											<div class="form-group row align-items-end settlement-row-parent">
												<div class="col-md-1 width-10">
													<label>Invoice Number</label>
													<div class="kt-input-icon">
														<div class="kt-input-icon">
															<div class="input-group date">
																<input type="hidden" name="settlements[][invoice_id]"
																	value="0" class="js-invoice-id" />
																<input readonly class="form-control js-invoice-number"
																	data-invoice-id="0"
																	name="settlements[][invoice_number]" value="0" />
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-1 width-9">
													<label>Invoice Date</label>
													<div class="kt-input-icon">
														<div class="input-group date">
															<input name="settlements[][invoice_date]" type="text"
																class="form-control js-invoice-date" disabled />
														</div>
													</div>
												</div>
												<div class="col-md-1 width-9">
													<label>Due Date</label>
													<div class="kt-input-icon">
														<div class="input-group date">
															<input name="settlements[][invoice_due_date]" type="text"
																class="form-control js-invoice-due-date" disabled />
														</div>
													</div>
												</div>
												<div class="col-md-1 width-8">
													<label>Currency</label>
													<div class="kt-input-icon">
														<input name="settlements[][currency]" type="text" disabled
															class="form-control js-currency" />
													</div>
												</div>
												<div class="col-md-1 width-12">
													<label>Net Invoice Amount</label>
													<div class="kt-input-icon">
														<input name="settlements[][net_invoice_amount]" type="text"
															disabled class="form-control js-net-invoice-amount" />
													</div>
												</div>
												<div class="col-md-2 width-12">
													<label>Paid Amount</label>
													<div class="kt-input-icon">
														<input name="settlements[][paid_amount]" type="text" disabled
															class="form-control js-paid-amount" />
													</div>
												</div>
												<div class="col-md-2 width-12">
													<label>Net Balance</label>
													<div class="kt-input-icon">
														<input name="settlements[][net_balance]" type="text" readonly
															class="form-control js-net-balance" />
													</div>
												</div>
												<div class="col-md-1 width-9-5">
													<label>Settlement Amount <span class="text-danger">*</span></label>
													<div class="kt-input-icon">
														<input name="settlements[][settlement_amount]" placeholder=""
															type="text"
															class="form-control js-settlement-amount only-greater-than-or-equal-zero-allowed settlement-amount-class" />
													</div>
												</div>
												<div class="col-md-1 width-9-5">
													<label>Withhold Amount <span class="text-danger">*</span></label>
													<div class="kt-input-icon">
														<input name="settlements[][withhold_amount]" placeholder=""
															type="text"
															class="form-control js-withhold-amount only-greater-than-or-equal-zero-allowed" />
													</div>
												</div>
												<div class="col-md-1">
													<button type="button" class="add-new btn btn-primary d-block"
														data-toggle="modal" data-target="#add-new-customer-modal--0">
														Allocate </button>
													<div class="modal fade modal-class-js allocate-modal-class"
														id="add-new-customer-modal--0" tabindex="-1" role="dialog"
														aria-hidden="true">
														<div class="modal-dialog modal-xl" role="document">
															<div class="modal-content">
																<div class="modal-header">
																	<h5 class="modal-title">Allocate</h5>
																	<button type="button" class="close"
																		data-dismiss="modal" aria-label="Close">
																		<span aria-hidden="true">&times;</span>
																	</button>
																</div>
																<div class="modal-body">
																	<div class="form-group row justify-content-center">
																		<div class="col-md-12 show-class-js js-parent-to-table"
																			data-table-id="m_repeater--0">
																			<table id="m_repeater--0"
																				class="table m_repeater--0 table-white repeater-class repeater allocations">
																				<thead>
																					<tr>
																						<th
																							class="form-label font-weight-bold text-center align-middle col-md-1 action-class">
																							+/-</th>
																						<th
																							class="form-label font-weight-bold text-center align-middle th-main-color">
																							Customer</th>
																						<th
																							class="form-label font-weight-bold text-center align-middle th-main-color">
																							Contract Name</th>
																						<th
																							class="form-label font-weight-bold text-center align-middle th-main-color">
																							Contract Code</th>
																						<th
																							class="form-label font-weight-bold text-center align-middle th-main-color">
																							Contract Amount</th>
																						<th
																							class="form-label font-weight-bold text-center align-middle th-main-color">
																							Allocate Amount</th>
																					</tr>
																				</thead>
																				<tbody data-repeater-list="allocations">
																					<tr data-repeater-item>
																						<td class="text-center">
																							<input type="hidden"
																								name="company_id"
																								:value="company.id" />
																							<div class="">
																								<i data-repeater-delete=""
																									class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle"></i>
																							</div>
																						</td>
																						<td>
																							<select name="partner_id"
																								data-name="partner_id"
																								class="form-control mb-1 select select3-select suppliers-or-customers-js custom-w-25"
																								data-live-search="true"
																								data-add-new="0"
																								data-all="0"
																								data-filter-type="create">
																								<option
																									v-for="c in clientsWithContracts"
																									:key="c.id"
																									:value="c.id">
																									{{ c.name }}
																								</option>
																							</select>
																						</td>
																						<td>
																							<select name="contract_id"
																								data-name="contract_id"
																								data-current-selected=""
																								class="form-control mb-1 select select3-select contracts-js custom-w-25"
																								data-live-search="true"
																								data-add-new="0"
																								data-all="0"
																								data-filter-type="create"></select>
																						</td>
																						<td>
																							<div
																								class="kt-input-icon custom-w-20">
																								<div
																									class="input-group">
																									<input disabled
																										type="text"
																										class="form-control contract-code"
																										value="" />
																								</div>
																							</div>
																						</td>
																						<td>
																							<div
																								class="kt-input-icon custom-w-15">
																								<div
																									class="input-group">
																									<input disabled
																										type="text"
																										class="form-control contract-amount"
																										value="0" />
																								</div>
																							</div>
																						</td>
																						<td>
																							<div
																								class="kt-input-icon custom-w-15">
																								<div
																									class="input-group">
																									<input type="text"
																										data-name="allocation_amount"
																										name="allocation_amount"
																										class="form-control allocation-amount-class"
																										value="0" />
																								</div>
																							</div>
																						</td>
																					</tr>
																				</tbody>
																				<td>
																					<div data-repeater-create=""
																						class="btn btn btn-sm text-white add-row btn-div border-green bg-green m-btn m-btn--icon m-btn--pill m-btn--wide">
																						<span>+ <span></span></span>
																					</div>
																				</td>
																			</table>
																		</div>
																	</div>
																</div>
																<div class="modal-footer">
																	<button type="button" class="btn btn-secondary"
																		data-dismiss="modal">Close</button>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<hr />
								<div class="row" id="contract-row-id">
									<div class="col-12">
										<hr />
									</div>
									<div class="col-md-12">
										<h3 class="kt-portlet__head-title head-title text-primary">Choose Contract For
											Down Payment</h3>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label for="contracts">Contracts</label>
											<select :data-current-selected="model?.contractId ?? 0" name="contract_id"
												id="contract-id"
												class="form-control ajax-get-purchases-orders-for-contract"></select>
										</div>
									</div>
									<div v-if="warningMessage" class="col-md-12" v-html="warningMessage"></div>
									<div class="col-md-12">
										<div class="js-append-down-payment-to">
											<div class="col-md-12 js-duplicate-node"></div>
										</div>
										<div class="js-down-payment-template hidden">
											<div class="col-md-12 js-duplicate-node">
												<div class="kt-margin-b-10 border-class">
													<div class="form-group row align-items-end">
														<div class="col-md-4">
															<label>PO Number</label>
															<div class="kt-input-icon">
																<input
																	name="purchases_orders_amounts[][purchases_order_name]"
																	type="text" readonly
																	class="form-control js-purchases-order-name" />
																<input
																	name="purchases_orders_amounts[][purchases_order_id]"
																	type="hidden" readonly
																	class="form-control js-purchases-order-number" />
															</div>
														</div>
														<div class="col-md-2 closest-parent">
															<label>Amount <span
																	class="contract-currency"></span></label>
															<div class="kt-input-icon">
																<input
																	name="purchases_orders_amounts[][net_invoice_amount]"
																	type="text" disabled
																	class="form-control js-amount" />
															</div>
														</div>
														<div class="col-md-2 closest-parent">
															<label>Paid Amount <span class="contract-currency"></span>
																<span
																	class="text-danger required-label">*</span></label>
															<div class="kt-input-icon">
																<input name="purchases_orders_amounts[][paid_amounts]"
																	placeholder="Paid Amount" type="text"
																	class="form-control js-paid-amount only-greater-than-or-equal-zero-allowed settlement-amount-class" />
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-1 width-10"></div>
									<div class="col-md-1 width-8"></div>
									<div class="col-md-1 width-8"></div>
									<div class="col-md-1 width-8"></div>
									<div class="col-md-1 width-12"></div>
									<div class="col-md-2 width-12"></div>
									<div class="col-md-2 width-12"></div>
									<div class="col-md-2 width-12 closest-parent">
										<label class="label text-nowrap">Unapplied Amount <span
												class="taking-currency-span"></span></label>
										<input readonly id="remaining-settlement-taking-js" class="form-control"
											placeholder="Unapplied Amount" type="text" value="0" />
									</div>
									<div class="col-md-2 width-12 closest-parent">
										<label class="label">Unapplied Amount <span
												class="invoice-currency-span"></span></label>
										<input readonly id="remaining-settlement-js" class="form-control"
											placeholder="Unapplied Amount" type="text" name="unapplied_amount"
											value="0" />
									</div>
								</div>
							</div>
						</div>
					</template>
					<div class="kt-portlet mp-card">
						<div class="kt-portlet__head">
							<div class="kt-portlet__head-label">
								<h3 class="kt-portlet__head-title head-title text-primary">User Comment</h3>
							</div>
						</div>
						<div class="kt-portlet__body">
							<div class="form-group row">
								<div class="col-md-12">
									<label for="user-comment">Comment</label>
									<textarea id="user-comment" v-model="userComment" class="form-control"
										name="user_comment"></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="kt-portlet mp-card">
						<div class="kt-portlet__foot">
							<div class="kt-form__actions">
								<div class="row">
									<div class="col-lg-6"></div>
									<div class="col-lg-6 kt-align-right">
										<a v-if="routes.viewMoneyPayment" :href="routes.viewMoneyPayment"
											class="btn active-style">Close</a>
										<button type="submit" class="btn active-style submit-form-btn">Save</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
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

.mp-create-root {
	background: var(--bg-page);
	color: var(--text-primary);
	min-height: 100%;
}

.mp-card {
	background: var(--bg-card) !important;
	border: 1px solid var(--border) !important;
	border-radius: 10px !important;
	box-shadow: 0 4px 24px rgba(0, 0, 0, 0.5) !important;
	border-top: 3px solid var(--teal) !important;
	overflow: visible !important;
	margin-bottom: 1rem;
}

.mp-card .kt-portlet__head {
	background: var(--bg-card-hover) !important;
	border-bottom: 1px solid var(--border) !important;
}

.mp-card .kt-portlet__head-title,
.mp-card h3.head-title {
	color: var(--teal) !important;
	border-left: 4px solid var(--gold);
	padding-left: 0.5rem;
}

.mp-create-root :deep(.form-control),
.mp-create-root :deep(select.form-control),
.mp-create-root :deep(textarea.form-control) {
	background: var(--bg-input) !important;
	border: 1px solid var(--border) !important;
	border-radius: 6px !important;
	color: var(--text-primary) !important;
}

.mp-create-root :deep(.form-control:focus) {
	border-color: var(--border-focus) !important;
	box-shadow: 0 0 0 3px rgba(20, 144, 168, 0.2) !important;
}

.mp-create-root :deep(label),
.mp-create-root :deep(.label) {
	color: var(--text-secondary) !important;
	font-weight: 600 !important;
	font-size: 0.85rem !important;
	text-transform: uppercase;
}

.mp-create-root :deep(.required-label),
.mp-create-root :deep(.text-danger.required-label) {
	color: var(--gold) !important;
}

.mp-create-root :deep(.btn-primary),
.mp-create-root :deep(.btn-success),
.mp-create-root :deep(.bg-green) {
	background: var(--teal) !important;
	color: #0c1829 !important;
	border-radius: 6px !important;
	font-weight: 600 !important;
	border: none !important;
}

.mp-create-root :deep(.active-style) {
	background: var(--gold) !important;
	color: #0c1829 !important;
	border-radius: 6px !important;
	font-weight: 600 !important;
}

.mp-create-root :deep(.btn-secondary) {
	background: transparent !important;
	color: var(--teal) !important;
	border: 1px solid var(--teal) !important;
	border-radius: 6px !important;
	font-weight: 600 !important;
}

.mp-create-root :deep(input.form-control[disabled]:not(.ignore-global-style)),
.mp-create-root :deep(input.form-control:not(.is-date-css)[readonly]) {
	background: var(--bg-input) !important;
	opacity: 1 !important;
	font-weight: bold !important;
}

.kt-portlet {
	overflow: visible !important;
}

.max-w-6 {
	max-width: initial !important;
	width: 6% !important;
	flex: initial !important;
}

.max-w-15 {
	max-width: initial !important;
	width: 15% !important;
	flex: initial !important;
}

.width-8 {
	max-width: initial !important;
	width: 8% !important;
	flex: initial !important;
}

.width-9-5 {
	max-width: initial !important;
	width: 9% !important;
	flex: initial !important;
}

.width-10 {
	max-width: initial !important;
	width: 10% !important;
	flex: initial !important;
}

.width-12 {
	max-width: initial !important;
	width: 12.5% !important;
	flex: initial !important;
}

.width-40 {
	max-width: initial !important;
	width: 40% !important;
	flex: initial !important;
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
</style>
