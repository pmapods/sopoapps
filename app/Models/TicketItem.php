<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\PoDetail;
use App\Models\Po;

class TicketItem extends Model
{
    use SoftDeletes;
    protected $table = 'ticket_item';
    protected $primaryKey = 'id';

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function ticket_item_attachment()
    {
        return $this->hasMany(TicketItemAttachment::class);
    }

    public function ticket_item_file_requirement()
    {
        return $this->hasMany(TicketItemFileRequirement::class);
    }

    public function budget_pricing()
    {
        // hanya untuk barang terdaftar
        return $this->belongsTo(BudgetPricing::class);
    }

    public function maintenance_budget()
    {
        // hanya untuk maintenance
        return $this->belongsTo(MaintenanceBudget::class);
    }

    public function ho_budget()
    {
        // hanya untuk ho budget
        return $this->belongsTo(HOBudget::class);
    }

    public function pr_detail()
    {
        return $this->hasOne(PrDetail::class);
    }

    public function cancelled_by_employee()
    {
        if ($this->cancelled_by) {
            return Employee::find($this->cancelled_by);
        } else {
            return null;
        }
    }

    public function isFilesChecked()
    {
        $flag = true;
        foreach ($this->ticket_item_attachment as $attachment) {
            if ($attachment->status != 1) {
                $flag = false;
            }
        }
        foreach ($this->ticket_item_file_requirement as $requirement) {
            if ($requirement->status != 1) {
                $flag = false;
            }
        }
        return $flag;
    }

    public function bidding()
    {
        return $this->hasOne(Bidding::class);
    }

    public function custom_bidding()
    {
        return $this->hasOne(CustomBidding::class);
    }

    public function po_list()
    {
        $podetails = PoDetail::where('ticket_item_id', $this->id)->get();
        $po_ids = $podetails->pluck('po_id')->unique();
        return Po::whereIn('id', $po_ids)->get();
    }

    public function rejected_by_employee()
    {
        if ($this->file_missing_rejected_by != null) {
            return Employee::find($this->file_missing_rejected_by);
        } else {
            return null;
        }
    }
    public function revised_by_employee()
    {
        if ($this->file_missing_revised_by != null) {
            return Employee::find($this->file_missing_revised_by);
        } else {
            return null;
        }
    }
    public function confirmed_by_employee()
    {
        if ($this->file_missing_confirmed_by != null) {
            return Employee::find($this->file_missing_confirmed_by);
        } else {
            return null;
        }
    }
}
