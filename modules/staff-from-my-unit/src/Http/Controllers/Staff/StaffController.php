<?php

namespace Satis2020\StaffFromMyUnit\Http\Controllers\Staff;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Satis2020\InstitutionPackage\Http\Resources\Institution as InstitutionResource;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\StaffManagement;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class StaffController extends ApiController
{
    use VerifyUnicity, Telephone, DataUserNature, StaffManagement, IdentityManagement;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-staff-from-my-unit')->only(['index']);
        $this->middleware('permission:show-staff-from-my-unit')->only(['show']);
        $this->middleware('permission:store-staff-from-my-unit')->only(['store', 'create']);
        $this->middleware('permission:update-staff-from-my-unit')->only(['update', 'edit']);
        $this->middleware('permission:destroy-staff-from-my-unit')->only(['destroy']);

        $this->middleware('mystaff')->except(['index', 'create', 'store']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function index()
    {
        $paginationSize = \request()->query('size');
        $recherche = \request()->query('key');
        $unit_id = \request()->query('unit_id');

        return response()->json(
            Staff::query()
                ->select('staff.*')
                ->with(['identite', 'position', 'unit', 'institution'])
                ->where('institution_id', $this->institution()->id)
                ->when(request()->filled('unit_id'),function (Builder $builder) use($unit_id){
                    return $builder->where('unit_id',$unit_id);
                })
                ->whereHas('identite', function($query) use ($recherche) {
                    return $query
                        ->whereRaw('(`identites`.`firstname` LIKE ?)', ["%$recherche%"])
                        ->orWhereRaw('`identites`.`lastname` LIKE ?', ["%$recherche%"])
                        ->orwhereJsonContains('telephone', $recherche)
                        ->orwhereJsonContains('email', $recherche);
                })->paginate($paginationSize));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function create()
    {
        return response()->json([
            'units' => Unit::with('lead.identite')->where('institution_id', $this->institution()->id)->get(),
            'positions' => Position::all()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function store(Request $request)
    {
        $request->merge(['institution_id' => $this->institution()->id]);

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rules());

        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);

        if (!$this->checkEmailAllowDomain($request)){
            return response()->json(["error"=>["email"=>["Cet email ne respecte pas les noms de domaine configurés"]]], 409);
        }

        // Institution & Unit Consistency Verification
        $this->handleUnitInstitutionVerification($request->institution_id, $request->unit_id);

        // Staff PhoneNumber and Email Unicity Verification
        $this->handleStaffPhoneNumberAndEmailVerificationStore($request);

        $identite = $this->createIdentity($request);

        $staff = $this->createStaff($request, $identite);

        return response()->json($staff->load('identite', 'position', 'unit', 'institution'), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Staff $staff)
    {
        return response()->json($staff->load('identite', 'position', 'unit', 'institution'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function edit(Staff $staff)
    {
        $staff->load('identite', 'position', 'unit.lead.identite', 'institution.units.lead.identite');

        return response()->json([
            'staff' => $staff,
            'units' => Unit::with('lead.identite')->where('institution_id', $this->institution()->id)->get(),
            'positions' => Position::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function update(Request $request, Staff $staff)
    {
        $staff->load('identite', 'position', 'unit', 'institution');

        $request->merge(['institution_id' => $this->institution()->id]);

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rules(true,$staff->identite_id));

        $institution = $this->institution();

        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);

        // Institution & Unit Consistency Verification
        $this->handleUnitInstitutionVerification($request->institution_id, $request->unit_id);

        // Staff PhoneNumber and Email Unicity Verification
        $this->handleStaffPhoneNumberAndEmailVerificationUpdate($request, $staff->identite);

        $this->updateIdentity($request, $staff->identite);

        $this->updateStaff($request, $staff);

        return response()->json($staff, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Staff $staff)
    {
        $staff = $staff->load('identite.user');
        if ($staff->identite && $staff->identite()->has('user')){
            abort(Response::HTTP_FORBIDDEN,"Impossible de supprimer cet agent, il est lié a un compte utilisateur.");
        }
        $staff->delete();

        return response()->json($staff, 200);
    }

}
