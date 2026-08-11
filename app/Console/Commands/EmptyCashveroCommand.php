<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Schema;

class EmptyCashveroCommand extends Command
{
    protected $signature = 'empty:cashvero {--force : Required. Without this flag the command exits immediately.}';

    protected $description = 'Empty CashVero (destructive — disabled on production/staging)';

    public function handle()
    {
        if (app()->environment(['production', 'staging'])) {
            $this->error('empty:cashvero is disabled on production/staging to protect client Cash Vero data.');

            return 1;
        }

        if (! $this->option('force')) {
            $this->error('Refusing to run without --force. This command deletes Cash Vero rows for every company_id != 41.');

            return 1;
        }

        if (! $this->confirm('This will DELETE Cash Vero data for all companies except company_id 41. Continue?')) {
            $this->warn('Aborted.');

            return 1;
        }

        $cashVeroTables = getCashVeroTableNames();

        foreach ($cashVeroTables as $tableName) {
            if (Schema::hasColumn($tableName, 'company_id')) {
                DB::table($tableName)->where('company_id', '!=', 41)->delete();
            }
        }

        $this->info('Cash Vero wipe finished (company_id != 41).');

        return 0;
    }
}
