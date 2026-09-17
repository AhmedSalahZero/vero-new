<?php

namespace Tests\Feature\Views;

use Tests\TestCase;

/**
 * * صفحات الفورم كان فيها مشكلتين بيخلّوا الصفحة مكسورة و النص مش باين
 *
 * * ١) كومبوننت x-sectionTitle كانت بتحقن <style> فيه
 * *    .text-black { color: white !important; } — و الكومبوننت دي في ٣٨
 * *    صفحة ، فالقاعدة كانت بتتحقن في كلها. أي label عليه الكلاس
 * *    text-black كان بيطلع أبيض على خلفية بيضا ، يعني نص مش ظاهر خالص.
 * *    و الكلاس اسمه text-black أصلا يعني المفروض أسود.
 *
 * * ٢) نفس التلات فورمات كان فيهم <form> جوّه <form> (ممنوع في HTML) و
 * *    <div>ين مش مقفولين ، فالـ </form> كانت بتقفل الفورم الخارجي في نص
 * *    الـ divs و كل اللي بعد المحتوى في الليـاوت كان بيتحشر جوّه .row
 */
class FormMarkupTest extends TestCase
{
    /** @return list<string> */
    private static function formsThatUseTextBlack(): array
    {
        return [
            'partners/form.blade.php',
            'reports/certificates-of-deposit/form.blade.php',
            'reports/time-of-deposit/form.blade.php',
        ];
    }

    private function source(string $relative): string
    {
        $path = resource_path('views/'.$relative);
        $this->assertFileExists($path);

        // كومنتات بليد مابتتطبعش ، فمابتعدّش في الوسوم
        return preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents($path));
    }

    /* ───────── ١) الكلاس المفروض يكون أسود ───────── */

    public function test_no_component_paints_text_black_white(): void
    {
        $offenders = [];

        foreach (glob(resource_path('views/components/*.blade.php')) as $component) {
            $body = file_get_contents($component);

            if (preg_match('/\.text-black\s*\{[^}]*color\s*:\s*(white|#fff)/i', $body)) {
                $offenders[] = basename($component);
            }
        }

        $this->assertSame([], $offenders,
            'كلاس اسمه text-black مايصحّش يتلوّن أبيض — النص بيختفي على الخلفية البيضا');
    }

    public function test_the_text_black_class_is_actually_black(): void
    {
        $css = file_get_contents(public_path('assets/css/custom.css'));

        $this->assertMatchesRegularExpression(
            '/\.text-black\s*\{[^}]*color\s*:\s*#000/i',
            $css,
            'لازم يفضل متعرّف أسود في ملف الـ css بتاع التطبيق'
        );
    }

    /**
     * * الكومبوننت مايصحّش تحقن CSS عام — أي قاعدة جواها بتتطبق على كل
     * * صفحة بتستخدمها
     */
    public function test_the_section_title_component_injects_no_global_css(): void
    {
        $this->assertStringNotContainsString(
            '<style',
            file_get_contents(resource_path('views/components/sectionTitle.blade.php'))
        );
    }

    /* ───────── ٢) بنية الفورمات ───────── */

    /** @dataProvider formProvider */
    public function test_the_form_has_no_nested_form(string $relative): void
    {
        $this->assertSame(1, substr_count($this->source($relative), '<form'),
            'HTML مابيسمحش بفورم جوّه فورم — المتصفح بيرمي الداخلي و البنية بتتشابك');
    }

    /** @dataProvider formProvider */
    public function test_every_form_tag_is_closed(string $relative): void
    {
        $source = $this->source($relative);

        $this->assertSame(
            substr_count($source, '<form'),
            substr_count($source, '</form>'),
        );
    }

    /** @dataProvider formProvider */
    public function test_every_div_is_closed(string $relative): void
    {
        $source = $this->source($relative);

        $this->assertSame(
            substr_count($source, '<div'),
            substr_count($source, '</div>'),
            'div مش مقفول بيخلي كل اللي بعد المحتوى يتحشر جوّاه'
        );
    }

    /** @return array<string, array{0: string}> */
    public static function formProvider(): array
    {
        $cases = [];
        foreach (self::formsThatUseTextBlack() as $relative) {
            $cases[$relative] = [$relative];
        }

        return $cases;
    }
}
