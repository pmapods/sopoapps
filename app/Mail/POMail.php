<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Auth;

class POMail extends Mailable
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
        $mail_subject = (isset($this->data['mail_subject'])) ? $this->data['mail_subject'] : null;
        if($this->type == 'posignedrequest'){
            $default_subject = 'Keperluan Upload Tanda tangan Basah';
            if(!$mail_subject){
                $mail_subject = $default_subject;
            }
            $return_mail = $this->subject($mail_subject)
                            ->view('mail.posignedrequest',compact('original_emails','original_ccs'))
                            ->attachFromStorageDisk('public',$this->data['po']->internal_signed_filepath);
            if ($this->data['po']->additional_po_filepath) {
                $return_mail = $return_mail->attachFromStorageDisk('public',$this->data['po']->additional_po_filepath);
            }

            return $return_mail;
        }
        if($this->type == 'posignedreject'){
            return $this->subject('Penolakan Signed PO')
                        ->view('mail.posignedreject',compact('original_emails','original_ccs'));
        }
        if($this->type == 'vendorposignedreject'){
            $reject_notes       = $this->data['reject_notes'] ?? "";
            $rejected_by        = $this->data['rejected_by'] ?? "";
            $po_upload_request  = $this->data['po_upload_request'];
            $po                 = $this->data['po'];
            return $this->subject('Vendor "'.$po_upload_request->vendor_name.'" melaporkan kesalahan pada PO ('.$po->no_po_sap.')')
                        ->view('mail.vendorposignedreject',compact('original_emails','original_ccs','reject_notes','rejected_by','po','po_upload_request'));
        }
        if($this->type == 'poconfirmed'){
            return $this->subject('Konfirmasi Upload Tanda Tangan Basah dari supplier')
                        ->view('mail.poconfirmed',compact('original_emails','original_ccs'))
                        ->attachFromStorageDisk('public',$this->data['external_signed_filepath']);
        }
    }
}
