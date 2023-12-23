<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PrAuthorization extends Model
{
    use SoftDeletes;
    protected $table = 'pr_authorization';
    protected $primaryKey = 'id';

    public function pr()
    {
        return $this->belongsTo(Pr::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }
}
