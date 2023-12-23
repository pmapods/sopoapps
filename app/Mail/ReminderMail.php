<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $data;
    public $type;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $type)
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
        $transaction_type = $this->data['type'];
        if ($this->type == 'poreminder') {
            return $this->subject('Reminder Expired PO ' . $this->data['type'] . ' ' . $this->data['salespoint_name'])
                ->view('mail.expiredporeminder', compact('original_emails', 'original_ccs', 'transaction_type'));
        }
        if ($this->type == 'vendorevaluationreminder') {
            return $this->subject('Reminder Vendor Evaluation ' . $this->data['type'] . ' ' . $this->data['salespoint_name'])
                ->view('mail.vendorevaluationreminder', compact('original_emails', 'original_ccs', 'transaction_type'));
        }
        if ($this->type == 'assetnumberreminder') {
            return $this->subject('Reminder Proses IO / Melengkapi Nomor Asset ' . $this->data['salespoint_name'])
                ->view('mail.assetnumberreminder', compact('original_emails', 'original_ccs'));
        }
        if ($this->type == 'pomanualattachmentreminder') {
            return $this->subject('Reminder PO Manual Attachment')
                ->view('mail.pomanualattachmentreminder', compact('original_emails'));
        }
        if ($this->type == 'barangjasaitreminder') {
            return $this->subject('Reminder Barang Jasa IT')
                ->view('mail.barangjasaitreminder', compact('original_emails'));
        }
    }
}
