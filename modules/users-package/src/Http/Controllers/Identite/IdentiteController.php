<?php

namespace Satis2020\UserPackage\Http\Controllers\Identite;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Events\SendMail;
use Satis2020\ServicePackage\Mail\UserMailChanged;
use Satis2020\UserPackage\Http\Resources\Identite as IdentiteResource;
use Satis2020\UserPackage\Http\Resources\IdentiteCollection;
class IdentiteController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        /*$this->middleware('permission:can-list-identite')->only(['index']);
        $this->middleware('permission:can-create-identite')->only(['store']);
        $this->middleware('permission:can-update-identite')->only(['update']);
        $this->middleware('permission:can-show-identite')->only(['show']);
        $this->middleware('permission:can-delete-identite')->only(['destroy']);*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return IdentiteCollection
     */
    public function index()
    {
        return new IdentiteCollection(Identite::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return IdentiteResource
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'firstname' => 'required',
            'lastname' => 'required',
            'sexe' => 'in:M,F',
            'email' => 'email|unique:identites',
            'other_attributes' => 'array'
        ];

        $this->validate($request, $rules);

        $identite = Identite::create($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'other_attributes']));
        return new IdentiteResource($identite);
    }

    /**
     * Display the specified resource.
     *
     * @param  Identite  $identite
     * @return IdentiteResource
     */
    public function show(Identite $identite)
    {
        return new IdentiteResource($identite);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Identite $identite
     * @return IdentiteResource
     * @throws ValidationException
     */
    public function update(Request $request, Identite $identite)
    {
        $rules = [
            'sexe' => 'in:M,F',
            'email' => 'email|unique:identites,email,'.$identite->id,
            'other_attributes' => 'array'
        ];

        $this->validate($request, $rules);

        if($request->has('email') && $identite->email != $request->email){
            $user = $identite->user;
            if(! is_null($user)){
                $user->verified = User::UNVERIFIED_USER;
                $user->verification_token = User::generateVerificationToken();
                $user->save();
                event(new SendMail(new UserMailChanged($user->load('identite'))));
            }
            $identite->email = $request->email;
        }

        $identite->fill($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'other_attributes']));

        if(! $identite->isDirty()){
            return $this->errorResponse('You need to specify a different value to update', 422);
        }

        $identite->save();
        return new IdentiteResource($identite);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Identite $identite
     * @return IdentiteResource
     * @throws \Exception
     */
    public function destroy(Identite $identite)
    {
        $identite->secureDelete('user','claims');
        return new IdentiteResource($identite);
    }
}
