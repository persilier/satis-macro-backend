<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\User;

/**
 * Trait DataUserNature
 * @package Satis2020\ServicePackage\Traits
 */
trait DataUserNature
{
    protected $nature;
    protected $user;
    protected $institution;
    protected $staff;

    /**
     * @return Authenticatable|null
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

        $message = __('messages.unable_to_fin_user_institution',[],getAppLang());
        $staff = $this->user()->load('identite.staff')->identite->staff;

        $this->institution = Institution::with('institutionType')->find($staff->institution_id);

        if ($this->institution == null) {
            throw new RetrieveDataUserNatureException($message);
        }

        return $this->institution;
    }

    protected function institutionWithUserId($user_id)
    {

        $message = "Unable to find the user institution";
        $staff = User::find($user_id)->load('identite.staff')->identite->staff;

        $this->institution = Institution::with('institutionType')->find($staff->institution_id);

        if ($this->institution == null) {
            throw new RetrieveDataUserNatureException($message);
        }

        return $this->institution;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * @throws RetrieveDataUserNatureException
     */
    protected function connectedInstitution()
    {
        return $this->institution();
    }

    /**
     * @return mixed
     * @throws RetrieveDataUserNatureException
     */
    protected function staff()
    {

        $message = __('messages.unable_to_fin_user_staff',[],getAppLang());

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

        $message = __('messages.unable_to_find_app_nature',[],getAppLang());

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


    /**
     * @param $row
     * @param $table
     * @param $keyRow
     * @param $column
     * @param bool $id
     * @return mixed
     */
    public function getIds($row, $table, $keyRow, $column)
    {
        if (array_key_exists($keyRow, $row)) {

            $model = DB::table($table)
                ->whereNull('deleted_at')
                ->where($column . '->' . App::getLocale(), $row[$keyRow])
                ->first();

            $row[$keyRow] = optional($model)->id;

        }

        return $row;

    }


    /**
     * @param $row
     * @param $table
     * @param $keyRow
     * @param $column
     * @param bool $id
     * @return mixed
     */
    public function getAccountIds($row, $table, $keyRow, $column)
    {
        if (array_key_exists($keyRow, $row)) {

            $model = DB::table($table)
                ->whereNull('deleted_at')
                ->where($column, $row[$keyRow])
                ->first();

            $row[$keyRow] = optional($model)->id;

        }

        return $row;

    }


    /**
     * @param $data
     * @return mixed
     */
    protected function libellePeriode($data)
    {

        $start = $data['startDate'];
        $end = $data['endDate'];

        if ($start === $end) {

            $libelle = $end->day . " " . $end->shortMonthName . " " . $end->year;

        } else {

            if ($start->year !== $end->year) {

                $libelle = $start->day . " " . $start->shortMonthName . " " . $start->year . " au " . $end->day . " " . $end->shortMonthName . " " . $end->year;

            } else {

                if ($start->month !== $end->month) {

                    $libelle = $start->day . " " . $start->shortMonthName . " au " . $end->day . " " . $end->shortMonthName . " " . $end->year;

                } else {

                    if ($start->day !== $end->day) {

                        $libelle = $start->day . " au " . $end->day . " " . $end->shortMonthName . " " . $end->year;

                    } else {

                        $libelle = $end->day . " " . $end->shortMonthName . " " . $end->year;
                    }

                }
            }
        }

        return $libelle;
    }

    protected function base64SaveImg($base64_img, $link_store, $add_name = "")
    {
        $extension = explode('/', mime_content_type($base64_img))[1];
        $safeName = $add_name . time() . '.' . $extension;
        $base64_image = $base64_img;
        $data = substr($base64_image, strpos($base64_image, ',') + 1);
        $data = base64_decode($data);
        $path = public_path("storage") . "/" . $link_store;
        if (!file_exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        file_put_contents($path . $safeName, $data);

        return ["ext" => $extension, "link" => "/storage/" . $link_store . $safeName];
    }


    protected function attachFilesToClaimBase64($claim, $request)
    {
        if ($request->has('attach_files')) {
            for ($i = 0; $i < sizeof($request->attach_files); $i++) {
                $save_img = $this->base64SaveImg($request->attach_files[$i], 'claim-attachments/', $i);
                $claim->files()->create(['title' => "attachement portal web" . $claim->reference, 'url' => $save_img['link']]);
            }
        }
    }


    /**
     * @param $request
     */
    protected function convertEmailInStrToLower($request)
    {

        try {
            if ($request->has('email') && !empty($request->has('email'))) {
                $request->merge(['email' => array_map('strtolower', $request->email)]);
            }
        } catch (\Exception $exception) {
        }

    }

    /**
     * @param $request
     */
    protected function checkEmailAllowDomain($request)
    {

        $config = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'coef-relance-domaine-prefixe')->firstOrFail()->data);
        if ($config) {
            if (sizeof($config) > 0) {
                $email = $request->email;
                $element_state = true;
                foreach ($email as $item){
                    $end_email = substr($item, strpos($item, "@") + 1);
                    if (!in_array($end_email, $config)) {
                        $element_state = false;
                        break;
                    }
                }
                return $element_state;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }


}
