<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArmadaTicket;
use App\Models\SecurityTicket;

class PoManual extends Model
{
    public $timestamps = false;
    protected $table = 'po_manual';
    protected $primaryKey = 'id';

    public function status()
    {
        switch ($this->status) {
            case -1:
                return 'Draft';
                break;
            case 0:
                return 'PO diterbitkan';
                break;
            case 1:
                return 'Internal sudah TTD';
                break;
            case 2:
                return 'Supplier sudah TTD';
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

    public function plate()
    {
        return ($this->gt_plate != "") ? $this->gt_plate : $this->gs_plate;
    }

    public function current_ticketing()
    {
        $po_number = $this->po_number;
        if ($po_number) {
            if (trim(strtolower($this->category_name)) == 'armada') {
                $ticket = ArmadaTicket::where('po_reference_number', $po_number)->where('status', '!=', -1)->first();
                return $ticket;
            }
            if (trim(strtolower($this->category_name)) == 'security') {
                $ticket = SecurityTicket::where('po_reference_number', $po_number)->where('status', '!=', -1)->first();
                return $ticket;
            }
            if (trim(strtolower($this->category_name)) == 'cit') {
                $ticket = Ticket::where('po_reference_number', $po_number)->where('status', '!=', -1)->first();
                return $ticket;
            }
            if (trim(strtolower($this->category_name)) == 'pest_control') {
                $ticket = Ticket::where('po_reference_number', $po_number)->where('status', '!=', -1)->first();
                return $ticket;
            }
            if (trim(strtolower($this->category_name)) == 'merchandiser') {
                $ticket = Ticket::where('po_reference_number', $po_number)->where('status', '!=', -1)->first();
                return $ticket;
            }
            return null;
        } else {
            return null;
        }
    }
}
