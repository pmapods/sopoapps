<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailReminderDetail extends Model
{
    protected $table = 'email_reminder_detail';
    protected $primaryKey = 'id';

    public function email_reminder(){
        return $this->belongsTo(EmailReminder::class);
    }

    public function isMaxDays(){
        if(max($this->email_reminder->detail->pluck('days')->toArray()) == $this->days){
            return true;
        }else{
            return false;
        }
    }
}
