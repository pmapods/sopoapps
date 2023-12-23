<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetPricingCategory extends Model
{
    use SoftDeletes;
    protected $table = 'budget_pricing_category';
    protected $primaryKey = 'id';

    public function budget_pricing(){
        return $this->hasMany(BudgetPricing::class);
    }
    public function budget_pricing_with_trashed(){
        return $this->hasMany(BudgetPricing::class)->withTrashed();
    }
}
