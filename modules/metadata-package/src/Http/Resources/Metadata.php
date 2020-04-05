<?php
namespace Satis2020\MetadataPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class Metadata extends JsonResource
{
    /**
     * @var
     */
    private $type;
    private $data;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param null $type
     * @param string $data
     */
    public function __construct($resource, $type =null, $data = 'meta')
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;
        $this->type = $type;
        $this->data = $data;
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

        if($this->type == 'models')
            return [
                'name' => $this->name,
                'description' => $this->description,
                'fonction' =>  $this->fonction,
            ];
        if($this->type == 'forms'){
            if($this->data == 'meta')
                return [
                    'name' => $this->name,
                    'description' => $this->description,
                    'content_default' => $this->content_default,
                ];
            if($this->data == 'data')
                return [
                    'name' => $this->name,
                    'description' => $this->description,
                    'content_default' =>  $this->content_default,
                    'content' => $this->content_current,
                ];
        }

        if($this->type == 'action-forms')
            return [
                'name' => $this->name,
                'description' => $this->description,
                'endpoint' => $this->endpoint,
            ];

    }

}
