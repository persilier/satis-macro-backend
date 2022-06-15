<?php


namespace Satis2020\ServicePackage\Services;


use Illuminate\Support\Facades\Http;
use Satis2020\Escalation\Repositories\TreatmentBoardRepository;
use Satis2020\Escalation\Requests\TreatmentBoardRequest;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Traits\DataUserNature;

class TreatmentBoardService
{

    use DataUserNature;

    /**
     * @var TreatmentBoardRepository
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new TreatmentBoardRepository;
    }

    public function store(TreatmentBoardRequest $request)
    {
        $request->merge(['created_by'=>$this->staff()->id]);

       return $this->repository->store($request->only([
           'name',
           'description',
           'type',
       ]),$request->members);
    }


    public function update(TreatmentBoardRequest $request,$treatme)
    {
        $request->merge(['created_by'=>$this->staff()->id]);

       return $this->repository->store($request->only([
           'name',
           'description',
           'type',
       ]),$request->members);
    }
}