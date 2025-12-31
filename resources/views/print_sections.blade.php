     @if ($subSection->route != null && count($subSection->subSections) == 0)
     <?php
                                                    $route = isset($subSection->route) && $subSection->route !== null ? explode('.', $subSection->route) : null;
                                                    ?>
     @if ($subSection->id == 222 || $subSection->id == 223 || $subSection->id == 224 || $subSection->id == 225 || $subSection->id == 226)
     <?php
                                                                $show_route = 0 ;
                                                                $modified_seasonality =  App\Models\ModifiedSeasonality::where('company_id', $company->id)->first();
                                                                if (($subSection->id == 222 || $subSection->id == 226) && isset($modified_seasonality)  ) {
                                                                    $show_route = 1 ;
                                                                }
                                                                elseif ($subSection->id == 223 && isset($modified_seasonality) &&
                                                                (( App\Models\ExistingProductAllocationBase::where('company_id', $company->id)->first()) !== null) ) {
                                                                    $show_route = 1 ;
                                                                }elseif (($subSection->id == 224) && isset($modified_seasonality) &&
                                                                ((App\Models\SecondExistingProductAllocationBase::where('company_id', $company->id)->first()) !== null) ) {
                                                                    $show_route = 1 ;
                                                                }elseif (($subSection->id == 225  ) && isset($modified_seasonality) &&
                                                                ((App\Models\CollectionSetting::where('company_id', $company->id)->first()) !== null) ) {
                                                                    $show_route = 1 ;
                                                                }else {
                                                                    $show_route = 0;
                                                                }
                                                            ?>


     @if ($show_route == 1)
     <li class="kt-menu__item " aria-haspopup="true">
         <a href="{{ @$subSection->route == 'home' ? route(@$subSection->route) : route(@$subSection->route, $company) }}" class="kt-menu__link "><i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i><span class="kt-menu__link-text">{{ __($subSection->name[$lang]) }} 1oooo</span></a>
     </li>
     @endif
     @else
     @if($subSection->id != 270 || (Auth()->user()->canViewIncomeStatement()&& $user->can('view income statement dashboard')))
     @if($subSection->id == 270 )
     {{-- {{Income Statement Dashboard }} --}}
     <li class="kt-menu__item  kt-menu__item--submenu" data-ktmenu-submenu-toggle="hover" aria-haspopup="true"><a href="#" class="kt-menu__link kt-menu__toggle"><i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i><span class="kt-menu__link-text">{{ __('Income Statement') }}</span><i class="kt-menu__hor-arrow la la-angle-right"></i><i class="kt-menu__ver-arrow la la-angle-right"></i></a>
         <div class="kt-menu__submenu kt-menu__submenu--classic kt-menu__submenu--right">
             <ul class="kt-menu__subnav">
                 @foreach(['forecast'=>__('Forecast Dashboard'),'actual'=>__('Actual Dashboard'),'adjusted'=>__('Adjusted Dashboard'),'modified'=>__('Modified Dashboard'),'comparing'=>__('Comparing Dashboard')] as $reportType=>$reportName)
                 @if($reportType == 'comparing' && $user->can('view income statement comparing dashboard'))
                 <li class="kt-menu__item " aria-haspopup="true"><a href="{{ route('dashboard.intervalComparing.incomeStatement',['company'=>$company->id , 'subItemType'=>$reportType]) }}" class="kt-menu__link "><span class="kt-menu__link-text">{{ __($reportName) }} </span></a></li>
                 @else
                 @if($reportType == 'forecast' && $user->can('view forecast income statement dashboard') || $reportType=='actual' &&$user->can('view actual income statement dashboard') || $reportType=='adjusted' && $user->can('view adjusted income statement dashboard') || $reportType=='modified'&&$user->can('view modified income statement dashboard'))
                 <li class="kt-menu__item " aria-haspopup="true"><a href="{{ route('dashboard.breakdown.incomeStatement',['company'=>$company->id , 'reportType'=>$reportType]) }}" class="kt-menu__link "><span class="kt-menu__link-text">{{ __($reportName) }} </span></a></li>
                 @endif
                 @endif
                 @endforeach
             </ul>
         </div>
     </li>
     @else
     @if($subSection->id == 215 && $user->can('view sales dashboard') || $subSection->id == 216 && $user->can('view breakdown dashboard') || $subSection->id == 219 && $user->can('view interval comparing dashboard'))
     <li class="kt-menu__item " aria-haspopup="true">

         <a href="{{ @$subSection->route == 'home' ? route(@$subSection->route) : route(@$subSection->route, $company) }}" class="kt-menu__link "><i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i><span class="kt-menu__link-text">{{ __($subSection->name[$lang] )  }} </span></a>
     </li>
     @endif
     @endif
     @endif

     @endif
     @else
     <?php
                                                        $route = isset($subSection->route) && $subSection->route !== null ? explode('.', $subSection->route) : null;
                                                        ?>

     <li class="kt-menu__item " aria-haspopup="true">
         <a href="{{ @$subSection->route == 'home' ? route(@$subSection->route) : route(@$subSection->route, $company) }}" class="kt-menu__link "><i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i><span class="kt-menu__link-text">{{ __($subSection->name[$lang]) }} 5hhhhhh</span></a>
     </li>

     @endif
