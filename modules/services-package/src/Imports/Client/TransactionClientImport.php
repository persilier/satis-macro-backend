<?php

namespace Satis2020\ServicePackage\Imports\Client;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Satis2020\ServicePackage\Rules\NameModelRules;
use Satis2020\ServicePackage\Services\Imports\ClientImportService;

class TransactionClientImport implements OnEachRow, WithHeadingRow, WithChunkReading, ShouldQueue
{
    protected $myInstitution;
    protected $stopIdentityExist;
    protected $updateIdentity;

    public function __construct($myInstitution, $updateIdentity, $stopIdentityExist)
    {
        $this->myInstitution = $myInstitution;
        $this->stopIdentityExist = $stopIdentityExist;
        $this->updateIdentity = $updateIdentity;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $row = $this->transformRowBeforeStoring($row);

        $validator = Validator::make(
            $row,
            [
                'institution' => 'required|exists:institutions,name',
                'category_client' => ['required', new NameModelRules(['table' => 'category_clients', 'column' => 'name'])],
                'account_type' => ['required', new NameModelRules(['table' => 'account_types', 'column' => 'name'])],
                'account_number' => 'required',
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
                'telephone' => [
                    'required',
                    'array',
                    //new TelephoneArray
                ],
                'email' => [
                    'required',
                    'array',
                    //new EmailValidationRules
                ],
                'ville' => 'required',
            ]
        );

        if (!$validator->fails()) {

            (new ClientImportService())->store($row, $this->stopIdentityExist, $this->updateIdentity);

        }

    }

    public function transformRowBeforeStoring($data)
    {
        $data['institution'] = $this->myInstitution ? $this->myInstitution : $data['institution'];
        $data['telephone'] = !is_null($data['telephone']) ? explode('/', $data['telephone']) : [];
        $data['email'] = !is_null($data['email']) ? explode('/', $data['email']) : [];

        return $data;
    }
}
