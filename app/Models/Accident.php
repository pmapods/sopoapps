<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accident extends Model
{
    protected $table = 'accident';
    protected $primaryKey = 'id';

    public function salespoint(){
        return $this->belongsTo(SalesPoint::class);
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }
    
    public function armada(){
        return $this->belongsTo(Armada::class);
    }

    public function type(){
        switch ($this->type){
            case 'armada':
                return 'Armada';
                break;
            case 'security':
                return 'Security';
                break;
            case 'cit':
                return 'CIT';
                break;
            case 'pest_control':
                return 'Pest Control';
                break;
            case 'merchandiser':
                return 'Merchandiser';
                break;
        }
    }
}
