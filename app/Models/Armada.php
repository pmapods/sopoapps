<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\SalesPoint;

class Armada extends Model
{
    use SoftDeletes;
    protected $table = 'armada';
    protected $primaryKey = 'id';

    public function salespoint(){
        if($this->salespoint_id != null){
            return SalesPoint::find($this->salespoint_id);
        }else{
            return null;
        }
    }

    public function armada_type(){
        return $this->belongsTo(ArmadaType::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
