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
        return 1000;
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

            $identity = Identite::query()
                ->whereJsonContains('telephone', $row['telephone'])
                ->orWhereJsonContains('email', $row['email'])
                ->first(['id']);

            if ($identity) {
                $identity->update([
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname'],
                    'sexe' => $row['sexe'],
                    'telephone' => $row['telephone'],
                    'email' => $row['email'],
                    'ville' => $row['ville'],
                ]);
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

            $account = Account::query()->updateOrCreate(
                ['number' => $row['account_number'], 'client_institution_id' => $clientInstitution->id],
                ['account_type_id' => $row['account_type']]
            );

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
            $institution = Institution::query()
                ->where('name', $data['institution'])
                ->first(["id"]);

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
            CategoryClient::where('name->' . App::getLocale(), $data['category_client'])->first(['id'])
        )->id;

        $data['account_type'] = optional(
            AccountType::where('name->' . App::getLocale(), $data['account_type'])->first(['id'])
        )->id;

        return $data;
    }

}
