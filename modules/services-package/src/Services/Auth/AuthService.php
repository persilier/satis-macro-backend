<?php

namespace Satis2020\ServicePackage\Services\Auth;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\In;
use Satis2020\ServicePackage\Exceptions\TwoSessionNotAllowed;
use Satis2020\ServicePackage\Models\InactivityReactivationHistory;
use Satis2020\ServicePackage\Models\LoginAttempt;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Repositories\UserRepository;
use Satis2020\ServicePackage\Requests\LoginRequest;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\CheckInactivityDuration;

class AuthService
{

    use \Satis2020\ServicePackage\Traits\Metadata, CheckInactivityDuration;

    /**
     * @var mixed
     */
    private $configs;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var \Illuminate\Contracts\Foundation\Application|mixed
     */
    private $userRepository;
    private $activityLogService;

    public function __construct(LoginRequest $request)
    {
        $this->configs = $this->getMetadataByName(Metadata::AUTH_PARAMETERS);
        $this->request = $request;
        $this->userRepository = app(UserRepository::class);
        $this->activityLogService = app(ActivityLogService::class);

    }

    /**
     * @return bool
     */
    public function accountExists()
    {
        return $this->userRepository
            ->getByEmail($this->request->username) !=null;
    }

    public function isAccountDisabled()
    {
        return $this->getUser()
                ->disabled_at!=null;
    }

    /**
     * @return bool
     */
    public function isAccountBlocked()
    {
        if ($this->configs->block_attempt_control){
            return $this->getAttempts()->attempts== $this->configs->max_attempt;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAccountActive()
    {

        if ($this->isAccountDisabled()){
            $response =  false;
        }else{

            if ($this->inactivityTimeIsPassed($this->getUser(),$this->configs)){
                if ($this->accountHasBeenReactivated($this->getUser())){
                    if ($this->inactivityTimeIsPassedAfReactivation($this->getUser(),$this->configs)){
                        $response = false;
                        $this->disableAccount();
                    }else{
                        $response = true;
                    }
                }else{
                    $response = false;
                    $this->disableAccount();
                }
            }else{
                $response = true;
            }

        }

        return $response;
    }

    /**
     * @return array
     */
    public function proceedAttempt()
    {
        $response = ["status"=>Response::HTTP_OK];

        $user = $this->getUser()->load('roles');

        $this->checkIfUserIsAlreadyConnected();

        if (!$user->hasRole(['admin-holding','admin-pro','admin-filial','admin-observatory'])){

            //check if account inactivity  control is activated
            if ($this->configs->inactivity_control){
                if (!$this->isAccountActive()){
                    $response = [
                        'status'=>Response::HTTP_BAD_REQUEST,
                        'message'=>$this->configs->inactive_account_msg
                    ];
                }
            }

            //check if password expiration control is activated
            if ($this->configs->password_expiration_control){
                if ($this->passwordIsExpired()){
                    $response = [
                        'status'=>Response::HTTP_LOCKED,
                        'message'=>$this->configs->password_expiration_msg
                    ];
                }
            }

            //check if login attempts control is activated
            if ($this->configs->block_attempt_control){
                $attempt_waiting_time = $this->configs->attempt_waiting_time*60;
                if ($this->isAccountBlocked()){
                    if ($this->getDurationSinceLastAttempt()>=$attempt_waiting_time){
                        $this->resetAttempts();
                        $response['status'] = Response::HTTP_OK;
                    }else{
                        //remaining time to wait in seconds
                        $remainingTime =  $this->getDurationSinceLastAttempt()+$attempt_waiting_time;
                        $response = [
                            'status'=>Response::HTTP_FORBIDDEN,
                            'expire_in'=>$remainingTime,
                            'message'=>$this->configs->account_blocked_msg
                        ];
                    }
                }
            }
        }
        if ($response['status']!=Response::HTTP_OK){
            $this->activityLogService->store(
                'Attempt login',
                null,
                ActivityLogService::ATTEMPT_LOGIN,
                'user',
                $this->getUser(),
                $this->getUser()
            );


        }
        return $response;
    }

    public function getUser()
    {
        return $this->userRepository
            ->getByEmail($this->request->username);
    }

    /**
     * @return int
     */
    public function getDurationSinceLastAttempt()
    {
        return  Carbon::parse($this->getAttempts()
            //->last_attempt_at)->diffInSeconds(now()->addSeconds(5));
            ->last_attempt_at)->diffInSeconds(now());

    }

    /**
     * @return bool
     */
    public function passwordIsExpired()
    {
        return Carbon::parse($this->getUser()
                ->password_updated_at)
                ->diffInWeekdays(now())>=$this->configs->password_lifetime;
    }

    /**
     * void
     */
    public function logAttempt()
    {
        $numberOfAtempts=1;
        if ($this->getDurationSinceLastAttempt()<$this->configs->attempt_delay*60){
            $numberOfAtempts = $this->getAttempts()->attempts+1;
        }
        $attempt = $this->getAttempts();
        $attempt->last_attempt_at = now();
        $attempt->attempts = $numberOfAtempts;
        $attempt->save();
    }

    /**
     * void
     * @param bool $loggedIn
     */
    public function resetAttempts($loggedIn=false)
    {
        $attempt = $this->getAttempts();
        $attempt->last_attempt_at = null;
        $attempt->attempts = 0;
        $attempt->save();
        if ($loggedIn){
            $this->activityLogService->store(
                'Attempt login',
                $this->userRepository->getInstitutionByUser($this->getUser()->id)->id,
                ActivityLogService::LOGIN,
                'user',
                $this->getUser(),
                $this->getUser()
            );
        }
    }

    /**
     * void
     */
    public function disableAccount()
    {
        $user = $this->userRepository->getByEmail($this->request->username);
        $this->userRepository->update(['disabled_at'=>now()]
            ,$user->id);
        InactivityReactivationHistory::query()
            ->create([
                "action"=>InactivityReactivationHistory::DEACTIVATION,
                "user_id"=>$user->id
            ]);
    }

    /**
     * @return Builder|Model|object
     */
    public function getAttempts()
    {
        $loginAttempt = LoginAttempt::query()
            ->where('ip',\request()->ip())
            ->where('email',$this->request->username)
            ->latest()
            ->first();
        if ($loginAttempt==null){
            $loginAttempt = LoginAttempt::query()
                ->create([
                    "ip"=>\request()->ip(),
                    "email"=>$this->request->username,
                ]);
        }
        return $loginAttempt;
    }

    public function checkIfUserIsAlreadyConnected()
    {
        $user = $this->getUser();

        //revoke all user expired tokens
        $now = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $user->tokens()
            ->where('expires_at','<',$now)
            ->update(['revoked'=>1]);

        $user->tokens()->limit(1)->get()->map(function ($token) use ($user) {
            if (!$token->revoked){
                throw new TwoSessionNotAllowed("Désolé, vous êtes déjà connecté sur un autre appareil");
            }
    });
    }
}