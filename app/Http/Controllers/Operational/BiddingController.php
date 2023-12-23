<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Ticket;
use App\Models\SalesPoint;
use App\Models\TicketItem;
use App\Models\TicketVendor;
use App\Models\Vendor;
use App\Models\TicketItemFileRequirement;
use App\Models\TicketItemAttachment;
use App\Models\TicketMonitoring;
use App\Models\Authorization;
use App\Models\Bidding;
use App\Models\BudgetUpload;
use App\Models\CustomBidding;
use App\Models\BiddingDetail;
use App\Models\BiddingAuthorization;
use App\Models\FRIForm;
use App\Models\FRIFormAuthorization;
use App\Mail\NotificationMail;
use Mail;
use Auth;
use DB;
use PDF;
use Storage;
use Crypt;
use Carbon\Carbon;

class BiddingController extends Controller
{
    public function biddingView(Request $request)
    {
        if ($request->input('status') == -1) {
            $tickets = Ticket::where('status', '>', 2)
                ->get()
                ->sortByDesc('created_at');
        } else {
            $tickets = Ticket::where('status', 2)
                ->get()
                ->sortByDesc('created_at');
        }

        return view('Operational.bidding', compact('tickets'));
    }

    public function biddingDetailView($ticket_code)
    {
        $ticket = Ticket::where('code', $ticket_code)->first();
        $trashed_ticket_vendors = TicketVendor::where('ticket_id', $ticket->id)->onlyTrashed()->get();
        $isVendorEditAvailable = $this->isVendorEditAvailable($ticket->code);
        $vendors = Vendor::all();
        if ($ticket->status < 2) {
            return back()->with('error', 'Tiket belum siap untuk dilakukan proses bidding');
        }
        return view('Operational.biddingdetail', compact('ticket', 'vendors', 'trashed_ticket_vendors', 'isVendorEditAvailable'));
    }

    public function addVendor(Request $request)
    {
        try {
            $ticket = Ticket::where('code', $request->ticket_code)->first();
            if (!$ticket) {
                throw new \Exception('Kode Ticket tidak ditemukan');
            } else if (!$this->isVendorEditAvailable($request->ticket_code)) {
                throw new \Exception('Salah satu item dalam pengadaan sudah dalam proses seleksi vendor, tidak dapat mengubah daftar vendor');
            } else {
                DB::beginTransaction();
                $newTicketVendor = new TicketVendor;
                $newTicketVendor->ticket_id = $ticket->id;
                // dd($request);
                if ($request->vendor_type == 0) {
                    $vendor = Vendor::findOrFail($request->vendor_id);
                    $newTicketVendor->vendor_id     = $vendor->id;
                    $newTicketVendor->name          = $vendor->name;
                    $vendor_name = $vendor->name;
                    $newTicketVendor->salesperson   = $vendor->salesperson;
                    $newTicketVendor->phone         = '';
                    $newTicketVendor->type          = 0;
                    $newTicketVendor->added_on      = "bidding";
                } else {
                    $newTicketVendor->vendor_id     = null;
                    $newTicketVendor->name          = $request->vendor_name;
                    $vendor_name = $request->vendor_name;
                    $newTicketVendor->salesperson   = $request->salesperson_name;
                    $newTicketVendor->phone         = $request->phone;
                    $newTicketVendor->type          = 1;
                    $newTicketVendor->added_on      = "bidding";
                }
                $newTicketVendor->save();
                DB::commit();
                return back()->with('success', 'Berhasil menambahkan vendor ' . $vendor_name . ' untuk ticket ' . $request->ticket_code);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', $ex->getMessage());
        }
    }

    public function removeVendor(Request $request)
    {
        try {
            $ticket_vendor                  = TicketVendor::findOrFail($request->ticket_vendor_id);
            if (!$this->isVendorEditAvailable($ticket_vendor->ticket->code)) {
                throw new \Exception('Salah satu item dalam pengadaan sudah dalam proses seleksi vendor, tidak dapat mengubah daftar vendor');
            }
            DB::beginTransaction();
            $ticket_vendor->deleted_by      = Auth::user()->id;
            $ticket_vendor->delete_reason   = $request->reason;
            $ticket_vendor->save();
            $ticket_vendor->delete();
            DB::commit();

            return back()->with('success', 'Berhasil menghapus vendor dari pengadaan');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', $ex->getMessage());
        }
    }

    private function isVendorEditAvailable($ticket_code)
    {
        $ticket = Ticket::where('code', $ticket_code)->first();
        if (!$ticket) {
            return false;
        } else {
            // pastikan status rejected tidak terhitung
            $ticket_biddings = Bidding::where('ticket_id', $ticket->id)->where('status', '!=', -1)
                ->first();
            $custom_ticket_biddings = CustomBidding::where('ticket_id', $ticket->id)
                ->first();
            if ($ticket_biddings != null || $custom_ticket_biddings != null) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function confirmFileRequirement(Request $request)
    {
        try {
            if ($request->type == 'file') {
                $item = TicketItemFileRequirement::findOrFail($request->id);
            } else if ($request->type == 'attachment') {
                $item = TicketItemAttachment::findOrFail($request->id);
            } else {
                // ba_vendor
                $ticket = Ticket::findOrFail($request->id);
            }

            if ($request->type == 'vendor') {
                $ticket->ba_status = 1;
                $ticket->ba_confirmed_by = Auth::user()->id;
                $ticket->save();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Konfirmasi berita acara pengajuan vendor';
                $monitor->save();
            } else {
                $item->status = 1;
                $item->confirmed_by = Auth::user()->id;
                $item->save();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $item->ticket_item->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Konfirmasi file pengadaan' . $item->name;
                $monitor->save();
            }
            return back()->with('success', 'Berhasil melakukan confirm kelengkapan');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal melakukan confirm kelengkapan');
        }
    }

    public function rejectFileRequirement(Request $request)
    {
        try {
            if ($request->type == 'file') {
                // file
                $item = TicketItemFileRequirement::findOrFail($request->id);
            } else if ($request->type == 'attachment') {
                // attachment
                $item = TicketItemAttachment::findOrFail($request->id);
            } else {
                // ba_vendor
                $ticket = Ticket::findOrFail($request->id);
            }

            if ($request->type == 'vendor') {
                $ticket->ba_status = -1;
                $ticket->ba_rejected_by = Auth::user()->id;
                $ticket->ba_reject_notes = $request->reason;
                $ticket->save();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Reject berita acara vendor';
                $monitor->save();
            } else {
                $item->status = -1;
                $item->rejected_by = Auth::user()->id;
                $item->reject_notes = $request->reason;
                $item->save();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $item->ticket_item->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Reject file pengadaan' . $item->name;
                $monitor->save();
            }
            return back()->with('success', 'Berhasil melakukan reject kelengkapan');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal melakukan reject kelengkapan');
        }
    }

    public function reviseConfirmedFileRequirement(Request $request)
    {
        try {
            DB::beginTransaction();
            if ($request->type == 'file') {
                // file
                $item = TicketItemFileRequirement::findOrFail($request->id);
            } else if ($request->type == 'attachment') {
                // attachment
                $item = TicketItemAttachment::findOrFail($request->id);
            } else {
                // ba_vendor
                $ticket = Ticket::findOrFail($request->id);
            }

            if ($request->type == 'vendor') {
                $ticket->ba_status = -1;
                $ticket->ba_rejected_by = Auth::user()->id;
                $ticket->ba_reject_notes = $request->reason;
                $ticket->save();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Revisi confirm berita acara vendor dengan alasan : ' . $request->reason;
                $monitor->save();
            } else {
                $item->status = -1;
                $item->rejected_by = Auth::user()->id;
                $item->reject_notes = $request->reason;
                $item->save();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $item->ticket_item->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Revisi confirm file pengadaan' . $item->name . ' dengan alasan : ' . $request->reason;
                $monitor->save();
            }

            DB::commit();
            return back()->with('success', 'Berhasil melakukan revisi confirm kelengkapan');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan revisi confirm kelengkapan (' . $ex->getMessage() . ')[' . $ex->getLine() . ']');
        }
    }

    public function removeTicketItem(Request $request)
    {
        try {
            $ticket_item                = TicketItem::find($request->ticket_item_id);
            // validasi jumlah item ticket yang tidak di cancel. jika sisa 1 maka tolak penghapusan item
            if ($ticket_item->ticket->ticket_item->where('isCancelled', false)->count() == 1) {
                throw new \Exception('Minimal jumlah item ticket 1');
            }

            DB::beginTransaction();
            $ticket_item->isCancelled   = true;
            $ticket_item->cancelled_by  = Auth::user()->id;
            $ticket_item->cancel_reason = $request->reason;
            $ticket_item->save();
            // tambahkan ke list cancel hardware details ke fri
            $ticket = $ticket_item->ticket;
            if ($ticket->fri_forms->count() > 0) {
                $fri_form = $ticket->fri_forms->first();
                $it_alias = "";
                if ($ticket_item->budget_pricing) {
                    $it_alias = $ticket_item->budget_pricing->IT_alias;
                } elseif ($ticket_item->maintenance_budget) {
                    $it_alias = $ticket_item->maintenance_budget->IT_alias;
                } elseif ($ticket_item->ho_budget) {
                    $it_alias = $ticket_item->ho_budget->IT_alias;
                } else {
                    // non budget ambil nama ticket item
                    $it_alias = $ticket_item->name;
                }
                if ($fri_form->disabled_hardware_details) {
                    // jika tidak kosong tambahkan ke list
                    $existing_disabled_items = json_decode($fri_form->disabled_hardware_details);
                    array_push($existing_disabled_items, $it_alias);
                    $fri_form->disabled_hardware_details = json_encode($existing_disabled_items);
                } else {
                    // jika tidak kosong langsung set
                    $fri_form->disabled_hardware_details = json_encode([$it_alias]);
                }
                $fri_form->save();
                $fri_form->refresh();
            }
            $isvalidated =  $this->validateBiddingDone($ticket_item->ticket->id);
            if ($isvalidated) {
                if ($ticket->fri_forms->count() > 0) {
                    $this->splitFRIForm($ticket->fri_forms->first());
                }
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket_item->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Menghapus item ' . $ticket_item->name . ' dari pengadaan';
                $monitor->save();

                DB::commit();
                return redirect('/bidding/' . $ticket_item->ticket->code)->with('success', 'Berhasil Menghapus item, seluruh otorisasi Bidding telah selesai, Silahkan melanjutkan proses di menu Purchase Requistion');
            } else {
                DB::commit();
                return back()->with('success', 'Berhasil menghapus item.');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus item ' . $ex->getMessage());
        }
    }

    public function vendorSelectionView($ticket_code, $ticket_item_id)
    {
        $ticket_item = TicketItem::find($ticket_item_id);
        $ticket = Ticket::where('code', $ticket_code)->first();
        $salespoint = SalesPoint::find($ticket->salespoint_id);
        $authorizations = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 1)->get();
        $bidding_revision_count = Bidding::where('ticket_id', $ticket_item->ticket_id)->where('is_because_revision', 1)->withTrashed()->get()->count();

        // validate kalo misal item sama form codenya sesuai
        if ($ticket_item->bidding) {
            $bidding = $ticket_item->bidding;
            if ($ticket_item->bidding->status == -1) {
                return view('Operational.vendorselection', compact('ticket_item', 'ticket', 'authorizations', 'bidding', 'bidding_revision_count'));
            } else {
                return view('Operational.vendorselectionresult', compact('ticket_item', 'ticket', 'bidding', 'bidding_revision_count'));
            }
        }
        return view('Operational.vendorselection', compact('ticket_item', 'ticket', 'authorizations', 'bidding_revision_count'));
    }

    public function addBiddingForm(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $ticket = Ticket::find($request->ticket_id);
            $ticket_item = TicketItem::find($request->ticket_item_id);
            if ($ticket_item->bidding) {
                $newBidding = $ticket_item->bidding;
                // remove old data on detail and authorization
                foreach ($newBidding->bidding_detail as $detail) {
                    $detail->delete();
                }

                foreach ($newBidding->bidding_authorization as $authorization) {
                    $authorization->delete();
                }
            } else {
                $newBidding                         = new Bidding;
            }
            $newBidding->ticket_id                  = $request->ticket_id;
            $newBidding->ticket_item_id             = $request->ticket_item_id;
            $newBidding->product_name               = $ticket_item->name;
            $newBidding->salespoint_name            = $ticket->salespoint->name;
            $newBidding->group                      = $request->group;
            $newBidding->other_name                 = ($request->group == 'others') ? $request->others_name : '-';
            $newBidding->price_notes                = $request->keterangan_harga;
            $newBidding->ketersediaan_barang_notes  = $request->keterangan_ketersediaan;
            $newBidding->ketentuan_bayar_notes      = $request->keterangan_pembayaran;
            $newBidding->others_notes               = $request->keterangan_lain;
            $newBidding->optional1_name             = $request->optional1_name;
            $newBidding->optional2_name             = $request->optional2_name;
            $newBidding->authorization_id           = $request->authorization_id;
            $newBidding->reason_revision           = $request->reason_revision;

            $newBidding->status                     = 0;
            $newBidding->rejected_by = null;
            $newBidding->reject_notes = null;
            $newBidding->save();

            foreach ($request->vendor as $vendor) {
                $selectedticketvendor = TicketVendor::find($vendor['ticket_vendor_id']);
                if ($selectedticketvendor) {
                    $newdetail                            = new BiddingDetail;
                    $newdetail->bidding_id                = $newBidding->id;
                    $newdetail->ticket_vendor_id          = $selectedticketvendor->id;
                    $newdetail->address                   = ($selectedticketvendor->vendor_id) ? $selectedticketvendor->vendor()->address : $vendor['address'];
                    $newdetail->start_harga               = $vendor['harga_awal'];
                    $newdetail->end_harga                 = $vendor['harga_akhir'];
                    $newdetail->start_ppn                 = $vendor['ppn_awal'];
                    $newdetail->end_ppn                   = $vendor['ppn_akhir'];
                    $newdetail->start_ongkir_price        = $vendor['send_fee_awal'];
                    $newdetail->end_ongkir_price          = $vendor['send_fee_akhir'];
                    $newdetail->start_pasang_price        = $vendor['apply_fee_awal'];
                    $newdetail->end_pasang_price          = $vendor['apply_fee_akhir'];
                    $newdetail->price_score               = $vendor['nilai_harga'];
                    $newdetail->spesifikasi               = $vendor['specs'];
                    $newdetail->ready                     = $vendor['ready'];
                    $newdetail->indent                    = $vendor['indent'];
                    $newdetail->garansi                   = $vendor['garansi'];
                    $newdetail->bonus                     = $vendor['bonus'];
                    $newdetail->ketersediaan_barang_score = $vendor['nilai_ketersediaan'];
                    $newdetail->creditcash                = $vendor['cc'];
                    $newdetail->menerbitkan_faktur_pajak  = $vendor['pajak'];
                    $newdetail->ketentuan_bayar_score     = $vendor['nilai_pembayaran'];
                    $newdetail->masa_berlaku_penawaran    = $vendor['period'];
                    $newdetail->start_lama_pengerjaan     = $vendor['time_awal'];
                    $newdetail->end_lama_pengerjaan       = $vendor['time_akhir'];
                    $newdetail->optional1_start           = $vendor['optional1_awal'];
                    $newdetail->optional1_end             = $vendor['optional1_akhir'];
                    $newdetail->optional2_start           = $vendor['optional2_awal'];
                    $newdetail->optional2_end             = $vendor['optional2_akhir'];
                    $newdetail->others_score              = $vendor['nilai_other'];
                    $newdetail->save();
                }
            }

            $authorization = Authorization::find($request->authorization_id);
            foreach ($authorization->authorization_detail as $detail) {
                $newauthorization                    = new BiddingAuthorization;
                $newauthorization->bidding_id        = $newBidding->id;
                $newauthorization->employee_id       = $detail->employee_id;
                $newauthorization->employee_name     = $detail->employee->name;
                $newauthorization->as                = $detail->sign_as;
                $newauthorization->employee_position = $detail->employee_position->name;
                $newauthorization->level             = $detail->level;
                $newauthorization->save();
            }
            $newBidding =  $newBidding->refresh();
            $monitor = new TicketMonitoring;
            $monitor->ticket_id      = $ticket->id;
            $monitor->employee_id    = Auth::user()->id;
            $monitor->employee_name  = Auth::user()->name;
            $monitor->message        = 'Membuat Form Bidding item "' . $newBidding->ticket_item->name . '" untuk di otorisasi';
            $monitor->save();
            DB::commit();

            $mail_to = $newBidding->current_authorization()->employee->email;
            $data = array(
                'original_emails' => [$mail_to],
                'transaction_type' => 'Pengadaan',
                'ticketing_type' => 'Barang Jasa',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $newBidding->current_authorization()->employee_name,
                'code' => $ticket->code,
                'bidding_id' => $newBidding->id,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'bidding_approval'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            return redirect('/bidding/' . $ticket->code . '/' . $request->ticket_item_id)->with('success', 'Berhasil membuat form bidding. Menunggu proses otorisasi oleh ' . $newBidding->current_authorization()->employee_name . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/bidding/' . $ticket->code)->with('error', 'Gagal membuat form bidding. Silahkan coba kembali atau hubungi developer');
        }
    }

    public function approveBidding(Request $request)
    {
        try {
            DB::beginTransaction();
            $bidding = Bidding::find($request->bidding_id);
            $ticket = $bidding->ticket;
            $bidding_authorization = BiddingAuthorization::find($request->bidding_authorization_id);
            if ($bidding_authorization->employee_id == Auth::user()->id) {
                $bidding_authorization->status = 1;
                $bidding_authorization->save();

                // upload untuk otorisasi kedua dan ketiga
                if ($request->file('file_penawaran') != null) {
                    $salespointname = str_replace(' ', '_', $bidding->ticket->salespoint->name);
                    $newext = pathinfo($request->file('file_penawaran')->getClientOriginalName(), PATHINFO_EXTENSION);
                    $name = "Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_SIGNED_" . $salespointname . '.' . $newext;
                    $path = "/attachments/ticketing/barangjasa/" . $bidding->ticket->code . '/item' . $bidding->ticket_item->id . '/files/' . $name;
                    $file = pathinfo($path);
                    $path = $request->file('file_penawaran')->storeAs($file['dirname'], $file['basename'], 'public');
                    $bidding->signed_filename = $file['basename'];
                    $bidding->signed_filepath = $path;
                    $bidding->save();
                }

                if ($request->file('file_cop') != null) {
                    $salespointname = str_replace(' ', '_', $bidding->ticket->salespoint->name);
                    $newext = pathinfo($request->file('file_cop')->getClientOriginalName(), PATHINFO_EXTENSION);
                    $name = "File_COP_" . $salespointname . '.' . $newext;
                    $path = "/attachments/ticketing/barangjasa/" . $bidding->ticket->code . '/item' . $bidding->ticket_item->id . '/files/' . $name;
                    $file = pathinfo($path);
                    $path = $request->file('file_cop')->storeAs($file['dirname'], $file['basename'], 'public');
                    $bidding->cop_filename = $file['basename'];
                    $bidding->cop_filepath = $path;
                    $bidding->save();
                }

                $bidding = Bidding::find($request->bidding_id);
                $next_authorization_text = ($bidding->current_authorization() != null) ? '(otorisasi selanjutnya ' . $bidding->current_authorization()->employee->name . ')' : '';
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $bidding->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Melakukan Approval Bidding item "' . $bidding->product_name . '" ' . $next_authorization_text;
                $monitor->save();

                if ($bidding->current_authorization() != null) {
                    $mail_to = $bidding->current_authorization()->employee->email;
                    $data = array(
                        'original_emails' => [$mail_to],
                        'transaction_type' => 'Pengadaan',
                        'ticketing_type' => 'Barang Jasa',
                        'salespoint_name' => $ticket->salespoint->name,
                        'from' => Auth::user()->name,
                        'to' => $bidding->current_authorization()->employee_name,
                        'code' => $ticket->code,
                        'bidding_id' => $bidding->id,
                    );
                    if (config('app.env') == 'local') {
                        $mail_to = [config('mail.testing_email')];
                    }

                    $emailflag = true;
                    try {
                        Mail::to($mail_to)->send(new NotificationMail($data, 'bidding_approval'));
                    } catch (\Exception $ex) {
                        $emailflag = false;
                    }
                    $emailmessage = "";
                    if (!$emailflag) {
                        $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                    }
                    DB::commit();
                    return redirect('/bidding/' . $bidding->ticket->code . '/' . $bidding->ticket_item_id)->with('success', 'Approval Bidding berhasil, menunggu otorisasi selanjutnya oleh ' . $bidding->current_authorization()->employee->name . $emailmessage);
                } else {
                    // tambahkan expired date +30 hari dari now() jika pengadaan selain salespoint HO / Area
                    $salespoint = $bidding->ticket->salespoint;
                    $bidding->expired_date = now()->addDays(30)->format('Y-m-d');

                    $bidding->status = 1;
                    $bidding->save();

                    $ticket = $ticket->refresh();
                    $area_employee_ids = $ticket->ticket_authorization->sortByDesc('level')->take(2)->pluck('employee_id');
                    $area_employee_emails = Employee::whereIn('id', $area_employee_ids)->get()->pluck('email')->toArray();

                    $purchasing_employee_ids = $bidding->bidding_authorization->pluck('employee_id');
                    $purchasing_employee_emails = Employee::whereIn('id', $purchasing_employee_ids)->get()->pluck('email')->toArray();
                    $mail_to = array_merge($area_employee_emails, $purchasing_employee_emails);
                    $additional_emails = $bidding->ticket->additional_emails() ?? [];
                    $area_first_author_email = $ticket->ticket_authorization->sortBy('level')->first()->employee->email;
                    $cc = array_merge($additional_emails, [$area_first_author_email]);
                    $data = array(
                        'original_emails' => $mail_to,
                        'original_ccs' => $cc,
                        'transaction_type' => 'Pengadaan',
                        'ticketing_type' => 'Barang Jasa',
                        'salespoint_name' => $ticket->salespoint->name,
                        'from' => Auth::user()->name,
                        'to' => 'OM / BM Manager',
                        'code' => $ticket->code,
                        'bidding_id' => $bidding->id,
                    );
                    if (config('app.env') == 'local') {
                        $mail_to = [config('mail.testing_email')];
                        $cc = [];
                    }
                    $emailflag = true;
                    try {
                        Mail::to($mail_to)->cc($cc)->send(new NotificationMail($data, 'bidding_approved'));
                    } catch (\Exception $ex) {
                        $emailflag = false;
                    }
                    $emailmessage = "";
                    if (!$emailflag) {
                        $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                    }

                    DB::commit();
                    $isvalidated =  $this->validateBiddingDone($bidding->ticket->id);
                    if ($isvalidated) {
                        $ticket->refresh();

                        if ($ticket->fri_forms->count() > 0 && $ticket->item_type == 4) {
                            $this->splitFRIForm($ticket->fri_forms->first());
                        }
                        return redirect('/bidding/' . $bidding->ticket->code)->with('success', 'Menunggu upload berkas penerimaan');

                        if ($ticket->fri_forms->count() > 0 && $ticket->item_type != 4) {
                            $this->splitFRIForm($ticket->fri_forms->first());
                        }
                        return redirect('/bidding/' . $bidding->ticket->code)->with('success', 'Seluruh Otorisasi Bidding telah selesai (Status ticket saat ini : ' . $ticket->status() . ')' . $emailmessage);
                    }
                    return redirect('/bidding/' . $bidding->ticket->code . '/' . $bidding->ticket_item_id)->with('success', 'Otorisasi telah selesai');
                }
            } else {
                throw new \Exception('Otorisasi login tidak sesuai');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan otorisasi (' . $ex->getMessage() . ')');
        }
    }

    public function rejectBidding(Request $request)
    {
        try {
            DB::beginTransaction();
            $bidding = Bidding::find($request->bidding_id);
            $ticket = $bidding->ticket;
            $bidding_authorization = BiddingAuthorization::find($request->bidding_authorization_id);
            if ($bidding_authorization->employee_id == Auth::user()->id) {
                $bidding_authorization->status = -1;
                $bidding_authorization->save();

                $bidding_authorization->bidding->status = -1;
                $bidding_authorization->bidding->rejected_by = Auth::user()->id;
                $bidding_authorization->bidding->reject_notes = $request->reason;
                $bidding_authorization->bidding->signed_filename = null;
                Storage::disk('public')->delete($bidding_authorization->bidding->signed_filepath);
                $bidding_authorization->bidding->signed_filepath = null;
                $bidding_authorization->bidding->cop_filename = null;
                Storage::disk('public')->delete($bidding_authorization->bidding->cop_filepath);
                $bidding_authorization->bidding->cop_filepath = null;
                $bidding_authorization->bidding->save();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $bidding->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Melakukan Reject Bidding';
                $monitor->save();

                $employee_ids = $bidding->bidding_authorization->pluck('employee_id');
                $employee_emails = Employee::whereIn('id', $employee_ids)->get()->pluck('email')->toArray();
                $mail_to = $employee_emails;
                $data = array(
                    'original_emails' => $mail_to,
                    'transaction_type' => 'Pengadaan',
                    'ticketing_type' => 'Barang Jasa',
                    'salespoint_name' => $ticket->salespoint->name,
                    'from' => Auth::user()->name,
                    'to' => "Purchasing Team",
                    'code' => $ticket->code,
                    'reason' => $request->reason,
                    'bidding_id' => $bidding->id,
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                }
                $emailflag = true;
                try {
                    Mail::to($mail_to)->send(new NotificationMail($data, 'bidding_reject'));
                } catch (\Exception $ex) {
                    $emailflag = false;
                }
                $emailmessage = "";
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }

                DB::commit();
                return redirect('/bidding/' . $bidding->ticket->code)->with('success', 'Berhasil melakukan reject form bidding. Silahkan melakukan pengajuan ulang' . $emailmessage);
            } else {
                DB::rollback();
                return redirect('/bidding/' . $bidding->ticket->code)->with('error', 'Otorisasi login tidak sesuai');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
            return redirect('/bidding/' . $bidding->ticket->code)->with('error', 'Reject form bidding gagal, silahkan coba kembali atau hubungi developer');
        }
    }

    public function uploadSignedFile(Request $request)
    {
        try {
            $bidding = Bidding::findOrFail($request->bidding_id);
            $salespointname = str_replace(' ', '_', $bidding->ticket->salespoint->name);
            $newext = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_SIGNED_" . $salespointname . '.' . $newext;
            $path = "/attachments/ticketing/barangjasa/" . $bidding->ticket->code . '/item' . $bidding->ticket_item->id . '/files/' . $name;
            $file = pathinfo($path);
            $path = $request->file('file')->storeAs($file['dirname'], $file['basename'], 'public');
            $bidding->signed_filename = $file['basename'];
            $bidding->signed_filepath = $path;
            $bidding->save();
            return back()->with('success', 'Berhasil upload file penawaran yang sudah ditandatangan, Silahkan melanjutkan proses approval');
        } catch (\Exception $ex) {
            return redirect('/bidding/' . $bidding->ticket->code)->with('error', 'Gagal upload file penawaran yang ditandatangan');
        }
    }

    public function uploadCopFile(Request $request)
    {
        try {
            $bidding = Bidding::findOrFail($request->bidding_id);
            $salespointname = str_replace(' ', '_', $bidding->ticket->salespoint->name);
            $newext = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "File_COP_" . $salespointname . '.' . $newext;
            $path = "/attachments/ticketing/barangjasa/" . $bidding->ticket->code . '/item' . $bidding->ticket_item->id . '/files/' . $name;
            $file = pathinfo($path);
            $path = $request->file('file')->storeAs($file['dirname'], $file['basename'], 'public');
            $bidding->cop_filename = $file['basename'];
            $bidding->cop_filepath = $path;
            $bidding->save();
            return back()->with('success', 'Berhasil upload file COP, Silahkan melanjutkan proses approval');
        } catch (\Exception $ex) {
            return redirect('/bidding/' . $bidding->ticket->code)->with('error', 'Gagal upload file COP');
        }
    }

    public function confirmMissingFile($ticket_item_id)
    {
        try {
            $ticket_item                            = TicketItem::findOrFail($ticket_item_id);
            $ticket_item->file_missing_status       = 1;
            $ticket_item->file_missing_confirmed_by = Auth::user()->id;
            $ticket_item->file_missing_rejected_by  = null;
            $ticket_item->file_missing_reject_notes = null;

            $ticket_item->save();

            return back()->with('success', 'Berhasil Confirm File Kekurangan Berkas');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Confirm File Kekurangan Berkas' . $ex->getMessage());
        }
    }

    public function rejectMissingFile(Request $request, $ticket_item_id)
    {
        try {
            $ticket_item                            = TicketItem::findOrFail($ticket_item_id);
            $ticket_item->file_missing_status       = -1;
            $ticket_item->file_missing_confirmed_by = null;
            $ticket_item->file_missing_reject_notes = $request->file_missing_reject_notes;
            $ticket_item->file_missing_rejected_by  = Auth::user()->id;

            $ticket_item->save();

            return back()->with('success', 'Berhasil Reject File Kekurangan Berkas');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Reject File Kekurangan Berkas' . $ex->getMessage());
        }
    }

    public function validateBiddingDone($ticket_id)
    {
        $ticket = Ticket::findOrFail($ticket_id);
        $flag = true;
        foreach ($ticket->ticket_item->where('isCancelled', '!=', true) as $ticket_item) {
            if (isset($ticket_item->bidding)) {
                if ($ticket_item->bidding->status != 1) {
                    $flag = false;
                }
            } else if (isset($ticket_item->custom_bidding)) {
                continue;
            } else {
                $flag = false;
            }
        }
        if ($flag) {
            if ($ticket->custom_settings != null) {
                // custom ticketing
                $custom_settings = json_decode($ticket->custom_settings);

                if (in_array('pr_manual', $custom_settings->steps)) {
                    $ticket->status = 3;
                } else if (in_array('po_sap', $custom_settings->steps)) {
                    $ticket->status = 6;
                } else if (in_array('received_file_upload', $custom_settings->steps)) {
                    $ticket->status = 6;
                } else {
                    throw new \Exception("Terjadi kesalahan pada custom settings. Silahkan hubungi developer");
                }
            } else {
                // non custom ticketing
                $ticket->status = 3;
            }
            $ticket->save();
        }
        return $flag;
    }

    public function terminateTicket(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($request->ticket_id);
            if ($ticket->status == 3) {
                return back()->with('error', 'Ticket yang sudah mencapai proses PR tidak dapat dibatalkan');
            }
            $ticket->status = -1;
            $ticket->terminated_by = Auth::user()->id;
            $ticket->termination_reason = $request->reason;
            $ticket->save();

            $monitor = new TicketMonitoring;
            $monitor->ticket_id      = $ticket->id;
            $monitor->employee_id    = Auth::user()->id;
            $monitor->employee_name  = Auth::user()->name;
            $monitor->message        = 'Membatalkan Ticket Pengadaan';
            $monitor->save();
            DB::commit();
            return redirect('/bidding')->with('success', 'Berhasil membatalkan pengadaan ' . $ticket->code);
        } catch (\Exception $ex) {
            DB::rollback();
            return back('/bidding')->with('error', 'Gagal membatalkan pengadaan ' . $ex->getMessage());
        }
    }

    public function uploadBiddingFile(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::find($request->ticket_id);
            $ticket_item = TicketItem::find($request->ticket_item_id);

            $customBidding = new CustomBidding;
            $customBidding->ticket_id       = $request->ticket_id;
            $customBidding->ticket_item_id  = $request->ticket_item_id;
            $customBidding->vendors         = $ticket->ticket_vendor->pluck('name')->toJson();
            $customBidding->status          = 0;

            $salespointname = str_replace(' ', '_', $ticket->salespoint->name);
            $ext = pathinfo($request->file('biddingfile')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "BIDDING_" . strtoupper($ticket_item->name) . $ticket_item->id . "_" . $salespointname . '.' . $ext;
            $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/' . $name;
            $file = pathinfo($path);
            $path = $request->file('biddingfile')->storeAs($file['dirname'], $file['basename'], 'public');

            $customBidding->filepath        = $path;
            $customBidding->created_by = Auth::user()->id;
            $customBidding->save();

            $monitor = new TicketMonitoring;
            $monitor->ticket_id      = $ticket->id;
            $monitor->employee_id    = Auth::user()->id;
            $monitor->employee_name  = Auth::user()->name;
            $monitor->message        = 'Upload File Bidding item "' . $customBidding->ticket_item->name;
            $monitor->save();

            $isvalidated =  $this->validateBiddingDone($ticket->id);
            DB::commit();
            if ($isvalidated) {
                if ($ticket->fri_forms->count() > 0) {
                    $this->splitFRIForm($ticket->fri_forms->first());
                }
                return redirect('/bidding/' . $ticket->code)->with('success', 'Seluruh Otorisasi Bidding telah selesai, Silahkan melanjutkan proses di menu Purchase Requistion');
            }

            return redirect('/bidding/' . $ticket->code)->with('success', 'Berhasil upload file bidding');
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
            return redirect('/bidding/' . $ticket->code)->with('error', 'Gagal upload file bidding. Silahkan coba kembali atau hubungi developer');
        }
    }

    public function biddingPrintView($encrypted_bidding_id)
    {
        try {
            $decrypted = Crypt::decryptString($encrypted_bidding_id);
        } catch (\Exception $ex) {
            abort(404);
        }
        $bidding = Bidding::find($decrypted);
        $ticket_item = $bidding->ticket_item;
        $ticket = $bidding->ticket;
        return view('Operational.biddingprintoutview', compact('ticket_item', 'ticket', 'bidding'));
    }

    public function extendExpiredDate(Request $request, $ticket_code, $ticket_item_id)
    {
        try {
            DB::beginTransaction();
            $bidding = Bidding::findOrFail($request->bidding_id);
            $old_expired_date = new Carbon($bidding->expired_date);
            $new_expired_date = new Carbon($request->new_expired_date);
            if ($old_expired_date >= $new_expired_date) {
                throw new \Exception('Tanggal expired date harus lebih baru dari expired date sebelumnya');
            } else {
                $bidding->expired_date = $request->new_expired_date;
                $bidding->save();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $bidding->ticket_id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Extend bidding form seleksi vendor terkait item ' . $bidding->product_name . " dari " . $old_expired_date->format('d-m-Y') . " menjadi " . $new_expired_date->format('d-m-Y');
                $monitor->save();
                DB::commit();
                return back()->with('success', 'Berhasil memperpanjangan expired date');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal memperpanjangan expired date.' . $ex->getMessage());
        }
    }

    public function revise(Request $request, $ticket_code, $ticket_item_id)
    {
        try {
            DB::beginTransaction();
            $bidding = Bidding::findOrFail($request->bidding_id);
            $ticket = $bidding->ticket;
            $custom_ticketing = json_decode($ticket->custom_settings);

            // sebelum otorisasi pr / pr setup kebawah
            if ($ticket->status < 4) {
                // kembalikan status tiket ke bidding / 2
                $product_name = $bidding->product_name;
                $bidding->is_because_revision = 1;
                $ticket->status = 2;
                $bidding->save();
                $ticket->save();
                $bidding->delete();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Revisi form seleksi vendor terkait item ' . $product_name;
                $monitor->save();

                DB::commit();
                return back()->with('success', 'Berhasil melakukan revisi terkait item ' . $bidding->product_name . '. Silahkan melakukan seleksi vendor ulang');
            } elseif ($ticket->status == 6 && $ticket->item_type == 4) {
                // disposal inventaris
                $product_name = $bidding->product_name;
                $ticket->status = 2;
                $bidding->is_because_revision = 1;
                $bidding->save();
                $ticket->save();
                $bidding->delete();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Revisi form seleksi vendor terkait item ' . $product_name;
                $monitor->save();

                DB::commit();
                return back()->with('success', 'Berhasil melakukan revisi terkait item ' . $bidding->product_name . '. Silahkan melakukan seleksi vendor ulang');
            } elseif ($ticket->status == 6 && $custom_ticketing->ticket_name == 'Pengadaan Fasilitas Karyawan COP') {
                // COP
                $product_name = $bidding->product_name;
                $ticket->status = 2;
                $bidding->is_because_revision = 1;
                $bidding->save();
                $ticket->save();
                $bidding->delete();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Revisi form seleksi vendor terkait item ' . $product_name;
                $monitor->save();

                DB::commit();
                return back()->with('success', 'Berhasil melakukan revisi terkait item ' . $bidding->product_name . '. Silahkan melakukan seleksi vendor ulang');
            } else {
                throw new \Exception('Bidding item ' . $bidding->product_name . ' dengan kode pengadaan ' . $ticket_code . " memiliki PR Manual terkait yang sedang dalam proses otorisasi / sudah full otorisasi");
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan revisi seleksi vendor.' . $ex->getMessage());
        }
    }

    public function reviseCustomBidding(Request $request)
    {
        try {
            DB::beginTransaction();
            $custom_bidding = CustomBidding::findOrFail($request->custom_bidding_id);
            $custom_bidding->delete_reason = $request->reason;
            $custom_bidding->save();
            $custom_bidding->delete();

            $ticket = $custom_bidding->ticket;
            $ticket_item = $custom_bidding->ticket_item;

            $monitor = new TicketMonitoring;
            $monitor->ticket_id      = $ticket->id;
            $monitor->employee_id    = Auth::user()->id;
            $monitor->employee_name  = Auth::user()->name;
            $monitor->message        = 'Revisi File Custom Bidding item "' . $ticket_item->name;
            $monitor->save();
            DB::commit();
            return redirect('/bidding/' . $ticket->code)->with('success', 'Berhasil revisi custom bidding');
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
            return back()->with('error', 'Gagal revisi custom bidding. Silahkan coba kembali atau hubungi developer');
        }
    }

    public function splitFRIForm($fri_form)
    {
        try {
            DB::beginTransaction();
            $ticket = $fri_form->ticket;
            // remove semua fri form yang bekas di split / ticket_vendor_id != null
            foreach ($ticket->fri_forms->where('ticket_vendor_id', '!=', null) as $fri_form) {
                foreach ($fri_form->authorizations as $auth) {
                    $auth->delete();
                }
                $fri_form->delete();
            }
            $lists = [];
            foreach ($ticket->bidding as $bidding) {
                $list = new \stdClass();
                $list->ticket_vendor_id = $bidding->selected_vendor()->ticket_vendor_id;
                $list->ticket_item_id = $bidding->ticket_item_id;
                array_push($lists, $list);
            }
            $lists = collect($lists);
            foreach ($lists->groupBy('ticket_vendor_id') as $list) {
                $ticket_vendor = TicketVendor::find($list[0]->ticket_vendor_id);
                $it_aliases = [];
                $ticket_items = TicketItem::whereIn('id', collect($list)->pluck('ticket_item_id'))->get();
                foreach ($ticket_items as $ticket_item) {
                    if ($ticket_item->budget_pricing) {
                        array_push($it_aliases, $ticket_item->budget_pricing->IT_alias);
                        continue;
                    }
                    if ($ticket_item->maintenance_budget) {
                        array_push($it_aliases, $ticket_item->maintenance_budget->IT_alias);
                        continue;
                    }
                    if ($ticket_item->ho_budget) {
                        array_push($it_aliases, $ticket_item->ho_budget->IT_alias);
                        continue;
                    }
                    array_push($it_aliases, $ticket_item->name);
                }

                $newFRIForm                       = new FRIForm;
                // untuk split vendor
                $newFRIForm->ticket_vendor_id     = $ticket_vendor->id;
                $newFRIForm->ticket_id            = $fri_form->ticket_id;
                $newFRIForm->date_request         = $fri_form->date_request;
                $newFRIForm->date_use             = $fri_form->date_use;
                $newFRIForm->work_location        = $fri_form->work_location;
                $newFRIForm->salespoint_id        = $fri_form->salespoint_id;
                $newFRIForm->salespoint_name      = $fri_form->salespoint_name;
                $newFRIForm->username_position    = $fri_form->username_position;
                $newFRIForm->division_department  = $fri_form->division_department;
                $newFRIForm->contact_number       = $fri_form->contact_number;
                $newFRIForm->email_address        = $fri_form->email_address;
                $newFRIForm->hardware_details     = $fri_form->hardware_details;
                $newFRIForm->application_details  = $fri_form->application_details;

                // tambah application details ke disabled
                $disabled_hardware_details = json_decode($newFRIForm->disabled_hardware_details);
                if ($disabled_hardware_details == null) {
                    $disabled_hardware_details = [];
                }

                foreach (json_decode($fri_form->hardware_details) as $hardware_detail) {
                    if (!in_array($hardware_detail->name, $it_aliases) && $hardware_detail->qty > 0) {
                        array_push($disabled_hardware_details, $hardware_detail->name);
                    }
                }
                $newFRIForm->disabled_hardware_details  = json_encode($disabled_hardware_details);
                $newFRIForm->created_by           = $fri_form->created_by;
                $newFRIForm->status               = $fri_form->status;
                $newFRIForm->save();

                // fri authorization
                foreach ($fri_form->authorizations as $auth) {
                    $newAuth                     = new FRIFormAuthorization;
                    $newAuth->fri_form_id        = $newFRIForm->id;
                    $newAuth->employee_id        = $auth->employee_id;
                    $newAuth->employee_name      = $auth->employee_name;
                    $newAuth->as                 = $auth->as;
                    $newAuth->employee_position  = $auth->employee_position;
                    $newAuth->level              = $auth->level;
                    $newAuth->status             = $auth->status;
                    $newAuth->save();
                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
        }
    }

    public function manualSplitFRI(Request $request)
    {
        $fri_form = FRIForm::find($request->fri_form_id);
        $this->splitFRIForm($fri_form);
        return back()->with('success', 'berhasil split form request infrastruktur');
    }
}
