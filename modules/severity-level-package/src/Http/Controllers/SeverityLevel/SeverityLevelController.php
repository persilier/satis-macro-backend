<?php

namespace Satis2020\SeverityLevelPackage\Http\Controllers\SeverityLevel;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\SeverityLevel;
use Satis2020\SeverityLevelPackage\Http\Resources\SeverityLevelCollection;
use Satis2020\SeverityLevelPackage\Http\Resources\SeverityLevel as SeverityLevelResource;
class SeverityLevelController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        /*$this->middleware('permission:can-list-severity-level')->only(['index']);
        $this->middleware('permission:can-create-severity-level')->only(['store']);
        $this->middleware('permission:can-update-severity-level')->only(['update']);
        $this->middleware('permission:can-show-severity-level')->only(['show']);
        $this->middleware('permission:can-delete-severity-level')->only(['destroy']);*/
    }
    /**
     * Display a listing of the resource.
     *
     * @return SeverityLevelCollection
     */
    public function index()
    {
        return new SeverityLevelCollection(SeverityLevel::all());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return SeverityLevelResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'time_limit' => 'integer',
            'description' => 'required|string',
            'others' => 'array',
        ];
        $this->validate($request, $rules);
        if($severityLevel_exist = SeverityLevel::where('name->'.App::getLocale(), $request->name)->first())
            return $this->errorResponse('Ce niveau de gravité existe déjà.', 400);
        $severityLevel = SeverityLevel::create($request->only(['name', 'time_limit', 'description', 'others']));
        return new SeverityLevelResource($severityLevel);

    }

    /**
     * Display the specified resource.
     *
     * @param SeverityLevel $severityLevel
     * @return SeverityLevelResource
     */
    public function show(SeverityLevel $severityLevel)
    {
        return new SeverityLevelResource($severityLevel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param SeverityLevel $severityLevel
     * @return SeverityLevelResource
     * @throws ValidationException
     */
    public function update(Request $request, SeverityLevel $severityLevel)
    {
        $rules = [
            'name' => 'required|string',
            'time_limit' => 'integer',
            'description' => 'required|string',
            'others' => 'array',
        ];
        $this->validate($request, $rules);
        if($check = SeverityLevel::where('name->'.App::getLocale(), $request->name)->where('id', '!=', $severityLevel->id)->first())
            return $this->errorResponse('Veuillez renseigner un nom du niveau de gravité qui n\'existe pas.', 400);
        $severityLevel->update($request->only(['name', 'time_limit', 'description', 'others']));
        return new SeverityLevelResource($severityLevel);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SeverityLevel $severityLevel
     * @return SeverityLevelResource
     * @throws \Exception
     */
    public function destroy(SeverityLevel $severityLevel)
    {
        $severityLevel->secureDelete('claimObjects', 'claimCategories');
        return new SeverityLevelResource($severityLevel);
    }
}
