<?php

namespace App\Models\Repositories;
use Illuminate\Database\Eloquent\Collection;
use App\Interfaces\Repositories\IBaseRepository;
use App\Models\Currency;


class CurrencyRepository implements IBaseRepository 
{

    public function all():Collection
    {
        return Currency::get();
    }
    public function allFormattedForSelect()
    {
        $currencys = $this->all();
        return formatOptionsForSelect($currencys , 'getId' , 'getName');
    }
     public function oneFormattedForSelect($model)
    {
        $currencys = Currency::where('id',$model->getPositionId())->get();
        return formatOptionsForSelect($currencys , 'getId' , 'getName');
    }
    
   






}
