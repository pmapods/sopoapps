<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class Pr extends Model
{
    use SoftDeletes;
    protected $table = 'pr';
    protected $primaryKey = 'id'; 

    public function ticket(){
        return $this->belongsTo(Ticket::class);
    }
    
    public function armada_ticket(){
        return $this->belongsTo(ArmadaTicket::class);
    }
    
    public function security_ticket(){
        return $this->belongsTo(SecurityTicket::class);
    }

    public function pr_detail(){
        return $this->hasMany(PrDetail::class);
    }

    public function pr_authorizations(){
        return $this->hasMany(PrAuthorization::class);
    }

    public function authorization_emails(){
        $pr_author_ids = $this->pr_authorizations->pluck('employee_id');
        $author_emails = Employee::whereIn('id',$pr_author_ids)->pluck('email');
        return $author_emails->toArray();
    }

    public function setup_dates(){
        $array = [];
        foreach ($this->pr_detail as $detail) {
            if($detail->setup_date != null){
                array_push($array,\Carbon\Carbon::parse($detail->setup_date)->format('d-m-Y'));
            }
        }
        return $array;
    }

    public function pr_logs(){
        return $this->hasMany(PrLog::class);
    }

    public function current_authorization(){
        $queue = $this->pr_authorizations->where('status',0)->sortBy('level');
        $current = $queue->first();
        if($this->status != 0){
            // authorization done
            return null;
        }else{
            return $current;
        }
    }

    public function last_authorization(){
        $queue = $this->pr_authorizations->where('status',1)->sortByDesc('level');
        $last = $queue->first();
        return $last;
    }

    public function rejected_by_employee(){
        if($this->rejected_by != null){
            return Employee::find($this->rejected_by);
        }else{
            return null;
        }
    }

    public function revised_by_employee(){
        if($this->revised_by != null){
            return Employee::find($this->revised_by);
        }else{
            return null;
        }
    }

    public function isBudget(){
        if($this->isBudget==null){
            if($this->ticket_id != null){
                if($this->ticket->budget_type == 0){
                    return true;
                }else{
                    return false;
                }
            }
            if($this->armada_ticket_id != null){
                return true;
            }
            if($this->security_ticket_id != null){
                $isBudget = true;
                // pengadaan lembur
                if($this->security_ticket->ticketing_type == 4){
                    $isBudget = false;
                }
                return $isBudget;
            }
        }else{
            return $this->isBudget;
        }
    }

    public function getPath(){
        if($this->ticket){
            $code = $this->ticket->code;
        }
        if($this->armada_ticket){
            $code = $this->armada_ticket->code;
        }
        if($this->security_ticket){
            $code = $this->security_ticket->code;
        }
        if($code){
            $data = app('app\Http\Controllers\Operational\PRController')->printPR($code,'path');
            return $data;
        }else{
            return null;
        }
    }
}
