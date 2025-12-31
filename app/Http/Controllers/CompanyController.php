<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\NonBankingService\ConsumerfinanceProduct;
use App\Models\NonBankingService\LeasingCategory;
use App\Models\NonBankingService\MicrofinanceProduct;
use App\Models\Partner;
use App\Traits\ImageSave;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::orderBy('id','desc')->get();
		
        return view('super_admin_view.companies.index',compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('super_admin_view.companies.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
		
        return redirect()->back();
    }
    public function adminCompany(Request $request,$company_id)
    {
        $company_row = Company::findOrFail($company_id);
        if ($request->method() == 'GET') {
            return view('super_admin_view.companies.form',compact('company_row'));
        }elseif ($request->method() == "POST") {
            $request['sub_of'] = $company_id;
            $request['type'] = 'single';

            $companySection = Company::create($request->except('image'));
            ImageSave::saveIfExist('image',$companySection);
            (new BranchController)->createMainBrach($companySection->id);
            toastr()->success('Created Successfully');
            return redirect()->back();
        }

    }

    public function editAdminCompany(Request $request,$company_id,Company $companySection)
    {
        $company_row = Company::findOrFail($company_id);


        if ($request->method() == 'GET') {
            return view('super_admin_view.companies.form',compact('company_row','companySection'));
        }else {
            $companySection->update($request->except('image'));
            ImageSave::saveIfExist('image',$companySection);
            toastr()->success('Updated Successfully');
            return redirect()->back();
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $companySection)
    {
        return view('super_admin_view.companies.form',compact('companySection'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $companySection)
    {
        toastr()->success('Updated Successfully');
		
		$oldSystems =$companySection->getSystemsNames(); 
		$newSystems = $request->get('systems');
		$systemsToPreserve  = array_intersect($oldSystems,$newSystems);
		$newSystemsToBeAdded  = array_diff($newSystems,$oldSystems);
		$companySection->update([
			'odoo_id'=>null 
		]);
		Partner::handleTaxesColumnsToPartnerTable($companySection);
        $companySection->update($request->except(['image','systems']));
		
		$companySection->systems()->delete();
		foreach($newSystems as $systemName){
			$companySection->systems()->create(['system_name'=>$systemName]);
		}
        ImageSave::saveIfExist('image',$companySection);
		$companySection->syncPermissionForAllUser($systemsToPreserve,$newSystemsToBeAdded);
        toastr()->success('Updated Successfully');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
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
