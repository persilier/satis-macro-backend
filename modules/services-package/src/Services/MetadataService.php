<?php


namespace Satis2020\ServicePackage\Services;


use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Metadata as MetadataModel;
use Satis2020\ServicePackage\Repositories\MetadataRepository;
use Satis2020\ServicePackage\Traits\Metadata as MetadataTraits;

class MetadataService
{
    use MetadataTraits;

    /**
     * @var MetadataRepository
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new MetadataRepository;
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


    public function getProxy(){
        $proxy = [Constants::PROXY];
        $meta = $this->getAllDataProxyByTypes($proxy)->toArray();

        return json_decode($meta['data']["fr"],true);
    }

    public function updateProxyMetadata($request){
        return $this->repository->updateProxy($request->all());
    }

    public function destroyProxyMetadata(){
        return $this->repository->updateProxy(NULL);
    }

    public function proxyExist()
    {
        return $this->getProxy()!=null;
    }

    public function getRequestProxy()
    {

        $proxy = $this->getProxy();
        $http = !empty($proxy[Constants::PROXY_HTTP_PORT])?$proxy[Constants::PROXY_HTTP_SERVER].':'.$proxy[Constants::PROXY_HTTP_PORT]:$proxy[Constants::PROXY_HTTP_SERVER];
        $https = !empty($proxy[Constants::PROXY_HTTPS_PORT])?$proxy[Constants::PROXY_HTTPS_SERVER].':'.$proxy[Constants::PROXY_HTTPS_PORT]:$proxy[Constants::PROXY_HTTPS_SERVER];

        return [
            "http"=>$http,
            "https"=>$https
        ];
    }

}