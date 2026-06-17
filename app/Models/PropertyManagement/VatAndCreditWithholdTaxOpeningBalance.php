<?php
namespace App\Models\PropertyManagement;


use App\Models\PropertyManagement\Study;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property numeric $corporate_taxes_payable
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\VatAndCreditWithholdTaxOpeningBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\VatAndCreditWithholdTaxOpeningBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\VatAndCreditWithholdTaxOpeningBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\VatAndCreditWithholdTaxOpeningBalance whereCorporateTaxesPayable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\VatAndCreditWithholdTaxOpeningBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\VatAndCreditWithholdTaxOpeningBalance whereCreditWithholdTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\VatAndCreditWithholdTaxOpeningBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\VatAndCreditWithholdTaxOpeningBalance whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\VatAndCreditWithholdTaxOpeningBalance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\VatAndCreditWithholdTaxOpeningBalance whereVatAmount($value)
 * @mixin \Eloquent
 */
class VatAndCreditWithholdTaxOpeningBalance extends Model
{
    protected $guarded = ['id'];
	protected $connection= PROPERTY_MANAGEMENT_CONNECTION_NAME;
	protected $casts = [
		
	];
    public function study():BelongsTo
    {
        return $this->belongsTo(Study::class, 'study_id', 'id');
    }
	
  

}
