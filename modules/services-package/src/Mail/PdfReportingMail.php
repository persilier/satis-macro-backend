<?php
namespace Satis2020\ServicePackage\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PdfReportingSend
 * @package Satis2020\ServicePackage\Mail
 */
class PdfReportingMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $details = [];

    /**
     * PdfReportingSend constructor.
     * @param $details
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reporting')
                    ->attach($this->details['file'], [
                        'mime' => 'application/pdf',
                    ])
                    ->markdown('ServicePackage::mails.pdf-reporting')
                    ->with(['details' => $this->details]);
    }
}
