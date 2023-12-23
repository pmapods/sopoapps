<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\BudgetUpload;
use App\Models\Ticket;
use App\Models\ArmadaTicket;
use App\Models\SecurityTicket;
use App\Models\Bidding;
use App\Models\Pr;
use App\Models\VendorEvaluation;

class NotificationMail extends Mailable implements ShouldQueue
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
        $type_name = ($this->data['transaction_type'] ?? "") . ' ' . ($this->data['ticketing_type'] ?? "");
        $budget_type = ($this->data['budget_type'] ?? "");
        $salespoint_name = $this->data['salespoint_name'] ?? "";
        $from = $this->data['from'] ?? "";
        $to = $this->data['to'] ?? "";
        $attachments = $this->data['attachments'] ?? [];
        $budget = BudgetUpload::where('code', $this->data['code'])->first();
        $ticket = Ticket::where('code', $this->data['code'])->first();
        $armadaticket = ArmadaTicket::where('code', $this->data['code'])->first();
        $securityticket = SecurityTicket::where('code', $this->data['code'])->first();
        $vendor_evaluation = VendorEvaluation::with('vendor_evaluation_detail')->where('code', $this->data['code'])->first();
        $email = null;
        // BUDGET
        if ($this->type == 'budget_approval') {
            $email = $this->subject('Approval Budget ' . $budget_type . ' ' . $salespoint_name)
                ->view('mail.notifications.budgetupload', compact('budget_type', 'from', 'to', 'budget', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'budget_reject') {
            $email = $this->subject('Reject Budget ' . $budget_type . ' ' . $salespoint_name)
                ->view('mail.notifications.budgetupload', compact('budget_type', 'from', 'to', 'budget', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'budget_approved') {
            $email = $this->subject('Full Approval Budget ' . $budget_type . ' ' . $salespoint_name)
                ->view('mail.notifications.budgetupload', compact('budget_type', 'from', 'to', 'budget', 'original_emails', 'original_ccs'));
        }
        // TICKETING
        if ($this->type == 'ticketing_approval') {
            $email = $this->subject('Approval Ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'armadaticket', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'ticketing_approved') {
            $email =  $this->subject('Full Approval Ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'armadaticket', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'ticketing_reject') {
            $email =  $this->subject('Reject Ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'armadaticket', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'ticketing_cancel') {
            $email =  $this->subject('Cancel Ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing');
        }
        if ($this->type == 'facilityform_approval') {
            $email =  $this->subject('Approval Form Fasilitas untuk ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'armadaticket', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'facilityform_reject') {
            $email =  $this->subject('Reject Form Fasilitas untuk ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'armadaticket', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'perpanjanganform_approval') {
            $perpanjanganform = $armadaticket->perpanjangan_form;
            $email =  $this->subject('Approval Form Perpanjangan untuk ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'armadaticket', 'perpanjanganform', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'perpanjanganform_approved') {
            $perpanjanganform = $armadaticket->perpanjangan_form;
            $email =  $this->subject('Full Approval Form Perpanjangan untuk ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'armadaticket', 'perpanjanganform', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'perpanjanganform_reject') {
            $perpanjanganform = $armadaticket->perpanjangan_form()->withTrashed()->first();
            $email =  $this->subject('Reject Form Perpanjangan untuk ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'armadaticket', 'perpanjanganform', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'mutasiform_approval') {
            $mutasiform = $armadaticket->mutasi_form;
            $email =  $this->subject('Approval Form Mutasi untuk ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'armadaticket', 'mutasiform', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'mutasiform_approved') {
            $mutasiform = $armadaticket->mutasi_form;
            $email =  $this->subject('Full Approval Form Mutasi untuk ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'armadaticket', 'mutasiform', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'mutasiform_reject') {
            $mutasiform = $armadaticket->mutasi_form()->withTrashed()->first();
            $email =  $this->subject('Reject Form Mutasi untuk ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'armadaticket', 'mutasiform', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'evaluasiform_approval') {
            $email =  $this->subject('Approval Form Evaluasi untuk ticketing ' . $type_name . ' ' . $salespoint_name);
        }
        if ($this->type == 'evaluasiform_approved') {
            $email =  $this->subject('Full Approval Form Evaluasi untuk ticketing ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'securityticket', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'evaluasiform_reject') {
            $email =  $this->subject('Reject Form Evaluasi untuk ticketing ' . $type_name . ' ' . $salespoint_name);
        }
        if ($this->type == 'legal_upload_file') {
            $email =  $this->subject('Legal sudah upload file COP ' .  $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'user_upload_evidance_overplafond') {
            $email =  $this->subject('User upload bukti transfer overplafond COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'user_reupload_evidance_overplafond') {
            $email =  $this->subject('User upload ulang bukti transfer overplafond COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'reject_evidance_overplafond') {
            $email =  $this->subject('Reject bukti tranfser overplafond COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }

        if ($this->type == 'reject_agreement_legal') {
            $email =  $this->subject('Reject File Perjanjian Tim Legal COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }

        if ($this->type == 'reject_tor_legal') {
            $email =  $this->subject('Reject File TOR Tim Legal COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }

        if ($this->type == 'reject_sph_legal') {
            $email =  $this->subject('Reject File SPH Tim Legal COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }

        if ($this->type == 'reject_user_agreement_legal') {
            $email =  $this->subject('Reject File Perjanjian User Tim Legal COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }

        if ($this->type == 'reupload_agreement_legal') {
            $email =  $this->subject('Upload Ulang File Perjanjian Tim Legal COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }

        if ($this->type == 'reupload_tor_legal') {
            $email =  $this->subject('Upload Ulang File TOR Tim Legal COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }

        if ($this->type == 'reupload_sph_legal') {
            $email =  $this->subject('Upload Ulang File SPH Tim Legal COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }

        if ($this->type == 'reupload_user_agreement_legal') {
            $email =  $this->subject('Upload Ulang File Perjanjian User Tim Legal COP '  . $salespoint_name)
                ->view('mail.notifications.ticketing', compact('type_name', 'from', 'to', 'ticket', 'original_emails', 'original_ccs'));
        }

        // BIDDING
        if ($this->type == 'bidding_approval') {
            $bidding = Bidding::find($this->data['bidding_id']);
            if ($bidding->product_name == 'Disposal Inventaris') {
                $email =  $this->subject('Approval Bidding Disposal Inventaris ' .  ' ' . $salespoint_name)
                    ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
            } elseif ($bidding->product_name == 'Fasilitas Karyawan COP ') {
                $email =  $this->subject('Approval Bidding Fasilitas Karyawan COP' .  ' ' . $salespoint_name)
                    ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
            } else {
                $email =  $this->subject('Approval Bidding ' . $type_name . ' ' . $salespoint_name)
                    ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
            }
        }
        if ($this->type == 'bidding_approved') {
            $bidding = Bidding::find($this->data['bidding_id']);
            if ($bidding->product_name == 'Disposal Inventaris') {
                $email =  $this->subject('Full Approval Bidding Disposal Inventaris ' . ' ' . $salespoint_name)
                    ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
            } elseif ($bidding->product_name == 'Fasilitas Karyawan COP') {
                $email =  $this->subject('Full Approval Bidding Fasilitas Karyawan COP ' . ' ' . $salespoint_name)
                    ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
            } else {
                $email =  $this->subject('Full Approval Bidding ' . $type_name . ' ' . $salespoint_name)
                    ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
            }
        }
        if ($this->type == 'bidding_reject') {
            $bidding = Bidding::find($this->data['bidding_id']);
            $email =  $this->subject('Reject Bidding ' . ' ' . $salespoint_name)
                ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'bidding_revision_file') {
            $bidding = Bidding::find($this->data['bidding_id']);
            if ($bidding->product_name == 'Disposal Inventaris') {
                $email =  $this->subject('Revision File Bidding Disposal Inventaris ' .  ' ' . $salespoint_name)
                    ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
            } elseif ($bidding->product_name == 'Fasilitas Karyawan COP') {
                $email =  $this->subject('Revision File Bidding Fasilitas Karyawan COP ' .  ' ' . $salespoint_name)
                    ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
            } else {
                $email =  $this->subject('Revision File Bidding ' . $type_name . ' ' . $salespoint_name)
                    ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
            }
        }
        if ($this->type == 'bidding_cancel') {
            $email =  $this->subject('Cancel Bidding ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.bidding', compact('bidding', 'from', 'to', 'original_emails', 'original_ccs'));
        }
        // PR MANUAL
        if ($this->type == 'pr_approval') {
            $pr = Pr::find($this->data['pr_id']);
            $email =  $this->subject('Approval PR ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.pr', compact('pr', 'from', 'to', 'type_name', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'pr_approved') {
            $pr = Pr::find($this->data['pr_id']);
            $email =  $this->subject('Full Approval PR ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.pr', compact('pr', 'from', 'to', 'type_name', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'pr_reject') {
            $pr = Pr::find($this->data['pr_id']);
            $email =  $this->subject('Reject PR ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.pr', compact('pr', 'from', 'to', 'type_name', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'pr_cancel') {
            $email =  $this->subject('Cancel PR ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.pr');
        }
        if ($this->type == 'pr_ga') {
            $pr = Pr::find($this->data['pr_id']);
            $email = $this->subject('PR Manual ready for PR SAP ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.pr', compact('pr', 'from', 'to', 'type_name', 'original_emails', 'original_ccs'));
        }
        // PO
        if ($this->type == 'po_approved') {
            $email = $this->subject('Full Approval PO ' . $type_name . ' ' . $salespoint_name)
                ->view('mail.notifications.po');
        }

        // VENDOR EVALUATION
        if ($this->type == 'vendor_evaluation_approval') {
            $email = $this->subject('Approval Vendor Evaluation ' . ' ' . $salespoint_name)
                ->view('mail.notifications.vendorevaluationnotification', compact('type_name', 'from', 'to', 'vendor_evaluation', 'original_emails', 'original_ccs'));
        }
        if ($this->type == 'vendor_evaluation_reject') {
            $email =  $this->subject('Reject Vendor Evaluation ' . ' ' . $salespoint_name)
                ->view('mail.notifications.vendorevaluationnotification', compact('type_name', 'from', 'to', 'vendor_evaluation', 'original_emails', 'original_ccs'));
        }

        foreach ($attachments ?? [] as $attach) {
            if ($attach) {
                $email->attachFromStorageDisk('public', $attach);
            }
        }

        return $email;
    }
}
