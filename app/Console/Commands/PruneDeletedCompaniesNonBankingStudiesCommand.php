<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * * المرحلة 7: دراسات الـ non-banking اللي شركتها اتمسحت. قبل V9 (المرحلة 3) مسح الشركة مكانش بيمسح دراساتها من قاعدة
 * * الـ non-banking ، فداتاها فضلت (زي دراسة 85: شركة 31 اتمسحت). الأمر بيعرضها بس ، ومع --force بيمسح كل صفوف كل
 * * دراسة منهم بنفس أمر مسح الدراسة (delete:study).
 * *   php artisan non-banking:prune-deleted-companies            (عرض بس)
 * *   php artisan non-banking:prune-deleted-companies --force    (مسح)
 */
class PruneDeletedCompaniesNonBankingStudiesCommand extends Command
{
	protected $signature = 'non-banking:prune-deleted-companies {--force : delete the studies listed (without it nothing is deleted)}';

	protected $description = 'List (and with --force delete) the non-banking studies whose company no longer exists';

	public function handle(): int
	{
		$studies = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('studies')->orderBy('id')->get(['id', 'company_id', 'name']);
		$companies = Company::query()->pluck('id')->map(fn ($id) => (int) $id)->flip();
		$orphans = $studies->filter(fn ($study) => ! $companies->has((int) $study->company_id))->values();

		$this->table(['study', 'company (deleted)', 'name'], $orphans->map(fn ($s) => [$s->id, $s->company_id, $s->name])->all());
		if (! $this->option('force')) {
			$this->line($orphans->count().' studies of deleted companies. Nothing deleted: add --force to delete them.');

			return self::SUCCESS;
		}
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$deleted = 0;
		$failed = [];
		foreach ($orphans as $study) {
			// every row of the study (delete:study), then the study itself, which has no study_id column — only when no
			// row of it is left: delete:study gives up on a table quietly, and once the study row is gone its rows left
			// could not be found again. One transaction per study.
			$nb->beginTransaction();
			Artisan::call('delete:study', ['study_id' => $study->id, '--connection' => NON_BANKING_SERVICE_CONNECTION_NAME]);
			$left = $this->rowsLeft((int) $study->id);
			if ($left) {
				$nb->rollBack();
				$failed[] = $study->id;
				$this->error("study {$study->id}: rows are left in ".implode(', ', array_keys($left)).': nothing of it was deleted.');

				continue;
			}
			$nb->table('studies')->where('id', $study->id)->delete();
			$nb->commit();
			$deleted++;
		}
		$this->line("{$deleted} studies of deleted companies deleted".($failed ? ', '.count($failed).' kept (see above).' : '.'));

		return $failed ? self::FAILURE : self::SUCCESS;
	}

	/** @return array<string, int> the tables that still hold rows of the study, with how many */
	private function rowsLeft(int $studyId): array
	{
		$left = [];
		foreach (getTableNamesThatHasColumn('study_id', NON_BANKING_SERVICE_CONNECTION_NAME) as $table) {
			$count = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table($table)->where('study_id', $studyId)->count();
			if ($count > 0) {
				$left[$table] = $count;
			}
		}

		return $left;
	}
}
