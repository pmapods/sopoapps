<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetBrand extends Model
{
    protected $table = 'budget_brand';
    protected $primaryKey = 'id';

    public function budget_pricing(){
        $this->belongsTo(BudgetPricing::class);
    }
}
