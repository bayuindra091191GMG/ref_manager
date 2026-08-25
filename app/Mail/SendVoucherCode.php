<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendVoucherCode extends Mailable
{
    use Queueable, SerializesModels;

    public $urls;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($urls, $subject)
    {
        $this->urls = $urls;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $now = Carbon::now('Asia/Jakarta');
        $data =[
            'urls'       => $this->urls,
            'year'      => $now->year,
        ];

        $fromMail = env('MAIL_USERNAME_NOREPLY', 'dev@goodhrapp.com');

        return $this->from($fromMail, "KARADA")
            ->subject($this->subject)
            ->view('mails.send_voucher')
            ->with($data);
    }
}
