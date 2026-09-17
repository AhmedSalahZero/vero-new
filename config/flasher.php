<?php

declare(strict_types=1);

use Flasher\Prime\Configuration;

/*
 * * النظام ده بيعرض رسايل النجاح/الخطأ بـ SweetAlert من الليـاوت
 * * (resources/views/layouts/dashboard.blade.php) ، اللي بيقرا
 * * session('success') و session('fail') على طول.
 *
 * * ⚠️ مهم: الخريطة الافتراضية لـ flash_bag في php-flasher بتخطف
 * * session('success') (و error/warning/info) من كل ريسبونس:
 * * Flasher\Laravel\Middleware\SessionMiddleware — و دي بتتحقن في
 * * مجموعة web أوتوماتيك من الباكدج ، مش مكتوبة في app/Http/Kernel.php —
 * * بتحوّلها لـ flasher::envelopes و بتعمل forget للمفتاح الأصلي.
 *
 * * النتيجة كانت: redirect()->with('success', ...) بيوصل الليـاوت و
 * * المفتاح مش موجود ، فرسالة النجاح ما بتظهرش خالص — لا من SweetAlert
 * * (المفتاح اتمسح) و لا من flasher (الـ views ما بتناديش flasher_render).
 * * و عشان كده مسار الخطأ كان مكتوب session()->put('fail', ...) : كلمة
 * * fail مش في خريطة flasher فكانت بتنجو.
 *
 * * flash_bag => false بيوقّف التحويل ده. الكونترولرز اللي بتنادي
 * * toastr()/flash() بتكتب في flasher مباشرة و بتفضل شغالة زي ما هي.
 */
return Configuration::from([
    'default' => 'flasher',
    'main_script' => '/vendor/flasher/flasher.min.js',
    'public_path' => '',
    'styles' => [
        '/vendor/flasher/flasher.min.css',
    ],
    'inject_assets' => true,
    'translate' => true,
    'excluded_paths' => [],
    'flash_bag' => false,
]);
