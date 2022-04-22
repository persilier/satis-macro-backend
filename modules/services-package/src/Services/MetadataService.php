<?php


namespace Satis2020\ServicePackage\Services;


use Satis2020\ServicePackage\Repositories\MetadataRepository;

class MetadataService
{
    /**
     * @var MetadataRepository
     */
    private $repository;

    public function __construct(MetadataRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getByName($name)
    {
        return $this->repository->getByName($name);
    }

    public function getMetaByName($name)
    {
        return $this->repository->getMetadataByName($name);
    }

    public function updateMetadata($request,$name)
    {
        $data = [
            "title"=>$request->title,
            "description"=>$request->description,
        ];
        return $this->repository->update($data,$name);
    }

}