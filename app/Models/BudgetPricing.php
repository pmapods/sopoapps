<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BudgetPricing extends Model
{
    use SoftDeletes;
    protected $table = 'budget_pricing';
    protected $primaryKey = 'id';

    public function budget_pricing_category(){
        return $this->belongsTo(BudgetPricingCategory::class);
    }

    public function budget_brand(){
        return $this->hasMany(BudgetBrand::class);
    }
    
    public function budget_type(){
        return $this->hasMany(BudgetType::class);
    }

    public function brand_list_text(){
        if(count($this->budget_brand) > 0){
            $array = $this->budget_brand->pluck('name')->toArray();
            return implode(" / ", $array);
        }
        return '';
    }
    public function type_list_text(){
        if(count($this->budget_type) > 0){
            $array = $this->budget_type->pluck('name')->toArray();
            return implode(" / ", $array);
        }
        return '';
    }
}
