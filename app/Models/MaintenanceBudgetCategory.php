<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MaintenanceBudgetCategory extends Model
{
    use SoftDeletes;
    protected $table = 'maintenance_budget_category';
    protected $primaryKey = 'id';

    public function maintenance_budget(){
        return $this->hasMany(MaintenanceBudget::class);
    }
}
