<?php

namespace Satis2020\StaffFromMaybeNoUnit\Http\Controllers\Staff;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\InstitutionPackage\Http\Resources\Institution as InstitutionResource;
use Satis2020\ServicePackage\Consts\Constants;
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

        $this->middleware('permission:list-staff-from-maybe-no-unit')->only(['index']);
        $this->middleware('permission:show-staff-from-maybe-no-unit')->only(['show']);
        $this->middleware('permission:store-staff-from-maybe-no-unit')->only(['store', 'create']);
        $this->middleware('permission:update-staff-from-maybe-no-unit')->only(['update', 'edit']);
        $this->middleware('permission:destroy-staff-from-maybe-no-unit')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $paginationSize = \request()->query('size');
        $recherche = \request()->query('key');
        return response()->json(Staff::with(['identite', 'position', 'unit', 'institution'])
            ->when($recherche !=null,function(Builder $query) use ($recherche ) {
                $query
                    ->leftJoin('identites', 'staff.identite_id', '=', 'identites.id')
                    ->whereRaw('(`identites`.`firstname` LIKE ?)', ["%$recherche%"])
                    ->orWhereRaw('`identites`.`lastname` LIKE ?', ["%$recherche%"])
                    ->orwhereJsonContains('telephone', $recherche)
                    ->orwhereJsonContains('email', $recherche);
            })
            ->paginate($paginationSize),
            200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json([
            'institutions' => Institution::all(),
            'positions' => Position::all(),
            'units' => Unit::with('lead.identite')->get()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request)
    {
        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rules(false));

        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);

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
     * @return \Illuminate\Http\Response
     */
    public function show(Staff $staff)
    {
        return response()->json($staff->load('identite', 'position', 'unit', 'institution'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\Response
     */
    public function edit(Staff $staff)
    {
        $staff->load('identite', 'position', 'unit.lead.identite', 'institution.units.lead.identite');
        return response()->json([
            'staff' => $staff,
            'institutions' => Institution::all(),
            'positions' => Position::all(),
            'units' => Unit::with('lead.identite')->get()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function update(Request $request, Staff $staff)
    {
        $staff->load('identite', 'position', 'unit', 'institution');

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rules(false));

        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);

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
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Staff $staff)
    {
        $staff->delete();

        return response()->json($staff, 200);
    }

}
