<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
        if(array_key_exists($keyRow, $row)) {
            // put keywords into array
            try {

                $lang = app()->getLocale();

                $data = DB::table($table)->whereNull('deleted_at')->get();

                $data = $data->filter(function ($item) use ($row, $column, $keyRow, $lang) {

                    $name = json_decode($item->{$column})->{$lang};

                    if($name === $row[$keyRow])

                        return $item;

                })->first()->id;

            } catch (\Exception $exception) {

                $data = null;

            }

            $row[$keyRow] = $data;
        }

        return $row;

    }


    /**
     * @param $data
     * @return mixed
     */
    protected function libellePeriode($data){

        $start = $data['startDate'];
        $end = $data['endDate'];

        if($start === $end){

            $libelle = $end->day." ".$end->shortMonthName." ".$end->year;

        }else{

            if($start->year !== $end->year){

                $libelle = $start->day." ".$start->shortMonthName." ".$start->year." au ".$end->day." ".$end->shortMonthName." ".$end->year;

            }else{

                if($start->month !== $end->month){

                    $libelle = $start->day." ".$start->shortMonthName." au ".$end->day." ".$end->shortMonthName." ".$end->year;

                }else{

                    if($start->day !== $end->day){

                        $libelle = $start->day." au ".$end->day." ".$end->shortMonthName." ".$end->year;

                    }else{

                        $libelle = $end->day." ".$end->shortMonthName." ".$end->year;
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

        return ["ext" => $extension, "link" =>  "/storage/" . $link_store . $safeName];
    }


    /**
     * @param $request
     */
    protected function convertEmailInStrToLower($request){

        try {
            if($request->has('email') && !empty($request->has('email') )){
                $request->merge(['email' => array_map('strtolower',$request->email)]);
            }
        } catch (\Exception $exception) { }

    }



}
