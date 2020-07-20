<?php


namespace Satis2020\ServicePackage\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Rules\ExplodeEmailRules;
use Satis2020\ServicePackage\Rules\ExplodeTelephoneRules;
use Satis2020\ServicePackage\Rules\NameModelRules;

/**
 * Trait ImportClient
 * @package Satis2020\ServicePackage\Traits
 */
trait ImportClient
{

    /**
     * @param $row
     * @param $keyRow
     * @param string $separator
     * @return mixed
     */
    public function explodeValueRow($row, $keyRow, $separator = ' ')
    {
        if(array_key_exists($keyRow, $row)) {
            // put keywords into array
            $datas = explode($separator, $row[$keyRow]);
            $i = 0;
            $values = [];
            foreach($datas as $data)
            {
                $values[$i] = $data;
                $i++;
            }

            $row[$keyRow] = $values;
        }

        return $row;
    }


    /**
     * @param $row
     * @param $table
     * @param $keyRow
     * @param $column
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
     * @param $row
     * @param $keyRow
     * @param $column
     * @return mixed
     */
    public function getIdInstitution($row, $keyRow, $column)
    {
        if(array_key_exists($keyRow, $row)) {
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
     * @return array
     */
    protected function rules(){

        $rules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'telephone' => [
                'required', new ExplodeTelephoneRules,
            ],
            'email' => [
                'required', new ExplodeEmailRules,
            ],
            'ville' => 'required|string',
            'account_number' => 'required|string',
            'account_type' => ['required',
                new NameModelRules(['table' => 'account_types', 'column'=> 'name']),
            ],
            'category_client' => ['required',
                new NameModelRules(['table' => 'category_clients', 'column'=> 'name']),
            ],
            'others' => 'array',
            'other_attributes' => 'array',
        ];

        if (!$this->myInstitution)
            $rules['institution'] = 'required|exists:institutions,name';

        return $rules;

    }

    /**
     * @param $row
     * @return mixed
     */
    protected function mergeMyInstitution($row){

        if(!$this->myInstitution){

            $row['institution'] = $this->myInstitution;

        }

        return $row;
    }


    /**
     * @param $row
     * @return mixed
     */
    protected function storeIdentite($row)
    {
        $store = $this->fillableIdentite($row);
        return $identite = Identite::create($store);
    }

    /**
     * @param $row
     * @param $identiteId
     * @return array
     */
    protected function storeClient($row, $identiteId)
    {
        if(!$client = Client::where('identites_id', $identiteId)
            ->first()
        ){
            $store = [
                'identites_id' => $identiteId,
                'others'  => $row['others_clients'],
            ];

            $client = Client::create($store);
        }

        return $client;

    }


    /**
     * @param $row
     * @param $clientId
     * @return array
     */
    protected function storeClientInstitution($row, $clientId)
    {
        if(!$clientInstitution = ClientInstitution::where('institution_id', $row['institution'])
            ->where('client_id', $clientId)
            ->first()
        ){
            $store = [
                'category_client_id'  => $row['category_client'],
                'client_id' => $clientId,
                'institution_id'  => $row['institution']
            ];

            $clientInstitution = ClientInstitution::create($store);
        }

        return $clientInstitution;
    }

    /**
     * @param $row
     * @param $clientInstitutionId
     * @return array
     */
    protected function storeAccount($row, $clientInstitutionId)
    {
        $store = [
            'client_institution_id' => $clientInstitutionId,
            'account_type_id'  => $row['account_type'],
            'number'  => $row['account_number']
        ];
        return $account = Account::create($store);
    }


    /**
     * @param $row
     * @return mixed
     */
    protected function getIdentite($row){

        $identite = $this->handleInArrayUnicityVerification($row['email'], 'identites', 'email');

        if(!$identite['status']){
            $identite = $identite['entity'];
        }

        return $identite;
    }


    /**
     * @param $row
     * @return array
     */
    protected function fillableIdentite($row){

        return [
            'firstname' => $row['firstname'],
            'lastname'  => $row['lastname'],
            'sexe'      => $row['sexe'],
            'telephone' => $row['telephone'],
            'email'     => $row['email'],
            'ville'     => $row['ville'],
            'other_attributes' => $row['other_identites'],
        ];
    }


}