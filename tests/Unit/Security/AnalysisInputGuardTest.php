<?php

namespace Tests\Unit\Security;

use App\Http\Middleware\GuardAnalysisSqlInput;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * GuardAnalysisSqlInput rejects SQL-meaningful input on the legacy raw-SQL
 * report endpoints, and stays out of the way for everything else.
 */
class AnalysisInputGuardTest extends TestCase
{
	private const ANALYSIS_ACTION = 'App\\Http\\Controllers\\Analysis\\SalesGathering\\ZoneAgainstAnalysisReport@result';

	private function request(array $input, string $action = self::ANALYSIS_ACTION, string $uri = 'en/{company}/report', array $params = ['company' => '1']): Request
	{
		$request = Request::create('/'.str_replace('{company}', '1', $uri), 'POST', $input);
		$route = new Route(['POST'], $uri, ['uses' => $action, 'controller' => $action]);
		$route->bind($request);
		foreach ($params as $key => $value) {
			$route->setParameter($key, $value);
		}
		$request->setRouteResolver(fn () => $route);

		return $request;
	}

	private function passes(Request $request): bool
	{
		try {
			(new GuardAnalysisSqlInput())->handle($request, fn () => response('ok'));

			return true;
		} catch (HttpException $e) {
			$this->assertSame(422, $e->getStatusCode());

			return false;
		}
	}

	public static function maliciousInput(): array
	{
		return [
			'identifier with boolean injection' => [['type' => 'zone OR 1=1']],
			'identifier with comment' => [['type' => 'zone--']],
			'identifier subquery' => [['column' => '(select password from users)']],
			'identifier array element' => [['sales_discounts_fields' => ['quantity_discount', 'x) union select 1']]],
			'date breakout' => [['start_date' => "2024-01-01' OR '1'='1"]],
			'date suffix date key' => [['end_date_second' => '2024-01-01; DROP TABLE users']],
			'non numeric company id' => [['company_id' => '1 OR 1=1']],
			'arbitrary table' => [['filterTableName' => 'users']],
			'backslash escaping trick' => [['branches' => ["\\' OR 1=1 -- "]]],
			'backslash inside json array' => [['branches' => [json_encode(["x\\"])]]],
			'null byte' => [['zones' => ["a\0b"]]],
		];
	}

	#[DataProvider('maliciousInput')]
	public function test_malicious_report_input_is_rejected(array $input): void
	{
		$this->assertFalse($this->passes($this->request($input)));
	}

	public function test_route_parameters_are_validated(): void
	{
		$action = 'App\\Http\\Controllers\\BalancesController@showTotalNetBalanceDetailsReport';
		$uri = 'en/{company}/customer-balances/total-net-balance-details/{currency}/{modelType}';
		$this->assertFalse($this->passes($this->request([], $action, $uri, ['company' => '1', 'currency' => 'EGP" OR "1"="1', 'modelType' => 'CustomerInvoice'])));
		$this->assertTrue($this->passes($this->request([], $action, $uri, ['company' => '1', 'currency' => 'EGP', 'modelType' => 'CustomerInvoice'])));
	}

	public function test_legitimate_report_input_passes(): void
	{
		$this->assertTrue($this->passes($this->request([
			'type' => 'product_item',
			'start_date' => '2024-01-01',
			'end_date' => '31-12-2024',
			'date' => '2024-03',
			'previous_date' => '03-2024',
			'date_from' => '03/15/2024',
			'company_id' => '42',
			'branches' => ["Men's Wear", 'Cairo "Downtown"'],
			'zones' => [json_encode(["Men's", 'A "B"'])],
			'sales_discounts_fields' => ['quantity_discount', 'cash_discount'],
			'filterTableName' => 'expense_analysis',
			'interval' => 'monthly',
		])));
	}

	public function test_non_report_endpoints_are_not_affected(): void
	{
		$request = $this->request(['type' => 'free text; with symbols\\', 'description' => 'C:\\path'], 'App\\Http\\Controllers\\CashExpenseController@store');
		$this->assertTrue($this->passes($request));
	}

	public function test_scoping_covers_dashboards_and_date_only_reports(): void
	{
		$dashboard = new Route(['POST'], 'x', ['uses' => 'App\\Http\\Controllers\\HomeController@dashboardCustomers', 'controller' => 'App\\Http\\Controllers\\HomeController@dashboardCustomers']);
		$forecast = new Route(['POST'], 'x', ['uses' => 'App\\Http\\Controllers\\SalesForecastReport@result', 'controller' => 'App\\Http\\Controllers\\SalesForecastReport@result']);
		$other = new Route(['POST'], 'x', ['uses' => 'App\\Http\\Controllers\\HomeController@index', 'controller' => 'App\\Http\\Controllers\\HomeController@index']);

		$this->assertSame('full', GuardAnalysisSqlInput::modeFor($dashboard));
		$this->assertSame('dates', GuardAnalysisSqlInput::modeFor($forecast));
		$this->assertNull(GuardAnalysisSqlInput::modeFor($other));
	}
}
