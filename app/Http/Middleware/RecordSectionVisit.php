<?php

namespace App\Http\Middleware;

use App\Models\Log;
use App\Support\SectionLabel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * بيسجل في جدول logs كل صفحة CashVero المستخدم بيفتحها.
 * بيشتغل بعد ما الصفحة تترد، وبيتجاهل طلبات الـ ajax والـ JSON
 * وتحميل الملفات وأي رد مش ناجح. أي خطأ في التسجيل ما يوقفش الصفحة.
 */
class RecordSectionVisit
{
    /**
     * صفحات CashVero اللي برا الـ block بتاع isCashManagement.
     */
    private const EXTRA_CASH_VERO_ROUTES = ['viewHomePage'];

    /**
     * view.uploading بيخدم Vero و CashVero، والموديلات دي بتاعة CashVero
     * (نفس الشرط اللي المنيو بيستخدمه في getHeaderMenu).
     */
    private const CASH_VERO_UPLOAD_MODELS = ['CustomerInvoice', 'SupplierInvoice', 'LoanSchedule'];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            if ($this->shouldRecord($request, $response)) {
                Log::storeNewLogRecord(
                    'enterSection',
                    $request->user(),
                    SectionLabel::forRoute($request->route()),
                    (int) $request->segment(2)
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $response;
    }

    private function shouldRecord(Request $request, $response): bool
    {
        if (! $request->isMethod('GET') || ! $request->user()) {
            return false;
        }

        $route = $request->route();
        if (! $route instanceof Route || ! $route->getName()) {
            return false;
        }

        if (! is_numeric($request->segment(2))) {
            return false;
        }

        return $this->isPageResponse($request, $response) && $this->belongsToCashVero($route);
    }

    private function isPageResponse(Request $request, $response): bool
    {
        if (! $response instanceof Response || ! $response->isSuccessful()) {
            return false;
        }

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return false;
        }

        if ($request->hasHeader('X-Inertia')) {
            return ! $request->hasHeader('X-Inertia-Partial-Data');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        return str_contains(strtolower((string) $response->headers->get('Content-Type')), 'text/html');
    }

    private function belongsToCashVero(Route $route): bool
    {
        if (in_array('isCashManagement', $route->gatherMiddleware(), true)) {
            return true;
        }

        if (in_array($route->getName(), self::EXTRA_CASH_VERO_ROUTES, true)) {
            return true;
        }

        return $route->getName() === 'view.uploading'
            && in_array($route->parameter('model'), self::CASH_VERO_UPLOAD_MODELS, true);
    }
}
