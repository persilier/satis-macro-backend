<?php
namespace Satis2020\ServicePackage\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\MonitoringClaim;


/**
 * Class RelanceCommand
 * @package Satis2020\ServicePackage\Console\Commands
 */
class RelanceCommand extends Command
{
    use MonitoringClaim;

    protected $signature = 'service:generate-relance';

    protected $description = 'Envoie des notifications pour les réclamations non traités dont le  délai de traitement est moins de trois jours.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @throws \Throwable
     */
    public function handle()
    {
        $claims = Claim::with($this->getRelations())->where('status', '!=','archived')->orWhere('status','!=', 'unfounded')
            ->get()->map(function ($item) {
            $item['time_expire'] = $this->timeExpire($item->created_at, $item->claimObject->time_limit);
            if($item['time_expire'] <= 3){
                $this->treamentRelance($item);
            }
        });

    }

}