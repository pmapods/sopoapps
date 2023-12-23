<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GlobalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $data;
    public $type;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data,$type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $original_emails = $this->data['original_emails'] ?? [];
        $original_ccs = $this->data['original_ccs'] ?? [];
        $from = $this->data['from'] ?? "";
        $to = $this->data['to'] ?? "";
        
        // RESET PASSWORD
        if($this->type == 'reset_password'){
            $email = $this->subject('Reset Password PODS')
                ->view('mail.resetpassword',compact('original_emails','original_ccs','from','to'));
        }

        return $email;
    }
}
