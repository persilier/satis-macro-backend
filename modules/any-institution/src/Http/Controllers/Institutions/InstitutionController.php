<?php

namespace Satis2020\AnyInstitution\Http\Controllers\Institutions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Exceptions\SecureDeleteException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Traits\InstitutionTrait;
use Satis2020\ServicePackage\Traits\UploadFile;

class InstitutionController extends ApiController
{
    use UploadFile, InstitutionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-any-institution')->only(['index']);
        $this->middleware('permission:store-any-institution')->only(['store','updateLogo']);
        $this->middleware('permission:show-any-institution')->only(['show']);
        $this->middleware('permission:update-any-institution')->only(['update','updateLogo']);
        $this->middleware('permission:destroy-any-institution')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $institutions = Institution::with('institutionType')->get();
        return response()->json($institutions, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json([
            'institutionTypes' => InstitutionType::all()
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Institution $institution
     * @return \Illuminate\Http\Response
     */
    public function edit(Institution $institution)
    {
        return response()->json([
            'institution' => $institution->load('InstitutionType'),
            'institutionTypes' => InstitutionType::all()
        ], 200);
    }


    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'acronyme' => 'required|string|max:255',
            'iso_code' => 'required|string|max:50',
            'logo' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'institution_type_id' => 'required|exists:institution_types,id',
            'orther_attributes' => 'array',
        ];

        $this->validate($request, $rules);

        if (false === $this->getVerifiedStore($request->institution_type_id, $this->nature()))
            return response()->json(['error'=> "Impossible d'enregistrer une autre institution du type sélectionné.", 'code' => 400], 400);

        $filePath = null;

        if ($request->has('logo')) {
            // Get image file
            $image = $request->file('logo');
            $name = Str::slug($request->name).'_'.time();
            $folder = '/assets/images/institutions/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
        }

        $datas['name'] = $request->name;
        $datas['acronyme'] = $request->acronyme;
        $datas['iso_code'] = $request->iso_code;
        $datas['other_attributes'] = $request->other_attributes;
        $datas['institution_type_id'] = $request->institution_type_id;

        if(isset($filePath))
            $datas['logo'] = $filePath;

        $institution = Institution::create($datas);
        return response()->json($institution, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param $institution
     * @return void
     */
    public function show(Institution $institution)
    {
        return response()->json($institution->load('institutionType'), 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $institution
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws RetrieveDataUserNatureException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function update(Request $request, Institution $institution)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'acronyme' => 'required|string|max:255',
            'iso_code' => 'required|string|max:50',
            'logo' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'institution_type_id' => 'required|exists:institution_types,id',
            'orther_attributes' => 'array',
        ];
        $this->validate($request, $rules);

        if (false === $this->getVerifiedStore($request->institution_type_id, $this->nature()))
            return response()->json(['error'=> "Impossible d'enregistrer une autre institution du type sélectionné.", 'code' => 400], 400);

        if ($request->has('logo')) {
            // Get image file
            $image = $request->file('logo');
            $name = Str::slug($request->name).'_'.time();
            $folder = '/assets/images/institutions/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
        }

        $datas['name'] = $request->name;
        $datas['acronyme'] = $request->acronyme;
        $datas['iso_code'] = $request->iso_code;
        $datas['other_attributes'] = $request->other_attributes;
        $datas['institution_type_id'] = $request->institution_type_id;

        if(isset($filePath))
            $datas['logo'] = $filePath;

        $institution->slug = null;
        $institution->update($datas);
        return response()->json($institution, 201);
    }


    /**
     * update the logo institution.
     *
     * @param Request $request
     * @param $institution
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateLogo(Request $request,Institution $institution){
        $rules = [
            'logo' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        $this->validate($request, $rules);

        $image = $request->file('logo');

        $name = Str::slug($institution->name).'_'.time();
        $folder = '/assets/images/institutions/';
        $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
        $this->uploadOne($image, $folder, 'public', $name);
        $institution->logo = $filePath;
        $institution->save();
        return $this->showMessage('Mise à jour du logo effectuée avec succès.',201);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param $institution
     * @return \Illuminate\Http\JsonResponse
     * @throws SecureDeleteException
     * @throws \Exception
     */
    public function destroy(Institution $institution)
    {
        $institution->secureDelete('units', 'clients', 'positions', 'staff','staff.identite.user', 'accounts');
        return response()->json($institution, 201);
    }

}
