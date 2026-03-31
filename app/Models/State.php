<?php

namespace App\Models;

use App\Models\Traits\Relations\Commons\StateRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $country_id
 * @property string $name_ar
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Country|null $country
 * @property-read \App\Models\State|null $state
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\State query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\State whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\State whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\State whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\State whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class State extends Model  
{
    public function getRouteKeyName()
    {
        return 'states.id' ;
    }
    
    use StateRelations;
    
    protected $casts = [
        'name' => 'array',
    ];

    public function getName(): string
    {
		return $this['name_'.App()->getLocale()];
       
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
