<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AssumptionBudget extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'assumption_budget';
    protected $appends = ['pending_quota','used_quota'];

    public function budget_upload(){
        return $this->belongsTo(BudgetUpload::class);
    }

    public function getPendingQuotaAttribute(){
        $count = 0;
        $budget_upload_id = $this->budget_upload_id;
        $tickets = Ticket::where('budget_upload_id', $budget_upload_id)->whereNotIn('status',[-1,0])->get();
        foreach($tickets as $ticket){
            foreach($ticket->ticket_item->where('isCancelled',false)->whereNotNull('maintenance_budget_id') as $ticket_item){
                $code = $ticket_item->maintenance_budget->code;
                // jika kode sesuai dengan budget maka tambahin jumlahnya
                if($code == $this->code){
                    $count  += $ticket_item->count;
                } 
            }
        }
        // jika security maka cek pengadaan security yang udah di create dan masih aktif juga belom dibatalkan
        if($this->code == "SCRT"){
            $securityticket = SecurityTicket::where('budget_upload_id',$budget_upload_id)
                                            ->where('status','!=',-1)
                                            ->first();
            if($securityticket){
                $count += $securityticket->personil_count;
            }
        }
        
        // jika budget maka cek pengadaan CIT (additional) yang udah di create dan masih aktif juga belom dibatalkan
        if($this->code == "CIT"){
            $citticket = Ticket::where('budget_upload_id',$budget_upload_id)
                                    ->where('status','!=',-1)
                                    ->first();
            if($citticket){
                $ticket_item = $citticket->ticket_item->first();
                if($ticket_item){
                    if(trim($ticket_item->name) == "CIT"){
                        $count += $ticket_item->count;
                    }
                }
            }
        }
        return $count-$this->getUsedQuotaAttribute();
    }

    public function getTicketsCode(){
        $tickets_code = [];
        $budget_upload_id = $this->budget_upload_id;
        $tickets = Ticket::where('budget_upload_id', $budget_upload_id)->where('status',"!=",-1)->get();
        foreach($tickets as $ticket){
            foreach($ticket->ticket_item->where('isCancelled',false)->whereNotNull('maintenance_budget_id') as $ticket_item){
                $code = $ticket_item->maintenance_budget->code;
                // jika kode sesuai dengan budget maka tambahin jumlahnya
                if($code == $this->code){
                    array_push($tickets_code,$ticket->code);
                } 
            }
        }
        // jika security maka cek pengadaan security yang udah di create dan masih aktif juga belom dibatalkan
        if($this->code == "SCRT"){
            $securityticket = SecurityTicket::where('budget_upload_id',$budget_upload_id)
                                            ->where('status','!=',-1)
                                            ->first();
            if($securityticket){
                array_push($tickets_code,$securityticket->code);
            }
        }
        
        // jika budget maka cek pengadaan CIT (additional) yang udah di create dan masih aktif juga belom dibatalkan
        if($this->code == "CIT"){
            $citticket = Ticket::where('budget_upload_id',$budget_upload_id)
                                    ->where('status','!=',-1)
                                    ->first();
            $ticket_item = $citticket->ticket_item->first();
            if($ticket_item){
                if(trim($ticket_item->name) == "CIT"){
                    array_push($tickets_code,$citticket->code);
                }
            }
        }
        $tickets_code = array_unique($tickets_code);
        return $tickets_code;
    }

    public function getUsedQuotaAttribute(){
        $count = 0;
        return $count;
    }
}
