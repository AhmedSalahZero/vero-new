<?php
namespace App\Models\Trading;


use App\Models\Trading\Study;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VatAndCreditWithholdTaxOpeningBalance extends Model
{
    protected $connection= TRADING_CONNECTION_NAME;
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
