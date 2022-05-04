<?php

namespace Satis2020\ServicePackage\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\Component;
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
    protected $description = 'Allow you to test the sms sending feature';

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

        if ($appNature === 'PRO') {
            Log::info('Suppression de la base de données test en cours...');
            DB::table('notifications')->truncate();
            DB::table('jobs')->truncate();
            File::query()->doesntHaveMorph(
                'attachmentable',
                [Component::class]
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
            Log::info('Suppression effectuée...');
            Account::query()->truncate();
            $clientIds = Client::query()->pluck("identites_id")->toArray();
            DB::table("identites")->whereIn("id",$clientIds)
                ->delete();
            Client::query()->truncate();
        }
        return 0;
    }
}