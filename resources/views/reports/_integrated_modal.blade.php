@php
	/**
	 * * $extraOdooReferenceNames اختياري — صفحة الدفعات المقدمة بتضيف
	 * * مراجع التسويات اللي اتعملت علي الدفعة، مش بس مرجع الدفعة نفسها.
	 */
	$odooExtraReferenceNames = $extraOdooReferenceNames ?? [] ;
	/**
	 * * getOdooReferenceNames() اصلا بتلف علي التسويات وبتضيف
	 * * odoo_reference بتاعها، فلو الصفحة بعتت نفس المرجع ومعاه وصف
	 * * (مثلا "REF — Transfer Customer Advance to Receivable") كان
	 * * المرجع هيتعرض مرتين. بنشيل النسخة المجردة اللي ليها نسخة موصوفة.
	 */
	$odooReferenceNamesToShow = array_values(array_unique(array_merge(
		array_filter($model->getOdooReferenceNames(), function ($referenceName) use ($odooExtraReferenceNames) {
			foreach ($odooExtraReferenceNames as $extraReferenceName) {
				if (str_starts_with($extraReferenceName, $referenceName)) {
					return false;
				}
			}
			return true;
		}),
		$odooExtraReferenceNames
	))) ;
	$isFullyIntegratedWithOdoo = $model->fullyIntegratedWithOdoo() || count($odooExtraReferenceNames) ;
	$integratedModalId = 'fully-integrated-id-'.($modalKey ?? $model->id) ;
@endphp
 @if(
	$company->hasOdooIntegrationCredentials() &&
 $isFullyIntegratedWithOdoo)
<style>
.modal-header.blue{
	    border-bottom-color:#a8bcee !important;
}
</style>
 <a data-toggle="modal" data-target="#{{ $integratedModalId }}" type="button" class="btn btn-primary btn-icon" title="{{ __('Fully Integrated') }}" href="#"><i class="fa fa-thumbs-up"></i></a>
 <div class="modal fade" id="{{ $integratedModalId }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
             <form action="#" method="post">
                 @csrf
					{{-- <input type="hidden" name="model_name" value="{{ getModelNameWithoutNamespace($model) }}" >
					<input type="hidden" name="table_name" value="{{ $model->getTable() }}" > --}}
                 <div class="modal-header blue">
                     <h5 class="modal-title text-blue" id="exampleModalLongTitle">{{ __('Odoo References') }}</h5>
					 {{-- <hr class="text"> --}}
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                         <span aria-hidden="true">&times;</span>
                     </button>
                 </div>
				 <div class="modal-body">
				 
				 	<div>
					<ul class="list-unstyled">
				 @foreach($odooReferenceNamesToShow as $referenceName)
						<li class="mb-3 text-left">{{ $referenceName }}</li>
					@endforeach 
					</ul>
					</div>
				 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('Close') }}</button>
                     {{-- <button type="submit" class="btn btn-success">{{ __('Confirm') }}</button> --}}
                 </div>

             </form>
         </div>
     </div>
 </div>
@endif 
