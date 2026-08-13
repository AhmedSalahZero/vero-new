{{--
	* بيعرض زرار الخطأ (الحشرة) لو اخر محاولة مزامنة مع اودو فشلت، وجوه
	* المودال رسالة الخطأ نفسها.
	*
	* * $resendUrl اختياري:
	* * الراوت بتاع resend.with.odoo مربوط موديل MoneyReceived بالتحديد
	* * (MoneyReceivedController::resendToOdoo(MoneyReceived $moneyReceived))،
	* * فلو الصفحة بتعرض موديل تاني (مصروف، تحويل داخلي، خطاب ضمان ...)
	* * وبعتنا الـ id بتاعه هيروح يدور عليه في جدول MoneyReceived ويجيب
	* * سجل تاني خالص بنفس الرقم أو يطلع 404. عشان كده زرار "اعادة الارسال"
	* * بيظهر بس لما الصفحة تبعت لينك حقيقي شغال للموديل ده، وباقي الصفحات
	* * بتعرض الخطأ للقراءة فقط.
	--}}
@php
	$odooResendUrl = $resendUrl ?? null ;
	$odooModalId = 'odoo-model-'.($modalKey ?? $model->id) ;
	$odooExtraErrors = $extraOdooErrors ?? [] ;
	/**
	 * * في صفحة الدفعات المقدمة الخطأ ممكن يكون علي التسويات نفسها مش علي
	 * * الدفعة، عشان كده الصفحة تقدر تبعت $hasOdooErrorOverride.
	 */
	$showOdooError = $hasOdooErrorOverride ?? $model->hasOdooError() ;
@endphp
@if($company->hasOdooIntegrationCredentials() && $showOdooError )
 <a data-toggle="modal" data-target="#{{ $odooModalId }}" type="button" class="btn btn-icon bg-red text-white" title="{{ __('Odoo Error') }}" href="#"><i class="fa fa-bug"></i></a>
 <div class="modal fade" id="{{ $odooModalId }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
     <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
         <div class="modal-content">
             <form action="{{ $odooResendUrl ?: '#' }}" method="post">
                 @csrf
                 <div class="modal-header">
                     <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Odoo Error') }}</h5>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                         <span aria-hidden="true">&times;</span>
                     </button>
                 </div>
				 <div class="modal-body">
				 	<h2 class="text-wrap {{ \App\Helpers\HStr::isArabic($model->getOdooError()) ? 'text-right' : 'text-left' }}">{{ $model->getOdooError()  }}</h2>
					@foreach($odooExtraErrors as $extraOdooError)
						<h4 class="text-wrap mt-3 {{ \App\Helpers\HStr::isArabic($extraOdooError) ? 'text-right' : 'text-left' }}">{{ $extraOdooError }}</h4>
					@endforeach
				 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
					 @if($odooResendUrl)
                     <button type="submit" class="btn btn-success">{{ __('Resend') }}</button>
					 @endif
                 </div>

             </form>
         </div>
     </div>
 </div>
@endif
