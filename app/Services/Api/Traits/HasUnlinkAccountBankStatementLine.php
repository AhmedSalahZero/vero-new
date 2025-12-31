<?php 
namespace App\Services\Api\Traits;


trait HasUnlinkAccountBankStatementLine 
{

	public function unlink(int $journalEntryId)
    { 
        // Check if the payment exists
        $entry = $this->execute(
            'account.move',
            'read',
            [[$journalEntryId], ['id', 'state']]
        );
		
		 if (empty($entry)) {
			return ;
            // throw new \Exception("Move ID not found: " . $journalEntryId);
        }
        if ($entry[0]['state'] === 'draft') {
    //        Log::info("Payment $accountBankStatementLineId is already in draft state");
            return true;
        }
        
        // Set the account.payment to draft
         $this->execute(
            'account.move',
            'button_draft',
            [[$journalEntryId]]
        );
		
		
		$this->execute(
                'account.move',
                'unlink',
                [[$journalEntryId]]
            );
			

       
    }
	
}
