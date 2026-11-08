<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * Input guard for the legacy analysis / dashboard / report endpoints that build
 * raw SQL by string concatenation.
 *
 * It does not replace parameter binding (string values are quoted with
 * sqlValue() at each concatenation site); it guarantees that the parts of those
 * queries that cannot be bound — column names, dates, company ids, table names —
 * can only ever contain safe characters:
 *
 *  - identifier keys (type, field, column, ...) must be plain identifiers;
 *  - date keys must look like dates;
 *  - company_id must be digits;
 *  - table-name keys must be one of the analysis tables;
 *  - no other string may contain a backslash or NUL byte (this keeps the
 *    remaining single-quote escaping sound under MySQL's default sql_mode).
 *
 * The middleware scopes itself by controller action, so it can sit in the web
 * group without touching the (partly generated) route definitions.
 */
class GuardAnalysisSqlInput
{
	public const IDENTIFIER_KEYS = [
		'type', 'main_type', 'sub_main_type', 'field', 'column', 'main_field', 'sub_main_field',
		'third_main_field', 'firstColumnName', 'secondColumnName', 'thirdColumnName',
		'mainColumnName', 'mainType', 'subType', 'sales_discounts_fields', 'data_type',
	];

	public const IDENTIFIER_ROUTE_PARAMS = [
		'firstColumn', 'secondColumn', 'thirdColumn', 'columnName', 'modelType', 'currency',
	];

	public const DATE_KEYS = [
		'start_date', 'end_date', 'date', 'date_from', 'date_to', 'startDate', 'endDate', 'previous_date',
	];

	public const TABLE_KEYS = ['filterTableName', 'tableName'];

	public const ALLOWED_TABLES = ['sales_gathering', 'expense_analysis', 'export_analysis'];

	private const FULL_RULE_CONTROLLERS = [
		'App\\Http\\Controllers\\FilterColumnBasedOnAnotherColumnController',
		'App\\Http\\Controllers\\FilterMainTypeBasedOnDatesController',
	];

	private const FULL_RULE_ACTIONS = [
		'App\\Http\\Controllers\\BalancesController@index',
		'App\\Http\\Controllers\\BalancesController@showTotalNetBalanceDetailsReport',
		'App\\Http\\Controllers\\HomeController@incomeStatementDashboard',
	];

	private const DATE_ONLY_CONTROLLERS = [
		'App\\Http\\Controllers\\SalesForecastReport',
		'App\\Http\\Controllers\\QuantitySalesForecastReport',
		'App\\Http\\Controllers\\SeasonalityReport',
		'App\\Http\\Controllers\\QuantitySeasonalityReport',
	];

	private const IDENTIFIER_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*$/';

	// Y-m-d, d-m-Y, m/d/Y, Y-m, m-Y (+ optional time). Digits and separators only.
	private const DATE_PATTERN = '/^(?:\d{1,4}[-\/.]\d{1,2}(?:[-\/.]\d{1,4})?|\d{1,2}[-\/.]\d{4})(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?$/';

	public function handle(Request $request, Closure $next)
	{
		$mode = self::modeFor($request->route());
		if ($mode !== null && ($error = self::violation($request, $mode)) !== null) {
			abort(422, 'Invalid report parameter: ' . $error);
		}

		return $next($request);
	}

	/**
	 * @return 'full'|'dates'|null
	 */
	public static function modeFor($route): ?string
	{
		if (! $route instanceof Route) {
			return null;
		}
		$action = $route->getActionName();
		[$controller, $method] = array_pad(explode('@', $action, 2), 2, null);

		if (str_starts_with($controller, 'App\\Http\\Controllers\\Analysis\\')
			|| in_array($controller, self::FULL_RULE_CONTROLLERS, true)
			|| in_array($action, self::FULL_RULE_ACTIONS, true)
			|| ($controller === 'App\\Http\\Controllers\\HomeController' && is_string($method) && str_starts_with($method, 'dashboard'))) {
			return 'full';
		}
		if (in_array($controller, self::DATE_ONLY_CONTROLLERS, true)) {
			return 'dates';
		}

		return null;
	}

	/**
	 * Returns the name of the first offending parameter, or null when the input is safe.
	 */
	public static function violation(Request $request, string $mode): ?string
	{
		$input = $request->all();

		foreach ($input as $key => $value) {
			if (self::isDateKey((string) $key) && ! self::validDates($value)) {
				return (string) $key;
			}
		}
		if ($mode === 'dates') {
			return null;
		}

		foreach (self::IDENTIFIER_KEYS as $key) {
			if (array_key_exists($key, $input) && ! self::validIdentifiers($input[$key])) {
				return $key;
			}
		}
		$route = $request->route();
		if ($route instanceof Route) {
			foreach (self::IDENTIFIER_ROUTE_PARAMS as $param) {
				$value = $route->parameter($param);
				if ($value !== null && ! is_object($value) && ! self::validIdentifiers($value)) {
					return $param;
				}
			}
		}
		if (array_key_exists('company_id', $input) && ! self::emptyValue($input['company_id'])
			&& ! (is_scalar($input['company_id']) && ctype_digit((string) $input['company_id']))) {
			return 'company_id';
		}
		foreach (self::TABLE_KEYS as $key) {
			if (array_key_exists($key, $input) && ! self::emptyValue($input[$key])
				&& ! in_array($input[$key], self::ALLOWED_TABLES, true)) {
				return $key;
			}
		}
		$path = self::firstUnsafeString($input);

		return $path;
	}

	public static function isDateKey(string $key): bool
	{
		return in_array($key, self::DATE_KEYS, true)
			|| preg_match('/^(start|end)_date_[a-z]+$/', $key) === 1;
	}

	private static function emptyValue($value): bool
	{
		return $value === null || $value === '';
	}

	private static function validDates($value): bool
	{
		if (self::emptyValue($value)) {
			return true;
		}

		return is_string($value) && preg_match(self::DATE_PATTERN, trim($value)) === 1;
	}

	private static function validIdentifiers($value): bool
	{
		if (self::emptyValue($value)) {
			return true;
		}
		if (is_array($value)) {
			foreach ($value as $item) {
				if (! self::validIdentifiers($item)) {
					return false;
				}
			}

			return true;
		}

		return is_string($value) && preg_match(self::IDENTIFIER_PATTERN, $value) === 1;
	}

	/**
	 * Recursively finds a string containing a backslash or NUL. JSON-encoded
	 * arrays/objects (several report forms post them) are decoded first, so
	 * legitimately escaped quotes inside JSON are not rejected.
	 */
	private static function firstUnsafeString($value, string $path = ''): ?string
	{
		if (is_array($value)) {
			foreach ($value as $key => $item) {
				$found = self::firstUnsafeString($item, $path === '' ? (string) $key : $path . '.' . $key);
				if ($found !== null) {
					return $found;
				}
			}

			return null;
		}
		if (! is_string($value)) {
			return null;
		}
		$trimmed = ltrim($value);
		if ($trimmed !== '' && ($trimmed[0] === '[' || $trimmed[0] === '{')) {
			$decoded = json_decode($value, true);
			if (is_array($decoded)) {
				return self::firstUnsafeString($decoded, $path);
			}
		}
		if (str_contains($value, '\\') || str_contains($value, "\0")) {
			return $path === '' ? 'input' : $path;
		}

		return null;
	}
}
