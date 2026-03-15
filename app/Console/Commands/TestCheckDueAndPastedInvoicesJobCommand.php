<?php

namespace App\Console\Commands;

use App\Jobs\CheckDueAndPastedInvoicesJob;
use App\Models\Cheque;
use App\Models\Company;
use App\Models\PayableCheque;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCheckDueAndPastedInvoicesJobCommand extends Command
{
  
    protected $signature = 'test:test1 {company_id?}';

    
    protected $description = 'Seed minimal data and run CheckDueAndPastedInvoicesJob to test all notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $companyId = $this->argument('company_id');

        if (!$companyId) {
            $companyId = Company::query()->value('id');
        }

        /** @var Company|null $company */
        $company = $companyId ? Company::find($companyId) : null;

        if (!$company) {
            $this->error('No company found. Please pass a valid {company_id} argument.');
            return 1;
        }

        $this->info('Using company ID: ' . $company->id);

        $dateFormat = 'Y-m-d';
        $today = Carbon::today()->format($dateFormat);
        $yesterday = Carbon::yesterday()->format($dateFormat);
        $tomorrow = Carbon::tomorrow()->format($dateFormat);

        // نحاول نخلي كل الداتا تقع جوه الرينجات اللي الجوب بيستخدمها
        $customerInvoiceComingDueDays = (int) $company->getCustomerPastDuesInvoicesNotificationsDays();
        $supplierInvoiceComingDueDays = (int) $company->getSupplierPastDuesInvoicesNotificationsDays();
        $comingReceivableChequesDays = (int) $company->getComingReceivableChequesNotificationDays();
        $comingPayableChequeNotificationDays = (int) $company->getComingPayableChequeNotificationDays();

        // نضمن على الأقل 1 يوم علشان between مايبقاش فاضي
        $customerInvoiceComingDueDays = max($customerInvoiceComingDueDays, 1);
        $supplierInvoiceComingDueDays = max($supplierInvoiceComingDueDays, 1);
        $comingReceivableChequesDays = max($comingReceivableChequesDays, 1);
        $comingPayableChequeNotificationDays = max($comingPayableChequeNotificationDays, 1);

        $upcomingCustomerInvoiceDueDate = Carbon::today()->addDays(1)->format($dateFormat);
        $upcomingSupplierInvoiceDueDate = Carbon::today()->addDays(1)->format($dateFormat);
        $upcomingReceivableChequeDueDate = Carbon::today()->addDays(1)->format($dateFormat);
        $upcomingPayableChequeDueDate = Carbon::today()->addDays(1)->format($dateFormat);

        DB::beginTransaction();

        try {
            // Partner واحد نستخدمه في كل الداتا
            $partnerId = DB::table('partners')->insertGetId([
                'odoo_id' => null,
                'company_id' => $company->id,
                'name' => 'Test Partner For Notifications ' . now()->timestamp,
                'is_customer' => true,
                'is_supplier' => true,
                'is_employee' => 0,
                'is_shareholder' => 0,
                'is_other_partner' => 0,
                'is_subsidiary_company' => 0,
                'is_tax' => false,
            ]);

            // ====== Customer Invoices (past / current / upcoming) ======
            DB::table('customer_invoices')->insert([
                // Past due
                [
                    'company_id' => $company->id,
                    'customer_id' => $partnerId,
                    'customer_name' => 'Test Customer Past',
                    'invoice_date' => $yesterday,
                    'invoice_due_date' => $yesterday,
                    'invoice_number' => 'CUST-PAST-' . now()->timestamp,
                    'invoice_amount' => '1000',
                    'currency' => 'EGP',
                    'net_balance' => '1000',
                    'invoice_status' => 'not_due_yet',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                // Current due (today)
                [
                    'company_id' => $company->id,
                    'customer_id' => $partnerId,
                    'customer_name' => 'Test Customer Today',
                    'invoice_date' => $today,
                    'invoice_due_date' => $today,
                    'invoice_number' => 'CUST-TODAY-' . now()->timestamp,
                    'invoice_amount' => '2000',
                    'currency' => 'EGP',
                    'net_balance' => '2000',
                    'invoice_status' => 'not_due_yet',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                // Upcoming (within coming days window)
                [
                    'company_id' => $company->id,
                    'customer_id' => $partnerId,
                    'customer_name' => 'Test Customer Upcoming',
                    'invoice_date' => $today,
                    'invoice_due_date' => $upcomingCustomerInvoiceDueDate,
                    'invoice_number' => 'CUST-UP-' . now()->timestamp,
                    'invoice_amount' => '3000',
                    'currency' => 'EGP',
                    'net_balance' => '3000',
                    'invoice_status' => 'not_due_yet',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // ====== Supplier Invoices (past / current / upcoming) ======
            DB::table('supplier_invoices')->insert([
                // Past due
                [
                    'company_id' => $company->id,
                    'supplier_id' => $partnerId,
                    'supplier_name' => 'Test Supplier Past',
                    'invoice_date' => $yesterday,
                    'invoice_due_date' => $yesterday,
                    'invoice_number' => 'SUP-PAST-' . now()->timestamp,
                    'invoice_amount' => '1500',
                    'currency' => 'EGP',
                    'net_balance' => '1500',
                    'invoice_status' => 'not_due_yet',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                // Current due (today)
                [
                    'company_id' => $company->id,
                    'supplier_id' => $partnerId,
                    'supplier_name' => 'Test Supplier Today',
                    'invoice_date' => $today,
                    'invoice_due_date' => $today,
                    'invoice_number' => 'SUP-TODAY-' . now()->timestamp,
                    'invoice_amount' => '2500',
                    'currency' => 'EGP',
                    'net_balance' => '2500',
                    'invoice_status' => 'not_due_yet',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                // Upcoming
                [
                    'company_id' => $company->id,
                    'supplier_id' => $partnerId,
                    'supplier_name' => 'Test Supplier Upcoming',
                    'invoice_date' => $today,
                    'invoice_due_date' => $upcomingSupplierInvoiceDueDate,
                    'invoice_number' => 'SUP-UP-' . now()->timestamp,
                    'invoice_amount' => '3500',
                    'currency' => 'EGP',
                    'net_balance' => '3500',
                    'invoice_status' => 'not_due_yet',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // ====== Receivable Cheques (cheques + money_received) ======
            $moneyReceivedId = DB::table('money_received')->insertGetId([
                'odoo_reference' => null,
                'journal_entry_id' => null,
                'account_bank_statement_line_id' => null,
                'transaction_type' => 'test',
                'has_unapplied_or_down_payment' => false,
                'odoo_error_message' => null,
                'synced_with_odoo' => true,
                'advanced_opening_balance_id' => null,
                'odoo_id' => null,
                'odoo_move_id' => null,
                'partner_type' => 'is_customer',
                'partner_id' => $partnerId,
                'is_reviewed' => false,
                'reviewed_by' => null,
                'money_type' => 'money-received',
                'down_payment_type' => null,
                'down_payment_settlement_date' => null,
                'contract_id' => null,
                'opening_balance_id' => null,
                'type' => 'test',
                'receiving_date' => $today,
                'received_amount' => 5000,
                'received_amount_in_main_currency' => 5000,
                'total_withhold_amount' => 0,
                'total_withhold_amount_in_main_currency' => 0,
                'amount_in_invoice_currency' => 5000,
                'currency' => 'EGP',
                'receiving_currency' => 'EGP',
                'exchange_rate' => 1,
                'user_id' => null,
                'company_id' => $company->id,
                'comment_ar' => 'Test money received',
                'comment_en' => 'Test money received',
                'created_at' => now(),
                'updated_at' => now(),
                'user_comment' => null,
            ]);

            // Past due cheque
            DB::table('cheques')->insert([
                [
                    'branch_id' => null,
                    'cheque_number' => 'CHQ-PAST-' . now()->timestamp,
                    'status' => Cheque::IN_SAFE,
                    'money_received_id' => $moneyReceivedId,
                    'drawee_bank_id' => 9,
                    'drawl_bank_id' => 9,
                    'account_type' => 'test',
                    'account_number' => null,
                    'due_date' => $yesterday,
                    'deposit_date' => null,
                    'days_count' => 0,
                    'expected_collection_date' => null,
                    'actual_collection_date' => null,
                    'clearance_days' => 0,
                    'account_balance' => 0,
                    'collection_fees' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'company_id' => $company->id,
                ],
                // Current due cheque (today)
                [
                    'branch_id' => null,
                    'cheque_number' => 'CHQ-TODAY-' . now()->timestamp,
                    'status' => Cheque::IN_SAFE,
                    'money_received_id' => $moneyReceivedId,
                    'drawee_bank_id' => 9,
                    'drawl_bank_id' => 9,
                    'account_type' => 'test',
                    'account_number' => null,
                    'due_date' => $today,
                    'deposit_date' => null,
                    'days_count' => 0,
                    'expected_collection_date' => null,
                    'actual_collection_date' => null,
                    'clearance_days' => 0,
                    'account_balance' => 0,
                    'collection_fees' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'company_id' => $company->id,
                ],
                // Upcoming cheque
                [
                    'branch_id' => null,
                    'cheque_number' => 'CHQ-UP-' . now()->timestamp,
                    'status' => Cheque::IN_SAFE,
                    'money_received_id' => $moneyReceivedId,
                    'drawee_bank_id' => 9,
                    'drawl_bank_id' => 9,
                    'account_type' => 'test',
                    'account_number' => null,
                    'due_date' => $upcomingReceivableChequeDueDate,
                    'deposit_date' => null,
                    'days_count' => 0,
                    'expected_collection_date' => null,
                    'actual_collection_date' => null,
                    'clearance_days' => 0,
                    'account_balance' => 0,
                    'collection_fees' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'company_id' => $company->id,
                ],
            ]);

            // ====== Payable Cheques (payable_cheques + money_payments) ======
            $currentMoneyPaymentId = DB::table('money_payments')->insertGetId([
                'odoo_reference' => null,
                'journal_entry_id' => null,
                'account_bank_statement_line_id' => null,
                'transaction_type' => 'test',
                'has_unapplied_or_down_payment' => false,
                'odoo_error_message' => null,
                'synced_with_odoo' => true,
                'advanced_opening_balance_id' => null,
                'odoo_id' => null,
                'odoo_move_id' => null,
                'partner_type' => 'is_supplier',
                'partner_id' => $partnerId,
                'is_reviewed' => false,
                'reviewed_by' => null,
                'money_type' => 'money-payment',
                'down_payment_type' => null,
                'down_payment_settlement_date' => null,
                'contract_id' => null,
                'opening_balance_id' => null,
                'type' => 'test',
                'delivery_date' => $today,
                'paid_amount' => 4000,
                'paid_amount_in_main_currency' => 4000,
                'total_withhold_amount' => 0,
                'total_withhold_amount_in_main_currency' => 0,
                'amount_in_invoice_currency' => 4000,
                'currency' => 'EGP',
                'payment_currency' => 'EGP',
                'exchange_rate' => 1,
                'user_id' => null,
                'company_id' => $company->id,
                'comment_ar' => 'Test money payment current',
                'comment_en' => 'Test money payment current',
                'created_at' => now(),
                'updated_at' => now(),
                'user_comment' => null,
            ]);

            $comingMoneyPaymentId = DB::table('money_payments')->insertGetId([
                'odoo_reference' => null,
                'journal_entry_id' => null,
                'account_bank_statement_line_id' => null,
                'transaction_type' => 'test',
                'has_unapplied_or_down_payment' => false,
                'odoo_error_message' => null,
                'synced_with_odoo' => true,
                'advanced_opening_balance_id' => null,
                'odoo_id' => null,
                'odoo_move_id' => null,
                'partner_type' => 'is_supplier',
                'partner_id' => $partnerId,
                'is_reviewed' => false,
                'reviewed_by' => null,
                'money_type' => 'money-payment',
                'down_payment_type' => null,
                'down_payment_settlement_date' => null,
                'contract_id' => null,
                'opening_balance_id' => null,
                'type' => 'test',
                'delivery_date' => $today,
                'paid_amount' => 6000,
                'paid_amount_in_main_currency' => 6000,
                'total_withhold_amount' => 0,
                'total_withhold_amount_in_main_currency' => 0,
                'amount_in_invoice_currency' => 6000,
                'currency' => 'EGP',
                'payment_currency' => 'EGP',
                'exchange_rate' => 1,
                'user_id' => null,
                'company_id' => $company->id,
                'comment_ar' => 'Test money payment upcoming',
                'comment_en' => 'Test money payment upcoming',
                'created_at' => now(),
                'updated_at' => now(),
                'user_comment' => null,
            ]);

            // Current payable cheque (<= today)
            DB::table('payable_cheques')->insert([
                [
                    'company_id' => $company->id,
                    'cheque_number' => 'PAY-CHQ-CUR-' . now()->timestamp,
                    'status' => PayableCheque::PENDING,
                    'money_payment_id' => $currentMoneyPaymentId,
                    'cash_expense_id' => null,
                    'delivery_bank_id' => 9,
                    'account_type' => 'test',
                    'account_number' => null,
                    'due_date' => $yesterday,
                    'delivery_date' => $today,
                    'actual_payment_date' => null,
                    'account_balance' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                // Coming payable cheque (between day after today and afterIntervalDate)
                [
                    'company_id' => $company->id,
                    'cheque_number' => 'PAY-CHQ-UP-' . now()->timestamp,
                    'status' => PayableCheque::PENDING,
                    'money_payment_id' => $comingMoneyPaymentId,
                    'cash_expense_id' => null,
                    'delivery_bank_id' => 9,
                    'account_type' => 'test',
                    'account_number' => null,
                    'due_date' => $upcomingPayableChequeDueDate,
                    'delivery_date' => $today,
                    'actual_payment_date' => null,
                    'account_balance' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error seeding test data: ' . $e->getMessage());
            throw $e;
        }

        // نمسح أي Notifications قديمة للشركة دي (الجوب كمان بيعمل كده في الأول)
        DB::table('notifications')->where('notifiable_id', $company->id)->delete();

        // Run the job synchronously so نقدر نشوف النتيجة فوراً
        (new CheckDueAndPastedInvoicesJob($company->id))->handle();

        $totalNotifications = DB::table('notifications')
            ->where('notifiable_id', $company->id)
            ->count();

        $this->info('Job executed successfully.');
        $this->info('Total notifications for company ' . $company->id . ': ' . $totalNotifications);
        $this->info('You can now inspect the notifications table or the UI to verify each notification payload.');

        return 0;
    }
}
