<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Department;
use App\Models\User;

/**
 * * تاب "Microfinance Departments" في صفحة الـ departments لازم يفتح صفحة تعديل قسم الـ microfinance.
 *
 * * قبل الإصلاح: اللينك كان عليه onclick="return false" ، فالضغط عليه مكانش بيعمل أي حاجة.
 */
class MicrofinanceDepartmentsTabTest extends NonBankingTestCase
{
	public function test_the_microfinance_departments_tab_links_to_the_microfinance_department_edit_page_without_blocking_the_click(): void
	{
		$company = $this->makeCompany('Non Banking Departments', [NON_BANKING_SERVICE]);
		$admin = $this->makeUser(User::SUPER_ADMIN, [$company]);

		$html = $this->actAs($admin)
			->get(route('non.banking.view.departments', ['company' => $company->id]))
			->assertOk()
			->assertViewIs('non_banking_services.departments.index')
			->getContent();

		$microfinanceDepartment = Department::where('company_id', $company->id)->where('type', Department::MICROFINANCE)->firstOrFail();
		$editUrl = route('non.banking.edit.departments', ['company' => $company->id, 'department' => $microfinanceDepartment->id, 'type' => Department::MICROFINANCE]);

		$tab = $this->anchorWithHref($html, $editUrl);
		$this->assertStringContainsString(__('Microfinance Departments'), $tab);
		$this->assertStringNotContainsString('return false', $tab, 'the tab must not cancel its own navigation');
	}

	public function test_the_general_departments_tab_still_switches_the_in_page_tab(): void
	{
		$company = $this->makeCompany('Non Banking Departments', [NON_BANKING_SERVICE]);
		$admin = $this->makeUser(User::SUPER_ADMIN, [$company]);

		$html = $this->actAs($admin)
			->get(route('non.banking.view.departments', ['company' => $company->id]))
			->assertOk()
			->getContent();

		$tab = $this->anchorWithHref($html, '#'.Department::GENERAL);
		$this->assertStringContainsString('data-toggle="tab"', $tab);
		$this->assertStringContainsString('onclick="return false"', $tab);
	}

	public function test_the_page_the_microfinance_tab_opens_renders(): void
	{
		$company = $this->makeCompany('Non Banking Departments', [NON_BANKING_SERVICE]);
		$admin = $this->makeUser(User::SUPER_ADMIN, [$company]);
		$company->syncMicrofinanceDepartments();
		$microfinanceDepartment = Department::where('company_id', $company->id)->where('type', Department::MICROFINANCE)->firstOrFail();

		$this->actAs($admin)
			->get(route('non.banking.edit.departments', ['company' => $company->id, 'department' => $microfinanceDepartment->id, 'type' => Department::MICROFINANCE]))
			->assertOk()
			->assertViewIs('non_banking_services.microfinance-departments.form');
	}

	/** The whole <a ...>...</a> element whose href is exactly $href. */
	private function anchorWithHref(string $html, string $href): string
	{
		$pattern = '/<a\b[^>]*\bhref="'.preg_quote(e($href), '/').'"[^>]*>.*?<\/a>/s';
		$this->assertSame(1, preg_match($pattern, $html, $match), "no link to {$href} on the page");

		return $match[0];
	}
}
