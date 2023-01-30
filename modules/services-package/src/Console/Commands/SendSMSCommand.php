<?php

namespace Satis2020\ServicePackage\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionMessageApi;
use Satis2020\ServicePackage\Services\SendSMService;

class SendSMSCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:send-sms
                            {--institution= : The ID of the institution}
                            {--to= : The number of the recipient}
                            {--text= : The message to send}';

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
        $options = $this->options();

        if (!isset($options['institution'])) {
            $options['institution'] = optional(Institution::query()->first())->id;
        }

        $validator = Validator::make(
            $options,
            [
                'institution' => 'required',
                'to' => 'required',
                'text' => 'required',
            ],
            [
                'required' => 'The --:attribute option is required',
            ]
        );

        if ($validator->fails()) {

            $this->error(json_encode($validator->errors()));
            return 0;

        }

        $data = [
            'to' => $options['to'],
            'text' => $options['text']
        ];

        $data['institutionMessageApi'] = InstitutionMessageApi::with('messageApi')
            ->where('institution_id', $options['institution'])
            ->first();

        $response = (new SendSMService())->send($data);

        $this->info($response);

        return 1;
    }
}
