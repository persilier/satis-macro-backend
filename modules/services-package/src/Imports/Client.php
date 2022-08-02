<?php
namespace Satis2020\ServicePackage\Imports;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\ImportClient;
use Satis2020\ServicePackage\Traits\ImportIdentite;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class Client
 * @package Satis2020\ServicePackage\Imports
 */
class Client implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    use Importable, SkipsFailures,DataUserNature, ImportClient, ImportIdentite, IdentiteVerifiedTrait, VerifyUnicity;

    private $etat; // action for
    private $errors; // array to accumulate errors
    private $myInstitution;
    private $stop_identite_exist;

    /**
     * Client constructor.
     * @param $etat
     * @param $myInstitution
     * @param $stop_identite_exist
     */
    public function __construct($etat, $myInstitution, $stop_identite_exist)
    {
        $this->etat = $etat;
        $this->myInstitution = $myInstitution;
        $this->stop_identite_exist = $stop_identite_exist;
    }

    /**
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection)
    {

        $collection = $collection->toArray();

        if(empty($collection)){

            throw new CustomException("Le fichier excel d'import des clients est vide.", 404);
        }

        // iterating each row and validating it:
        foreach ($collection as $key => $row) {
            // conversions email and telephone en table
            $data = $this->explodeValueRow($row, 'email', $separator = '/');
            $data = $this->explodeValueRow($data, 'telephone', $separator = '/', true);

            $validator = Validator::make($row, $this->rules());

            // fields validations
            if ($validator->fails()) {

                $errors_validations = [];

                foreach ($validator->errors()->messages() as $messages) {

                    foreach ($messages as $error) {

                        $errors_validations[] = $error;

                    }
                }

                $this->errors[$key] = [

                    'error' => $errors_validations,
                    'data' => $row
                ];

            } else {

                $data = $this->mergeMyInstitution($data);

                $data = $this->getIdInstitution($data, 'institution', 'name');

                $data = $this->getIds($data, 'category_clients', 'category_client', 'name');

                $data = $this->getIds($data, 'account_types', 'account_type','name');

                $verifyPhone = $this->handleClientIdentityVerification($data['telephone'], 'identites', 'telephone', 'telephone', $data['institution']);

                $verifyEmail = $this->handleClientIdentityVerification($data['email'], 'identites', 'email', 'email', $data['institution']);

                // Client Phone Unicity Verification

                if (!$verifyPhone['status']) {

                    if($this->etat === 0){

                        $this->errors[$key] = ['data' => $row] ?? (!$this->errors[$key]);
                        $this->errors[$key]['conflits']['telephone'] = $verifyPhone['message'];
                    }

                }

                // Client Email Unicity Verification

                if (!$verifyEmail['status']) {

                    if($this->etat === 0){

                        $this->errors[$key] = ['data' => $row] ?? (!$this->errors[$key]);
                        $this->errors[$key]['conflits']['email'] = $verifyEmail['message'];

                    }

                }

                // Account Number Verification
                $verifyAccount = $this->handleAccountVerification($data['account_number']);

                if (!$verifyAccount['status']) {

                    $this->errors[$key] = ['data' => $row] ?? (!$this->errors[$key]);
                    $this->errors[$key]['conflits']['account_number'] = $verifyAccount['message'];

                }else{

                    if(($verifyEmail['status'] === false) || ($verifyPhone['status'] === false)){

                        $identite = $this->getIdentite($data);

                        if($this->stop_identite_exist === 0){

                            if($this->etat === 1){

                                $identite->update($this->fillableIdentite($data));

                                $client = $this->storeClient($data, $identite->id);

                                $clientInstitution = $this->storeClientInstitution($data, $client->id);

                                $account = $this->storeAccount($data, $clientInstitution->id);

                            }else{

                                $client = $this->storeClient($data, $identite->id);

                                $clientInstitution = $this->storeClientInstitution($data, $client->id);

                                $account = $this->storeAccount($data, $clientInstitution->id);

                            }

                        }else{

                            $this->errors[$key] = ['data' => $row] ?? (!$this->errors[$key]);

                        }

                    }else{

                        $identite = $this->storeIdentite($data);

                        $client = $this->storeClient($data, $identite->id);

                        $clientInstitution = $this->storeClientInstitution($data, $client->id);

                        $account = $this->storeAccount($data, $clientInstitution->id);

                    }

                }

            }

        }

    }

    // this function returns all validation errors after import
    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

}
