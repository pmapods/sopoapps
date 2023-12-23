<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\SalesPoint;
use App\Models\VendorEvaluationDetail;
use App\Models\VendorEvaluationAuthorization;

class VendorEvaluation extends Model
{
    use HasFactory;

    public function created_by_employee()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id')->withTrashed();
    }

    public function salespoint()
    {
        return $this->belongsTo(SalesPoint::class);
    }

    public function vendor_evaluation_detail()
    {
        return $this->hasOne(VendorEvaluationDetail::class);
    }

    public function vendor_evaluation_authorizations()
    {
        return $this->hasMany(VendorEvaluationAuthorization::class);
    }

    public function vendor()
    {
        switch ($this->vendor) {
            case '0':
                return 'Pest Control';
                break;
            case '1':
                return 'CIT';
                break;
            case '2':
                return 'Si Cepat';
                break;
            case '3':
                return 'Ekspedisi';
                break;
            default:
                return 'vendor_undefined';
                break;
        }
    }

    public function status()
    {
        switch ($this->status) {
            case '1':
                return 'Belum melakukan evaluasi';
                break;
            case '2':
                $current_authorization = $this->current_authorization();
                return 'Menunggu approval oleh ' . $current_authorization->employee_name;
                break;
            case '3':
                return 'Selesai';
                break;
            case '0':
                $string = 'Reject';
                if (isset($this->reason)) {
                    $string .= "\n" . 'Alasan : ' . $this->reason;
                }
                if (isset($this->rejected_by)) {
                    $string .= "\n" . 'Direject oleh : ' . $this->rejected_by_employee->name;
                }
                return $string;
                break;
            case '4':
                return 'Batal';
                break;

            default:
                return 'status_undefined';
                break;
        }
    }

    public function revised_by_employee()
    {
        if ($this->revised_by != null) {
            return Employee::find($this->revised_by);
        } else {
            return null;
        }
    }

    public function rejected_by_employee()
    {
        return $this->belongsTo(Employee::class, 'rejected_by', 'id')->withTrashed();
    }

    public function current_authorization()
    {
        $queue = $this->vendor_evaluation_authorizations->where('status', '=', 0)->sortBy('level');
        $current = $queue->first();
        if ($this->status != 2) {
            return null;
        } else {
            return $current;
        }
    }

    public function last_authorization()
    {
        $queue = $this->vendor_evaluation_authorizations->where('status', '=', 1)->sortByDesc('level');
        $last = $queue->first();
        return $last;
    }

    public function authorization_emails()
    {
        $vendor_evaluation_author_ids = $this->vendor_evaluation_authorizations->pluck('employee_id');
        $author_emails = Employee::whereIn('id', $vendor_evaluation_author_ids)->pluck('email');
        return $author_emails->toArray();
    }
}
