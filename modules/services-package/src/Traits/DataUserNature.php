<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\User;

trait DataUserNature
{
    protected $nature;
    protected $user;
    protected $institution;
    protected $staff;

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws RetrieveDataUserNatureException
     */
    protected function user()
    {
        $message = "Unable to find the user";

        try {
            $this->user = Auth::user();
        } catch (\Exception $exception) {
            throw new RetrieveDataUserNatureException($message);
        }

        if (is_null($this->user)) {
            throw new RetrieveDataUserNatureException($message);
        }

        return $this->user;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * @throws RetrieveDataUserNatureException
     */
    protected function institution()
    {

        $message = "Unable to find the user institution";

        try {
            $this->institution = Institution::with('institutionType')->findOrFail($this->staff()->institution_id);
        } catch (\Exception $exception) {
            throw new RetrieveDataUserNatureException($message);
        }

        if (is_null($this->institution)) {
            throw new RetrieveDataUserNatureException($message);
        }

        return $this->institution;
    }

    /**
     * @return mixed
     * @throws RetrieveDataUserNatureException
     */
    protected function staff()
    {

        $message = "Unable to find the user staff";

        try {
            $this->staff = $this->user()->load('identite.staff')->identite->staff;
        } catch (\Exception $exception) {
            throw new RetrieveDataUserNatureException($message);
        }

        if (is_null($this->staff)) {
            throw new RetrieveDataUserNatureException($message);
        }

        return $this->staff;
    }

    /**
     * @return mixed
     * @throws RetrieveDataUserNatureException
     */
    protected function nature()
    {

        $message = "Unable to find the nature of the application";

        try {
            $this->nature = json_decode(Metadata::where('name', 'app-nature')->firstOrFail()->data);
        } catch (\Exception $exception) {
            throw new RetrieveDataUserNatureException($message);
        }

        if (is_null($this->nature)) {
            throw new RetrieveDataUserNatureException($message);
        }

        return $this->nature;
    }

    protected function getAppNature($institutionId)
    {
        $institutionTargeted = Institution::with('institutionType')->findOrFail($institutionId);

        $nature = "PRO";

        switch ($institutionTargeted->institutionType->name) {
            case "filiale":
            case "holding":
                $nature = 'MACRO';
                break;

            case "observatory":
            case "membre":
                $nature = 'HUB';
                break;

            default:
                $nature = "PRO";
                break;
        }

        return $nature;
    }

}