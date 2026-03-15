<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name_en
 * @property string|null $name_ar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\State> $states
 * @property-read int|null $states_count
 * @property-read bool|null $states_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Country query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Country whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Country whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Country whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Country whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Country whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Country extends Model  {

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
