<?php
/**
 * Created by PhpStorm.
 * User: hellb
 * Date: 6/24/2018
 * Time: 5:35 PM
 */

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOTP extends Mailable
{
    use Queueable, SerializesModels;

    public $otpCode;
    public $user;
    public $msg;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otpCode, $user, $msg, $subject)
    {
        $this->otpCode = $otpCode;
        $this->user = $user;
        $this->msg = $msg;
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
            'user'      => $this->user,
            'otp_code'  => $this->otpCode,
            'year'      => $now->year,
            'message'   => $this->msg,
        ];

        $fromMail = env('MAIL_USERNAME_NOREPLY', 'dev@goodhrapp.com');

        return $this->from($fromMail, "KARADA")
            ->subject($this->subject)
            ->view('mails.send_otp')
            ->with($data);
    }
}
