<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class TempDumpPageTest extends TestCase
{
    use DatabaseTransactions;

    public function createApplication()
    {
        putenv('ROUTING_LOCALE=en');
        $_ENV['ROUTING_LOCALE'] = 'en';
        $_SERVER['ROUTING_LOCALE'] = 'en';

        return parent::createApplication();
    }

    protected function setUpTraits()
    {
        config(['database.connections.mysql.database' => env('SMOKE_DB', 'veroanalysisb_dev')]);
        DB::purge('mysql');

        return parent::setUpTraits();
    }

    public function test_dump(): void
    {
        $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ]);

        $actor = User::findOrFail(1);
        $out = getenv('DUMP_DIR');

        $this->withoutExceptionHandling();

        foreach ([
            'money-payment' => route('view.money.payment', ['company' => 92]),
            'cash-expense' => route('view.cash.expense', ['company' => 92]),
        ] as $name => $url) {
            try {
                $res = $this->actingAs($actor)->get($url);
            } catch (\Throwable $e) {
                fwrite(STDERR, "\n  ❌ $name: ".get_class($e).': '.$e->getMessage()."\n");
                foreach (array_slice($e->getTrace(), 0, 6) as $t) {
                    fwrite(STDERR, '        '.str_replace(base_path().'/', '', $t['file'] ?? '?').':'.($t['line'] ?? '?')."\n");
                }
                continue;
            }
            fwrite(STDERR, sprintf("  %-16s %s → %d (%d KB)\n", $name, $url, $res->getStatusCode(), strlen($res->getContent()) / 1024));
            file_put_contents($out.'/'.$name.'.html', $res->getContent());
        }

        $this->assertTrue(true);
    }
}
