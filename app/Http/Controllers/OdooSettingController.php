<?php
namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\FinancialInstitution;
use App\Models\Partner;
use App\Services\Api\OdooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OdooSettingController
{
    // use GeneralFunctions;
	public function index(Company $company,Request $request)
	{
		$financialInstitutionBanks = FinancialInstitution::onlyForCompany($company->id)->onlyBanks()->get();
        return view('other-odoo-settings.form', [
			'company'=>$company,
			'model'=>$company->odooSetting,
			'financialInstitutionBanks'=>$financialInstitutionBanks
		]);
    }
	public function store(Request $request, Company $company)
	{
		$setting = $company->odooSetting;
		$odooService = $this->odooService($company);
		$taxesColumns = Partner::getTaxesNames();

		/**
		 * * كل نداءات أودو بتحصل الأول ، قبل ما نلمس أي صف في قاعدة
		 * * البيانات. الترتيب ده هو الإصلاح : الكود القديم كان بيمسح
		 * * وهو لسه مش عارف أودو ردّ صح ولا لأ.
		 */
		$submittedRevenues = array_filter((array) $request->get('revenues', []), 'is_array');
		$revenueResults = [];

		foreach ($submittedRevenues as $revenueArr) {
			$code = $revenueArr['odoo_code'] ?? null;
			$odooId = $this->odooAccountId($odooService, $code);

			if (! $odooId) {
				continue;
			}

			$revenueResults[] = [
				'odoo_id' => $odooId,
				'odoo_code' => $code,
				'financial_institution_id' => isset($revenueArr['bank']) && is_numeric($revenueArr['bank']) ? $revenueArr['bank'] : null,
				'company_id' => $company->id,
			];
		}

		/**
		 * * صمام أمان : المستخدم بعت حسابات إيراد وأودو ما حلّش ولا
		 * * واحد منهم ؟ ده أودو واقع أو صلاحيات ناقصة — مش إن الحسابات
		 * * دي بقت غلط.
		 *
		 * * الكود القديم كان بيعمل delete() غير مشروط بعدين insert()
		 * * مشروط بـ count() ، فالحالة دي كانت بتمسح ربط الشركة كله
		 * * وما تدخّلش حاجة مكانه. المستخدم كان بيشوف صفحة نجاح عادية.
		 */
		if ($submittedRevenues !== [] && $revenueResults === []) {
			return back()->withInput()->with(
				'fail',
				__('Odoo Did Not Answer For Any Of The Interest Revenue Accounts, So Nothing Was Changed')
			);
		}

		$result = [];
		$taxOdooIds = [];

		foreach ($request->except(['_token', '_method', 'revenues']) as $key => $value) {
			$odooId = $this->odooAccountId($odooService, $value);

			if ($odooId === null) {
				continue;
			}

			$dbKeyName = str_replace('_code', '_id', $key);
			$result[$dbKeyName] = $odooId;
			$result[$key] = $value;

			if (isset($taxesColumns[$key])) {
				$taxOdooIds[$taxesColumns[$key]] = $odooId;
			}
		}

		/**
		 * * المسح والإدخال وتحديث الضرايب والإعدادات كلهم كتابة واحدة :
		 * * لو أي واحدة فيهم وقعت مفيش حاجة بتتنفّذ.
		 */
		DB::transaction(function () use ($company, $setting, $revenueResults, $result, $taxOdooIds) {
			$company->interestRevenuesAccounts()->delete();

			if ($revenueResults !== []) {
				DB::table('interest_revenue_accounts')->insert($revenueResults);
			}

			foreach ($taxOdooIds as $partnerName => $odooId) {
				Partner::where('company_id', $company->id)
					->where('name', $partnerName)
					->where('is_tax', 1)
					->update(['odoo_id' => $odooId]);
			}

			$setting ? $setting->update($result) : $company->odooSetting()->create($result);
		});

		return redirect()->route('odoo-settings.index', ['company' => $company->id]);
	}

	/**
	 * * نقطة واحدة لبناء الخدمة عشان الاختبار يقدر يحط بديل مكانها
	 */
	protected function odooService(Company $company): OdooService
	{
		return new OdooService($company);
	}

	/**
	 * * بترجّع رقم حساب أودو للكود المبعوت ، و null لو أودو ما ردّش صح.
	 *
	 * * ripcord بيرجّع array فيه faultCode/faultString لما أودو يرفض أو
	 * * يقع ، و الـ array ده truthy — فالكود القديم كان بيعدّي شرط
	 * * if($journal) وبعدين يعمل $journal[0]['id'] عليه و يقع بـ
	 * * "Undefined array key 0". والكود المش موجود بيرجّع [].
	 */
	private function odooAccountId(OdooService $odooService, $code): ?int
	{
		if ($code === null || $code === '' || is_array($code)) {
			return null;
		}

		$journal = $odooService->fetchData('account.account', ['code', 'name'], [[['code', '=', $code]]]);

		if (! is_array($journal) || isset($journal['faultCode']) || isset($journal['faultString'])) {
			return null;
		}

		$odooId = $journal[0]['id'] ?? null;

		return is_numeric($odooId) && (int) $odooId > 0 ? (int) $odooId : null;
	}
	
}
