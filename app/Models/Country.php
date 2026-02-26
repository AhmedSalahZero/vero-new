<?php

namespace App\Models;

use App\Interfaces\Models\IBaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model implements IBaseModel
{

     public function getRouteKeyName()
    {
        return 'countries.id' ;
    }

    
    public function getName(): string
    {
        $locale = App()->getLocale();
        $nameKey = 'name_' . $locale;
        
        // Return localized name or fallback to English
        return $this[$nameKey] ?? $this['name_en'] ?? '';
    }
    public function getUpdateRoute():string
    {
       return 'test';
    //    return route('admin.customers.update' , $this->id);

    }

    public static function getStoreRoute():string
    {
       return 'test';
    //    return route('admin.customers.store' , );

    }

    public static function getViewVariables(): array
    {

        return [
            // 'customerGroups'=>$customerGroupsRepo->allFormatted() ,
            // 'modelName'=>'Customer' ,
            // 'exportRoute'=>route('admin.customers.export'),
            // 'importRoute'=>route('admin.customers.import'),
            // 'downloadImportFileRoute'=>route('admin.customers.download.import.file'),
            // 'companies'=>App(CompanyRepository::class)->allFormatted(),
        ];
    }
    
    public function states():HasMany{
        return $this->hasMany(State::class , 'country_id' , 'id') ; 
    } // 
}
