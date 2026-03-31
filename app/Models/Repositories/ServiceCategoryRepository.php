<?php

namespace App\Models\Repositories;

use App\Interfaces\Repositories\IBaseRepository;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ServiceCategoryRepository implements IBaseRepository 
{
    public function all():Collection
    {
        return ServiceCategory::onlyCurrentCompany()->get();
    }

   
    public function allFormattedForSelect()
    {
        $serviceCategories = $this->all();
        return formatOptionsForSelect($serviceCategories , 'getId' , 'getName');
    }
   
  

    public function store(Request $request )
    {
        return ServiceCategory::create([
        'revenue_business_line_id'=>$request->get('revenue_business_line_id'),
        'name'=>$request->get('service_category_name')
        ]);
    }





  



}
