<?php
namespace App\Services\Api;

use App\Models\Company;
use App\Models\Currency;
use App\Services\Api\Traits\AuthTrait;
use App\Services\Api\Traits\HasJournal;
use App\Services\Api\Traits\HasPayment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OdooPayment
{
    use AuthTrait,HasPayment,HasJournal ;
    
    public function createDownPayment($moneyModel)
    {
        try {
            $company = $moneyModel->company ;
            $paymentDate = $moneyModel->getReceivingOrPaymentMoneyDate();
            if (!$company->withinIntegrationDate($paymentDate)) {
                return ;
            }
            $journalId = $this->getJournalId($moneyModel) ;
        
            /**
             * * $bankOrSafeId
             */
            $paymentAmount = $moneyModel->isInvoiceSettlementWithDownPayment() ? $moneyModel->downPaymentSettlements->sum('down_payment_amount') : $moneyModel->getAmount()  ;
            
            if ($moneyModel->isChequeAndNotCustomerOrSupplier()) {
                $paymentAmount=$moneyModel->getAmount();
            }
            $currencyName = $moneyModel->getReceivingOrPaymentCurrency();
            $odooCurrencyId = Currency::getOdooId($currencyName);
            
            /**
             * @var Company $company;
             */
            
            $odooPartnerId = $moneyModel->partner ? $moneyModel->partner->getOdooId() : null;
            $inBoundOrOutBound =$moneyModel->getInboundOrOutbound();
            $customerOrSupplier = $moneyModel->getCustomerOrSupplier();
    
       
            // Step 2: Register payment using account.payment.register
            $context = [
                'active_model' => 'account.move',
                   'active_ids' => [],
            ];
          
            $paymentId = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->password,
                'account.payment',
                'create',
                [[
                    'amount' => $paymentAmount,
                    'journal_id' => $journalId,
                    'date' => $paymentDate,
                    'currency_id'=>$odooCurrencyId,
                    'partner_id' => $odooPartnerId,
                    'payment_type' => $inBoundOrOutBound,
                    'partner_type' => $customerOrSupplier ,
                    'payment_method_line_id'=>(int)$moneyModel->getPaymentMethodLineId(),
                    'memo'=>$moneyModel->generateDownPaymentMessage(),
                ]],
                ['context' => $context]
            );
            
            
            $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->password,
                'account.payment',
                'action_post',
                [[$paymentId]],
            );
    
            if (is_array($paymentId) && isset($paymentId['faultString'])) {
                session()->put('fail', $paymentId['faultString']);
                $moneyModel->update([
                    'synced_with_odoo'=>false ,
                    'odoo_error_message'=>$paymentId['faultString']
                ]);
                return ;
            }
            $odooAccountPayment = $this->fetchData('account.payment', [], [[['id','=',$paymentId]]]);
            $moneyModel->update([
                'odoo_id'=>$paymentId,
				'odoo_move_id'=>$odooAccountPayment[0]['move_id'][0]??null,
                'odoo_reference'=>$odooAccountPayment[0]['name']??null,
                'synced_with_odoo'=>true ,
                'odoo_error_message'=>null
            ]);
        } catch (\Exception $e) {
            session()->put('fail', __('Error While Connecting With Odoo : ' . $e->getMessage()));
            $moneyModel->update([
                'synced_with_odoo'=>false ,
                'odoo_error_message'=>$e->getMessage()
            ]);
        }
        

         
    }
    
    public function createDownPaymentFromSettlement($settlement)
    {
    
      
        try {
            $company = $settlement->company ;
            $moneyModel =  $settlement->getMoney() ;
            $paymentDate =$moneyModel->getReceivingOrPaymentMoneyDate();
            if (!$company->withinIntegrationDate($paymentDate)) {
                return ;
            }
            $journalId = $this->getJournalId($moneyModel) ;
            /**
             * * $bankOrSafeId
             */
            $paymentAmount = $settlement->getAmountInReceivingCurrency()   ;
            $currencyName = $moneyModel->getReceivingOrPaymentCurrency();
            $odooCurrencyId = Currency::getOdooId($currencyName);
            
            /**
             * @var Company $company;
             */
            
            $odooPartnerId = $moneyModel->partner->getOdooId();
            $inBoundOrOutBound =$moneyModel->getInboundOrOutbound();
            $customerOrSupplier = $moneyModel->getCustomerOrSupplier();
    
       
            // Step 2: Register payment using account.payment.register
            $context = [
                'active_model' => 'account.move',
                   'active_ids' => [],
            ];

            $paymentId = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->password,
                'account.payment',
                'create',
                [[
                    'amount' => $paymentAmount,
                    'journal_id' => $journalId,
                    'date' => $paymentDate,
                    'currency_id'=>$odooCurrencyId,
                    'partner_id' => $odooPartnerId,
                    'payment_type' => $inBoundOrOutBound,
                    'partner_type' => $customerOrSupplier ,
                    'payment_method_line_id'=>(int)$moneyModel->getPaymentMethodLineId()
                ]],
                ['context' => $context]
            );
            
            
            $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->password,
                'account.payment',
                'action_post',
                [[$paymentId]],
            );
            if (is_array($paymentId) && isset($paymentId['faultString'])) {
                session()->put('fail', $paymentId['faultString']);
                $moneyModel->update([
                    'synced_with_odoo'=>false ,
                    'odoo_error_message'=>$paymentId['faultString']
                ]);
                return ;
            }
            $odooAccountPayment = $this->fetchData('account.payment', [], [[['id','=',$paymentId]]]);
            $moneyModel->update([
                'synced_with_odoo'=>true ,
                'odoo_error_message'=>null
            ]);
            $settlement->update([
                'odoo_id'=>$paymentId,
                'odoo_reference_name'=>$odooAccountPayment[0]['name']??null,
				'odoo_move_id'=>$odooAccountPayment[0]['move_id'][0]??null,
            ]);
                
        } catch (\Exception $e) {
            session()->put('fail', __('Error While Connecting With Odoo : ' . $e->getMessage()));
            $moneyModel->update([
                'synced_with_odoo'=>false ,
                'odoo_error_message'=>$e->getMessage()
            ]);
        }
        

         
    }
   
	
// 	public function createPayment($customerInvoiceSettlement)
// {
//     $invoice = $customerInvoiceSettlement->invoice;
//     $moneyModel = $customerInvoiceSettlement->getMoney();
//     $amountInInReceivingCurrency = $customerInvoiceSettlement->getAmountInReceivingCurrency();
    
//     if ($invoice->opening_balance_id) {
//         return $this->createDownPaymentFromSettlement($customerInvoiceSettlement);
//     }
    
//     $journalId = $this->getJournalId($moneyModel);
//     $invoiceId = $invoice->getOdooId();
//     $paymentDate = $moneyModel->getReceivingOrPaymentMoneyDate();
    
//     if (!$this->company->withinIntegrationDate($paymentDate)) {
//         return;
//     }
    
//     $odooPartnerId = $moneyModel->partner->getOdooId();
//     $invoiceNumber = $invoice->getInvoiceNumber();
//     $inBoundOrOutBound = $moneyModel->getInboundOrOutbound();
//     $customerOrSupplier = $moneyModel->getCustomerOrSupplier();
    
    
    
//     $invoiceCurrencyId = 1;
//     $invoiceAmount = $customerInvoiceSettlement->getAmount();
    
//     $receivingCurrencyName = $moneyModel->getReceivingOrPaymentCurrency();
//     $odooReceivingCurrencyId = Currency::getOdooId($receivingCurrencyName);
    
//     // Calculate manual exchange rate
//     $manualExchangeRate = null;
//     if ($invoiceCurrencyId != $odooReceivingCurrencyId && $invoiceAmount > 0) {
//         $manualExchangeRate = $amountInInReceivingCurrency / $invoiceAmount;
//     }
    
//     $context = [
//         'active_model' => 'account.move',
//         'active_ids' => [$invoiceId],
//         'active_id' => $invoiceId,
//     ];
    
//     $paymentWizardData = [
//         'amount' => (float)$invoiceAmount,
//         'currency_id' => (int)$invoiceCurrencyId,
//         'journal_id' => (int)$journalId,
//         'payment_date' => $paymentDate,
//         'communication' => $invoiceNumber,
//         'partner_id' => (int)$odooPartnerId,
//         'payment_type' => $inBoundOrOutBound,
//         'partner_type' => $customerOrSupplier,
//         'payment_method_line_id' => (int)$moneyModel->getPaymentMethodLineId(),
//     ];
    
//     if ($manualExchangeRate !== null) {
//         $paymentWizardData['manual_currency_exchange_rate'] = (float)$manualExchangeRate;
//     }
    
//     try {
//         // Create the payment wizard with context
//         $paymentWizardId = $this->models->execute_kw(
//             $this->db,
//             $this->uid,
//             $this->password,
//             'account.payment.register',
//             'create',
//             [[$paymentWizardData]],
//             ['context' => $context]
//         );
        
//         if (!$paymentWizardId) {
//             throw new \Exception('Failed to create payment wizard');
//         }
        
//         // Create payments - pass the ID in correct format
//         $paymentResult = $this->models->execute_kw(
//             $this->db,
//             $this->uid,
//             $this->password,
//             'account.payment.register',
//             'action_create_payments',
//             [[$paymentWizardId]],
//             ['context' => $context]
//         );
        
//     } catch (\Exception $e) {
//         $errorMessage = $e->getMessage();
//         session()->put('fail', $errorMessage);
//         $moneyModel->update([
//             'synced_with_odoo' => false,
//             'odoo_error_message' => $errorMessage
//         ]);
//         return null;
//     }
    
//     if (is_array($paymentResult) && isset($paymentResult['faultString'])) {
//         session()->put('fail', $paymentResult['faultString']);
//         $moneyModel->update([
//             'synced_with_odoo' => false,
//             'odoo_error_message' => $paymentResult['faultString']
//         ]);
//         return null;
//     }
    
//     $resId = $paymentResult['res_id'] ?? null;
    
//     if (is_numeric($resId) && $resId > 0) {
//         $odooAccountPayment = $this->fetchData(
//             'account.payment',
//             ['id', 'name', 'move_id'],
//             [[['id', '=', $resId]]]
//         );
        
//         $moneyModel->update([
//             'synced_with_odoo' => true,
//             'odoo_error_message' => null
//         ]);
        
//         $customerInvoiceSettlement->update([
//             'odoo_reference_name' => $odooAccountPayment[0]['name'] ?? null,
//             'odoo_id' => $resId,
//             'odoo_move_id' => $odooAccountPayment[0]['move_id'][0] ?? null,
//         ]);
        
//         return [
//             'odoo_id' => $resId
//         ];
//     }
    
//     return null;
// }


	
    // public function createPayment($customerInvoiceSettlement)
    // {
        
        
            
    //     $invoice = $customerInvoiceSettlement->invoice;
    //     $moneyModel = $customerInvoiceSettlement->getMoney();
    //     $amountInInReceivingCurrency = $customerInvoiceSettlement->getAmountInReceivingCurrency();
    //     if ($invoice->opening_balance_id) {
    //         return $this->createDownPaymentFromSettlement($customerInvoiceSettlement);
    //     }
    //     $journalId = $this->getJournalId($moneyModel) ;
    //     /**
    //      * * $bankOrSafeId
    //      */
    //     $invoiceId = $invoice->getOdooId();
    //     $receivingCurrencyName = $moneyModel->getReceivingOrPaymentCurrency();
    //     $odooReceivingCurrencyId =  Currency::getOdooId($receivingCurrencyName) ;
    //     $paymentDate = $moneyModel->getReceivingOrPaymentMoneyDate();
    //     if (!$this->company->withinIntegrationDate($paymentDate)) {
    //         return ;
    //     }
    //     $odooPartnerId = $moneyModel->partner->getOdooId();
    //     $invoiceNumber = $invoice->getInvoiceNumber();
    //     $inBoundOrOutBound =$moneyModel->getInboundOrOutbound();
    //     $customerOrSupplier = $moneyModel->getCustomerOrSupplier();
    
       
    //     $context = [
    //         'active_model' => 'account.move',
    //         'active_ids' => [$invoiceId],
	// 	];
    //         //  $paymentWizardData['manual_currency_exchange_rate'] = (float)$manualExchangeRate;
    //     $paymentWizardId = $this->models->execute_kw(
    //         $this->db,
    //         $this->uid,
    //         $this->password,
    //         'account.payment.register',
    //         'create',
    //         [[
    //             'amount' => $amountInInReceivingCurrency,
    //             'currency_id'=>$odooReceivingCurrencyId,
    //             'journal_id' => $journalId,
    //             'payment_date' => $paymentDate,
    //             'communication' => $invoiceNumber,
    //             'partner_id' => $odooPartnerId,
    //             'payment_type' => $inBoundOrOutBound,
    //             'partner_type' => $customerOrSupplier ,
    //              'payment_method_line_id'=>$moneyModel->getPaymentMethodLineId(),
    //         ]],
    //         ['context' => $context]
    //     );
            
    //     $paymentResult = $this->models->execute_kw(
    //         $this->db,
    //         $this->uid,
    //         $this->password,
    //         'account.payment.register',
    //         'action_create_payments',
    //         [[$paymentWizardId]],
    //         ['context' => $context]
    //     );
    //     if (is_array($paymentResult) && isset($paymentResult['faultString'])) {
    //         session()->put('fail', $paymentResult['faultString']);
    //         $moneyModel->update([
    //             'synced_with_odoo'=>false ,
    //             'odoo_error_message'=>$paymentResult['faultString']
    //         ]);
    //         return ;
    //     }
    //     $resId = $paymentResult['res_id'];
    //     if (is_numeric($resId)) {
    //         $odooAccountPayment = $this->fetchData('account.payment', [], [[['id','=',$resId]]]);
    //         $moneyModel->update([
    //         'synced_with_odoo'=>true ,
    //         'odoo_error_message'=>null
    //         ]);
	// 		$accountMoveId = $odooAccountPayment[0]['move_id'][0]??null;
    //         $customerInvoiceSettlement->update([
    //             'odoo_reference_name'=>$odooAccountPayment[0]['name']??null,
    //             'odoo_id'=>$resId,
	// 			'odoo_move_id'=>$accountMoveId,
    //         ]);
			
			
	// 	// 	$this->execute(
    //     //     'account.move',
    //     //     'button_draft',
    //     //     [[$accountMoveId]]
    //     // );
		
	// 		// $this->execute(
    //         //     'account.move',
    //         //     'write',
    //         //     [$accountMoveId, [
	// 		// 		'amount_total'=>1000,
	// 		// 		'amount_total_in_currency_signed'=>1000,
    //         //     ]]
    //         // );
			
    //         // $res=$fetch->fetchData('account.move',[],[[['id','=',$accountMoveId]]]);
            
    
    //         return [
    //             'odoo_id'=>$resId
    //         ];
            
    //     }
            
            
       
    // }
	
	public function createPayment($customerInvoiceSettlement)
    {
        
        
            
        $invoice = $customerInvoiceSettlement->invoice;
        $moneyModel = $customerInvoiceSettlement->getMoney();
        $amountInInReceivingCurrency = $customerInvoiceSettlement->getAmountInReceivingCurrency();
        if ($invoice->opening_balance_id) {
            return $this->createDownPaymentFromSettlement($customerInvoiceSettlement);
        }
        $journalId = $this->getJournalId($moneyModel) ;
        /**
         * * $bankOrSafeId
         */
        $invoiceId = $invoice->getOdooId();
        $receivingCurrencyName = $moneyModel->getReceivingOrPaymentCurrency();
        $odooReceivingCurrencyId =  Currency::getOdooId($receivingCurrencyName) ;
        $paymentDate = $moneyModel->getReceivingOrPaymentMoneyDate();
        if (!$this->company->withinIntegrationDate($paymentDate)) {
            return ;
        }
        $odooPartnerId = $moneyModel->partner->getOdooId();
        $invoiceNumber = $invoice->getInvoiceNumber();
        $inBoundOrOutBound =$moneyModel->getInboundOrOutbound();
        $customerOrSupplier = $moneyModel->getCustomerOrSupplier();
    
       
        $context = [
            'active_model' => 'account.move',
            'active_ids' => [$invoiceId],
		];
            //  $paymentWizardData['manual_currency_exchange_rate'] = (float)$manualExchangeRate;
        $paymentWizardId = $this->models->execute_kw(
            $this->db,
            $this->uid,
            $this->password,
            'account.payment.register',
            'create',
            [[
                'amount' => $amountInInReceivingCurrency,
                'currency_id'=>$odooReceivingCurrencyId,
                'journal_id' => $journalId,
                'payment_date' => $paymentDate,
                'communication' => $invoiceNumber,
                'partner_id' => $odooPartnerId,
                'payment_type' => $inBoundOrOutBound,
                'partner_type' => $customerOrSupplier ,
                 'payment_method_line_id'=>$moneyModel->getPaymentMethodLineId(),
				 
            ]],
            ['context' => $context]
        );
            
        $paymentResult = $this->models->execute_kw(
            $this->db,
            $this->uid,
            $this->password,
            'account.payment.register',
            'action_create_payments',
            [[$paymentWizardId]],
            ['context' => $context]
        );
        if (is_array($paymentResult) && isset($paymentResult['faultString'])) {
            session()->put('fail', $paymentResult['faultString']);
            $moneyModel->update([
                'synced_with_odoo'=>false ,
                'odoo_error_message'=>$paymentResult['faultString']
            ]);
            return ;
        }
        $resId = $paymentResult['res_id'];
        if (is_numeric($resId)) {
            $odooAccountPayment = $this->fetchData('account.payment', ['id','name'], [[['id','=',$resId]]]);
            $moneyModel->update([
            'synced_with_odoo'=>true ,
            'odoo_error_message'=>null
            ]);
            $customerInvoiceSettlement->update([
                'odoo_reference_name'=>$odooAccountPayment[0]['name']??null,
                'odoo_id'=>$resId,
				'odoo_move_id'=>$odooAccountPayment[0]['move_id'][0]??null,
            ]);
            
            
    
            return [
                'odoo_id'=>$resId
            ];
            
        }
            
            
       
    }
    
    public function reCreatePayment($customerInvoiceSettlement)
    {
        if ($customerInvoiceSettlement->odoo_id) {
            $this->cancelPayments($customerInvoiceSettlement->odoo_id);
        }
        $this->createPayment($customerInvoiceSettlement);

    }
    
    public function reCreateDownPayment($moneyModel)
    {
        
        if ($moneyModel->odoo_id) {
            $this->cancelPayments($moneyModel->odoo_id);
        }
        
        $this->createDownPayment($moneyModel);

    }
    
   
    
    
    public function chequeCollection(
        int $accountPayment_id,
        float $amount,
        string $date,
        int $currency_id,
        int $journal_id, // NBE Journal
        int $debitOdooAccountId, // Misr Account
        int $creditOdooAccountId, // Cheque Receivable Account
        int $PartnerId,
        $ref,
        $message = ''
    ) {
    
        
        try {
            // Step 1: Verify the payment exists and get its details
            $paymentData = $this->execute(
                'account.payment',
                'read',
                [[$accountPayment_id], ['state', 'move_id', 'reconciled_invoice_ids', 'is_matched']],
                []
            );
            if (!$paymentData || !is_array($paymentData) || empty($paymentData)) {
                throw new Exception("Payment ID $accountPayment_id not found or invalid response");
            }

            $paymentData = $paymentData[0]; // Access first element safely
            $paymentState = $paymentData['state'];
            $invoiceIds = $paymentData['reconciled_invoice_ids'] ?? [];
            $moveId = $paymentData['move_id'] ? $paymentData['move_id'][0] : null;
            $isMatched = $paymentData['is_matched'] ?? false;

            if (!in_array($paymentState, ['draft', 'posted', 'in_process'])) {
                throw new Exception("Payment ID $accountPayment_id is in state '$paymentState' and cannot be processed");
            }
            
            // Step 2: If payment is in draft, post it
            if ($paymentState === 'draft') {
                $this->execute(
                    'account.payment',
                    'action_post',
                    [[$accountPayment_id]],
                    []
                );
                $paymentState = 'posted';
            }


            // Step 3: Check if payment is already linked to a bank statement
            $existingStatementLines = $this->execute(
                'account.bank.statement.line',
                'search',
                [[['payment_ids', 'in', [$accountPayment_id]]]],
                []
            );



            $statementEntryId = null;
            $statementMoveId = null;
            $statementLineIds = [];

        
            if (empty($existingStatementLines)) {
                // Step 4: Create bank statement line to affect bank balance
                $statementEntryData = [
                    'journal_id' => $journal_id,
                    'amount' => $amount, // Positive for bank deposit
                    'date' => $date,
					'payment_ref'=>__('Cheque Received'),
                    'ref' => $ref,
                    'partner_id' => $PartnerId,
                    'payment_ids' => [[6, 0, [$accountPayment_id]]], // Link payment using payment_ids
                    'name' => $message ,
                    'is_reconciled' => true,
                    'line_ids' => [
                        [0, 0, [
                            'account_id' => $debitOdooAccountId,
                            'debit' => abs($amount),
                            'credit' => 0.0,
                            'currency_id' => $currency_id,
                            'name' => $message,
                            'partner_id' => $PartnerId,
                           
                        ]],
                        
                        [0, 0, [
                            'account_id' => $creditOdooAccountId,
                            'debit' => 0.0,
                            'credit' => abs($amount),
                            'currency_id' => $currency_id,
                            'name' => $message,
                          'partner_id' => $PartnerId,
                          
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
            
      
                if (!is_numeric($statementEntryId)) {
                    throw new Exception("Failed to create bank statement line: " . json_encode($statementEntryId));
                }



                // Step 5: Get the move_id and line_ids from the bank statement line
                $statementData = $this->execute(
                    'account.bank.statement.line',
                    'read',
                    [[$statementEntryId], ['move_id', 'line_ids']],
                    []
                );



                if (!is_array($statementData) || empty($statementData) || !isset($statementData[0]['move_id'])) {
                    throw new Exception("Failed to retrieve move_id for statement entry: $moveId, response: " . json_encode($statementData));
                }

                $statementMoveId = $statementData[0]['move_id'][0];
                $bankReference = $statementData[0]['move_id'][1];
        
                $statementLineIds = $statementData[0]['line_ids'][1] ?? [];



                // Step 6: Reconcile payment and bank statement lines
                $paymentLineIds = $this->execute(
                    'account.move.line',
                    'search',
                    [[['move_id', '=', $moveId], ['account_id', '=', $creditOdooAccountId]]],
                    []
                );

                if (!$paymentLineIds || !is_array($paymentLineIds)) {
                    throw new Exception("Failed to retrieve payment move lines for move_id: $moveId");
                }

                $linesToReconcile = array_merge($paymentLineIds, (array)$statementLineIds);


                try {
                    $result = $this->execute(
                        'account.move.line',
                        'reconcile',
                        [$linesToReconcile],
                        ['context' => ['skip_full_reconcile_check' => true]]
                    );
                    // Handle success
                } catch (Exception $e) {
                    session()->put('fail', $e->getMessage());
                
                    // Log or handle error
                    Log::error('Odoo reconciliation failed: ' . $e->getMessage());
                }
                
            }

       
            // Step 7: Update payment to set is_matched to true if not already
            if (!$isMatched) {
                $matching  = $this->execute(
                    'account.payment',
                    'write',
                    [[$accountPayment_id], ['is_matched' => true]],
                    []
                );
            }

            // Step 8: Verify invoice state is 'paid'
            if (!empty($invoiceIds)) {
                $invoiceState = $this->execute(
                    'account.move',
                    'read',
                    [$invoiceIds, ['state']],
                    []
                );

                foreach ($invoiceState as $invoice) {
                    if ($invoice['state'] !== 'paid') {
                        Log::warning("Invoice ID {$invoice['id']} state is {$invoice['state']} instead of 'paid'");
                    }
                }
            } else {
                Log::warning("No invoices linked to payment ID $accountPayment_id");
            }
    
            return [
                'statement_entry_id' => $statementEntryId,
                'bank_reference'=>$bankReference??null,
                'entry_id' => $statementMoveId,
                'payment_id' => $accountPayment_id,
                'invoice_state' => !empty($invoiceState) ? $invoiceState[0]['state'] : 'unknown',
                'message' => 'Cheque collection processed successfully, payment marked as matched, and invoice set to paid'
            ];

        } catch (\Exception $e) {
            session()->put('fail', 'Error in chequeCollection: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Failed to process cheque collection: ' . $e->getMessage()
            ];
        }
    }
    
    public function chequePayment(
        $accountPayment_id,
        float $amount,
        string $date,
        int $currency_id,
        int $journal_id, // Misr Bank Journal
        int $debitOdooAccountId, // Cheque Payable Account
        int $creditOdooAccountId, // Bank Misr Account
        ?int $PartnerId,
        string $ref,
        $message = ''
    ) {
        try {
            // Step 1: Verify the payment exists and get its details
            $paymentData = $this->execute(
                'account.payment',
                'read',
                [[$accountPayment_id], ['state', 'move_id', 'reconciled_invoice_ids', 'is_matched']],
                []
            );


            if (!$paymentData || !is_array($paymentData) || empty($paymentData)) {
                throw new Exception("Payment ID $accountPayment_id not found or invalid response");
            }

            $paymentData = $paymentData[0]; // Access first element safely
            $paymentState = $paymentData['state'];
            $invoiceIds = $paymentData['reconciled_invoice_ids'] ?? [];
            $moveId = $paymentData['move_id'] ? $paymentData['move_id'][0] : null;
            $isMatched = $paymentData['is_matched'] ?? false;

            if (!in_array($paymentState, ['draft', 'posted', 'in_process'])) {
                throw new Exception("Payment ID $accountPayment_id is in state '$paymentState' and cannot be processed");
            }
        
            // Step 2: If payment is in draft, post it
            if ($paymentState === 'draft') {
                $this->execute(
                    'account.payment',
                    'action_post',
                    [[$accountPayment_id]],
                    []
                );
                $paymentState = 'posted';
            }

            // Step 3: Check if payment is already linked to a bank statement
            $existingStatementLines = $this->execute(
                'account.bank.statement.line',
                'search',
                [[['payment_ids', 'in', [$accountPayment_id]]]],
                []
            );


            $statementEntryId = null;
            $statementMoveId = null;
            $statementLineIds = [];


            
    
            if (empty($existingStatementLines)) {
                // Step 4: Create bank statement line to affect bank balance
                $statementEntryData = [
                    'journal_id' => $journal_id,
                    'amount' => $amount * -1, // Negative for bank payments
                    'date' => $date,
                    'ref' => $ref,
                    'partner_id' => $PartnerId,
					'payment_ref'=>__('Cheque Paid'),
                    'payment_ids' => [[6, 0, [$accountPayment_id]]], // Link payment using payment_ids
                    'name' => $message ,
                    'is_reconciled' => true,
                    
                    'line_ids' => [
                        [0, 0, [
                            'account_id' => $debitOdooAccountId,
                            'debit' => abs($amount),
                            'credit' => 0.0,
                            'currency_id' => $currency_id,
                            'name' => $message,
                              'partner_id' => $PartnerId,
                           
                        ]],
                        
                        [0, 0, [
                            'account_id' => $creditOdooAccountId,
                            'debit' => 0.0,
                            'credit' => abs($amount),
                            'currency_id' => $currency_id,
                            'name' => $message,
                            'partner_id' => $PartnerId,
                          
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

           
                if (!is_numeric($statementEntryId)) {
                    throw new Exception("Failed to create bank statement line: " . json_encode($statementEntryId));
                }




                // Step 5: Get the move_id and line_ids from the bank statement line
                $statementData = $this->execute(
                    'account.bank.statement.line',
                    'read',
                    [[$statementEntryId], ['move_id', 'line_ids']],
                    []
                );



                if (!is_array($statementData) || empty($statementData) || !isset($statementData[0]['move_id'])) {
                    throw new Exception("Failed to retrieve move_id for statement entry: $moveId, response: " . json_encode($statementData));
                }

                $statementMoveId = $statementData[0]['move_id'][0];
                $statementLineIds = $statementData[0]['line_ids'][0] ?? [];
                $bankReference = $statementData[0]['move_id'][1];


                // Step 6: Reconcile payment and bank statement lines
                $paymentLineIds = $this->execute(
                    'account.move.line',
                    'search',
                    [[['move_id', '=', $moveId], ['account_id', '=', $debitOdooAccountId]]],
                    []
                );


                if (!$paymentLineIds || !is_array($paymentLineIds)) {
                    throw new Exception("Failed to retrieve payment move lines for move_id: $moveId");
                }

                $linesToReconcile = array_merge($paymentLineIds, (array)$statementLineIds);


                try {
                    $result = $this->execute(
                        'account.move.line',
                        'reconcile',
                        [$linesToReconcile],
                        ['context' => ['skip_full_reconcile_check' => true]]
                    );
                    // Handle success
                } catch (Exception $e) {
                    // Log or handle error
                    Log::error('Odoo reconciliation failed: ' . $e->getMessage());
                }
                
            }

       

            // Step 7: Update payment to set is_matched to true if not already
            if (!$isMatched) {
                $this->execute(
                    'account.payment',
                    'write',
                    [[$accountPayment_id], ['is_matched' => true]],
                    []
                );
            }

            // Step 8: Verify invoice state is 'paid'
            if (!empty($invoiceIds)) {
                $invoiceState = $this->execute(
                    'account.move',
                    'read',
                    [$invoiceIds, ['state']],
                    []
                );

                foreach ($invoiceState as $invoice) {
                    if ($invoice['state'] !== 'paid') {
                        Log::warning("Invoice ID {$invoice['id']} state is {$invoice['state']} instead of 'paid'");
                    }
                }
            } else {
                Log::warning("No invoices linked to payment ID $accountPayment_id");
            }

            // Step 9: Return result
            return [
                'bank_reference'=>$bankReference??null,
                'statement_entry_id' => $statementEntryId,
                'entry_id' => $statementMoveId,
                'payment_id' => $accountPayment_id,
                'invoice_state' => !empty($invoiceState) ? $invoiceState[0]['state'] : 'unknown',
                'message' => 'Cheque collection processed successfully, payment marked as matched, and invoice set to paid'
            ];

        } catch (\Exception $e) {
            Log::error('Error in chequeCollection: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Failed to process cheque collection: ' . $e->getMessage()
            ];
        }
    }
    
    public function unlinkBankCollection(int $accountBankStatementId)
    {
        $models = $this->models;
    
        $move_line_ids = $models->execute_kw(
            $this->db,
            $this->uid,
            $this->password,
            'account.bank.statement.line',
            'read',
            [[$accountBankStatementId]],
            ['fields' => ['line_ids']]
        );
        $line_ids = $move_line_ids[0]['line_ids']??[]; // [33352, 33353]

        $models->execute_kw(
            $this->db,
            $this->uid,
            $this->password,
            'account.move.line',
            'remove_move_reconcile',
            [$line_ids]
        );

        // Now unlink works
        $models->execute_kw(
            $this->db,
            $this->uid,
            $this->password,
            'account.bank.statement.line',
            'unlink',
            [[$accountBankStatementId]]
        );
    
    }
	
	
	
	///////////////////////////////////////
	
	
	public function reconcileDownPaymentWithInvoice($downPaymentMoveId, $invoiceMoveId, $accountType = 'receivable')
    {
        // try {
            // Get unreconciled lines from down payment
            $downPaymentLines = $this->getAccountMoveLines($downPaymentMoveId, $accountType);
            
            // Get unreconciled lines from invoice
            $invoiceLines = $this->getAccountMoveLines($invoiceMoveId, $accountType);
		
            if (empty($downPaymentLines)) {
                throw new \Exception("No unreconciled lines found in down payment");
            }

            if (empty($invoiceLines)) {
                throw new \Exception("No unreconciled lines found in invoice");
            }

            // Collect line IDs to reconcile
            $lineIdsToReconcile = [];
            foreach ($downPaymentLines as $line) {
                $lineIdsToReconcile[] = $line['id'];
            }
            foreach ($invoiceLines as $line) {
                $lineIdsToReconcile[] = $line['id'];
            }
			// [1,2,3]
			// ['amount'=>55 , 'id'=>4]

            // Perform reconciliation
            $result = $this->execute(
                'account.move.line',
                'reconcile',
                [$lineIdsToReconcile],
                []
            );

            // Log::info('Odoo reconciliation successful', [
            //     'down_payment_id' => $downPaymentMoveId,
            //     'invoice_id' => $invoiceMoveId,
            //     'reconciled_lines' => $lineIdsToReconcile
            // ]);

            return [
                'success' => true,
                'message' => 'Reconciliation completed successfully',
                'reconciled_line_ids' => $lineIdsToReconcile,
                'result' => $result
            ];

        // } catch (\Exception $e) {
        //     Log::error('Odoo reconciliation failed', [
        //         'error' => $e->getMessage(),
        //         'down_payment_id' => $downPaymentMoveId,
        //         'invoice_id' => $invoiceMoveId
        //     ]);

        //     return [
        //         'success' => false,
        //         'message' => $e->getMessage()
        //     ];
        // }
    }

   


    /**
     * Unreconcile (remove reconciliation)
     * 
     * @param int $moveId
     * @return array
     */
 
	
    public function getAccountMoveLines($moveId, $accountType = 'receivable')
    {
        $accountInternalType = $accountType === 'receivable' 
            ? 'asset_receivable' 
            : 'liability_payable';

        // Get the account.move record
        $move = $this->execute(
            'account.move',
            'search_read',
            [[['id', '=', $moveId]]],
            ['fields' => ['line_ids', 'partner_id']]
        );

        if (empty($move)) {
            throw new \Exception("Move ID {$moveId} not found");
        }

        $lineIds = $move[0]['line_ids'];

        // Get account move lines that are not reconciled
        $lines = $this->execute(
            'account.move.line',
            'search_read',
            [[
                ['id', 'in', $lineIds],
                ['account_id.account_type', '=', $accountInternalType],
                ['reconciled', '=', false]
            ]],
            ['fields' => ['id', 'debit', 'credit', 'amount_residual', 'account_id', 'name']]
        );

        return $lines;
    }

	

	
	
	
	
	
	
	//// partial
	
	
	
	/**
     * Partial reconciliation - Match specific amount from down payment to invoice
     * 
     * @param int $downPaymentMoveId - The account.move ID of the down payment
     * @param int $invoiceMoveId - The account.move ID of the invoice
     * @param float $amountToMatch - The amount to match (e.g., 5000 from 20000)
     * @param string $accountType - 'receivable' for customer, 'payable' for supplier
     * @return array
     */
  
		
	public function partialReconcile($downPaymentMoveId, $invoiceMoveId, $amountToMatch, $accountType = 'receivable')
    {
        try {
            // Get unreconciled lines from down payment
            $downPaymentLines = $this->getAccountMoveLines($downPaymentMoveId, $accountType);
            
            // Get unreconciled lines from invoice
            $invoiceLines = $this->getAccountMoveLines($invoiceMoveId, $accountType);

            if (empty($downPaymentLines)) {
                throw new \Exception("No unreconciled lines found in down payment");
            }

            if (empty($invoiceLines)) {
                throw new \Exception("No unreconciled lines found in invoice");
            }

            // Get the first line from each (usually there's only one receivable/payable line per move)
            $downPaymentLine = $downPaymentLines[0];
            $invoiceLine = $invoiceLines[0];

            // Validate amounts
            if (abs($downPaymentLine['amount_residual']) < $amountToMatch) {
                throw new \Exception("Amount to match ({$amountToMatch}) exceeds available down payment balance (" . abs($downPaymentLine['amount_residual']) . ")");
            }

            if (abs($invoiceLine['amount_residual']) < $amountToMatch) {
                throw new \Exception("Amount to match ({$amountToMatch}) exceeds invoice balance (" . abs($invoiceLine['amount_residual']) . ")");
            }

            // Determine debit and credit lines based on actual amounts
            $debitLineId = null;
            $creditLineId = null;
            $debitLine = null;
            $creditLine = null;

            // The line with debit > 0 is the debit line, the one with credit > 0 is the credit line
            if ($downPaymentLine['debit'] > 0) {
                $debitLineId = $downPaymentLine['id'];
                $debitLine = $downPaymentLine;
                $creditLineId = $invoiceLine['id'];
                $creditLine = $invoiceLine;
            } else {
                $debitLineId = $invoiceLine['id'];
                $debitLine = $invoiceLine;
                $creditLineId = $downPaymentLine['id'];
                $creditLine = $downPaymentLine;
            }

            // Prepare reconciliation data with currency support
            $partialReconcileData = [
                'debit_move_id' => $debitLineId,
                'credit_move_id' => $creditLineId,
                'amount' => $amountToMatch,
            ];
	
            // Add currency amount if lines have currency (for multi-currency support)
            // if (!empty($debitLine['currency_id']) && $debitLine['currency_id'] !== false) {
            //     $partialReconcileData['debit_amount_currency'] = $amountToMatch;
            //     $partialReconcileData['credit_amount_currency'] = $amountToMatch;
            // }
			
			 $partialReconcileData['debit_amount_currency'] = $amountToMatch;
             $partialReconcileData['credit_amount_currency'] = $amountToMatch;
			 
            Log::info('Creating partial reconciliation', [
                'data' => $partialReconcileData,
                'debit_line' => $debitLine,
                'credit_line' => $creditLine
            ]);
			
            $partialReconcileId = $this->execute(
                'account.partial.reconcile',
                'create',
                [$partialReconcileData],
                []
            );
            Log::info('Odoo partial reconciliation successful', [
                'down_payment_id' => $downPaymentMoveId,
                'invoice_id' => $invoiceMoveId,
                'amount_matched' => $amountToMatch,
                'partial_reconcile_id' => $partialReconcileId
            ]);

            // Get updated balances
            $updatedDownPayment = $this->getAccountMoveLines($downPaymentMoveId, $accountType);
            $updatedInvoice = $this->getAccountMoveLines($invoiceMoveId, $accountType);

            return [
                'success' => true,
                'message' => 'Partial reconciliation completed successfully',
                'partial_reconcile_id' => $partialReconcileId,
                'amount_matched' => $amountToMatch,
                'down_payment_remaining' => !empty($updatedDownPayment) ? abs($updatedDownPayment[0]['amount_residual']) : 0,
                'invoice_remaining' => !empty($updatedInvoice) ? abs($updatedInvoice[0]['amount_residual']) : 0
            ];

        } catch (\Exception $e) {
            Log::error('Odoo partial reconciliation failed', [
                'error' => $e->getMessage(),
                'down_payment_id' => $downPaymentMoveId,
                'invoice_id' => $invoiceMoveId,
                'amount_to_match' => $amountToMatch
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

	
     public function matchDownPaymentToMultipleInvoices($downPaymentMoveId, $invoiceMatches, $accountType = 'receivable')
    {
        try {
            $results = [];
            $totalMatched = 0;

            // Get initial down payment balance
            $downPaymentLines = $this->getAccountMoveLines($downPaymentMoveId, $accountType);
            if (empty($downPaymentLines)) {
                throw new \Exception("No unreconciled lines found in down payment");
            }

            $availableBalance = abs($downPaymentLines[0]['amount_residual']);

            // Calculate total amount to match
            $totalToMatch = array_sum(array_column($invoiceMatches, 'amount'));

            if ($totalToMatch > $availableBalance) {
                throw new \Exception("Total amount to match ({$totalToMatch}) exceeds available down payment balance ({$availableBalance})");
            }

            // Process each invoice match
            foreach ($invoiceMatches as $match) {
                $result = $this->partialReconcile(
                    $downPaymentMoveId,
                    $match['invoice_id'],
                    $match['amount'],
                    $accountType
                );

                if ($result['success']) {
                    $totalMatched += $match['amount'];
                    $results[] = [
                        'invoice_id' => $match['invoice_id'],
                        'amount' => $match['amount'],
                        'status' => 'matched',
                        'partial_reconcile_id' => $result['partial_reconcile_id']
                    ];
                } else {
                    $results[] = [
                        'invoice_id' => $match['invoice_id'],
                        'amount' => $match['amount'],
                        'status' => 'failed',
                        'error' => $result['message']
                    ];
                }
            }

            // Get final down payment balance
            $finalDownPayment = $this->getAccountMoveLines($downPaymentMoveId, $accountType);
            $remainingBalance = !empty($finalDownPayment) ? abs($finalDownPayment[0]['amount_residual']) : 0;

            return [
                'success' => true,
                'message' => 'Down payment matched to multiple invoices',
                'total_matched' => $totalMatched,
                'remaining_balance' => $remainingBalance,
                'matches' => $results
            ];

        } catch (\Exception $e) {
            Log::error('Multiple invoice matching failed', [
                'error' => $e->getMessage(),
                'down_payment_id' => $downPaymentMoveId
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
	

    /**
     * Reconcile multiple down payments with one invoice
     * 
     * @param array $downPaymentMoveIds - Array of account.move IDs for down payments
     * @param int $invoiceMoveId - The account.move ID of the invoice
     * @param string $accountType - 'receivable' for customer, 'payable' for supplier
     * @return array
     */
    public function reconcileMultipleDownPayments($downPaymentMoveIds, $invoiceMoveId, $accountType = 'receivable')
    {
        try {
            $lineIdsToReconcile = [];

            // Collect all down payment lines
            foreach ($downPaymentMoveIds as $downPaymentId) {
                $downPaymentLines = $this->getAccountMoveLines($downPaymentId, $accountType);
                foreach ($downPaymentLines as $line) {
                    $lineIdsToReconcile[] = $line['id'];
                }
            }

            // Get invoice lines
            $invoiceLines = $this->getAccountMoveLines($invoiceMoveId, $accountType);
            foreach ($invoiceLines as $line) {
                $lineIdsToReconcile[] = $line['id'];
            }

            if (count($lineIdsToReconcile) < 2) {
                throw new \Exception("Not enough lines to reconcile");
            }

            // Perform reconciliation
            $result = $this->execute(
                'account.move.line',
                'reconcile',
                [$lineIdsToReconcile],
                []
            );

            return [
                'success' => true,
                'message' => 'Multiple down payments reconciled successfully',
                'reconciled_line_ids' => $lineIdsToReconcile,
                'result' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Multiple down payments reconciliation failed', [
                'error' => $e->getMessage(),
                'down_payment_ids' => $downPaymentMoveIds,
                'invoice_id' => $invoiceMoveId
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get reconciliation status
     * 
     * @param int $moveId
     * @return array
     */
    public function getReconciliationStatus($moveId)
    {
        $lines = $this->execute(
            'account.move.line',
            'search_read',
            [[
                ['move_id', '=', $moveId],
                ['account_id.account_type', 'in', ['asset_receivable', 'liability_payable']]
            ]],
            ['fields' => ['id', 'reconciled', 'full_reconcile_id', 'amount_residual', 'amount_residual_currency', 'matched_debit_ids', 'matched_credit_ids', 'debit', 'credit']]
        );

        return $lines;
    }

    /**
     * Unreconcile (remove reconciliation)
     * 
     * @param int $moveId
     * @return array
     */
    public function removeReconciliation($moveId)
    {
        try {
            $lines = $this->getReconciliationStatus($moveId);
            
            $reconciledLines = array_filter($lines, function($line) {
                return $line['reconciled'] === true || !empty($line['matched_debit_ids']) || !empty($line['matched_credit_ids']);
            });

            if (empty($reconciledLines)) {
                return [
                    'success' => false,
                    'message' => 'No reconciled lines found'
                ];
            }

            $lineIds = array_column($reconciledLines, 'id');

            $result = $this->execute(
                'account.move.line',
                'remove_move_reconcile',
                [$lineIds],
                []
            );

            return [
                'success' => true,
                'message' => 'Reconciliation removed successfully',
                'unreconciled_line_ids' => $lineIds
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
	}
}
