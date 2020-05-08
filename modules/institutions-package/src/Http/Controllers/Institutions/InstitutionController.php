<?php

namespace Satis2020\InstitutionPackage\Http\Controllers\Institutions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\UploadFile;
use Satis2020\InstitutionPackage\Http\Resources\Institution as InstitutionResource;
use Satis2020\InstitutionPackage\Http\Resources\InstitutionCollection;

class InstitutionController extends ApiController
{
    use UploadFile;

    public function __construct()
    {
        parent::__construct();
        /*$this->middleware('permission:can-list-institution')->only(['index']);
        $this->middleware('permission:can-create-institution')->only(['store']);
        $this->middleware('permission:can-show-institution')->only(['show']);
        $this->middleware('permission:can-update-institution')->only(['update']);
        $this->middleware('permission:can-delete-institution')->only(['destroy']);*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return InstitutionCollection
     */
    public function index()
    {
        return new InstitutionCollection(Institution::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return InstitutionResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100|unique:institutions,name',
            'acronyme' => 'required|string|max:255',
            'iso_code' => 'required|string|max:50',
            'logo' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'orther_attributes' => 'array',
        ];

        $this->validate($request, $rules);
        $filePath = null;
        if ($request->has('logo')) {
            // Get image file
            $image = $request->file('logo');
            $name = Str::slug($request->name).'_'.time();
            $folder = '/assets/images/institutions/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
        }
        if(isset($filePath))
            $datas = ['name'=>$request->name, 'acronyme' => $request->acronyme,
            'iso_code'=>$request->iso_code, 'logo'=> $filePath, 'other_attributes'=>$request->other_attributes];
        else
            $datas = ['name'=>$request->name, 'acronyme' => $request->acronyme,
                'iso_code'=>$request->iso_code, 'other_attributes'=>$request->other_attributes];

        $institution = Institution::create($datas);
        return new InstitutionResource($institution);
    }

    /**
     * Display the specified resource.
     *
     * @param $slug
     * @return InstitutionResource
     */
    public function show($slug)
    {
        $institution = Institution::where('slug', $slug)->orWhere('id',$slug)->firstOrFail();
        return new InstitutionResource(
            $institution
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $slug
     * @return InstitutionResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $slug)
    {
        $institution = Institution::where('slug', $slug)
            ->orWhere('id',$slug)->firstOrFail();
        $rules = [
            'name' => 'required|string|max:100|unique:institutions,name,'.$institution->id,
            'acronyme' => 'required|string|max:255',
            'iso_code' => 'required|string|max:50',
            'logo' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'orther_attributes' => 'array',
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
        if(isset($filePath))
            $datas = ['name'=>$request->name, 'acronyme' => $request->acronyme,
                'iso_code'=>$request->iso_code, 'logo'=> $filePath, 'other_attributes'=>$request->other_attributes];
        else
            $datas = ['name'=>$request->name, 'acronyme' => $request->acronyme,
                'iso_code'=>$request->iso_code, 'other_attributes'=>$request->other_attributes];
        $institution->slug = null;
        $institution->update($datas);
        return new InstitutionResource($institution);
    }


    /**
     * update the logo institution.
     *
     * @param $slug
     * @return InstitutionResource
     * @throws \Exception
     */
    public function updateLogo(Request $request, $slug){
        $rules = [
            'logo' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        $this->validate($request, $rules);
        $institution = Institution::where('slug', $slug)
                            ->orWhere('id',$slug)->firstOrFail();
        $image = $request->file('logo');
        $name = Str::slug($institution->name).'_'.time();
        $folder = '/assets/images/institutions/';
        $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
        $this->uploadOne($image, $folder, 'public', $name);
        $institution->logo = $filePath;
        $institution->save();
        return $this->showMessage('Mise à jour du logo effectuée avec succès.');
    }

    /**
     * update the logo institution.
     *
     * @param Request $request
     * @return void
     */
    public function getLogo(Request $request){

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $slug
     * @return InstitutionResource
     * @throws \Exception
     */
    public function destroy($slug)
    {
        $institution = Institution::where('slug', $slug)
            ->orWhere('id',$slug)->firstOrFail();
        $institution->delete();
        return new InstitutionResource($institution);
    }

}
