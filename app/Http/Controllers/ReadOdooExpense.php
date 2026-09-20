<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashExpense;
use App\Models\Company;
use App\Models\FinancialInstitutionAccount;
use App\Models\OdooExpense;
use App\Models\Partner;
use App\Services\Api\ExpensePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ReadOdooExpense extends Controller
{
	public function handle(Request $request,  Company $company)
	{
		$startDate = $request->get('odoo_start_date');
		$endDate = $request->get('odoo_end_date');
		$odooExpensePayment = $this->expenseService($company);
		$fields = ['id','write_date','currency_id','expense_line_ids', 'name', 'state', 'payment_state', 'employee_id', 'total_amount', 'account_move_ids', 'journal_id','payment_method_line_id', 'payment_mode'];
		
		$filters = [[['state','=','approve'],['payment_state','=','not_paid'],
			['write_date', '<=', $endDate],['write_date', '>=', $startDate]
		]];
		
		$odooExpenses = $odooExpensePayment->fetchData('hr.expense.sheet',$fields,$filters);

		/**
		 * * أودو ما ردّش صح ؟ بنوقف قبل أي مسح.
		 *
		 * * ripcord بيرجّع array فيه faultString لما أودو يرفض أو يقع ، و
		 * * array_column() عليه بترجّع [] — و الكود القديم كان بياخد الـ []
		 * * دي على إنها "مفيش مصروفات" و يمسح كل اللي عندنا.
		 */
		if (! $this->odooAnswered($odooExpenses)) {
			Log::warning('Skipped the Odoo expense import: Odoo did not answer', [
				'company_id' => $company->id,
			]);

			return redirect()->back()->with('fail', __('Odoo Did Not Answer, So Nothing Was Changed'));
		}

		/**
		 * * المسح بيتقرر بسؤال أودو عن صفوفنا بالتحديد ، مش بغياب الصف من
		 * * نتيجة الفترة المطلوبة.
		 *
		 * * الكود القديم كان بيقارن كل صفوفنا (من غير أي فلتر تاريخ) بنتيجة
		 * * استعلام محصور بالفترة اللي المستخدم كتبها — فاختيار فترة ضيقة
		 * * كان بيمسح كل اللي بره الفترة ، وبيستدعي destroy() على المصروفات
		 * * النقدية المربوطة بيهم.
		 */
		$oldIds = OdooExpense::whereNotNull('odoo_id')
			->where('company_id',$company->id)
			->pluck('odoo_id')
			->map(fn ($id) => (int) $id)
			->all();

		if ($oldIds) {
			$stillPending = $this->expenseSheetIdsStillApprovedAndUnpaid($odooExpensePayment,$oldIds);

			if (is_null($stillPending)) {
				Log::warning('Skipped the deleted-expense sweep: Odoo did not answer the existence check', [
					'company_id' => $company->id,
					'checked_expenses' => count($oldIds),
				]);

				return redirect()->back()->with('fail', __('Odoo Did Not Answer, So Nothing Was Changed'));
			}

			foreach (array_diff($oldIds,$stillPending) as $odooId) {
				$cashExpense = CashExpense::where('company_id',$company->id)->where('odoo_id',$odooId)->first();
				if($cashExpense){
					(new CashExpenseController)->destroy($company,$cashExpense);
				}
				OdooExpense::where('company_id',$company->id)->where('odoo_id',$odooId)->delete();
			}
		}
		foreach($odooExpenses as $odooExpense){
			$odooId = $odooExpense['id'];
			$odooPartnerId = $odooExpense['employee_id'][0] ;
			$odooPartnerName = $odooExpense['employee_id'][1];
			Partner::handlePartnerForOdoo($odooPartnerId ,$odooPartnerName,false ,false,true,false,$company->id );
			$journalId = $odooExpense['journal_id'][0] ;
	//		$journalName = $odooExpense['journal_id'][1] ;
			$accountJournalRows = $odooExpensePayment->fetchData('account.journal',[],[[['id','=',$journalId]]]);

			/**
			 * * [0] على رد فاضي أو رد خطأ = "Undefined array key 0" في نُصّ
			 * * الحلقة ، بعد ما المسح فوق يكون اتنفّذ. بنسيب الصف ده و نكمّل.
			 */
			if (! $this->odooAnswered($accountJournalRows) || ! isset($accountJournalRows[0]['type'])) {
				Log::warning('Skipped one Odoo expense: its journal could not be read', [
					'company_id' => $company->id,
					'odoo_expense_id' => $odooId,
					'journal_id' => $journalId,
				]);

				continue;
			}

			$accountJournal = $accountJournalRows[0];
			$additionalData = [
				'account_number'=>null ,
				'bank_name'=>null 
			];
			if($accountJournal['type'] == 'bank'){
			$odooId = $accountJournal['id'];
			$financialInstitutionAccount = FinancialInstitutionAccount::where('company_id',$company->id)->where('odoo_id',$odooId)->first();
			$deliveryBankName = $financialInstitutionAccount->getFinancialInstitutionName();
			$accountNumber = $financialInstitutionAccount->getAccountNumber();
			$additionalData['account_number']= $accountNumber ;
			$additionalData['bank_name']= $deliveryBankName ;
			}elseif($accountJournal['type'] == 'cash'){
				$deliveryBranchName = Branch::getNameFromOdooId($company->id,$accountJournal['id']);
				$additionalData['bank_name']= $deliveryBranchName ;
				$additionalData['account_number']= $accountJournal['default_account_id'][1] ;
			}
			$data = array_merge($additionalData, [
				'odoo_id'=>$odooId,
				'company_id'=>$company->id ,
				'name'=>$odooExpense['name'],
				'odoo_currency_id'=>$odooExpense['currency_id'][0],
				'state'=>$odooExpense['state'],
				'payment_state'=>$odooExpense['payment_state'],
				'odoo_employee_id'=>$odooPartnerId,
				'total_amount'=>$odooExpense['total_amount'],
				'account_move_ids'=>$odooExpense['account_move_ids'][0],
				'journal_id'=>$journalId,
				'payment_method_line_id'=>$odooExpense['payment_method_line_id'][0],
				'payment_mode'=>$odooExpense['payment_mode'][1]
			]);
			$odooExpense = OdooExpense::where('company_id',$company->id)->where('odoo_id',$odooId)->first();
			if($odooExpense){
				$odooExpense->update($data);
			}else{
				OdooExpense::create($data);
			}
		}
		return redirect()->back()->with('success',__('Read Approved Expenses Has Been Completed'));
		
	}

	/**
	 * * نقطة واحدة لبناء الخدمة عشان الاختبار يقدر يحط بديل مكانها
	 */
	protected function expenseService(Company $company): ExpensePayment
	{
		return new ExpensePayment($company);
	}

	/**
	 * * رد أودو سليم ؟ الـ fault بييجي array بـ faultCode/faultString و هو
	 * * truthy ، فمجرد if($x) مش كفاية.
	 */
	private function odooAnswered($response): bool
	{
		return is_array($response)
			&& ! isset($response['faultCode'])
			&& ! isset($response['faultString']);
	}

	/**
	 * * بتسأل أودو عن أرقامنا بالتحديد : أنهي واحد لسه approve و not_paid ؟
	 * * بترجّع null لو أودو ما ردّش — و ساعتها مابنمسحش حاجة.
	 *
	 * @param  list<int>  $odooIds
	 * @return list<int>|null
	 */
	private function expenseSheetIdsStillApprovedAndUnpaid(ExpensePayment $odooExpensePayment, array $odooIds): ?array
	{
		$rows = $odooExpensePayment->fetchData('hr.expense.sheet',['id'],[[
			['id','in',array_values($odooIds)],
			['state','=','approve'],
			['payment_state','=','not_paid'],
		]]);

		if (! $this->odooAnswered($rows)) {
			return null;
		}

		return array_map('intval',array_column($rows,'id'));
	}
}
