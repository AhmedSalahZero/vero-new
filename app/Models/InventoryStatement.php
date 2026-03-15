<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereBeginningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereDocumentNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereEndBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereLocalOrImported($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereMeasurmentUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereProductSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereSubCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereVolumeIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\InventoryStatement whereVolumeOut($value)
 * @mixin \Eloquent
 */
class InventoryStatement extends Model
{
    use StaticBoot;
    // SoftDeletes

    protected $guarded = [];

    protected $table = 'inventory_statements';
    public function scopeCompany($query)
    {

        return $query->where('company_id', request()->company->id);
    }
}
