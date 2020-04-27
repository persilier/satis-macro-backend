<?php


namespace Satis2020\ClientPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Satis2020\InstitutionPackage\Http\Resources\Institution;
use Satis2020\UnitPackage\Http\Resources\Unit;
use Satis2020\UserPackage\Http\Resources\Identite;
class Client extends JsonResource
{
    /**
     * The additional meta data that should be added to the resource response.
     *
     * Added during response construction by the developer.
     *
     * @var array
     */
    public $additional = [];

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }
    /** Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'account_number'        => $this->account_number,
            'others'                => $this->others,
            'identite'              => New Identite($this->identite),
            'unit'                  => New Unit($this->unit),
            'institution'           => New Institution($this->institution),
            'type_client'           => New TypeClient($this->type_client),
            'category_client'       => New CategoryClient($this->category_client),
        ];
    }

    /**
     * Add additional meta data to the resource response.
     *
     * @param  array  $data
     * @return $this
     */
    public function additional(array $data)
    {
        $this->additional = $data;
        return $this;
    }

}