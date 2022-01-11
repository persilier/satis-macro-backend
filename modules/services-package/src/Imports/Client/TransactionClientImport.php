<?php

namespace Satis2020\ServicePackage\Imports\Client;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Rules\EmailValidationRules;
use Satis2020\ServicePackage\Rules\TelephoneArray;

class TransactionClientImport implements OnEachRow, WithHeadingRow, WithChunkReading, ShouldQueue
{

    protected $myInstitution;
    protected $stopIdentityExist;
    protected $updateIdentity;

    /***
     * TransactionClientImport constructor.
     * @param $myInstitution
     * @param $updateIdentity
     * @param $stopIdentityExist
     */
    public function __construct($myInstitution, $updateIdentity, $stopIdentityExist)
    {
        $this->myInstitution = $myInstitution;
        $this->stopIdentityExist = $stopIdentityExist;
        $this->updateIdentity = $updateIdentity;
    }

    /***
     * @return int
     */
    public function chunkSize(): int
    {
        return 2000;
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

            $account = Account::with([
                'client_institution.client.identite',
            ])
                ->where('number', $row['account_number'])
                ->first();

            if ($account) {
                $this->updateClientAccount($account, $row);
            } else {

                $identity = Identite::with('client.client_institution')
                    ->get()
                    ->filter(function ($identity, $key) use ($row) {

                        foreach ($row['telephone'] as $value) {
                            if (in_array($value, $identity->telephone)) {
                                return true;
                            }
                        }

                        foreach ($row['email'] as $value) {
                            if (in_array($value, $identity->email)) {
                                return true;
                            }
                        }

                        return false;
                    })
                    ->first();

                if ($identity) {
                    $this->updateClientIdentity($identity, $row);
                } else {
                    $this->createClientIdentity($row);
                }

            }

        } else {

            Log::error($validator->errors());

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
                    'required',
                    'array',
                    new TelephoneArray
                ],
                'email' => [
                    'required',
                    'array',
                    new EmailValidationRules
                ],
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
        $institutionId = $this->myInstitution->id;

        if (array_key_exists('institution', $data)) {
            $institution = Institution::where('name', $data['institution'])->first();

            if ($institution) {
                $institutionId = $institution->id;
            }
        }

        $data['institution'] = $institutionId;

        $data['telephone'] = !is_null($data['telephone']) ? explode('/', $data['telephone']) : [];

        foreach ($data['telephone'] as $key => $value) {
            $data['telephone'][$key] = preg_replace("/\s+/", "", $value);
        }

        $data['email'] = !is_null($data['email']) ? explode('/', $data['email']) : [];

        foreach ($data['email'] as $key => $value) {
            $data['email'][$key] = Str::lower($value);
        }

        $data['category_client'] = optional(
            CategoryClient::where('name->' . App::getLocale(), $data['category_client'])->first()
        )->id;

        $data['account_type'] = optional(
            AccountType::where('name->' . App::getLocale(), $data['account_type'])->first()
        )->id;

        return $data;
    }

    protected function updateModel($model, $row, $columnsToUpdate)
    {
        $data = collect($row)
            ->only($columnsToUpdate)
            ->all();

        $model->update($data);
    }

    protected function updateClientAccount($account, $row)
    {
        if (optional(optional($account)->client_institution)->institution_id) {

            if ($account->client_institution->institution_id == $row['institution']) {

                $clientInstitution = $account->client_institution;
                $this->updateModel(
                    $clientInstitution,
                    $row,
                    [
                        'category_client'
                    ]
                );

                if (optional(optional(optional($account)->client_institution)->client)->identite) {
                    $identity = $account->client_institution->client->identite;
                    $this->updateModel(
                        $identity,
                        $row,
                        [
                            'firstname',
                            'lastname',
                            'sexe',
                            'telephone',
                            'email',
                            'ville',
                        ]
                    );
                }

            }
        }
    }

    protected function updateClientIdentity($identity, $row)
    {
        $this->updateModel(
            $identity,
            $row,
            [
                'firstname',
                'lastname',
                'sexe',
                'telephone',
                'email',
                'ville',
            ]
        );

        if (optional($identity->client)->client_institution) {

            $clientInstitution = $identity->client->client_institution;

            if ($clientInstitution->institution_id == $row['institution']) {

                $account = $this->createAccount($clientInstitution, $row);

            } else {

                $clientInstitution = $this->addClientToInstitution($identity->client, $row);
                $account = $this->createAccount($clientInstitution, $row);

            }

        } else {

            $client = $this->createClient($identity);
            $clientInstitution = $this->addClientToInstitution($client, $row);
            $account = $this->createAccount($clientInstitution, $row);

        }
    }

    protected function createAccount($clientInstitution, $row)
    {
        return Account::create([
            'client_institution_id' => $clientInstitution->id,
            'account_type_id' => $row['account_type'],
            'number' => $row['account_number']
        ]);
    }

    protected function addClientToInstitution($client, $row)
    {
        return ClientInstitution::create([
            'category_client_id' => $row['category_client'],
            'client_id' => $client->id,
            'institution_id' => $row['institution']
        ]);
    }

    protected function createClient($identity)
    {
        return Client::create([
            'identites_id' => $identity->id
        ]);
    }

    protected function createClientIdentity($row)
    {
        $identity = Identite::create([
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'sexe' => $row['sexe'],
            'telephone' => $row['telephone'],
            'email' => $row['email'],
            'ville' => $row['ville'],
        ]);

        $client = $this->createClient($identity);
        $clientInstitution = $this->addClientToInstitution($client, $row);
        return $this->createAccount($clientInstitution, $row);

    }


}
