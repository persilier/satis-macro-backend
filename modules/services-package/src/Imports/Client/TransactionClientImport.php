<?php

namespace Satis2020\ServicePackage\Imports\Client;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Rules\EmailValidationRules;
use Satis2020\ServicePackage\Rules\NameModelRules;

class TransactionClientImport implements OnEachRow, WithHeadingRow, ShouldQueue, WithChunkReading//, WithValidation
{
    protected $myInstitution;
    protected $stopIdentityExist;
    protected $updateIdentity;
    protected $clientImportService;

    public function __construct(
        $myInstitution,
        $updateIdentity,
        $stopIdentityExist,
        $clientImportService
    )
    {
        $this->myInstitution = $myInstitution;
        $this->stopIdentityExist = $stopIdentityExist;
        $this->updateIdentity = $updateIdentity;
        $this->clientImportService = $clientImportService;
    }

//    public function collection(Collection $collections)
//    {
//        foreach ($collections as $client) {
//            $this->clientImportService->store($client, $this->stopIdentityExist, $this->updateIdentity);
//        }
//    }


//    public function rules(): array
//    {
//        return [
//            'institution' => 'required|exists:institutions,name',
//            'category_client' => ['required', new NameModelRules(['table' => 'category_clients', 'column' => 'name'])],
//            'account_type' => ['required',  new NameModelRules(['table' => 'account_types', 'column'=> 'name'])],
//            'account_number' => 'required',
//            'firstname' => 'required|string',
//            'lastname' => 'required|string',
//            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
//            'telephone' => 'required|array',
//            'email' => [
//                'required', 'array', /*new EmailValidationRules*/
//            ],
//            'ville' => 'required',
//        ];
//    }
//
//    public function prepareForValidation($data, $index)
//    {
//        $data['institution'] = $this->myInstitution ? $this->myInstitution : $data['institution'];
//        $data['telephone'] = !is_null($data['telephone']) ? explode('/', $data['telephone']) : [];
//        $data['email'] = !is_null($data['email']) ? explode('/', $data['email']) : [];
//        return $data;
//    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $row = $this->transformRowBeforeStoring($row);

        Identite::create([
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'sexe' => $row['sexe'],
            'telephone' => $row['telephone'],
            'email' => $row['email'],
            'ville' => $row['ville'],
        ]);

//        dump($row);
//        $this->clientImportService->store($row, $this->stopIdentityExist, $this->updateIdentity);
    }

    public function transformRowBeforeStoring($data)
    {
        $data['institution'] = $this->myInstitution ? $this->myInstitution : $data['institution'];
        $data['telephone'] = !is_null($data['telephone']) ? explode('/', $data['telephone']) : [];
        $data['email'] = !is_null($data['email']) ? explode('/', $data['email']) : [];

        return $data;
    }
}
