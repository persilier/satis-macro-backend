<?php

namespace Satis2020\ServicePackage\Imports\Client;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Satis2020\ServicePackage\Services\Imports\ClientImportService\ClientImportService;

class TransactionClientImport implements ToCollection, WithHeadingRow, WithChunkReading, WithValidation
{
    use Importable;

    protected $myInstitution;
    protected $stopIdentityExist;
    protected $updateIdentity;

    public function __construct($myInstitution, $updateIdentity, $stopIdentityExist)
    {
        $this->myInstitution = $myInstitution;
        $this->stopIdentityExist = $stopIdentityExist;
        $this->updateIdentity = $updateIdentity;
    }

    public function collection(Collection $collections)
    {
//        (new ClientImportService($collections))->store($this->myInstitution, $this->updateIdentity, $this->stopIdentityExist);
    }

    public function rules(): array
    {
        return [
            'institution' => 'required|exists:institutions,name',
            'category_client' => 'required',
            'account_type' => 'required',
            'account_number' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
            'sexe' => 'required',
            'telephone' => 'required',
            'email' => 'required',
            'ville' => 'required',
        ];
    }


    public function chunkSize(): int
    {
        return 1000;
    }
}
