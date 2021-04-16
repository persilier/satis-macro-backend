<?php

namespace Satis2020\MetadataPackage\Http\Controllers\Installation;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\MetadataPackage\Http\Controllers\Formulaire\FormulaireController;
use Satis2020\MetadataPackage\Http\Controllers\Header\HeaderController;
use Satis2020\MetadataPackage\Http\Controllers\Metadata\MetadataController;
use Satis2020\MetadataPackage\POST_Caller\POST_Caller;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Traits\Formulaire;

class InstallationController extends ApiController
{
    use Formulaire;
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function next(Request $request)
    {
        $number_steps = collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'installation-steps')->first()->data))->count();
        $current_step = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'current-step')->first()->data);

        // Enregistrement des configurations de l'étape actuelle
        if ($current_step != 0) {
            $step = collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'installation-steps')->first()->data))->where('name', '=', $current_step)->first();

            // Enregistrement de la nature de l'application
            if ($step->family === "nature"){
                $rules = [
                    'nature' => ['required', Rule::in(collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'app-types')->first()->data))->pluck('libelle')->all())]
                ];
                $this->validate($request, $rules);
                Metadata::where('name', 'app-nature')->first()->update(['data'=> json_encode($request->nature)]);
            }

            // Enregistrement d'un formulaire
            if ($step->family === "register-form"){
                $rules = [
                    'name' => [
                        'required',
                        'string',
                        'max:50',
                        function ($attribute, $value, $fail) use ($step) {
                            if ($value !== $step->content->name) {
                                $fail('invalid name, the name has to be : '.$step->content->name);
                            }
                        },
                    ]
                ];

                $this->validate($request, $rules);

                $this->store($request->all());

            }

            // Enregistrement d'un header
            if ($step->family === "register-header"){
                Metadata::where('name', 'app-nature')->first()->update(['data'=> json_encode($request->nature)]);
            }

        }

        if($current_step == $number_steps){
            return response()->json("Installation terminée", 200);
        }

        // Mise à jour de l'étape actuelle
        Metadata::where('name', 'current-step')->first()->update(['data'=> json_encode($current_step+1)]);

        // Renvoyer les données nécessaires à l'affichage de l'étape suivante
        $next_step = collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'installation-steps')->first()->data))->where('name', '=', $current_step+1)->first();
        if ($next_step->family === "nature"){
            return response()->json($next_step, 200);
        }

        if ($next_step->family === "register-form"){
            return app(FormulaireController::class)->create($next_step->content->name);
        }

        if ($next_step->family === "register-header"){
            return app(HeaderController::class)->create($next_step->content->name);
        }

    }
}