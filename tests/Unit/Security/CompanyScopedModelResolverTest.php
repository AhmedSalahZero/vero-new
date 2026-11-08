<?php

namespace Tests\Unit\Security;

use App\Models\RevenueBusinessLine;
use App\Models\User;
use App\Support\CompanyScopedModelResolver as Resolver;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CompanyScopedModelResolverTest extends TestCase
{
	private function httpStatusOf(callable $callback): ?int
	{
		try {
			$callback();

			return null;
		} catch (HttpException $e) {
			return $e->getStatusCode();
		}
	}

	public static function rejectedModelNames(): array
	{
		return [
			'not allow-listed' => ['User'],
			'namespace traversal' => ['..\\User'],
			'fully qualified' => ['\\App\\Models\\User'],
			'lowercase' => ['revenueBusinessLine'],
			'non string' => [['RevenueBusinessLine']],
			'empty' => [''],
		];
	}

	#[DataProvider('rejectedModelNames')]
	public function test_model_names_outside_the_allow_list_are_rejected($name): void
	{
		$this->assertSame(404, $this->httpStatusOf(fn () => Resolver::modelClass($name, ['RevenueBusinessLine'])));
	}

	public function test_allow_listed_model_resolves(): void
	{
		$this->assertSame(RevenueBusinessLine::class, Resolver::modelClass('RevenueBusinessLine', ['RevenueBusinessLine']));
	}

	public function test_only_real_relations_can_be_invoked(): void
	{
		$model = new RevenueBusinessLine();
		$allowed = ['serviceCategories', 'delete', 'truncate', 'forceDelete', 'getTable', 'company'];

		$this->assertSame('serviceCategories', Resolver::assertRelation($model, 'serviceCategories', $allowed));
		$this->assertSame('serviceCategories', Resolver::assertRelation($model, 'SERVICECATEGORIES', $allowed), 'PHP method names are case-insensitive');
		// Even if a destructive name slipped into an allow-list, reflection refuses it.
		foreach (['delete', 'truncate', 'forceDelete', 'getTable'] as $method) {
			$this->assertSame(422, $this->httpStatusOf(fn () => Resolver::assertRelation($model, $method, $allowed)), $method);
		}
		$this->assertSame(422, $this->httpStatusOf(fn () => Resolver::assertRelation($model, 'serviceItems', $allowed)), 'not allow-listed');
		$this->assertSame(422, $this->httpStatusOf(fn () => Resolver::assertRelation($model, 'service-categories', $allowed)));
	}

	public function test_protected_columns_list_covers_ownership_and_secrets(): void
	{
		foreach (['id', 'company_id', 'created_by', 'user_id', 'password', 'odoo_db_password', 'created_at'] as $column) {
			$this->assertContains($column, Resolver::PROTECTED_COLUMNS);
		}
		$this->assertSame(422, $this->httpStatusOf(fn () => Resolver::assertWritableColumn(new User(), 'name; DROP TABLE users')));
		$this->assertSame(422, $this->httpStatusOf(fn () => Resolver::assertWritableColumn(new User(), 'company_id')));
	}
}
