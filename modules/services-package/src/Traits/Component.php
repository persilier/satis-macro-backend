<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Rules\ComponentDoesNotExistRules;

trait Component
{

    protected function rules($request)
    {

        $rules = [
            'params' => 'required|array',
            'params.*' => 'required|array',
            'params.*.type' => ['required', Rule::in(['image', 'text'])],
            'name' => 'required|unique:components,name,NULL,NULL,deleted_at,NULL',
            'description' => 'nullable|string'
        ];

        try {

            foreach ($request->params as $attr => $value) {

                if ($value['type'] == 'image') {
                    $rules["params.$attr.value"] = 'mimes:jpeg,bmp,png,jpg,gif';
                }

                if ($value['type'] == 'text') {
                    $rules["params.$attr.value"] = 'nullable|string';
                }

            }

        } catch (\Exception $exception) {
        }

        return $rules;
    }

    protected function updateRules($request, $component)
    {

        $rules = [
            'description' => 'nullable|string'
        ];

        $params = json_decode($component->params);

        try {

            foreach ($params as $attr => $value) {

                if ($value->type == 'image') {
                    $rules["params_$attr"] = 'mimes:jpeg,bmp,png,jpg,gif';
                }

                if ($value->type == 'text') {
                    $rules["params_$attr"] = 'required|string';
                }

            }

        } catch (\Exception $exception) {
        }

        return $rules;
    }

    protected function formatComponent($component)
    {

        $params = collect(json_decode($component->params))->map(function ($value, $attr) use ($component) {

            if ($value->type == 'image' && $component->files->count() > 0) {
                $value->value = $component->files->first(function ($file, $fileKey) use ($value) {
                    return $file->id == $value->value;
                });
            }

            return $value;
        });

        $component->params = $params;

        return $component;
    }

}