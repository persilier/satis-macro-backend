<?php
namespace Satis2020\FaqPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class Faq extends JsonResource
{

    /** Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'question' => $this->question,
            'answer' => $this->answer,
            'category' => new FaqCategory($this->faqCategory),
        ];
    }
}

