<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\PoMonitoring;
use App\Models\EmailAdditional;
use DB;

class Po extends Model
{
    use SoftDeletes;
    protected $table = 'po';
    protected $primaryKey = 'id';

    public function po_item()
    {
        return $this->hasMany(PoItem::class);  
    }

    public function po_authorization()
    {
        return $this->hasMany(POAuthorization::class);
    }

    public function cancel_authorization()
    {
        return $this->hasMany(CancelAuthorization::class);
    }

    public function created_by_employee()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id')->withTrashed();
    }

    public function salespoint()
    {
        return $this->belongsTo(SalesPoint::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function request_type()
    {
        switch ($this->request_type) {
            case '1':
                return 'PO Sewa';
                break;
            case '2':
                return 'PO Jual';
                break;
            case '3':
                return 'PO Custom';
                break;

            default:
                return 'request_type_undefined';
                break;
        }
    }

    public function status($type = "default")
    {
        switch ($this->status) {
            case '0':
                if ($this->reject_reason != null) {
                    return 'di Reject oleh ' . $this->reject_by_employee->name . ', Alasan: ' . $this->reject_reason;
                } else {
                    return 'Draft PO';
                }
                break;

            case '1':
                $current_authorization = $this->current_authorization();
                return 'Menunggu Otorisasi PO Oleh ' . $current_authorization->employee_name;
                break;

            case '2':
                return 'Otorisasi PO Selesai / Menunggu Proses Delivery Order';
                break;

            case '3':
                return 'Proses Delivery Order Selesai / Menunggu Proses Shipment (Pengiriman)';
                break;

            case '4':
                if ($this->is_dp == 0) {
                    if ($this->reschedule_at != null) {
                        return 'Proses Proses Shipment (Pengiriman) Ulang Sedang Berlangsung';
                    } else {
                        return 'Proses Proses Shipment (Pengiriman) Sedang Berlangsung';
                    }
                }
                elseif ($this->is_dp == 1) {
                    if ($this->reschedule_at != null) {
                        return 'Proses Proses Shipment (Pengiriman) Ulang Sedang Berlangsung / Menunggu Proses Collection (Pelunasan)';
                    } else {
                        return 'Proses Proses Shipment (Pengiriman) Sedang Berlangsung / Menunggu Proses Collection (Pelunasan)';
                    }
                }
                break;

            case '5':
                if ($this->is_dp == 0) {
                    return 'Proses Shipment (Pengiriman) Selesai';
                }
                elseif ($this->is_dp == 1) {
                    return 'Proses Shipment (Pengiriman) & Collection (Pelunasan) Selesai';
                }
                break;

            case '-1':
                $string = 'Tidak Terkirim';
                if (isset($this->undelivery_reason)) {
                    $string .= "\n" . 'Alasan : ' . $this->undelivery_reason;
                }
                if (isset($this->undelivery_at)) {
                    $string .= "\n" . 'Tanggal : ' . $this->undelivery_at;
                }
                return $string;
                break;

            case '-2':
                $string = 'Batal';
                if (isset($this->cancel_end_reason)) {
                    $string .= "\n" . 'Alasan : ' . $this->cancel_end_reason;
                }
                if (isset($this->cancel_end_by_employee)) {
                    $string .= "\n" . 'Dibatalkan oleh : ' . $this->cancel_end_by_employee->name;
                }
                if (isset($this->cancel_end_at)) {
                    $string .= "\n" . 'Tanggal : ' . $this->cancel_end_at;
                }
                return $string;
                break;

            default:
                return 'status_undefined';
                break;
        }
    }

    public function current_authorization()
    {
        $queue = $this->po_authorization->where('status', 0)->sortBy('level');
        $current = $queue->first();
        if ($this->status != 1) {
            return null;
        } else {
            return $current;
        }
    }

    public function reject_by_employee()
    {
        return $this->belongsTo(Employee::class, 'reject_by', 'id')->withTrashed();
    }

    public function cancel_end_by_employee()
    {
        return $this->belongsTo(Employee::class, 'cancel_end_by', 'id')->withTrashed();
    }

    public function monitoring_log()
    {
        return PoMonitoring::where('po_id', $this->id)->get();
    }

    public function email_template($data)
    {
        $items_name = collect($data['po_item'])->pluck('name');
        $items_name = implode(",", $items_name->toArray());
        $texts = "";
        $texts .= "Dear Bapak/Ibu" . "\n";
        $texts .= "Terlampir adalah PO dengan informasi berikut" . "\n";
        $texts .= "Nomor PO : " . $data['po_number'] . "\n";
        $texts .= "List Item : " . $items_name . "\n";
        $texts .= "Mohon bantuannya untuk memastikan kesesuian nya, apabila sudah Clear Mohon diapproval" . "\n";
        $texts .= "Regards " . "\n";
        $texts .= "Staff" . "\n";
        return $texts;
    }
}
