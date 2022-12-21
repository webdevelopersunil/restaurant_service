<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendReferralEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $details;

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
        $email_body = '<p>Hello '. $this->details['name'] .',</p>
                    <p>'. $this->details['restaurant_name'] .' has sent invitation for 2Top tech app.</p>
                    <p>Thankyou</p>';

        $subject = "Inviting referrals for 2Top Tech";

        return $this->to($this->details['email'])
            ->from(trim(config('mail.from.address')) )
            ->subject(trim($subject))
            ->view('emails.email')
            ->with('logo', '')
            ->with('email_body', trim($email_body));
    }
}
