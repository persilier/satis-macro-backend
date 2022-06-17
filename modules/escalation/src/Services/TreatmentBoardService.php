<?php


namespace Satis2020\Escalation\Services;


use Illuminate\Support\Facades\Http;
use Satis2020\Escalation\Repositories\TreatmentBoardRepository;
use Satis2020\Escalation\Requests\TreatmentBoardRequest;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Traits\ClaimTrait;
use Satis2020\ServicePackage\Traits\DataUserNature;

class TreatmentBoardService
{

    use DataUserNature,ClaimTrait;

    /**
     * @var TreatmentBoardRepository
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new TreatmentBoardRepository;
    }

    public function getAll($size=15)
    {
        return $this->repository->getAll($size);
    }
    public function getById($id)
    {
        return $this->repository->getById($id);
    }

    public function store(TreatmentBoardRequest $request)
    {
        $request->merge(['created_by'=>$this->staff()->id]);


        $claim = $this->getOneClaimQuery($request->claim_id);
        $treatmentBord =  $this->repository->store($request->only([
           'name',
           'description',
           'type',
           'institution_id',
           'created_by'
       ]),$request->members);

        $claim->update(['treatment_board_id'=>$treatmentBord->id]);

        return $treatmentBord;
    }

    public function getStandardBoard()
    {
        return $this->repository->getStandardBoard();
    }

    public function update(TreatmentBoardRequest $request,$treatmentBoardId)
    {

       return $this->repository->update($request->only([
           'name',
           'description',
           'type',
       ]),$treatmentBoardId,$request->members);
    }
}