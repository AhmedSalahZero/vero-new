<?php

namespace App\Models;

use App\Interfaces\Models\IBaseModel;
use App\Models\Traits\Relations\Commons\StateRelations;
use Illuminate\Database\Eloquent\Model;

class State extends Model implements IBaseModel 
{
    public function getRouteKeyName()
    {
        return 'states.id' ;
    }
    
    use StateRelations;
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'array',
    ];

    public function getName(): string
    {
        if (!$this->name) {
            return '';
        }
        
        // If name is string, return it directly
        if (is_string($this->name)) {
            return $this->name;
        }
        
        // If name is array/JSON, get localized version
        $locale = App()->getLocale();
        return $this->name[$locale] ?? $this->name['en'] ?? '';
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
}
