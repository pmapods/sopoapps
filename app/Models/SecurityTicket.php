<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmailAdditional;
use DB;

class SecurityTicket extends Model
{
    use SoftDeletes;
    protected $table = 'security_ticket';
    protected $primaryKey = 'id';
    protected $appends = ['budget_upload'];

    public function salespoint()
    {
        return $this->belongsTo(SalesPoint::class);
    }

    public function authorizations()
    {
        return $this->hasMany(SecurityTicketAuthorization::class);
    }

    public function pr()
    {
        return $this->hasOne(Pr::class);
    }

    public function po()
    {
        return $this->hasMany(Po::class);
    }

    public function rejected_by_employee()
    {
        $author = $this->authorizations->where('status', -1)->first();
        return ArmadaTicketAuthorization::find($author->id) ?? null;
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

    public function status($type = "default")
    {
        switch ($this->status) {
            case '0':
                $rejectauthor = $this->authorizations->where('status', -1)->first();
                if ($rejectauthor) {
                    return 'di Reject oleh ' . $rejectauthor->employee_name . ', Alasan: ' . $rejectauthor->reject_notes;
                } else {
                    return 'Pengadaan Baru';
                }
                break;

            case '1':
                return 'Dalam proses otorisasi';
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
                }
                if ($type == "default") {
                    if (count($this->po) == 0) {
                        return 'Menunggu Setup PO';
                    } else {
                        return 'Menunggu proses PO';
                    }
                }
                break;

            case '5':
                return 'Menunggu Upload Berkas dari Area';

            case '6':
                $string_text = 'Pengadaan Security Selesai';
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
    }

    public function type()
    {
        switch ($this->ticketing_type) {
            case 0:
                return 'Pengadaan';
                break;
            case 1:
                return 'Perpanjangan';
                break;
            case 2:
                return 'Replace';
                break;
            case 3:
                return 'End Kontrak';
                break;
            case 4:
                return 'Pengadaan Lembur';
                break;
            case 5:
                return 'Percepatan Replace';
                break;
            case 6:
                return 'Percepatan End Kontrak';
                break;

            default:
                return 'undefined_security_type';
                break;
        }
    }

    public function po_reference()
    {
        return $this->belongsTo(Po::class, 'po_reference_number', 'no_po_sap');
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

    public function evaluasi_form()
    {
        return $this->hasMany(EvaluasiForm::class);
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
            case 'End Kontrak':
                $type = 'end_kontrak';
                break;
            case 'Pengadaan Lembur':
                $type = 'pengadaan_lembur';
                break;
            default:
                $type = '';
                break;
        }
        $additional_email = EmailAdditional::where('category', 'security')->where('type', $type)->first();
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
        $email_by_transaction_type = EmailAdditional::where('category', 'ga')->where('type', 'security')->first()->emails;
        $emails = json_decode($email_by_transaction_type);
        return $emails;
    }

    public function email_template($data)
    {
        switch ($this->type()) {
            case 'Pengadaan':
                $text = "Dear Bapak/Ibu Vendor Rekanan PT. PMA" . "\n";
                $text .= "Terlampir adalah PO Jasa Security" . "\n";
                $text .= "Nomor PO : " . $data["po_number"] . "\n";
                $text .= "Nama Area : " . $data["salespoint_name"] . "\n";
                $text .= "Dengan Rincian Sebagai Berikut" . "\n";
                $text .= "PIC : " . $data["pic_name"] . "\n";
                $text .= "NO Telp :" . $data["phone"] . "\n";
                $text .= "Mohon bantuannya untuk security dapat dikoordinasikan dengan tim area kami untuk Set Up pada Tanggal " . "\n";
                $text .= "Apabila PO tersebut sudah sesuai mohon bantuannya untuk proses Approvalnya dan dikirimkan kembali kepada kami Max H+3 setelah PO diterima" . "\n";
                $text .= "Regards " . "\n";
                $text .= "Purchasing Staff" . "\n";
                break;
            case 'Perpanjangan':
                $text = "Dear Bapak/Ibu Vendor Rekanan PT. PMA" . "\n";
                $text .= "Terlampir adalah PO Perpanjangan Sewa Jasa Security" . "\n";
                $text .= "Nama Area : " . $data["salespoint_name"] . "\n";
                $text .= "Nomor PO : " . $data["po_number"] . "\n";
                $text .= "Apabila PO tersebut sudah sesuai mohon bantuannya untuk proses Approvalnya dan dikirimkan kembali kepada kami Max H+3 setelah PO diterima" . "\n";
                $text .= "Regards" . "\n";
                $text .= "Purchasing Staff" . "\n";
                break;
            case 'Replace':
                $text = "Dear Bapak/Ibu Vendor Rekanan PT. PMA" . "\n";
                $text .= "Terlampir adalah PO Jasa Security" . "\n";
                $text .= "Nama Area : " . $data["salespoint_name"] . "\n";
                $text .= "Nomor PO : " . $data["po_number"] . "\n";
                $text .= "Dengan Rincian Sebagai Berikut" . "\n";
                $text .= "PIC : " . $data["pic_name"] . "\n";
                $text .= "NO Telp :" . $data["phone"] . "\n";
                $text .= "Mohon bantuannya untuk security dapat dikoordinasikan dengan tim area kami untuk serah terima jabatan di tanggal" . "\n";
                $text .= "Apabila PO tersebut sudah sesuai mohon bantuannya untuk proses Approvalnya dan dikirimkan kembali kepada kami Max H+3 setelah PO diterima" . "\n";
                $text .= "Regards" . "\n";
                $text .= "Purchasing Staff" . "\n";
                break;
            case 'End Kontrak':
                $text = "Dear Bapak/Ibu Vendor Rekanan PT. PMA" . "\n";
                $text .= "Sehubungan dengan adanya" . "\n";
                $text .= "Nama Area :" . $data["salespoint_name"] . "\n";
                $text .= "Alasan Pemutusan Kontrak :" . "\n";
                $text .= "Maka dari itu dengan berat hati kami menyampaikan Pemutusan Hubungan Kerjasama dengan perusahaan Bapak/Ibu" . "\n";
                $text .= "Terhitung sejak tanggal" . "\n";
                $text .= "Tanggal Akhir Kerjasama :" . $data["finish_date"] . "\n";
                $text .= "Atas Nama PT. Pinus Merah Abadi kami Mengucapkan terimakasih banyak untuk Support yang telah diberikan selama ini" . "\n";
                $text .= "Regards" . "\n";
                $text .= "Purchasing SPV" . "\n";
                break;
            case 'Pengadaan Lembur':
                $text = "";
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
