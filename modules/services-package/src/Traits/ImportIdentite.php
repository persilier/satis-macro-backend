<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Rules\ExplodeEmailRules;
use Satis2020\ServicePackage\Rules\ExplodeTelephoneRules;

/**
 * Trait ImportIdentite
 * @package Satis2020\ServicePackage\Traits
 */
trait ImportIdentite
{

    /**
     * @param $row
     * @param $keyRow
     * @param string $separator
     * @param bool $phone
     * @return mixed
     */
    public function explodeValueRow($row, $keyRow, $separator = '/', $phone = false)
    {
        if (array_key_exists($keyRow, $row)) {
            // put keywords into array
            $datas = explode($separator, $row[$keyRow]);
            $i = 0;
            $values = [];
            foreach ($datas as $data) {
                $values[$i] = $phone ? preg_replace("/\s+/", "", $data) : $data;
                $i++;
            }

            if ($keyRow === 'email') {
                foreach ($values as $key => $value) {
                    $values[$key] = trim(strtolower($value));
                }
            }

            $row[$keyRow] = $values;
        }

        return $row;
    }


    /**
     * @return array
     */
    protected function rulesIdentite()
    {

        $rules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'telephone' => [
                'nullable', new ExplodeTelephoneRules,
            ],
            'email' => [
                new ExplodeEmailRules,
            ],
            'ville' => 'nullable|string',
        ];

        return $rules;

    }


    /**
     * @param $row
     * @return mixed
     */
    protected function mergeMyInstitution($row)
    {

        if ($this->myInstitution) {
            $row['institution'] = $this->myInstitution;
        }

        return $row;
    }


    /**
     * @param $row
     * @param $keyRow
     * @param $column
     * @return mixed
     */
    public function getIdInstitution($row, $keyRow, $column)
    {
        if (array_key_exists($keyRow, $row)) {
            // put keywords into array
            try {
                $data = Institution::where($column, $row[$keyRow])->first()->id;
            } catch (\Exception $exception) {
                $data = null;
            }
            $row[$keyRow] = $data;
        }

        return $row;

    }


    /**
     * @param $row
     * @return mixed
     */
    protected function getIdentite($row)
    {

        $identite = $this->handleInArrayUnicityVerification($row['email'], 'identites', 'email');

        if (!$identite['status']) {

            $identite = $identite['entity'];

        }

        return $identite;
    }


    /**
     * @param $row
     * @return mixed
     */
    protected function storeIdentite($row)
    {
        $store = $this->fillableIdentite($row);
        return Identite::create($store);
    }


    /**
     * @param $row
     * @return array
     */
    protected function fillableIdentite($row)
    {
        return [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'sexe' => $row['sexe'],
            'telephone' => $row['telephone'],
            'email' => $row['email'],
            'ville' => $row['ville'],
        ];
    }


}
