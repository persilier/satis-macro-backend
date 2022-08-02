<?php
namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\MetadataPackage\Http\Resources\Header;
use Satis2020\ServicePackage\Models\TypeClient;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;

trait MetaWithResources
{
    public function getHeader($name){
        if(!$headers = Metadata::where('name','headers')->get()->first())
            return null;
        $datas = json_decode($headers->data);

        if(is_null($datas))
            return null;
        foreach ($datas as $key => $value){
            if($value->name == $name)
                return new Header($value);
        }
        return null;
    }


}
