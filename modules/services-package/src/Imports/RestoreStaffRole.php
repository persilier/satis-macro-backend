<?php
namespace Satis2020\ServicePackage\Imports;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Role;
use Satis2020\ServicePackage\Models\User;

/**
 * Class Staff
 * @package Satis2020\ServicePackage\Imports
 */
class RestoreStaffRole implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;

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
    public function __construct()
    {

    }

    /**
     * @param Collection $collection
     * @return int
     */
    public function collection(Collection $collection)
    {

        $collection = $collection->toArray();
        if(empty($collection)){
            throw new CustomException("Le fichier excel d'import des staffs est vide.", 404);
        }
        // iterating each row and validating it:
        foreach ($collection as $key => $row) {
          $data['email'] = trim($row['email']);
          $data['role'] = trim($row['roles']);
          $role = Role::query()
              ->where('name',$data['role'])
              ->first();
            $user = User::query()
                ->where('username',$data['email'])
                ->first();
            if (!is_null($user))
            $user->assignRole($role);
        }
        return 0;
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
