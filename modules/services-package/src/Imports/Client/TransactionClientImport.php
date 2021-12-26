<?php

namespace Satis2020\ServicePackage\Imports\Client;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use mysql_xdevapi\Exception;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Rules\NameModelRules;
use Satis2020\ServicePackage\Services\Imports\ClientImportService;
use Satis2020\ServicePackage\Traits\ClientTrait;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\ImportClient;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class TransactionClientImport implements OnEachRow, WithHeadingRow, WithChunkReading, ShouldQueue
{
    use IdentiteVerifiedTrait, VerifyUnicity, ImportClient, SecureDelete;
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
        return 500;
    }

    /***
     * @param Row $row
     */
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

//            $this->store($row, $this->stopIdentityExist, $this->updateIdentity);

            $identity = Identite::create([
                'firstname' => $row['firstname'],
                'lastname'  => $row['lastname'],
                'sexe'      => $row['sexe'],
                'telephone' => $row['telephone'],
                'email'     => $row['email'],
                'ville'     => $row['ville'],
            ]);

            $client = Client::create(['identites_id' => $identity->id]);

            $clientInstitution =  ClientInstitution::create([
                'category_client_id' => CategoryClient::first()->id,
                'client_id' => $client->id,
                'institution_id'  => Institution::first()->id
            ]);

            Account::create([
                'client_institution_id' => $clientInstitution->id,
                'account_type_id' => AccountType::first()->id,
                'number'  => $row['account_number']
            ]);

        }

    }

    /***
     * @param $data
     * @return mixed
     */
    protected function transformRowBeforeStoring($data)
    {
        $data['institution'] = $this->myInstitution ? $this->myInstitution : $data['institution'];
        $data['telephone'] = !is_null($data['telephone']) ? explode('/', $data['telephone']) : [];
        $data['email'] = !is_null($data['email']) ? explode('/', $data['email']) : [];

        return $data;
    }


}
