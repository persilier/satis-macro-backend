<?php
namespace Satis2020\ServicePackage\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PdfReportingSend
 * @package Satis2020\ServicePackage\Mail
 */
class PdfRegulatoryStateReportingMail extends Mailable
{
    use Queueable, SerializesModels;

    private $data;

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
        return $this->subject('Reporting')
                    ->attach($this->data['file'], [
                        'mime' => 'application/pdf',
                    ])
                    ->markdown('ServicePackage::mails.pdf-regulatory-state-reporting');
    }
}
