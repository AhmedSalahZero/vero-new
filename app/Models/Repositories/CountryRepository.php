<?php

namespace App\Models\Repositories;


use App\Interfaces\Repositories\IBaseRepository;
use App\Models\Country;


class CountryRepository implements IBaseRepository 
{
  



    public function find($id):?Country
    {
        return Country::find($id);
    }







}
