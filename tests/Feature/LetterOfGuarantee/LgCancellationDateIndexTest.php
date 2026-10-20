<?php

namespace Tests\Feature\LetterOfGuarantee;

use App\Models\LetterOfGuaranteeIssuance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Cancelled LG issuance rows on the index show a calendar button that
 * opens a read-only modal with cancellation_date.
 */
class LgCancellationDateIndexTest extends TestCase
{
    private ?string $originalDatabase = null;

    protected function tearDown(): void
    {
        if ($this->originalDatabase !== null) {
            config(['database.connections.mysql.database' => $this->originalDatabase]);
            DB::purge('mysql');
        }

        parent::tearDown();
    }

    public function test_cancelled_index_actions_include_the_cancellation_date_modal(): void
    {
        $blade = file_get_contents(resource_path('views/reports/LetterOfGuaranteeIssuance/actions.blade.php'));

        $this->assertMatchesRegularExpression(
            '/@elseif\(\$model->isCancelled\(\)\)[\s\S]*cancellation-date-modal-\{\{ \$model->id \}\}/',
            $blade
        );
        $this->assertStringContainsString('getCancellationDateFormatted()', $blade);
        $this->assertStringContainsString("{{ __('Cancellation Date') }}", $blade);
        $this->assertStringContainsString('fa-calendar-alt', $blade);
        $this->assertStringContainsString('@include(\'reports.LetterOfGuaranteeIssuance.actions\')',
            file_get_contents(resource_path('views/reports/LetterOfGuaranteeIssuance/index.blade.php'))
        );
    }

    public function test_the_cancellation_date_is_formatted_like_the_other_lg_dates(): void
    {
        $letterOfGuaranteeIssuance = new LetterOfGuaranteeIssuance();
        $letterOfGuaranteeIssuance->cancellation_date = '2026-06-01';

        $this->assertSame('01-06-2026', $letterOfGuaranteeIssuance->getCancellationDateFormatted());

        $letterOfGuaranteeIssuance->cancellation_date = null;
        $this->assertNull($letterOfGuaranteeIssuance->getCancellationDateFormatted());
    }

    public function test_the_migration_adds_the_column_and_backfills_cancelled_rows(): void
    {
        $migration = file_get_contents(database_path(
            'migrations/2026_10_20_120000_add_cancellation_date_to_letter_of_guarantee_issuances_table.php'
        ));

        $this->assertStringContainsString(
            "Schema::hasColumn('letter_of_guarantee_issuances', 'cancellation_date')",
            $migration
        );
        $this->assertStringContainsString(
            "date('cancellation_date')->nullable()->after('renewal_date')",
            $migration
        );
        $this->assertStringContainsString('letter_of_guarantee_statements', $migration);
        $this->assertStringContainsString('LetterOfGuaranteeIssuance::FOR_CANCELLATION', $migration);
        $this->assertStringContainsString('LetterOfGuaranteeIssuance::CANCELLED', $migration);
        $this->assertStringContainsString('MIN(`date`)', $migration);
        $this->assertStringContainsString('cancellation_date IS NULL', $migration);
    }

    public function test_the_column_exists_when_the_database_is_reachable(): void
    {
        $this->connectToSmokeDatabase();

        $this->assertTrue(Schema::hasColumn('letter_of_guarantee_issuances', 'cancellation_date'));
    }

    private function connectToSmokeDatabase(): void
    {
        $this->originalDatabase = config('database.connections.mysql.database');
        $candidates = array_values(array_unique(array_filter([
            env('SMOKE_DB'),
            'veroanalysis',
            'veroanalysisb_dev',
            $this->originalDatabase,
        ])));

        foreach ($candidates as $database) {
            config(['database.connections.mysql.database' => $database]);
            DB::purge('mysql');
            try {
                DB::connection('mysql')->getPdo();
                DB::table('letter_of_guarantee_issuances')->limit(1)->get();

                return;
            } catch (\Throwable $e) {
                continue;
            }
        }

        config(['database.connections.mysql.database' => $this->originalDatabase]);
        DB::purge('mysql');
        $this->originalDatabase = null;
        $this->markTestSkipped('Development database not reachable.');
    }
}
