<?php

namespace Satis2020\ServicePackage\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\ImportIdentite;
use Satis2020\ServicePackage\Traits\ImportStaff;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class Staff
 * @package Satis2020\ServicePackage\Imports
 */
class Staff implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures, DataUserNature, ImportIdentite, ImportStaff, VerifyUnicity;

    private $errors; // array to accumulate errors
    private $etat;
    private $myInstitution;
    private $unitRequired;
    private $stop_identite_exist;

    /**
     * Staff constructor.
     * @param $etat
     * @param $myInstitution
     * @param $unitRequired
     * @param $stop_identite_exist
     */
    public function __construct($etat, $myInstitution, $unitRequired, $stop_identite_exist)
    {
        $this->myInstitution = $myInstitution;
        $this->unitRequired = $unitRequired;
        $this->stop_identite_exist = $stop_identite_exist;
        $this->etat = $etat;
    }

    /**
     * @param Collection $collection
     * @return Collection
     * @throws CustomException
     */
    public function collection(Collection $collection)
    {

        $collection = $collection->toArray();
        if (empty($collection)) {

            throw new CustomException("Le fichier excel d'import des staffs est vide.", 404);
        }
        // iterating each row and validating it:
        foreach ($collection as $key => $row) {
            // conversions email and telephone en table
            $data = $this->explodeValueRow($row, 'email', $separator = '/');
            $data = $this->explodeValueRow($data, 'telephone', $separator = '/', true);
            $data = $this->explodeValueRow($data, 'roles', $separator = '/');

            $validator = Validator::make($row, $this->rules($row));
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

                $data = $this->modifiedDataKeysInId($data);

                // Unité Verification
                $verifiedUnit = $this->handleUnitVerification($data);

                if (!$verifiedUnit['status']) {

                    $this->errors[$key] = ['data' => $row] ?? (!$this->errors[$key]);
                    $this->errors[$key]['conflits']['unite'] = $verifiedUnit['message'];

                } else {

                    $staff = $this->verificationAndStoreStaff($data);

                    if ($staff['status'] === false) {

                        $this->errors[$key] = ['data' => $row] ?? (!$this->errors[$key]);
                        $this->errors[$key]['conflits']['staff'] = $staff['message'];

                    }

                }

            }

        }

    }

    public function headingRow(): int
    {
        return 2;
    }

    // this function returns all validation errors after import

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
