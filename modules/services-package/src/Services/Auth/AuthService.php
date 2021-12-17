<?php

namespace Satis2020\ServicePackage\Services\Auth;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Satis2020\ServicePackage\Models\LoginAttempt;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Repositories\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthService
{

    use \Satis2020\ServicePackage\Traits\Metadata;

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

    public function __construct(Request $request)
    {
        $this->configs = $this->getMetadataByName(Metadata::AUTH_PARAMETERS);
        $this->request = $request;
        $this->userRepository = app(UserRepository::class);

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
        return $this->userRepository
                ->getByEmail($this->request->username)
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
        $lastLogin = "2021-12-16 15:42:01";
        $response = true;
        if ($this->isAccountDisabled()){
            $response =  false;
        }else{
            if (
            Carbon::parse($lastLogin)->diffInWeekdays(now())>=
            $this->configs->inactivity_time_limit){
                $this->disableAccount();
                $response =  false;
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

        /*if (!$this->accountExists()){
            throw new NotFoundHttpException();
        }*/

        //check if account inactivity  control is activated
        if ($this->configs->inactivity_control){
            if (!$this->isAccountActive()){
                $response = [
                    'status'=>Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS,
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
            if ($this->isAccountBlocked()){

                if ($this->getDurationSinceLastAttempt()>$this->configs->attempt_waiting_time){
                    $this->resetAttempts();
                    $response['status'] = Response::HTTP_OK;
                }else{
                    $remainingTime =  $this->getDurationSinceLastAttempt()+$this->configs->attempt_waiting_time;
                    $response = [
                        'status'=>Response::HTTP_FORBIDDEN,
                        'expire_in'=>$remainingTime,
                        'message'=>$this->configs->account_blocked_msg
                    ];
                }
            }
        }
        return $response;
    }

    /**
     * @return int
     */
    public function getDurationSinceLastAttempt()
    {
        return  Carbon::parse($this->getAttempts()
            ->last_attempt_at)->diffInMinutes(now());
    }

    /**
     * @return bool
     */
    public function passwordIsExpired()
    {
        return Carbon::parse($this->userRepository
                ->getByEmail($this->request->username)
                ->password_updated_at)
                ->diffInWeekdays(now())>=$this->configs->password_lifetime;
    }

    /**
     * void
     */
    public function logAttempt()
    {
        $numberOfAtempts=1;
        if ($this->getDurationSinceLastAttempt()<$this->configs->attempt_delay){
            $numberOfAtempts = $this->getAttempts()->attempts+1;
        }
        $attempt = $this->getAttempts();
        $attempt->last_attempt_at = now();
        $attempt->attempts = $numberOfAtempts;
        $attempt->save();
    }

    /**
     * void
     */
    public function resetAttempts()
    {
        $attempt = $this->getAttempts();
        $attempt->last_attempt_at = null;
        $attempt->attempts = 0;
        $attempt->save();
    }

    /**
     * void
     */
    public function disableAccount()
    {
        $this->userRepository->update(['disabled_at'=>now()]
            ,$this->userRepository->getByEmail($this->request->username)->id);
    }

    /**
     * @return Builder|Model|object
     */
    public function getAttempts()
    {
        $loginAttempt = LoginAttempt::query()
            ->where('ip',\request()->ip())
            ->orWhere('email',$this->request->username)
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
}