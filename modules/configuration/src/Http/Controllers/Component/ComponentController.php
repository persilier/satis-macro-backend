<?php

namespace Satis2020\Configuration\Http\Controllers\Component;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\Component;

class ComponentController extends ApiController
{
    use Component;

    public function __construct()
    {
        //parent::__construct();
        $this->middleware('set.language');

        $this->middleware('auth:api')->only(['update', 'store']);

        $this->middleware('permission:update-components-parameters')->only(['update', 'store']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(\Satis2020\ServicePackage\Models\Component::with('files')
            ->get()
            ->map(function ($item, $key) {
                return $this->formatComponent($item);
            }), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     * @throws CustomException
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules($request));

        DB::transaction(function () use ($request) {

            $params = [];
            $requestParams = $request->params;

            $component = \Satis2020\ServicePackage\Models\Component::create(['name' => $request->name, 'description' => $request->description]);

            foreach ($request->params as $attr => $value) {
                try {
                    if ($value['type'] == 'image' && $request->hasfile("params.$attr.value")) {

                        $title = $request->file("params.$attr.value")->getClientOriginalName();
                        $path = $request->file("params.$attr.value")->store('components', 'public');
                        $url = Storage::url("$path");

                        $file = $component->files()->create(['title' => $title, 'url' => $url]);

                        $params[$attr]['value'] = $file->id;

                    }
                } catch (\Exception $exception) {
                    throw new CustomException(json_encode(["params" => [$attr => ["value" => "Unable to upload the file"]]]), 422);
                }

                if ($value['type'] == 'text') {
                    $params[$attr]['value'] = $requestParams[$attr]['value'];
                }

                $params[$attr]['type'] = $requestParams[$attr]['type'];
            }

            $component->update(['params' => json_encode($params)]);

        });

        $component = \Satis2020\ServicePackage\Models\Component::where('name', $request->name)->firstOrFail();

        return response()->json($this->formatComponent($component), 201);

    }

    /**
     * Display the specified resource.
     *
     * @param \Satis2020\ServicePackage\Models\Component $component
     * @return \Illuminate\Http\Response
     */
    public function show(\Satis2020\ServicePackage\Models\Component $component)
    {
        $component->load('files');
        return response()->json($this->formatComponent($component), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param $componentName
     * @return \Illuminate\Http\Response
     */
    public function showByName($componentName)
    {
        $component = \Satis2020\ServicePackage\Models\Component::with('files')->where('name', $componentName)->firstOrFail();
        return response()->json($this->formatComponent($component), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Satis2020\ServicePackage\Models\Component $component
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     * @throws CustomException
     */
    public function update(Request $request, \Satis2020\ServicePackage\Models\Component $component)
    {
        $component->load('files');

        $this->validate($request, $this->updateRules($request, $component));

        DB::transaction(function () use ($request, $component) {

            $params = [];
            $componentParams = json_decode($component->params);

            foreach ($componentParams as $attr => $value) {
                try {
                    if ($value->type == 'image' && $request->hasfile("params_$attr")) {

                        // replace the property value of the stdObject by the file corresponding to the image
                        $value->value = $component->files->first(function ($file, $fileKey) use ($value) {
                            return $file->id == $value->value;
                        });

                        if (!is_null($value->value)) {
                            $value->value->forceDelete();

                            // if an image exist already, delete it
                            list($storage, $pathToImage) = explode('/storage/', $value->value->url);

                            if (Storage::disk('public')->exists($pathToImage)) {
                                Storage::disk('public')->delete($pathToImage);
                            }
                        }                        

                        $title = $request->file("params_$attr")->getClientOriginalName();
                        $path = $request->file("params_$attr")->store('components', 'public');
                        $url = Storage::url("$path");

                        $file = $component->files()->create(['title' => $title, 'url' => $url]);

                        $params[$attr]['value'] = $file->id;

                    }else{

                        $params[$attr]['value'] = $value->value;

                    }

                } catch (\Exception $exception) {
                    throw new CustomException(json_encode(["params" => [$attr => ["value" => "Unable to upload the file"]]]), 422);
                }

                if ($value->type == 'text') {
                    $params[$attr]['value'] = $request->{"params_$attr"};
                }

                $params[$attr]['type'] = $value->type;

            }

            $component->update(['params' => json_encode($params), 'description' => $request->description]);

        });

        $component = \Satis2020\ServicePackage\Models\Component::findOrFail($component->id);

        return response()->json($this->formatComponent($component), 201);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
