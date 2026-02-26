<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Helpers\HArr;
use App\Helpers\HDate;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PropertyManagement\Contract;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyDueInstallment;
use App\Models\PropertyManagement\Study;
use App\Traits\PropertyManagement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use PropertyManagement ;
    
    public function viewPropertyDashboard(Company $company, Request $request)
    {
        $allProperties = Property::whereNotIn('nature_id', [Property::COMPLEX,Property::BUILDING])->where('company_id', $company->id)->with(['type'])->get();
        $propertiesGroupedByType = $allProperties->groupBy('type_id')->map(fn ($q) =>count($q))->toArray();
        $occupiedPropertiesGroupedByType = $allProperties->filter(function (Property $property) {
            return $property->isOccupiedOrOccupiedWithInstallments();
        })->groupBy('type_id')->map(fn ($q) =>count($q))->toArray();
        $vacantPropertiesGroupedByType = $allProperties->filter(function (Property $property) {
            return $property->isVacantOrVacantWithInstallments();
        })->groupBy('type_id')->map(fn ($q) =>count($q))->toArray();
        $notDeliveredPropertiesGroupedByType = $allProperties->filter(function (Property $property) {
            return $property->isToBeDelivered();
        })->groupBy('type_id')->map(fn ($q) =>count($q))->toArray();
		$durationToStore = 6 ;
		$durationToShow = 3 ;
        $dashboardDate = $request->dashboard_date ?? now()->format('Y-m-d');
        $currentMonth = Carbon::make($dashboardDate)->format('F Y');
        $nextMonth = Carbon::make($dashboardDate)->addMonth()->format('F Y');
        $twoMonthsLater = Carbon::make($dashboardDate)->addMonths(2)->format('F Y');
        $propertiesOverviewCards = [];
        $propertiesOverviewCards[] = [
            'title'=>__('Total Properties'),
            'value'=>$total = count($allProperties),
            'color_class'=>'primary',
        ];
		$occupiedProperties = $allProperties->filter(function (Property $property) {
                return $property->isOccupied();
            });
			
		// $occupiedPropertiesRows = ;
		
        $propertiesOverviewCards[] = [
            'title'=>__('Total Occupied Properties'),
            'value'=>$totalOccupied = $occupiedProperties->count(),
            'color_class'=>'success',
            'percentage'=>$total ? $totalOccupied/$total*100 : 0,
			'details_model'=>[
				'id'=>'occupied-details',
				'title'=>__('Occupied Properties Details'),
				'headers'=>[
					'name'=>[
						'title'=>__('Name'),
						'classes'=>''
					],
					'type'=>[
						'title'=>__('Type'),
						'classes'=>'max-w-200'
					]	,
					'tenant-name'=>[
						'title'=>__('Tenant Name'),
						'classes'=>''
					],
					'value'=>[
						'title'=>__('Rent Amount'),
						'classes'=>''
					]
				],
				'rows'=>$occupiedProperties->map(function(Property $occupiedProperty){
					return [
						[
							'td_classes'=>'w-20-percentage',
							'input-classes'=>'text-left',
							'value'=>$occupiedProperty->getName()
						],
						[
							'td_classes'=>'w-20-percentage',
							'input-classes'=>'text-left min-w-200',
							'value'=>$occupiedProperty->getTypeName()
						],
						[
							'td_classes'=>'text-center w-30-percentage',
							'input-classes'=>'text-center',
							'value'=>$occupiedProperty->getTenantName()
						],
						[
							'td_classes'=>'text-center w-10-percentage',
							'input-classes'=>'text-center',
							'value'=>$occupiedProperty->getContractMonthlyRentFormatted()
						]
					];
				}),
				
			]
        ];
		$vacantProperties = $allProperties->filter(function (Property $property) {
                return $property->isVacantOrVacantWithInstallments();
            });
		
        $propertiesOverviewCards[] = [
            'title'=>__('Total Vacant Properties'),
            'value'=>$totalVacant = $vacantProperties->count(),
            'percentage'=>$total ? $totalVacant/$total*100 : 0,
            'color_class'=>'danger',
			'details_model'=>[
				'id'=>'vacant-details',
				'title'=>__('Vacant Properties Details'),
				'headers'=>[
					'name'=>[
						'title'=>__('Name'),
						'classes'=>''
					],
					'type'=>[
						'title'=>__('Type'),
						'classes'=>''
					]	,
					
					'vacant-date'=>[
						'title'=>__('Vacant Date'),
						'classes'=>''
					]
				],
				'rows'=>$vacantProperties->map(function(Property $vacantProperty){
					;return [
						[
							'td_classes'=>'w-30-percentage',
							'input-classes'=>'text-left',
							'value'=>$vacantProperty->getName()
						],
						[
							'td_classes'=>'w-30-percentage',
							'input-classes'=>'text-left',
							'value'=>$vacantProperty->getTypeName()
						],
						
						[
							'td_classes'=>'w-30-percentage text-left',
							'input-classes'=>'text-left',
							'value'=>$vacantProperty->getVacantDate()
						]
					];
				})
			]
			
        ];
		$notDeliveredProperties = $allProperties->filter(function (Property $property) {
                return $property->isToBeDelivered();
            });
			
			
			// $notDeliveredPropertiesRows = ;
		
        $propertiesOverviewCards[] = [
            'title'=>__('Total Not Delivered Properties'),
            'value'=>$totalNotDelivered = $notDeliveredProperties->count(),
            'percentage'=>$total ? $totalNotDelivered/$total*100 : 0,
            'color_class'=>'primary',
			'details_model'=>[
				'id'=>'not-delivered-details',
				'title'=>__('Not Delivered Properties Details'),
				'headers'=>[
					'name'=>[
						'title'=>__('Name'),
						'classes'=>''
					],
					'type'=>[
						'title'=>__('Type'),
						'classes'=>''
					]	,
					
					'delivery-date'=>[
						'title'=>'Delivery Date',
						'classes'=>''
					]
				],
				'rows'=>$notDeliveredProperties->map(function(Property $notDeliveredProperty){
					return [
						[
							'td_classes'=>'w-30-percentage',
							'input-classes'=>'text-left',
							'value'=>$notDeliveredProperty->getName()
						],
						[
							'td_classes'=>'w-30-percentage',
							'input-classes'=>'text-left',
							'value'=>$notDeliveredProperty->getTypeName()
						],
						
						[
							'td_classes'=>'w-30-percentage text-left',
							'input-classes'=>'text-left',
							'value'=>$notDeliveredProperty->getInstallmentDeliveryDate()
						]
					];
				})
			]
			
        
        ];
        // $dueInstallmentsFormatted = [];
    //    $rentAndCollectionOverviewCards = [];

        $currentRunningContractMonthRentAndCollectionsPerType = [];
        // $currentRunningContractMonthCollections = 0;
        // $nextMonthContractMonthRent = 0;
        // $nextMonthContractMonthCollections = 0;
        // $twoMonthsLaterContractMonthRent = 0;
        // $twoMonthsLaterContractMonthCollections = 0;
        // $nextMonthDate = Carbon::make($dashboardDate)->addMonth()->format('Y-m-d');
		// $twoMonthsLaterDate = Carbon::make($dashboardDate)->addMonths(2)->format('Y-m-d');
		$totalCollections = [];
		$totalDueInstallments = [];
        // $twoMonthsLater = Carbon::make($dashboardDate)->addMonths(2)->format('Y-m-d');
        $allProperties->each(function (Property $property) use ($durationToShow,$durationToStore,&$totalDueInstallments,&$totalCollections, &$currentRunningContractMonthRentAndCollectionsPerType, $dashboardDate) {
            $dueInstallment = $property->dueInstallment;
			$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['name'] = $property->type ? $property->type->getName() : '-';
			for($i=0;$i< $durationToStore;$i++){
				$currentDate = Carbon::make($dashboardDate)->addMonths($i)->format('Y-m-d');
				$currentTotalDueInstallment = $dueInstallment ? (getValueAtMonthAndYear($dueInstallment->total_due_installments, $currentDate)) : 0;
				$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments'][$i] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments'][$i]) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments'][$i] + $currentTotalDueInstallment : $currentTotalDueInstallment;
				if($i< $durationToShow){
					$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['total'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['total']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['total'] + $currentTotalDueInstallment : $currentTotalDueInstallment;
				}
				// if ($currentTotalDueInstallment>0) {
				// }
				$totalDueInstallments[$currentDate] = isset($totalDueInstallments[$currentDate]) ? $totalDueInstallments[$currentDate] + $currentTotalDueInstallment : $currentTotalDueInstallment;
			}
            // $totalDueInstallmentNextMonth = $dueInstallment ? (getValueAtMonthAndYear($dueInstallment->total_due_installments, $nextMonthDate)) : 0;
            // if ($totalDueInstallmentNextMonth>0) {
            //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['next_month'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['next_month']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['next_month'] + $totalDueInstallmentNextMonth : $totalDueInstallmentNextMonth;
			// 	$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['total'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['total']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['total'] + $totalDueInstallmentNextMonth : $totalDueInstallmentNextMonth;
			// 	$totalDueInstallments[$nextMonthDate] = isset($totalDueInstallments[$nextMonthDate]) ? $totalDueInstallments[$nextMonthDate] + $totalDueInstallmentNextMonth : $totalDueInstallmentNextMonth;
            // }
			// $totalDueInstallmentTwoMonthsLater = $dueInstallment ? (getValueAtMonthAndYear($dueInstallment->total_due_installments, $twoMonthsLaterDate)) : 0;
			// if ($totalDueInstallmentTwoMonthsLater>0) {
			// 	$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['two_months_later'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['two_months_later']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['two_months_later'] + $totalDueInstallmentTwoMonthsLater : $totalDueInstallmentTwoMonthsLater;
			// 	$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['total'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['total']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['due_installments']['total'] + $totalDueInstallmentTwoMonthsLater : $totalDueInstallmentTwoMonthsLater;
			// 	$totalDueInstallments[$twoMonthsLaterDate] = isset($totalDueInstallments[$twoMonthsLaterDate]) ? $totalDueInstallments[$twoMonthsLaterDate] + $totalDueInstallmentTwoMonthsLater : $totalDueInstallmentTwoMonthsLater;
			// }
            $property->contracts->each(function (Contract $contract) use ($durationToShow,$durationToStore,$property, &$totalCollections, &$currentRunningContractMonthRentAndCollectionsPerType, $dashboardDate) {
				
				for($i=0;$i< $durationToStore;$i++){
					
					$currentDate = Carbon::make($dashboardDate)->addMonths($i)->format('Y-m-d');
					if ($contract->isRunningAt($currentDate)) {
						$currentRent=getValueAtMonthAndYear($contract->rent_revenues?:[], $currentDate);
						 $currentCollection=getValueAtMonthAndYear($contract->rent_collections?:[], $currentDate);
						$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['name'] = $property->type ? $property->type->getName() : '-';
						$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues'][$i] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues'][$i]) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues'][$i] + $currentRent : $currentRent;
						$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections'][$i] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections'][$i]) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections'][$i] + $currentCollection : $currentCollection;
						if($i< $durationToShow){
							$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['total'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['total']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['total'] + $currentRent : $currentRent;
							$currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['total'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['total']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['total'] + $currentCollection : $currentCollection;
						}
						$totalCollections[$currentDate] = isset($totalCollections[$currentDate]) ? $totalCollections[$currentDate] + $currentCollection : $currentCollection;
					}
					
				}
               
                // if ($contract->isRunningAt($nextMonthDate)) {
                //     $nextMonthContractMonthRent += $currentRent=getValueAtMonthAndYear($contract->rent_revenues?:[], Carbon::make($dashboardDate)->addMonth()->format('Y-m-d'));
                //     $nextMonthContractMonthCollections += $currentCollection=getValueAtMonthAndYear($contract->rent_collections?:[], Carbon::make($dashboardDate)->addMonth()->format('Y-m-d'));
                //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['name'] = $property->type ? $property->type->getName() : '-';
                //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['next_month'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['next_month']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['next_month'] + $currentRent : $currentRent;
                //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['total'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['total']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['total'] + $currentRent : $currentRent;
                //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['next_month'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['next_month']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['next_month'] + $currentCollection : $currentCollection;
                //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['total'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['total']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['total'] + $currentCollection : $currentCollection;
				// 	$totalCollections[$nextMonthDate] = isset($totalCollections[$nextMonthDate]) ? $totalCollections[$nextMonthDate] + $currentCollection : $currentCollection;
                // }
                // if ($contract->isRunningAt($twoMonthsLaterDate)) {
                //     $twoMonthsLaterContractMonthRent += $currentRent=getValueAtMonthAndYear($contract->rent_revenues?:[], Carbon::make($dashboardDate)->addMonths(2)->format('Y-m-d'));
                //     $twoMonthsLaterContractMonthCollections += $currentCollection=getValueAtMonthAndYear($contract->rent_collections?:[], Carbon::make($dashboardDate)->addMonths(2)->format('Y-m-d'));
                //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['name'] = $property->type ? $property->type->getName() : '-';
                //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['two_months_later'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['two_months_later']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['two_months_later'] + $currentRent : $currentRent;
                //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['total'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['total']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['rent_revenues']['total'] + $currentRent : $currentRent;
                //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['two_months_later'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['two_months_later']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['two_months_later'] + $currentCollection : $currentCollection;
                //     $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['total'] = isset($currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['total']) ? $currentRunningContractMonthRentAndCollectionsPerType[$property->type_id]['collections']['total'] + $currentCollection : $currentCollection;
				// 	$totalCollections[$twoMonthsLaterDate] = isset($totalCollections[$twoMonthsLaterDate]) ? $totalCollections[$twoMonthsLaterDate] + $currentCollection : $currentCollection;
                // }
                
            });
        });
        // $rentAndCollectionOverviewCards[] = [
        //     'title'=>__('Current Month Rent'),
        //     'value'=>$currentRunningContractMonthRent,
        //     'color_class'=>'primary',
        // ];
        // $rentAndCollectionOverviewCards[] = [
        //     'title'=>__('Current Month Collections'),
        //     'value'=>$currentRunningContractMonthCollections,
        //     'color_class'=>'success',
        // ];
        
        // $rentAndCollectionOverviewCards[] = [
        //     'title'=>__('Next Month Rent'),
        //     'value'=>$nextMonthContractMonthRent,
        //     'color_class'=>'primary',
        // ];
        // $rentAndCollectionOverviewCards[] = [
        //     'title'=>__('Next Month Collections'),
        //     'value'=>$nextMonthContractMonthCollections,
        //     'color_class'=>'success',
        // ];
        
        // $rentAndCollectionOverviewCards[] = [
        //     'title'=>__('Two Months Later Rent'),
        //     'value'=>$twoMonthsLaterContractMonthRent,
        //     'color_class'=>'primary',
        // ];
        
        // $rentAndCollectionOverviewCards[] = [
        //     'title'=>__('Two Months Later Collections'),
        //     'value'=>$twoMonthsLaterContractMonthCollections,
        //     'color_class'=>'success',
        // ];
        $propertyTypes = Property::getTypesFormatted($company);

        // Pie chart: rent revenues per type (name + total)
        $rentRevenuesPerTypePieData = [];
        $rentCollectionsPerTypePieData = [];
        foreach ($currentRunningContractMonthRentAndCollectionsPerType as $typeId => $data) {
            $rentRevenuesPerTypePieData[] = [
                'name' => $data['name'] ?? __('Type'),
                'value' => (float) ($data['rent_revenues']['total'] ?? 0),
            ];
            $rentCollectionsPerTypePieData[] = [
                'name' => $data['name'] ?? __('Type'),
                'value' => (float) ($data['collections']['total'] ?? 0),
            ];
            $dueInstallmentsPerTypePieData[] = [
                'name' => $data['name'],
                'value' => (float) ($data['due_installments']['total'] ?? 0),
            ];
        }
		$dates = [];
		
		$dates = HDate::generateDatesBetweenStartDateAndDuration(0, $dashboardDate, $durationToStore-1, 'monthly');
		DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('property_dashboard')->updateOrInsert([
			'company_id'=>$company->id,
		], [
			'company_id'=>$company->id,
			'total_due_installments'=>json_encode($totalDueInstallments),
			'total_collections'=>json_encode($totalCollections),
			'dates'=>json_encode($dates),
		]);
        return view('property_managements.dashboard.property_dashboard', [
            'propertiesOverviewCards'=>$propertiesOverviewCards,
            // 'rentAndCollectionOverviewCards'=>$rentAndCollectionOverviewCards,
            'propertyTypes'=>$propertyTypes,
			'durationToShow'=>$durationToShow,
			// 'dates'=>$dates,
            'propertiesGroupedByType'=>$propertiesGroupedByType,
            'occupiedPropertiesGroupedByType'=>$occupiedPropertiesGroupedByType,
            'vacantPropertiesGroupedByType'=>$vacantPropertiesGroupedByType,
            'notDeliveredPropertiesGroupedByType'=>$notDeliveredPropertiesGroupedByType,
            'currentRunningContractMonthRentAndCollectionsPerType'=>$currentRunningContractMonthRentAndCollectionsPerType,
            'rentRevenuesPerTypePieData'=>$rentRevenuesPerTypePieData,
			'dueInstallmentsPerTypePieData'=>$dueInstallmentsPerTypePieData,
            'rentCollectionsPerTypePieData'=>$rentCollectionsPerTypePieData,
            'currentMonth'=>$currentMonth,
            'nextMonth'=>$nextMonth,
            'twoMonthsLater'=>$twoMonthsLater,
			// 'dueInstallmentsFormatted'=>$dueInstallmentsFormatted,
			'dashboardDate'=>$dashboardDate,
     
            'company' => $company,
          
            
        ]);
    }
    public function viewPropertyCashflowForecastDashboard(Company $company, Request $request)
    {
		return view('property_managements.dashboard.property_cashflow_dashboard', [
			'company' => $company,
			'title'=>__('Property Cashflow Forecast Dashboard'),
			
		]);
    }
	public function getCashflowForecastDashboardOldData(Company $company, Request $request)
	{
		// $study = Study::find($request->get('study_id'));
		$propertyDashboard = DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('property_dashboard')->where('company_id', $company->id)->first();
		$totalDueInstallments = json_decode($propertyDashboard->total_due_installments, true);
		$totalCollections = json_decode($propertyDashboard->total_collections, true);
		// $dates  = json_decode($propertyDashboard->dates, true);
		
		$dashboardDate = $propertyDashboard->dashboard_date??now()->format('Y-m-d');
		$dates = HDate::generateDatesBetweenStartDateAndDuration(0, $dashboardDate, 5, 'monthly');
		
		$totalCashIn = [];
		$totalCashOut = [];
		foreach($dates as $dateAsIndex=>$date){
			$totalCollections[$dateAsIndex] = $totalCollections[$date] ?? 0 ;
			$currentCollectionAmount = $totalCollections[$date] ?? 0 ;
			$totalCashIn[$dateAsIndex] = isset($totalCollections[$date]) ?  + $currentCollectionAmount : $currentCollectionAmount;
			$values[$dateAsIndex] = 0;
			
			
			$totalDueInstallments[$dateAsIndex] = $totalDueInstallments[$date] ?? 0 ;
			$currentDueInstallmentAmount = $totalDueInstallments[$date] ?? 0 ;
			$totalCashOut[$dateAsIndex] = isset($totalDueInstallments[$date]) ?  + $currentDueInstallmentAmount : $currentDueInstallmentAmount;
			
			
		}
		$cashInSubItems = json_decode($propertyDashboard->cash_in,true)['sub_items']??[];
		$totalCashInSubItems = [];
		foreach(count($cashInSubItems) ? $cashInSubItems : [['name'=>'', 'values'=>$values]] as $cashInSubItem) {
			$totalCashInSubItems[] = [
				'name'=>$cashInSubItem['name'],
				'values'=>$cashInSubItem['values'],
			];
		}
		
		$cashOutSubItems = json_decode($propertyDashboard->cash_out,true)['sub_items']??[];
		$totalCashOutSubItems = [];
		foreach(count($cashOutSubItems) ? $cashOutSubItems : [['name'=>'', 'values'=>$values]] as $cashOutSubItem) {
			$totalCashOutSubItems[] = [
				'name'=>$cashOutSubItem['name'],
				'values'=>$cashOutSubItem['values'],
			];
		}
		return response()->json([
			'submitUrl'=>route('property.management.dashboard.cashflow-forecast.submit', ['company'=>$company->id]),
			'dates'=>$dates,
			'model'=>[
				'dashboard_date'=>$dashboardDate,
				'cash_in'=>[
					'total_cash_in'=> $totalCashIn,
					'total_collections'=>$totalCollections,
					'sub_items'=>$totalCashInSubItems
				],
				'cash_out'=>[
					'total_cash_out'=> $totalCashOut,
					'total_due_installments'=>$totalDueInstallments,
					'sub_items'=>$totalCashOutSubItems
				]
			]
		]);
	}
	public function submitCashflowForecast(Company $company, Request $request)
	{
		DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('property_dashboard')->where('company_id', $company->id)->update([
			'cash_in'=>json_encode($request->get('cash_in',[])),
			'cash_out'=>json_encode($request->get('cash_out',[])),
			'dashboard_date'=>$request->get('dashboard_date'),
		]);
		return response()->json([
			'success'=>true,
			'redirectTo'=>route('property.management.view.property.cashflow.forecast.dashboard', ['company'=>$company->id]),
		]);
	}
    protected function formatForTheeLineChart(array $items)
    {
        $lineChart = [];
        $barChart = [];
        foreach ($items as $key => $arrayItems) {
            $previous = 0 ;
            foreach ($arrayItems as $year => $value) {
                $currentGrowthRate = $previous ? (($value / $previous)-1)*100 : 0   ;
                $previous = $value ;
                $lineChart[$key][] = [
                    'date'=> $year.'-01-01' ,
                    'revenue_value'=>number_format($value/1000000, 2) ,
                    'growth_rate'=>number_format($currentGrowthRate, 2)
                ];
                if ($key != 'all') {
                    $value = $value / 1000000;
                    $barChart[$year][$key] =  isset($barChart[$year][$key]) ? $barChart[$year][$key] + $value : $value;
                    $barChart[$year]['year'] = strval($year);
                }
            }
        }

        $barChart = array_values($barChart);
        
        return [
            'line_chart'=>$lineChart,
            'bar_chart'=>$barChart
        ] ;
    }
    protected function generateDashboardData(Study $study, Company $company, bool $isSensitivity = false):array
    {
    
        $dateIndexWithDate = app('dateIndexWithDate');
        $formattedExpenses = [];
        $formattedResult = [];
        $yearWithItsIndexes = $study->getOperationDurationPerYearFromIndexes();
        // $titlesMapping = $study->getProjectionTitles();
        
        $incomeStatement = (new IncomeStatementController())->index($company, $study, true);
        $resultPerRevenueStreamType = $incomeStatement['resultPerRevenueStreamType']??[];
        
        $chartsFormatted =$this->formatForTheeLineChart($resultPerRevenueStreamType);
        $lineChart = $chartsFormatted['line_chart'];
        $barChart = $chartsFormatted['bar_chart'];
        $incomeStatement = $incomeStatement['tableDataFormatted'];
        $salesRevenueMainItems = $incomeStatement[0]['main_items']??[];
        $costOfServices = $incomeStatement[1]??[];
        $grossProfits = $incomeStatement[2]['main_items']??[];
        $ebitda = $incomeStatement[7]??[];
        $ebit = $incomeStatement[9]??[];
        $ebt = $incomeStatement[11]??[];
        $netProfit = $incomeStatement[13]??[];
        $key =  'data';
        $yearWithItsMonths=$study->getYearIndexWithItsMonths();
        $salesRevenuesPerYears = HArr::sumPerYearIndex($salesRevenueMainItems['sales-revenue'][$key], $yearWithItsMonths);
        $formattedResult['sales_revenue'] = array_values($salesRevenueMainItems['sales-revenue'][$key]??[]);
        $formattedResult['growth_rate'] = array_values($salesRevenueMainItems['growth-rate'][$key]??[]);
        $formattedResult['sales_revenue_growth_rate_per_years'] =HArr::calculateGrowthRate($salesRevenuesPerYears);
        $formattedResult['interest_cogs'] = array_values($costOfServices['sub_items']['Interest Cost'][$key]??[]);
        $growthProfit = $grossProfits['gross-profit'][$key]??[];
        $formattedResult['gross_profit'] = array_values($growthProfit);
        $growthProfitPerYears = HArr::sumPerYearIndex($growthProfit, $yearWithItsMonths);

        $formattedResult['percentage_of_revenues_per_years']['gross_profit'] =HArr::calculatePercentageOf($salesRevenuesPerYears, $growthProfitPerYears);
            
        $formattedResult['gross_profit_percentage_of_sales'] = array_values($grossProfits['% Of Revenue'][$key]??[]);
        $ebitdaMonthly = $ebitda['main_items']['ebitda'][$key]??[];
        $formattedResult['ebitda'] = array_values($ebitdaMonthly);
        $ebitdaPerYears = HArr::sumPerYearIndex($ebitdaMonthly, $yearWithItsMonths);
        $formattedResult['percentage_of_revenues_per_years']['ebitda'] =HArr::calculatePercentageOf($salesRevenuesPerYears, $ebitdaPerYears);

                
        $formattedResult['ebitda_percentage_of_sales'] = array_values($ebitda['main_items']['% Of Revenue'][$key]??[]);
        $ebitMonthly = $ebit['main_items']['ebit'][$key]??[];
        $formattedResult['ebit'] = array_values($ebitMonthly);
        $ebitPerYears = HArr::sumPerYearIndex($ebitMonthly, $yearWithItsMonths);
        $formattedResult['percentage_of_revenues_per_years']['ebit'] =HArr::calculatePercentageOf($salesRevenuesPerYears, $ebitPerYears);
        

        
        $formattedResult['ebit_percentage_of_sales'] = array_values($ebit['main_items']['% Of Revenue'][$key]??[]);
        $ebtMonthly = $ebt['main_items']['ebt'][$key]??[];
        $formattedResult['ebt'] = array_values($ebtMonthly);
            
        $ebtPerYears = HArr::sumPerYearIndex($ebtMonthly, $yearWithItsMonths);
        $formattedResult['percentage_of_revenues_per_years']['ebt'] =HArr::calculatePercentageOf($salesRevenuesPerYears, $ebtPerYears);
        
        
        $formattedResult['ebt_percentage_of_sales'] = array_values($ebt['main_items']['% Of Revenue'][$key]??[]);
        $netProfitMonthly = $netProfit['main_items']['net-profit'][$key]??[];
        $formattedResult['net_profit'] = array_values($netProfitMonthly);
        $netProfitPerYears = HArr::sumPerYearIndex($netProfitMonthly, $yearWithItsMonths);
        $formattedResult['percentage_of_revenues_per_years']['net_profit'] =HArr::calculatePercentageOf($salesRevenuesPerYears, $netProfitPerYears);
        
        
        $formattedResult['net_profit_percentage_of_sales'] = array_values($netProfit['main_items']['% Of Revenue'][$key]??[]);
        $expenseOrderIds = [
            1 , 3 , 4 , 5 ,6
        ];
        
        foreach ($expenseOrderIds as $expenseOrderId) {
            $expenseItem = $incomeStatement[$expenseOrderId];
            $mainItemKeyId = array_keys($expenseItem['main_items'])[0];
            $subItems = $expenseItem['sub_items']??[];
            foreach ($subItems as $subItemName => $subItemData) {
                $formattedExpenses[$mainItemKeyId][$subItemName] = array_values($subItemData[$key]??[]);
            }
            if (count($subItems)) {
                $formattedExpenses[$mainItemKeyId]['total'] = array_values($expenseItem['main_items'][$mainItemKeyId][$key]??[]);
            }
            
            
        }
        foreach ($formattedExpenses as $costType=> $expenseArr) {
            $currentMonthlyValues = $expenseArr['total']??[];
            $currentYearlyValues = HArr::sumPerYearIndex($currentMonthlyValues, $yearWithItsMonths);
            $formattedResult['percentage_of_revenues_per_years'][$costType] =HArr::calculatePercentageOf($salesRevenuesPerYears, $currentYearlyValues);
        
        }
        return [
            // 'titlesMapping'=>$titlesMapping,
            'lineChart'=>$lineChart ,
            'barChart'=>$barChart ,
            'formattedResult'=>$formattedResult ,
            'formattedExpenses'=>$formattedExpenses,
            'yearWithItsIndexes'=>$yearWithItsIndexes,
            'dateIndexWithDate'=>$dateIndexWithDate
        ];
        
    }
    public function view(Request $request, Company $company, Study $study)
    {
        
        $withSensitivity = $request->routeIs('property.management.view.results.dashboard.with.sensitivity') ;
        // if($study->duration_in_years>=2){
        // 	$study->force_yearly = true;
        // }
        $dashboardData = $this->generateDashboardData($study, $company, false);
        
        
        
        $formattedResult = $dashboardData['formattedResult'];
    
        $formattedExpenses =$dashboardData['formattedExpenses'];
        $lineChart =$dashboardData['lineChart'];
        // $titlesMapping =$dashboardData['titlesMapping'];
        $barChart =$dashboardData['barChart'];
        $yearWithItsIndexes = $dashboardData['yearWithItsIndexes'];
        $dateIndexWithDate = $dashboardData['dateIndexWithDate'];
        $sensitivityFormattedResult = [];
        $sensitivityFormattedExpenses=[];
        if ($withSensitivity) {
            $sensitivityDashboardData = $this->generateDashboardData($study, $company, true);
            $sensitivityFormattedResult = $sensitivityDashboardData['formattedResult'];
            $sensitivityFormattedExpenses = $sensitivityDashboardData['formattedExpenses'];
        }
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
    
        $isYearsStudy = !$study->isMonthlyStudy();
        return view(
            'property_managements.dashboard.dashboard',
            [
        'dateIndexWithDate'=>$dateIndexWithDate,
        'yearsWithItsMonths' => $study->getOperationDurationPerYearFromIndexes(),
        'model'=>$study,
        'study'=>$study,
        'formattedResult'=>$formattedResult,
        'formattedExpenses'=>$formattedExpenses,
        // 'titlesMapping'=>$titlesMapping,
        'lineChart'=>$lineChart,
        'barChart'=>$barChart,
        'yearWithItsIndexes'=>$yearWithItsIndexes,
        'sensitivityFormattedResult'=>$sensitivityFormattedResult,
        'sensitivityFormattedExpenses'=>$sensitivityFormattedExpenses,
        'withSensitivity'=>$withSensitivity,
        'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
        'isYearsStudy'=>$isYearsStudy
    ]
        );
    }
}
