<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class VendorEvaluationAuthorization extends Model
{
    use SoftDeletes;
    protected $table = 'vendor_evaluation_authorization';
    protected $primaryKey = 'id';

    public function vendorEvaluation()
    {
        return $this->belongsTo(VendorEvaluation::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function status()
    {
        switch ($this->status) {
            case '0':
                return 'Pending';
                break;
            case '1':
                return 'Approved';
                break;
            case '2':
                return 'Terminated';
                break;

            default:
                return 'undefined_authorization_status';
                break;
        }
    }
}
