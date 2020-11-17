<?php

namespace Satis2020\AnyInstitutionModule\Http\Controllers\AnyInstitutionModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Module;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;


/**
 * Class AnyInstitutionModuleController
 * @package Satis2020\AnyInstitutionModule\Http\Controllers\AnyInstitutionModule
 */
class AnyInstitutionModuleController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        /*$this->middleware('permission:list-any-institution-module')->only(['index']);
        $this->middleware('permission:store-any-institution-module')->only(['store']);
        $this->middleware('permission:show-any-institution-module')->only(['show']);
        $this->middleware('permission:update-any-institution-module')->only(['update']);
        $this->middleware('permission:delete-any-institution-module')->only(['destroy']);*/
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(Module::withCount('permissions')->get(), 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', new TranslatableFieldUnicityRules('modules', 'name')],
            'description' => 'nullable',
        ]);

        return response()->json(Module::create($request->only(['name', 'description'])), 201);
    }


    /**
     * @param Module $module
     * @return JsonResponse
     */
    public function show(Module $module)
    {
        return response()->json($module, 200);
    }


    /**
     * @param Request $request
     * @param Module $module
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Module $module)
    {
       $this->validate($request, [
           'name' => ['required', new TranslatableFieldUnicityRules('modules', 'name', 'id', "{$module->id}")],
           'description' => 'nullable',
       ]);

        $module->update($request->only(['name', 'description']));

        return response()->json($module, 201);
    }


    /**
     * @param Module $module
     * @return JsonResponse
     */
    public function destroy(Module $module)
    {
        $module->secureDelete('permissions');

        return response()->json($module, 200);
    }

}
