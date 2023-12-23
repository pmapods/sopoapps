<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FRIForm extends Model
{
    use SoftDeletes;
    protected $table = 'fri_form';
    protected $primaryKey = 'id';

    public function salespoint(){
        return $this->belongsTo(SalesPoint::class);
    }

    public function ticket(){
        return $this->belongsTo(Ticket::class);
    }

    public function authorizations(){
        return $this->hasMany(FRIFormAuthorization::class,'fri_form_id','id');
    }

    public function current_authorization(){
        $queue = $this->authorizations->where('status',0)->sortBy('level');
        $current = $queue->first();
        if($this->status != 0){
            // authorization done
            return null;
        }else{
            return $current;
        }
    }

    public function getPath(){
        $code = $this->armada_ticket->code;
        if($code){
            $data = app('app\Http\Controllers\Operational\TicketingController')->printFRIForm($code,'path');
            return $data;
        }else{
            return null;
        }
    }

    public function ticket_vendor(){
        return $this->belongsTo(Ticketvendor::class);
    }
}
