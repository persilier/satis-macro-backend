<?php

namespace Satis2020\ServicePackage\Traits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

trait ApiResponser
{
    private function successResponse($data, $code)
    {
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code)
    {
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    protected function showAll(Collection $collection, $code = 200)
    {
        if ($collection->isEmpty()) {
            return $this->successResponse(['data' => $collection], $code);
        }

        $transformer = null;
        if (isset($collection->first()->transformer)) {
            $transformer = $collection->first()->transformer;
        }

        $collection = $this->filterData($collection, $transformer);

        $collection = $this->sortData($collection, $transformer);

        $collection = $this->paginate($collection);

        $collection = $this->transformData($collection, $transformer);

        return $this->successResponse($collection, $code);
    }

    protected function showOne(Model $model, $code = 200)
    {
        $transformer = $model->transformer;

        $model = $this->transformData($model, $transformer);

        return $this->successResponse($model, $code);
    }

    protected function showMessage(String $message, $code = 200)
    {
        return $this->successResponse(['data' => $message], $code);
    }

    protected function sortData(Collection $collection, $transformer)
    {
        if (request()->has('sort_by')) {

            if (is_null($transformer)) {
                $attribute = request()->sort_by;
            } else {
                $attribute = $transformer::originalAttribute(request()->sort_by);
            }

            $collection = $collection->sortBy->{$attribute};
        }

        return $collection;
    }

    protected function filterData(Collection $collection, $transformer)
    {
        foreach (request()->query() as $query => $value) {

            if (is_null($transformer)) {
                $attribute = $query;
            } else {
                $attribute = $transformer::originalAttribute($query);
            }

            if (isset($attribute, $value)) {
                $collection = $collection->where($attribute, $value);
            }
        }

        return $collection;
    }

    protected function paginate(Collection $collection)
    {
        $rules = [
            'per_page' => 'integer|min:2|max:50',
        ];

        Validator::validate(request()->all(), $rules);

        if (request()->has('per_page')) {
            $page = LengthAwarePaginator::resolveCurrentPage();

            $perPage = (int)request()->per_page;

            $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

            $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPage()
            ]);

            $paginated->appends(request()->all());

            return $paginated;
        }

        return $collection;
    }

    protected function transformData($data, $transformer)
    {
        if (is_null($transformer)) {
            return $data->toArray();
        }

        $transformation = fractal($data, new $transformer);

        return $transformation->toArray();
    }

    /**
     * Permet de retourner un Menu
     *
     * @return mixed
     */
    protected function getMenus()
    {
        $maxLevel = Menu::max('level');

        $with = "";
        for ($i = 0; $i < $maxLevel - 1; $i++) {
            $with .= $i == 0 ? "children" : ".children";
        }

        return Menu::where('level', 1)->with($with)->get();
    }

    /**
     * Permet de retourner un formulaire
     *
     * @param $formulaire
     * @param bool $filterVisible
     * @param bool $hideSomeKeys
     * @return Collection
     */
    protected function getForm($formulaire, $filterVisible = true, $hideSomeKeys = true)
    {
        $collection = collect($formulaire->inputs);

        if ($filterVisible) {
            $collection = $collection->filter(function ($value, $key) {
                return $value['visible'] == 1;
            });
        }

        $collection = $collection->map(function ($item, $key) use ($hideSomeKeys, $formulaire) {

            $item['values'] = Arr::exists($item, 'values') ? $item['values'] : (Arr::exists($item, 'model') && Arr::exists($item, 'method') ? call_user_func($item['model'] . '::' . $item['method']) : null);

            if ($hideSomeKeys) {
                $item = Arr::except($item, $formulaire->getInputsHidden());
            }

            return $item;
        });

        return $formulaire_collection = collect([
            "name" => $formulaire->name,
            "description" => $formulaire->description,
            "inputs" => $this->filterInputs($collection->all())
        ]);

    }

    protected function getListWithHeaderByHeaderName($header_name, $params = null)
    {
        $header = Header::where('name', $header_name)->firstOrFail();
        $header_collection = $this->getHeader($header);
        if(is_null($params)){
            $collection = collect($this->{config('datatableheader.' . $header_name)}());
        }else{
            $collection = collect($this->{config('datatableheader.' . $header_name)}(implode(",", $params)));
        }

        return [
            'list' => $this->getListWithHeader($collection, $header_collection),
            'header' => $header_collection->toArray()['libelles'],
        ];
    }

    /**
     * Permet de renvoyer une liste reformatée en fonction du header envoyé
     *
     * @param Collection $collection
     * @param Collection $header_collection
     * @return Collection
     */
    protected function getListWithHeader(Collection $collection, Collection $header_collection)
    {

        return $collection->map(function ($model) use ($header_collection) {
            $available_attr = [];
            foreach ($header_collection->toArray()['content'] as $content) {
                $available_attr[Str::slug($content['id'])] = Arr::get($model, $content['value']);
            }
            return $available_attr;
        });

    }

    /**
     * Permet d'obtenir une collection de données liées à un Header
     *
     * @param Header $header
     * @param bool $filterVisible
     * @return Collection
     */
    protected function getHeader(Header $header, $filterVisible = true)
    {

        $collection = collect($header->content);

        if ($filterVisible) {
            $collection = $collection->filter(function ($value, $key) {
                return $value['visible'] == 1;
            });
        }

        return $header_collection = collect([
            "name" => $header->name,
            "description" => $header->description,
            "content" => $collection->all(),
            "libelles" => Arr::pluck($collection->all(), 'libelle')
        ]);
    }

    protected function addToOrUpdateHeader(Header $header, array $content_array)
    {

        $header_content_collection = collect($this->getHeader($header)->toArray()['content']);

        foreach ($content_array as $content) {

            $search = $header_content_collection->search(function ($value, $key) use ($content) {
                return $value['id'] == $content['id'];
            });

            if ($search !== False) {
                $header_content_collection->pull($search);
            }

            $header_content_collection->push($content);

        }

        return $header_content_collection->toArray();

    }

    protected function removeFromHeader(Header $header, $id)
    {

        $header_content_collection = collect($this->getHeader($header)->toArray()['content']);

        $search = $header_content_collection->search(function ($value, $key) use ($id) {
            return $value['id'] == $id;
        });

        if ($search === False) {
            return false;
        }

        $header_content_collection->pull($search);

        return $header_content_collection->toArray();

    }

    protected function removeFromFormulaire(Formulaire $formulaire, $name)
    {

        $formulaire_inputs_collection = collect($this->getForm($formulaire)->toArray()['inputs']);

        $search = $formulaire_inputs_collection->search(function ($value, $key) use ($name) {
            return $value['name'] == $name;
        });

        if ($search === False) {
            return false;
        }

        $formulaire_inputs_collection->pull($search);

        return $formulaire_inputs_collection->toArray();

    }

    /**
     * Epure les inputs d'un Formulaire et ne renvoie que les valeurs désirées
     *
     * @param $inputs
     * @return array
     */
    protected function filterInputs(array $inputs)
    {
        $input_values = [];
        foreach ($inputs as $value) {
            $fillable = [];
            foreach (Formulaire::inputsFillable() as $item) {
                if (Arr::exists($value, $item)) {
                    $fillable[$item] = $value[$item];
                }
            }
            $input_values[] = $fillable;
        }

        return $input_values;
    }

    protected function addToOrUpdateFormulaire(Formulaire $formulaire, array $inputs_array)
    {

        $formulaire_inputs_collection = collect($this->getForm($formulaire, false, false)->toArray()['inputs']);

        foreach ($inputs_array as $input) {

            $search = $formulaire_inputs_collection->search(function ($value, $key) use ($input) {
                return $value['name'] == $input['name'];
            });

            if ($search !== False) {
                $formulaire_inputs_collection->pull($search);
            }

            $formulaire_inputs_collection->push($input);

        }

        return $formulaire_inputs_collection->toArray();

    }

}
