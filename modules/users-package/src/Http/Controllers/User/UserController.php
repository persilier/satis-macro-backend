<?php
namespace Satis2020\UserPackage\Http\Controllers\User;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Satis2020\UserPackage\Http\Resources\UserCollection;
use Satis2020\UserPackage\Http\Resources\User as UserResource;
use Satis2020\ServicePackage\Events\SendMail;
use Satis2020\ServicePackage\Mail\UserCreated;
use Satis2020\ServicePackage\Mail\UserMailChanged;

class UserController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity;

    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-user-my-institution')->only(['index']);
        $this->middleware('permission:show-user-my-institution')->only(['show']);
        $this->middleware('permission:store-user-my-institution')->only(['store']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return UserCollection
     */
    public function index()
    {
        $users =  User::all();
        return new UserCollection($users);
    }


    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return UserResource
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return UserResource
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request)
    {
        $rules = [
            'username' => 'required|unique:users',
            'password' => 'required|min:6|confirmed',
            'identite_id' => 'required|exists:identites,id'
        ];

        $this->validate($request, $rules);

        $data = $request->only(['username', 'password']);
        $data['identite_id'] = $request->identite_id;
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationToken();

        $user = User::create($data);
        event(new SendMail(new UserCreated($user->load('identite'))));

        $this->activityLogService->store("Nouvel utilisateur créé.",
            $this->institution()->id,
            ActivityLogService::NEW_USER_CREATED,
            'user',
            $this->user(),
            $user
        );

        return new UserResource($user);
    }

    public function create(){
        $identites = Identite::has('staff')->get();
        $users = collect($identites)->filter(function ($item, $key){
            return null == $item->user;
        })->flatten()->all();

        $data = [
            'identites' => $users
        ];
        return response()->json($data,200);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return Response
     * @throws ValidationException
     */
    /*public function update(Request $request, User $user)
    {
        $rules = [
            'firstname' => 'required',
            'lastname' => 'required',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'email' => 'array',
            'ville' => 'required|string',
            'other_attributes' => 'array',
            'username' => 'unique:users,username,' . $user->id,
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request, $rules);

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', 'id', $user->identite->id);
        if (!$verifyPhone['status']) {
            $verifyPhone['message'] = "We can't perform your request. The phone number ".$verifyPhone['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', 'id', $user->identite->id);
        if (!$verifyEmail['status']) {
            $verifyEmail['message'] = "We can't perform your request. The email address ".$verifyEmail['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyEmail, 409);
        }

        $user->identite->fill($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'other_attributes']));

        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('email') && $user->identite->email != $request->email) {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationToken();
            $user->identite->email = $request->email;

            $user->identite->save();
            $user->save();

            event(new SendMail(new UserMailChanged($user->load('identite'))));

            return $this->showOne($user->load('identite'));
        }

        if (!$user->isDirty() && !$user->identite->isDirty()) {
            return $this->errorResponse('You need to specify a different value to update', 422);
        }

        $user->identite->save();
        $user->save();

        return $this->showOne($user->load('identite'));
    }*/


    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return UserResource
     * @throws Exception
     * @throws SecureDeleteException
     */
    public function destroy(User $user)
    {
        $user->secureDelete();
        return new UserResource($user);
    }

    /**
     * @param $token
     * @return mixed
     */
    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;
        $user->save();
        return $this->showMessage('The account has been verified successfully');
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function resend(User $user)
    {
        if ($user->isVerified()) {
            $this->errorResponse('This user is already verified', 409);
        }
        event(new SendMail(new UserCreated($user->load('identite'))));
        return $this->showMessage('The verification email has been resent');
    }



}
