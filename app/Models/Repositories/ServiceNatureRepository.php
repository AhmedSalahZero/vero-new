<?php

namespace App\Models\Repositories;

use App\Interfaces\Repositories\IBaseRepository;
use App\Models\ServiceNature;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ServiceNatureRepository implements IBaseRepository 
{
    public function all():Collection
    {
        return ServiceNature::get();
    }

    public function allFormattedForSelect()
    {
        $serviceNatures = $this->all();
        return formatOptionsForSelect($serviceNatures , 'getId' , 'getName');
    }
      






}
