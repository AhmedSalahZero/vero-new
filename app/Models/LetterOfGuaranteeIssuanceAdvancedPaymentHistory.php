<?php

namespace App\Models;

use App\Services\Api\LetterOfGuaranteeService;
use App\Traits\HasCompany;
use App\Traits\Models\HasDeleteButTriggerChangeOnLastElement;
use App\Traits\Models\HasLetterOfGuaranteeCashCoverStatements;
use App\Traits\Models\HasLetterOfGuaranteeStatements;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterOfGuaranteeIssuanceAdvancedPaymentHistory extends Model
{
    use HasLetterOfGuaranteeStatements,HasLetterOfGuaranteeCashCoverStatements,HasDeleteButTriggerChangeOnLastElement,HasCompany;
    protected $table ='lg_issuance_advanced_payment_histories';
    protected $guarded =  [
        'id'
    ];
    public function letterOfGuaranteeIssuance()
    {
        return $this->belongsTo(LetterOfGuaranteeIssuance::class, 'letter_of_guarantee_issuance_id');
    }
    
    public function getDate()
    {
        return $this->date ;
    }
    public function getDateFormatted()
    {
        $date = $this->getDate() ;
        return $date ? Carbon::make($date)->format('d-m-Y') : null   ;
    }
    public function getAmount()
    {
        return $this->amount ?:0 ;
    }
    public function getAmountFormatted()
    {
        return number_format($this->getAmount()) ;
    }
    public function currentAccountBankStatements():HasMany
    {
        return $this->hasMany(CurrentAccountBankStatement::class, 'lg_advanced_payment_history_id', 'id');
    }
    public function letterOfGuaranteeStatements()
    {
        return $this->hasMany(LetterOfGuaranteeStatement::class, 'lg_advanced_payment_history_id', 'id');
    }
    public function letterOfGuaranteeCashCoverStatements()
    {
        return $this->hasMany(LetterOfGuaranteeCashCoverStatement::class, 'lg_advanced_payment_history_id', 'id');
    }
    
    public function currentAccountCreditBankStatement()
    {
        return $this->hasOne(CurrentAccountBankStatement::class, 'lg_advanced_payment_history_id', 'id')->where('is_credit', 1);
    }
    public function currentAccountCreditBankStatements()
    {
        return $this->hasMany(CurrentAccountBankStatement::class, 'lg_advanced_payment_history_id', 'id')->where('is_credit', 1)->orderBy('full_date', 'desc');
    }
    
    public function currentAccountDebitBankStatement()
    {
        return $this->hasOne(CurrentAccountBankStatement::class, 'lg_advanced_payment_history_id', 'id')->where('is_debit', 1);
    }
    public function currentAccountDebitBankStatements()
    {
        return $this->hasMany(CurrentAccountBankStatement::class, 'lg_advanced_payment_history_id', 'id')->where('is_debit', 1)->orderBy('full_date', 'desc');
    }
	public function deleteOdooRelations()
	{
		foreach (['journal_entry_id'] as $journalColumnName) {
            $currentJournalEntryId = $this->{$journalColumnName};
            if ($currentJournalEntryId) {
                $odooLetterOfGuaranteeIssuance = new LetterOfGuaranteeService($this->company);
                $odooLetterOfGuaranteeIssuance->unlink($currentJournalEntryId);
            }
        }
		
	}
    public function deleteAllRelations():void
    {
        $this->deleteOdooRelations();
        
        LetterOfGuaranteeStatement::deleteButTriggerChangeOnLastElement($this->letterOfGuaranteeStatements);
        LetterOfGuaranteeCashCoverStatement::deleteButTriggerChangeOnLastElement($this->letterOfGuaranteeCashCoverStatements);
        CurrentAccountBankStatement::deleteButTriggerChangeOnLastElement($this->currentAccountBankStatements);
    }

}
