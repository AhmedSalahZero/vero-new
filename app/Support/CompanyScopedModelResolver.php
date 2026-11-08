<?php

namespace App\Support;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Safe resolution of "generic" model endpoints that receive a model class name,
 * a record id, a column name or a relation name from the request.
 *
 * Every lookup is:
 *  - restricted to an explicit per-endpoint allow-list of model names,
 *  - scoped to the company in the route (`{company}`),
 *  - limited to real, non-protected columns and real Eloquent relations.
 *
 * Anything else fails closed (404 for unknown models / records, 422 for bad input).
 */
class CompanyScopedModelResolver
{
	/**
	 * Columns that must never be written from generic endpoints.
	 */
	public const PROTECTED_COLUMNS = [
		'id', 'company_id', 'creator_id', 'created_by', 'updated_by', 'user_id',
		'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token',
		'odoo_id', 'odoo_username', 'odoo_db_password',
	];

	private const IDENTIFIER_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*$/';

	/** @var array<string, array<int,string>> */
	private static array $columnCache = [];

	/**
	 * The company id from the `{company}` route parameter (model or numeric).
	 */
	public static function routeCompanyId(?Request $request = null): ?int
	{
		$request = $request ?: request();
		$route = $request->route();
		if (! $route) {
			return null;
		}
		$company = $route->parameter('company');
		if ($company instanceof Company) {
			return (int) $company->getKey();
		}

		return is_numeric($company) ? (int) $company : null;
	}

	/**
	 * Resolve an allow-listed model name to its fully qualified class.
	 *
	 * @param  array<int,string>  $allowed  short class names under App\Models
	 * @return class-string<Model>
	 */
	public static function modelClass($name, array $allowed): string
	{
		if (! is_string($name) || ! preg_match('/^[A-Z][A-Za-z0-9]*$/', $name) || ! in_array($name, $allowed, true)) {
			abort(404);
		}
		$class = 'App\\Models\\' . $name;
		if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
			abort(404);
		}

		return $class;
	}

	public const INVOICE_MODELS = ['CustomerInvoice', 'SupplierInvoice'];

	/**
	 * Invoice model class from a URL segment ({invoiceModelName} / {modelType}).
	 *
	 * @return class-string<Model>
	 */
	public static function invoiceClass($modelName): string
	{
		return self::modelClass($modelName, self::INVOICE_MODELS);
	}

	/**
	 * Invoice of the route company, or 404.
	 */
	public static function invoiceForCompany($modelName, $id, int $companyId): Model
	{
		return self::findForCompany(self::invoiceClass($modelName), $id, $companyId);
	}

	/**
	 * @return array<int,string>
	 */
	public static function columns(Model $model): array
	{
		$key = $model->getConnectionName() . '|' . $model->getTable();
		if (! isset(self::$columnCache[$key])) {
			self::$columnCache[$key] = Schema::connection($model->getConnectionName())->getColumnListing($model->getTable());
		}

		return self::$columnCache[$key];
	}

	public static function hasColumn(Model $model, string $column): bool
	{
		return in_array($column, self::columns($model), true);
	}

	/**
	 * Find a record of $class that belongs to $companyId, or abort 404.
	 *
	 * @param  class-string<Model>  $class
	 */
	public static function findForCompany(string $class, $id, int $companyId): Model
	{
		/** @var Model $instance */
		$instance = new $class;
		if (! self::hasColumn($instance, 'company_id') || ! is_scalar($id) || ! is_numeric($id)) {
			abort(404);
		}

		return $class::query()->where($instance->getTable() . '.company_id', $companyId)->findOrFail($id);
	}

	public static function belongsToCompany(Model $model, int $companyId): bool
	{
		if (! self::hasColumn($model, 'company_id')) {
			return false;
		}

		return (int) $model->getAttribute('company_id') === $companyId;
	}

	public static function isIdentifier($value): bool
	{
		return is_string($value) && preg_match(self::IDENTIFIER_PATTERN, $value) === 1;
	}

	/**
	 * Abort 422 unless $column is an existing, writable column of $model.
	 */
	public static function assertWritableColumn(Model $model, $column): string
	{
		if (! self::isIdentifier($column)
			|| in_array(strtolower($column), self::PROTECTED_COLUMNS, true)
			|| ! self::hasColumn($model, $column)) {
			abort(422, 'Invalid column.');
		}

		return $column;
	}

	/**
	 * Abort 422 unless $name is an allow-listed Eloquent relation method of $model.
	 * The method is verified through reflection BEFORE it is ever invoked, so names
	 * like delete / truncate / forceDelete can never be called.
	 *
	 * @param  array<int,string>  $allowed
	 */
	public static function assertRelation(Model $model, $name, array $allowed): string
	{
		if (! self::isIdentifier($name)
			|| ! in_array(strtolower($name), array_map('strtolower', $allowed), true)
			|| ! method_exists($model, $name)) {
			abort(422, 'Invalid relation.');
		}

		$method = new ReflectionMethod($model, $name);
		$returnType = $method->getReturnType();
		$isRelationType = $returnType instanceof ReflectionNamedType
			&& ! $returnType->isBuiltin()
			&& is_a($returnType->getName(), Relation::class, true);

		if (! $method->isPublic()
			|| $method->isStatic()
			|| $method->getNumberOfRequiredParameters() > 0
			|| $method->getDeclaringClass()->getName() === Model::class
			|| ! $isRelationType) {
			abort(422, 'Invalid relation.');
		}

		return $method->getName();
	}
}
