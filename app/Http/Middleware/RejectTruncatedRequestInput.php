<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * * PHP بيرمي من غير أي رسالة كل حقل في الـ POST بعد حد max_input_vars (الافتراضي 1000).
 * * الفورمات الشهرية بتكبر مع طول الدراسة (مثال: صفحة Microfinance All Branches لدراسة 3 سنين بـ 9 منتجات
 * * بتعدّي 1000 حقل) ، فآخر الفورم كان بيضيع: الحفظ يقع (Undefined array key "flat_rates") أو يتحفظ ناقص.
 * * الـ middleware ده بيرفض الطلب اللي وصل للحد برسالة واضحة بدل ما يتحفظ جزء منه.
 * * طلبات الـ JSON مش بتتأثر: Laravel بيقرا الـ body بنفسه ، والحد بيتطبق على $_POST بس.
 */
class RejectTruncatedRequestInput
{
	private ?int $limit;

	public function __construct(?int $limit = null)
	{
		$this->limit = $limit;
	}

	public function handle(Request $request, Closure $next)
	{
		$limit = $this->limit ?? (int) ini_get('max_input_vars');
		if ($limit > 0 && $request->getRealMethod() === 'POST' && ! $request->isJson() && self::countInputVars($request->request->all()) >= $limit) {
			abort(422, __('This form has more fields than the server accepts (max_input_vars = :limit), so nothing was saved. Please ask the system administrator to raise max_input_vars.', ['limit' => $limit]));
		}

		return $next($request);
	}

	/** every value PHP counted as one input variable: the leaves of the posted (nested) arrays */
	public static function countInputVars(array $input): int
	{
		$count = 0;
		array_walk_recursive($input, function () use (&$count) {
			$count++;
		});

		return $count;
	}
}
