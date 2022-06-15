<?php
namespace Satis2020\Escalation\Services;

use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\Escalation\Repositories\TreatmentBoardRepository;
use Satis2020\Escalation\Requests\EscalationConfigRequest;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Repositories\MetadataRepository;
use Satis2020\ServicePackage\Services\TreatmentBoardService;

class EscalationConfigService
{
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
        if ($request->specific_bord_exists){
            $treatmentBoardRepo = new TreatmentBoardRepository;
            $request->merge([
                'type'=>TreatmentBoard::STANDARD
            ]);

            $treatmentBoardRepo->store(
                $request->only([
                    'name',
                    'type'
                ],$request->members)
            );
        }
        return $this->metadataRepository->update($request->all(),Metadata::ESCALATION);
    }
}