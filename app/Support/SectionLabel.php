<?php

namespace App\Support;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;

/**
 * اسم القسم اللي بيتكتب في جدول logs لما المستخدم يفتح صفحة.
 * بيتعمل من اسم الـ route: كلمة الفعل (view / index / show) بتتشال،
 * و create / edit بيبقوا لاحقة، والباراميترات النصية (زي modelType)
 * بتتضاف بين قوسين عشان "أرصدة العملاء" تتفرق عن "أرصدة الموردين".
 */
class SectionLabel
{
    private const SILENT_ACTIONS = ['view', 'index', 'show'];

    private const NAMED_ACTIONS = ['create' => 'Create', 'edit' => 'Edit'];

    private const IGNORED_PARAMETERS = ['company', 'locale', 'lang'];

    private const MAX_LENGTH = 200;

    public static function forRoute(Route $route): string
    {
        return self::fromName((string) $route->getName(), $route->parameters());
    }

    public static function fromName(string $routeName, array $parameters = []): string
    {
        $tokens = array_values(array_filter(
            preg_split('/[.\-_\s]+/', Str::snake($routeName, '.')),
            fn ($token) => $token !== ''
        ));
        $suffix = null;

        foreach ([0, count($tokens) - 1] as $edge) {
            if (count($tokens) < 2 || ! isset($tokens[$edge])) {
                break;
            }
            $token = strtolower($tokens[$edge]);
            if (in_array($token, self::SILENT_ACTIONS, true)) {
                array_splice($tokens, $edge, 1);
                break;
            }
            if (isset(self::NAMED_ACTIONS[$token])) {
                $suffix = self::NAMED_ACTIONS[$token];
                array_splice($tokens, $edge, 1);
                break;
            }
        }

        $label = Str::headline(implode(' ', $tokens));

        $details = self::descriptiveParameters($parameters);
        if ($details !== []) {
            $label .= ' ('.implode(', ', $details).')';
        }

        if ($suffix !== null) {
            $label .= ' - '.$suffix;
        }

        return Str::limit($label, self::MAX_LENGTH, '');
    }

    /**
     * بس الباراميترات اللي هي كلمة (CustomerInvoice / Customer ...).
     * الأرقام والتواريخ والموديلات المربوطة بتتجاهل عشان الاسم يفضل ثابت.
     */
    private static function descriptiveParameters(array $parameters): array
    {
        $details = [];
        foreach ($parameters as $name => $value) {
            if (in_array($name, self::IGNORED_PARAMETERS, true) || ! is_string($value)) {
                continue;
            }
            if (preg_match('/^[A-Za-z][A-Za-z_-]*$/', $value) !== 1) {
                continue;
            }
            $details[] = Str::headline($value);
        }

        return $details;
    }
}
