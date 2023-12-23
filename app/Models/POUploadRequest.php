<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class POUploadRequest extends Model
{
    use Uuids;
    protected $table = 'po_upload_request';
    protected $primaryKey = 'id';

    public function po(){
        return $this->belongsTo(Po::class);
    }
}
