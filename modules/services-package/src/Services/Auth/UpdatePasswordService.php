<?php

namespace Satis2020\ServicePackage\Services\Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Repositories\HistoryPasswordRepository;
use Satis2020\ServicePackage\Repositories\UserRepository;

class UpdatePasswordService
{

    protected $userRepository;
    protected $historyPasswordRepository;
    protected $historyPasswords;
    protected $limitPassword;

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

    /***
     * @param $newPassword
     * @param $user
     * @return mixed
     * @throws CustomException
     */
    public function update($newPassword, $user)
    {
        $tes = true;
        if ($tes === true) {
            $this->limitPassword = 4;
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
     * @param $newPassword
     * @param $user
     * @throws CustomException
     */
    protected function verifyPasswordExistInTheHistory($newPassword, $user)
    {
         $this->historyPasswords = $this->historyPasswordRepository->getPasswordForHistoryManagement($user->id,
             $this->limitPassword);

         if ($passwordExist = $this->historyPasswords->first(function ($item) use ($newPassword) {
             return Hash::check($newPassword, $item->password);
         })) {
             throw new CustomException('Le mot de passe saisi est se trouve dans les '. $this->limitPassword . ' derniers mot de passes déjà utilisés.');
         }

         if ($this->historyPasswords->count() >= $this->limitPassword) {
             $this->historyPasswordRepository->delete($this->historyPasswords->first()->id);
         }
    }

}