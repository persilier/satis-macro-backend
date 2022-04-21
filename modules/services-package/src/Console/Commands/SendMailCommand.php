<?php

namespace Satis2020\ServicePackage\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Satis2020\ServicePackage\Mail\SendMail;

class SendMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:send-mail
                            {--to= : The email of the recipient}
                            {--text= : The message to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allow you to test the email sending feature';

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

        $validator = Validator::make(
            $options,
            [
                'to' => ['required', 'email'],
                'text' => 'required',
            ],
            [
                'required' => 'The --:attribute option is required',
                'email' => 'The --:attribute option is not a valid email'
            ]
        );

        if ($validator->fails()) {

            $this->error(json_encode($validator->errors()));
            return 0;

        }

        try {
            Mail::to($options['to'])->send(new SendMail($options['text']));
        }catch (\Exception $e){
            Log::debug($e);
            $this->error($e);
        }
        return 0;
    }
}
