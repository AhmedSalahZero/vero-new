<?php 
namespace App\Services\Api;

use App\Services\Api\Traits\AuthTrait;
use App\Services\Api\Traits\HasJournal;
use App\Services\Api\Traits\HasUnlinkAccountBankStatementLine;
use Exception;

class MoneyPaymentOdooService
{
    use AuthTrait,HasJournal,HasUnlinkAccountBankStatementLine;
	// string $date,int $outJournalId,float $amount,int $odooCurrencyId,int $lgOddoAccountId
	
	protected function getRef()
	{
		return null ;
	}protected function getMessage()
	{
		return null;
	}
	public function getOdooPartnerId()
	{
		return null ;
	}
	
	 protected function createAndPostJournalEntry(string $date , float $amountInCurrency , float $amountInMainFunctionalCurrency  , int $odooCurrencyId , int $journalId, int $debitOdooAccountId , int $creditOdooAccountId , ?string $ref , ?int $partner_id ,?string $message , ?bool $isMoneyReceived = false ) 
    {
			// $id = null ;  // in edit mode 
            $journalEntryData = $this->getDataFormatted($date,$amountInCurrency,$amountInMainFunctionalCurrency,$odooCurrencyId,$journalId,$debitOdooAccountId,$creditOdooAccountId,$ref,$partner_id,$message,$isMoneyReceived) ;

            $context = [
                'check_move_validity' => true,
            ];
            $accountBankStatementLineId = $this->execute(
                'account.bank.statement.line',
                'create',
                [$journalEntryData],
                ['context' => $context]
            );
            if (!is_numeric($accountBankStatementLineId)) {
                throw new Exception("Failed to create journal entry: " . json_encode($accountBankStatementLineId));
            }
			
		
			  $statementData = $this->execute(
          	  'account.bank.statement.line',
           	 'read',
         	   [[$accountBankStatementLineId], ['move_id','name']],
        	    []
       		 );

        if (!is_array($statementData) || empty($statementData[0]['move_id'])) {
            throw new Exception("Failed to retrieve move_id for statement entry: " . $accountBankStatementLineId);
        }
			$moveId = $statementData[0]['move_id'][0];
            if (!is_numeric($accountBankStatementLineId)) {
                throw new Exception("Failed to create journal entry: " . json_encode($accountBankStatementLineId));
            }
			if($partner_id){
				$this->updatePartner($partner_id,$moveId,$context);
			}
			
			
            return [
				'account_bank_statement_line_id'=>$accountBankStatementLineId,
				'journal_entry_id'=>$moveId,
				'odoo_reference'=>$statementData[0]['name']??null
			];
    }
	protected function updatePartner($partner_id,$moveId,$context)
	{
		return ;
		$this->execute(
            'account.move',
            'button_draft',
            [[$moveId]],
            ['context' => $context]
        );
		
		$this->execute(
            'account.move',
            'read',
            [$moveId, ['partner_id', 'commercial_partner_id', 'bank_partner_id']],
            ['context' => $context]
        );
		$x = $this->execute(
                'account.move',
                'write',
                [$moveId, [
                    'partner_id' => $partner_id,
                    'commercial_partner_id' => $partner_id,
                    'bank_partner_id' => $partner_id,
                ]]
            );
			$x  = $this->execute(
                'account.move',
                'action_post',
                [[$moveId]]
            );
			
	}
	protected function getDataFormatted(string $date , float $amountInCurrency  , float $amountInMainFunctionalCurrency  , int $odooCurrencyId , int $journalId, int $debitOdooAccountId , int $creditOdooAccountId   , ?string $ref , ?int $partner_id ,?string $message , bool $isMoneyReceived = false ):array 
	{
		
				$paymentRef = $ref ;
				$message =$paymentRef;

		return [
               'journal_id' => $journalId, // account journal id (safe or bank journal id )
               'amount' => $isMoneyReceived ? $amountInCurrency : -$amountInCurrency,
               'date' => $date,
               'partner_id' => $partner_id,
               'ref' =>  $ref, // create lg type
               'payment_ref' =>  $paymentRef, // create lg type
               'line_ids' => [
                    [0,0, [
                        'account_id' => $debitOdooAccountId, // lg cash cover odoo id (create lg cash cover)
                        'debit' => abs($amountInMainFunctionalCurrency),
						'amount_currency'=>abs($amountInCurrency),
                        'credit' => 0.0,
                       'partner_id' => $partner_id ,
                        'currency_id' => $odooCurrencyId,
                        'name' => $message , // cash cover  
                    ]],
                    [0,0, [
                        'account_id' => $creditOdooAccountId, // chart of account odoo id 
                        'debit' => 0.0,
                        'credit' => abs($amountInMainFunctionalCurrency),
						'amount_currency'=>-$amountInCurrency,
                        'currency_id' => $odooCurrencyId,
                        'name' => $message ,
                 	    'partner_id' => $partner_id
                    ]],
                ],
            ];
	}
	
    public function createCashExpense(string $date,float $amountInCurrency,float $amountInMainFunctionalCurrency,int $journalId,int $odooCurrencyId,int $debitOdooAccountId,int $creditOdooAccountId,int $odooPartnerId,string $ref,int $isTax , bool $isMoneyReceived = false )
    {
		  $message =$this->getMessage(); 
		  $odooPartnerId = $isTax ? null : $odooPartnerId;
          return $this->createAndPostJournalEntry($date,$amountInCurrency,$amountInMainFunctionalCurrency,$odooCurrencyId,$journalId,$debitOdooAccountId,$creditOdooAccountId,$ref,$odooPartnerId,$message,$isMoneyReceived);
       
    }
	
	
	
	
// 	public function createJournalEntry(
//     float $amount = 30000,
//     string $date = '2025-11-19',
//     int $debitAccountId = 134,
//     int $creditAccountId = 225,
//     int $journalId = null,                  // Optional now
//     int $currencyId = 74,
//     string $ref = '-------',
//     string $message = '',
//     string $moveName = null
// ) {
//     // Prepare move (journal entry) data
//     $moveData = [
//         'date'         => $date,
//         'journal_id'   => $journalId,       // Can be false/null → Odoo will pick default
//         'ref'          => $ref,
//         'name'         => $moveName ?? '/', // '/' lets Odoo auto-generate the number
//         'line_ids'     => [
//             [0, 0, [
//                 'account_id'   => $debitAccountId,
//                 'debit'        => $amount,
//                 'credit'       => 0.0,
//                 'currency_id'  => $currencyId !== 74 ? $currencyId : false,
//                 'name'         => $message ?: '/',
//                 'partner_id'   => false,
//             ]],
//             [0, 0, [
//                 'account_id'   => $creditAccountId,
//                 'debit'        => 0.0,
//                 'credit'       => $amount,
//                 'currency_id'  => $currencyId !== 74 ? $currencyId : false,
//                 'name'         => $message ?: '/',
//                 'partner_id'   => false,
//             ]],
//         ],
//     ];

//     // Optional: bypass move validity check only if you're 100% sure it's balanced
//     $context = [
//         'check_move_validity' => true,
//         // 'skip_account_move_synchronization' => true, // only if needed
//     ];

//     $moveId = $this->execute(
//         'account.move',
//         'create',
//         [$moveData],
//         ['context' => $context]
//     );

//     // Optionally post the entry immediately
//     if ($moveId) {
//         $this->execute('account.move', 'action_post', [$moveId]);
//     }

//     dd('Journal Entry Created & Posted', $moveId);

//     return $moveId;
// }

}
?>
