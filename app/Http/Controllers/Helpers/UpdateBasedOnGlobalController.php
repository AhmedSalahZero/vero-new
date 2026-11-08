<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use App\Support\CompanyScopedModelResolver as Resolver;
use Illuminate\Http\Request;

class UpdateBasedOnGlobalController extends Controller
{
    /** parent model => relations the dependent selects may load (components/js/commons.blade.php). */
    public const ALLOWED_PARENT_RELATIONS = [
        'RevenueBusinessLine' => ['serviceCategories'],
        'ServiceCategory' => ['serviceItems'],
    ];

    
    public function __invoke(Request $request)
    {
        $result = '';

        // if($request->get('add_new_item'))
        // {
                
        //     $result = '<option class="add-new-item" >'. __('Add New')  .' </option>';
        // }
        // SECURITY: the parent model, its id and the method to call all came from
        // the request (GET), so any method (delete, truncate via __call, ...) could be
        // invoked on any record of any company. Parent model + relation are now
        // allow-listed, the relation is verified by reflection before it is called,
        // and the parent must belong to the {company} in the route.
        $parentClass = Resolver::modelClass($request->get('parentModelName'), array_keys(self::ALLOWED_PARENT_RELATIONS));
        $relationName = Resolver::assertRelation(new $parentClass, $request->get('childRelationName'), self::ALLOWED_PARENT_RELATIONS[$request->get('parentModelName')]);
        foreach (['model_id', 'model_value'] as $attributeKey) {
            if ($request->filled($attributeKey) && ! Resolver::isIdentifier($request->get($attributeKey))) {
                abort(422, 'Invalid attribute.');
            }
        }
        $companyId = Resolver::routeCompanyId($request);
        $parentId = $request->get('parentModelId');
        $parentModel = $companyId !== null && is_scalar($parentId) && is_numeric($parentId)
            ? $parentClass::query()->where('company_id', $companyId)->find($parentId)
            : null;
        
        if($parentModel)
        {
            $childrenModels = $parentModel->$relationName()->get();
            $result =  formatSelects($childrenModels , $request->selectedItem , $request->model_id , $request->model_value , $request->get('add_new_item') , $request->get('select_all'));

        }
        return response()->json([
            'status'=>true ,
            'append_id'=>$request->append_id ,
            'result'=>$result,
            'isFullQuerySelector'=>$request->get('isFullQuerySelector'),
            'addNew'=>$request->get('add_new_item')
        ]);
        
    }
}
