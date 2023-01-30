<?php

namespace Satis2020\MyInstitution\Http\Controllers\Institutions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Satis\CountriesPackage\Facades\Country;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Services\CountryService;
use Satis2020\ServicePackage\Traits\InstitutionTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UploadFile;

class InstitutionController extends ApiController
{
    use UploadFile,SecureDelete, InstitutionTrait;

    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:update-my-institution')->only(['getMyInstitution','updateMyInstitution','updateLogo']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */

    public function getMyInstitution(){
        return response()->json(["institution"=>$this->institution()->load('defaultCurrency',"country"),"countries"=>Country::getAllAfricaCountries()], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     */
    public function updateMyInstitution(Request $request)
    {
        $institution = $this->institution();

        $rules = [
            'name' => 'required|string|max:100',
            'acronyme' => 'required|string|max:255',
            'iso_code' => 'required|string|max:50',
            'default_currency_slug' => ['nullable', 'exists:currencies,slug'],
            'logo' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'orther_attributes' => 'array',
            'country_id'=>'numeric'
        ];
        $this->validate($request, $rules);

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
        $datas['country_id'] = $request->country_id;

        if(isset($filePath))
            $datas['logo'] = $filePath;

        $institution->slug = null;
        $institution->update($datas);

        $this->activityLogService->store("Mise à jour des informations de l'institution",
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'institution',
            $this->user(),
            $institution
        );

        return response()->json($institution, 201);
    }


    /**
     * update the logo institution.
     *
     * @param Request $request
     * @return void
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     */
    public function updateLogo(Request $request){
        $rules = [
            'logo' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        $this->validate($request, $rules);
        $institution = $this->institution();

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
