<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CustomerType extends Model
{
    protected $table = 'customer_type';
    protected $hidden = [
        'code'
    ];

    public function customer()
    {
        return $this->hasMany(Customer::class);
    }
}
