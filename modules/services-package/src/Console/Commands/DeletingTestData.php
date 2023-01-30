<?php

namespace Satis2020\ServicePackage\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Message;
use Satis2020\ServicePackage\Models\NotificationProof;
use Satis2020\ServicePackage\Models\Treatment;

class DeletingTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:deleting-test-data';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'test data deletion script';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $appNature = Config::get('services.app_nature', 'PRO');

       // if ($appNature === 'PRO') {
            $this->info('--------------------------- VIDAGE DES DONNEES TESTS -----------------------------');
            $this->warn('------------------------ CETTE ACTION EST IRREVERSIBLE --------------------------');
            if ($this->confirm('êtes-vous sûr de vouloir supprimer les données tests ? (yes|no)[no]',true)) {
                DB::table('notifications')->truncate();
                DB::table('jobs')->truncate();
                File::query()->hasMorph(
                    'attachmentable',
                    [Claim::class, Message::class]
                )->forceDelete();
                Message::query()->forceDelete();
                DB::table('discussion_staff')->truncate();
                Discussion::query()->forceDelete();
                NotificationProof::query()->forceDelete();
                Claim::query()->update([
                    "active_treatment_id" => NULL
                ]);
                Treatment::query()->forceDelete();
                Claim::query()->forceDelete();
                Identite::query()->doesntHave('staff')
                    ->doesntHave('user')
                    ->doesntHave('client')
                    ->forceDelete();
                $this->info('Suppression effectuée...');
            } else {
                $this->info('Suppression annulée...');
            }
      //  }
    }
}
