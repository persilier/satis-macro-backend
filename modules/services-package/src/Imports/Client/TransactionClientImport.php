<?php

namespace Satis2020\ServicePackage\Imports\Client;

use Faker\Factory;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Satis2020\ServicePackage\Models\Client;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Satis2020\ServicePackage\Models\Account;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Satis2020\ServicePackage\Models\Identite;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Rules\UniqueTelephoneRule;
use Satis2020\ServicePackage\Rules\EmailValidationRules;
use Satis2020\ServicePackage\Rules\UniqueEmailInIdentiteRule;

class TransactionClientImport implements OnEachRow, WithHeadingRow, WithChunkReading, SkipsOnFailure, SkipsOnError //, ShouldQueue
{
    use SkipsFailures, SkipsErrors;
    protected $myInstitution;
    protected $data;
    private $hasError = false;
    //private $errors; // array to accumulate errors
    private $stop_identite_exist;
    private $etat_update;


    /***
     * TransactionClientImport constructor.
     * @param $myInstitution
     * @param $data
     * @param $stop_identite_exist
     * @param $etat_update
     */
    public function __construct($myInstitution, $data, $stop_identite_exist, $etat_update)
    {
        $this->myInstitution = $myInstitution;
        $this->data = $data;
       // $this->errors = [];
        $this->stop_identite_exist = $stop_identite_exist;
        $this->etat_update = $etat_update;
    }

    /***
     * @return int
     */
    public function chunkSize(): int
    {
        return 30;
    }



    /***
     * @param Row $row
     */
    public function onRow(Row $row)
    {

        $rowIndex = $row->getIndex();
        $row = $row->toArray();


        $row = $this->transformRowBeforeStoring($row);

        $validator = $this->validateRow($row);

        if (!$validator->fails()) {

            //verify if an account with the provided account_number already exist
            $account = Account::query()
                ->where('number', $row['account_number'])
                ->leftJoin("client_institution", "client_institution.id", "=", "accounts.client_institution_id")
                ->where("client_institution.institution_id", $this->myInstitution->id)
                ->first();

            if ($row['telephone'] == null && $row['email'] == null) {
                if ($account == null) {
                    $faker = Factory::create();
                    $row['email'] = [$faker->email];
                }
            }

            $identity = null;
            $newIdentityExist = null;

            if ($row['telephone']) {
                foreach ($row['telephone'] as $phone) {
                    $identity = Identite::query()
                        ->whereJsonContains('telephone', $phone)
                        ->first();

                    if ($identity != null) {
                        $newIdentityExist = Identite::query()
                            ->where('id', '!=', $identity->id)
                            ->orWhereJsonContains('telephone', $phone)
                            ->first();
                        break;
                    }
                }
            }

            if ($identity == null &&  $row['email']) {

                foreach ($row['email'] as $email) {
                    $identity = Identite::query()
                        ->orWhereJsonContains('email', $email)
                        ->first();

                    if ($identity != null) {
                        $newIdentityExist = Identite::query()
                            ->where('id', '!=', $identity->id)
                            ->whereJsonContains('email', $email)
                            ->first();
                        break;
                    }
                }
            }

            if ($identity) {
                if (!$newIdentityExist) {
                    if ($this->etat_update == 1) {
                        $identity->update([
                            'firstname' => $row['firstname'],
                            'lastname' => $row['lastname'],
                            'sexe' => $row['sexe'],
                            'telephone' => $row['telephone'],
                            'email' => $row['email'],
                            'ville' => $row['ville'],
                        ]);
                    }
                } else {
                    Log::error($validator->errors());
                    $error = ['messages' => "Un compte avec ses informations existe déjà", 'data' => $row, 'line' => $rowIndex];
                    array_push($this->errors, $error);
                    $this->hasError = true;
                }
            } else {
                $identity = Identite::query()
                    ->create([
                        'firstname' => $row['firstname'],
                        'lastname' => $row['lastname'],
                        'sexe' => $row['sexe'],
                        'telephone' => $row['telephone'],
                        'email' => $row['email'],
                        'ville' => $row['ville'],
                    ]);
            }
            $client = Client::query()->updateOrCreate(
                ['identites_id' => $identity->id]
            );

            $clientInstitution = ClientInstitution::query()->updateOrCreate(
                ['client_id' => $client->id, 'institution_id' => $row['institution']],
                ['category_client_id' => $row['category_client']]
            );

            Account::query()->updateOrCreate(
                ['number' => $row['account_number'], 'client_institution_id' => $clientInstitution->id],
                ['account_type_id' => $row['account_type']]
            );

        } else {
            Log::error($validator->errors());
            $error = ['messages' => $validator->getMessageBag()->getMessages(), 'data' => $row, 'line' => $rowIndex];
            array_push($this->errors, $error);
            $this->hasError = true;
        }
    }

    protected function validateRow($row)
    {
        return Validator::make(
            $row,
            [
                'institution' => 'nullable|exists:institutions,id',
                'category_client' => ['required', 'exists:category_clients,id'],
                'account_type' => ['required', 'exists:account_types,id'],
                'account_number' => 'required',
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
                'telephone' => [
                    'array',
                    new TelephoneArray
                ],
                #'telephone.*'=>[new UniqueTelephoneRule()],
                'email' => [
                    'array',
                    new EmailValidationRules
                ],
                #'email.*'=>[
                #    "sometimes",
                #    new UniqueEmailInIdentiteRule()
                #],
                'ville' => 'nullable|string',
            ]
        );
    }

    /***
     * @param $data
     * @return mixed
     */
    protected function transformRowBeforeStoring($data)
    {

        foreach ($data as $key => $value) {
            $data[$key] = trim($value);
        }

        $institutionId = $this->myInstitution->id;

        if (array_key_exists('institution', $data)) {
            $institution = $this->data['institutions']->firstWhere('name', $data['institution']);

            if ($institution) {
                $institutionId = $institution->id;
            }
        }

        $data['institution'] = $institutionId;

        $data['telephone'] = !empty($data['telephone']) ? explode('/', $data['telephone']) : [];

        foreach ($data['telephone'] as $key => $value) {
            $value = preg_replace("/\s+/", "", $value);
            $value = preg_replace("/-/", "", $value);
            $value = preg_replace("/\./", "", $value);

            if (empty($value)) {
                unset($data['telephone'][$key]);
            } else {
                $data['telephone'][$key] = $value;
            }
        }

        $data['email'] = !empty($data['email']) ? explode('/', $data['email']) : [];

        foreach ($data['email'] as $key => $value) {
            $value = trim($value);
            $value = Str::lower($value);

            if (empty($value)) {
                unset($data['email'][$key]);
            } else {
                $data['email'][$key] = $value;
            }
        }

        $data['category_client'] = $this->getModelId( $this->data['categoryClients'],$data['category_client']);

        $data['account_type'] = $this->getModelId( $this->data['accountTypes'],$data['account_type']);

        return $data;
    }

    public function getModelId($models,$name)
    {
        $id = null;
        foreach ($models as $model){
            if (strtolower($model->name)==strtolower($name)){
                $id = $model->id;
                break;
            }
        }
        return $id;
    }

    public function getImportErrors()
    {
        return $this->errors;
    }


    public function headingRow(): int
    {
        return 2;
    }
    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
        array_push($this->errors, $failures);
    }
    public function onError(\Throwable $e)
    {
       return $this->errors;
    }
}
