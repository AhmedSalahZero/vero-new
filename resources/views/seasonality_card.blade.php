@if($isYearsStudy)
<div class="kt-portlet">
    <div class="kt-portlet__body">
        <div class="row">
            <div class="col-md-10">
                <div class="d-flex align-items-center ">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                        {{ __('Seasonality') }}
                    </h3>
                </div>
            </div>
            <div class="col-md-2 text-right">
                <x-show-hide-btn :query="'.seasonality-row'"></x-show-hide-btn>
            </div>
        </div>

        <div class="row">
            <hr style="flex:1;background-color:lightgray">
        </div>
        <div class="row seasonality-row">
            <div class="col-12 ">
                {{-- Section Of Months --}}
                <?php $seasonalityType = $model->getSeasonalityType(); ?>
                <div class="form-group">
                    <label>{{__('Choose Seasonality')}}</label><span class="red">*</span>
                    <select name="seasonality[type]" id="seasonality" class="form-control @error('seasonality_type') is-invalid @enderror">
                        <option value="flat" {{$seasonalityType == 'flat' ?'selected' : '' }}>{{__('Flat Monthly')}}</option>
                        <option value="quarterly" {{$seasonalityType == 'quarterly' ?'selected' : '' }}>{{__('Distribute Quarterly')}}</option>
                        <option value="monthly" {{$seasonalityType == 'monthly' ?'selected' : '' }}>{{__('Distribute Monthly')}}</option>
                    </select>
                    @error('seasonality_type')
                    <div class="alert alert-danger" role="alert">
                        {{$message}}
                    </div>
                    @enderror
                </div>
                @error('monthly_total_percentage')
                <div class="alert alert-danger" role="alert">
                    {{$message}}
                </div>
                @enderror
                @error('quarterly_total_percentage')
                <div class="alert alert-danger" role="alert">
                    {{$message}}
                </div>
                @enderror
                @error('seasonality_constant')
                <div class="alert alert-danger" role="alert">
                    {{$message}}
                </div>
                @enderror
                {{-- flat --}}
                <div class="form-group flat_section {{$seasonalityType == 'flat' ?'' : 'hidden' }}">
                    <label for="annual_collection_rate">{{__('Monthly Seasonality %')}}</label>
                    <input type="number" step="any" class="form-control" value="{{ number_format(1/12*100 , 2)}}" readonly>
                </div>
                {{-- quarterly --}}
                <div class="form-group quarterly_section {{$seasonalityType == 'quarterly' ?'' : 'hidden' }}">

                    <div class="row closest-parent">

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{__('First Quarter %')}}</label>
                                <input type="number" step="any" class="form-control total_input quarterly" placeholder="{{__('First Quarter %')}}" value="{{$model->isQuarterlySeasonality() ? $model->getSeasonalityPercentagesAtIndex(0): 0}}" name="seasonality[quarterly][0]" id="first_quarter">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{__('Second Quarter %')}}</label>
                                <input type="number" step="any" class="form-control total_input quarterly" placeholder="{{__('Second Quarter %')}}" value="{{$model->isQuarterlySeasonality() ? $model->getSeasonalityPercentagesAtIndex(1): 0}}" name="seasonality[quarterly][1]" id="second_quarter">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{__('Third Quarter %')}}</label>
                                <input type="number" step="any" class="form-control total_input quarterly" placeholder="{{__('Third Quarter %')}}" value="{{$model->isQuarterlySeasonality() ? $model->getSeasonalityPercentagesAtIndex(2): 0}}" name="seasonality[quarterly][2]" id="third_quarter">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{__('Fourth Quarter %')}}</label>
                                <input type="number" step="any" class="form-control total_input quarterly" placeholder="{{__('Fourth Quarter %')}}" value="{{$model->isQuarterlySeasonality() ? $model->getSeasonalityPercentagesAtIndex(3) : 0}}" name="seasonality[quarterly][3]" id="fourth_quarter">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{__('Total %')}}</label>
                                <input type="text" step="any" class="form-control total_row_result" value="{{ $model->isQuarterlySeasonality() ? 100 :0 }}" readonly>
                            </div>
                        </div>

                    </div>
                </div>
                {{-- monthly --}}
                <div class="form-group monthly_section {{$seasonalityType == 'monthly' ?'' : 'hidden' }}">

                    <?php $month_num = date('d-m-y',strtotime('01-01-2020')); ?>
                    <div class="row closest-parent">
                        @foreach(getMonthsList() as $index => $monthName)

                        @php
                        $currentFieldName = "seasonality[monthly][$index]";
                        $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                        $currentVal = $model->isMonthlySeasonality() ? $model->getSeasonalityPercentagesAtIndex($index) : 0 ;
                        @endphp

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{__($monthName ." %")}}</label>
                                <input type="number" step="any" class="form-control total_input monthly" placeholder="{{__($monthName." %")}}" value="{{$currentVal}}" name="{{$currentFieldName}}" id="{{$nameToOld}}">
                                <input type="hidden" class="form-control total_input flat" value="{{ (1/12)*100 }}" name="seasonality[flat][{{ $index }}]" id="{{$nameToOld}}">
                            </div>
                        </div>


                        @endforeach
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{__('Total %')}}</label>
                                <input type="text" step="any" class="form-control total_row_result" value="{{ $model->isMonthlySeasonality() ? 100 :0 }}" readonly>
                            </div>
                        </div>

                    </div>



                </div>

                <div class="percentage mt--15 {{ ($seasonalityType == 'quarterly' || $seasonalityType == 'monthly') ? '' : 'hidden'}}">
                    <span class="red" style="color: green">{{"* ".__('Total Percentages Must Equal 100%')}}</span>
                </div>


            </div>
        </div>
    </div>
</div>
@endif
