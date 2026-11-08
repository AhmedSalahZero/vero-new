<?php

namespace Tests\Unit\Security;

use App\Helpers\HArr;
use App\Services\Api\Traits\AuthTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Remote code execution through eval():
 * HArr::stringArrayToArray() eval'd json_encode()d request input
 * (sub_items[*][is_percentage_of]) — "{${...}}" interpolation executed code.
 */
class EvalRemovalTest extends TestCase
{
	protected function tearDown(): void
	{
		unset($GLOBALS['__security_pwned']);
		parent::tearDown();
	}

	public static function attackPayloads(): array
	{
		$marker = '$GLOBALS[\'__security_pwned\']=1';

		return [
			'interpolation in json string' => [json_encode(['{${!('.$marker.')}}'])],
			'dollar-brace interpolation' => [json_encode(['${!('.$marker.')}'])],
			'raw php expression' => ['[(function(){'.$marker.';return 1;})()]'],
			'function call' => ['[phpinfo()]'],
			'statement injection' => ['[1]; '.$marker.'; ['],
		];
	}

	#[DataProvider('attackPayloads')]
	public function test_string_array_to_array_never_executes_code(string $payload): void
	{
		ob_start();
		$result = HArr::stringArrayToArray($payload);
		$output = ob_get_clean();

		$this->assertArrayNotHasKey('__security_pwned', $GLOBALS, 'payload was executed');
		$this->assertStringNotContainsString('phpinfo', strtolower((string) $output));
		$this->assertIsArray($result);
	}

	public function test_the_real_storage_format_is_parsed_like_before(): void
	{
		$this->assertSame(['1', '2', 'all'], HArr::stringArrayToArray(json_encode((array) ['1', '2', 'all'])));
		$this->assertSame([5, 7], HArr::stringArrayToArray('[5,7]'));
		$this->assertSame([], HArr::stringArrayToArray(''));
		$this->assertSame([], HArr::stringArrayToArray(null));
		$this->assertSame(['1', '2'], HArr::stringArrayToArray("['1','2']"));
		$this->assertSame(['3', '4'], HArr::stringArrayToArray('array(3, 4)'));
		$this->assertSame(["Men's"], HArr::stringArrayToArray(json_encode(["Men's"])));
	}

	public function test_arithmetic_evaluator_only_evaluates_numbers(): void
	{
		$this->assertEquals(7, HArr::evaluateArithmetic('1+2*3'));
		$this->assertEquals(1000, HArr::evaluateArithmetic('1.5E+3-500'));
		$this->assertEquals(-4, HArr::evaluateArithmetic('-(2+2)'));
		$this->assertEquals(0, HArr::evaluateArithmetic(''));

		ob_start();
		$this->assertSame(0, HArr::evaluateArithmetic('system("id")'));
		$this->assertSame(0, HArr::evaluateArithmetic('1+'.'$GLOBALS[\'__security_pwned\']=1'));
		$this->assertSame(0, HArr::evaluateArithmetic('phpinfo()'));
		$output = ob_get_clean();

		$this->assertSame('', $output);
		$this->assertArrayNotHasKey('__security_pwned', $GLOBALS);
	}

	public function test_odoo_selection_literal_is_parsed_without_eval(): void
	{
		$this->assertSame(
			[['draft', 'Draft'], ['posted', 'Posted']],
			AuthTrait::parseSelectionLiteral("[('draft', 'Draft'), ('posted', 'Posted')]")
		);
		$this->assertSame([['a', 'A']], AuthTrait::parseSelectionLiteral('[["a","A"]]'));
		$this->assertSame([], AuthTrait::parseSelectionLiteral('[__import__("os").system("id")]'));
		$this->assertSame([], AuthTrait::parseSelectionLiteral('phpinfo()'));
	}

	public function test_request_driven_code_paths_contain_no_eval(): void
	{
		$sources = [
			app_path('Helpers/HArr.php') => 1, // only inside evaluateArithmetic(), behind a strict whitelist
			app_path('Services/Api/Traits/AuthTrait.php') => 0,
			app_path('Models/Traits/Mutators/FinancialStatementAbleMutator.php') => 0,
		];
		foreach ($sources as $file => $allowed) {
			$code = preg_replace('#//[^\n]*|/\*.*?\*/#s', '', (string) file_get_contents($file));
			$this->assertSame($allowed, preg_match_all('/\beval\s*\(/', $code), basename($file).' must not eval request data');
		}
	}
}
