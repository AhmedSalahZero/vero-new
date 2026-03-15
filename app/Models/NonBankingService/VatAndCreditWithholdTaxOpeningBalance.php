<?php
namespace App\Models\NonBankingService;


use App\Models\NonBankingService\Study;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\VatAndCreditWithholdTaxOpeningBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\VatAndCreditWithholdTaxOpeningBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\VatAndCreditWithholdTaxOpeningBalance query()
 * @mixin \Eloquent
 */
class VatAndCreditWithholdTaxOpeningBalance extends Model
{
    protected $guarded = ['id'];

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
