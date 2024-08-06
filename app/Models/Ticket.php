<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\TicketMonitoring;
use App\Models\EmailAdditional;
use App\Models\TicketItemAttachment;
use App\Models\TicketItemFileRequirement;
use DB;

class Ticket extends Model
{
    use SoftDeletes;
    protected $table = 'ticket';
    protected $primaryKey = 'id';
    protected $appends = ['budget_upload'];

    public function ticket_item()
    {
        return $this->hasMany(TicketItem::class);
    }

    public function ticket_vendor()
    {
        return $this->hasMany(TicketVendor::class);
    }

    public function bidding()
    {
        return $this->hasMany(Bidding::class);
    }

    public function custom_bidding()
    {
        return $this->hasMany(CustomBidding::class);
    }

    public function ticket_authorization()
    {
        return $this->hasMany(TicketAuthorization::class);
    }

    public function cancel_authorization()
    {
        return $this->hasMany(CancelAuthorization::class);
    }

    public function ticket_additional_attachment()
    {
        return $this->hasMany(TicketAdditionalAttachment::class);
    }

    public function created_by_employee()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id')->withTrashed();
    }

    public function salespoint()
    {
        return $this->belongsTo(SalesPoint::class);
    }

    public function pr()
    {
        return $this->hasOne(Pr::class);
    }

    public function po()
    {
        return $this->hasMany(Po::class);
    }

    public function item_type()
    {
        switch ($this->item_type) {
            case '0':
                return 'Barang';
                break;
            case '1':
                return 'Jasa';
                break;
            case '2':
                return 'Maintenance';
                break;
            case '3':
                return 'HO';
                break;
            case '4':
                return 'Disposal';
                break;
            default:
                return 'item_type_undefined';
                break;
        }
    }

    public function request_type()
    {
        switch ($this->request_type) {
            case '0':
                return 'Pengadaan';
                break;
            case '1':
                return 'Replace Existing';
                break;
            case '2':
                return 'Repeat Order';
                break;
            case '3':
                return 'Perpanjangan';
                break;
            case '4':
                return 'End Kontrak';
                break;
            case '5':
                return 'Percepatan Replace';
                break;
            case '6':
                return 'Percepatan End Kontrak';
                break;

            default:
                return 'request_type_undefined';
                break;
        }
    }

    public function budget_type()
    {
        switch ($this->budget_type) {
            case '0':
                return 'Budget';
                break;
            case '1':
                return 'Non Budget';
                break;
            default:
                return 'budget_type_undefined';
                break;
        }
    }

    public function status($type = "default")
    {
        switch ($this->status) {
            case '0':
                if ($this->termination_reason != null) {
                    return 'di Reject oleh ' . $this->terminated_by_employee->name . ', Alasan: ' . $this->termination_reason;
                } else {
                    return 'Draft Pengaadaan';
                }
                break;

            case '1':
                if ($this->is_cancel_end == 1) {
                    $current_cancel_authorization = $this->current_cancel_authorization();
                    return 'Menunggu Otorisasi Pengadaan Oleh ' . $current_cancel_authorization->employee_name;
                }
                else {
                    $current_authorization = $this->current_authorization();
                    return 'Menunggu Otorisasi Pengadaan Oleh ' . $current_authorization->employee_name;
                }
                break;

            case '2':
                return 'Otorisasi Pengadaan Selesai / Menunggu Proses Bidding';
                break;

            case '3':
                return 'Proses Bidding Selesai / Menunggu Proses PR';
                break;

            case '4':
                $current_authorization = $this->pr->current_authorization();
                return 'PR sudah dibuat, Menunggu otorisasi oleh ' . $current_authorization->employee_name;
                break;

            case '5':
                return 'Menunggu update kelengkapan Nomor Asset';
                break;

            case '6':
                $invoice = $this->invoice_filepath_ticket_item();
                $lpb = $this->lpb_filepath_ticket_item();

                if ($type == "complete") {
                    if ($invoice) {
                        return 'Pengadaan Selesai (Vendor belum mengirimkan LPB)';
                    } elseif ($lpb) {
                        return 'Pengadaan Selesai (Vendor belum mengirimkan Invoice)';
                    } elseif ($this->bastk_cop_filepath) {
                        return 'Pengadaan Selesai (Vendor belum mengirimkan LPB)';
                    }
                }

                if ($type == "complete" && $this->item_type == 4) {
                    $string_text = 'Menunggu upload berkas penerimaan';
                    return $string_text;
                } elseif ($type == "complete") {
                    $string_text = 'Menunggu Proses PR SAP - oleh GA ';
                    if (!$this->has_pr_sap()) {
                        $string_text .= '(undone)';
                    } else {
                        $string_text .= '(done)';
                    }
                    $string_text .= "\n";
                    if (count($this->po) == 0) {
                        $string_text .= "Menunggu Proses PO - oleh Purchasing (undone) ";
                        if ($this->revise_po == 1) {
                            $string_text .= "\r\n" . 'Po di Revisi oleh ' . $this->revise_by_employee->name . ', Alasan: ' . $this->reason_revise;
                        }
                    } 
                    else {
                        $flag_alldone = true;
                        foreach ($this->po as $po) {
                            if ($po->status != 3) {
                                $flag_alldone = false;
                            }
                        }
                        if ($flag_alldone) {
                            if ($this->salespoint->id == 251 || $this->salespoint->id == 252) {
                                return 'Menunggu Penerimaan barang di HO';
                            } else {
                                return 'Menunggu Penerimaan barang di Area';
                            }
                        } else {
                            $string_text .= "Menunggu Proses PO - oleh Purchasing";
                            $flag = false;
                            foreach ($this->po as $po) {
                                if (in_array($po->status, [1, 2])) {
                                    $flag = true;
                                    break;
                                }
                            }
                            if ($flag == true) {
                                $string_text .= ' (done)';
                                $string_text .= "\n";
                                if ($po->po_upload_request->isOpened == 0) {
                                    $string_text .= 'Upload File Vendor - oleh Vendor (undone)';
                                } else {
                                    $string_text .= 'Upload File Vendor - oleh Vendor (done)';
                                }
                            } else {
                                $string_text .= ' (undone)';
                            }
                        }
                    }
                    return $string_text;
                }

                if ($type == "default") {
                    if (count($this->po) == 0) {
                        return 'Menunggu Setup PO';
                    } else {
                        foreach ($this->po as $po) {
                            if ($po->status != 3) {
                                return 'Menunggu proses PO & Penerimaan Barang';
                            }
                        }
                        return 'Menunggu Penerimaan barang di Area';
                    }
                }
                break;

            case '7':
                $string_text = 'Pengadaan Selesai';
                if ($this->item_type == 4) {
                    $string_text = 'Pengadaan Disposal Inventaris Selesai';
                } 
                elseif (count($this->po) == 0 && $this->revise_po == 1) {
                    $string_text .= "\n";
                    $string_text .= 'Po di Revisi oleh ' . $this->revise_by_employee->name . ', Alasan: ' . $this->reason_revise;
                }

                return $string_text;

                break;
            case '8':
                return 'Sedang di Lelang';
                break;
            case '-1':
                $string = 'Batal';
                if (isset($this->termination_reason)) {
                    $string .= "\n" . 'Alasan : ' . $this->termination_reason;
                }
                if (isset($this->terminated_by_employee)) {
                    $string .= "\n" . 'Dibatalkan oleh : ' . $this->terminated_by_employee->name;
                }
                return $string;
                break;

            case '-2':
                $string = 'Cancel End Kontrak';
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

    public function invoice_filepath_ticket_item()
    {
        $invoice_ticket_item = $this->ticket_item->whereNotNull('invoice_filepath')->first();
        return $invoice_ticket_item;
    }

    public function lpb_filepath_ticket_item()
    {
        $lpb_ticket_item = $this->ticket_item->whereNotNull('lpb_filepath')->first();
        return $lpb_ticket_item;
    }

    public function current_authorization()
    {
        $queue = $this->ticket_authorization->where('status', 0)->sortBy('level');
        $current = $queue->first();
        if ($this->status != 1) {
            return null;
        } else {
            return $current;
        }
    }

    public function current_cancel_authorization()
    {
        $queue = $this->cancel_authorization->where('status', 0)->sortBy('level');
        $current = $queue->first();
        if ($this->status != 1) {
            return null;
        } else {
            return $current;
        }
    }

    public function last_authorization()
    {
        $queue = $this->ticket_authorization->where('status', 1)->sortByDesc('level');
        if ($last) {
            $last = $queue->first();
        }
        if ($this->status != 1) {
            return null;
        } else {
            return $last;
        }
    }

    public function terminated_by_employee()
    {
        return $this->belongsTo(Employee::class, 'terminated_by', 'id')->withTrashed();
    }

    public function cancel_end_by_employee()
    {
        return $this->belongsTo(Employee::class, 'cancel_end_by', 'id')->withTrashed();
    }

    public function revise_by_employee()
    {
        return $this->belongsTo(Employee::class, 'revise_by', 'id')->withTrashed();
    }

    public function ticket_items_with_attachments()
    {
        $data = array();
        foreach ($this->ticket_item as $item) {
            if ($item->budget_pricing_id != null) {
                $item->budget_pricing;
            }
            if ($item->ho_budget_id != null) {
                $item->ho_budget;
            }
            if ($item->maintenance_budget_id != null) {
                $item->maintenance_budget;
            }
            $item->attachments = $item->ticket_item_attachment;
            $item->files = $item->ticket_item_file_requirement;
            array_push($data, $item);
        }
        return $data;
    }

    public function ticket_vendors_with_additional_data()
    {
        $data = array();
        foreach ($this->ticket_vendor as $ticket_vendor) {
            if (isset($ticket_vendor->vendor()["code"])) {
                $ticket_vendor->code = $ticket_vendor->vendor()["code"];
                $ticket_vendor->vendor_id = $ticket_vendor->vendor()["id"];
            } else {
                $ticket_vendor->code = null;
                $ticket_vendor->vendor_id = null;
            }
            array_push($data, $ticket_vendor);
        }
        return $data;
    }

    public function ba_rejected_by_employee()
    {
        if ($this->ba_rejected_by != null) {
            return Employee::find($this->ba_rejected_by);
        } else {
            return null;
        }
    }
    public function ba_revised_by_employee()
    {
        if ($this->ba_rejected_by != null) {
            return Employee::find($this->ba_revised_by);
        } else {
            return null;
        }
    }
    public function ba_confirmed_by_employee()
    {
        if ($this->ba_confirmed_by != null) {
            return Employee::find($this->ba_confirmed_by);
        } else {
            return null;
        }
    }

    public function monitoring_log()
    {
        return TicketMonitoring::where('ticket_id', $this->id)->get();
    }

    public function additional_emails()
    {
        switch ($this->request_type) {
            case '0':
                $type = 'pengadaan';
                break;
            case '1':
                $type = 'replace_existing';
                break;
            case '2':
                $type = 'repeat_order';
                break;
            default:
                return [];
                break;
        }
        $additional_email = EmailAdditional::where('category', 'barang_jasa')->where('type', $type)->first();
        $emails = json_decode($additional_email->emails);
        return $emails;
    }

    public function purchasing_emails()
    {
        $email_by_region = EmailAdditional::where('category', 'purchasing')->where('type', $this->salespoint->region_type)->first()->emails;
        $email_national = EmailAdditional::where('category', 'purchasing')->where('type', 'national')->first()->emails;
        $emails = array_merge(json_decode($email_by_region), json_decode($email_national));
        return $emails;
    }

    public function ga_emails()
    {
        $email_by_transaction_type = EmailAdditional::where('category', 'ga')->where('type', 'barang_jasa')->first();
        if ($email_by_transaction_type) {
            $emails = json_decode($email_by_transaction_type->emails);
            return $emails;
        } else {
            return [];
        }
    }

    public function isPOFinished()
    {
    }

    public function email_template($data)
    {
        $items_name = collect($data['ticket_items'])->pluck('name');
        $items_name = implode(",", $items_name->toArray());
        $texts = "";
        $texts .= "Dear Bapak/Ibu" . "\n";
        $texts .= "Terlampir adalah PO dengan informasi berikut" . "\n";
        $texts .= "Nomor PO : " . $data['po_number'] . "\n";
        $texts .= "List Item : " . $items_name . "\n";
        $texts .= "Mohon bantuannya untuk memastikan kesesuian nya, apabila sudah Clear Mohon diapproval dan dikirimkan kembali kepada kami" . "\n";
        $texts .= "Regards " . "\n";
        $texts .= "Purchasing Staff" . "\n";
        return $texts;
    }

    public function po_reference()
    {
        $po = Po::where('no_po_sap', $this->po_reference_number)->first();
        $po_manual = PoManual::where('po_number', $this->po_reference_number)->first();
        if ($po != null) {
            return $this->belongsTo(Po::class, 'po_reference_number', 'no_po_sap');
        }
        if ($po_manual != null) {
            return $this->belongsTo(PoManual::class, 'po_reference_number', 'po_number');
        }
    }

    public function getBudgetUploadAttribute()
    {
        $budget_upload = BudgetUpload::find($this->budget_upload_id);
        return $budget_upload;
    }

    public function all_attachments()
    {
        $list = [];

        // ba vendor (optional)
        if ($this->ba_vendor_filepath) {
            $newAtt = new \stdClass();
            $newAtt->name = $this->ba_vendor_filename;
            $newAtt->url = config('app.url') . "/storage/" . $this->ba_vendor_filename;
            array_push($list, $newAtt);
        }
        $ticket_item_ids = $this->ticket_item->pluck('id');

        // ticket item attachment
        $ticketItemAttachments = TicketItemAttachment::whereIn('ticket_item_id', $ticket_item_ids)->get();
        foreach ($ticketItemAttachments as $attachment) {
            $newAtt = new \stdClass();
            $newAtt->name = $attachment->name;
            $newAtt->url = config('app.url') . "/storage/" . $attachment->path;
            array_push($list, $newAtt);
        }

        // ticket item attachment
        $ticketItemFileRequirements = TicketItemFileRequirement::whereIn('ticket_item_id', $ticket_item_ids)->get();
        foreach ($ticketItemFileRequirements as $attachment) {
            $newAtt = new \stdClass();
            $newAtt->name = $attachment->name;
            $newAtt->url = config('app.url') . "/storage/" . $attachment->path;
            array_push($list, $newAtt);
        }

        // bidding
        foreach ($this->bidding as $bidding) {
            $newAtt = new \stdClass();
            $newAtt->name = "Bidding " . $bidding->product_name;
            $newAtt->url = config('app.url') . "/bidding/printview/" . \Crypt::encryptString($bidding->id);
            array_push($list, $newAtt);
        }

        // bidding - kalo kertas kerjanya yang manual
        foreach ($this->custom_bidding as $custom_bidding) {
            $newAtt = new \stdClass();
            $newAtt->name = "Bidding " . $custom_bidding->ticket_item->name ?? "";
            $newAtt->url = config('app.url') . "/storage/" . $custom_bidding->filepath;
            array_push($list, $newAtt);
        }

        // bidding - penawaran resmi 2 vendor
        foreach ($this->bidding as $bidding) {
            if ($bidding->signed_filepath) {
                $newAtt = new \stdClass();
                $newAtt->name = $bidding->signed_filename;
                $newAtt->url = config('app.url') . "/storage/" . $bidding->signed_filepath;
            }
            array_push($list, $newAtt);
        }

        // PR
        if ($this->pr) {
            $newAtt = new \stdClass();
            $newAtt->name = 'PR ' . $this->code;
            $newAtt->url = config('app.url') . "/printPR/" . $this->code;
            array_push($list, $newAtt);
        }

        // FORM FRI
        foreach ($this->fri_forms as $fri_form) {
            $newAtt = new \stdClass();
            $newAtt->name = 'FRI ' . $this->code;
            $newAtt->url = config('app.url') . "/printFRI/" . $this->code;
            array_push($list, $newAtt);
        }

        return collect($list);
    }

    public function has_pr_sap()
    {
        $check_pr_by_ticket_code = DB::table('pr_sap')->where("data", "like", '%' . '"textnote":"' . $this->code . '"' . '%')->first();
        if ($check_pr_by_ticket_code) {
            return true;
        } else {
            return false;
        }
    }

    public function fri_forms()
    {
        return $this->hasMany(FRIForm::class);
    }

    public function po_array_list()
    {
        $pos = [];
        foreach ($this->po as $po) {
            if ($po->no_po_sap != null && trim($po->no_po_sap) != '') {
                array_push($pos, $po->no_po_sap);
            }
        }
        return $pos;
    }

    public function sender_array_list()
    {
        $pos = [];
        foreach ($this->po as $po) {
            if ($po->sender_name != null && trim($po->sender_name) != '') {
                array_push($pos, $po->sender_name);
            }
        }
        return array_unique($pos);
    }

    public function auction_status() {
        switch ($this->auction_status) {
            case 0:
                return 'Ticket Tidak di Lelang';
                break;
            case 1:
                return 'Ticket Sedang di Lelang';
                break;
            default : 
                return 'Ticket Tidak di Lelang';
                break;
        }
    }
}
