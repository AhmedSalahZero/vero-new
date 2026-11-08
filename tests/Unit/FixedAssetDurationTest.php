<?php

namespace Tests\Unit;

use App\Models\NonBankingService\FixedAsset as NonBankingFixedAsset;
use App\Models\PropertyManagement\FixedAsset as PropertyManagementFixedAsset;
use App\ReadyFunctions\StraightMethodService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * CalculateFixedLoanAtEndService بياخد $ffe من غير type وبينادي getDuration عليه،
 * فلو الميثود اتشالت من أي موديل الغلطة مش هتظهر غير وقت حفظ الأصول الثابتة.
 */
class FixedAssetDurationTest extends TestCase
{
    public static function fixedAssetModels(): array
    {
        return [
            'non banking service' => [NonBankingFixedAsset::class],
            'property management' => [PropertyManagementFixedAsset::class],
        ];
    }

    #[DataProvider('fixedAssetModels')]
    public function test_fixed_asset_has_zero_execution_duration(string $modelClass): void
    {
        $this->assertSame(0, (new $modelClass)->getDuration());
    }

    public function test_zero_duration_books_full_amount_in_the_purchase_month(): void
    {
        $result = (new StraightMethodService)->calculateStraightAmount(20000000.0, 5, 0);

        $this->assertSame([5 => 20000000.0], $result);
    }
}
