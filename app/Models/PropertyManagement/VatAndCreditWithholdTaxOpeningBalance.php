<?php
namespace App\Models\PropertyManagement;


use App\Models\PropertyManagement\Study;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperVatAndCreditWithholdTaxOpeningBalance
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
	
    public function getVatAmount():float 
    {
        return $this->vat_amount ;
    } 
	public function getCreditWithholdTaxes():float 
    {
        return $this->credit_withhold_taxes ;
    }
	public function getCorporateTaxesPayableAmount():float 
    {
        return $this->corporate_taxes_payable ;
    }
}
