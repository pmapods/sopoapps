<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use DB;
use PDF;
use Auth;
use Mail;
use Storage;
use App\Models\Po;
use App\Models\Customer;
use App\Models\Product;
use App\Models\IssuePO;
use App\Models\Employee;
use App\Models\SalesPoint;
use App\Models\POItem;
use App\Models\Authorization;
use App\Models\POMonitoring;
use App\Models\POAuthorization;

class POController extends Controller
{
    public function poView(Request $request)
    {
        // show ticket liat based on auth access area
        $access = Auth::user()->location_access->pluck('salespoint_id');

        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');
        $indirect_salespoints = SalesPoint::where('region', 19)->get();

        return view('Operational.Sales.po', compact('available_salespoints', 'indirect_salespoints'));
    }

    public function poData(Request $request)
    {
        $search_value = $request->search["value"];
        if ($request->type == "posewa") { 
            $employee_access = Auth::user()->location_access_list();
            $posewa =  PO::leftJoin('salespoint', 'salespoint.id', '=', 'po.salespoint_id')
                ->leftJoin('po_item', 'po_item.po_id', '=', 'po.id')
                ->leftJoin('po_authorization', 'po_authorization.po_id', '=', 'po.id')
                ->leftJoin('employee', 'employee.id', '=', 'po.created_by')
                ->where(function ($query) use ($employee_access, $search_value) {
                    // filter apakan punya akses
                    $query->whereIn('po.salespoint_id', $employee_access)
                        ->orwhere('po_authorization.employee_id', Auth::user()->id);
                })
                ->where(function ($query) use ($search_value) {
                    // filter apakan punya akses
                    $query->where(DB::raw('lower(po.code)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(employee.name)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(salespoint.name)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(po_item.name)'), 'like', '%' . strtolower($search_value) . '%');
                })

                ->select('po.*')
                ->orderBy('po.status', 'desc')
                ->orderBy('po.created_at', 'asc')
                ->distinct('po.id');

            if ($request->type == "posewa") {
                $posewa = $posewa->where('po.request_type', '=', 1);
            }
            if ($request->type == "pojual") {
                $posewa = $posewa->where('po.request_type', '=', 2);
            }
            if ($request->type == "pocustom") {
                $posewa = $posewa->where('po.request_type', '=', 3);
            }
            if ($request->searchBySalesPoint) {
                $posewa = $posewa->where('salespoint_id', '=', $request->searchBySalesPoint);
            }
            if ($request->searchByMonth) {
                $posewa = $posewa->where(DB::raw('month(ticket.created_at)'), '=', $request->searchByMonth);
            }
            if ($request->searchByyear) {
                $posewa = $posewa->where(DB::raw('year(ticket.created_at)'), '=', $request->searchByyear);
            }
            $posewa = $posewa->get();

            $posewa = $posewa->filter(function ($item) use ($request) {
                if ($request->status == -1) {
                    if (in_array($item->status, [-1, 7, -2])) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if (!in_array($item->status, [-1, 7, -2])) {
                        return true;
                    } else {
                        return false;
                    }
                }
            });

            $posewa_paginate = $posewa->skip($request->start)->take($request->length);
            $datas = [];
            $count = 1 + $request->start;
            foreach ($posewa_paginate as $psewa) {
                $array = [];
                $created_by_employee = "";
                if (isset($psewa->created_by)) {
                    $created_by_employee = $psewa->created_by_employee->name;
                }
                $keterangan = "";
                if (isset($psewa->request_type)) {
                    $keterangan .= "\n";
                    $keterangan .= "Jenis Permintaan : " . $psewa->request_type();
                }
                $extra_text = "";

                // trim with max 200 character
                $status_text = $psewa->status("complete") . $extra_text;
                if (strlen($status_text) > 200) {
                    $status_text = substr($status_text, 0, 200) . '...';
                }

                array_push($array, $count);
                array_push($array, $psewa->code);
                array_push($array, $psewa->po_number);
                array_push($array, $psewa->created_at->translatedFormat('d F Y (H:i)'));
                array_push($array, $psewa->customer->name);
                array_push($array, $created_by_employee);
                array_push($array, $psewa->salespoint->name);
                array_push($array, $keterangan);
                array_push($array, implode(",\n", array_values($psewa->po_item->pluck('name')->toArray())));
                array_push($array, $status_text);
                array_push($datas, $array);
                $count++;
            }
            return response()->json([
                "data" => $datas,
                "draw" => $request->draw,
                "recordsFiltered" => $posewa->count(),
                "recordsTotal" => $posewa->count(),
            ]);
        }
    }

    public function poDetailView($code)
    {
        // dd(base64_decode($code));

        $ticket = Ticket::where('code', $code)->first();
        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $has_access = false;
        $armada_types = ArmadaType::all();
        if ($user_location_access->contains($ticket->salespoint_id)) {
            $has_access = true;
        }
        // jika ticket fri dan ada user yang ada di approval fri boleh akses tiket
        if ($ticket->fri_forms->count() > 0) {
            $check_if_author_exist = $ticket->fri_forms->first()->authorizations->where('employee_id', Auth::user()->id)->first();
            if ($check_if_author_exist) {
                $has_access = true;
            }
        }
        if (!$has_access) {
            return redirect('/ticketing')->with('error', 'Anda tidak memiliki akses untuk tiket berikut. Tidak memiliki akses salespoint "' . $ticket->salespoint->name . '"');
        }
        if ($ticket) {
            $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
            $available_salespoints = $available_salespoints->groupBy('region');

            $indirect_salespoints = SalesPoint::where('region', 19)->get();

            $budget_category_items = BudgetPricingCategory::all();
            $maintenance_budgets = MaintenanceBudget::all()->groupBy('category_name');
            $ho_budgets = HOBudget::all()->groupBy('category_name');

            // active vendors
            $vendors = Vendor::where('status', 0)->get();

            // trashed ticket vendor
            $trashed_ticket_vendors = TicketVendor::where('ticket_id', $ticket->id)->onlyTrashed()->get();

            // show file completement data
            $filecategories = FileCategory::all();

            // fri_authorization
            $fri_authorization = Authorization::where('form_type', 12)->first();
            if ($ticket->status == 0) {
                // if draft make it editable
                return view('Operational.ticketingdetail', compact('ticket', 'available_salespoints', 'indirect_salespoints', 'budget_category_items', 'vendors', 'filecategories', 'trashed_ticket_vendors', 'maintenance_budgets', 'ho_budgets', 'fri_authorization'));
            } else {
                return view('Operational.ticketingform', compact('ticket', 'available_salespoints', 'indirect_salespoints', 'budget_category_items', 'vendors', 'filecategories', 'trashed_ticket_vendors', 'maintenance_budgets', 'ho_budgets', 'fri_authorization', 'armada_types'));
            }
        } else {
            return redirect('/ticketing')->with('error', 'Form tidak ditemukan');
        }
    }

    public function addNewPO(Request $request)
    {
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');

        // active customer
        $customers = Customer::where('status', 0)->get();
        // product
        $product = Product::whereNull('deleted_at')->get();
        // authorization
        $request_type = $request->request_type;

        if ($request->request_type == '1') {
            return view('Operational.Sales.posewadetail', compact('available_salespoints', 'customers', 'product', 'request_type'));
        }
        if ($request->request_type == '2') {
            return view('Operational.Sales.pojualdetail', compact('available_salespoints', 'customers', 'product', 'request_type'));
        }
        if ($request->request_type == '3') {
            return view('Operational.Sales.pocustomdetail', compact('available_salespoints', 'customers', 'product', 'request_type'));
        }
        return back()->with('error', 'Terjadi Kesalahan silahkan mencoba lagi');
    }

    public function deleteTicket(Request $request)
    {
        $ticket = Ticket::where('code', $request->code)->first();
        if ($ticket) {
            $ticket->delete();
            return redirect('/ticketing')->with('success', 'Berhasil Menghapus Ticket');
        } else {
            return redirect('/ticketing')->with('error', 'Gagal Menghapus Ticket');
        }
    }

    public function addTicket(Request $request)
    {
        try {
            // dd($request);
            DB::beginTransaction();
            if (!isset($request->salespoint)) {
                return back()->with('error', 'SalesPoint harus dipilih');
            }
            $ticket = Ticket::find($request->id);
            $isnew = true;
            if ($ticket == null) {
                $ticket = new Ticket;
            } else {
                $isnew = false;
            }
            $ticket->requirement_date   = $request->requirement_date;
            $ticket->salespoint_id      = $request->salespoint;
            $ticket->authorization_id   = $request->authorization;
            $ticket->item_type          = $request->item_type;
            $ticket->request_type       = $request->request_type;
            $ticket->is_it              = $request->is_it;
            $ticket->budget_type        = $request->budget_type;
            $ticket->division           = $request->division;
            $ticket->over_budget_reason    = $request->reason_over_budget;
            $ticket->is_over_budget        = $request->is_over_budget;

            if ($ticket->division == "Indirect") {
                $ticket->indirect_salespoint_id        = $request->indirect_salespoint_id;
            } else {
                $ticket->indirect_salespoint_id        = null;
            }
            $ticket->reason             = $request->reason;
            $ticket->save();
            if ($ticket->code == null) {
                $ticket->code = 'draft_' . date('ymdHi') . $ticket->id;
            }
            if ($request->ba_vendor_name != null && $request->ba_vendor_file != null) {
                $salespointname = str_replace(' ', '_', $ticket->salespoint->name);
                $ext = pathinfo($request->ba_vendor_name, PATHINFO_EXTENSION);
                $ticket->ba_vendor_filename = "berita_acara_vendor_" . $salespointname . '.' . $ext;
                $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/' . $ticket->ba_vendor_filename;

                if (!str_contains($request->ba_vendor_file, 'base64,')) {
                    // url
                    $file = Storage::disk('public')->get(explode('storage', $request->ba_vendor_file)[1]);
                    Storage::disk('public')->put($path, $file);
                } else {
                    // base 64 data
                    $file = explode('base64,', $request->ba_vendor_file)[1];
                    Storage::disk('public')->put($path, base64_decode($file));
                }
                $ticket->ba_vendor_filepath = $path;
            } else {
                $ticket->ba_vendor_filename = null;
                $ticket->ba_vendor_filepath = null;
            }
            $ticket->save();
            $salespoint = $ticket->salespoint;

            // remove old data
            if ($ticket->ticket_item->count() > 0) {
                // get deleted new data
                $registered_id = collect($request->item)->pluck('id')->filter(function ($item) {
                    if ($item != "undefined") {
                        return true;
                    } else {
                        return false;
                    }
                });
                $deleted_item = [];
                if ($ticket->ticket_item->count() > 0) {
                    $deleted_item = $ticket->ticket_item->whereNotIn('id', $registered_id);
                }
                foreach ($deleted_item as $deleted) {
                    $deleted->delete();
                }
            }
            // get all registered file id on ticket
            $registered_file_id = [];
            foreach ($ticket->ticket_item as $t_item) {
                if ($t_item->ticket_item_file_requirement->count() > 0) {
                    foreach ($t_item->ticket_item_file_requirement as $t_item_file) {
                        array_push($registered_file_id, $t_item_file->id);
                    }
                }
            }
            // get all files from request
            $allfiles = [];
            if (count($request->item ?? []) > 0) {
                foreach ($request->item as $item) {
                    if (isset($item['files'])) {
                        foreach ($item['files'] as $file) {
                            if ($file['id'] != "undefined") {
                                array_push($allfiles, $file);
                            }
                        }
                    }
                }
            }
            $deleted_files = array_diff($registered_file_id, collect($allfiles)->pluck('id')->toArray());
            foreach ($deleted_files as $del) {
                $r = TicketItemFileRequirement::find($del);
                // TODO delete the file from the storage
                $r->delete();
            }
            // update registered files with new data if updated
            foreach ($allfiles as $afiles) {
                $tfile = TicketItemFileRequirement::find($afiles['id']);
                $ext = pathinfo($afiles['name'], PATHINFO_EXTENSION);
                $salespointname = str_replace(' ', '_', $ticket->salespoint->name);
                $name = $tfile->file_completement->filename . '_' . $salespointname . '.' . $ext;
                $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/item' . $tfile->ticket_item->id . '/files/' . $name;
                if (str_contains($afiles['file'], 'base64,')) {
                    $file = explode('base64,', $afiles['file'])[1];
                    $tfile->path = $path;
                    $tfile->name = $name;
                    $tfile->save();
                    Storage::disk('public')->put($path, base64_decode($file));
                }
            }

            // add ticket item that not registered
            $newitems = collect($request->item)->where('id', 'undefined');
            if (isset($newitems)) {
                foreach ($newitems as $key => $item) {
                    $newTicketItem                        = new TicketItem;
                    $newTicketItem->ticket_id             = $ticket->id;
                    if ($item['budget_pricing_id'] != "undefined" && is_numeric($item['budget_pricing_id'])) {
                        $newTicketItem->budget_pricing_id = $item['budget_pricing_id'];
                    }
                    if ($item['maintenance_budget_id'] != "undefined" && is_numeric($item['maintenance_budget_id'])) {
                        $newTicketItem->maintenance_budget_id = $item['maintenance_budget_id'];
                    }
                    if ($item['ho_budget_id'] != "undefined" && is_numeric($item['ho_budget_id'])) {
                        $newTicketItem->ho_budget_id = $item['ho_budget_id'];
                    }
                    $newTicketItem->name                  = $item['name'];
                    $newTicketItem->brand                 = $item['brand'];
                    $newTicketItem->type                  = $item['type'];
                    $newTicketItem->price                 = $item['price'];
                    $newTicketItem->count                 = $item['count'];

                    if (
                        $request->is_over_budget == 1 && $item['price'] > $item['item_max_price'] ||
                        $ticket->is_over_budget == 1 && $item['price'] > $item['item_max_price']
                    ) {
                        $newTicketItem->nilai_budget_over_budget = $item['item_max_price'];
                        $newTicketItem->nilai_ajuan_over_budget  = $item['price'];
                        $selisih_over_budget_item = $item['item_max_price'] - $item['price'];
                        $selisih_over_budget_item_remove_minus = str_replace('-', '', $selisih_over_budget_item);
                        $newTicketItem->selisih_over_budget      = $selisih_over_budget_item_remove_minus;
                    } else {
                        $newTicketItem->nilai_budget_over_budget = null;
                        $newTicketItem->nilai_ajuan_over_budget  = null;
                        $newTicketItem->selisih_over_budget      = null;
                    }

                    $newTicketItem->save();
                    if (isset($item["attachments"])) {
                        foreach ($item["attachments"] as $attachment) {
                            $newAttachment = new TicketItemAttachment;
                            $newAttachment->ticket_item_id = $newTicketItem->id;
                            $salespointname = str_replace(' ', '_', $ticket->salespoint->name);
                            $filename = pathinfo($attachment['filename'], PATHINFO_FILENAME);
                            $ext = pathinfo($attachment['filename'], PATHINFO_EXTENSION);
                            $newAttachment->name = $filename . '_' . $salespointname . '.' . $ext;
                            $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/item' . $newTicketItem->id . '/' . $newAttachment->name;
                            if (!str_contains($attachment['file'], 'base64,')) {
                                $file = Storage::disk('public')->get(explode('storage', $attachment['file'])[1]);
                                Storage::disk('public')->put($path, $file);
                            } else {
                                // base 64 data
                                $file = explode('base64,', $attachment['file'])[1];
                                Storage::disk('public')->put($path, base64_decode($file));
                            }
                            $newAttachment->path = $path;
                            $newAttachment->save();
                        }
                    }
                    if (isset($item['files'])) {
                        foreach ($item["files"] as $filereq) {
                            $ext = pathinfo($filereq['name'], PATHINFO_EXTENSION);
                            $salespointname = str_replace(' ', '_', $ticket->salespoint->name);
                            $filecompletement = FileCompletement::find($filereq['file_completement_id']);
                            $name = $filecompletement->filename . '_' . $salespointname . '.' . $ext;

                            $newfile                        = new TicketItemFileRequirement;
                            $newfile->ticket_item_id        = $newTicketItem->id;
                            $newfile->file_completement_id  = $filereq['file_completement_id'];
                            $newfile->name                  = $name;
                            $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/item' . $newTicketItem->id . '/files/' . $name;
                            if (!str_contains($filereq['file'], 'base64,')) {
                                $file = Storage::disk('public')->get(explode('storage', $filereq['file'])[1]);
                                Storage::disk('public')->put($path, $file);
                            } else {
                                // base 64 data
                                $file = explode('base64,', $filereq['file'])[1];
                                Storage::disk('public')->put($path, base64_decode($file));
                            }
                            $newfile->path                  = $path;
                            $newfile->save();
                        }
                    }
                }
            }

            $registereditem = collect($request->item)->filter(function ($oitem) {
                if ($oitem['id'] != "undefined") {
                    return true;
                } else {
                    return false;
                }
            });
            foreach ($registereditem as $reg) {
                if (isset($reg['files'])) {
                    foreach ($reg['files'] as $regfile) {
                        if ($regfile['id'] == "undefined") {
                            $filecompletement = FileCompletement::find($regfile['file_completement_id']);
                            $ext = pathinfo($regfile['name'], PATHINFO_EXTENSION);
                            $salespointname = str_replace(' ', '_', $ticket->salespoint->name);
                            $name = $filecompletement->filename . '_' . $salespointname . '.' . $ext;

                            $newfile                        = new TicketItemFileRequirement;
                            $newfile->ticket_item_id        = $reg['id'];
                            $newfile->file_completement_id  = $regfile['file_completement_id'];
                            $newfile->name                  = $name;
                            $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/item' . $reg['id'] . '/files/' . $name;
                            if (str_contains($regfile['file'], 'base64,')) {
                                // base 64 data
                                $newfile->path = $path;
                                $newfile->save();
                                $file = explode('base64,', $regfile['file'])[1];
                                Storage::disk('public')->put($path, base64_decode($file));
                            }
                        }
                    }
                }
            }
            // ticket vendor
            if ($ticket->ticket_vendor->count() > 0) {
                $registered_id = collect($request->vendor)->pluck('id')->filter(function ($item) {
                    if ($item != "undefined") {
                        return true;
                    } else {
                        return false;
                    }
                });
                $deleted_item = [];
                if ($ticket->ticket_vendor->count() > 0) {
                    $deleted_item = $ticket->ticket_vendor->whereNotIn('id', $registered_id);
                }
                foreach ($deleted_item as $deleted) {
                    $deleted->deleted_by = Auth::user()->id;
                    $deleted->delete_reason = null;
                    $deleted->delete();
                }
            }

            // add ticket vendor that not registered yet
            $newitems = collect($request->vendor)->where('id', 'undefined');
            if (isset($newitems)) {
                foreach ($newitems as $list) {
                    $vendor = Vendor::find($list['vendor_id']);
                    $newTicketVendor = new TicketVendor;
                    $newTicketVendor->ticket_id         = $ticket->id;
                    if ($vendor) {
                        $newTicketVendor->vendor_id     = $vendor->id;
                        $newTicketVendor->name          = $vendor->name;
                        $newTicketVendor->salesperson   = $vendor->salesperson;
                        // hide phone on order (personal for purhasing team)
                        $newTicketVendor->phone         = '';
                        $newTicketVendor->type          = 0;
                    } else {
                        $newTicketVendor->vendor_id     = null;
                        $newTicketVendor->name          = $list['name'];
                        $newTicketVendor->salesperson   = $list['sales'];
                        $newTicketVendor->phone         = $list['phone'];
                        $newTicketVendor->type          = 1;
                    }
                    $newTicketVendor->save();
                }
            }

            // ticket authorization
            if (isset($ticket->ticket_authorization)) {
                if ($ticket->ticket_authorization->count() > 0) {
                    foreach ($ticket->ticket_authorization as $auth) {
                        $auth->delete();
                    }
                }
            }

            $authorization_over_budget_area = Authorization::where('form_type', 14)->first();
            $authorization_over_budget_ho = Authorization::where('form_type', 15)->first();

            $authorizations = Authorization::find($request->authorization);

            if (isset($authorizations)) {
                foreach ($authorizations->authorization_detail as $detail) {
                    $newTicketAuthorization                     = new TicketAuthorization;
                    $newTicketAuthorization->ticket_id          = $ticket->id;
                    $newTicketAuthorization->employee_id        = $detail->employee_id;
                    $newTicketAuthorization->employee_name      = $detail->employee->name;
                    $newTicketAuthorization->as                 = $detail->sign_as;
                    $newTicketAuthorization->employee_position  = $detail->employee_position->name;
                    $newTicketAuthorization->level              = $detail->level;
                    $newTicketAuthorization->save();
                }

                if ($request->is_over_budget == 1 || $ticket->is_over_budget == 1) {
                    if ($request->salespoint != 251 || $request->salespoint != 252) {
                        foreach ($authorization_over_budget_area->authorization_detail as $detail) {
                            $newTicketAuthorization                     = new TicketAuthorization;
                            $newTicketAuthorization->ticket_id          = $ticket->id;
                            $newTicketAuthorization->employee_id        = $detail->employee_id;
                            $newTicketAuthorization->employee_name      = $detail->employee->name;
                            $newTicketAuthorization->as                 = $detail->sign_as;
                            $newTicketAuthorization->employee_position  = $detail->employee_position->name;
                            $newTicketAuthorization->level              = $detail->level;
                            $newTicketAuthorization->save();
                        }
                    } else {
                        foreach ($authorization_over_budget_ho->authorization_detail as $detail) {
                            $newTicketAuthorization                     = new TicketAuthorization;
                            $newTicketAuthorization->ticket_id          = $ticket->id;
                            $newTicketAuthorization->employee_id        = $detail->employee_id;
                            $newTicketAuthorization->employee_name      = $detail->employee->name;
                            $newTicketAuthorization->as                 = $detail->sign_as;
                            $newTicketAuthorization->employee_position  = $detail->employee_position->name;
                            $newTicketAuthorization->level              = $detail->level;
                            $newTicketAuthorization->save();
                        }
                    }
                }
            }

            // optional attachment
            if ($ticket->ticket_additional_attachment->count() > 0) {
                foreach ($ticket->ticket_additional_attachment as $attach) {
                    $attach->delete();
                }
            }
            if (isset($request->opt_attach)) {
                foreach ($request->opt_attach as $attach) {
                    $path = '/attachments/ticketing/barangjasa/' . $ticket->code . '/optional_attachment/' . $attach['name'];
                    if (!str_contains($attach['file'], 'base64,')) {
                        // url
                        $replaced = str_replace('%20', ' ', explode('storage', $attach['file'])[1]);;
                        $file = Storage::disk('public')->get($replaced);
                        Storage::disk('public')->put($path, $file);
                    } else {
                        // base 64 data
                        $file = explode('base64,', $attach['file'])[1];
                        Storage::disk('public')->put($path, base64_decode($file));
                    }
                    $newAttachment = new TicketAdditionalAttachment;
                    $newAttachment->ticket_id = $ticket->id;
                    $newAttachment->name = $attach['name'];
                    $newAttachment->path = $path;
                    $newAttachment->save();
                }
            }

            // if IT create FRI Form
            $ticket->refresh();
            if ($ticket->is_it) {
                if ($ticket->fri_forms->count() > 0) {
                    $fri_form = $ticket->fri_forms->first();
                } else {
                    $fri_form             = new FRIForm;
                    $fri_form->ticket_id  = $ticket->id;
                }
                $fri_form->date_request         = $ticket->created_at->format('Y-m-d');
                $fri_form->date_use             = $ticket->requirement_date;
                $fri_form->work_location        = $request->work_location;
                $fri_form->salespoint_id        = $ticket->salespoint_id;
                $fri_form->salespoint_name      = $ticket->salespoint->name;

                $username_position = null;
                $user_email_address = null;
                if (isset($ticket->ticket_authorization) && $ticket->ticket_authorization->count() > 0) {
                    $first_author = $ticket->ticket_authorization->sortBy('level')->first();
                    $username_position = $first_author->employee_name . " | " . $first_author->employee_position;
                    $user_email_address = $first_author->employee->email;
                }
                $fri_form->username_position    = $username_position;

                $fri_form->division_department  = $request->division_department;
                $fri_form->contact_number       = $request->contact_number;
                $fri_form->email_address        = $user_email_address;

                $fri_form->hardware_details     = json_encode(array_values($request->hardware_details));

                $application_details = json_encode(array_values(array_filter(array_unique($request->application_details))));
                $fri_form->application_details  = $application_details;

                $fri_form->created_by           = Auth::user()->id;
                $fri_form->save();

                // fri authorization
                if (isset($fri_form->authorizations)) {
                    if ($fri_form->authorizations->count() > 0) {
                        foreach ($fri_form->authorizations as $auth) {
                            $auth->delete();
                        }
                    }
                }

                // Otorisasi FRI ambil 2 author pertatma dari ticketing + otorisasi TIM IT
                $level = 1;
                if (isset($ticket->ticket_authorization)) {
                    foreach ($ticket->ticket_authorization->sortBy('level')->take(2) as $ticket_authorization) {
                        $newFRIAuthorization                     = new FRIFormAuthorization;
                        $newFRIAuthorization->fri_form_id        = $fri_form->id;
                        $newFRIAuthorization->employee_id        = $ticket_authorization->employee_id;
                        $newFRIAuthorization->employee_name      = $ticket_authorization->employee_name;
                        $newFRIAuthorization->as                 = $ticket_authorization->as;
                        $newFRIAuthorization->employee_position  = $ticket_authorization->employee_position;
                        $newFRIAuthorization->level              = $level;
                        $newFRIAuthorization->save();
                        $level++;
                    }
                }
                $fri_authorization = Authorization::find($request->fri_authorization);
                if (isset($fri_authorization)) {
                    foreach ($fri_authorization->authorization_detail->sortBy('level') as $detail) {
                        $newFRIAuthorization                     = new FRIFormAuthorization;
                        $newFRIAuthorization->fri_form_id        = $fri_form->id;
                        $newFRIAuthorization->employee_id        = $detail->employee_id;
                        $newFRIAuthorization->employee_name      = $detail->employee->name;
                        $newFRIAuthorization->as                 = $detail->sign_as;
                        $newFRIAuthorization->employee_position  = $detail->employee_position->name;
                        $newFRIAuthorization->level              = $level;
                        $newFRIAuthorization->save();
                        $level++;
                    }
                }
            } else {
                if ($ticket->fri_forms->count() > 0) {
                    if (isset($ticket->fri_forms->first()->authorizations)) {
                        if ($ticket->fri_forms->first()->authorizations->count() > 0) {
                            foreach ($fri_form->authorizations as $auth) {
                                $auth->delete();
                            }
                        }
                    }
                    $ticket->fri_forms->first()->forceDelete();
                }
            }
            DB::commit();
            if ($request->type == 1) {
                // start authorization
                $ticket = Ticket::find($ticket->id);
                return $this->startAuthorization($ticket);
            } else {
                if ($isnew) {
                    return redirect('/ticketing/' . $ticket->code)->with('success', 'Berhasil menambah form pengadaan kedalam draft. Silahkan melakukan review kembali');
                } else {
                    return back()->with('success', 'Berhasil update form pengadaan');
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal menyimpan tiket "' . $ex->getMessage() . '"');
        }
    }

    public function startAuthorization($ticket)
    {
        $emailflag = true;
        $emailmessage = "";

        try {
            DB::beginTransaction();
            $validate = $this->validateticket($ticket);
            if ($validate['error']) {
                return redirect('/ticketing/' . $ticket->code)->with('error', implode("\r\n", $validate['messages']));
            }
            // ambil tipe pengadaan barang atau jasa
            if ($ticket->item_type == 0) {
                $code_type = 'P01';
            } else {
                $code_type = 'P02';
            }
            // ambil kode inisial salespoint
            $code_salespoint_initial = strtoupper($ticket->salespoint->initial);

            // ambil jumlah urutan ticketing d terkait dalam bulan dan tahun ini
            $armada_ticket_count = ArmadaTicket::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
                ->withTrashed()
                ->count();

            $security_ticket_count = SecurityTicket::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
                ->withTrashed()
                ->count();

            $barang_ticket_count = Ticket::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
                ->where('status', '>', 0)
                ->withTrashed()
                ->count();

            $code_total_count = $armada_ticket_count + $security_ticket_count + $barang_ticket_count;
            do {
                $code = $code_type . "-" . $code_salespoint_initial . "-" . now()->translatedFormat('dmy') . str_repeat("0", 4 - strlen($code_total_count + 1)) . ($code_total_count + 1);
                $code_total_count++;
                $checkbarang = Ticket::where('code', $code)->first();
                $checkarmada = ArmadaTicket::where('code', $code)->first();
                $checksecurity = SecurityTicket::where('code', $code)->first();
                ($checkbarang != null || $checkarmada != null) ? $flag = false : $flag = true;
            } while (!$flag);
            $old_code               = $ticket->code;
            $ticket->code           = $code;
            $ticket->created_at     = Carbon::now()->translatedFormat('Y-m-d H:i:s');
            $ticket->created_by     = Auth::user()->id;
            $ticket->status = 1;
            // cari oper semua path kode lama ke kode baru
            $oldpath = '/attachments/ticketing/barangjasa/' . $old_code;
            $newpath = '/attachments/ticketing/barangjasa/' . $code;
            if ($ticket->ba_vendor_filepath != null) {
                $ticket->ba_vendor_filepath = str_replace($oldpath, $newpath, $ticket->ba_vendor_filepath);
                $ticket->save();
            }

            $ticket_item_attachments = TicketItemAttachment::where('path', 'LIKE', $oldpath . '%')->get();
            foreach ($ticket_item_attachments as $attachment) {
                $attachment->path = str_replace($oldpath, $newpath, $attachment->path);
                $attachment->save();
            }

            $ticket_file_item_requirements = TicketItemFileRequirement::where('path', 'LIKE', $oldpath . '%')->get();
            foreach ($ticket_file_item_requirements as $requirement) {
                $requirement->path = str_replace($oldpath, $newpath, $requirement->path);
                $requirement->save();
            }
            // end cari oper semua path kode
            $oldpath = 'storage' . $oldpath;
            $newpath = 'storage' . $newpath;

            if (is_dir($oldpath)) {
                Storage::disk('public')->deleteDirectory(str_replace('storage', '', $newpath));
                rename($oldpath, $newpath);
            }
            $ticket->save();

            // tambahkan info budget id ke dalam tiket
            if ($ticket->budget_type == 0) {
                if ($ticket->item_type == 0 || $ticket->item_type == 1) {
                    // barang , jasa
                    $budget = BudgetUpload::where('salespoint_id', $ticket->salespoint_id)->where('status', 1)->where('type', 'inventory')
                        ->where('year', '=', Carbon::now()->year)
                        ->first();
                } else if ($ticket->item_type == 2) {
                    // maintenance
                    $budget = BudgetUpload::where('salespoint_id', $ticket->salespoint_id)->where('status', 1)->where('type', 'assumption')
                        ->where('year', '=', Carbon::now()->year)
                        ->first();
                } else {
                    $budget = BudgetUpload::where('salespoint_id', $ticket->salespoint_id)->where('status', 1)->where('type', 'ho')
                        ->where('division', $ticket->division)
                        ->where('year', '=', Carbon::now()->year)
                        ->first();
                }
                $ticket->budget_upload_id = $budget->id;
                $ticket->save();
            }

            // Jika item IT tambahkan otorisasi Form FRI kedalam list otorisasi tiket
            if ($ticket->is_it == true) {
                $level = $ticket->ticket_authorization->sortByDesc('level')->first()->level + 1;
                foreach ($ticket->fri_forms->first()->authorizations as $authorization) {
                    $ticket->refresh();
                    $check_if_author_exist = $ticket->ticket_authorization->where('employee_id', $authorization->employee_id)->first();
                    if (!$check_if_author_exist) {
                        $newAuthorization                     = new TicketAuthorization;
                        $newAuthorization->ticket_id          = $ticket->id;
                        $newAuthorization->employee_id        = $authorization->employee_id;
                        $newAuthorization->employee_name      = $authorization->employee_name;
                        $newAuthorization->as                 = $authorization->as;
                        $newAuthorization->employee_position  = $authorization->employee_position;
                        $newAuthorization->level              = $level;
                        $newAuthorization->save();
                        $level++;
                    }
                }
            }
            // TICKET MONITOR_LOG
            $monitor = new TicketMonitoring;
            $monitor->ticket_id      = $ticket->id;
            $monitor->employee_id    = Auth::user()->id;
            $monitor->employee_name  = Auth::user()->name;
            $monitor->message        = 'Memulai Otorisasi Ticket';
            $monitor->save();

            // mail ke otorisasi pertama
            $mail_to = $ticket->current_authorization()->employee->email;
            $name_to = $ticket->current_authorization()->employee->name;
            $data = array(
                'original_emails' => [$mail_to],
                'transaction_type' => 'Pengadaan',
                'ticketing_type' => 'Barang Jasa',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $name_to,
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'ticketing_approval'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            DB::commit();
            // oper path

            return redirect('/ticketing')->with('success', 'Berhasil memulai otorisasi untuk form ' . $ticket->code . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/ticketing')->with('error', 'Gagal memulai otorisasi ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }

    public function validateticket($ticket)
    {
        $messages = array();
        $flag = true;
        if (empty($ticket->requirement_date)) {
            $flag = false;
            array_push($messages, 'Tanggal Pengadaan harus dipilih');
        }
        // validasi otorisasi
        if (empty($ticket->authorization_id)) {
            $flag = false;
            array_push($messages, 'Otorisasi belum dipilih');
        }
        if ($ticket->item_type === null) {
            $flag = false;
            array_push($messages, 'Jenis Item belum dipilih');
        }
        if ($ticket->request_type === null) {
            $flag = false;
            array_push($messages, 'Jenis Pengadaan belum dipilih');
        }
        if ($ticket->is_it === null) {
            $flag = false;
            array_push($messages, 'Jenis IT belum dipilih');
        }
        if ($ticket->budget_type === null) {
            $flag = false;
            array_push($messages, 'Jenis Budget belum dipilih');
        }
        if ($ticket->item_type === "ho") {
            if ($ticket->division === null) {
                $flag = false;
                array_push($messages, 'Divisi Harus dipilih untuk pengadaan HO');
            }
            if ($ticket->division === "Indirect") {
                if ($ticket->indirect_salespoint_id === null) {
                    $flag = false;
                    array_push($messages, 'Divisi Harus dipilih untuk pengadaan HO');
                }
            }
        }
        // validate setiap item harus punya file penawaran untuk Jenis Pengadaan (Baru, Repear Order, Replace Existing)
        if (in_array($ticket->request_type, [0, 1, 2])) {
            foreach ($ticket->ticket_item as $item) {
                $flaghavefile = false;
                foreach ($item->ticket_item_file_requirement as $file) {
                    $req = FileCompletement::find($file->file_completement_id);
                    if (strpos(strtolower($req->name), "penawaran") !== false) {
                        $flaghavefile = true;
                    }
                }
                if (!$flaghavefile) {
                    array_push($messages, "Item " . $item->name . " belum memiliki file penawaran, Minimal 1 file penawaran");
                    $flag = false;
                }
            }
        }
        // jika Jenis item / item_type = HO / 3. maka validasi divisi harus dipih dan jika divisi yang dipilih indirect, maka salespoint indirect harus dipilih juga
        if ($ticket->item_type == 3) {
            if ($ticket->division == null) {
                array_push($messages, "Divisi perlu dipilih untuk pengadaan dengan jenis item 'HO'");
                $flag = false;
            }
            if (strtolower($ticket->division) == strtolower("Indirect") && $ticket->indirect_salespoint_id == null) {
                array_push($messages, "Salespoint indirect belum dipilih untuk pilihan divisi indirect");
                $flag = false;
            }
        }

        if ($ticket->ticket_item->count() < 1) {
            array_push($messages, "Jumlah permintaan item minimal 1");
            $flag = false;
        }
        // maksimal 3 hanya untuk selain HO
        if ($ticket->item_type != 3) {
            if ($ticket->ticket_item->count() > 3) {
                array_push($messages, "Jumlah permintaan item maksimal 3");
                $flag = false;
            }
        }
        // jika vendor 1 butuh berita acara
        if ($ticket->ticket_vendor->count() == 1 && $ticket->ba_vendor_filepath == null) {
            array_push($messages, "Untuk pemilihan hanya satu vendor membutuhkan berita acara vendor");
            $flag = false;
        }
        // vendor gaboleh kosong
        if ($ticket->ticket_vendor->count() == 0) {
            array_push($messages, "Silahkan ajukan / pilih 2 vendor");
            $flag = false;
        }
        // alasan harus diisi
        if (empty($ticket->reason) || trim($ticket->reason) == "") {
            array_push($messages, 'Alasan pengadaan barang atau jasa harus diisi');
        }
        // mapping validasi dengan budget sebelum mulai otorisasi
        if ($ticket->budget_type == 0) {
            if ($ticket->item_type == 0 || $ticket->item_type == 1) {
                // barang / jasa / inventory
                $budget = BudgetUpload::where('salespoint_id', $ticket->salespoint_id)->where('status', 1)->where('type', 'inventory')
                    ->where('year', '=', Carbon::now()->year)->first();
            } else if ($ticket->item_type == 2) {
                // maintenance / assumption
                $budget = BudgetUpload::where('salespoint_id', $ticket->salespoint_id)->where('status', 1)->where('type', 'assumption')
                    ->where('year', '=', Carbon::now()->year)->first();
            } else {
                // ho budget
                $budget = BudgetUpload::where('salespoint_id', $ticket->salespoint_id)->where('status', 1)->where('type', 'ho')
                    ->where('division', $ticket->division)
                    ->where('year', '=', Carbon::now()->year)->first();
            }
            if ($budget == null) {
                $flag = false;
                array_push($messages, 'Budget belum tersedia. harap melakukan request budget terlebih dahulu');
            } else {
                // validasi stock ambil jumlah stock setiap item
                if ($ticket->item_type == 0 || $ticket->item_type == 1) {
                    // Barang Jasa
                    $ticket_items = TicketItem::join('budget_pricing', 'ticket_item.budget_pricing_id', '=', 'budget_pricing.id')
                        ->where('ticket_item.ticket_id', $ticket->id)
                        ->groupBy('ticket_item.budget_pricing_id')
                        ->groupBy('ticket_item.name')
                        ->groupBy('budget_pricing.code')
                        ->groupBy('budget_pricing.name')
                        ->select(DB::raw('sum(ticket_item.count) as total, ticket_item.budget_pricing_id, budget_pricing.code, ticket_item.name'))
                        ->get();
                } else if ($ticket->item_type == 2) {
                    $ticket_items = TicketItem::join('maintenance_budget', 'ticket_item.maintenance_budget_id', '=', 'maintenance_budget.id')
                        ->where('ticket_item.ticket_id', $ticket->id)
                        ->groupBy('ticket_item.maintenance_budget_id')
                        ->groupBy('ticket_item.name')
                        ->groupBy('maintenance_budget.code')
                        ->groupBy('maintenance_budget.name')
                        ->select(DB::raw('sum(ticket_item.count) as total, ticket_item.maintenance_budget_id, maintenance_budget.code, ticket_item.name'))
                        ->get();
                } else {
                    // HO Budget
                    $ticket_items = TicketItem::join('ho_budget', 'ticket_item.ho_budget_id', '=', 'ho_budget.id')
                        ->where('ticket_item.ticket_id', $ticket->id)
                        ->groupBy('ticket_item.ho_budget_id')
                        ->groupBy('ticket_item.name')
                        ->groupBy('ho_budget.code')
                        ->groupBy('ho_budget.name')
                        ->select(DB::raw('sum(ticket_item.count) as total, ticket_item.ho_budget_id, ho_budget.code, ticket_item.name'))
                        ->get();
                }
                foreach ($ticket_items as $item) {
                    $code  = $item->code;
                    $name  = $item->name;
                    $selectedbudget = $budget->budget_detail->where('code', $code)->first();
                    if ($selectedbudget != null) {
                        if (in_array($ticket->item_type, [0, 1, 2])) {
                            // barang, jasa, Maintenance
                            $available_quota = $selectedbudget->qty - $selectedbudget->pending_quota - $selectedbudget->used_quota;
                        } else {
                            // HO
                            $month = now()->month;
                            $available_quota = $selectedbudget->getQty($month) - $selectedbudget->getPendingQuota($month) - $selectedbudget->getUsedQuota($month);
                            // $available_quota =
                        }
                        if ($available_quota < $item->total) {
                            $flag = false;
                            array_push($messages, "Jumlah permintaan item " . $name . " tidak tersedia 'jumlah permintaan = " . $item->total . "| stock = " . $available_quota . "'");
                        }
                    } else {
                        $flag = false;
                        array_push($messages, "Item " . $name . " tidak tersedia di budget");
                    }
                }

                // validasi harga per item apakah melebihi jumlah value di stock
                foreach ($ticket->ticket_item as $item) {
                    $name  = $item->name;
                    if ($ticket->item_type == 0 || $ticket->item_type == 1) {
                        $code = $item->budget_pricing->code;
                    } else if ($ticket->item_type == 2) {
                        $code = $item->maintenance_budget->code;
                    } else {
                        $code = $item->ho_budget->code;
                    }

                    $selectedbudget = $budget->budget_detail->where('code', $code)->first();
                    if ($selectedbudget != null) {
                        if (in_array($ticket->item_type, [0, 1, 2])) {
                            // barang, jasa, Maintenance
                            $max_price = $selectedbudget->value;
                        } else {
                            // HO
                            $month = now()->month;
                            $max_price = $selectedbudget->getValue($month);
                        }
                        // if ($max_price < $item->price) {
                        //     $flag = false;
                        //     array_push($messages, "Harga item " . $name . " melebih harga budget yang tersedia 'budget = " . $max_price . " ,request = " . $item->price . "'");
                        // }
                    } else {
                        $flag = false;
                        array_push($messages, "Item " . $name . " tidak tersedia di budget");
                    }
                }
            }
        }
        // jika item IT maka harus ada satu form FRI
        if ($ticket->is_it == true) {
            if ($ticket->fri_forms->count() < 1) {
                $flag = false;
                array_push($messages, 'Pengajuan item Jenis IT membutuhkan FRI (Form Request Infrastruktur)');
            } else {
                $fri_form = $ticket->fri_forms->first();
                if ($fri_form->date_request == null || $fri_form->date_request == "") {
                    $flag = false;
                    array_push($messages, 'Date Request belum diisi pada FRI (Form Request Infrastruktur)');
                }
                if ($fri_form->date_use == null || $fri_form->date_use == "") {
                    $flag = false;
                    array_push($messages, 'Date Use belum diisi pada FRI (Form Request Infrastruktur)');
                }
                if ($fri_form->work_location == null || $fri_form->work_location == "") {
                    $flag = false;
                    array_push($messages, 'Work Location belum diisi pada FRI (Form Request Infrastruktur)');
                }
                if ($fri_form->salespoint_name == null || $fri_form->salespoint_name == "") {
                    $flag = false;
                    array_push($messages, 'Area belum diisi pada FRI (Form Request Infrastruktur)');
                }
                if ($fri_form->username_position == null || $fri_form->username_position == "") {
                    $flag = false;
                    array_push($messages, 'User Name / Position belum diisi pada FRI (Form Request Infrastruktur)');
                }
                if ($fri_form->division_department == null || $fri_form->division_department == "") {
                    $flag = false;
                    array_push($messages, 'Div / Dept belum diisi pada FRI (Form Request Infrastruktur)');
                }
                if ($fri_form->contact_number == null || $fri_form->contact_number == "") {
                    $flag = false;
                    array_push($messages, 'Contact number belum diisi pada FRI (Form Request Infrastruktur)');
                }
                if ($fri_form->email_address == null || $fri_form->email_address == "") {
                    $flag = false;
                    array_push($messages, 'Email Address belum diisi pada FRI (Form Request Infrastruktur)');
                }
                if ($fri_form->hardware_details == null || $fri_form->hardware_details == "") {
                    $flag = false;
                    array_push($messages, 'Date Request belum diisi pada FRI (Form Request Infrastruktur)');
                }
                if ($fri_form->application_details == null || $fri_form->application_details == "") {
                    $flag = false;
                    array_push($messages, 'Date Request belum diisi pada FRI (Form Request Infrastruktur)');
                }
                // jika belum ada matriks otorisasi form fri di master
                $fri_authorization = Authorization::where('form_type', 12)->first();
                if (!isset($fri_authorization)) {
                    $flag = false;
                    array_push($messages, 'Matriks Otorisasi FRI (Form Request Infrastruktur) belum tersedia. Anda dapat menguhubungi admin terkiat issue berikut');
                }
            }
        }
        $data = collect([
            "error" => !$flag,
            "messages" => array_unique($messages)
        ]);
        return $data;
    }

    public function approveTicket(Request $request, $return_data_type = 'view')
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($request->id);
            $updated_at = new Carbon($request->updated_at);
            if ($updated_at == $ticket->updated_at) {
                if ($ticket->is_cancel_end == 1) {
                    $authorization = $ticket->current_cancel_authorization();
                }
                else {
                    $authorization = $ticket->current_authorization();
                }
                if ($authorization->employee_id == Auth::user()->id) {
                    // set status jadi approve
                    $authorization->status = 1;
                    $authorization->save();

                    // TICKET MONITOR_LOG
                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id      = $ticket->id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    if ($ticket->is_cancel_end == 1) {
                        $monitor->message        = 'Approval Cancel ' . $ticket->reason;
                    }
                    else {
                        $monitor->message        = 'Approval Ticket Pengadaan';
                    }
                    $monitor->save();

                    // jika item IT approve di form fri juga
                    if ($ticket->fri_forms->count() > 0) {
                        $fri_form = $ticket->fri_forms->first();
                        $authors = $fri_form->authorizations->where('employee_id', Auth::user()->id);
                        foreach ($authors as $author) {
                            $author->status = 1;
                            $author->save();
                        }
                        $fri_form->refresh();
                        // kalau form fri dah full approval set formnya ke full approved
                        if ($fri_form->authorizations->where('status', 0)->first() == null) {
                            $fri_form->status = 1;
                            $fri_form->save();
                        }
                    }

                    // mail ke otorisasi selanjutnya
                    if ($authorization != null) {
                        $mail_to = $authorization->employee->email;
                        $name_to = $authorization->employee->name;
                        $data = array(
                            'original_emails' => [$mail_to],
                            'transaction_type' => 'Pengadaan',
                            'ticketing_type' => 'Barang Jasa',
                            'salespoint_name' => $ticket->salespoint->name,
                            'from' => Auth::user()->name,
                            'to' => $name_to,
                            'code' => $ticket->code,
                        );
                        if (config('app.env') == 'local') {
                            $mail_to = [config('mail.testing_email')];
                        }

                        try {
                            Mail::to($mail_to)->send(new NotificationMail($data, 'ticketing_approval'));
                        } catch (\Exception $ex) {
                            $emailflag = false;
                        }
                        if (!$emailflag) {
                            $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                        }
                    }
                    $this->checkTicketApproval($ticket->id);
                    $ticket->refresh();
                    
                    if ($ticket->is_cancel_end == 1) {
                        $current_authorization = $ticket->current_cancel_authorization();
                    }
                    else {
                        $current_authorization = $ticket->current_authorization();
                    }

                    if ($current_authorization) {
                        $returnmessage = 'Berhasil melakukan approve ticket Otorisasi selanjutnya oleh ' . $current_authorization->employee_name;
                    } 
                    elseif (str_contains($ticket->reason, 'End Kontrak PEST Control')) {
                        $returnmessage = 'Approval terkait ticketing dengan kode ticket ' . $ticket->code . ' sudah full approval. (Status tiket saat ini : ' . $ticket->status();
                        
                        if ($ticket->is_cancel_end == 1) {
                            $returnmessage .= ' & Status PO '.$ticket->po_reference_number .' Open)';
                        }
                        else {
                            $returnmessage .= ' & Status PO '.$ticket->po_reference_number .' Closed)';
                        }
                    }
                    else {
                        $returnmessage = 'Approval terkait ticketing dengan kode ticket ' . $ticket->code . ' sudah full approval. (Status tiket saat ini : ' . $ticket->status() . ')';
                    }


                    DB::commit();
                    if ($return_data_type == 'api') {
                        return response()->json([
                            "error" => false,
                            "message" => $returnmessage . $emailmessage
                        ]);
                    } else {
                        return back()->with('success', $returnmessage . $emailmessage);
                    }
                } else {
                    if ($return_data_type == 'api') {
                        return response()->json([
                            "error" => true,
                            "message" => 'ID otorisasi tidak sesuai. Silahkan coba kembali'
                        ]);
                    } else {
                        return back()->with('error', 'ID otorisasi tidak sesuai. Silahkan coba kembali');
                    }
                }
            } else {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Ticket sudah di approve sebelumnya' . $emailmessage
                    ]);
                } else {
                    return back()->with('error', 'Ticket sudah di approve sebelumnya' . $emailmessage);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan approve ticket (' . $ex->getMessage() . ') [' . $ex->getLine() . ']'
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan approve ticket (' . $ex->getMessage() . ') [' . $ex->getLine() . ']');
            }
        }
    }

    public function checkTicketApproval($ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);
            $flag = true;
            if ($ticket->is_cancel_end == 1) {
                foreach ($ticket->cancel_authorization as $cancelauthorization) {
                    if ($cancelauthorization->status != 1) {
                        $flag = false;
                        break;
                    }
                }
            }
            else {
                foreach ($ticket->ticket_authorization as $authorization) {
                    if ($authorization->status != 1) {
                        $flag = false;
                        break;
                    }
                }
            }
            
            if ($flag) {
                if ($ticket->custom_settings != null) {
                    $custom_settings = json_decode($ticket->custom_settings);

                    if (in_array('bidding', $custom_settings->steps)) {
                        $ticket->status = 2;
                    } else if (in_array('pr_manual', $custom_settings->steps)) {
                        $ticket->status = 3;
                    } else if (in_array('po_sap', $custom_settings->steps)) {
                        $ticket->status = 6;
                    } else if (in_array('received_file_upload', $custom_settings->steps)) {
                        $ticket->status = 6;
                    } else {
                        throw new \Exception("Terjadi kesalahan pada custom settings. Silahkan hubungi developer");
                    }
                } else {
                    if ($ticket->request_type == 3) {
                        // perpanjangan (ubah status ke pr / po sap / skip bidding dan pr manual)
                        $ticket->status = 6;
                    } else if ($ticket->request_type == 4) {
                        if (str_contains($ticket->reason, 'End Kontrak PEST Control')) {
                            if ($ticket->is_cancel_end == 1) {
                                // end kontrak (ubah status selesai)
                                $ticket->status = -2;
                                $po = Po::where('no_po_sap', $ticket->po_reference_number)->first();
                                $pomanual = PoManual::where('po_number', $ticket->po_reference_number)->first();
                                if ($po) {
                                    $po->status = 3;
                                    $po->save();
                                }
                                else if ($pomanual) {
                                    $pomanual->status = 3;
                                    $pomanual->save();
                                }
                            }
                            else {
                                // end kontrak (ubah status selesai)
                                $ticket->status = 7;    
                                // end kontrak (ubah status jadi closed)
                                $po = Po::where('no_po_sap', $ticket->po_reference_number)->first();
                                $pomanual = PoManual::where('po_number', $ticket->po_reference_number)->first();
                                if ($po) {
                                    $po->status = 4;
                                    $po->save();
                                }
                                else if ($pomanual) {
                                    $pomanual->status = 4;
                                    $pomanual->save();
                                }
                            }
                        }
                    } else {
                        // pengadaan baru / replace renewal (ubah status ke bidding)
                        $ticket->status = 2;
                    }
                }
                $ticket->save();
                $ticket->refresh();

                // Hapus approver kedua akhir form fri dari ticket (level 4,5)
                if ($ticket->fri_forms->count()) {
                    $take_count = $ticket->ticket_authorization->count() - 3;
                    foreach ($ticket->ticket_authorization->sortByDesc('level')->take($take_count) as $author) {
                            $author->delete();
                    }
                }

                // mail ke tim purchasing bidding ready
                if ($ticket->status == 2) {
                    $ticket->refresh();
                    $last_ticket_author = $ticket->ticket_authorization->sortByDesc('level')->first();
                    $region_purchasing_emails = EmailAdditional::where('type', $ticket->salespoint->region_type)
                        ->where('category', 'purchasing')->first()->emails ?? [];
                    $region_purchasing_emails = json_decode($region_purchasing_emails);
                    $national_purchasing_emails = EmailAdditional::where('type', 'national')->where('category', 'purchasing')->first()->emails ?? [];
                    $national_purchasing_emails = json_decode($national_purchasing_emails);

                    $mail_to = array_unique(array_merge($national_purchasing_emails, $region_purchasing_emails));
                    $ccs = $ticket->additional_emails() ?? [];
                    $data = array(
                        'original_emails' => $mail_to,
                        'original_ccs' => $ccs,
                        'transaction_type' => 'Pengadaan',
                        'ticketing_type' => 'Barang Jasa',
                        'salespoint_name' => $ticket->salespoint->name,
                        'from' => $last_ticket_author->employee_name,
                        'to' => 'Purchasing Team',
                        'code' => $ticket->code,
                    );
                    if (config('app.env') == 'local') {
                        $mail_to = [config('mail.testing_email')];
                        $ccs = [];
                    }
                    $emailflag = true;
                    try {
                        Mail::to($mail_to)->cc($ccs)
                            ->send(new NotificationMail($data, 'ticketing_approved'));
                    } catch (\Exception $ex) {
                        $emailflag = false;
                    }
                    $emailmessage = "";
                    if (!$emailflag) {
                        $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                    }
                }
                $ticket->refresh();

                // TICKET MONITOR_LOG
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Approval ticket selesai';
                $monitor->save();
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Approval checker error please contact admin ' . $ex->getMessage());
        }
    }

    public function rejectTicket(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($request->id);
            $updated_at = new Carbon($request->updated_at);
            if ($updated_at == $ticket->updated_at) {
                $authorization = $ticket->current_authorization();
                if ($authorization->employee_id == Auth::user()->id) {
                    // balikin ke draft
                    $ticket->status = 0;
                    $ticket->terminated_by = Auth::user()->id;
                    $ticket->termination_reason = $request->reason;
                    $ticket->save();

                    // TICKET MONITOR_LOG
                    $monitor                 = new TicketMonitoring;
                    $monitor->ticket_id      = $ticket->id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Melakukan reject ticket pengadaan';
                    $monitor->save();

                    // ambil semua otorisasi
                    $employee_ids = $ticket->ticket_authorization->pluck('employee_id');
                    $employee_emails = Employee::whereIn('id', $employee_ids)->get()->pluck('email');
                    $mail_to = $employee_emails->toArray();
                    $data = array(
                        'original_emails' => $mail_to,
                        'transaction_type' => 'Pengadaan',
                        'ticketing_type' => 'Barang Jasa',
                        'salespoint_name' => $ticket->salespoint->name,
                        'from' => Auth::user()->name,
                        'to' => 'Bapak/Ibu',
                        'code' => $ticket->code,
                    );
                    if (config('app.env') == 'local') {
                        $mail_to = [config('mail.testing_email')];
                    }
                    $emailflag = true;
                    try {
                        Mail::to($mail_to)->send(new NotificationMail($data, 'ticketing_reject'));
                    } catch (\Exception $ex) {
                        $emailflag = false;
                    }
                    $emailmessage = "";
                    if (!$emailflag) {
                        $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                    }
                    DB::commit();
                    if ($return_data_type == 'api') {
                        return response()->json([
                            "error" => false,
                            "message" => 'Berhasil membatalkan ticket' . $emailmessage
                        ]);
                    } else {
                        return redirect('/ticketing')->with('success', 'Berhasil membatalkan ticket' . $emailmessage);
                    }
                } else {
                    if ($return_data_type == 'api') {
                        return response()->json([
                            "error" => true,
                            "message" => 'ID otorisasi tidak sesuai. Silahkan coba kembali'
                        ]);
                    } else {
                        return back()->with('error', 'ID otorisasi tidak sesuai. Silahkan coba kembali');
                    }
                }
            } else {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Ticket sudah dibatalkan sebelumnya'
                    ]);
                } else {
                    return redirect('/ticketing')->with('error', 'Ticket sudah dibatalkan sebelumnya');
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal membatalkan ticket ' . $ex->getMessage()
                ]);
            } else {
                return back()->with('error', 'Gagal membatalkan ticket ' . $ex->getMessage());
            }
        }
    }

    public function uploadFileRevision(Request $request)
    {
        try {
            DB::beginTransaction();
            if ($request->type == 'file') {
                // file
                $ticketitem = TicketItemFileRequirement::findOrFail($request->id);
            } else if ($request->type == 'attachment') {
                // attachment
                $ticketitem = TicketItemAttachment::findOrFail($request->id);
            } else {
                // vendor
                $ticket = Ticket::findOrFail($request->id);
            }
            if ($request->type == 'vendor') {
                $ticket->ba_status      = 0;
                $ticket->ba_revised_by  = Auth::user()->id;
                $ticket->save();

                // TICKET MONITOR_LOG
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Upload Revisi File Kelengkapan ticket pengadaan';
                $monitor->save();

                $file = explode('base64,', $request->file)[1];
                $newfilename = pathinfo($ticket->ba_vendor_filename, PATHINFO_FILENAME) . '.' . pathinfo($request->filename, PATHINFO_EXTENSION);
                $path = str_replace($ticket->ba_vendor_filename, $newfilename, $ticket->ba_vendor_filepath);
                $ticket->ba_vendor_filename = $newfilename;
                $ticket->ba_vendor_filepath = $path;
                $ticket->save();
                Storage::disk('public')->put($ticket->ba_vendor_filepath, base64_decode($file));
            } else {
                $ticketitem->status = 0;
                $ticketitem->revised_by = Auth::user()->id;
                $ticketitem->save();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticketitem->ticket_item->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Upload Revisi File Kelengkapan ticket pengadaan';
                $monitor->save();

                $file = explode('base64,', $request->file)[1];
                $newfilename = pathinfo($ticketitem->name, PATHINFO_FILENAME) . '.' . pathinfo($request->filename, PATHINFO_EXTENSION);
                $path = str_replace($ticketitem->name, $newfilename, $ticketitem->path);
                $ticketitem->name = $newfilename;
                $ticketitem->path = $path;
                $ticketitem->save();
                Storage::disk('public')->put($ticketitem->path, base64_decode($file));
            }
            DB::commit();
            return back()->with('success', 'Berhasil melakukan revisi upload file');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan revisi upload file');
        }
    }

    public function revisionConfirmationFile(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket_item = TicketItem::findOrFail($request->ticket_item_id);
            $ticket = $ticket_item->ticket;
            if ($ticket->custom_settings != null) {
                for ($i = 0; $i < count($request->filename); $i++) {
                    $original_filename              = $request->file('file')[$i]->getClientOriginalName();
                    $newAttachment                  = new TicketItemAttachment;
                    $newAttachment->ticket_item_id  = $ticket_item->id;
                    $salespointname                 = str_replace(' ', '_', $ticket->salespoint->name);
                    $filename                       = str_replace(' ', '_', trim($request->filename[$i]));
                    $ext                            = pathinfo($original_filename, PATHINFO_EXTENSION);
                    $newAttachment->name            = $filename . '_' . $salespointname . '.' . $ext;
                    $path                           = "/attachments/ticketing/barangjasa/" . $ticket->code . '/item' . $ticket_item->id . '/' . $newAttachment->name;
                    $file                           = pathinfo($path);
                    $path                           = $request->file('file')[$i]->storeAs($file['dirname'], $file['basename'], 'public');
                    $newAttachment->path            = $path;
                    $newAttachment->save();
                }
            } else {
                $lpbfile                        = $request->file('lpb');
                $invoicefile                    = $request->file('invoice');

                if ($lpbfile && $invoicefile) {
                    $lpbfile                        = $request->file()['lpb'];
                    $invoicefile                    = $request->file()['invoice'];
                    $lpb_ext                        = pathinfo($lpbfile->getClientOriginalName(), PATHINFO_EXTENSION);
                    $invoice_ext                    = pathinfo($invoicefile->getClientOriginalName(), PATHINFO_EXTENSION);
                    $salespoint                     = $ticket_item->ticket->salespoint;
                    $salespointname                 = str_replace(' ', '_', $salespoint->name);
                    $code                           = $ticket_item->ticket->code;

                    $path                           = 'attachments/ticketing/barangjasa/' . $code . '/item' . $ticket_item->id . '/LPB_' . $salespointname . '.' . $lpb_ext;
                    $info                           = pathinfo($path);
                    $lpb_path                       = $lpbfile->storeAs($info['dirname'], $info['basename'], 'public');

                    $path                           = 'attachments/ticketing/barangjasa/' . $code . '/item' . $ticket_item->id . '/INVOICE_' . $salespointname . '.' . $invoice_ext;
                    $info                           = pathinfo($path);
                    $invoice_path                   = $invoicefile->storeAs($info['dirname'], $info['basename'], 'public');

                    $ticket_item->lpb_filepath      = $lpb_path;
                    $ticket_item->invoice_filepath  = $invoice_path;
                } elseif ($lpbfile) {
                    $lpbfile                        = $request->file()['lpb'];
                    $lpb_ext                        = pathinfo($lpbfile->getClientOriginalName(), PATHINFO_EXTENSION);
                    $salespoint                     = $ticket_item->ticket->salespoint;
                    $salespointname                 = str_replace(' ', '_', $salespoint->name);
                    $code                           = $ticket_item->ticket->code;

                    $path                           = 'attachments/ticketing/barangjasa/' . $code . '/item' . $ticket_item->id . '/LPB_' . $salespointname . '.' . $lpb_ext;
                    $info                           = pathinfo($path);
                    $lpb_path                       = $lpbfile->storeAs($info['dirname'], $info['basename'], 'public');
                    $ticket_item->lpb_filepath      = $lpb_path;
                } elseif ($invoicefile) {
                    $invoicefile                    = $request->file()['invoice'];
                    $invoice_ext                    = pathinfo($invoicefile->getClientOriginalName(), PATHINFO_EXTENSION);
                    $salespoint                     = $ticket_item->ticket->salespoint;
                    $salespointname                 = str_replace(' ', '_', $salespoint->name);
                    $code                           = $ticket_item->ticket->code;

                    $path                           = 'attachments/ticketing/barangjasa/' . $code . '/item' . $ticket_item->id . '/INVOICE_' . $salespointname . '.' . $invoice_ext;
                    $info                           = pathinfo($path);
                    $invoice_path                   = $invoicefile->storeAs($info['dirname'], $info['basename'], 'public');
                    $ticket_item->invoice_filepath  = $invoice_path;
                }
            }
            $ticket_item->save();

            DB::commit();

            return back()->with('success', 'Berhasil Revisi Dokumen');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal melakukan Revisi Dokumen' . $ex->getMessage());
        }
    }

    public function uploadMissingFile(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket_item = TicketItem::findOrFail($request->id);

            $missingfile                         = $request->file()['file_kekurangan_berkas'];
            $missingfile_ext                     = pathinfo($missingfile->getClientOriginalName(), PATHINFO_EXTENSION);
            $salespoint                          = $ticket_item->ticket->salespoint;
            $salespointname                      = str_replace(' ', '_', $salespoint->name);
            $code                                = $ticket_item->ticket->code;

            $path                                = 'attachments/ticketing/barangjasa/' . $code . '/File_kekurangan_berkas_' . $salespointname . '.' . $missingfile_ext;
            $info                                = pathinfo($path);
            $missingfile_path                    = $missingfile->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket_item->file_missing_name_file = $request->name_file;
            $ticket_item->file_missing_filepath  = $missingfile_path;
            $ticket_item->file_missing_reason    = $request->reason;
            $ticket_item->file_missing_status    = 0;

            $ticket_item->save();

            DB::commit();

            return back()->with('success', 'Berhasil Upload Kekurangan Berkas');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload Kekurangan Berkas' . $ex->getMessage());
        }
    }

    public function revisionMissingFile(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket_item = TicketItem::findOrFail($request->id);

            $revisionmissingfile                  = $request->file()['revision_missing_file'];
            $revisionmissingfile_ext              = pathinfo($revisionmissingfile->getClientOriginalName(), PATHINFO_EXTENSION);
            $salespoint                           = $ticket_item->ticket->salespoint;
            $salespointname                       = str_replace(' ', '_', $salespoint->name);
            $code                                 = $ticket_item->ticket->code;

            $path                                 = 'attachments/ticketing/barangjasa/' . $code . '/File_kekurangan_berkas_' .  $salespointname . '.' . $revisionmissingfile_ext;
            $info                                 = pathinfo($path);
            $revisionmissingfile_path             = $revisionmissingfile->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket_item->file_missing_filepath   = $revisionmissingfile_path;
            $ticket_item->file_missing_status     = 0;
            $ticket_item->file_missing_revised_by = Auth::user()->id;

            $ticket_item->save();

            DB::commit();

            return back()->with('success', 'Berhasil Revisi Kekurangan Berkas');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Revisi Kekurangan Berkas' . $ex->getMessage());
        }
    }

    public function uploadConfirmationFile(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket_item = TicketItem::findOrFail($request->ticket_item_id);
            $ticket = $ticket_item->ticket;
            if ($ticket->custom_settings != null) {
                for ($i = 0; $i < count($request->filename); $i++) {
                    $original_filename              = $request->file('file')[$i]->getClientOriginalName();
                    $newAttachment                  = new TicketItemAttachment;
                    $newAttachment->ticket_item_id  = $ticket_item->id;
                    $salespointname                 = str_replace(' ', '_', $ticket->salespoint->name);
                    $filename                       = str_replace(' ', '_', trim($request->filename[$i]));
                    $ext                            = pathinfo($original_filename, PATHINFO_EXTENSION);
                    $newAttachment->name            = $filename . '_' . $salespointname . '.' . $ext;
                    $path                           = "/attachments/ticketing/barangjasa/" . $ticket->code . '/item' . $ticket_item->id . '/' . $newAttachment->name;
                    $file                           = pathinfo($path);
                    $path                           = $request->file('file')[$i]->storeAs($file['dirname'], $file['basename'], 'public');
                    $newAttachment->path            = $path;
                    $newAttachment->save();

                    $ticket_item->isFinished        = true;
                    $ticket_item->confirmed_by      = Auth::user()->id;
                    $ticket_item->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id      = $ticket_item->ticket->id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Upload File LPB dan Invoice untuk item ' . $ticket_item->name;
                    $monitor->save();

                    $ticket = $ticket_item->ticket;
                    $isTicketFinished = $this->isTicketFinished($ticket->id);
                    if ($isTicketFinished) {
                        $ticket->status = 7;
                        $ticket->finished_date = now()->format('Y-m-d');
                        $ticket->save();
                    }
                    DB::commit();
                    if ($ticket->status == 7 && $ticket->item_type == 4) {
                        return back()->with('success', 'Pengadaan disposal selesai');
                    } elseif ($ticket->status == 7) {
                        return back()->with('success', 'Berhasil konfirmasi barang, Pengadaan selesai');
                    } else {
                        return back()->with('success', 'Berhasil konfirmasi barang');
                    }
                }
            } else {
                $lpbfile                        = $request->file('lpb');
                $invoicefile                    = $request->file('invoice');

                if ($lpbfile && $invoicefile) {
                    $lpbfile                        = $request->file()['lpb'];
                    $invoicefile                    = $request->file()['invoice'];
                    $lpb_ext                        = pathinfo($lpbfile->getClientOriginalName(), PATHINFO_EXTENSION);
                    $invoice_ext                    = pathinfo($invoicefile->getClientOriginalName(), PATHINFO_EXTENSION);
                    $salespoint                     = $ticket_item->ticket->salespoint;
                    $salespointname                 = str_replace(' ', '_', $salespoint->name);
                    $code                           = $ticket_item->ticket->code;

                    $path                           = 'attachments/ticketing/barangjasa/' . $code . '/item' . $ticket_item->id . '/LPB_' . $salespointname . '.' . $lpb_ext;
                    $info                           = pathinfo($path);
                    $lpb_path                       = $lpbfile->storeAs($info['dirname'], $info['basename'], 'public');

                    $path                           = 'attachments/ticketing/barangjasa/' . $code . '/item' . $ticket_item->id . '/INVOICE_' . $salespointname . '.' . $invoice_ext;
                    $info                           = pathinfo($path);
                    $invoice_path                   = $invoicefile->storeAs($info['dirname'], $info['basename'], 'public');

                    $ticket_item->lpb_filepath      = $lpb_path;
                    $ticket_item->invoice_filepath  = $invoice_path;

                    $ticket_item->isFinished        = true;
                    $ticket_item->confirmed_by      = Auth::user()->id;
                    $ticket_item->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id      = $ticket_item->ticket->id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Upload File LPB dan Invoice untuk item ' . $ticket_item->name;
                    $monitor->save();

                    $ticket = $ticket_item->ticket;
                    $isTicketFinished = $this->isTicketFinished($ticket->id);
                    if ($isTicketFinished) {
                        $ticket->status = 7;
                        $ticket->finished_date = now()->format('Y-m-d');
                        $ticket->save();
                    }
                    DB::commit();
                    if ($ticket->status == 7 && $ticket->item_type == 4) {
                        return back()->with('success', 'Pengadaan disposal selesai');
                    } elseif ($ticket->status == 7) {
                        return back()->with('success', 'Berhasil konfirmasi barang, Pengadaan selesai');
                    } else {
                        return back()->with('success', 'Berhasil konfirmasi barang');
                    }
                } elseif ($lpbfile && $ticket_item->invoice_filepath == '') {
                    $lpbfile                        = $request->file()['lpb'];
                    $lpb_ext                        = pathinfo($lpbfile->getClientOriginalName(), PATHINFO_EXTENSION);
                    $salespoint                     = $ticket_item->ticket->salespoint;
                    $salespointname                 = str_replace(' ', '_', $salespoint->name);
                    $code                           = $ticket_item->ticket->code;

                    $path                           = 'attachments/ticketing/barangjasa/' . $code . '/item' . $ticket_item->id . '/LPB_' . $salespointname . '.' . $lpb_ext;
                    $info                           = pathinfo($path);
                    $lpb_path                       = $lpbfile->storeAs($info['dirname'], $info['basename'], 'public');
                    $ticket_item->lpb_filepath      = $lpb_path;

                    $ticket_item->confirmed_by      = Auth::user()->id;
                    $ticket_item->save();

                    DB::commit();
                    return back()->with('success', 'Berhasil Upload File LPB');
                } elseif ($lpbfile && $ticket_item->invoice_filepath != '') {
                    $lpbfile                        = $request->file()['lpb'];
                    $lpb_ext                        = pathinfo($lpbfile->getClientOriginalName(), PATHINFO_EXTENSION);
                    $salespoint                     = $ticket_item->ticket->salespoint;
                    $salespointname                 = str_replace(' ', '_', $salespoint->name);
                    $code                           = $ticket_item->ticket->code;

                    $path                           = 'attachments/ticketing/barangjasa/' . $code . '/item' . $ticket_item->id . '/LPB_' . $salespointname . '.' . $lpb_ext;
                    $info                           = pathinfo($path);
                    $lpb_path                       = $lpbfile->storeAs($info['dirname'], $info['basename'], 'public');
                    $ticket_item->lpb_filepath      = $lpb_path;

                    $ticket_item->isFinished        = true;
                    $ticket_item->confirmed_by      = Auth::user()->id;
                    $ticket_item->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id      = $ticket_item->ticket->id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Upload File LPB dan Invoice untuk item ' . $ticket_item->name;
                    $monitor->save();

                    $ticket = $ticket_item->ticket;
                    $isTicketFinished = $this->isTicketFinished($ticket->id);
                    if ($isTicketFinished) {
                        $ticket->status = 7;
                        $ticket->finished_date = now()->format('Y-m-d');
                        $ticket->save();
                    }
                    DB::commit();
                    if ($ticket->status == 7 && $ticket->item_type == 4) {
                        return back()->with('success', 'Pengadaan disposal selesai');
                    } elseif ($ticket->status == 7) {
                        return back()->with('success', 'Berhasil konfirmasi barang, Pengadaan selesai');
                    } else {
                        return back()->with('success', 'Berhasil konfirmasi barang');
                    }
                } elseif ($invoicefile && $ticket_item->lpb_filepath == '') {
                    $invoicefile                    = $request->file()['invoice'];
                    $invoice_ext                    = pathinfo($invoicefile->getClientOriginalName(), PATHINFO_EXTENSION);
                    $salespoint                     = $ticket_item->ticket->salespoint;
                    $salespointname                 = str_replace(' ', '_', $salespoint->name);
                    $code                           = $ticket_item->ticket->code;

                    $path                           = 'attachments/ticketing/barangjasa/' . $code . '/item' . $ticket_item->id . '/INVOICE_' . $salespointname . '.' . $invoice_ext;
                    $info                           = pathinfo($path);
                    $invoice_path                   = $invoicefile->storeAs($info['dirname'], $info['basename'], 'public');
                    $ticket_item->invoice_filepath  = $invoice_path;

                    $ticket_item->confirmed_by      = Auth::user()->id;
                    $ticket_item->save();

                    DB::commit();

                    return back()->with('success', 'Berhasil Upload File Invoice');
                } elseif ($invoicefile && $ticket_item->lpb_filepath != '') {
                    $invoicefile                    = $request->file()['invoice'];
                    $invoice_ext                    = pathinfo($invoicefile->getClientOriginalName(), PATHINFO_EXTENSION);
                    $salespoint                     = $ticket_item->ticket->salespoint;
                    $salespointname                 = str_replace(' ', '_', $salespoint->name);
                    $code                           = $ticket_item->ticket->code;

                    $path                           = 'attachments/ticketing/barangjasa/' . $code . '/item' . $ticket_item->id . '/INVOICE_' . $salespointname . '.' . $invoice_ext;
                    $info                           = pathinfo($path);
                    $invoice_path                   = $invoicefile->storeAs($info['dirname'], $info['basename'], 'public');
                    $ticket_item->invoice_filepath  = $invoice_path;

                    $ticket_item->isFinished        = true;
                    $ticket_item->confirmed_by      = Auth::user()->id;
                    $ticket_item->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id      = $ticket_item->ticket->id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Upload File LPB dan Invoice untuk item ' . $ticket_item->name;
                    $monitor->save();

                    $ticket = $ticket_item->ticket;
                    $isTicketFinished = $this->isTicketFinished($ticket->id);
                    if ($isTicketFinished) {
                        $ticket->status = 7;
                        $ticket->finished_date = now()->format('Y-m-d');
                        $ticket->save();
                    }
                    DB::commit();
                    if ($ticket->status == 7 && $ticket->item_type == 4) {
                        return back()->with('success', 'Pengadaan disposal selesai');
                    } elseif ($ticket->status == 7) {
                        return back()->with('success', 'Berhasil konfirmasi barang, Pengadaan selesai');
                    } else {
                        return back()->with('success', 'Berhasil konfirmasi barang');
                    }
                }
            }
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal melakukan konfirmasi penerimaan barang ' . $ex->getMessage());
        }
    }

    public function uploadFileLegal(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);

            $perjanjian_file            = $request->file()['file_perjanjian_legal'];
            $tor_file                   = $request->file()['file_tor_legal'];
            $sph_file                   = $request->file()['file_sph_legal'];

            $perjanjian_ext             = pathinfo($perjanjian_file->getClientOriginalName(), PATHINFO_EXTENSION);
            $tor_ext                    = pathinfo($tor_file->getClientOriginalName(), PATHINFO_EXTENSION);
            $sph_ext                    = pathinfo($sph_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $ticket->code;

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/File_Perjanjian_COP_' . $salespointname . '.' . $perjanjian_ext;
            $info                       = pathinfo($path);
            $perjanjian_path            = $perjanjian_file->storeAs($info['dirname'], $info['basename'], 'public');

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/File_TOR_COP_' . $salespointname . '.' . $tor_ext;
            $info                       = pathinfo($path);
            $tor_path                   = $tor_file->storeAs($info['dirname'], $info['basename'], 'public');

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/File_SPH_COP_' . $salespointname . '.' . $sph_ext;
            $info                       = pathinfo($path);
            $sph_path                   = $sph_file->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket->agreement_filepath = $perjanjian_path;
            $ticket->tor_filepath       = $tor_path;
            $ticket->sph_filepath       = $sph_path;

            $ticket->is_over_plafon     = $request->over_platform;
            $ticket->agreement_filepath_status = 0;
            $ticket->sph_filepath_status = 0;
            $ticket->tor_filepath_status = 0;

            $ticket->save();

            DB::commit();

            // mail ke tim purchasing file sudah di upload
            $ticket->refresh();
            $legal_uploader =  Auth::user()->name;
            $region_purchasing_emails = EmailAdditional::where('type', $ticket->salespoint->region_type)->where('category', 'purchasing')->first()->emails ?? [];
            $region_purchasing_emails = json_decode($region_purchasing_emails);
            $national_purchasing_emails = EmailAdditional::where('type', 'national')->where('category', 'purchasing')->first()->emails ?? [];
            $national_purchasing_emails = json_decode($national_purchasing_emails);

            $mail_to = array_unique(array_merge($national_purchasing_emails, $region_purchasing_emails));
            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'Pengadaan',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $legal_uploader,
                'to' => 'Purchasing Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'legal_upload_file'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Upload File Legal Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload File Legal ' . $ex->getMessage());
        }
    }

    public function reUploadAgreementFileCop(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);

            $perjanjian_file            = $request->file()['upload_ulang_file_agreement_cop'];
            $perjanjian_ext             = pathinfo($perjanjian_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $ticket->code;

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/File_Perjanjian_COP_' . $salespointname . '.' . $perjanjian_ext;
            $info                       = pathinfo($path);
            $perjanjian_path            = $perjanjian_file->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket->agreement_filepath = $perjanjian_path;
            $ticket->agreement_filepath_status = 0;

            $ticket->save();

            // mail ke tim purchasing file sudah di upload
            $ticket->refresh();
            $user =  Auth::user()->name;
            $region_purchasing_emails = EmailAdditional::where('type', $ticket->salespoint->region_type)->where('category', 'purchasing')->first()->emails ?? [];
            $region_purchasing_emails = json_decode($region_purchasing_emails);
            $national_purchasing_emails = EmailAdditional::where('type', 'national')->where('category', 'purchasing')->first()->emails ?? [];
            $national_purchasing_emails = json_decode($national_purchasing_emails);

            $mail_to = array_unique(array_merge($national_purchasing_emails, $region_purchasing_emails));
            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'Upload Ulang File Perjanjian Tim Legal COP',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $user,
                'to' => 'Purchasing Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'reupload_agreement_legal'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Upload Ulang File Perjanjian (Legal) Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload Ulang File Perjanjian (Legal) ' . $ex->getMessage());
        }
    }

    public function reUploadTorFileCop(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);

            $tor_file                   = $request->file()['upload_ulang_file_tor_cop'];
            $tor_ext                    = pathinfo($tor_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $ticket->code;

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/File_TOR_COP_' . $salespointname . '.' . $tor_ext;
            $info                       = pathinfo($path);
            $tor_path                   = $tor_file->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket->tor_filepath       = $tor_path;
            $ticket->tor_filepath_status = 0;
            $ticket->save();

            // mail ke tim purchasing file sudah di upload
            $ticket->refresh();
            $user =  Auth::user()->name;
            $region_purchasing_emails = EmailAdditional::where('type', $ticket->salespoint->region_type)->where('category', 'purchasing')->first()->emails ?? [];
            $region_purchasing_emails = json_decode($region_purchasing_emails);
            $national_purchasing_emails = EmailAdditional::where('type', 'national')->where('category', 'purchasing')->first()->emails ?? [];
            $national_purchasing_emails = json_decode($national_purchasing_emails);

            $mail_to = array_unique(array_merge($national_purchasing_emails, $region_purchasing_emails));
            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'Upload Ulang File TOR Tim Legal COP',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $user,
                'to' => 'Purchasing Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'reupload_tor_legal'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Upload Ulang File File TOR Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload Ulang File File TOR ' . $ex->getMessage());
        }
    }

    public function reUploadSphFileCop(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);

            $sph_file                   = $request->file()['upload_ulang_file_sph_cop'];
            $sph_ext                    = pathinfo($sph_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $ticket->code;

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/File_SPH_COP_' . $salespointname . '.' . $sph_ext;
            $info                       = pathinfo($path);
            $sph_path                   = $sph_file->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket->sph_filepath       = $sph_path;
            $ticket->sph_filepath_status = 0;

            $ticket->save();

            // mail ke tim purchasing file sudah di upload
            $ticket->refresh();
            $user =  Auth::user()->name;
            $region_purchasing_emails = EmailAdditional::where('type', $ticket->salespoint->region_type)->where('category', 'purchasing')->first()->emails ?? [];
            $region_purchasing_emails = json_decode($region_purchasing_emails);
            $national_purchasing_emails = EmailAdditional::where('type', 'national')->where('category', 'purchasing')->first()->emails ?? [];
            $national_purchasing_emails = json_decode($national_purchasing_emails);

            $mail_to = array_unique(array_merge($national_purchasing_emails, $region_purchasing_emails));
            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'Upload Ulang File SPH Tim Legal COP',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $user,
                'to' => 'Purchasing Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'reupload_sph_legal'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Upload Ulang File SPH Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload Ulang File SPH Legal ' . $ex->getMessage());
        }
    }

    public function reUploadUserAgreementFileCop(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);

            $perjanjian_file            = $request->file()['upload_ulang_file_user_agreement_cop'];
            $perjanjian_ext             = pathinfo($perjanjian_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $ticket->code;

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/File_Perjanjian_User_COP_' . $salespointname . '.' . $perjanjian_ext;
            $info                       = pathinfo($path);
            $perjanjian_path            = $perjanjian_file->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket->user_agreement_filepath = $perjanjian_path;
            $ticket->user_agreement_filepath_status = 0;
            $ticket->save();

            // mail ke tim purchasing file sudah di upload
            $ticket->refresh();
            $user =  Auth::user()->name;
            $region_purchasing_emails = EmailAdditional::where('type', $ticket->salespoint->region_type)->where('category', 'purchasing')->first()->emails ?? [];
            $region_purchasing_emails = json_decode($region_purchasing_emails);
            $national_purchasing_emails = EmailAdditional::where('type', 'national')->where('category', 'purchasing')->first()->emails ?? [];
            $national_purchasing_emails = json_decode($national_purchasing_emails);

            $mail_to = array_unique(array_merge($national_purchasing_emails, $region_purchasing_emails));
            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'Upload Ulang File Perjanjian User Tim Legal COP',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $user,
                'to' => 'Purchasing Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'reupload_user_agreement_legal'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Upload Ulang File Perjanjian User Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload Ulang File Perjanjian User ' . $ex->getMessage());
        }
    }

    public function userUploadFileAggrementTicket(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);

            $perjanjian_file            = $request->file()['file_perjanjian_user'];
            $perjanjian_ext             = pathinfo($perjanjian_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $ticket->code;

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/File_Perjanjian_User_COP_' . $salespointname . '.' . $perjanjian_ext;
            $info                       = pathinfo($path);
            $perjanjian_path            = $perjanjian_file->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket->user_agreement_filepath = $perjanjian_path;
            $ticket->user_agreement_filepath_status = 0;

            $ticket->save();
            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Upload File Perjanjian Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload Perjanjian Legal ' . $ex->getMessage());
        }
    }

    public function reUploadBastkFileCop(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);

            $bastk_file                 = $request->file()['upload_ulang_file_bastk_cop'];
            $bastk_ext                  = pathinfo($bastk_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $ticket->code;

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/BASTK_COP_' . $salespointname . '.' . $bastk_ext;
            $info                       = pathinfo($path);
            $bastk_path                 = $bastk_file->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket->bastk_cop_filepath   = $bastk_path;
            $ticket->save();

            DB::commit();
            return redirect('/ticketing/' . $ticket->code)->with('success', 'Upload Ulang File BASTK COP Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload Ulang File BASTK COP ' . $ex->getMessage());
        }
    }

    public function uploadEvidanceRransferOverPlatform(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);

            $over_plafon_file           = $request->file()['file_over_platform'];
            $over_plafon_ext            = pathinfo($over_plafon_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $ticket->code;

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/Over_Plafon_COP_' . $salespointname . '.' . $over_plafon_ext;
            $info                       = pathinfo($path);
            $over_plafon_path           = $over_plafon_file->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket->over_plafon_filepath = $over_plafon_path;
            $ticket->over_plafon_status = 0;

            $ticket->save();

            // mail ke tim purchasing file sudah di upload
            $ticket->refresh();
            $user =  Auth::user()->name;
            $region_purchasing_emails = EmailAdditional::where('type', $ticket->salespoint->region_type)->where('category', 'purchasing')->first()->emails ?? [];
            $region_purchasing_emails = json_decode($region_purchasing_emails);
            $national_purchasing_emails = EmailAdditional::where('type', 'national')->where('category', 'purchasing')->first()->emails ?? [];
            $national_purchasing_emails = json_decode($national_purchasing_emails);

            $mail_to = array_unique(array_merge($national_purchasing_emails, $region_purchasing_emails));

            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'User upload bukti transfer overplafond',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $user,
                'to' => 'Purchasing Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'user_upload_evidance_overplafond'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            DB::commit();
            return redirect('/ticketing/' . $ticket->code)->with('success', 'Upload Bukti Transfer Over Plafon Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload Bukti Transfer Over Plafon ' . $ex->getMessage());
        }
    }

    public function uploadBastkCop(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $master_armada = Armada::where('plate', $request->nopol)->first();

            $bastk_file                 = $request->file()['file_bastk'];
            $bastk_ext                  = pathinfo($bastk_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $ticket->code;

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/BASTK_COP_' . $salespointname . '.' . $bastk_ext;
            $info                       = pathinfo($path);
            $bastk_path                 = $bastk_file->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket->bastk_cop_filepath   = $bastk_path;
            $ticket->save();

            if ($master_armada) {
                $master_armada->booked_by = $request->booked_by;
                $master_armada->save();

                $ticket->cop_plate = $request->nopol;
                $ticket->save();
            } else {
                $new_master_armada = new Armada;
                $new_master_armada->armada_type_id = $request->armada_type_id;
                $new_master_armada->status = 1;
                $new_master_armada->vehicle_year = $request->vehicle_year . '-01-01';
                $new_master_armada->plate = $request->nopol;
                $new_master_armada->booked_by = $request->booked_by;
                $ticket->cop_plate = $request->nopol;

                $new_master_armada->save();
                $ticket->save();
            }
            DB::commit();
            return redirect('/ticketing/' . $ticket->code)->with('success', 'Upload BASTK COP Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload File Legal ' . $ex->getMessage());
        }
    }

    public function approveOverPlafonTicket(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $ticket->over_plafon_status = 1;
            $ticket->save();
            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Approve Over Plafon Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Approve Over Plafon ' . $ex->getMessage());
        }
    }

    public function rejectOverPlafonTicket(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $ticket->over_plafon_status = -1;
            $ticket->over_plafon_reject_notes = $request->reason;

            $ticket->save();

            // mail ke pembuat tiket
            $ticket->refresh();
            $mail_to = $ticket->created_by_employee->email;
            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'Reject bukti tranfser overplafond',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => 'Bapak/Ibu',
                'code' => $ticket->code,
            );

            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'reject_evidance_overplafond'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Reject Over Plafon Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Reject Over Plafon ' . $ex->getMessage());
        }
    }

    public function revisionOverPlafonTicket(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);

            $code                       = $ticket->code;
            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/Over_Plafon_COP_' . $salespointname;
            File::delete($path);

            $over_plafon_file           = $request->file()['file_over_plafon_revision'];
            $over_plafon_ext            = pathinfo($over_plafon_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $ticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $ticket->code;

            $path                       = 'attachments/ticketing/barangjasa/' . $code . '/COP'  . '/Over_Plafon_COP_' . $salespointname . '.' . $over_plafon_ext;
            $info                       = pathinfo($path);
            $over_plafon_path           = $over_plafon_file->storeAs($info['dirname'], $info['basename'], 'public');

            $ticket->over_plafon_filepath = $over_plafon_path;
            $ticket->over_plafon_status = 0;
            $ticket->over_plafon_reject_notes = '';

            $ticket->save();

            // mail ke tim purchasing file sudah di upload
            $ticket->refresh();
            $user =  Auth::user()->name;
            $region_purchasing_emails = EmailAdditional::where('type', $ticket->salespoint->region_type)->where('category', 'purchasing')->first()->emails ?? [];
            $region_purchasing_emails = json_decode($region_purchasing_emails);
            $national_purchasing_emails = EmailAdditional::where('type', 'national')->where('category', 'purchasing')->first()->emails ?? [];
            $national_purchasing_emails = json_decode($national_purchasing_emails);

            $mail_to = array_unique(array_merge($national_purchasing_emails, $region_purchasing_emails));
            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'User upload bukti transfer overplafond',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $user,
                'to' => 'Purchasing Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'user_reupload_evidance_overplafond'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Revisi Upload Bukti Transfer Over Plafon Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Re Upload Bukti Transfer Over Plafon ' . $ex->getMessage());
        }
    }

    public function uploadArmadaTypeCop(Request $request, $id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $armadatype = ArmadaType::where('name', $request->nama_jenis_kendaraan)->first();
            if ($armadatype) {
                throw new \Exception('Nama Jenis Kendaraan sudah ada');
            }
            $newArmadatype = new ArmadaType;
            $newArmadatype->name = $request->nama_jenis_kendaraan;
            $newArmadatype->brand_name = $request->nama_merk;
            $newArmadatype->alias = $request->nama_alias ?? null;
            $newArmadatype->isNiaga = 2;
            $newArmadatype->save();
            return redirect('/ticketing/' . $ticket->code)->with('success', 'Berhasil Menambah Jenis Armada');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal Menambahkan Jenis Kendaraan (' . $ex->getMessage() . ')');
        }
    }

    public function isTicketFinished($ticket_id)
    {
        $ticket = Ticket::findOrFail($ticket_id);
        $unfisinished_count = 0;
        // cek semua item yang nggak status cancelled
        foreach ($ticket->ticket_item->where('isCancelled', 0) as $item) {
            if (!$item->isFinished) $unfisinished_count++;
        }
        if ($unfisinished_count > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function issuePO(Request $request)
    {
        if ($request->sumInvoice == "smaller") {
            return back()->with('error', 'Jika total invoice lebih kecil dari PO tidak perlu melakukan revisi PO');
        }
        try {
            DB::beginTransaction();
            $po = Po::where('no_po_sap', $request->po_number)->first();
            $po->status = 0;
            $po->save();

            $newIssuedPO             = new IssuePO;
            $newIssuedPO->po_number  = $request->po_number;
            $newIssuedPO->sumInvoice = $request->sumInvoice;
            $newIssuedPO->notes      = $request->notes;

            $ticket = $po->ticket;
            $ext = pathinfo($request->file('ba_file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "BA_RevisePO_" . $request->po_number . '.' . $ext;
            $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/' . $name;
            $file = pathinfo($path);
            $path = $request->file('ba_file')->storeAs($file['dirname'], $file['basename'], 'public');

            $newIssuedPO->ba_file = $path;
            $newIssuedPO->save();

            $monitor                        = new TicketMonitoring;
            $monitor->ticket_id             = $ticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Upload BA issue PO ' . $request->po_number . ' dengan alasan : ' . $request->notes;
            $monitor->save();
            DB::commit();
            return back()->with('success', 'PO berhasil di issued');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal issuePO (' . $ex->getMessage() . ')');
        }
    }

    public function printFRIForm($fri_form_id, $method = 'stream')
    {
        try {
            $fri_form = FRIForm::findOrFail($fri_form_id);
            $ticket = $fri_form->ticket;
            if (!$fri_form) {
                return 'fri form for this ticket is unavailable';
            }
            if ($fri_form->status < 1) {
                return 'fri form unavaiable / incomplete';
            }

            $pdf = PDF::loadView('pdf.friformpdf', compact('fri_form'))->setPaper('tabloid', 'portrait');
            if ($method == 'stream') {
                return $pdf->stream('FRI (' . $ticket->code . ')' . $fri_form->id . '.pdf');
            } elseif ($method == 'path') {
                $path = "/temporary/FRIform/FRI (" . $ticket->code . ")" . $fri_form->id . ".pdf";
                Storage::disk('public')->put($path, $pdf->output());
                return $path;
            }
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal Mencetak FRI (Form Request Infrastruktur) ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }

    public function terminateTicket(Request $request)
    {
        // Superadmin Only
        if (
            Auth::user()->id != 1 &&
            Auth::user()->id != 115 &&
            Auth::user()->id != 116 &&
            Auth::user()->id != 117 &&
            Auth::user()->id != 197 &&
            Auth::user()->id != 118 &&
            Auth::user()->id != 717
        ) {
            return back()->with('error', 'Hanya Admin dan Purchasing yang dapat membatalkan tiket');
        }
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($request->ticket_id);
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
            return back()->with('success', 'Berhasil membatalkan pengadaan ' . $ticket->code);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membatalkan pengadaan ' . $ex->getMessage());
        }
    }

    public function changeOverPlafonTicket(Request $request, $id)
    {
        try {
            // Superadmin & Purchasing Team Only
            if (
                Auth::user()->id == 1 ||
                Auth::user()->id == 115 ||
                Auth::user()->id == 117 ||
                Auth::user()->id == 197 ||
                Auth::user()->id == 116
            ) {
                DB::beginTransaction();
                $ticket = Ticket::findOrFail($id);
                $ticket->is_over_plafon = 0;
                $ticket->save();
                DB::commit();
                return back()->with('success', 'Berhasil ubah status tiket (tidak jadi over plafon) ');
            } else {
                return back()->with('error', 'Hanya Admin dan tim purchasing yang dapat mengubah status tiket (over plafon)');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal ubah status tiket (status tiket masih over plafon)' . $ex->getMessage());
        }
    }

    public function approveAgreementCOP(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $ticket->agreement_filepath_status = 1;
            $ticket->save();
            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Approve File Agreement COP (Legal) Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Approve File Agreement COP (Legal) ' . $ex->getMessage());
        }
    }

    public function approveTorCOP(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $ticket->tor_filepath_status = 1;
            $ticket->save();
            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Approve File TOR COP (Legal) Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Approve File TOR COP (Legal) ' . $ex->getMessage());
        }
    }

    public function approveSphCOP(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $ticket->sph_filepath_status = 1;
            $ticket->save();
            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Approve File SPH COP (Legal) Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Approve File SPH COP (Legal) ' . $ex->getMessage());
        }
    }

    public function approveUserAgreementCOP(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $ticket->user_agreement_filepath_status = 1;
            $ticket->save();
            DB::commit();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Approve File Perjanjian User COP (Legal) Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Approve File Perjanjian User COP (Legal) ' . $ex->getMessage());
        }
    }

    public function rejectAgreementCOP(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $ticket->agreement_filepath_status = -1;
            $ticket->agreement_filepath_reject_notes = $request->reason;

            $ticket->save();
            DB::commit();

            // mail ke tim legal
            $ticket->refresh();
            $user =  Auth::user()->name;
            $legal_emails = EmailAdditional::where('type', 'COP')->where('category', 'legal')->first()->emails ?? [];
            $legal_emails = json_decode($legal_emails);

            $mail_to = array_unique(array_merge($legal_emails));

            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'Reject File Perjanjian Tim Legal COP',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $user,
                'to' => 'Legal Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'reject_agreement_legal'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();


            return redirect('/ticketing/' . $ticket->code)->with('success', 'Reject File Agreement COP (Legal) Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Reject File Agreement COP (Legal) ' . $ex->getMessage());
        }
    }

    public function rejectTorCOP(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $ticket->tor_filepath_status = -1;
            $ticket->tor_filepath_reject_notes = $request->reason;

            $ticket->save();
            DB::commit();

            // mail ke tim legal
            $ticket->refresh();
            $user =  Auth::user()->name;
            $legal_emails = EmailAdditional::where('type', 'COP')->where('category', 'legal')->first()->emails ?? [];
            $legal_emails = json_decode($legal_emails);

            $mail_to = array_unique(array_merge($legal_emails));

            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'Reject File TOR Tim Legal COP',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $user,
                'to' => 'Legal Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'reject_tor_legal'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Reject File TOR COP (Legal) Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Reject File TOR COP (Legal) ' . $ex->getMessage());
        }
    }

    public function rejectSphCOP(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $ticket->sph_filepath_status = -1;
            $ticket->sph_filepath_reject_notes = $request->reason;

            $ticket->save();
            DB::commit();

            // mail ke tim legal
            $ticket->refresh();
            $user =  Auth::user()->name;
            $legal_emails = EmailAdditional::where('type', 'COP')->where('category', 'legal')->first()->emails ?? [];
            $legal_emails = json_decode($legal_emails);

            $mail_to = array_unique(array_merge($legal_emails));

            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'Reject File SPH Tim Legal COP',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $user,
                'to' => 'Legal Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'reject_sph_legal'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Reject File SPH COP (Legal) Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Reject File SPH COP (Legal) ' . $ex->getMessage());
        }
    }

    public function rejectUserAgreementCOP(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($id);
            $ticket->user_agreement_filepath_status = -1;
            $ticket->user_agreement_filepath_reject_notes = $request->reason;

            $ticket->save();
            DB::commit();

            // mail ke tim legal
            $ticket->refresh();
            $user =  Auth::user()->name;
            $legal_emails = EmailAdditional::where('type', 'COP')->where('category', 'legal')->first()->emails ?? [];
            $legal_emails = json_decode($legal_emails);

            $mail_to = array_unique(array_merge($legal_emails));

            $ccs = $ticket->additional_emails() ?? [];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => 'Reject File Perjanjian User Tim Legal COP',
                'ticketing_type' => 'COP',
                'salespoint_name' => $ticket->salespoint->name,
                'from' => $user,
                'to' => 'Legal Team',
                'code' => $ticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)
                    ->send(new NotificationMail($data, 'reject_user_agreement_legal'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            $ticket->refresh();

            return redirect('/ticketing/' . $ticket->code)->with('success', 'Reject File User Agreement COP (Legal) Berhasil');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Reject File User Agreement COP (Legal) ' . $ex->getMessage());
        }
    }

    public function uploadRevisionLpbCop(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket_item = TicketItem::findOrFail($request->id);
            $ticket_attachment = TicketItemAttachment::where('ticket_item_id', '=', $request->id)->where('name', 'like', '%LPB_HO_TANGERANG%')->first();
            $ticket = $ticket_item->ticket;

            $lpb_file                   = $request->file()['file_revision_lpb_cop'];
            $lpb_ext                    = pathinfo($lpb_file->getClientOriginalName(), PATHINFO_EXTENSION);
            $name                       = 'LPB_HO_TANGERANG' . '.' . $lpb_ext;
            $path                       = "/attachments/ticketing/barangjasa/" . $ticket->code . '/item' . $ticket_item->id . '/' . $name;
            $info                       = pathinfo($path);
            $lpb_path                   = $lpb_file->storeAs($info['dirname'], $info['basename'], 'public');
            $ticket_attachment->path    = $lpb_path;
            $ticket_attachment->save();

            DB::commit();

            return back()->with('success', 'Berhasil Revisi LPB COP, Pengadaan selesai');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal melakukan Revisi LPB COP ' . $ex->getMessage());
        }
    }

    public function uploadLpbCop(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket_item = TicketItem::findOrFail($request->id);
            $ticket = $ticket_item->ticket;

            $original_filename              = $request->file('file_lpb_cop')->getClientOriginalName();
            $newAttachment                  = new TicketItemAttachment;
            $newAttachment->ticket_item_id  = $ticket_item->id;
            $ext                            = pathinfo($original_filename, PATHINFO_EXTENSION);
            $newAttachment->name            = 'LPB_HO_TANGERANG' . '.' . $ext;
            $path                           = "/attachments/ticketing/barangjasa/" . $ticket->code . '/item' . $ticket_item->id . '/' . $newAttachment->name;
            $file                           = pathinfo($path);
            $path                           = $request->file('file_lpb_cop')->storeAs($file['dirname'], $file['basename'], 'public');
            $newAttachment->path            = $path;
            $newAttachment->save();

            $ticket_item->isFinished        = true;
            $ticket_item->confirmed_by      = Auth::user()->id;
            $ticket_item->save();

            $monitor = new TicketMonitoring;
            $monitor->ticket_id      = $ticket_item->ticket->id;
            $monitor->employee_id    = Auth::user()->id;
            $monitor->employee_name  = Auth::user()->name;
            $monitor->message        = 'Upload File LPB dan Invoice untuk item ' . $ticket_item->name;
            $monitor->save();

            $ticket = $ticket_item->ticket;
            $isTicketFinished = $this->isTicketFinished($ticket->id);
            if ($isTicketFinished) {
                $ticket->status = 7;
                $ticket->show_lpb_cop = 0;
                $ticket->finished_date = now()->format('Y-m-d');
                $ticket->save();
            }

            DB::commit();

            return back()->with('success', 'Berhasil Upload LPB COP, Pengadaan selesai');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal melakukan Upload LPB COP ' . $ex->getMessage());
        }
    }

    public function showLpbCop(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($request->id);
            $ticket->show_lpb_cop = 1;
            $ticket->save();

            DB::commit();

            return back()->with('success', 'Berhasil Show LPB COP');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Show LPB COP ' . $ex->getMessage());
        }
    }

    // Custom Ticket
    public function createTicket(Request $request)
    {
        try {
            DB::beginTransaction();
            $customticketing = DB::table('custom_ticketing')->where('id', $request->custom_ticketing_id)->first();
            $settings = json_decode($customticketing->settings);

            $ticket                         = new Ticket;
            $ticket->budget_upload_id       = null;
            $ticket->po_reference_number    = $request->po_number ?? null;
            $ticket->requirement_date       = $request->requirement_date;

            if ($settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP') {
                $ticket->salespoint_id      = 251;
            } else {
                $ticket->salespoint_id      = $request->salespoint_id;
            }

            $ticket->authorization_id       = $request->authorization_id;
            $item_type = '';
            if ($settings->item_type == "barang") {
                $item_type = 0;
            } elseif ($settings->item_type == "jasa") {
                $item_type = 1;
            } else {
                $item_type = 4;
            }

            $ticket->item_type              = $item_type;

            $ticket->request_type           = 0;
            $ticket->budget_type            = $request->budget_type;
            $ticket->reason                 = "";
            $ticket->custom_settings        = $customticketing->settings;
            $ticket->created_by             = Auth::user()->id;
            $ticket->save();

            $code_type = '';
            if ($item_type == 0) {
                $code_type = "P01";
            } elseif ($item_type == 1) {
                $code_type = "P02";
            } else {
                $code_type = "P03";
            }

            // ambil kode inisial salespoint
            $code_salespoint_initial = strtoupper($ticket->salespoint->initial);

            // ambil jumlah urutan ticketing d terkait dalam bulan dan tahun ini
            $armada_ticket_count = ArmadaTicket::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])->withTrashed()->count();

            $security_ticket_count = SecurityTicket::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])->withTrashed()->count();

            $barang_ticket_count = Ticket::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])->where('status', '>', 0)->withTrashed()->count();

            $code_total_count = $armada_ticket_count + $security_ticket_count + $barang_ticket_count;
            do {
                $code = $code_type . "-" . $code_salespoint_initial . "-" . now()->translatedFormat('dmy') . str_repeat("0", 4 - strlen($code_total_count + 1)) . ($code_total_count + 1);
                $code_total_count++;
                $checkbarang = Ticket::where('code', $code)->first();
                $checkarmada = ArmadaTicket::where('code', $code)->first();
                $checksecurity = SecurityTicket::where('code', $code)->first();
                ($checkbarang != null || $checkarmada != null) ? $flag = false : $flag = true;
            } while (!$flag);
            $ticket->code           = $code;
            $ticket->save();

            $salespoint = $ticket->salespoint;

            $ticket_item = $settings->ticket_item_name;
            $qty = 1;
            $value = 0;
            // tambahin ke ticket item default nama itemnya
            $newTicketItem                        = new TicketItem;
            $newTicketItem->ticket_id             = $ticket->id;
            $newTicketItem->budget_pricing_id     = -1;
            $newTicketItem->name                  = $ticket_item;
            $newTicketItem->brand                 = "";
            $newTicketItem->type                  = "";
            $newTicketItem->price                 = $value;
            $newTicketItem->count                 = $qty;
            $newTicketItem->save();

            // set upload file ke ticket item di atas
            for ($i = 0; $i < count($request->filename); $i++) {
                $original_filename              = $request->file('file')[$i]->getClientOriginalName();
                $newAttachment                  = new TicketItemAttachment;
                $newAttachment->ticket_item_id  = $newTicketItem->id;
                $salespointname                 = str_replace(' ', '_', $ticket->salespoint->name);
                $filename                       = str_replace(' ', '_', trim($request->filename[$i]));
                $ext                            = pathinfo($original_filename, PATHINFO_EXTENSION);
                $newAttachment->name            = $filename . '_' . $salespointname . '.' . $ext;
                $path                           = "/attachments/ticketing/barangjasa/" . $ticket->code . '/item' . $newTicketItem->id . '/' . $newAttachment->name;
                $file                           = pathinfo($path);
                $path                           = $request->file('file')[$i]->storeAs($file['dirname'], $file['basename'], 'public');
                $newAttachment->path            = $path;
                $newAttachment->save();
            }

            // Pengadaan Baru ubah status ke draft untuk mengisi vendor, attachment, alasan pengadaan
            $authorizations = Authorization::find($request->authorization_id);
            foreach ($authorizations->authorization_detail as $detail) {
                $newTicketAuthorization                     = new TicketAuthorization;
                $newTicketAuthorization->ticket_id          = $ticket->id;
                $newTicketAuthorization->employee_id        = $detail->employee_id;
                $newTicketAuthorization->employee_name      = $detail->employee->name;
                $newTicketAuthorization->as                 = $detail->sign_as;
                $newTicketAuthorization->employee_position  = $detail->employee_position->name;
                $newTicketAuthorization->level              = $detail->level;
                $newTicketAuthorization->save();
            }
            $ticket->reason = "Pengadaan Baru";
            $ticket->status = 1;
            $ticket->save();
            DB::commit();
            return redirect('/ticketing')->with('success', 'Berhasil menambah custom ticket. Silahkan Melakukan Otorisasi');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('error', 'Gagal membuat tiket. Harap mencoba kembali"' . $ex->getMessage() . '"');
        }
    }
}
