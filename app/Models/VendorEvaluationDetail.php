<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VendorEvaluation;

class VendorEvaluationDetail extends Model
{
    use HasFactory;
    protected $table = 'vendor_evaluation_detail';
    protected $primaryKey = 'id';

    public function vendor_evaluation()
    {
        return $this->belongsTo(VendorEvaluation::class);
    }
}
