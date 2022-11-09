<?php

namespace Satis2020\AnyInstitution\Http\Controllers\Institutions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Exceptions\SecureDeleteException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Traits\InstitutionTrait;
use Satis2020\ServicePackage\Traits\UploadFile;

/**
 * Class InstitutionController
 * @package Satis2020\AnyInstitution\Http\Controllers\Institutions
 */
class InstitutionController extends ApiController
{
    use UploadFile, InstitutionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-any-institution')->only(['index']);
        $this->middleware('permission:store-any-institution')->only(['store', 'updateLogo']);
        $this->middleware('permission:show-any-institution')->only(['show']);
        $this->middleware('permission:update-any-institution')->only(['update', 'updateLogo']);
        $this->middleware('permission:destroy-any-institution')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $institutions = Institution::with('institutionType', 'defaultCurrency')->get();
        return response()->json($institutions, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function create()
    {
        return response()->json([
            'currencies' => Currency::all(),
            'institutionTypes' => null
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Institution $institution
     * @return JsonResponse
     */
    public function edit(Institution $institution)
    {
        return response()->json([
            'institution' => $institution->load('InstitutionType', 'defaultCurrency'),
            'currencies' => Currency::all(),
            'institutionTypes' => null
        ], 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     * @throws CustomException
     */
    public function store(Request $request)
    {

        if ($this->institution()->institutionType->name == 'holding' || $this->institution()->institutionType->name == 'observatory') {
            $request->merge([
                'institution_type_id' => $this->institution()->institutionType->name == 'holding'
                    ? InstitutionType::where('name', 'filiale')->firstOrFail()->id
                    : InstitutionType::where('name', 'membre')->firstOrFail()->id
            ]);
        }

        $this->validate($request, $this->rules());

        if (false === $this->getVerifiedStore($request->institution_type_id, $this->nature()))
            return response()->json(['error' => "Impossible d'enregistrer une autre institution du type sélectionné.", 'code' => 400], 400);

        $filePath = null;

        if ($request->has('logo')) {
            // Get image file
            $image = $request->file('logo');
            $name = Str::slug($request->name) . '_' . time();
            $folder = '/assets/images/institutions/';
            $filePath = $folder . $name . '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
        }

        $datas['name'] = $request->name;
        $datas['acronyme'] = $request->acronyme;
        $datas['iso_code'] = $request->iso_code;
        $datas['default_currency_slug'] = $request->default_currency_slug;
        $datas['other_attributes'] = $request->other_attributes;
        $datas['institution_type_id'] = $request->institution_type_id;

        if (isset($filePath))
            $datas['logo'] = $filePath;

        $institution = Institution::create($datas);
        return response()->json($institution, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param $institution
     * @return JsonResponse
     */
    public function show(Institution $institution)
    {
        return response()->json($institution->load('institutionType', 'defaultCurrency'), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $institution
     * @return JsonResponse
     * @throws ValidationException
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
     */
    public function update(Request $request, Institution $institution)
    {
        $request->merge(['institution_type_id' => $institution->institution_type_id]);
        $this->validate($request, $this->rules($institution));

        if (false === $this->getVerifiedStore($request->institution_type_id, $this->nature()))
            return response()->json(['error' => "Impossible d'enregistrer une autre institution du type sélectionné.", 'code' => 400], 400);

        if ($request->has('logo')) {
            // Get image file
            $image = $request->file('logo');
            $name = Str::slug($request->name) . '_' . time();
            $folder = '/assets/images/institutions/';
            $filePath = $folder . $name . '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
        }

        $datas['name'] = $request->name;
        $datas['acronyme'] = $request->acronyme;
        $datas['iso_code'] = $request->iso_code;
        $datas['default_currency_slug'] = $request->default_currency_slug;
        $datas['other_attributes'] = $request->other_attributes;
        $datas['institution_type_id'] = $request->institution_type_id;

        if (isset($filePath))
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
     * @return JsonResponse
     * @throws ValidationException
     */
    public function updateLogo(Request $request, Institution $institution)
    {
        $rules = [
            'logo' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        $this->validate($request, $rules);

        $image = $request->file('logo');

        $name = Str::slug($institution->name) . '_' . time();
        $folder = '/assets/images/institutions/';
        $filePath = $folder . $name . '.' . $image->getClientOriginalExtension();
        $this->uploadOne($image, $folder, 'public', $name);
        $institution->logo = $filePath;
        $institution->save();
        return $this->showMessage('Mise à jour du logo effectuée avec succès.', 201);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param $institution
     * @return JsonResponse
     * @throws SecureDeleteException
     * @throws \Exception
     */
    public function destroy(Institution $institution)
    {
        $institution->secureDelete('units', 'clients', 'positions', 'staff', 'staff.identite.user', 'accounts');
        return response()->json($institution, 201);
    }

}
