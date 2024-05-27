<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmailAdditional;
use App\Models\Po;
use App\Models\PoManual;
use DB;

class ArmadaTicket extends Model
{
    use SoftDeletes;
    protected $table = 'armada_ticket';
    protected $primaryKey = 'id';
    protected $appends = ['budget_upload'];

    public function salespoint()
    {
        return $this->belongsTo(SalesPoint::class);
    }

    public function mutation_salespoint()
    {
        return $this->belongsTo(SalesPoint::class, 'mutation_salespoint_id', 'id');
    }

    public function armada_type()
    {
        return $this->belongsTo(ArmadaType::class, 'armada_type_id', 'id');
    }

    public function armada()
    {
        return $this->belongsTo(Armada::class, 'armada_id', 'id')->withTrashed();
    }

    public function pr()
    {
        return $this->hasOne(Pr::class);
    }

    public function po()
    {
        return $this->hasMany(Po::class);
    }

    public function authorizations()
    {
        return $this->hasMany(ArmadaTicketAuthorization::class);
    }

    public function current_authorization()
    {
        $queue = $this->authorizations->where('status', 0)->sortBy('level');
        $current = $queue->first();
        if ($this->status != 1) {
            // authorization done
            return null;
        } else {
            return $current;
        }
    }

    public function rejected_by_employee()
    {
        $author = $this->authorizations->where('status', -1)->first();
        return ArmadaTicketAuthorization::find($author->id) ?? null;
    }

    public function status($type = "default")
    {
        switch ($this->status) {
            case '0':
                $rejectauthor = $this->authorizations->where('status', -1)->first();
                if ($rejectauthor) {
                    return 'di Reject oleh ' . $rejectauthor->employee_name . ', Alasan: ' . $rejectauthor->reject_notes;
                } else {
                    // get status reject
                    if (isset($this->last_rejected_facility_form) && $this->facility_form == null) {
                        $form = $this->last_rejected_facility_form;
                        return 'Form Fasilitas Ditolak oleh ' . $form->terminated_by_employee->name . ', alasan: ' . $form->termination_reason;
                    }
                    if (isset($this->last_rejected_perpanjangan_form) && $this->perpanjangan_form == null) {
                        $form = $this->last_rejected_perpanjangan_form;
                        return 'Form Perpanjangan Ditolak oleh ' . $form->terminated_by_employee->name . ', alasan: ' . $form->termination_reason;
                    }
                    if (isset($this->last_rejected_mutasi_form) && $this->mutasi_form == null) {
                        $form = $this->last_rejected_mutasi_form;
                        return 'Form Mutasi Ditolak oleh ' . $form->terminated_by_employee->name . ', alasan: ' . $form->termination_reason;
                    }
                    if ($this->facility_form) {
                        $current_authorization = $this->facility_form->current_authorization();
                        if ($current_authorization) {
                            return 'Menunggu otorisasi form fasilitas oleh ' . $current_authorization->employee_name;
                        }
                    }
                    if ($this->perpanjangan_form) {
                        $armada_ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Armada')->first();
                        $ticketing_block_open_request = \App\Models\TicketingBlockOpenRequest::where('ticket_code', $this->code)
                            ->whereIn('status', [0, 1])
                            ->first();
                        if (now()->day > $armada_ticketing_block->max_block_day) {
                            return "Perpanjangan tidak dapat dilanjutkan karna sudah melebihi tanggal batas";
                        }
                        if (now()->day > $armada_ticketing_block->block_day) {
                            // munculkan link upload BA hanya pada saat sudah melebihi max day
                            if (isset($ticketing_block_open_request)) {
                                if ($ticketing_block_open_request->status == 0) {
                                    return 'Menunggu Konfirmasi BA oleh purchasing';
                                }
                            } else {
                                return 'Membutuhkan BA di upload oleh area untuk melanjutkan otorisasi perpanjangan';
                            }
                        }
                        $current_authorization = $this->perpanjangan_form->current_authorization();
                        if ($current_authorization) {
                            return 'Menunggu otorisasi form perpanjangan oleh ' . $current_authorization->employee_name;
                        }
                    }
                    if ($this->mutasi_form) {
                        $current_authorization = $this->mutasi_form->current_authorization();
                        if ($current_authorization) {
                            return 'Menunggu otorisasi form mutasi oleh ' . $current_authorization->employee_name;
                        }
                    }
                    return 'Pengadaan Baru';
                }
                break;

            case '1':
                $current_authorization = $this->current_authorization();
                return 'Menunggu Otorisasi Pengadaan Oleh ' . $current_authorization->employee_name;
                break;

            case '2':
                return 'Menunggu Proses PR';
                break;

            case '3':
                $current_authorization = $this->pr->current_authorization();
                return 'PR sudah dibuat, Menunggu otorisasi oleh ' . $current_authorization->employee_name;
                break;

            case '4':
                if ($type == "complete") {
                    if ($this->isFormValidationComplete() == true) {
                        $string_text = 'Menunggu Proses PR SAP - oleh GA ';
                        if (!$this->has_pr_sap()) {
                            $string_text .= '(undone)';
                        } else {
                            $string_text .= '(done)';
                        }
                        $string_text .= "\n";
                        if (count($this->po) == 0) {
                            $string_text .= "Menunggu Proses PO - oleh Purchasing (undone)";
                            if ($this->revise_po == 1) {
                                $string_text .= "\r\n" . 'Po di Revisi oleh ' . $this->revise_by_employee->name . ', Alasan: ' . $this->reason_revise;
                            }
                        } else {
                            $flag_alldone = true;
                            foreach ($this->po as $po) {
                                if ($po->status != 3) {
                                    $flag_alldone = false;
                                }
                            }
                            if ($flag_alldone) {
                                return 'Menunggu Penerimaan barang di Area';
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
                    } else {
                        return 'Menunggu validasi form kelengkapan';
                    }
                }
                if ($type == "default") {
                    if ($this->isFormValidationComplete() == true) {
                        if (count($this->po) == 0) {
                            return 'Menunggu Setup PO';
                        } else {
                            return 'Menunggu proses PO';
                        }
                    } else {
                        return 'Menunggu validasi form kelengkapan';
                    }
                }
                break;

            case '5':
                if (in_array($this->type(), ['Pengadaan', 'Replace', 'Renewal'])) {
                    return 'Menunggu Upload Berkas Penerimaan';
                } else {
                    return 'Menunggu Upload Berkas Penyerahan';
                }
                break;

            case '6':
                $string_text = 'Pengadaan Selesai';
                if (count($this->po) == 0 && $this->revise_po == 1) {
                    $string_text .= "\n";
                    $string_text .= 'Po di Revisi oleh ' . $this->revise_by_employee->name . ', Alasan: ' . $this->reason_revise;
                }

                return $string_text;

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

            default:
                return 'item_type_undefined';
                break;
        }

        // -1 Terminated
        // 0 New
        // 1 Pending Authorization
        // 2 Finish Authorization
        // 3 Otorisasi PR Dimulai
        // 4 Otorisasi Selesai
    }

    public function authorization_type() {
        switch ($this->authorization_type) {
            case 0:
                return 'PR Manual';
                break;
            case 1:
                return 'Form Fasilitas';
                break;
        }
    }

    public function type()
    {
        switch ($this->ticketing_type) {
            case 0:
                return 'Pengadaan';
                break;
            case 1:
                switch ($this->perpanjangan_form->form_type ?? 'unset') {
                    case 'perpanjangan':
                        return 'Perpanjangan';
                        break;

                    case 'stopsewa':
                        switch ($this->perpanjangan_form->stopsewa_reason) {
                            case 'replace':
                                return 'Replace';
                                break;
                            case 'renewal':
                                return 'Renewal';
                                break;
                            case 'end':
                                return 'End Kontrak';
                                break;
                        }
                        break;
                    default:
                        return 'Perpanjangan/Replace/Renewal/End Kontrak';
                        break;
                }
                break;
            case 2:
                return 'Mutasi';
                break;
            case 4:
                switch ($this->perpanjangan_form->form_type ?? 'unset') {
                    case 'stopsewa':
                        switch ($this->perpanjangan_form->stopsewa_reason) {
                            case 'replace':
                                return 'Percepatan Replace';
                                break;
                            case 'renewal':
                                return 'Percepatan Renewal';
                                break;
                            case 'end':
                                return 'Percepatan End Kontrak';
                                break;
                        }
                        break;
                    default:
                        return 'Percepatan Replace/Renewal/Stop Sewa';
                        break;
                }
        }
    }

    public function facility_form()
    {
        return $this->hasOne(FacilityForm::class);
    }

    public function last_rejected_facility_form()
    {
        return $this->hasOne(FacilityForm::class)->onlyTrashed()->orderBy('id', 'desc');
    }

    public function perpanjangan_form()
    {
        return $this->hasOne(PerpanjanganForm::class);
    }

    public function last_rejected_perpanjangan_form()
    {
        return $this->hasOne(PerpanjanganForm::class)->onlyTrashed()->orderBy('id', 'desc');
    }

    public function mutasi_form()
    {
        return $this->hasOne(MutasiForm::class);
    }

    public function last_rejected_mutasi_form()
    {
        return $this->hasOne(MutasiForm::class)->onlyTrashed()->orderBy('id', 'desc');
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

    public function created_by_employee()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id')->withTrashed();
    }

    public function terminated_by_employee()
    {
        return $this->belongsTo(Employee::class, 'terminated_by', 'id')->withTrashed();
    }

    public function revise_by_employee()
    {
        return $this->belongsTo(Employee::class, 'revise_by', 'id')->withTrashed();
    }

    public function additional_emails()
    {
        switch ($this->type()) {
            case 'Pengadaan':
                $type = 'pengadaan';
                break;
            case 'Perpanjangan':
                $type = 'perpanjangan';
                break;
            case 'Replace':
                $type = 'replace';
                break;
            case 'Renewal':
                $type = 'renewal';
                break;
            case 'End Kontrak':
                $type = 'end_kontrak';
                break;
            case 'Mutasi':
                $type = 'mutasi';
                break;
            default:
                return [];
                break;
        }
        $additional_email = EmailAdditional::where('category', 'armada')->where('type', $type)->first();
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
        $email_by_transaction_type = EmailAdditional::where('category', 'ga')->where('type', 'armada')->first()->emails;
        $emails = json_decode($email_by_transaction_type);
        return $emails;
    }

    public function email_template($data)
    {
        switch ($this->type()) {
            case 'Pengadaan':
                $text = "Dear Bapak/Ibu Vendor Rekanan PT. PMA" . "\n";
                $text .= "Terlampir adalah PO Armada Baru. Dengan Rincian Sebagai Berikut" . "\n";
                $text .= "Nomor PO : " . $data['po_number'] . "\n";
                $text .= "PIC : " . $data['pic'] . "\n";
                $text .= "Mohon bantuannya untuk memastikan proses serah terima unit nya sesuai tanggal Set Up pada PO" . "\n";
                $text .= "Mohon dibantu untuk koordinasi dengan PIC di area dan apabila ada perubahan mohon agar kami (Team Purchasing) juga selalu diinfokan" . "\n";
                $text .= "Apabila PO tersebut sudah sesuai mohon bantuannya untuk proses Approvalnya dan dikirimkan kembali kepada kami Max H+3 setelah PO diterima" . "\n";
                $text .= "Regards" . "\n";
                $text .= "Purchasing Staff" . "\n";
                break;
            case 'Perpanjangan':
                $text = "Dear Bapak/Ibu Vendor Rekanan PT. PMA" . "\n";
                $text .= "Terlampir adalah PO Perpanjangan" . "\n";
                $text .= "Nomor PO : " . $data['po_number'] . "\n";
                $text .= "Nomor Polisi : " . $data["plate"] . "\n";
                $text .= "Jenis Kendaraan : " . $data['unit_name'] . "\n";
                $text .= "Nama Area PMA : " . $data['salespoint_name'] . "\n";
                $text .= "Apabila PO tersebut sudah sesuai mohon bantuannya untuk proses Approvalnya dan dikirimkan kembali kepada kami Max H+3 setelah PO diterima" . "\n";
                $text .= "Regards " . "\n";
                $text .= "Purchasing Staff" . "\n";
                break;
            case 'Replace':
                $text = "Dear Bapak/Ibu Vendor Rekanan PT. PMA" . "\n";
                $text .= "Terlampir adalah PO Armada Baru. Dengan Rincian Sebagai Berikut" . "\n";
                $text .= "Nomor PO : " . $data['po_number'] . "\n";
                $text .= "PIC : " . $data['pic'] . "\n";
                $text .= "Nomor Telp : " . $data['phone'] . "\n";
                $text .= "Mohon bantuannya untuk memastikan proses serah terima unit nya sesuai tanggal Set Up pada PO" . "\n";
                $text .= "Mohon dibantu untuk koordinasi dengan PIC di area dan apabila ada perubahan mohon agar kami (Team Purchasing) juga selalu diinfokan" . "\n";
                $text .= "Apabila PO tersebut sudah sesuai mohon bantuannya untuk proses Approvalnya dan dikirimkan kembali kepada kami Max H+3 setelah PO diterima" . "\n";
                $text .= "Regards" . "\n";
                $text .= "Purchasing Staff" . "\n";
                break;
            case 'Renewal':

                $text = "Dear Bapak/Ibu Vendor Rekanan PT. PMA" . "\n";
                $text .= "Terlampir adalah PO Renewal Armada" . "\n";
                $text .= "Nomor PO : " . $data['po_number'] . "\n";
                $text .= "Nomor Polisi : " . $data["plate"] . "\n";
                $text .= "Jenis Kendaraan : " . $data['unit_name'] . "\n";
                $text .= "Nama Area PMA : " . $data['salespoint_name'] . "\n";
                $text .= "Dengan Rincian Sebagai Berikut" . "\n";
                $text .= "PIC : " . $data['pic'] . "\n";
                $text .= "Nomor Telp : " . $data['phone'] . "\n";
                $text .= "Mohon bantuannya untuk memastikan proses serah terima unit nya sesuai tanggal Set Up pada PO" . "\n";
                $text .= "Mohon dibantu untuk koordinasi dengan PIC di area dan apabila ada perubahan mohon agar kami (Team Purchasing) juga selalu diinfokan" . "\n";
                $text .= "Apabila PO tersebut sudah sesuai mohon bantuannya untuk proses Approvalnya dan dikirimkan kembali kepada kami Max H+3 setelah PO diterima" . "\n";
                $text .= "Regards" . "\n";
                $text .= "Purchasing Staff" . "\n";
                break;
            case 'End Kontrak':
                $text = "Dear Bapak/Ibu Vendor Rekanan PT. PMA" . "\n";
                $text .= "Unit Bapak/Ibu dengan rincian sebagai berikut" . "\n";
                $text .= "Nomor PO : " . $data['po_number'] . "\n";
                $text .= "Nomor Polisi : " . $data["plate"] . "\n";
                $text .= "Jenis Kendaraan : " . $data['unit_name'] . "\n";
                $text .= "Nama Area PMA : " . $data['salespoint_name'] . "\n";
                $text .= "Dengan Rincian Sebagai Berikut";
                $text .= "PIC : " . $data['pic'] . "\n";
                $text .= "Nomor Telp : " . $data['phone'] . "\n";
                $text .= "Tidak kami perpanjang masa sewanya" . "\n";
                $text .= "Mohon bantuannya untuk dilakukan penarikan kendaraan atas unit tersebut" . "\n";
                $text .= "Regards ";
                $text .= "Purchasing SPV";

                break;
            case 'Mutasi':
                $text = "Dear Bapak/Ibu Vendor Rekanan PT. PMA" . "\n";
                $text .= "Terlampir adalah PO Mutasi Armada" . "\n";
                $text .= "Nomor PO : " . $data['po_number'] . "\n";
                $text .= "Nomor Polisi : " . $data["plate"] . "\n";
                $text .= "Jenis Kendaraan : " . $data['unit_name'] . "\n";
                $text .= "Asal Area PMA : " . $data['salespoint_name'] . "\n";
                $text .= "Tujuan Area PMA : " . $data['send_name'] . "\n";
                $text .= "Apabila PO tersebut sudah sesuai mohon bantuannya untuk proses Approvalnya dan dikirimkan kembali kepada kami Max H+3 setelah PO diterima" . "\n";
                $text .= "Regards " . "\n";
                $text .= "Purchasing Staff" . "\n";
                break;
            default:
                $text = "";
                break;
        }
        return trim($text);
    }

    public function getBudgetUploadAttribute()
    {
        $budget_upload = BudgetUpload::find($this->budget_upload_id);
        return $budget_upload;
    }

    public function isFormValidationComplete()
    {
        $is_form_validated = true;
        if (isset($this->facility_form)) {
            if ($this->facility_form->is_form_validated == false) {
                $is_form_validated = false;
            }
        }
        if (isset($this->perpanjangan_form)) {
            if ($this->perpanjangan_form->is_form_validated == false) {
                $is_form_validated = false;
            }
        }
        if (isset($this->mutasi_form)) {
            if ($this->mutasi_form->is_form_validated == false) {
                $is_form_validated = false;
            }
        }

        return $is_form_validated;
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
}
