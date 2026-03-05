<?php

namespace App\Models\Repositories;

use App\Interfaces\Repositories\IBaseRepository;
use App\Models\QuotationPricingCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class QuotationPricingCalculatorRepository  
{
    
     public function store(Request $request )
    {
        $quotationPricingCalculator = App(QuotationPricingCalculator::class);
         $quotationPricingCalculator
         ->storeOfferedServiceSectionWithResult($request)
         ->storeServices($request)
         ->storeDirectManpowerExpense($request)
         ->storeFreelancersExpense($request)
         ->storeOtherVariableManpowerExpense($request)
         ->storeOtherDirectOperationsExpense($request)
         ->storeSalesAndMarketingExpense($request)
         ->storeGeneralExpense($request)
         ->storeProfitability($request);
         
         return $quotationPricingCalculator ; 
    }
    
    public function update(  $quotationPricingCalculator , Request $request ):void
    {
        $quotationPricingCalculator
        ->updateOfferedServiceSectionWithResult($request)
        ->updateDirectManpowerExpense($request)
        ->updateFreelancersExpense($request)
        ->updateOtherVariableManpowerExpense($request)
        ->updateOtherDirectOperationsExpense($request)
        ->updateSalesAndMarketingExpense($request)
        ->updateGeneralExpense($request)
        ->updateProfitability($request);
    }

    public function paginate(Request $request):array
    {

        $filterData = $this->commonScope($request);

        $allFilterDataCounter = $filterData->count();

        $datePerPage = $filterData->skip(Request('start'))->take(Request('length'))->get()->each(function(QuotationPricingCalculator $quotationPricingCalculator , $index){
            $quotationPricingCalculator->revenueBusinessLineName = $quotationPricingCalculator->getRevenueBusinessLineName();
            $quotationPricingCalculator->serviceCategoryName = $quotationPricingCalculator->getServiceCategoryName();
            $quotationPricingCalculator->serviceItemName = $quotationPricingCalculator->getServiceItemName();
            $quotationPricingCalculator->totalRecommendPriceWithoutVatFormatted = $quotationPricingCalculator->getTotalRecommendPriceWithoutVatFormatted();
            $quotationPricingCalculator->totalRecommendPriceWithVatFormatted = $quotationPricingCalculator->getTotalRecommendPriceWithVatFormatted();
            $quotationPricingCalculator->totalNetProfitAfterTaxesFormatted = $quotationPricingCalculator->getTotalNetProfitAfterTaxesFormatted();
            $quotationPricingCalculator->creator_name = $quotationPricingCalculator->getCreatorName();
            $quotationPricingCalculator->created_at_formatted = formatDateFromString($quotationPricingCalculator->created_at);
            $quotationPricingCalculator->updated_at_formatted = formatDateFromString($quotationPricingCalculator->updated_at);
            // $quotationPricingCalculator->serviceCategories = $quotationPricingCalculator->serviceCategories->load('serviceItems'); 
            $quotationPricingCalculator->order = $index+1 ;

        }) ;
        return [
            'data'=>$datePerPage ,
            "draw"=> (int)Request('draw'),
            "recordsTotal"=> QuotationPricingCalculator::onlyCurrentCompany()->count(),
            "recordsFiltered"=>$allFilterDataCounter,
        ] ;

    }

    public function commonScope(Request $request):builder
    {
        return QuotationPricingCalculator::onlyCurrentCompany()->when($request->filled('search_input') , function(Builder $builder) use ($request){

            $builder
            ->where(function(Builder $builder) use ($request){
                $builder->when($request->filled('search_input'),function(Builder $builder) use ($request){
                    $keyword = "%".$request->get('search_input')."%";
                    $builder
                    ->whereHas('revenueBusinessLine',function(Builder $builder) use ($keyword){
                        $builder->where('revenue_business_lines.name','like',$keyword);
                    })
                    ->orWhereHas('serviceCategory',function(Builder $builder) use ($keyword){
                        $builder->where('service_categories.name','like',$keyword);
                    })
                    ->orWhereHas('serviceCategory',function(Builder $builder) use ($keyword){
                        $builder->where('service_categories.name','like',$keyword);
                    })
                    ->orWhereHas('serviceItem',function(Builder $builder) use ($keyword){
                        $builder->where('service_items.name','like',$keyword);
                    })
                    // ->where('name' , 'like' , $keyword)
                    ->orWhereHas('creator',function(Builder $builder) use($keyword) {
                        $builder->where('name','like',$keyword);
                    })->orWhereHas('company',function(Builder $builder) use($keyword) {
                        $builder->where('name','like',$keyword);
                    })
                    ;
                    
                })
                ;
                
            });
        })
        ->when($request->filled('revenue_business_line_id') && $request->get('revenue_business_line_id') != 'All' , function(Builder $builder) use ($request){
                    // $builder->whereHas('revenueBusinessLine',function(Builder $builder) use ($request){
                        $builder->where('revenue_business_line_id',$request->get('revenue_business_line_id'));
                    // });
                })

                  ->when($request->filled('service_category_id') && $request->get('service_category_id') != 'All' , function(Builder $builder) use ($request){
                    // $builder->whereHas('serviceCategory',function(Builder $builder) use ($request){
                        $builder->where('service_category_id',$request->get('service_category_id'));
                    // });
                })
                ->when($request->filled('service_item_id') && $request->get('service_item_id') != 'All' , function(Builder $builder) use ($request){
                    // $builder->whereHas('serviceItems',function(Builder $builder) use ($request){
                        $builder->where('service_item_id',$request->get('service_item_id'));
                    // });
                })
        
        ->orderBy('quotation_pricing_calculators.'.getDefaultOrderBy()['column'],getDefaultOrderBy()['direction']) ;

    }

    // public function export(Request $request):Collection
    // {
    //     return $this->commonScope(
    //         $request->replace(
    //             array_merge($request->all(),[
    //                 'format'=>$request->get('format'),
    //             ]  )
    //         ))
    //         ->select(['quotation_pricing_calculators.id','revenue_business_line_id','service_category_id','service_item_id','quotation_pricing_calculators.created_at as join_at','delivery_days','total_recommend_price_without_vat','total_recommend_price_with_vat','total_net_profit_after_taxes'])
    //         ->get()->each(function($quotationPricingCalculator){
    //         });
    // }






}
