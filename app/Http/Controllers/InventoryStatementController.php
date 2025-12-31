<?php

namespace App\Http\Controllers;

use App\Exports\ExportData;
use App\Models\Company;
use App\Models\InventoryStatement;
use Illuminate\Http\Request;

class InventoryStatementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Company $company)
    {
        $inventoryStatements = InventoryStatement::company()->orderBy('date','desc')->paginate(50);
        $exportableFields  = (new ExportTable)->customizedTableField($company, 'inventoryStatement', 'selected_fields');
        $viewing_names = array_values($exportableFields);

        // foreach ($exportableFields as $field_name => $viewing) {

        //     TablesField::create([
        //         'model_name' => 'inventoryStatement',
        //         'field_name' => $field_name,
        //         'view_name' => $viewing,
        //     ]);
        // }

        $db_names = array_keys($exportableFields);
        return view('client_view.inventory_statement.index', compact('inventoryStatements','company','viewing_names','db_names'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Company $company)
    {
        $customerInvoice = new InventoryStatementViewModel($company);

        return view('client_view.inventory_statement.form', $customerInvoice);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,Company $company)
    {
        // $request['company_id'] = $company->id;
        InventoryStatement::create($request->all());
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\InventoryStatement  $inventoryStatement
     * @return \Illuminate\Http\Response
     */
    public function show(InventoryStatement $inventoryStatement )
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\InventoryStatement  $inventoryStatement
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company,InventoryStatement $inventoryStatement)
    {

        $inventoryStatement  = new InventoryStatementViewModel($company,$inventoryStatement);

        return view('client_view.inventory_statement.form',   $inventoryStatement);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\InventoryStatement  $inventoryStatement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Company $company, InventoryStatement $inventoryStatement)
    {

        $inventoryStatement->update($request->all());
        toastr()->success('Updated Successfully');
        return (new InventoryStatementViewModel($company,$inventoryStatement))->view('client_view.inventory_statement.form');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\InventoryStatement  $inventoryStatement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company, InventoryStatement $inventoryStatement)
    {
        toastr()->error('Deleted Successfully');
        $inventoryStatement->delete();
        return redirect()->back();
    }
    public function export(Company $company)
    {
        $exportableFields = exportableFields($company->id,'InventoryStatement');
        // If there are no exportable fields were found return with a warning msg
        if ($exportableFields === null) {
            toastr()->warning('Please choose exportable fields first');
            return redirect()->back() ;
        }
        // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
        $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
        // Array Contains Only the name of fields
        $exportable_fields = array_keys($selected_fields);
        $inventoryStatement = InventoryStatement::where('company_id',$company->id)->get();
        // Customizing the collection to be exported
        $inventoryStatement = collect($inventoryStatement)->map(function ($invoice)use($exportable_fields){
            $data = [];
            foreach ($exportable_fields as $field) {
                if (str_contains($field,'deduction_id_')) {
                    $value = Deduction::find($invoice->$field)->name[lang()] ??null;
                }elseif (str_contains($field,'date')) {
                    $value = $invoice->$field ===null ?: date('d-m-Y',strtotime($invoice->$field));
                } else{
                    $value = $invoice->$field;
                }
                $data[$field] = $value ;
            }
            return $data;
        });

        return (new ExportData($company->id,array_values($selected_fields),$inventoryStatement))->download('InventoryStatements.xlsx');

    }
}
