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
                    ->attach($this->data['file'], [
                        'mime' => 'application/pdf',
                    ])->subject('Reporting du periode du '.$this->data['dateStart'].' au '.$this->data['dateEnd'])
                    ->view('ServicePackage::mails.pdf-reporting')
                    ->with(['data' => $this->data]);
    }
}
