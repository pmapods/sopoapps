<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MaintenanceBudget extends Model
{
    use SoftDeletes;
    protected $table = 'maintenance_budget';
    protected $primaryKey = 'id';
    protected $appends = ['category_name'];
    public function category(){
        return $this->belongsTo(MaintenanceBudgetCategory::class);
    }

    public function getCategoryNameAttribute(){
        return MaintenanceBudgetCategory::find($this->maintenance_budget_category_id)->name;
    }
}
