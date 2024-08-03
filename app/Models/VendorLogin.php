<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\SalesPoint;

class VendorLogin extends Authenticatable
{
    use SoftDeletes;
    protected $table = 'vendor_login';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    // Attributes yang dapat diisi
    protected $fillable = [
        'code', 'email', 'username', 'password', 'name', 'type', 'vendor_code_ref'
    ];

    protected $hidden = [
        'password',
    ];

    // public function menu_access(){
    //     return $this->hasOne(EmployeeMenuAccess::class);
    // }

    public function statusName(){
        switch ($this->status){
            case 0:
                return 'Aktif';
                break;
            case 1:
                return 'Non Aktif';
                break;
            default:
                return 'status_name_undefined';
                break;
        }
    }
}