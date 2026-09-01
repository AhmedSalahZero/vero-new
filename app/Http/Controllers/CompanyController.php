<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\NonBankingService\ConsumerfinanceProduct;
use App\Models\NonBankingService\LeasingCategory;
use App\Models\NonBankingService\MicrofinanceProduct;
use App\Models\Partner;
use App\Models\PropertyManagement\Category;
use App\Models\PropertyManagement\Nature;
use App\Models\PropertyManagement\Ownership;
use App\Models\PropertyManagement\PropertyType;
use App\Models\PropertyManagement\UsageStatus;
use App\Models\User;
use App\Services\Api\OdooService;
use App\Traits\ImageSave;
use Illuminate\Http\Request;

class CompanyController extends Controller
{

    public function index()
    {
        $companies = Company::orderBy('id','desc')->get();
		
        return view('super_admin_view.companies.index',compact('companies'));
    }

 
    public function create()
    {
        return view('super_admin_view.companies.form');
    }

  
    public function store(Request $request)
    {
        toastr()->success('Created Successfully');
        $companySection = Company::create($request->except(['image','systems','is_api']));
		foreach($request->get('systems') as $systemName){
			$companySection->systems()->create([
				'system_name'=>$systemName
			]);
		}
		if($request->has('is_api')){
			return $companySection;
		}
        ImageSave::saveIfExist('image',$companySection);
		Partner::handleTaxesColumnsToPartnerTable($companySection);
		LeasingCategory::createAllForCompany($companySection->id );
		MicrofinanceProduct::createAllForCompany($companySection->id );
		ConsumerfinanceProduct::createAllForCompany($companySection->id );
		Nature::createAllForCompany($companySection->id );
		Category::createAllForCompany($companySection->id );
		UsageStatus::createAllForCompany($companySection->id );
		PropertyType::createAllForCompany($companySection->id );
		Ownership::createAllForCompany($companySection->id );
		
        return redirect()->back();
    }
    // public function adminCompany(Request $request,$company_id)
    // {
    //     $company_row = Company::findOrFail($company_id);
    //     if ($request->method() == 'GET') {
    //         return view('super_admin_view.companies.form',compact('company_row'));
    //     }elseif ($request->method() == "POST") {
    //         $request['sub_of'] = $company_id;
    //         $request['type'] = 'single';

    //         $companySection = Company::create($request->except('image'));
    //         ImageSave::saveIfExist('image',$companySection);
    //         (new BranchController)->createMainBrach($companySection->id);
    //         toastr()->success('Created Successfully');
    //         return redirect()->back();
    //     }

    // }

    // public function editAdminCompany(Request $request,$company_id,Company $companySection)
    // {
    //     $company_row = Company::findOrFail($company_id);


    //     if ($request->method() == 'GET') {
    //         return view('super_admin_view.companies.form',compact('company_row','companySection'));
    //     }else {
    //         $companySection->update($request->except('image'));
    //         ImageSave::saveIfExist('image',$companySection);
    //         toastr()->success('Updated Successfully');
    //         return redirect()->back();
    //     }
    // }
 
    public function edit(Company $companySection)
    {
        return view('super_admin_view.companies.form',compact('companySection'));
    }

  
     public function update(Request $request, Company $companySection)
    {
        toastr()->success('Updated Successfully');
		$oldSystems =$companySection->getSystemsNames(); 
		$newSystems = $request->get('systems');
		$systemsToPreserve  = array_intersect($oldSystems,$newSystems);
		$newSystemsToBeAdded  = array_diff($newSystems,$oldSystems);
		/**
		 * * قبل كده كان بيصفّر odoo_id لكل يوزرات الشركة هنا ، و بعدين
		 * * يعيد جلبه للي بعتوا بيانات دخول جديدة بس . فأي يوزر مش في
		 * * القايمة دي كان بيفضل بـ null للابد و تكامله يتكسر من غير ما
		 * * حد ياخد باله
		 *
		 * * دلوقتي مفيش تصفير : refreshUserOdooId هي اللي بتعيد المصادقة ،
		 * * و بتسيب القيمة القديمة زي ما هي لو المصادقة فشلت
		 */
		$odooConnectionChanged = $request->get('odoo_db_url') != $companySection->odoo_db_url
			|| $request->get('odoo_db_name') != $companySection->odoo_db_name;

		$usersWithNewCredentials = [];

		foreach($request->get('odoo_username',[]) as $userId => $odooUsername){
			$user = User::find($userId);
			if(!$user){
				continue;
			}
			$user->update([
				'odoo_username'=>$odooUsername,
				'odoo_db_password'=>$request->input('odoo_db_password.'.$userId)
			]);
			$usersWithNewCredentials[] = $user;
		}
		Partner::handleTaxesColumnsToPartnerTable($companySection);
        $companySection->update($request->except(['image','systems','odoo_username','odoo_db_password']));

		/**
		 * * بنجيب الـ odoo_id بعد ما الشركة تتحدّث
		 * * عشان المصادقة تتم على الـ url والداتابيز الجداد لو اتغيروا
		 * * وبنبعت اليوزر صراحةً لأن اللي عامل لوجن هنا هو السوبر أدمن
		 */
		/**
		 * * لو الـ url او الداتابيز اتغيروا فالـ odoo_id القديم بقى بتاع
		 * * سيرفر تاني ، فلازم كل يوزرات الشركة يعيدوا المصادقة — مش بس
		 * * اللي بعتوا بيانات جديدة
		 */
		$usersToRefresh = $odooConnectionChanged
			? $companySection->users()->get()->all()
			: $usersWithNewCredentials;

		foreach(collect($usersToRefresh)->unique('id') as $user){
			OdooService::refreshUserOdooId($companySection, $user);
		}

		$companySection->systems()->delete();
		foreach($newSystems as $systemName){
			$companySection->systems()->create(['system_name'=>$systemName]);
		}
        ImageSave::saveIfExist('image',$companySection);
		$companySection->syncPermissionForAllUser($systemsToPreserve,$newSystemsToBeAdded);
        toastr()->success('Updated Successfully');
        return redirect()->back();
    }
    

    public function destroy(Company $companySection)
    {
        toastr()->error('Deleted Successfully');
		
        $companySection->delete();
        return redirect()->back();
    }
	public function saveLabelingData(Request $request,Company $company){
	
		$company->update([
			'labeling_type'=>$request->get('labeling_type'),
			'labeling_report_title'=>$request->get('labeling_report_title'),
			'labeling_pagination_per_page'=>$request->get('labeling_pagination_per_page'),
			'label_width'=>$request->get('label_width'),
			'generate_labeling_code_fields'=>$request->get('generate_labeling_code_fields',null),
			'label_height'=>$request->get('label_height'),
			'labeling_client_logo'=>$request->hasFile('labeling_client_logo') ? $request->file('labeling_client_logo')->store('client_logos','public') :$company->labeling_client_logo,
			'labeling_use_client_logo'=>$request->boolean('labeling_use_client_logo'),
		]);
		
		return response()->json([
			'status'=>true ,
			'message'=>__('Done'),
			'reloadCurrentPage'=>true 
		]);
		
	}
}
