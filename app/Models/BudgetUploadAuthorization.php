<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BudgetUploadAuthorization extends Model
{
    use SoftDeletes;
    protected $table = 'budget_upload_authorization';
    protected $primaryKey = 'id';

    public function budget_upload(){
        return $this->belongsTo(BudgetUpload::class);
    }
    
    public function employee(){
        return $this->belongsTo(Employee::class)->withTrashed();
    }
}
