<?php
namespace Satis2020\ServicePackage\Console\Commands;
use Illuminate\Console\Command;
use Satis2020\ServicePackage\Traits\MonitoringClaim;
use Satis2020\ServicePackage\Traits\Notification;


/**
 * Class RelanceCommand
 * @package Satis2020\ServicePackage\Console\Commands
 */
class RelanceCommand extends Command
{
    use MonitoringClaim, Notification;

    protected $signature = 'service:generate-relance';

    protected $description = 'Envoie des notifications pour les réclamations non traités dont le  délai de traitement est moins de trois jours.';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {

        $this->info("Envoie de relance en cours...");

        $this->treatmentRelance( false);

        $this->treatmentRelance(true);

    }

}