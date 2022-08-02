<?php
namespace Satis2020\Escalation\Services;

use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\Escalation\Repositories\TreatmentBoardRepository;
use Satis2020\Escalation\Requests\EscalationConfigRequest;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Repositories\MetadataRepository;
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
     */
    public function __construct()
    {
        $this->metadataRepository = new MetadataRepository;
    }

    public function get()
    {
        $treatmentBoadService = new TreatmentBoardService;
        $config = $this->metadataRepository->getByName(Metadata::ESCALATION);
        $data = json_decode($config->data,true);
        $data['id']=$config->id;
        $board = $treatmentBoadService->getStandardBoard()->load('members');

        if ($board!=null){
            $data['members'] = $board->members()->pluck('id')->toArray();
            $data['name'] = $board->name;
            $data['type'] = $board->type;
        }

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

            $board = $treatmentBoardService->getStandardBoard();
            if ($board==null){
                $treatmentBoardRepo->store($boardData,$request->members);
            }else{
                $treatmentBoardRepo->update($boardData,$board->id,$request->members);
            }
        }
        return $this->metadataRepository->update($request->all(),Metadata::ESCALATION);
    }
}