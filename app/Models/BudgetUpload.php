<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BudgetUpload extends Model
{
    use SoftDeletes;
    protected $table = 'budget_upload';
    protected $primaryKey = 'id';
    protected $appends = ['employee_name','salespoint_name'];

    public function authorizations(){
        return $this->hasMany(BudgetUploadAuthorization::class);
    }

    public function budget_detail(){
        switch ($this->type) {
            case 'inventory':
                return $this->hasMany(InventoryBudget::class);
                break;

            case 'armada':
                return $this->hasMany(ArmadaBudget::class);
                break;

            case 'assumption':
                return $this->hasMany(AssumptionBudget::class);
                break;

            case 'ho':
                return $this->hasMany(HOBudgetUpload::class);
                break;
            default:
                break;
        }
    }

    public function historybudget(){
        $historybudget = $this->budget_detail()->onlyTrashed()->get()->sortByDesc('deleted_at');
        if($historybudget->count() < 0){
            $historybudget = collect($historybudget->toArray())->groupBy('deleted_at');
            return $historybudget;
        }
    }

    public function salespoint(){
        return $this->belongsTo(SalesPoint::class);
    }

    public function authorization_emails(){
        $budget_author_ids = $this->authorizations->pluck('employee_id');
        $author_emails = Employee::whereIn('id',$budget_author_ids)->pluck('email');
        return $author_emails->toArray();
    }

    public function created_by_employee(){
        return $this->belongsTo(Employee::class,'created_by','id');
    }

    public function rejected_by_employee(){
        return $this->belongsTo(Employee::class,'rejected_by','id');
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

    public function status(){
        // dd($this->status);
        switch ($this->status) {
            case '0':
                return 'Menunggu Otorisasi '.$this->current_authorization()->employee_name.' (Belum Aktif)';
                break;

            case '1':
                return 'Aktif';
                break;

            case '2':
                return 'Expired';
                break;

            case '-1':
                return 'Ditolak Oleh '.$this->rejected_by_employee->name.' ( alasan : '.$this->reject_notes.')';
                break;

            default:
                return 'item_type_undefined';
                break;
        }
    }

    // attribute
    public function getEmployeeNameAttribute(){
        return $this->created_by_employee->name;
    }
    public function getSalespointNameAttribute(){
        return $this->salespoint->name;
    }
}
