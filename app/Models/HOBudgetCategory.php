<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HOBudgetCategory extends Model
{
    use SoftDeletes;
    protected $table = 'ho_budget_category';
    protected $primaryKey = 'id';
}
