<?php


namespace Satis2020\ServicePackage\Traits;


use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Unit;

trait PredictionAITrait
{


    public function institutionCategoryObject()
    {
        $institution = Institution::all();
        $category_object = \Satis2020\ServicePackage\Models\ClaimCategory::with(["claimObjects"])->get();
        return [
            "institution" => $institution,
            "category_object" => $category_object,
        ];
    }

    public function institutionClaim()
    {
        $instutitions = Institution::all();
        $data = [];
        foreach ($instutitions as $institution) {
            $oneline = $institution;
            $units = Claim::with("claimObject.claimCategory")
                ->where("institution_targeted_id", $institution->id)
                ->orderBy("created_at", "desc")
                ->get();
            $oneline["claims"] = $units;
            $data[] = $oneline;
        }
        return $data;
    }


    public function institutionTUnitTreatedClaimWithObject()
    {
        $instutitions = Institution::all();
        $data = [];
        foreach ($instutitions as $institution) {
            $oneline = $institution;
            $units = Unit::with("unitType", "claimObjects")
                ->whereHas("unitType", function ($query) {
                    $query->where("can_treat", true);
                })->where("institution_id", $institution->id)->get();
            $oneline["units"] = $units;
            $data[] = $oneline;
        }
        return $data;
    }

    public function institutionClaimTreated()
    {
        $instutitions = Institution::all();
        $data = [];
        foreach ($instutitions as $institution) {
            $oneline = $institution;
            $units = Claim::with("activeTreatment.responsibleUnit","claimObject.claimCategory")
                ->where("institution_targeted_id", $institution->id)
                ->whereIn("status", [Claim::CLAIM_TREATED, Claim::CLAIM_VALIDATED, Claim::CLAIM_ARCHIVED,])
                ->get();
            $oneline["claims"] = $units;
            $data[] = $oneline;
        }
        return $data;
    }


}