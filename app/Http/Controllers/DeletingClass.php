<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DeletingClass
{
    /**
     * * بيمسح الصفوف واحد واحد و بيعدّي اللي محمي بدل ما يقع
     *
     * * فيه موديلات بترفض المسح لو فيه حاجة معتمدة عليها — زي الفاتورة
     * * اللي نازل عليها تسويات (CannotBeDeletedWhileSettled) . من غير
     * * المعالجة دي كان اول صف محمي بيرمي استثناء فالعملية بتقف في نصها :
     * * جزء اتمسح و جزء لا ، و المستخدم يشوف صفحة خطأ من غير ما يعرف السبب
     *
     * @return array{deleted: int, blocked: array<int, string>}
     */
    private function deleteWhatIsAllowed($rows): array
    {
        $deleted = 0;
        $blocked = [];

        foreach ($rows as $row) {
            try {
                $row->delete();
                $deleted++;
            } catch (\InvalidArgumentException $e) {
                $blocked[] = $e->getMessage();
            }
        }

        return ['deleted' => $deleted, 'blocked' => $blocked];
    }

    /**
     * * بيعرض رسالة واحدة تقول اتمسح كام و اتساب كام و ليه
     */
    private function reportDeletionOutcome(array $outcome, string $successMessage): void
    {
        if ($outcome['blocked'] === []) {
            toastr()->success($successMessage);

            return;
        }

        $message = __(':deleted row(s) deleted. :blocked could not be deleted:', [
            'deleted' => $outcome['deleted'],
            'blocked' => count($outcome['blocked']),
        ]).' '.implode(' | ', array_slice($outcome['blocked'], 0, 5));

        $outcome['deleted'] > 0 ? toastr()->warning($message) : toastr()->error($message);
    }

    public function truncate(Company $company, $model)
    {
        $model_name = 'App\\Models\\' . $model;
        $model_obj = new $model_name();
        $all_model_data = $model_obj->company()->get();
        $outcome = ['deleted' => 0, 'blocked' => []];

        if (count($all_model_data) > 0) {
            $outcome = $this->deleteWhatIsAllowed($all_model_data);
            if ($model == 'SalesGathering') {
                Artisan::call('caching:run', [
                    'company_id' => [$company->id]
                ]);
            }
        }

        $this->reportDeletionOutcome($outcome, 'All Rows Were Deleted  Successfully');

        return redirect()->back();
    }

    public function multipleRowsDeleting(Request $request, Company $company, $model)
    {

        if ($request->rows === null || count($request->rows) == 0) {
            toastr()->error('No Rows Were Selected');

            return redirect()->back();
        }

        $model_name = 'App\\Models\\' . $model;
        $model_obj = new $model_name();
        $all_model_data = null ;
        if ($request->has('delete_date_from')) {
			$deleteColumnName = method_exists($model_obj,'getDeleteByDateColumnName') ?  $model_obj->getDeleteByDateColumnName() : 'date';
            $all_model_data = $model_obj->company()->whereBetween($deleteColumnName, [$request->get('delete_date_from'), $request->get('delete_date_to')])->get();
        } elseif ($request->has('delete_serial_from')) {
            $all_model_data = $model_obj->company()->get()->filter(function ($element, $index) use ($request) {
                return $index + 1 >= $request->get('delete_serial_from') && $index + 1 <= $request->get('delete_serial_to') ;
            });
        } else {
            $all_model_data = $model_obj->company()->whereIn('id', is_array($request->rows) ? $request->rows : [$request->rows])->get();
        }
        $outcome = ['deleted' => 0, 'blocked' => []];

        if (count($all_model_data) > 0) {
            $outcome = $this->deleteWhatIsAllowed($all_model_data);
        }
        Artisan::call('caching:run', [
            'company_id' => [$company->id]
        ]);
        if ($request->ajax()) {
            return response()->json([
                'status' => $outcome['blocked'] === [],
                'deleted' => $outcome['deleted'],
                'blocked' => $outcome['blocked'],
            ]);
        }
        $this->reportDeletionOutcome($outcome, 'Deleted Selected Rows Successfully');

        return redirect()->back();
    }
}
