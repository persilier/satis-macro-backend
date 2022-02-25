<?php

namespace Satis2020\ServicePackage\Imports\Client;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Rules\EmailValidationRules;
use Satis2020\ServicePackage\Rules\TelephoneArray;

class TransactionClientImport implements OnEachRow, WithHeadingRow, WithChunkReading, ShouldQueue
{

    protected $myInstitution;
    protected $data;

    /***
     * TransactionClientImport constructor.
     * @param $myInstitution
     * @param $data
     */
    public function __construct($myInstitution, $data)
    {
        $this->myInstitution = $myInstitution;
        $this->data = $data;
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
            $value = preg_replace("/./", "", $value);

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

        $data['category_client'] = optional(
            $this->data['categoryClients']->firstWhere('name', $data['category_client'])
        )->id;

        $data['account_type'] = optional(
            $this->data['accountTypes']->firstWhere('name', $data['account_type'])
        )->id;

        return $data;
    }

}
