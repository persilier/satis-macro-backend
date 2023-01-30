<?php

namespace Satis2020\ServicePackage\Services\Auth;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Repositories\HistoryPasswordRepository;
use Satis2020\ServicePackage\Repositories\UserRepository;
/***
 * Class UpdatePasswordService
 * @package Satis2020\ServicePackage\Services\Auth
 */
class UpdatePasswordService
{
    use \Satis2020\ServicePackage\Traits\Metadata;

    /***
     * @var UserRepository
     */
    protected $userRepository;

    /***
     * @var HistoryPasswordRepository
     */
    protected $historyPasswordRepository;

    /***
     * @var $historyPasswords
     */
    protected $historyPasswords;

    /***
     * @var $maxPassword
     */
    protected $maxPassword;

    /***
     * UpdatePasswordService constructor.
     * @param HistoryPasswordRepository $historyPasswordRepository
     * @param UserRepository $userRepository
     */
    public function __construct(HistoryPasswordRepository $historyPasswordRepository, UserRepository $userRepository)
    {
        $this->historyPasswordRepository = $historyPasswordRepository;
        $this->userRepository = $userRepository;
    }

    public function getByEmail($email)
    {
        return $this->userRepository->getByEmail($email);
    }

    /***
     * Check if the management of password histories is active and Update Password
     * @param $newPassword
     * @param $user
     * @return mixed
     * @throws CustomException
     */
    public function update($newPassword, $user)
    {
        $configParameters = $this->getMetadataByName(Metadata::AUTH_PARAMETERS);
        if ($configParameters && $configParameters->password_expiration_control) {
            $this->maxPassword = $configParameters->max_password_histories;
            $this->verifyPasswordExistInTheHistory($newPassword, $user);
            $this->historyPasswordRepository->create([
                "password" => bcrypt($newPassword),
                "user_id" => $user->id,
            ]);
        }

        return $this->userRepository->update([
            'password' => bcrypt($newPassword),
            'password_updated_at' => Carbon::now()
        ], $user->id);
    }

    /***
     * Verify password exists in the history
     * @param $newPassword
     * @param $user
     * @throws CustomException
     */
    protected function verifyPasswordExistInTheHistory($newPassword, $user)
    {
         $this->historyPasswords = $this->historyPasswordRepository->getPasswordForHistoryManagement($user->id,
             $this->maxPassword);

         if ($passwordExist = $this->historyPasswords->first(function ($item) use ($newPassword) {
             return Hash::check($newPassword, $item->password);
         })) {
             throw new CustomException('Le mot de passe saisi est se trouve dans les '.
                 $this->maxPassword . ' derniers mot de passes déjà utilisés.',
                 Response::HTTP_PRECONDITION_REQUIRED);
         }

         if ($this->historyPasswords->count() >= $this->maxPassword) {
             $this->historyPasswordRepository->delete($this->historyPasswords->first()->id);
         }
    }

}