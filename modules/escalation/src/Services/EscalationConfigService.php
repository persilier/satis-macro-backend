<?php
namespace Satis2020\Escalation\Services;

use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\Escalation\Repositories\TreatmentBoardRepository;
use Satis2020\Escalation\Requests\EscalationConfigRequest;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Repositories\MetadataRepository;
use Satis2020\ServicePackage\Services\TreatmentBoardService;
use Satis2020\ServicePackage\Traits\DataUserNature;

class EscalationConfigService
{

    use DataUserNature;

    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    /**
     * AuthConfigService constructor.
     * @param MetadataRepository $metadataRepository
     */
    public function __construct(MetadataRepository $metadataRepository)
    {
        $this->metadataRepository = $metadataRepository;
    }

    public function get()
    {
        $config = $this->metadataRepository->getByName(Metadata::ESCALATION);
        $data = json_decode($config->data,true);
        $data['id']=$config->id;
        return $data;
    }

    /**
     * @param EscalationConfigRequest $request
     * @return Metadata
     */
    public function storeConfig(EscalationConfigRequest $request)
    {
        return $this->metadataRepository->save($request->all(),Metadata::ESCALATION);
    }

    /**
     * @param EscalationConfigRequest $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function updateConfig(EscalationConfigRequest $request)
    {

        if ($request->filled('standard_bord_exists') && $request->standard_bord_exists){
            $treatmentBoardRepo = new TreatmentBoardRepository;
            $request->merge([
                'type'=>TreatmentBoard::STANDARD
            ]);
            $treatmentBoardService = new \Satis2020\Escalation\Services\TreatmentBoardService;

            $boardData = $request->only([
                'name',
                'type',
                'institution_id'
            ]);
            if ($treatmentBoardService->getStandardBoard()==null){
                $treatmentBoardRepo->store($boardData,$request->members);
            }else{
                $treatmentBoardRepo->update($boardData,$request->members);
            }
        }
        return $this->metadataRepository->update($request->all(),Metadata::ESCALATION);
    }
}