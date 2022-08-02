<?php
namespace Satis2020\UserPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Satis2020\ServicePackage\Traits\MetaWithResources;
class Permission extends JsonResource
{
    use MetaWithResources;

    /** Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'header' => $this->getHeader('rfscrefezf')
        ];
    }
}

