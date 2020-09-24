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

    protected $identite;
    protected $claim;

    /**
     * PdfReportingSend constructor.
     * @param $identite
     * @param $claim
     */
    public function __construct($identite, $claim)
    {
        $this->identite = $identite;
        $this->claim = $claim;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Relance')
            ->markdown('ServicePackage::mails.relance')
            ->with(['claim' => $this->claim, 'identite' => $this->identite]);
    }
}
