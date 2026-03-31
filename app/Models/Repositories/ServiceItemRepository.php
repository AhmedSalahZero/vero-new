<?php

namespace App\Models\Repositories;

use App\Interfaces\Repositories\IBaseRepository;
use App\Models\ServiceItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ServiceItemRepository implements IBaseRepository 
{
	public function all():Collection
    {
        return ServiceItem::onlyCurrentCompany()->get();
    }

    public function allFormatted():array
    {
        return ServiceItem::onlyCurrentCompany()->get()->pluck('name_'.App()->getLocale(),'id')->toArray();
    }
	public function allFormattedForSelect()
    {
        $serviceItems = $this->all();
        return formatOptionsForSelect($serviceItems , 'getId' , 'getName');
    }
    public function store(Request $request )
    {
        
        return ServiceItem::create([
            'name'=>$request->get('service_item_name'),
            'service_category_id'=>$request->get('service_category_id'),
            // 'revenue_business_line_id'=>$request->get('revenue_business_line_id')
        ]);
    }




 




}
