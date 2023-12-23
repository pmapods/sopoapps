<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadReport extends Model
{
    protected $table = 'upload_report';
    protected $primaryKey = 'id';

    public function list(){
        return $this->hasMany(UploadReportList::class)->orderBy('created_at','desc');
    }
}
