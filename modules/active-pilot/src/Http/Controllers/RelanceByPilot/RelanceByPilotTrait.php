<?php


namespace Satis2020\ActivePilot\Http\Controllers\RelanceByPilot;


trait RelanceByPilotTrait
{
    public function rules(){
        return [
            "message" => "required|string",
            "actor" => "required|string",
        ];
    }
}