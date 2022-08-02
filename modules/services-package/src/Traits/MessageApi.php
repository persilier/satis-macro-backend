<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use ReflectionClass;
use Satis2020\ServicePackage\MessageApiMethod;

trait MessageApi
{

    protected function getMethods($except = null)
    {
        $methodsUsed = \Satis2020\ServicePackage\Models\MessageApi::all()->pluck('method');
        return collect(get_class_methods(MessageApiMethod::class))
            ->filter(function ($value, $key) use ($methodsUsed, $except) {
                return is_null($except)
                    ? $methodsUsed->search($value, true) === false
                    : $methodsUsed->search($value, true) === false || $value == $except;
            })
            ->values();
    }

    /**
     * @param $method
     * @return array
     * @throws \ReflectionException
     */
    protected function getParameters($method)
    {
        return collect((new ReflectionClass(MessageApiMethod::class))
            ->getMethod($method)
            ->getParameters()
        )
            ->pluck('name')
            ->all();
    }

    /**
     * @param $rules
     * @param $request
     * @return array
     */
    protected function getRules($rules, $request)
    {
        try {
            $messageApi = \Satis2020\ServicePackage\Models\MessageApi::findOrFail($request->message_api_id);

            foreach ($messageApi->params as $param) {
                $rules['params.' . $param] = 'required';
            }

            $rulesFiltered = Arr::except($rules, ['params.to', 'params.text']);
        } catch (ModelNotFoundException $modelNotFoundException) {
            $rulesFiltered = $rules;
        }

        return $rulesFiltered;
    }

}