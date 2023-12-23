<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadReportList extends Model
{
    protected $table = 'upload_report_list';
    protected $primaryKey = 'id';
    protected $appends = ['created_at_format'];

    public function upload_report(){
        return $this->belongsTo(UploadReport::class);
    }

    public function getCreatedAtFormatAttribute(){
        return $this->created_at->translatedFormat('d F Y');
    }
}
