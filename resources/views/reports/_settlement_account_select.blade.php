{{--
	* حساب التسوية : الحساب الجاري اللي اصل الوديعة هيترد عليه وقت الاستحقاق او الكسر
	* القيمة الافتراضية هي حساب الخصم الاصلي ، ولو الوديعة اتسجلت
	* opening balance
	* فا مفيش قيمة افتراضية واليوزر لازم يختار حساب
	*
	* $model    : TimeOfDeposit | CertificatesOfDeposit
	* $accounts : حسابات البنك الجارية ( بتتبعت من الصفحة علشان مانعملش استعلام لكل صف )
--}}
@php
	$settlementAccounts = $model->getSettlementAccountOptions($accounts ?? null);
	$selectedSettlementAccountId = (int) $model->getSettlementOrDeductedFromAccountId();
@endphp
<div class="col-md-4 mb-4">
	<label>{{ __('Settlement Account #') }}@include('star')</label>
	<div class="kt-input-icon">
		<select required name="settlement_account_id" class="form-control">
			<option value="">{{ __('Select') }}</option>
			@foreach($settlementAccounts as $settlementAccount)
			<option value="{{ $settlementAccount->getId() }}" @selected((int) $settlementAccount->getId() === $selectedSettlementAccountId)>{{ $settlementAccount->getAccountNumber() }}</option>
			@endforeach
		</select>
	</div>
</div>
