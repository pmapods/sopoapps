<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ticket;

class InventoryBudget extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'inventory_budget';
    protected $appends = ['pending_quota', 'used_quota'];

    public function budget_upload()
    {
        return $this->belongsTo(BudgetUpload::class);
    }

    public function getPendingQuotaAttribute()
    {
        $count = 0;
        $budget_upload_id = $this->budget_upload_id;
        $tickets = Ticket::where('budget_upload_id', $budget_upload_id)->whereNotIn('status', [-1, 0])->get();
        foreach ($tickets as $ticket) {
            foreach ($ticket->ticket_item->where('isCancelled', false)->whereNotNull('budget_pricing_id') as $ticket_item) {
                $code = $ticket_item->budget_pricing->code;
                // jika kode sesuai dengan budget maka tambahin jumlahnya
                if ($code == $this->code) {
                    $count  += $ticket_item->count;
                }
            }
        }
        return $count - $this->getUsedQuotaAttribute();
    }

    public function getTicketsCode()
    {
        $tickets_code = [];
        $budget_upload_id = $this->budget_upload_id;
        $tickets = Ticket::where('budget_upload_id', $budget_upload_id)->where('status', "!=", -1)->get();
        foreach ($tickets as $ticket) {
            foreach ($ticket->ticket_item->where('isCancelled', false)->whereNotNull('budget_pricing_id') as $ticket_item) {
                $code = $ticket_item->budget_pricing->code;
                // jika kode sesuai dengan budget maka tambahin jumlahnya
                if ($code == $this->code) {
                    array_push($tickets_code, $ticket->code);
                }
            }
        }
        $tickets_code = array_unique($tickets_code);
        return $tickets_code;
    }

    public function getUsedQuotaAttribute()
    {
        $count = 0;
        return $count;
    }
}
