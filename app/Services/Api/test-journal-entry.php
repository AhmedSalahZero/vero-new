<?php
public function testCreateAndPostJournalEntry(float $amount = 30000, string $date = '2025-11-19', int $currency_id = 74, int $journal_id = 19, int $debitOdooAccountId = 134, int $creditOdooAccountId = 225, $lgtype = '' , 
    $ref = '-------', $message = '') 
    {
            $statementEntryData = [
               'journal_id' => $journal_id,
               'amount' => $amount * -1,
               'date' => $date,
               'ref' =>  $ref,
               'line_ids' => [
                    [0, 0, [
                        'account_id' => $debitOdooAccountId, // 87
                        'debit' => abs($amount),
                        'credit' => 0.0,
                        'currency_id' => $currency_id,
                        'name' => $message ,
                       
                        
                    ]],
                    [0, 0, [
                        'account_id' => $creditOdooAccountId,
                        'debit' => 0.0,
                        'credit' => abs($amount),
                        'currency_id' => $currency_id,
                        'name' => $message ,    
                        
                        
                    ]],
                ],
            ];


            $context = [
                'check_move_validity' => true,
            ];

            $statementEntryId = $this->execute(
                'account.bank.statement.line',
                'create',
                [$statementEntryData],
                ['context' => $context]
            );
	
            // if (!is_numeric($journalEntryId)) {
            //     throw new Exception("Failed to create journal entry: " . json_encode($journalEntryId));
            // }

            

            return $statementEntryId;
    }
	