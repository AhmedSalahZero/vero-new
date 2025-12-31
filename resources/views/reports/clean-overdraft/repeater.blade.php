                @php
                $isRepeater = !(isset($removeRepeater) && $removeRepeater) ;
                $type = 'create';
                @endphp


                <div style="flex-wrap:nowrap;" @if($isRepeater) data-repeater-item @endif class="form-group date-element-parent m-form__group row align-items-center 
                                         @if($isRepeater)
                                         repeater_item
                                         @endif 
				                         ">
                    <input type="hidden" class="form-control " @if($isRepeater) name="id" @else name="infos[0][id]" @endif value="{{ isset($infos) ? $infos->getId() : 0 }}">



                    <div class="col-2">
                        <label class="form-label font-weight-bold ">{{ __('Lending Limit Per Customer') }}
                        </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input placeholder="{{ __('Lending Limit Per Customer') }}" type="text" class="form-control  exclude-text" @if($isRepeater) name="max_lending_limit_per_customer" @else name="infos[0][max_lending_limit_per_customer]" @endif value="{{ isset($infos) ? $infos->getMaxLendingLimitPerCustomer() : old('max_lending_limit_per_customer') }}">
                            </div>
                        </div>
                    </div>




                    <div class="col-lg-3">
                        <label>{{__('Select Customer')}} @include('star')</label>
                        <div class="input-group">
                            <select @if($isRepeater) name="customer_name" @else name="infos[0][customer_name]" @endif class="form-control repeater-select">
                                <option selected>{{__('Select')}}</option>
                                @foreach($customers as $customerName )
                                <option value="{{ $customerName  }}" @if(isset($infos) && $infos->getCustomerName() == $customerName ) selected @endif > {{ $customerName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-2">
                        <label class="form-label font-weight-bold">{{ __('Commercial Papers Due Within') }}
                        </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input @if($isRepeater) name="for_commercial_papers_due_within_days" @else name="infos[0][for_commercial_papers_due_within_days]" @endif type="text" class="form-control 
								{{-- trigger-change-repeater --}}
								
								" value="{{ (isset($infos) ? $infos->for_commercial_papers_due_within_days : old('for_commercial_papers_due_within_days',0)) }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-2">
                        <label class="form-label font-weight-bold">{{ __('Lending Rate (%) *') }}
                        </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input @if($isRepeater) name="lending_rate" @else name="infos[0][lending_rate]" @endif type="text" class="form-control only-percentage-allowed
								{{-- trigger-change-repeater --}}
								" value="{{ (isset($infos) ? $infos->lending_rate : old('lending_rate',0)) }}">
                            </div>
                        </div>
                    </div>


                    <div class="col-2">
                        <label class="form-label font-weight-bold">{{ __('Setteled Max Within') }}
                        </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input @if($isRepeater) name="to_be_setteled_max_within_days" @else name="infos[0][to_be_setteled_max_within_days]" @endif type="text" class="form-control only-greater-than-zero-allowed 
								{{-- trigger-change-repeater --}}
								" value="{{ (isset($infos) ? $infos->to_be_setteled_max_within_days: old('to_be_setteled_max_within_days',0)) }}">
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
