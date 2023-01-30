<?php

namespace Satis2020\ServicePackage\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Metadata;

class MetadataRepository
{
    use \Satis2020\ServicePackage\Traits\Metadata;
    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * MetadataRepository constructor.
     * @param Metadata $metadata
     */
    public function __construct()
    {
        $this->metadata = new Metadata;
    }

    public function getByName($name)
    {
        return $this->metadata
            ->newQuery()
            ->where('name',$name)
            ->first();
    }

    /**
     * @param $data
     * @param $name
     * @return Metadata
     */
    public function save($data,$name)
    {
        $this->metadata->name= $name;
        $this->metadata->data = json_encode($data);
        $this->metadata->save();
        return $this->metadata->refresh();
    }

    /**
     * @param $data
     * @param $name
     * @return Builder|Model|object
     */
    public function update($data,$name)
    {
        $metadata = $this->getByName($name);
        $updatedData = json_decode($metadata->data,true);
        foreach ($data as $key => $datum){
            $updatedData[$key] = $datum;
        }
        $metadata->data = json_encode($updatedData);
        $metadata->save();
        return $metadata->refresh();
    }


    /**
     * @param $data
     * @param $name
     * @return Builder|Model|object
     */
    public function updateProxy($data)
    {
        $metadata = $this->getByName(Constants::PROXY);
        $metadata->data = json_encode($data);
        $metadata->save();

        return $metadata->refresh();
    }

}