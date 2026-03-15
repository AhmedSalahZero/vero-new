<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StaticBoot;
/**
 * @property int $id
 * @property int|null $company_id
 * @property string|null $type
 * @property string|null $date
 * @property string|null $document_num
 * @property string|null $name
 * @property string|null $category
 * @property string|null $local_or_imported
 * @property string|null $sub_category
 * @property string|null $product
 * @property string|null $product_sku
 * @property string|null $measurment_unit
 * @property string|null $beginning_balance
 * @property string|null $volume_in
 * @property string|null $volume_out
 * @property string|null $end_balance
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property array<array-key, mixed>|null $validation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereBeginningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereDocumentNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereEndBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereLocalOrImported($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereMeasurmentUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereProductSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereSubCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereValidation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereVolumeIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatementTest whereVolumeOut($value)
 * @mixin \Eloquent
 */
class InventoryStatementTest extends Model
{
    use StaticBoot;
    //  SoftDeletes,

    
    protected $guarded = [];

  
    protected $table = 'inventory_statement_tests';
    protected $connection = 'client_view';
    /**
     * Scope a query to only include
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
    /**
     * Scope a query to only include
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeWithoutValidation($query)
    // {
    //     return $query->except(['validation']);
    // }
   
    protected $casts = [
        'validation' => 'array',
    ];
}
