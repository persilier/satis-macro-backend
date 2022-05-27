<?php

namespace Satis2020\MetadataPackage\Http\Controllers\Proxy;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\MetadataPackage\Http\Resources\Metadata as MetadataResource;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\MetadataService;
use Satis2020\ServicePackage\Traits\Metadata as MetadataTraits;

class ProxyConfigMetadataController extends ApiController
{
    use MetadataTraits;

    /**
     * MetadataController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:show-proxy-config')->only(['index']);
        $this->middleware('permission:update-proxy-config')->only(['update']);
        $this->middleware('permission:delete-proxy-config')->only(['delete']);

    }

    /**
     * @param MetadataService $metadataService
     * @return JsonResponse
     */
    public function index(MetadataService $metadataService)
    {
        return response()->json($metadataService->getProxy());
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param MetadataService $metadataService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request,MetadataService $metadataService)
    {

        $this->validate($request, [
            'proxy_http_server'=>['required','string'],
            'proxy_https_server'=>['required','string'],
            'proxy_http_port'=>['nullable','string'],
            'proxy_https_port'=>['nullable','string'],
            'proxy_modules'=>['array'],
            'proxy_modules.*'=>Rule::in(Constants::proxyModules())
        ]);

        $metadataService->updateProxyMetadata($request);
        return response()->json($metadataService->getProxy());
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param MetadataService $metadataService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function delete(MetadataService $metadataService)
    {
        return response()->json($metadataService->destroyProxyMetadata());
    }


}

