<?php

namespace App\Models\Repositories;

use App\Interfaces\Repositories\IBaseRepository;
use App\Models\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PositionRepository implements IBaseRepository 
{
    public function all():Collection
    {
        return Position::where('company_id',getCurrentCompanyId())->get();
    }

    public function allFormattedForSelect($type)
    {
        $positions = $this->all();
		$positions = $positions->where('position_type',$type);
        return formatOptionsForSelect($positions , 'getId' , 'getName');
    }
	
   

  





    


}
