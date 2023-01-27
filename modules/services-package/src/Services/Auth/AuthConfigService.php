<?php
namespace Satis2020\ServicePackage\Services\Auth;

use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Repositories\MetadataRepository;
use Satis2020\ServicePackage\Requests\AuthConfigRequest;

class AuthConfigService
{
    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    /**
     * AuthConfigService constructor.
     * @param MetadataRepository $metadataRepository
     */
    public function __construct(MetadataRepository $metadataRepository)
    {
        $this->metadataRepository = $metadataRepository;
    }

    public function get()
    {
        return $this->metadataRepository->getByName(Metadata::AUTH_PARAMETERS);
    }

    /**
     * @param AuthConfigRequest $request
     * @return Metadata
     */
    public function storeConfig(AuthConfigRequest $request)
    {
        return $this->metadataRepository->save($request->all(),Metadata::AUTH_PARAMETERS);
    }

    /**
     * @param AuthConfigRequest $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function updateConfig(AuthConfigRequest $request)
    {
        return $this->metadataRepository->update($request->all(),Metadata::AUTH_PARAMETERS);
    }
}