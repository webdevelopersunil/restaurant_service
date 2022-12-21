<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewRatingAdminMail extends Mailable
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
        $email_body = '<p>Hello Admin,</p>
                    <p>The technician has given '. $this->details['rate'].' star rating for the job '. $this->details['restaurant_name'] .'.</p>
                    <p>Thankyou</p>';

        $subject = "New Job rating for ".$this->details['restaurant_name'];

        return $this->to(env('ADMIN_EMAIL'))
            ->from(trim(config('mail.from.address')) )
            ->subject(trim($subject))
            ->view('emails.email')
            ->with('logo', '')
            ->with('email_body', trim($email_body));



    }
}
