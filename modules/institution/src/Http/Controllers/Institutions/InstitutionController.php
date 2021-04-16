<?php

namespace Satis2020\Institution\Http\Controllers\Institutions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\InstitutionTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UploadFile;

class InstitutionController extends ApiController
{
    use UploadFile,SecureDelete, InstitutionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-institution')->only(['index']);
        $this->middleware('permission:create-institution')->only(['store','updateLogo']);
        $this->middleware('permission:show-institution')->only(['show']);
        $this->middleware('permission:update-institution')->only(['update','updateLogo']);
        $this->middleware('permission:destroy-institution')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function index()
    {
        $user_institution = $this->institution();
        $institutions = $this->getAllInstitutionByType($user_institution->institutionType->name);
        if(null == $institutions)
            $institutions = $user_institution;
        return response()->json($institutions, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return InstitutionResource
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'acronyme' => 'required|string|max:255',
            'iso_code' => 'required|string|max:50',
            'default_currency_slug' => ['nullable', 'exists:currencies,slug'],
            'logo' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'institution_type_id' => 'required|exists:institution_types,id',
            'orther_attributes' => 'array',
        ];

        $this->validate($request, $rules);

        if (false == $this->getVerifiedStore($request->institution_type_id, $this->nature()))
            return response()->json(['error'=> "Impossible d'enregistrer une autre institution du type sélectionné.", 'code' => 400], 200);

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
        $datas['default_currency_slug'] = $request->default_currency_slug;
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
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function show($institution)
    {
        $user_institution = $this->institution();
        if(!$institution = $this->getInstitutionByType($institution, $user_institution->id, $user_institution->institutionType->name))
            return $this->errorMessageGetInstitution();
        return response()->json($institution, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $institution
     * @return InstitutionResource
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function update(Request $request, $institution)
    {
        $user_institution = $this->institution();
        if(!$institution = $this->getInstitutionByType($institution, $user_institution->id, $user_institution->institutionType->name))
            return $this->errorMessageGetInstitution();

        $rules = [
            'name' => 'required|string|max:100',
            'acronyme' => 'required|string|max:255',
            'iso_code' => 'required|string|max:50',
            'default_currency_slug' => ['nullable', 'exists:currencies,slug'],
            'logo' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'institution_type_id' => 'required|exists:institution_types,id',
            'orther_attributes' => 'array',
        ];
        $this->validate($request, $rules);

        if (false == $this->getVerifiedStore($request->institution_type_id, $this->nature()))
            return response()->json(['error'=> "Impossible d'enregistrer une autre institution du type sélectionné.", 'code' => 400], 200);

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
        $datas['default_currency_slug'] = $request->default_currency_slug;
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
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function updateLogo(Request $request, $institution){
        $rules = [
            'logo' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        $this->validate($request, $rules);
        $user_institution = $this->institution();

        if(!$institution = $this->getInstitutionByType($institution, $user_institution->id, $user_institution->institutionType->name))
            return $this->errorMessageGetInstitution();

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
     * @return InstitutionResource
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function destroy($institution)
    {
        $user_institution = $this->institution();
        if(!$institution = $this->getInstitutionByType($institution, $user_institution->id, $user_institution->institutionType->name))
            return $this->errorMessageGetInstitution();
        $institution->secureDelete('units', 'clients', 'positions', 'staff', 'accounts');
        return response()->json($institution, 201);
    }

}
