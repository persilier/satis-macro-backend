<?php

namespace Satis2020\UserPackage\Http\Controllers\User;

use Exception;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\SecureDeleteException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Mail\UserMailChanged;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Events\SendMail;
use Satis2020\ServicePackage\Mail\UserCreated;

class UserIdentiteController extends ApiController
{

    public function __construct()
    {
        $this->middleware('auth:api')->except(['verify', 'resend']);
        $this->middleware('set.language');
        $this->middleware('verification')->except(['verify', 'resend']);
        $this->middleware('can:view,user')->only(['show']);
        $this->middleware('can:update,user')->only(['update']);
        $this->middleware('permission:can-list-user')->only(['index']);
        $this->middleware('permission:can-show-user')->only(['show']);
        $this->middleware('permission:can-create-user')->only(['store']);
        $this->middleware('permission:can-update-user')->only(['update']);
        $this->middleware('permission:can-delete-user')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $header_with_list = $this->getListWithHeaderByHeaderName('user-list');

        $form_collection = $this->getForm(Formulaire::where('name', 'user')->firstOrFail());

        return $this->showAll(
            collect([
                'users' => $header_with_list['list'],
                'header' => $header_with_list['header'],
                'form' => $form_collection->toArray()['inputs']
            ])
        );
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return Response
     */
    public function show(User $user)
    {
        var_dump($user->load('identite'));
        exit;
        return $this->showOne($user->load('identite'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'firstname' => 'required',
            'lastname' => 'required',
            'sexe' => ['required', Rule::in(['M', 'F'])],
            'email' => 'email|unique:identites',
            'other_attributes' => 'array',
            'username' => 'required|unique:users',
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request, $rules);

        $identite = Identite::create($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'other_attributes']));

        $data = $request->only(['username', 'password']);
        $data['identite_id'] = $identite->id;
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationToken();

        $user = User::create($data);

        if ($request->has('email')){
            event(new SendMail(new UserCreated($user->load('identite'))));
        }
        return $this->showOne($user, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return Response
     * @throws ValidationException
     */
    public function update(Request $request, User $user)
    {
        $identite = $user->identite;

        $rules = [
            'sexe' => [Rule::in(['M', 'F'])],
            'email' => 'email|unique:identites,email,' . $identite->id,
            'other_attributes' => 'array',
            'username' => 'unique:users,username,' . $user->id,
            'password' => 'min:6|confirmed'
        ];

        $this->validate($request, $rules);

        $identite->fill($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'other_attributes']));

        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('email') && $identite->email != $request->email) {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationToken();
            $identite->email = $request->email;

            $identite->save();
            $user->save();

            event(new SendMail(new UserMailChanged($user->load('identite'))));

            return $this->showOne($user->load('identite'));
        }

        if (!$user->isDirty() && !$identite->isDirty()) {
            return $this->errorResponse('You need to specify a different value to update', 422);
        }

        $identite->save();
        $user->save();

        return $this->showOne($user->load('identite'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return Response
     * @throws Exception
     * @throws SecureDeleteException
     */
    public function destroy(User $user)
    {
        $user->secureDelete();
        return $this->showOne($user);
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
