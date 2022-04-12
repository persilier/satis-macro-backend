<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Facades\Validator;
use Satis2020\MetadataPackage\Http\Resources\Formulaire as FormulaireResource;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Traits\Metadata as MetadataTraits;

trait Formulaire
{
    use MetadataTraits;

    protected function store ($data)
    {
        $formulaires = Metadata::where('name', 'forms')->where('data','!=', '')->firstOrFail();
        $models = Metadata::where('name', 'models')->where('data','!=', '')->firstOrFail();
        $actions = Metadata::where('name', 'action-forms')->where('data','!=', '')->firstOrFail();
        $formulaire = json_decode($formulaires->data);
        $model = json_decode($models->data);
        $action = json_decode($actions->data);

        $type = $formulaires->name;
        $rules = $this->rulesStoreDescription('formulaire');

        Validator::make($data, $rules)->validate();

        $data = (object) $data;
        
        $validOther = $this->validateOthersMeta($data, $type);
        if(false!=$validOther)
            return $this->errorResponse($validOther, 422);

        $data = $this->getOneData($formulaire, $data->name);
        if(false==$data)
            return $this->errorResponse(__('messages.form_desc_do_not_exist',[],getAppLang()),422);
        $key = $data['key'];
        if(!empty($data['value']->content))
            return $this->errorResponse(__('messages.unable _to_create_metadata',['type'=>$type],getAppLang()),422);

        $value = $data['value'];

        $data_update = $this->getCreateDataForm($formulaire, $key,$value);
        $update = json_encode($data_update);
        $formulaires->update(['data'=> $update]);
        $data_response = $this->getOneData(json_decode($update), $value->name);
        if(false==$data_response)
            return $this->errorResponse(__('messages.no_metadata_value_found',[],getAppLang()),422);
        return new FormulaireResource($data_response['value']);
    }
}