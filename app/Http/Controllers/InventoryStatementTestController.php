<?php

namespace App\Http\Controllers;

use App\Imports\ImportData;
use App\Jobs\InventoryStatementTestJob;
use App\Models\Company;
use App\Models\InventoryStatementTest;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class InventoryStatementTestController extends Controller
{
    public function import(Company $company)
    {
        $company_id = $company->id ;
        $user_id = Auth::user()->id ;
        $exportableFields = exportableFields($company_id,'InventoryStatement');
        if ($exportableFields === null) {
            toastr()->warning('Please choose exportable fields first');
            return redirect()->back() ;
        }
        $inventoryStatements = InventoryStatementTest::company()->paginate(50);

        if (request()->method()  == 'GET') {
            $exportableFields  = (new ExportTable)->customizedTableField($company, 'inventoryStatement', 'selected_fields');
            $viewing_names = array_values($exportableFields);
            $db_names = array_keys($exportableFields);
            return view('client_view.inventory_statement.import',compact('company','inventoryStatements','viewing_names','db_names'));
        }else{



        // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
        $exportable_fields = (new ExportTable)->customizedTableField( $company,'InventoryStatement', 'selected_fields');

        // $inventoryStatement = InventoryStatement::where('company_id',$company_id)->get();
        // Customizing the collection to be exported

        $inventoryStatement_fields = [];
        foreach ($exportable_fields as $field_name => $column_name) {
            // To get the names of foreign keys of deductions
            $inventoryStatement_fields[$field_name] = $column_name;
        }
        $inventoryStatement_fields['company_id'] = $company_id;
        $inventoryStatement_fields['created_by'] = $user_id;



		//  new  ImportData($company_id, request()->format, 'SalesGatheringTest', $salesGathering_fields, $active_job->id,auth()->user()->id);

            Excel::import(new  ImportData($company_id,request()->format,'InventoryStatementTest',$inventoryStatement_fields,auth()->user()->id), request()->file('excel_file'));

            return redirect()->back();


        }

    }

    public function insertToMainTable(Company $company)
    {


        DB::table('inventory_statement_tests')->where('company_id', $company->id)->orderBy('id')->chunk(500, function ($invoices)   {
            InventoryStatementTestJob::dispatch($invoices);
        });



        return redirect()->back();
    }

    public function edit(Company $company, InventoryStatementTest $inventoryStatementTest)
    {
        return view('client_view.inventory_statement.importRowForm',compact('company','inventoryStatementTest'));

    }

    public function update(Request $request,Company $company ,InventoryStatementTest $inventoryStatementTest)
    {
        $inventoryStatementTest->update($request->all());
        toastr()->success('Updated Successfully');
        return redirect()->route('inventoryStatementImport',$company);

    }

    public function destroy(Company $company, InventoryStatementTest $inventoryStatementTest)
    {
        $inventoryStatementTest->delete();
        toastr()->error('Deleted Successfully');
        return redirect()->back();

    }
}
