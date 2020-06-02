<?php

namespace Satis2020\MyInstitution\Http\Controllers\Institutions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
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
        $this->middleware('permission:list-my-institution')->only(['index']);
        $this->middleware('permission:create-my-institution')->only(['store','updateLogo']);
        $this->middleware('permission:show-my-institution')->only(['show']);
        $this->middleware('permission:update-my-institution')->only(['update','updateLogo']);
        $this->middleware('permission:destroy-my-institution')->only(['destroy']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Institution $institution
     * @return \Illuminate\Http\Response
     * @throws RetrieveDataUserNatureException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function show($institution){
        $institution = $this->getOneMyInstitution($institution, $this->institution()->id);
        return response()->json($institution, 200);
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
    public function update(Request $request, $institution)
    {
        $institution = $this->getOneMyInstitution($institution, $this->institution()->id);

        $rules = [
            'name' => 'required|string|max:100',
            'acronyme' => 'required|string|max:255',
            'iso_code' => 'required|string|max:50',
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
     * @throws RetrieveDataUserNatureException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function updateLogo(Request $request, $institution){
        $rules = [
            'logo' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        $this->validate($request, $rules);
        $institution = $this->getOneMyInstitution($institution, $this->institution()->id);

        $image = $request->file('logo');

        $name = Str::slug($institution->name).'_'.time();
        $folder = '/assets/images/institutions/';
        $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
        $this->uploadOne($image, $folder, 'public', $name);
        $institution->logo = $filePath;
        $institution->save();
        return $this->showMessage('Mise à jour du logo effectuée avec succès.',201);
    }

}
