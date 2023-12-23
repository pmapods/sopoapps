<?php

namespace App\Models;

use App\Models\Vendor;
use App\Models\EmailCC;
use App\Models\ArmadaTicket;
use App\Models\Authorization;
use App\Models\SecurityTicket;
use App\Models\POUploadRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Po extends Model
{
    use SoftDeletes;
    protected $table = 'po';
    protected $primaryKey = 'id';

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function armada_ticket()
    {
        return $this->belongsTo(ArmadaTicket::class);
    }

    public function security_ticket()
    {
        return $this->belongsTo(SecurityTicket::class);
    }

    public function po_detail()
    {
        return $this->hasMany(PoDetail::class);
    }

    public function ticket_vendor()
    {
        return $this->belongsTo(TicketVendor::class);
    }

    public function created_by_employee()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id')->withTrashed();
    }

    public function po_authorization()
    {
        return $this->hasMany(PoAuthorization::class);
    }

    public function po_upload_request()
    {
        return $this->hasOne(PoUploadRequest::class, 'id', 'po_upload_request_id');
    }

    public function cc_email()
    {
        return $this->hasMany(EmailCC::class);
    }

    public function status()
    {
        switch ($this->status) {
            case -1:
                return 'Draft';
                break;
            case 0:
                return 'PO diterbitkan (Belum Aktif)';
                break;
            case 1:
                return 'Menunggu TTD Vendor (Belum Aktif)';
                break;
            case 2:
                return 'Menunggu Konfirmasi PO (Belum Aktif)';
                break;
            case 3:
                return 'PO Aktif';
                break;
            case 4:
                return 'Closed PO';
                break;

            default:
                return 'status_undefined';
                break;
        }
    }

    public function issue()
    {
        // latest of many buat ambil latest issue
        return $this->hasOne(IssuePO::class, 'po_number', 'no_po_sap')->latestOfMany();
    }

    public function sender_email()
    {
        try {
            $vendor = Vendor::where('code', $this->vendor_code)->first();
            if ($vendor) {
                $emails = json_decode($vendor->email);
                return $emails;
            } else {
                return [];
            }
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function cc()
    {
        if ($this->ticket) {
            $salespoint = $this->ticket->salespoint;
            $additional_emails = $this->ticket->additional_emails();
        }
        if ($this->armada_ticket) {
            $salespoint = $this->armada_ticket->salespoint;
            $additional_emails = $this->armada_ticket->additional_emails();
        }
        if ($this->security_ticket) {
            $salespoint = $this->security_ticket->salespoint;
            $additional_emails = $this->security_ticket->additional_emails();
        }

        $positions = array_column(EmailCC::get()->toArray(), 'employee_position');

        $author_emails =  AuthorizationDetail::join('authorization', 'authorization.id', '=', 'authorization_detail.authorization_id')
            ->join('employee_position', 'employee_position.id', '=', 'authorization_detail.employee_position_id')
            ->join('employee', 'employee.id', '=', 'authorization_detail.employee_id')
            ->where('authorization.salespoint_id', $salespoint->id)
            ->whereIn('employee_position.id', $positions)
            ->select('employee.email')
            ->get()
            ->pluck('email')
            ->toArray();

        $emails = array_unique(array_values(array_merge($additional_emails, ($author_emails ?? []))));
        return $emails;
    }

    public function current_ticketing()
    {
        $po_number = $this->no_po_sap;
        if ($po_number) {
            if ($this->armada_ticket_id) {
                $ticket = ArmadaTicket::where('po_reference_number', $po_number)->where('status', '!=', -1)->first();
                return $ticket;
            }
            if ($this->security_ticket_id) {
                $ticket = SecurityTicket::where('po_reference_number', $po_number)->where('status', '!=', -1)->first();
                return $ticket;
            }
        }
        return null;
    }

    public function email_template()
    {
        if ($this->ticket) {
            $data = [
                "po_number" => $this->no_po_sap,
                "ticket_items" => $this->ticket->ticket_item,
            ];
            return $this->ticket->email_template($data);
        }
        if ($this->armada_ticket) {
            $po = Po::where('no_po_sap', $this->armada_ticket->po_reference_number)->first();
            $pomanual = PoManual::where('po_number', $this->armada_ticket->po_reference_number)->first();
            try {
                if ($po) {
                    $plate = $po->armada_ticket->armada->plate;
                } else {
                    $plate = $pomanual->plate();
                }
            } catch (\Throwable $e) {
                $plate = "-";
            }
            $unit_name = $this->armada_ticket->armada_type->name;
            $salespoint_name = $this->armada_ticket->salespoint->name;
            $data = [
                "po_number" => $this->no_po_sap ?? "",
                "salespoint_name" => $salespoint_name ?? "",
                "plate" => $plate ?? "",
                "unit_name" => $unit_name ?? "",
                "sender_address" => $this->sender_address ?? "",
                "send_address" => $this->send_address ?? "",
                "send_name" => $this->send_name ?? "",
                "pic" => $this->send_address ?? "",
                "phone" => $this->phone ?? "",
            ];
            return $this->armada_ticket->email_template($data);
        }
        if ($this->security_ticket) {
            $salespoint_name = $this->security_ticket->salespoint->name;
            $data = [
                "po_number" => $this->no_po_sap ?? "",
                "salespoint_name" => $salespoint_name ?? "",
                "pic_name" => $this->supplier_pic_name ?? "",
                "phone" => "",
                "finish_date" => "",
            ];
            return $this->security_ticket->email_template($data);
        }
    }
}
