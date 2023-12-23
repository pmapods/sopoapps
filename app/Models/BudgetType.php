<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetType extends Model
{
    protected $table = 'budget_type';
    protected $primaryKey = 'id';

    public function budget_pricing(){
        $this->belongsTo(BudgetPricing::class);
    }
}
