                @php
                $isRepeater = !(isset($removeRepeater) && $removeRepeater) ;
                $type = 'create';
                @endphp


                <div style="flex-wrap:nowrap;" @if($isRepeater) data-repeater-item @endif class="form-group date-element-parent m-form__group row align-items-center 
                                         @if($isRepeater)
                                         repeater_item
                                         @endif 
				                         ">
                                <input type="hidden" class="form-control " @if($isRepeater) name="id" @else name="opening[0][id]" @endif value="{{ isset($receivable_and_payment) ? $receivable_and_payment->getId() : 0 }}">



                    <div class="col-2">
                        <label class="form-label font-weight-bold ">{{ __('Name') }}
                            {{-- @include('star')  --}}
                        </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input type="text" class="form-control  exclude-text" @if($isRepeater) name="receivable_name" @else name="opening[0][receivable_name]" @endif value="{{ isset($receivable_and_payment) ? $receivable_and_payment->getName() : old('receivable_name') }}">
                                <input type="hidden" class="form-control " @if($isRepeater) name="old_receivable_name" @else name="opening[0][old_receivable_name]" @endif value="{{ isset($receivable_and_payment) ? $receivable_and_payment->getName() : old('old_receivable_name') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-1">
                        <label class="form-label font-weight-bold">{{ __('Balance') }}
                            {{-- @include('star') --}}
                        </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input type="text" class="form-control only-greater-than-or-equal-zero-allowed trigger-change-repeater"  value="{{ number_format(isset($receivable_and_payment) ? $receivable_and_payment->getBalanceAmount() : old('balance_amount',0)) }}">
								<input type="hidden" value="{{ (isset($receivable_and_payment) ? $receivable_and_payment->getBalanceAmount() : old('balance_amount',0)) }}" @if($isRepeater) name="balance_amount" @else name="opening[0][balance_amount]" @endif>
                            </div>
                        </div>
                    </div>
				
                    @foreach($datesFormatted as $dateAsIndex => $dateAsString)
					<input type="hidden" name="dates" value="{{ $dateAsIndex }}">
                    <div class="col-1 text-center">
                        <label class="form-label font-weight-bold">{{ formatDateForView($dateAsString) }} </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input type="text" class="form-control only-greater-than-or-equal-zero-allowed date-value-element trigger-change-repeater"  value="{{ number_format(isset($receivable_and_payment) ? $receivable_and_payment->getReceivableValueAtDate($dateAsIndex) : old('payload',0) ) }}" step="0.5">
								<input class="date-value-element-hidden" type="hidden" @if($isRepeater) name="payload[{{ $dateAsIndex }}]" @else name="opening[0][payload][{{ $dateAsIndex }}]" @endif value="{{ (isset($receivable_and_payment) ? $receivable_and_payment->getReceivableValueAtDate($dateAsIndex) : old('balance_amount',0)) }}" >
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div class="col-1 text-center receivable_total_parent">
                        <label class="form-label font-weight-bold">{{ __('Total') }} </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input readonly type="text" class="form-control date-element-total-input only-greater-than-or-equal-zero-allowed trigger-change-repeater" @if($isRepeater) name="receivable_total" @else name="opening[0][receivable_total]" @endif value="0" step="0.5">
                            </div>
                        </div>
                    </div>

                    @if($isRepeater)
                    <div class="">
                        <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                        </i>
                    </div>
                    @endif


                </div>
