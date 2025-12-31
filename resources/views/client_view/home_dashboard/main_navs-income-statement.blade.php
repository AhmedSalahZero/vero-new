@php
	$user = Auth()->user();
@endphp
<ul class="kt-menu__nav ">
	@if($user->can('view income statement dashboard') && $user->can('view forecast income statement dashboard'))
    <li class="kt-menu__item  kt-menu__item 
        
        @if($active == 'breadkdown_dashboard')
                active-button
                @endif 

        " aria-haspopup="true"><a href="{{ route('dashboard.breakdown.incomeStatement', ['reportType'=>'forecast','company'=>$company]) }}" class="kt-menu__link "><span class="kt-menu__link-text
                
                @if($active == 'breadkdown_dashboard')
                active-text
                    @endif 

                "> {{ __('Income Statement Dashboard') }}</span></a>
    </li>
	@endif 

	@if($user->can('view income statement dashboard') && $user->can('view income statement variance dashboard'))
    <li class="kt-menu__item  kt-menu__item 
	@if($active == 'various_incomestatement_dashboard')
	
			 active-button
			  @endif 

  
  " aria-haspopup="true"><a href="{{ route('dashboard.various.incomeStatement',['subItemType'=>'forecast','company'=>$company]) }}" class="kt-menu__link 
		  
		  
		  "><span class="kt-menu__link-text 
			@if($active == 'various_incomestatement_dashboard')
			 active-text
			  @endif 
			  ">{{__("Variance Comparing")}}</span></a>
    </li>
	@endif 

	@if($user->can('view income statement dashboard') && $user->can('view income statement comparing dashboard'))
    <li class="kt-menu__item  kt-menu__item 
          @if($active == 'interval_dashboard')
                   active-button
                    @endif 

        
        " aria-haspopup="true"><a href="{{ route('dashboard.intervalComparing.incomeStatement',['subItemType'=>'forecast','company'=>$company]) }}" class="kt-menu__link 
                
                
                "><span class="kt-menu__link-text 
                  @if($active == 'interval_dashboard')
                   active-text
                    @endif 
                    ">{{__("Income Statement Comparing")}}</span></a>
    </li>
	@endif 


</ul>
