<?php
namespace Satis2020\MetadataPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class Metadata extends JsonResource
{
    /**
     * @var
     */
    private $type;

   /* public function type($value){
        $this->type = $value;
        return $this;
    }*/

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param null $type
     */
    public function __construct($resource, $type = null)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;
        $this->type = $type;
    }
    /** Transform the resource into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
    public function toArray($request)
    {
        if(is_null($this->type))
            return [
                'error' => 'Le paramètre type est requis.',
                'code' => 422,
            ];
        if($this->type=='models')
            return $this->loadModels($this->type);
        if($this->type=='action-forms')
            return $this->loadActions($this->type);
        if($this->type=='forms')
            return $this->loadForms($this->type);
        if($this->type=='headers')
            return $this->loadHeaders($this->type);
        if($this->type=='installation-steps')
            return $this->loadInstallationSteps($this->type);
        if($this->type=='app-nature')
            return $this->loadAppNature($this->type);
    }

    protected function loadModels($type){
        return [
            'name' => $this->name,
            'description' => $this->description,
            'fonction' => $this->fonction
        ];
    }

    protected function loadActions($type){
        return [
            'name' => $this->name,
            'description' => $this->description,
            'endpoint' => $this->endpoint
        ];
    }

    protected function loadForms($type){
        return [
            'name' => $this->name,
            'description' => $this->description,
            'content_default' => $this->content_default
        ];
    }

    protected function loadHeaders($type){
        return [
            'name' => $this->name,
            'description' => $this->description
        ];
    }

    protected function loadInstallationSteps($type){
        return [
            'family' => $this->family,
            'title' => $this->title,
            'content' => $this->content,
            'number' => $this->name
        ];
    }

    protected function loadAppNature($type){
        return [
            'nature' => $this->nature
        ];
    }

}

