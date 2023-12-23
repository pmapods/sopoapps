<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailReminder extends Model
{
    protected $table = 'email_reminder';
    protected $primaryKey = 'id';
    protected $appends = ['salespoint_name'];

    public function detail()
    {
        return $this->hasMany(EmailReminderDetail::class, 'email_reminder_id', 'id');
    }

    public function salespoint()
    {
        return $this->belongsTo(SalesPoint::class);
    }

    public function getSalespointNameAttribute()
    {
        if (is_numeric($this->salespoint_id)) {
            return $this->salespoint->name;
        } else {
            return ucwords($this->salespoint_id);
        }
    }

    public function type()
    {
        switch ($this->type) {
            case 'po_armada_niaga':
                return 'PO Armada Niaga';
                break;
            case 'po_armada_non_niaga':
                return 'PO Armada Non Niaga';
                break;
            case 'po_security':
                return 'PO Security';
                break;
            case 'po_cit':
                return 'PO CIT';
                break;
            case 'po_pest_control':
                return 'PO Pest Control';
                break;
            case 'po_merchandiser':
                return 'PO Merchandiser';
                break;
            case 'asset_number':
                return 'Nomor Asset';
                break;
            case 'vendor_evaluation':
                return 'Evaluasi Vendor';
                break;
            case 'po_manual':
                return 'PO Manual File Attachment';
                break;
            default:
                return 'type_undefined';
                break;
        }
    }
}
