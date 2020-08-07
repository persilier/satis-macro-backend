<?php
namespace Satis2020\ServicePackage\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


/**
 * Class RelanceMail
 * @package Satis2020\ServicePackage\Mail
 */
class RelanceMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $data = [];

    /**
     * PdfReportingSend constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('satisfintech@example.com', 'Satis Fintech')
            ->subject('Relance')
            ->view('ServicePackage::mails.relance')
            ->with(['data' => $this->data]);
    }
}
