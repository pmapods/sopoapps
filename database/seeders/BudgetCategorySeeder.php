<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BudgetPricingCategory;

class BudgetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category_list = ["Office Equipment","Fixture and Furniture","Warehouse Equipment","Table Computer", "Others", "Jasa"];
        $category_code = ["OE","FF","WE","TC","OT","JS"];
        foreach($category_list as $key=>$list){
            $newCategory = new BudgetPricingCategory;
            $newCategory->name = $list;
            $newCategory->code = $category_code[$key];
            $newCategory->save();
        }
    }
}
