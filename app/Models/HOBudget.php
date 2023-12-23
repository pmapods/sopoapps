<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HOBudget extends Model
{
    use SoftDeletes;
    protected $table = 'ho_budget';
    protected $primaryKey = 'id';
    protected $appends = ['category_name'];

    public function ho_budget_category(){
        return $this->belongsTo(HOBudgetCategory::class);
    }

    public function getCategoryNameAttribute(){
        return $this->ho_budget_category->name;
    }
}
