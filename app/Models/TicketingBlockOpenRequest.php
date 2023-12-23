<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class TicketingBlockOpenRequest extends Model
{
    protected $table = 'ticketing_block_open_request';
    protected $primaryKey = 'id';

    public function status_name()
    {
        switch ($this->status) {
            case 0:
                return 'Menunggu Konfirmasi BA';
                break;
            case 1:
                return 'Sudah Terkonfirmasi';
                break;
            case -1:
                return 'Reject BA dengan alasan ' . $this->reject_reason;
                break;
            default:
                return 'status_undefined';
                break;
        }
    }

    public function ticket()
    {
        $ticket = Ticket::where('code', $this->ticket_code)->first();
        $armadaticket = ArmadaTicket::where('code', $this->ticket_code)->first();
        $securityticket = SecurityTicket::where('code', $this->ticket_code)->first();
        $vendorevaluationticket = VendorEvaluation::where('code', $this->ticket_code)->first();

        if ($ticket != null) {
            return $this->belongsTo(Ticket::class, 'ticket_code', 'code');
        }
        if ($armadaticket != null) {
            return $this->belongsTo(ArmadaTicket::class, 'ticket_code', 'code');
        }
        if ($securityticket != null) {
            return $this->belongsTo(SecurityTicket::class, 'ticket_code', 'code');
        }
        if ($vendorevaluationticket != null) {
            return $this->belongsTo(VendorEvaluation::class, 'ticket_code', 'code');
        }
    }

    public function ticket_type_name()
    {
        $ticket = Ticket::where('code', $this->ticket_code)->first();
        $armadaticket = ArmadaTicket::where('code', $this->ticket_code)->first();
        $securityticket = SecurityTicket::where('code', $this->ticket_code)->first();
        $vendorevaluationticket = VendorEvaluation::where('code', $this->ticket_code)->first();

        if ($ticket != null) {
            return 'Barang Jasa';
        }
        if ($armadaticket != null) {
            return 'Armada';
        }
        if ($securityticket != null) {
            return 'Security';
        }
        if ($securityticket != null) {
            return 'Security';
        }
        if ($vendorevaluationticket != null) {
            return 'Vendor Evaluation';
        }
    }

    public function created_by_employee()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id');
    }

    public function rejected_by_employee()
    {
        return $this->belongsTo(Employee::class, 'rejected_by', 'id');
    }

    public function confirmed_by_employee()
    {
        return $this->belongsTo(Employee::class, 'confirmed_by', 'id');
    }
}
