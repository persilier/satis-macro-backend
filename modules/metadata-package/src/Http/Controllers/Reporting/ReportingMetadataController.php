<?php

namespace Satis2020\MetadataPackage\Http\Controllers\Reporting;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Satis2020\MetadataPackage\Http\Resources\Metadata as MetadataResource;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\MetadataService;
use Satis2020\ServicePackage\Traits\Metadata as MetadataTraits;

class ReportingMetadataController extends ApiController
{
    use MetadataTraits;

    /**
     * MetadataController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-titles-configs')->only(['index']);
        $this->middleware('permission:edit-reporting-titles-configs')->only(['edit']);
        $this->middleware('permission:update-reporting-titles-configs')->only(['update']);


    }

    /**
     * Display a listing of the resource.
     *
     * @param Metadata $metadata
     * @return JsonResponse
     */
    public function index()
    {
        $types = Constants::getReportTypesNames();
        $datas = $this->formatReportTitleMetas($types);
        return response()->json($datas);
    }

    /**
     * Display a listing of the resource.
     *
     * @param $name
     * @param MetadataService $metadataService
     * @return JsonResponse
     */
    public function edit($name,MetadataService $metadataService)
    {
        if ($metadataService->getByName($name)==null)
            abort(Response::HTTP_NOT_FOUND);

        $datas = $this->formatReportTitleMetas([$name])[0];
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
            'name'=>['required','string','exists:metadata,name'],
            'title'=>['required','string'],
            'description'=>['required','string'],
        ]);

        return response()->json($metadataService->updateMetadata($request,$request->name));
    }


}

