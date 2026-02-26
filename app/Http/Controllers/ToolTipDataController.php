<?php

namespace App\Http\Controllers;

use App\Models\ToolTipData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ToolTipDataController extends Controller
{
  
    public function index()
    {
        $fields = ToolTipData::groupBy('model_name')->get();

        return view('super_admin_view.tool_tip_data.index',compact('fields'));
    }
    public function sectionFields($id)
    {
        $section = ToolTipData::findOrFail($id);
        $fields = ToolTipData::where('model_name',$section->model_name)->get();


        return view('super_admin_view.tool_tip_data.fields',compact('fields'));
    }
   
    public function create()
    {

        return view('super_admin_view.tool_tip_data.form');
    }

  
    public function store(Request $request)
    {
        $model_name = $request->model_name;
        $model = 'App\\Models\\' . $model_name;
        $model_obj = new $model;
        $columns  = Schema::getColumnListing($model_obj->getTable());
        $columns = (new ExportTable)->columnsFiltration($columns) ;
        $columns = array_keys($columns);
        $columns = collect($columns)->each(function($field) use($model_name,$request){

            return ToolTipData::create([
                'field' =>$field,
                'model_name' => $model_name,
                'section_name' => $request->section_name,
            ]);
        });
        toastr()->success('Created Successfully');
        return back();
    }

   
    public function show(ToolTipData $toolTipData)
    {
        //
    }


    public function edit($id)
    {
        $toolTipData = ToolTipData::findOrFail($id);
        return view('super_admin_view.tool_tip_data.edit',compact('toolTipData'));
    }

   
    public function update(Request $request,$id)
    {
        $toolTipData = ToolTipData::findOrFail($id);

        $toolTipData->update(['data' => $request->data]);
        toastr()->success('Updated Successfully');
        return redirect()->back() ;
    }

    public function destroy(ToolTipData $toolTipData)
    {
        //
    }
}
