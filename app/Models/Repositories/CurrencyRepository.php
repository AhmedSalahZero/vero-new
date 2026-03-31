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
        return formatOptionsForSelect($this->all() , 'getId' , 'getName');
    }
   
    
   






}
