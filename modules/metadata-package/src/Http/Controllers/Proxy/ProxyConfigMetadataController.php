<?php

namespace Satis2020\MetadataPackage\Http\Controllers\Proxy;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    }

    /**
     * Display a listing of the resource.
     *
     * @param Metadata $metadata
     * @return JsonResponse
     */
    public function index()
    {
        $proxy = Constants::getProxyNames();

        $datas = $this->formatProxyMetas($proxy);
        return response()->json($datas);
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
            'proxy_http_server'=>['nullable','string'],
            'proxy_https_server'=>['nullable','string'],
            'proxy_http_port'=>['nullable','string'],
            'proxy_https_port'=>['nullable','string'],
            'proxy_modules'=>['array'],
            'proxy_modules.*'=>Rule::in(Constants::proxyModules())
        ]);

        return response()->json($metadataService->updateProxyMetadata($request));
    }


}

