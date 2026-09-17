<?php

namespace Tests\Feature\Flash;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * * رسالة النجاح بعد الـ redirect
 *
 * * php-flasher بيحقن Flasher\Laravel\Middleware\SessionMiddleware في
 * * مجموعة web أوتوماتيك (مش مكتوبة في app/Http/Kernel.php). الخريطة
 * * الافتراضية بتاعته بتخطف session('success') — و كمان error/warning/
 * * info — من كل ريسبونس ، بتحوّلها لـ flasher::envelopes و بتعمل
 * * forget للمفتاح الأصلي.
 *
 * * النتيجة كانت إن redirect()->with('success', ...) يوصل الليـاوت و
 * * المفتاح مش موجود ، فـ SweetAlert ما بتشتغلش ، و flasher كمان ما
 * * بيعرضش حاجة لأن الـ views ما بتناديش flasher_render. الرسالة كانت
 * * بتضيع بالكامل.
 *
 * * و ده يفسّر ليه مسار الخطأ متكتب session()->put('fail', ...) : كلمة
 * * fail مش في خريطة flasher فكانت بتنجو لوحدها.
 *
 * * الحل: config/flasher.php بـ flash_bag => false
 */
class SuccessFlashSurvivesRedirectTest extends TestCase
{
    public function test_the_flasher_flash_bag_bridge_is_disabled(): void
    {
        $this->assertFalse(
            config('flasher.flash_bag'),
            'من غير flash_bag => false بيتخطف session(\'success\') قبل ما أي صفحة تشوفه'
        );
    }

    public function test_a_success_flash_survives_a_redirect_through_the_web_group(): void
    {
        Route::middleware('web')->get('/__flash_set', fn () => redirect()->to('/__flash_read')->with('success', 'Invoices Reading Has Been Completed'));
        Route::middleware('web')->get('/__flash_read', fn () => (string) session('success', 'MISSING'));

        $this->get('/__flash_set')->assertRedirect('/__flash_read');

        $this->get('/__flash_read')->assertSee('Invoices Reading Has Been Completed');
    }

    /**
     * * الحل القديم لمسار الخطأ لازم يفضل شغال زي ما هو
     */
    public function test_a_fail_flash_still_survives(): void
    {
        Route::middleware('web')->get('/__fail_set', function () {
            session()->put('fail', 'Could not access Odoo');

            return redirect()->to('/__fail_read');
        });
        Route::middleware('web')->get('/__fail_read', fn () => (string) session('fail', 'MISSING'));

        $this->get('/__fail_set');

        $this->get('/__fail_read')->assertSee('Could not access Odoo');
    }

    /**
     * * كل مفاتيح الخريطة الافتراضية لازم تعدّي ، مش success بس
     */
    public function test_the_other_flash_keys_survive_too(): void
    {
        foreach (['error', 'warning', 'info', 'danger', 'notice'] as $key) {
            Route::middleware('web')->get("/__k_set_{$key}", fn () => redirect()->to('/x')->with($key, "value-of-{$key}"));
            Route::middleware('web')->get("/__k_read_{$key}", fn () => (string) session($key, 'MISSING'));

            $this->get("/__k_set_{$key}");

            $this->get("/__k_read_{$key}")->assertSee("value-of-{$key}");
        }
    }

    /**
     * * الليـاوت هي اللي بتعرض الرسالة فعلا — لو المفتاح اتغير هناك
     * * الرسالة هتضيع تاني من غير ما حد ياخد باله
     */
    public function test_the_layout_still_reads_the_success_key(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/dashboard.blade.php'));

        $this->assertStringContainsString("session()->has('success')", $layout);
        $this->assertStringContainsString("session()->get('success')", $layout);
    }
}
