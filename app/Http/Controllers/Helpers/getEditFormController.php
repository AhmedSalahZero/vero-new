<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use App\Support\CompanyScopedModelResolver as Resolver;
use Illuminate\Http\Request;

class getEditFormController extends Controller
{

    public function __invoke(Request $request)
    {
        // $formPath = $request->get('formPath');
        // SECURITY: static methods were called on any class named in the request.
        $modelFullPath = Resolver::modelClass($request->get('modelName'), ['BusinessSector']);
        // getCrudFormName
        return view($modelFullPath::getCrudFormName() , $modelFullPath::getViewVars())->render();
    }
}
