<?php
namespace App\Http\Controllers\Tradings;


use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Trading\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CopyStudyController extends Controller
{
    public function index(Request $request,Company $company, Study $study)
    {
       $id = $study->id ;
		$newProject = $study->replicate(['id']);
		$newProject->name = $request->get('name');

		$tablesWithOnlyHospitalitySectorAsForeignKey =getTableNamesThatHasColumn('study_id',TRADING_CONNECTION_NAME) ;
		
		$newProject->save();
		foreach( $tablesWithOnlyHospitalitySectorAsForeignKey as $tableName){
			
			$allData = [];
			$rows = DB::connection(TRADING_CONNECTION_NAME)->table($tableName)->where('study_id', $id)->get(); // استرجاع الصف ككائن stdClass
			foreach($rows as $row){
				$data = (array) $row; // تحويله إلى مصفوفة
				unset($data['id']); // حذف الـ id حتى لا يحدث تعارض (أو المفتاح الأساسي)
				$data['study_id'] = $newProject->id ; 
				if(isset($data['model_id'])){
					$data['model_id'] = $newProject->id;
				}
				$allData[] = $data;
			}
		
			DB::connection(TRADING_CONNECTION_NAME)->table($tableName)->insert($allData); // إدراج نسخة جديدة
			
		}
		$active = $study->getActiveTab();
			return redirect()->route('trading.view.study',['company'=>$company->id,'active'=>$active]);
		// return redirect()->back()->with('success',__('Done!'));
    }
  
}
