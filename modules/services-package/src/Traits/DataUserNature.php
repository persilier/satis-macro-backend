<?php


namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
trait DataUserNature
{


    protected function getInstitution($staff)
    {
        $institution = Institution::with('institutionType')->find($staff);
        if (!is_null($institution)) {
            return $institution;
        }
        return null;
    }

    public function getNatureApp(){
        return $app_nature = json_decode(Metadata::where('name', 'app-nature')->firstOrFail()->data);
    }

}