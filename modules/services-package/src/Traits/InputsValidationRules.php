<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionException;

trait InputsValidationRules
{
    protected $layout_list = ['layout-1', 'layout-2', 'layout-3', 'layout-4'];
    protected $count_panel = 3;
    protected $required_list = ['id','type','name','label','placeholder', 'inputClass', 'value', 'cols', 'rows', 'required','maxLength','minLength','regExp'];
    protected $available_type = ['text', 'select', 'password', 'email', 'textarea'];
    protected $available_visible = [true, false];
    protected $available_required = [true, false];


    protected function typeValidation($param)
    {
        if (Arr::exists($param, 'type')) {
            if (!in_array($param['type'], $this->available_type)) {
                return false;
            }
        }
        return true;
    }

    protected function visibleValidation($param)
    {
        if (Arr::exists($param, 'visible')) {
            if (!in_array($param['visible'], $this->available_visible)) {
                return false;
            }
        }
        return true;
    }

    protected function requiredValidation($param)
    {
        if (Arr::exists($param, 'required')) {
            if (!in_array($param['required'], $this->available_required)) {
                return false;
            }
        }
        return true;
    }

    protected function multipleValuesValidation($param)
    {

        if (!(Arr::exists($param, 'values') || (Arr::exists($param, 'model') && Arr::exists($param, 'method')))) {
            return ['validation' => false, 'message' => "cannot determine values for {$param['name']}. values or model & method not found"];
        }

        // values validation
        if (Arr::exists($param, 'values')) {
            if (!$this->confirmValues($param['values'])) {
                return ['validation' => false, 'message' => "cannot determine values for {$param['name']}. values provided is not an expected array"];
            }
        }

        // model & method validation
        if (Arr::exists($param, 'model') && Arr::exists($param, 'method')) {

            // model validation
            if (!class_exists($param['model'])) {
                return ['validation' => false, 'message' => "cannot determine values for {$param['name']}. The model provided is not a valid class"];
            }

            // method validation
            try {
                $reflection_model = new ReflectionClass($param['model']);
                $method = $reflection_model->getMethod($param['method']);
                if (!$method->isStatic()) {
                    return ['validation' => false, 'message' => "cannot determine values for {$param['name']}. The method provided is not a valid static method of the model provided class"];
                }
            } catch (ReflectionException $exception) {
                return ['validation' => false, 'message' => "cannot determine values for {$param['name']}. The method provided is not a valid static method of the model provided class"];
            }

            if (!$this->confirmValues(call_user_func($param['model'] . '::' . $param['method']))) {
                return ['validation' => false, 'message' => "cannot determine values for {$param['name']}. values provided is not an expected array"];
            }

        }

        return ['validation' => true, 'message' => ""];
    }

    protected function confirmValues($values)
    {
        if (!is_array($values)) {
            return false;
        }

        $collection = collect($values);

        return $collection->every(function ($value, $key) {
            $keys = array_keys($value);
            return count($keys) == 2 && in_array('label', $keys) && in_array('value', $keys);
        });
    }


}