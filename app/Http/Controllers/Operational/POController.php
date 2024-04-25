<?php

namespace App\Http\Controllers\Operational;

use DB;
use PDF;
use Auth;
use Form;
use Mail;
use Storage;
use App\Models\Po;
use Carbon\Carbon;
use App\Mail\POMail;
use App\Models\Armada;
use App\Models\Ticket;
use App\Models\Vendor;
use App\Models\EmailCC;
use App\Models\IssuePO;
use App\Models\Employee;
use App\Models\PoDetail;
use App\Models\PoManual;
use App\Models\PrDetail;
use App\Models\ArmadaType;
use App\Models\SalesPoint;
use App\Models\TicketItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use App\Models\ArmadaBudget;
use App\Models\ArmadaTicket;
use App\Models\TicketVendor;
use Illuminate\Http\Request;
use App\Models\Authorization;
use App\Models\SecurityTicket;
use App\Models\PoAuthorization;
use App\Models\POUploadRequest;
use App\Models\TicketMonitoring;
use App\Http\Controllers\Controller;
use App\Models\ArmadaTicketMonitoring;
use App\Models\EmployeeLocationAccess;
use App\Models\SecurityTicketMonitoring;


class POController extends Controller
{
    public function poView(Request $request)
    {
        return view('Operational.po');
    }

    public function poData(Request $request)
    {
        $search_value = $request->search["value"];
        if ($request->type == "ticket") {
            $item_type_array = [];
            if (str_contains(strtolower("Barang"), strtolower($search_value))) {
                array_push($item_type_array, 0);
            }
            if (str_contains(strtolower("Jasa"), strtolower($search_value))) {
                array_push($item_type_array, 1);
            }
            if (str_contains(strtolower("Maintenance"), strtolower($search_value))) {
                array_push($item_type_array, 2);
            }
            if (str_contains(strtolower("HO"), strtolower($search_value))) {
                array_push($item_type_array, 3);
            }

            $request_type_array = [];
            if (str_contains(strtolower("Pengadaan"), strtolower($search_value))) {
                array_push($request_type_array, 0);
            }
            if (str_contains(strtolower("Replace Existing"), strtolower($search_value))) {
                array_push($request_type_array, 1);
            }
            if (str_contains(strtolower("Repeat Order"), strtolower($search_value))) {
                array_push($request_type_array, 2);
            }
            if (str_contains(strtolower("Perpanjangan"), strtolower($search_value))) {
                array_push($request_type_array, 3);
            }
            if (str_contains(strtolower("End Kontrak"), strtolower($search_value))) {
                array_push($request_type_array, 4);
            }


            $employee_access = Auth::user()->location_access_list();
            $ticketing =  Ticket::leftJoin('salespoint', 'salespoint.id', '=', 'ticket.salespoint_id')
                ->leftJoin('po', 'po.ticket_id', '=', 'ticket.id')
                ->where(function ($query) use ($employee_access, $search_value) {
                    // filter apakan punya akses
                    $query->whereIn('ticket.salespoint_id', $employee_access);
                })
                ->where(function ($query) use ($search_value, $item_type_array, $request_type_array) {
                    // filter apakan punya akses
                    $query->where(DB::raw('lower(ticket.code)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(salespoint.name)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(po.no_po_sap)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(po.sender_name)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orWhereIn('ticket.item_type', $item_type_array)
                        ->orWhereIn('ticket.request_type', $request_type_array);
                })
                ->where('ticket.status', '>=', 6)
                ->where('item_type', '!=', 4)
                ->select('ticket.*')
                ->distinct('ticket.id')

                ->get();

            // munculkan data ticket yang po nya belum selesai semua & yang masih belom ada po / masih di setup
            $ticketing = $ticketing->filter(function ($value, $key) use ($request) {
                $isFinished = true;
                if (($value->po->count() ?? 0) == 0) {
                    $isFinished = false;
                }
                foreach ($value->po as $po) {
                    if ($po->status < 3) {
                        $isFinished = false;
                    }
                }
                if ($request->status == -1) {
                    if (!$isFinished) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    if ($isFinished) {
                        return false;
                    } else {
                        return true;
                    }
                }
            });

            $tickets_paginate = $ticketing->skip($request->start)->take($request->length);
            $datas = [];
            $count = $request->start + 1;
            foreach ($tickets_paginate as $ticket) {
                $isView = false;
                if (((Auth::user()->menu_access->operational ?? 0) & 8) != 0 && $ticket->status() == 'Menunggu Setup PO') {
                    $isView = true;
                }
                if (((Auth::user()->menu_access->operational ?? 0) & 16) != 0 && $ticket->status() != 'Menunggu Setup PO') {
                    $isView = true;
                }
                if (!$isView) {
                    continue;
                }
                $array = [];
                array_push($array, $count);
                array_push($array, $ticket->code);
                array_push($array, $ticket->item_type() . " " . $ticket->request_type());
                array_push($array, $ticket->salespoint->name);
                array_push($array, (count($ticket->sender_array_list()) > 0) ? (implode(', ', $ticket->sender_array_list())) : '-');
                array_push($array, (count($ticket->po_array_list()) > 0) ? (implode(', ', $ticket->po_array_list())) : '-');
                array_push($array, $ticket->created_at->translatedFormat('d F Y (H:i)'));
                array_push($array, implode(",\n", $ticket->ticket_item->pluck('name')->toArray()));
                array_push($array, $ticket->status("complete"));
                array_push($datas, $array);
                $count++;
            }
            return response()->json([
                "data" => $datas,
                "draw" => $request->draw,
                "recordsFiltered" => $ticketing->count(),
                "recordsTotal" => $ticketing->count(),
            ]);
        }
        if ($request->type == "armada") {
            $ticketing_type_array = [];
            if (str_contains(strtolower("Pengadaan"), strtolower($search_value))) {
                array_push($ticketing_type_array, 0);
            }
            if (str_contains(strtolower("Perpanjangan"), strtolower($search_value))) {
                array_push($ticketing_type_array, 1);
            }
            if (str_contains(strtolower("Replace"), strtolower($search_value))) {
                array_push($ticketing_type_array, 1);
            }
            if (str_contains(strtolower("Renewal"), strtolower($search_value))) {
                array_push($ticketing_type_array, 1);
            }
            if (str_contains(strtolower("End Kontrak"), strtolower($search_value))) {
                array_push($ticketing_type_array, 1);
            }
            if (str_contains(strtolower("Mutasi"), strtolower($search_value))) {
                array_push($ticketing_type_array, 2);
            }

            $employee_access = Auth::user()->location_access_list();
            $ticketing =  ArmadaTicket::leftJoin('salespoint', 'salespoint.id', '=', 'armada_ticket.salespoint_id')
                ->leftJoin('po', 'po.armada_ticket_id', '=', 'armada_ticket.id')
                ->where(function ($query) use ($employee_access, $search_value) {
                    // filter apakan punya akses
                    $query->whereIn('armada_ticket.salespoint_id', $employee_access);
                })
                ->where(function ($query) use ($search_value, $ticketing_type_array) {
                    // filter apakan punya akses
                    $query->where(DB::raw('lower(armada_ticket.code)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(salespoint.name)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(po.no_po_sap)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(po.sender_name)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orWhereIn('armada_ticket.ticketing_type', $ticketing_type_array);
                })
                ->where('armada_ticket.status', '>=', 4)
                // ->where('po.armada_ticket_id', '!=', null)
                ->select('armada_ticket.*')
                ->distinct('armada_ticket.id')
                ->get();

            // munculkan data ticket yang po nya belum selesai semua & yang masih belom ada po / masih di setup
            $ticketing = $ticketing->filter(function ($value, $key) use ($request) {
                $isFinished = true;
                if (($value->po->count() ?? 0) == 0) {
                    $isFinished = false;
                }
                foreach ($value->po as $po) {
                    if ($po->status < 3) {
                        $isFinished = false;
                    }
                }
                if ($request->status == -1) {
                    if (!$isFinished) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    if ($isFinished) {
                        return false;
                    } else {
                        return true;
                    }
                }
            });

            $tickets_paginate = $ticketing->skip($request->start)->take($request->length);
            $datas = [];
            $count = $request->start + 1;
            foreach ($tickets_paginate as $ticket) {
                $isView = false;
                if (((Auth::user()->menu_access->operational ?? 0) & 8) != 0 && $ticket->status() == 'Menunggu Setup PO') {
                    $isView = true;
                }
                if (((Auth::user()->menu_access->operational ?? 0) & 16) != 0 && $ticket->status() != 'Menunggu Setup PO') {
                    $isView = true;
                }
                if (!$isView) {
                    continue;
                }
                $array = [];
                array_push($array, $count);
                array_push($array, $ticket->code);
                array_push($array, $ticket->type());
                array_push($array, $ticket->salespoint->name);
                array_push($array, (count($ticket->sender_array_list()) > 0) ? (implode(', ', $ticket->sender_array_list())) : '-');
                array_push($array, (count($ticket->po_array_list()) > 0) ? (implode(', ', $ticket->po_array_list())) : '-');
                array_push($array, $ticket->created_at->translatedFormat('d F Y (H:i)'));
                array_push($array, $ticket->armada_type->name . " " . $ticket->armada_type->brand_name);
                array_push($array, $ticket->status("complete"));
                array_push($datas, $array);
                $count++;
            }
            return response()->json([
                "data" => $datas,
                "draw" => $request->draw,
                "recordsFiltered" => $ticketing->count(),
                "recordsTotal" => $ticketing->count(),
            ]);
        }
        if ($request->type == "security") {
            $ticketing_type_array = [];
            if (str_contains(strtolower("Pengadaan"), strtolower($search_value))) {
                array_push($ticketing_type_array, 0);
            }
            if (str_contains(strtolower("Perpanjangan"), strtolower($search_value))) {
                array_push($ticketing_type_array, 1);
            }
            if (str_contains(strtolower("Replace"), strtolower($search_value))) {
                array_push($ticketing_type_array, 2);
            }
            if (str_contains(strtolower("End Kontrak"), strtolower($search_value))) {
                array_push($ticketing_type_array, 3);
            }
            if (str_contains(strtolower("Pengadaan Lembur"), strtolower($search_value))) {
                array_push($ticketing_type_array, 4);
            }

            $employee_access = Auth::user()->location_access_list();
            $ticketing =  SecurityTicket::leftJoin('salespoint', 'salespoint.id', '=', 'security_ticket.salespoint_id')
                ->leftJoin('po', 'po.security_ticket_id', '=', 'security_ticket.id')
                ->where(function ($query) use ($employee_access, $search_value) {
                    // filter apakan punya akses
                    $query->whereIn('security_ticket.salespoint_id', $employee_access);
                })
                ->where(function ($query) use ($search_value, $ticketing_type_array) {
                    // filter apakan punya akses
                    $query->where(DB::raw('lower(security_ticket.code)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(salespoint.name)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(po.no_po_sap)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orwhere(DB::raw('lower(po.sender_name)'), 'like', '%' . strtolower($search_value) . '%')
                        ->orWhereIn('security_ticket.ticketing_type', $ticketing_type_array);
                })
                ->where('security_ticket.status', '>=', 4)
                ->select('security_ticket.*')
                ->distinct('security_ticket.id')
                ->get();

            // munculkan data ticket yang po nya belum selesai semua & yang masih belom ada po / masih di setup
            $ticketing = $ticketing->filter(function ($value, $key) use ($request) {
                $isFinished = true;
                if (($value->po->count() ?? 0) == 0) {
                    $isFinished = false;
                }
                foreach ($value->po as $po) {
                    if ($po->status < 3) {
                        $isFinished = false;
                    }
                }
                if ($request->status == -1) {
                    if (!$isFinished) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    if ($isFinished) {
                        return false;
                    } else {
                        return true;
                    }
                }
            });

            $tickets_paginate = $ticketing->skip($request->start)->take($request->length);
            $datas = [];
            $count = $request->start + 1;
            foreach ($tickets_paginate as $ticket) {
                $isView = false;
                if (((Auth::user()->menu_access->operational ?? 0) & 8) != 0 && $ticket->status() == 'Menunggu Setup PO') {
                    $isView = true;
                }
                if (((Auth::user()->menu_access->operational ?? 0) & 16) != 0 && $ticket->status() != 'Menunggu Setup PO') {
                    $isView = true;
                }
                if (!$isView) {
                    continue;
                }
                $array = [];
                array_push($array, $count);
                array_push($array, $ticket->code);
                array_push($array, $ticket->type());
                array_push($array, $ticket->salespoint->name);
                array_push($array, (count($ticket->sender_array_list()) > 0) ? (implode(', ', $ticket->sender_array_list())) : '-');
                array_push($array, (count($ticket->po_array_list()) > 0) ? (implode(', ', $ticket->po_array_list())) : '-');
                array_push($array, $ticket->created_at->translatedFormat('d F Y (H:i)'));
                array_push($array, $ticket->status("complete"));
                array_push($datas, $array);
                $count++;
            }
            return response()->json([
                "data" => $datas,
                "draw" => $request->draw,
                "recordsFiltered" => $ticketing->count(),
                "recordsTotal" => $ticketing->count(),
            ]);
        }
    }

    public function podetailView($ticket_code)
    {
        try {
            $ticket = Ticket::where('code', $ticket_code)->first();
            // $api_token = config('app.api_sap_token');
            $armadaticket = ArmadaTicket::where('code', $ticket_code)->first();
            $securityticket = SecurityTicket::where('code', $ticket_code)->first();
            if ($ticket ==  null && $armadaticket == null && $securityticket == null) {
                throw new \Exception("Ticket tidak ditemukan");
            }
            $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
            if ($ticket != null) {
                // validate detail has akses area
                if (!$user_location_access->contains($ticket->salespoint_id)) {
                    return redirect('/po')->with('error', 'Anda tidak memiliki akses untuk PO berikut. Tidak memiliki akses salespoint "' . $ticket->salespoint->name . '"');
                }

                if ($ticket->po->count() > 0) {
                    $authorization_list = Authorization::where('form_type', 3)->whereIn('salespoint_id', $ticket->salespoint->salespoint_id_list())->get();
                    return view('Operational.podetail', compact('ticket', 'authorization_list'));
                } else {
                    $issuepolist = Po::where('ticket_id', $ticket->id)->withTrashed()->get()->pluck('no_po_sap');
                    $issuepos = IssuePO::whereIn('po_number', $issuepolist->toArray())->get();
                    return view('Operational.setuppo', compact('ticket', 'ticket_code', 'issuepos'));
                }
            }
            if ($armadaticket != null) {
                // validate detail has akses area
                if (!$user_location_access->contains($armadaticket->salespoint_id)) {
                    return redirect('/po')->with('error', 'Anda tidak memiliki akses untuk PO berikut. Tidak memiliki akses salespoint "' . $armadaticket->salespoint->name . '"');
                }

                // validasi jika form form belum divalidasi
                if ($armadaticket->status() == "Menunggu validasi form kelengkapan") {
                    return redirect('/po')->with('error', 'Form kelengkapan terkait ticketing belum di validasi. Harap untuk melakukan validasi form di menu "Form Validation"');
                }

                if ($armadaticket->po->count() > 0) {
                    $authorization_list = Authorization::whereIn('salespoint_id', $armadaticket->salespoint->salespoint_id_list())->where('form_type', 3)->get();
                    return view('Operational.podetail', compact('armadaticket', 'authorization_list'));
                } else {
                    // OLD SETUP PO
                    // =====================================
                    // $armada_vendors = Vendor::where('type','armada')->get();
                    // ambil armada sesuai niaga yang terdaftar
                    // $armada_types = [];
                    // $po = $armadaticket->po_reference;
                    // $pomanual = PoManual::where('po_number',$armadaticket->po_reference_number)->first();
                    // if($armadaticket->po_reference_number != null){
                    //     $armada_types = ArmadaType::where('isNiaga',$po->armada_ticket->armada_type->isNiaga ?? $pomanual->isNiaga)
                    //     ->get();
                    // }
                    // return view('Operational.Armada.poitemselection',compact('armadaticket','armada_vendors','armada_types','po','pomanual'));
                    // =====================================

                    $issuepolist = Po::where('armada_ticket_id', $armadaticket->id)->withTrashed()->get()->pluck('no_po_sap');
                    $issuepos = IssuePO::whereIn('po_number', $issuepolist->toArray())->get();
                    return view('Operational.setuppo', compact('armadaticket', 'ticket_code', 'issuepos'));
                }
            }

            if ($securityticket != null) {
                // validate detail has akses area
                if (!$user_location_access->contains($securityticket->salespoint_id)) {
                    return redirect('/po')->with('error', 'Anda tidak memiliki akses untuk PO berikut. Tidak memiliki akses salespoint "' . $securityticket->salespoint->name . '"');
                }

                if (count($securityticket->po) > 0) {
                    $authorization_list = Authorization::where('form_type', 3)
                        ->whereIn('salespoint_id', $securityticket->salespoint->salespoint_id_list())
                        ->get();
                    return view('Operational.podetail', compact('securityticket', 'authorization_list'));
                } else {
                    // OLD SETUP PO
                    // =====================================
                    // $po = $securityticket->po_reference;
                    // $pomanual = PoManual::where('po_number',$securityticket->po_reference_number)->first();
                    // return view('Operational.Security.poitemselection',compact('securityticket','po','pomanual'));
                    // =====================================
                    $issuepolist = Po::where('armada_ticket_id', $securityticket->id)->withTrashed()->get()->pluck('no_po_sap');
                    $issuepos = IssuePO::whereIn('po_number', $issuepolist->toArray())->get();
                    return view('Operational.setuppo', compact('securityticket', 'ticket_code', 'issuepos'));
                }
            }
        } catch (\Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
    }

    public function setupPO(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::find($request->ticket_id);
            $armadaticket = ArmadaTicket::find($request->armada_ticket_id);
            $securityticket = SecurityTicket::find($request->security_ticket_id);
            if ($ticket != null) {
                // sudah di setup po sebelumnnya
                if ($ticket->po->count() > 0) {
                    return back()->with('error', 'PO sudah di setup sebelumnya');
                }
                $group_item_by_selected_vendor = collect($request->item)->groupBy('ticket_vendor_id');
                foreach ($group_item_by_selected_vendor as $vendor_items) {
                    $ticket_vendor = TicketVendor::find($vendor_items[0]["ticket_vendor_id"]);
                    $ppn_items = [];
                    $non_ppn_items = [];
                    foreach ($vendor_items as $item) {
                        $prdetail = PrDetail::findOrFail($item['pr_detail_id']);
                        $newDetail = new \stdClass();
                        $newDetail->item_name         = $prdetail->ticket_item->name;
                        $newDetail->item_description  = $prdetail->ticket_item->bidding->ketersediaan_barang_notes;
                        $newDetail->ticket_item_id    = $prdetail->ticket_item->id;
                        $newDetail->qty               = $prdetail->qty;
                        $newDetail->item_price        = $prdetail->price;
                        if ($item['ppn_percentage'] == null) {
                            array_push($non_ppn_items, $newDetail);
                        } else {
                            $newDetail->ppn_percentage    = $item['ppn_percentage'];
                            array_push($ppn_items, $newDetail);
                        }

                        if ($prdetail->ongkir > 0) {
                            $newDetail = new \stdClass();
                            $newDetail->item_name         = 'Ongkir ' . $prdetail->ticket_item->name;
                            $newDetail->item_description  = '';
                            $newDetail->ticket_item_id    = $prdetail->ticket_item->id;
                            $newDetail->qty               = 1;
                            $newDetail->item_price        = $prdetail->ongkir;
                            array_push($non_ppn_items, $newDetail);
                        }

                        if ($prdetail->ongpas > 0) {
                            $newDetail = new \stdClass();
                            $newDetail->item_name         = 'Ongpas ' . $prdetail->ticket_item->name;
                            $newDetail->item_description  = '';
                            $newDetail->ticket_item_id    = $prdetail->ticket_item->id;
                            $newDetail->qty               = 1;
                            $newDetail->item_price        = $prdetail->ongpas;
                            array_push($non_ppn_items, $newDetail);
                        }
                    }

                    if (count($ppn_items) > 0) {
                        // PPN ITEMS
                        $groupby_ppn_percentage = collect($ppn_items)->groupBy('ppn_percentage');
                        foreach ($groupby_ppn_percentage as $lists) {
                            $newPO = new Po;
                            $newPO->ticket_id        = $ticket->id;
                            $newPO->ticket_vendor_id = $item['ticket_vendor_id'];
                            $newPO->has_ppn          = true;
                            $newPO->ppn_percentage   = $lists[0]->ppn_percentage;
                            $newPO->sender_name      = $ticket_vendor->name;
                            $newPO->send_name        = $ticket->salespoint->name;
                            $newPO->save();
                            foreach ($lists as $list) {
                                $podetail = new PoDetail;
                                $podetail->po_id             = $newPO->id;
                                $podetail->item_name         = $list->item_name;
                                $podetail->item_description  = $list->item_description;
                                $podetail->ticket_item_id    = $list->ticket_item_id;
                                $podetail->qty               = $list->qty;
                                $podetail->item_price        = $list->item_price;
                                $podetail->save();
                            }
                        }
                    }

                    // NON PPN ITEM
                    if (count($non_ppn_items) > 0) {
                        $newPO = new Po;
                        $newPO->ticket_id        = $ticket->id;
                        $newPO->ticket_vendor_id = $item['ticket_vendor_id'];
                        $newPO->has_ppn          = false;
                        $newPO->sender_name      = $ticket_vendor->name;
                        $newPO->send_name        = $ticket->salespoint->name;
                        $newPO->save();

                        foreach ($non_ppn_items as $list) {
                            $podetail = new PoDetail;
                            $podetail->po_id             = $newPO->id;
                            $podetail->item_name         = $list->item_name;
                            $podetail->item_description  = $list->item_description;
                            $podetail->ticket_item_id    = $list->ticket_item_id;
                            $podetail->qty               = $list->qty;
                            $podetail->item_price        = $list->item_price;
                            $podetail->save();
                        }
                    }
                }

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Melakukan Setup PO';
                $monitor->save();
            }

            if ($armadaticket != null) {
                // sudah di setup po sebelumnnya
                if ($armadaticket->po->count() > 0) {
                    return back()->with('error', 'PO sudah di setup sebelumnya');
                }
                switch ($armadaticket->type()) {
                    case 'Pengadaan':
                        $armadaticket->vendor_name = $request->selected_vendor;
                        break;

                    case 'Perpanjangan':
                        $armadaticket->vendor_name = $request->selected_vendor;
                        break;

                    case 'Replace':
                        $armadaticket->vendor_name = $request->selected_vendor;
                        $armadaticket->armada_type_id = $request->armada_type_id;
                        break;

                    case 'Renewal':
                        $armadaticket->vendor_name = $request->selected_vendor;
                        $armadaticket->armada_type_id = $request->armada_type_id;
                        break;

                    case 'Mutasi':
                        // copy semua dari ticket armada lama
                        $old_armada_ticket              = $armadaticket->po_reference->armada_ticket;
                        $armadaticket->vendor_name      = $old_armada_ticket->vendor_name;
                        $armadaticket->armada_type_id   = $old_armada_ticket->armada_type_id;
                        $armadaticket->armada_id        = $old_armada_ticket->armada_id;
                        break;
                }
                $armadaticket->save();

                $newPo                   = new Po;
                $newPo->armada_ticket_id = $armadaticket->id;
                if ($armadaticket->type() == "Mutasi") {
                    // untuk mutasi sender dari salespoint lama dan penerima salespoint tujuan
                    $newPo->sender_name      = $armadaticket->po_reference->armada_ticket->vendor_name;
                    $newPo->send_name        = $armadaticket->mutasi_form->receiver_salespoint_name;
                } else {
                    $newPo->sender_name      = $request->selected_vendor;
                    $newPo->send_name        = $armadaticket->salespoint->name;
                }
                $newPo->has_ppn          = true;
                $newPo->ppn_percentage   = 10;
                $newPo->save();

                // sewa
                if ($request->sewa_value < 10000) {
                    return back()->with('error', 'Minimal biaya sewa Rp 10.000,-');
                }
                $notes = $request->sewa_notes;
                $value = $request->sewa_value;
                // jika ada biaya ekspedisi gabungkan dengan biaya sewa

                if ($request->ekspedisi_count != null) {
                    $value += $request->ekspedisi_value / $request->sewa_count;
                    $single_expedition_value = $request->ekspedisi_value / $request->sewa_count;

                    $notes = $notes . "\r\n" . 'Biaya Ekspedisi';
                    $notes = $notes . "\r\n" . $request->ekspedisi_value . '/' . $request->sewa_count . '=' . $single_expedition_value;
                    $notes = $notes . "\r\n" . $request->ekspedisi_notes;
                }

                // biaya sewa
                $newPoDetail = new PoDetail;
                $newPoDetail->po_id            = $newPo->id;
                $newPoDetail->item_name        = $request->sewa_name;
                $newPoDetail->item_description = $notes;
                $newPoDetail->uom              = 'AU';
                $newPoDetail->qty              = $request->sewa_count;
                $newPoDetail->item_price       = $value;
                $newPoDetail->save();

                // jika ada prorate tambahkan biaya
                if ($request->prorate_value != null) {
                    if ($request->prorate_value < 10000) {
                        return back()->with('error', 'Minimal biaya prorate Rp 10.000,-');
                    }
                    $newPoDetail = new PoDetail;
                    $newPoDetail->po_id            = $newPo->id;
                    $newPoDetail->item_name        = 'Prorate Armada';
                    $newPoDetail->item_description = $request->prorate_notes;
                    $newPoDetail->uom              = 'AU';
                    $newPoDetail->qty              = $request->prorate_count;
                    $newPoDetail->item_price       = $request->prorate_value;
                    $newPoDetail->save();
                }

                $monitor                        = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $armadaticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Melakukan Setup PO';
                $monitor->save();
            }

            if ($securityticket != null) {
                // sudah di setup po sebelumnnya
                if (count($securityticket->po) > 0) {
                    throw new \Exception('PO sudah di setup sebelumnya');
                }
                switch ($securityticket->type()) {
                    case 'Pengadaan':
                        $securityticket->vendor_name = $request->new_vendor;
                        $selected_vendor             = $request->new_vendor;
                        break;

                    case 'Pengadaan Lembur':
                        $securityticket->vendor_name = $request->new_vendor;
                        $selected_vendor             = $request->new_vendor;
                        break;

                    case 'Perpanjangan':
                        $securityticket->vendor_name = $request->old_vendor;
                        $selected_vendor             = $request->old_vendor;
                        break;

                    case 'Replace':
                        $securityticket->vendor_name = $request->new_vendor;
                        $selected_vendor             = $request->new_vendor;
                        break;
                }
                $securityticket->save();

                // untuk item ppn
                if (isset($request->item_ppn)) {
                    $newPo                     = new Po;
                    $newPo->security_ticket_id = $securityticket->id;
                    $newPo->sender_name        = $selected_vendor;
                    $newPo->send_name          = $securityticket->salespoint->name;
                    $newPo->has_ppn            = true;
                    $newPo->ppn_percentage     = 10;
                    $newPo->save();

                    foreach ($request->item_ppn as $item) {
                        $newPoDetail = new PoDetail;
                        $newPoDetail->po_id            = $newPo->id;
                        $newPoDetail->item_name        = $item['name'];
                        $newPoDetail->item_description = $item['notes'];
                        $newPoDetail->uom              = 'AU';
                        $newPoDetail->qty              = $item['count'];
                        $newPoDetail->item_price       = $item['value'];
                        $newPoDetail->save();
                    }
                }

                // untuk item non ppn
                if (isset($request->item_nonppn)) {
                    $newPo                     = new Po;
                    $newPo->security_ticket_id = $securityticket->id;
                    $newPo->sender_name        = $selected_vendor;
                    $newPo->send_name          = $securityticket->salespoint->name;
                    $newPo->has_ppn            = false;
                    $newPo->save();

                    foreach ($request->item_nonppn as $item) {
                        $newPoDetail = new PoDetail;
                        $newPoDetail->po_id            = $newPo->id;
                        $newPoDetail->item_name        = $item['name'];
                        $newPoDetail->item_description = $item['notes'];
                        $newPoDetail->uom              = 'AU';
                        $newPoDetail->qty              = $item['count'];
                        $newPoDetail->item_price       = $item['value'];
                        $newPoDetail->save();
                    }
                }
                $monitor                        = new SecurityTicketMonitoring;
                $monitor->security_ticket_id    = $securityticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Melakukan Setup PO';
                $monitor->save();
            }

            DB::commit();
            return back()->with('success', 'Berhasil melakukan setting PO. Silahkan melanjutkan penerbitan PO');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan setting PO. Silahkan hubungi developer atau coba kembali');
        }
    }

    public function quickRefresh(Request $request)
    {
        // hapus data pr terkait yang lama di table pr_sap dilanjutkan dengan insert data baru
        $prs = array_unique($request->pr ?? []);
        $pos = array_unique($request->po ?? []);
        if (count($prs) < 1 && count($pos) < 1) {
            return back()->with('error', 'Belum ada nomor PR atau PO yang dapat di refresh');
        }
        try {
            DB::beginTransaction();
            $current_time = now();
            foreach ($prs as $pr) {
                $curl = curl_init();
                switch (config('app.env')) {
                    case 'local':
                        // development
                        // $pr_url = "http://103.111.82.19:8000/sap/bc/zrvpods?sap-client=110&pgmna=zmmr0001";
                        // $pr_url = "http://103.111.82.20:8000/sap/bc/zrvpods?sap-client=200&pgmna=zmmr0001";
                        $pr_url = "http://103.111.82.21:8000/sap/bc/zrvpods?sap-client=300&pgmna=zmmr0001&s_banfn=" . $pr;
                        break;
                    case 'development':
                        //  QAS
                        // $pr_url = "http://103.111.82.20:8000/sap/bc/zrvpods?sap-client=200&pgmna=zmmr0001";
                        $pr_url = "http://103.111.82.21:8000/sap/bc/zrvpods?sap-client=300&pgmna=zmmr0001&s_banfn=" . $pr;
                        break;
                    case 'production':
                        // Production
                        $pr_url = "http://103.111.82.21:8000/sap/bc/zrvpods?sap-client=300&pgmna=zmmr0001&s_banfn=" . $pr;
                        break;
                    default:
                        $pr_url = "";
                        break;
                }
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $pr_url,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_FAILONERROR => true,
                    CURLOPT_HTTPHEADER => array(
                        'Cookie: sap-usercontext=sap-client=110'
                    ),
                ));

                $response = curl_exec($curl);
                if (curl_errno($curl)) {
                    $error_msg = curl_error($curl);
                }
                curl_close($curl);
                if (isset($error_msg)) {
                    throw new \Exception($error_msg);
                } else {
                    $old_pr_data = DB::table('pr_sap')
                        ->where("data", "like", '%' . '"banfn":"' . $pr . '"' . '%')
                        ->delete();
                    $response = json_decode($response);
                    foreach ($response as $key => $item) {
                        DB::table('pr_sap')->insert([
                            [
                                'data' => json_encode($item),
                                'created_at' => $current_time,
                                'updated_at' => $current_time,
                            ]
                        ]);
                    }
                }
            }
            foreach ($pos as $po) {
                $curl = curl_init();
                switch (config('app.env')) {
                    case 'local':
                        // development
                        // $pr_url = "http://103.111.82.19:8000/sap/bc/zrvpods?sap-client=110&pgmna=zmmr0001";
                        // $pr_url = "http://103.111.82.20:8000/sap/bc/zrvpods?sap-client=200&pgmna=zmmr0001";
                        $pr_url = "http://103.111.82.21:8000/sap/bc/zrvpods?sap-client=300&pgmna=zmmr0002&s_ebeln=" . $po;
                        break;
                    case 'development':
                        //  QAS
                        // $pr_url = "http://103.111.82.20:8000/sap/bc/zrvpods?sap-client=200&pgmna=zmmr0001";
                        $pr_url = "http://103.111.82.21:8000/sap/bc/zrvpods?sap-client=300&pgmna=zmmr0002&s_ebeln=" . $po;
                        break;
                    case 'production':
                        // Production
                        $pr_url = "http://103.111.82.21:8000/sap/bc/zrvpods?sap-client=300&pgmna=zmmr0002&s_ebeln=" . $po;
                        break;
                    default:
                        $pr_url = "";
                        break;
                }
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $pr_url,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_FAILONERROR => true,
                    CURLOPT_HTTPHEADER => array(
                        'Cookie: sap-usercontext=sap-client=110'
                    ),
                ));

                $response = curl_exec($curl);
                if (curl_errno($curl)) {
                    $error_msg = curl_error($curl);
                }
                curl_close($curl);
                if (isset($error_msg)) {
                    throw new \Exception($error_msg);
                } else {
                    $old_po_data = DB::table('po_sap')
                        ->where("data", "like", '%' . '"ebeln":"' . $po . '"' . '%')
                        ->delete();
                    $response = json_decode($response);
                    foreach ($response as $key => $item) {
                        DB::table('po_sap')->insert([
                            [
                                'data' => json_encode($item),
                                'created_at' => $current_time,
                                'updated_at' => $current_time,
                            ]
                        ]);
                    }
                }
            }
            DB::commit();
            return back()->with('success', 'Berhasil melakukan quick refresh');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal melakukan quick refresh data. (' . $ex->getMessage() . ')[' . $ex->getLine() . '])');
            DB::rollback();
        }
    }

    public function newsetupPO(Request $request)
    {
        $response = $this->getPoSap($request);
        $json_po_data = $response->getData();
        if ($json_po_data->error) {
            throw new \Exception('failed');
        }
        $po_datas = collect($json_po_data->data);
        $group_by_po_number = $po_datas->groupBy('po_number');
        if ($request->ticket_id) {
            $ticket = Ticket::find($request->ticket_id);
            $salespoint_name = $ticket->salespoint->name;
            $salespoint_address = $ticket->salespoint->address;
        }
        if ($request->armada_ticket_id) {
            $armada_ticket = ArmadaTicket::find($request->armada_ticket_id);
            $salespoint_name = $armada_ticket->salespoint->name;
            $salespoint_address = $armada_ticket->salespoint->address;
        }
        if ($request->security_ticket_id) {
            $security_ticket = SecurityTicket::find($request->security_ticket_id);
            $salespoint_name = $security_ticket->salespoint->name;
            $salespoint_address = $security_ticket->salespoint->address;
        }
        try {
            DB::beginTransaction();
            foreach ($group_by_po_number as $po) {
                $firstitem = $po->first();
                $newPO                          = new Po;
                $newPO->ticket_id               = $request->ticket_id ?? null;
                $newPO->armada_ticket_id        = $request->armada_ticket_id ?? null;
                $newPO->security_ticket_id      = $request->security_ticket_id ?? null;
                $newPO->has_ppn                 = false;
                $newPO->sender_name             = $firstitem->vendor_name;

                // try to get vendor address
                $vendor = Vendor::where('code', $firstitem->vendor)->first();
                $newPO->vendor_code             = $firstitem->vendor ?? null;
                // $newPO->sender_address          = $vendor->address ?? "";
                $vendor_text = ($firstitem->vendor_name ?? "") . "\n" . ($firstitem->vendor_addr ?? "") . "\n" . ($firstitem->vendor_city ?? "");
                $newPO->sender_address          = $vendor_text;

                $newPO->send_name               = $salespoint_name;
                $plant_text = ($firstitem->plant_name_1 ?? "") . "\n" . ($firstitem->plant_name_2 ?? "") . "\n" . ($firstitem->plant_addrs ?? "") . "\n" . ($firstitem->plant_city ?? "");
                $newPO->send_address            = $plant_text;
                if (isset($firstitem->payment_days)) {
                    $newPO->payment_days            = $firstitem->payment_days;
                }
                // $newPO->notes
                $newPO->no_pr_sap               = $firstitem->pr_number;
                $newPO->no_po_sap               = $firstitem->po_number;
                $newPO->created_at              = $firstitem->doc_date;
                $newPO->save();

                foreach ($po as $list) {
                    $podetail = new PoDetail;
                    $podetail->po_id             = $newPO->id;
                    $podetail->item_number       = $list->item_po;
                    $podetail->item_name         = $list->material_short_text;
                    $podetail->item_description  = $list->item_text_po ?? "";
                    // $podetail->ticket_item_id    = $list->ticket_item_id;
                    $podetail->qty               = $list->scheduled_qty_requested;
                    $podetail->uom               = $list->order_unit;
                    $podetail->item_price        = $list->net_order_price / $list->price_unit * 100;
                    $podetail->delivery_notes    = $list->delv_date . " = " . $podetail->qty . " " . $podetail->uom;
                    $podetail->save();
                }
            }
            DB::commit();
            return redirect('/po')->with('success', 'Berhasil melakukan setting PO. Silahkan melanjutkan penerbitan PO');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan setting PO. Silahkan hubungi developer atau coba kembali');
        }
    }

    public function revisePOData(Request $request)
    {
        $po = Po::where('no_po_sap', $request->po_number)->first();
        if ($po->updated_at != $request->updated_at) {
            return back()->with('error', 'Data telah terupdate');
        }
        $po->status = -1;
        $po->save();

        return back()->with('success', 'Silahkan melakukan Revisi Data');
    }

    public function revisePO(Request $request)
    {
        try {
            DB::beginTransaction();
            switch ($request->type) {
                case 'barangjasa':
                    $ticket = Ticket::find($request->id);
                    // if($ticket->status() != "Menunggu proses PO & Penerimaan Barang"){
                    //     return back()->with('error','Gagal Melakukan Revisi PO');
                    // }
                    break;

                case 'armada':
                    $ticket = ArmadaTicket::find($request->id);
                    // if($ticket->status() != "Menunggu proses PO"){
                    //     return back()->with('error','Gagal Melakukan Revisi PO');
                    // }
                    break;

                case 'security':
                    $ticket = SecurityTicket::find($request->id);
                    // if($ticket->status() != "Menunggu proses PO"){
                    //     return back()->with('error','Gagal Melakukan Revisi PO Security');
                    // }
                    break;

                default:
                    throw new \Exception('Tipe ticket ditemukan');
                    break;
            }
            if (!$ticket) {
                throw new \Exception('Ticket tidak ditemukan');
            }

            foreach ($ticket->po as $po) {
                $po->po_authorization()->delete();
                $po->po_detail()->delete();
                $po->delete();
            }
            DB::commit();
            return redirect("/po")->with("success", "Revisi PO berhasil silahkan mengulang setup PO");
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', "Gagal Melakukan Revisi PO (" . $ex->getMessage() . ")");
        }
    }

    public function old_submitPO(Request $request)
    {
        try {
            DB::beginTransaction();
            $po = Po::findOrFail($request->po_id);
            if ($po->status != -1) {
                throw new \Exception("PO sudah di proses sebelumnya oleh " . $po->created_by_employee->name . ' pada ' . $po->created_at->translatedFormat('d F Y (H:i)'));
            } else {
                $existing_po = Po::where('no_po_sap', $request->no_po_sap)->first();
                $existing_pr = Po::where('no_pr_sap', $request->no_pr_sap)->first();
                // re check on po manual
                if (!$existing_po) {
                    $existing_po = PoManual::where('po_number', $request->no_po_sap)->first();
                }

                if ($existing_pr) {
                    throw new \Exception('Nomor PR SAP ' . $request->no_pr_sap . 'telah sebelumnya di input di kode pengadaan ' . $existing_pr->ticket->code);
                }

                if ($existing_po) {
                    throw new \Exception('Nomor PO SAP ' . $request->no_po_sap . 'telah sebelumnya di input di kode pengadaan ' . $existing_po->ticket->code);
                }

                $po->sender_address         = $request->sender_address;
                $po->send_address           = $request->send_address;
                $po->payment_days           = $request->payment_days;
                $po->no_pr_sap              = $request->no_pr_sap;
                $po->no_po_sap              = $request->no_po_sap;
                $po->supplier_pic_name      = $request->supplier_pic_name;
                $po->supplier_pic_position  = $request->supplier_pic_position;
                $po->notes                  = $request->notes;
                $po->start_date             = $request->start_date;
                $po->end_date               = $request->end_date;
                $po->created_by             = Auth::user()->id;
                $po->status                 = 0;
                $po->save();

                foreach ($request->po_detail as $po_detail) {
                    $detail = PoDetail::findOrFail($po_detail['id']);
                    $detail->delivery_notes = $po_detail['delivery_notes'];
                    $detail->save();
                }

                foreach ($po->po_authorization as $author) {
                    $author->delete();
                }
                $authorization = Authorization::findOrFail($request->authorization_id);
                foreach ($authorization->authorization_detail as $authorization) {
                    $po_authorization                       = new PoAuthorization;
                    $po_authorization->po_id                = $po->id;
                    $po_authorization->employee_id          = $authorization->employee_id;
                    $po_authorization->employee_name        = $authorization->employee->name;
                    $po_authorization->as                   = $authorization->sign_as;
                    $po_authorization->employee_position    = $authorization->employee_position->name;
                    $po_authorization->level                = $authorization->level;
                    $po_authorization->save();
                }
            }

            if ($po->ticket_id != null) {
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $po->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Menerbitkan PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->armada_ticket_id != null) {
                $monitor = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $po->armada_ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Menerbitkan PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->security_ticket_id != null) {
                $monitor = new SecurityTicketMonitoring;
                $monitor->security_ticket_id      = $po->security_ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Menerbitkan PO ' . $po->no_po_sap;
                $monitor->save();
            }
            DB::commit();
            return back()->with('success', 'Berhasil Menerbitkan PO');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Menerbitkan PO ' . $ex->getMessage());
        }
    }

    public function submitPO(Request $request)
    {
        try {
            DB::beginTransaction();
            $po = Po::findOrFail($request->po_id);
            if ($po->status != -1) {
                throw new \Exception("PO sudah di proses sebelumnya oleh " . $po->created_by_employee->name . ' pada ' . $po->created_at->translatedFormat('d F Y (H:i)'));
            }
            if ($request->has_reminder) {
                if (Carbon::parse($request->start_date) > Carbon::parse($request->end_date)) {
                    throw new \Exception('Start date harus sebelum atau sama dengan end date');
                }
            }
            $po->sender_address         = $request->sender_address;
            $po->send_address           = $request->send_address;
            // $po->payment_days           = $request->payment_days;
            $po->supplier_pic_name      = $request->supplier_pic_name;
            $po->supplier_pic_position  = $request->supplier_pic_position;
            $po->has_ppn                = ($request->has_ppn) ? true : false;
            $po->ppn_percentage         = $request->ppn_percentage ?? null;
            $po->notes                  = $request->notes;
            if ($request->has_reminder) {
                $po->start_date     = $request->start_date;
                $po->end_date       = $request->end_date;
            } else {
                $po->start_date     = null;
                $po->end_date       = null;
            }
            $po->created_by             = Auth::user()->id;
            $po->status                 = 0;
            $po->save();

            // foreach($request->po_detail as $po_detail){
            //     $detail = PoDetail::findOrFail($po_detail['id']);
            //     $detail->delivery_notes = $po_detail['delivery_notes'];
            //     $detail->save();
            // }

            foreach ($po->po_authorization as $author) {
                $author->delete();
            }
            $authorization = Authorization::findOrFail($request->authorization_id);
            foreach ($authorization->authorization_detail as $authorization) {
                $po_authorization                       = new PoAuthorization;
                $po_authorization->po_id                = $po->id;
                $po_authorization->employee_id          = $authorization->employee_id;
                $po_authorization->employee_name        = $authorization->employee->name;
                $po_authorization->as                   = $authorization->sign_as;
                $po_authorization->employee_position    = $authorization->employee_position->name;
                $po_authorization->level                = $authorization->level;
                $po_authorization->save();
            }

            if ($po->ticket_id != null) {
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $po->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Menerbitkan PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->armada_ticket_id != null) {
                $monitor = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $po->armada_ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Menerbitkan PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->security_ticket_id != null) {
                $monitor = new SecurityTicketMonitoring;
                $monitor->security_ticket_id      = $po->security_ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Menerbitkan PO ' . $po->no_po_sap;
                $monitor->save();
            }
            DB::commit();
            return back()->with('success', 'Berhasil Menerbitkan PO');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Menerbitkan PO ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }

    public function printPO(Request $request)
    {
        try {
            $po = Po::where('no_po_sap', $request->input('code'))->first();
            if (!$po) {
                throw new \Exception('PO tidak ditemukan');
            }
            $pdf = PDF::loadView('pdf.popdf', compact('po'))->setPaper('legal', 'portrait');
            return $pdf->stream('PO (' . $po->no_po_sap . ').pdf');
            // return $pdf->download('invoice.pdf');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal Mencetak PO ' . $ex->getMessage() . $ex->getLine());
        }
    }

    public function cancelVendorConfirmation(Request $request)
    {
        try {
            DB::beginTransaction();
            $po                       = Po::findOrFail($request->id);
            $po_upload_request        = $po->po_upload_request;

            $po->status               = 3;
            $po->po_upload_request_id = null;
            $po->save();
            $po_upload_request->delete();

            $isComplete = false;
            if ($po->ticket_id != null) {
            }
            if ($po->armada_ticket_id != null) {
                $armada_ticket              = $po->armada_ticket;
                $armada_ticket->po_number   = $po->no_po_sap;
                // untuk kasus setelah revisi po kalau pengadaan udah selesai gausah reset pengadaan ulang
                if ($armada_ticket->status < 5) {
                    $armada_ticket->status      = 5;
                }
                $armada_ticket->save();

                // START CASE
                // Jika armada perpanjangan, setelah confirm PO langsung otomatis verifikasi PO (tanpa harus dari area)
                if ($armada_ticket->type() == "Perpanjangan") {
                    $newrequest = new Request;
                    $newrequest->replace([
                        'armada_ticket_id' => $armada_ticket->id,
                    ]);
                    $response = app('App\Http\Controllers\Operational\ArmadaTicketingController')->verifyPO($newrequest, "api");
                    $isComplete = true;
                }
                // END CASE
            }
            if ($po->security_ticket_id != null) {
                $security_ticket              = $po->security_ticket;
                $security_ticket->po_number   = $po->no_po_sap;

                // cek status seluruh po apakah sudah selesai
                if ($security_ticket->po->where('status', '<', 3)->count() == 0) {
                    if ($security_ticket->status < 5) {
                        $security_ticket->status      = 5;
                    }
                    $security_ticket->save();
                    // START CASE
                    // Jika security perpanjangan, setelah confirm PO langsung otomatis selesai pengadaan (tanpa LPB Area)
                    if ($security_ticket->type() == "Perpanjangan") {
                        $newrequest = new Request;
                        $newrequest->replace([
                            'security_ticket_id' => $security_ticket->id,
                        ]);
                        $response = app('App\Http\Controllers\Operational\SecurityTicketingController')->uploadSecurityLPB($newrequest, "api");
                        $isComplete = true;
                    }
                    // END CASE
                }
            }
            DB::commit();

            return back()->with('success', 'Berhasil Cancel Konfirmasi Vendor');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Cancel Konfirmasi Vendor' . $ex->getMessage() . $ex->getLine());
        }
    }

    public function confirmVendorConfirmation(Request $request)
    {
        try {
            DB::beginTransaction();
            $po                       = Po::findOrFail($request->id);
            $po->status               = 0;
            $po->save();

            DB::commit();
            return back()->with('success', 'Berhasil Confirm Konfirmasi Vendor, Silahkan Upload FIle Ulang dan Ceklis Konfirmasi Vendor');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Upload File ' . $ex->getMessage() . $ex->getLine());
        }
    }

    public function uploadInternalSignedFile(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $po = Po::findOrFail($request->po_id);
            if ($po->ticket_id != null) {
                $ticket = $po->ticket;
                $type = 'barangjasa';
            }
            if ($po->armada_ticket_id != null) {
                $ticket = $po->armada_ticket;
                $type = 'armada';
            }
            if ($po->security_ticket_id != null) {
                $ticket = $po->security_ticket;
                $type = 'security';
            }
            $salespointname = str_replace(' ', '_', $ticket->salespoint->name);
            $ext = pathinfo($request->file('internal_signed_file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = $po->no_po_sap . "_INTERNAL_SIGNED_" . $salespointname . '.' . $ext;
            $path = "/attachments/ticketing/" . $type . "/" . $ticket->code . '/po/' . $name;
            $file = pathinfo($path);
            $path = $request->file('internal_signed_file')->storeAs($file['dirname'], $file['basename'], 'public');
            $po->internal_signed_filepath = $path;
            $po->save();

            if ($request->needVendorConfirmation) {
                $po_upload_request               = new POUploadRequest;
                $po_upload_request->id           = (string) Str::uuid();
                $po_upload_request->po_id        = $po->id;
                if (strtolower(trim($po->sender_name)) == 'one time vendor') {
                    $po_upload_request->vendor_name  = $po->sender_address;
                } else {
                    $po_upload_request->vendor_name  = $po->sender_name;
                }
                $po_upload_request->vendor_pic   = $po->supplier_pic_name ?? "";
                $po_upload_request->save();

                $po->po_upload_request_id = $po_upload_request->id;
                $po->save();
            }

            if (count($this->email_text_to_array($request->email)) < 1) {
                throw new \Exception("Minimal satu email yang dibutuhkan untuk mengirim PO (" . $request->email . ")");
            }
            $mail_to = $this->email_text_to_array($request->email);
            $ccs = $this->email_text_to_array($request->cc);
            $mail_subject = $request->mail_subject;
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'po' => $po,
                'mail' => $mail_to,
                'mail_subject' => $mail_subject,
                'email_text' => $request->email_text,
                'needVendorConfirmation' => $request->needVendorConfirmation,
                'po_upload_request' => ($request->needVendorConfirmation) ? $po_upload_request : null,
                'url' => ($request->needVendorConfirmation) ? url('/signpo/' . $po_upload_request->id) : null,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new POMail($data, 'posignedrequest'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            $po->status = 1;
            $po->last_mail_send_to  = $request->email;
            $po->last_mail_cc_to    = $request->cc;
            $po->last_mail_text     = $request->email_text;
            $po->last_mail_subject  = $request->mail_subject;
            $po->save();

            if ($po->ticket_id != null) {
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $po->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Upload Internal Signed PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->armada_ticket_id != null) {
                $monitor = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $po->armada_ticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Upload Internal Signed PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->security_ticket_id != null) {
                $monitor = new SecurityTicketMonitoring;
                $monitor->security_ticket_id      = $po->security_ticket->id;
                $monitor->employee_id             = Auth::user()->id;
                $monitor->employee_name           = Auth::user()->name;
                $monitor->message                 = 'Upload Internal Signed PO ' . $po->no_po_sap;
                $monitor->save();
            }

            // Start case (Tidak butuh konfirmasi vendor)
            if (!$request->needVendorConfirmation) {
                $po->status = 3;
                $po->save();
                $isComplete = false;
                if ($po->ticket_id != null) {
                }
                if ($po->armada_ticket_id != null) {
                    $armada_ticket              = $po->armada_ticket;
                    $armada_ticket->po_number   = $po->no_po_sap;
                    // untuk kasus setelah revisi po kalau pengadaan udah selesai gausah reset pengadaan ulang
                    if ($armada_ticket->status < 5) {
                        $armada_ticket->status      = 5;
                    }
                    $armada_ticket->save();

                    // START CASE
                    // Jika armada perpanjangan, setelah confirm PO langsung otomatis verifikasi PO (tanpa harus dari area)
                    if ($armada_ticket->type() == "Perpanjangan") {
                        $newrequest = new Request;
                        $newrequest->replace([
                            'armada_ticket_id' => $armada_ticket->id,
                        ]);
                        $response = app('App\Http\Controllers\Operational\ArmadaTicketingController')->verifyPO($newrequest, "api");
                        $isComplete = true;
                    }
                    // END CASE
                }
                if ($po->security_ticket_id != null) {
                    $security_ticket              = $po->security_ticket;
                    $security_ticket->po_number   = $po->no_po_sap;

                    // cek status seluruh po apakah sudah selesai
                    if ($security_ticket->po->where('status', '<', 3)->count() == 0) {
                        if ($security_ticket->status < 5) {
                            $security_ticket->status      = 5;
                        }
                        $security_ticket->save();
                        // START CASE
                        // Jika security perpanjangan, setelah confirm PO langsung otomatis selesai pengadaan (tanpa LPB Area)
                        if ($security_ticket->type() == "Perpanjangan") {
                            $newrequest = new Request;
                            $newrequest->replace([
                                'security_ticket_id' => $security_ticket->id,
                            ]);
                            $response = app('App\Http\Controllers\Operational\SecurityTicketingController')->uploadSecurityLPB($newrequest, "api");
                            $isComplete = true;
                        }
                        // END CASE
                    }

                    $monitor                          = new SecurityTicketMonitoring;
                    $monitor->security_ticket_id      = $po->security_ticket->id;
                    $monitor->employee_id             = Auth::user()->id;
                    $monitor->employee_name           = Auth::user()->name;
                    $monitor->message                 = 'Upload File Internal Signed untuk PO ' . $po->no_po_sap;
                    $monitor->save();
                }
            }
            DB::commit();
            if ($po->status == 1) {
                // bug implode on server
                return back()->with('success', 'Berhasil Upload File Internal Signed untuk PO ' . $po->no_po_sap . ' File sudah dikirimkan ke email supplier (' . implode(",", $mail_to) . ') untuk ditandatangan' . $emailmessage);
                // return back()->with('success','Berhasil Upload File Intenal Signed untuk PO '.$po->no_po_sap.' File sudah dikirimkan ke email supplier untuk ditandatangan'.$emailmessage);
            }
            if ($po->status == 3) {
                return back()->with('success', 'Berhasil Upload File Intenal Signed untuk PO ' . $po->no_po_sap . $emailmessage);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Upload File ' . $ex->getMessage() . $ex->getLine());
        }
    }

    public function confirmPosigned(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $po = Po::findOrFail($request->po_id);
            $po->status = 3;

            $po_upload_request = $po->po_upload_request;
            $po_upload_request->status = 2;
            $po_upload_request->save();

            $po->external_signed_filepath = $po_upload_request->filepath;
            $po->save();

            // order purchase API E Log
            if ($po->armada_ticket_id != null) {
                $armadatickettt = $po->armada_ticket;
                $check_vendor_e_log_sync = Vendor::where('code', '=', $po->vendor_code)->first()->e_log_sync;


                if ($armadatickettt != null && $armadatickettt->ticketing_type == 0 && $check_vendor_e_log_sync == 1) {
                    $external_id = $po->no_po_sap;
                    $submission_date  = $po->created_at->format('Y-m-d');
                    $start_date  = $po->start_date;
                    $end_date  = $po->end_date;
                    $salespoint_code  = Salespoint::where('name', '=', $po->send_name)->first()->code;
                    $vehicle_model_id  = ArmadaTicket::where('id', '=', $po->armada_ticket_id)->first()->armada_type_id;
                    $vendor_code  = $po->vendor_code;
                    $external_id = $po->no_po_sap;

                    $vehicle_price = PoDetail::where('po_id', '=', $po->id)
                        ->where('item_name', 'not like', '%Prorate%')
                        ->first()->item_price;

                    $qty_vehicle_price = PoDetail::where('po_id', '=', $po->id)
                        ->where('item_name', 'not like', '%Prorate%')
                        ->first()->qty;

                    $quantity_month = $qty_vehicle_price;

                    $subtotal_vehicle_price = $vehicle_price * $qty_vehicle_price;
                    $count_additional_price = PoDetail::where('po_id', '=', $po->id)->where('item_name', 'like', '%Prorate%')->get()->count();
                    $additional_price = 0;

                    if ($count_additional_price > 0) {
                        $additional_price = PoDetail::where('po_id', '=', $po->id)
                            ->where('item_name', 'like', '%Prorate%')
                            ->first()->item_price;

                        $qty_additional_price = PoDetail::where('po_id', '=', $po->id)
                            ->where('item_name', 'like', '%Prorate%')
                            ->first()->qty;

                        $subtotal_additional_price = $additional_price * $qty_additional_price;

                        $subtotal_price = $subtotal_vehicle_price + $subtotal_additional_price;
                    } else {
                        $additional_price = 0;
                        $subtotal_price = $subtotal_vehicle_price;
                    }

                    if ($po->has_ppn) {
                        $ppn = ($po->ppn_percentage / 100) * $subtotal_price;
                        $ppn = round($ppn);
                    } else {
                        $ppn = 0;
                    }

                    $total_price = $subtotal_price + $ppn;
                    $order_note  = 'Create Order Purchase From PODS. PO = ' . $external_id;

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://dev-api.elog.co.id/openapi/pma/v1/rent/order',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => "external_id=$external_id&submission_date=$submission_date&start_date=$start_date&end_date=$end_date&quantity_month=$quantity_month&salespoint_code=$salespoint_code&vehicle_model_id=$vehicle_model_id&vendor_code=$vendor_code&vehicle_price=$vehicle_price&additional_price=$additional_price&subtotal_price=$subtotal_price&ppn=$ppn&total_price=$total_price&order_note=$order_note",
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded',
                            'Authorization: Basic cG1hLWVsb2ctZmFyaGFuOlhYQzEyMTI5OA=='
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);
                    // dd($response);
                }

                // Get Order Rent By External_ID API E Log
                $armadatickettt = $po->armada_ticket;
                if ($armadatickettt != null) {
                    $external_id  = $armadatickettt->po_reference_number;
                } else {
                    $external_id  = 0;
                }
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://dev-api.elog.co.id/openapi/pma/v1/rent/order/$external_id",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Basic cG1hLWVsb2ctZmFyaGFuOlhYQzEyMTI5OA=='
                    ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                $response = json_decode($response, true);
                // dd($response);

                // Perpanjangan API E Log

                if ($response['status'] == 'success' && $armadatickettt != null && $armadatickettt->ticketing_type == 1 && $armadatickettt->perpanjangan_form != null && $armadatickettt->perpanjangan_form->form_type == 'perpanjangan' && $check_vendor_e_log_sync == 1) {
                    $type = 0;
                    $start_date  = $po->start_date;
                    $end_date  = $po->end_date;

                    $vehicle_price = PoDetail::where('po_id', '=', $po->id)
                        ->where('item_name', 'not like', '%Prorate%')
                        ->first()->item_price;

                    $qty_vehicle_price = PoDetail::where('po_id', '=', $po->id)
                        ->where('item_name', 'not like', '%Prorate%')
                        ->first()->qty;

                    $quantity_month = $qty_vehicle_price;
                    $subtotal_vehicle_price = $vehicle_price * $qty_vehicle_price;
                    $count_additional_price = PoDetail::where('po_id', '=', $po->id)->where('item_name', 'like', '%Prorate%')->get()->count();
                    $additional_price = 0;

                    if ($count_additional_price > 0) {
                        $additional_price = PoDetail::where('po_id', '=', $po->id)
                            ->where('item_name', 'like', '%Prorate%')
                            ->first()->item_price;

                        $qty_additional_price = PoDetail::where('po_id', '=', $po->id)
                            ->where('item_name', 'like', '%Prorate%')
                            ->first()->qty;

                        $subtotal_additional_price = $additional_price * $qty_additional_price;

                        $subtotal_price = $subtotal_vehicle_price + $subtotal_additional_price;
                    } else {
                        $additional_price = 0;
                        $subtotal_price = $subtotal_vehicle_price;
                    }

                    if ($po->has_ppn) {
                        $ppn = ($po->ppn_percentage / 100) * $subtotal_price;
                        $ppn = round($ppn);
                    } else {
                        $ppn = 0;
                    }

                    $total_price = $subtotal_price + $ppn;
                    $previous_external_id = $armadatickettt->po_reference_number;
                    $external_id = $po->no_po_sap;

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://dev-api.elog.co.id/openapi/pma/v1/rent/order/$previous_external_id/contract",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'PUT',
                        CURLOPT_POSTFIELDS => "type=$type&start_date=$start_date&end_date=$end_date&quantity_month=$quantity_month&vehicle_price=$vehicle_price&additional_price=$additional_price&subtotal_price=$subtotal_price&ppn=$ppn&total_price=$total_price&external_id=$external_id",
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded',
                            'Authorization: Basic cG1hLWVsb2ctZmFyaGFuOlhYQzEyMTI5OA=='
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);
                    // dd($response);
                }

                // Renewal API E Log

                elseif ($response['status'] == 'success' && $armadatickettt != null && $armadatickettt->ticketing_type == 1 && $armadatickettt->perpanjangan_form != null && $armadatickettt->perpanjangan_form->stopsewa_reason == 'renewal' && $check_vendor_e_log_sync == 1) {
                    $previous_external_id = $armadatickettt->po_reference_number;
                    $external_id = $po->no_po_sap;
                    $submission_date = $po->created_at;
                    $start_date  = $po->start_date;
                    $end_date  = $po->end_date;

                    $vehicle_price = PoDetail::where('po_id', '=', $po->id)
                        ->where('item_name', 'not like', '%Prorate%')
                        ->first()->item_price;

                    $qty_vehicle_price = PoDetail::where('po_id', '=', $po->id)
                        ->where('item_name', 'not like', '%Prorate%')
                        ->first()->qty;

                    $quantity_month = $qty_vehicle_price;
                    $subtotal_vehicle_price = $vehicle_price * $qty_vehicle_price;
                    $count_additional_price = PoDetail::where('po_id', '=', $po->id)->where('item_name', 'like', '%Prorate%')->get()->count();
                    $additional_price = 0;

                    if ($count_additional_price > 0) {
                        $additional_price = PoDetail::where('po_id', '=', $po->id)
                            ->where('item_name', 'like', '%Prorate%')
                            ->first()->item_price;

                        $qty_additional_price = PoDetail::where('po_id', '=', $po->id)
                            ->where('item_name', 'like', '%Prorate%')
                            ->first()->qty;

                        $subtotal_additional_price = $additional_price * $qty_additional_price;

                        $subtotal_price = $subtotal_vehicle_price + $subtotal_additional_price;
                    } else {
                        $additional_price = 0;
                        $subtotal_price = $subtotal_vehicle_price;
                    }

                    if ($po->has_ppn) {
                        $ppn = ($po->ppn_percentage / 100) * $subtotal_price;
                        $ppn = round($ppn);
                    } else {
                        $ppn = 0;
                    }

                    $total_price = $subtotal_price + $ppn;
                    $stop_date = $armadatickettt->perpanjangan_form->stopsewa_date;
                    $order_note  = 'Renewal dari PO ' . $previous_external_id;

                    $get_delivery_notes_po_detail = PoDetail::where('po_id', '=', $po->id)
                        ->where('item_name', 'not like', '%Prorate%')
                        ->first()->delivery_notes;

                    preg_match_all('/\d{4}-\d{2}-\d{2}/', $get_delivery_notes_po_detail, $matches, PREG_SET_ORDER, 0);

                    $check_ETA_exists = [];
                    if (isset($matches[0])) {
                        $check_ETA_exists = DB::table('po_sap_e_log')
                            ->where("data", "like", '%' . '"ebeln":"' . $po->no_po_sap . '"' . '%')
                            ->where("date_eta",  '>', $matches[0][0])
                            ->first();

                        if ($check_ETA_exists) {
                            $check_ETA_exists = $check_ETA_exists->date_eta;
                        }
                    }

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://dev-api.elog.co.id/openapi/pma/v1/rent/order/renewal',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => "previous_external_id=$previous_external_id&external_id=$external_id&submission_date=$submission_date&start_date=$start_date&end_date=$end_date&quantity_month=$quantity_month&stop_date=$stop_date&vehicle_price=$vehicle_price&additional_price=$additional_price&subtotal_price=$subtotal_price&ppn=$ppn&total_price=$total_price&order_note=$order_note",
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded',
                            'Authorization: Basic cG1hLWVsb2ctZmFyaGFuOlhYQzEyMTI5OA=='
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);
                    // dd($response);

                    // Update Contract Estimation Return Date E Log

                    if ($check_ETA_exists) {
                        $previous_external_id = $armadatickettt->po_reference_number;
                        $before_date = $matches[0][0];
                        $after_date = $check_ETA_exists;

                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => "https://dev-api.elog.co.id/openapi/pma/v1/rent/order/$previous_external_id/estimate_date",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'PUT',
                            CURLOPT_POSTFIELDS => "before_date=$before_date&after_date=$after_date",
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/x-www-form-urlencoded',
                                'Authorization: Basic cG1hLWVsb2ctZmFyaGFuOlhYQzEyMTI5OA=='
                            ),
                        ));

                        $response = curl_exec($curl);
                        curl_close($curl);
                        // dd($response);
                    }
                }

                // Stop Sewa API E Log (GX JADI DI PAKE)

                // elseif ($response['status'] == 'success' && $armadatickettt->ticketing_type == 1 && $armadatickettt->perpanjangan_form != null && $armadatickettt->perpanjangan_form->stopsewa_reason == 'end' && $check_vendor_e_log_sync == 1) {
                //     $type  = 1;
                //     $stop_date  = $armadatickettt->perpanjangan_form->stopsewa_date;
                //     $previous_external_id = $armadatickettt->po_reference_number;

                //     $curl = curl_init();
                //     curl_setopt_array($curl, array(
                //         CURLOPT_URL => "https://dev-api.elog.co.id/openapi/pma/v1/rent/order/$previous_external_id/contract",
                //         CURLOPT_RETURNTRANSFER => true,
                //         CURLOPT_ENCODING => '',
                //         CURLOPT_MAXREDIRS => 10,
                //         CURLOPT_TIMEOUT => 0,
                //         CURLOPT_FOLLOWLOCATION => true,
                //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //         CURLOPT_CUSTOMREQUEST => 'PUT',
                //         CURLOPT_POSTFIELDS => "type=$type&stop_date=$stop_date",
                //         CURLOPT_HTTPHEADER => array(
                //             'Content-Type: application/x-www-form-urlencoded',
                //             'Authorization: Basic cG1hLWVsb2ctZmFyaGFuOlhYQzEyMTI5OA=='
                //         ),
                //     ));

                //     $response = curl_exec($curl);
                //     curl_close($curl);
                //     // dd($response);
                // }

                // Mutasi API E Log

                elseif ($response['status'] == 'success' && $armadatickettt != null && $armadatickettt->ticketing_type == 2 && $armadatickettt->mutasi_form != null && $check_vendor_e_log_sync == 1) {
                    $start_date  = $armadatickettt->mutasi_form->mutation_date;
                    $start_date2 = date_create($start_date);
                    $mutation_date = date_format($start_date2, "m-d-Y");

                    $end_date  = $armadatickettt->mutasi_form->received_date;
                    $end_date2 = date_create($end_date);
                    $received_date = date_format($end_date2, "m-d-Y");

                    $origin_salespoint_code  = Salespoint::where('id', '=', $armadatickettt->mutasi_form->salespoint_id)->first()->code;
                    $destination_salespoint_code  = Salespoint::where('id', '=', $armadatickettt->mutasi_form->receiver_salespoint_id)->first()->code;
                    $external_id = $po->no_po_sap;
                    $previous_external_id = $armadatickettt->po_reference_number;

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://dev-api.elog.co.id/openapi/pma/v1/rent/order/$previous_external_id/mutation",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => "mutation_date=$mutation_date&received_date=$received_date&origin_salespoint_code=$origin_salespoint_code&destination_salespoint_code=$destination_salespoint_code&external_id=$external_id",
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded',
                            'Authorization: Basic cG1hLWVsb2ctZmFyaGFuOlhYQzEyMTI5OA=='
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);
                    // dd($response);

                    // Update Contract Pricing After Mutation by Vendor API E Log

                    if (strpos(strtolower($po->notes), "sewa") !== false) {
                        $vehicle_price = PoDetail::where('po_id', '=', $po->id)
                            ->where('item_name', 'not like', '%Prorate%')
                            ->first()->item_price;

                        $qty_vehicle_price = PoDetail::where('po_id', '=', $po->id)
                            ->where('item_name', 'not like', '%Prorate%')
                            ->first()->qty;

                        $subtotal_vehicle_price = $vehicle_price * $qty_vehicle_price;
                        $count_additional_price = PoDetail::where('po_id', '=', $po->id)->where('item_name', 'like', '%Prorate%')->get()->count();
                        $additional_price = 0;

                        if ($count_additional_price > 0) {
                            $additional_price = PoDetail::where('po_id', '=', $po->id)
                                ->where('item_name', 'like', '%Prorate%')
                                ->first()->item_price;

                            $qty_additional_price = PoDetail::where('po_id', '=', $po->id)
                                ->where('item_name', 'like', '%Prorate%')
                                ->first()->qty;

                            $subtotal_additional_price = $additional_price * $qty_additional_price;
                            $subtotal_price = $subtotal_vehicle_price + $subtotal_additional_price;
                        } else {
                            $additional_price = 0;
                            $subtotal_price = $subtotal_vehicle_price;
                        }

                        if ($po->has_ppn) {
                            $ppn = ($po->ppn_percentage / 100) * $subtotal_price;
                            $ppn = round($ppn);
                        } else {
                            $ppn = 0;
                        }

                        $total_price = $subtotal_price + $ppn;

                        $price_po_notes = strtolower(str_replace('.', '', $po->notes));
                        preg_match('/sewa.*$/m', $price_po_notes, $before_price, PREG_OFFSET_CAPTURE, 0);
                        preg_match('/\d+/m', $before_price[0][0], $before_price, PREG_OFFSET_CAPTURE, 0);

                        $before_subtotal_price = $before_price[0][0] * $qty_vehicle_price;

                        if ($po->has_ppn) {
                            $before_ppn = ($po->ppn_percentage / 100) * $before_subtotal_price;
                            $before_ppn = round($before_ppn);
                        } else {
                            $before_ppn = 0;
                        }

                        $before_total_price = $before_subtotal_price + $before_ppn;
                        $previous_external_id = $armadatickettt->po_reference_number;

                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => "https://dev-api.elog.co.id/openapi/pma/v1/rent/order/$previous_external_id/pricing",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'PUT',
                            CURLOPT_POSTFIELDS => "before_subtotal_price=$before_subtotal_price&before_ppn=$before_ppn&before_total_price=$before_total_price&after_subtotal_price=$subtotal_price&after_ppn=$ppn&after_total_price=$total_price",
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/x-www-form-urlencoded',
                                'Authorization: Basic cG1hLWVsb2ctZmFyaGFuOlhYQzEyMTI5OA=='
                            ),
                        ));

                        $response = curl_exec($curl);
                        curl_close($curl);
                        // dd($response);
                    }
                }
            }

            // send email back to supplier and salespoint
            if ($po->ticket_id != null) {
                $salespoint_id = $po->ticket->salespoint_id;
            }
            if ($po->armada_ticket_id != null) {
                $salespoint_id = $po->armada_ticket->salespoint_id;
            }
            if ($po->security_ticket_id != null) {
                $salespoint_id = $po->security_ticket->salespoint_id;
            }
            $access = EmployeeLocationAccess::where('salespoint_id', $salespoint_id)->get();
            $employee_salespoint_ids = $access->pluck('employee_id')->unique();
            $employee_emails = [];
            foreach ($employee_salespoint_ids as $id) {
                $email = Employee::find($id)->email;
                array_push($employee_emails, $email);
            }
            $isComplete = false;
            if ($po->ticket_id != null) {
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $po->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Konfirmasi tanda tangan supplier PO ' . $po->no_po_sap;
                $monitor->save();

                // untuk case cit dan pest control punya po reference , ubah status ke closed;
                if ($po->ticket->po_reference_number) {
                    $po_reference = $po->ticket->po_reference;
                    $po_reference->status = 4;
                    $po_reference->save();
                }

                // Check If Perpanjangan Jasa Lainnya, Skip Upload and Status Done
                $ticket_item = TicketItem::where('ticket_id', $po->ticket->id)->get();
                foreach ($ticket_item as $item) {
                    if (str_contains($item->name, 'CIT')) {
                        $ticket = Ticket::where('id', $item->ticket_id)->first();
                        if ($ticket->request_type == 3) {
                            $ticket->finished_date = date('Y-m-d');
                            $ticket->status = 7;
                            $ticket->save();

                            $monitor = new TicketMonitoring;
                            $monitor->ticket_id      = $po->ticket->id;
                            $monitor->employee_id    = Auth::user()->id;
                            $monitor->employee_name  = Auth::user()->name;
                            $monitor->message        = 'Melakukan Verifikasi PO';
                            $monitor->save();

                            $isComplete = true;
                        }
                    } elseif (str_contains($item->name, 'Pest Control')) {
                        $ticket = Ticket::where('id', $item->ticket_id)->first();
                        if ($ticket->request_type == 3) {
                            $ticket->finished_date = date('Y-m-d');
                            $ticket->status = 7;
                            $ticket->save();

                            $monitor = new TicketMonitoring;
                            $monitor->ticket_id      = $po->ticket->id;
                            $monitor->employee_id    = Auth::user()->id;
                            $monitor->employee_name  = Auth::user()->name;
                            $monitor->message        = 'Melakukan Verifikasi PO';
                            $monitor->save();

                            $isComplete = true;
                        }
                    } elseif (str_contains($item->name, 'Merchandiser')) {
                        $ticket = Ticket::where('id', $item->ticket_id)->first();
                        if ($ticket->request_type == 3) {
                            $ticket->finished_date = date('Y-m-d');
                            $ticket->status = 7;
                            $ticket->save();

                            $monitor = new TicketMonitoring;
                            $monitor->ticket_id      = $po->ticket->id;
                            $monitor->employee_id    = Auth::user()->id;
                            $monitor->employee_name  = Auth::user()->name;
                            $monitor->message        = 'Melakukan Verifikasi PO';
                            $monitor->save();

                            $isComplete = true;
                        }
                    }
                }
            }
            if ($po->armada_ticket_id != null) {
                $armada_ticket              = $po->armada_ticket;
                $armada_ticket->po_number   = $po->no_po_sap;
                // untuk kasus setelah revisi po kalau pengadaan udah selesai gausah reset pengadaan ulang
                if ($armada_ticket->status < 5) {
                    $armada_ticket->status      = 5;
                }
                $armada_ticket->save();

                $monitor = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $po->armada_ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Konfirmasi tanda tangan supplier PO ' . $po->no_po_sap;
                $monitor->save();

                // START CASE
                // Jika armada perpanjangan, setelah confirm PO langsung otomatis verifikasi PO (tanpa harus dari area)
                if ($armada_ticket->type() == "Perpanjangan") {
                    $newrequest = new Request;
                    $newrequest->replace([
                        'armada_ticket_id' => $armada_ticket->id,
                    ]);
                    $response = app('App\Http\Controllers\Operational\ArmadaTicketingController')->verifyPO($newrequest, "api");
                    $isComplete = true;
                }
                // END CASE
            }
            if ($po->security_ticket_id != null) {
                $security_ticket              = $po->security_ticket;
                $security_ticket->po_number   = $po->no_po_sap;

                // cek status seluruh po apakah sudah selesai
                if ($security_ticket->po->where('status', '<', 3)->count() == 0) {
                    if ($security_ticket->status < 5) {
                        $security_ticket->status      = 5;
                    }
                    $security_ticket->save();
                    // START CASE
                    // Jika security perpanjangan, setelah confirm PO langsung otomatis selesai pengadaan (tanpa LPB Area)
                    if ($security_ticket->type() == "Perpanjangan") {
                        $newrequest = new Request;
                        $newrequest->replace([
                            'security_ticket_id' => $security_ticket->id,
                        ]);
                        $response = app('App\Http\Controllers\Operational\SecurityTicketingController')->uploadSecurityLPB($newrequest, "api");
                        $isComplete = true;
                    }
                    // END CASE
                }

                $monitor                          = new SecurityTicketMonitoring;
                $monitor->security_ticket_id      = $po->security_ticket->id;
                $monitor->employee_id             = Auth::user()->id;
                $monitor->employee_name           = Auth::user()->name;
                $monitor->message                 = 'Konfirmasi tanda tangan supplier PO ' . $po->no_po_sap;
                $monitor->save();
            }

            $mail_to = $po->last_mail_send_to;
            $cc = $po->last_mail_cc_to ?? "";
            $ccs = $this->email_text_to_array($cc);
            $data = array(
                'original_emails' => [$mail_to],
                'original_ccs' => $ccs,
                'po' => $po,
                'mail' => $mail_to,
                'external_signed_filepath' =>  $po_upload_request->filepath
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new POMail($data, 'poconfirmed'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            // dd($po, $isComplete, $check_vendor_e_log_sync);

            DB::commit();
            if ($isComplete) {
                if ($po->armada_ticket_id != null) {
                    if ($check_vendor_e_log_sync == 1) {
                        return back()->with('success', 'Berhasil melakukan konfirmasi tanda tangan Supplier untuk PO ' . $po->no_po_sap . ' . Pengadaan Selesai' . $emailmessage . '. Berhasil Kirim Data Ke E-Log');
                    } elseif ($check_vendor_e_log_sync == 0) {
                        return back()->with('success', 'Berhasil melakukan konfirmasi tanda tangan Supplier untuk PO ' . $po->no_po_sap . ' . Pengadaan Selesai' . $emailmessage . '. Gagal Kirim Data Ke E-Log');
                    }
                } else {
                    return back()->with('success', 'Berhasil melakukan konfirmasi tanda tangan Supplier untuk PO ' . $po->no_po_sap . ' . Pengadaan Selesai' . $emailmessage);
                }
            } else {
                if ($po->armada_ticket_id != null) {
                    if ($check_vendor_e_log_sync == 1) {
                        return back()->with('success', 'Berhasil melakukan konfirmasi tanda tangan Supplier untuk PO ' . $po->no_po_sap . ' dilanjutkan dengan penerimaan di salespoint/area bersangkutan' . '. Berhasil Kirim Data Ke E-Log' . $emailmessage);
                    } elseif ($check_vendor_e_log_sync == 0) {
                        return back()->with('success', 'Berhasil melakukan konfirmasi tanda tangan Supplier untuk PO ' . $po->no_po_sap . ' dilanjutkan dengan penerimaan di salespoint/area bersangkutan' . $emailmessage . '. Gagal Kirim Data Ke E-Log');
                    }
                } else {
                    return back()->with('success', 'Berhasil melakukan konfirmasi tanda tangan Supplier untuk PO ' . $po->no_po_sap . ' dilanjutkan dengan penerimaan di salespoint/area bersangkutan' . $emailmessage);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Confirm Signed PO ' . $ex->getMessage() . ' - line ' . $ex->getLine());
        }
    }

    // untuk purchasing reject dokumen yang uda di tandatangan vendor
    public function rejectPosigned(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $po = Po::findOrFail($request->po_id);
            $po->status = 1;
            $po->save();

            $porequest = POUploadRequest::findOrFail($request->po_upload_request_id);
            $porequest->reject_notes = $request->reason;
            $porequest->isExpired = true;
            $porequest->status = -1;
            $porequest->save();

            $po_upload_request               = new POUploadRequest;
            $po_upload_request->id           = (string) Str::uuid();
            $po_upload_request->po_id        = $po->id;
            if (strtolower(trim($po->sender_name)) == 'one time vendor') {
                $po_upload_request->vendor_name  = $po->sender_address;
            } else {
                $po_upload_request->vendor_name  = $po->sender_name;
            }
            $po_upload_request->vendor_pic   = $po->supplier_pic_name ?? "";
            $po_upload_request->save();

            $po->po_upload_request_id = $po_upload_request->id;
            $po->save();

            $mail_to = $this->email_text_to_array($po->last_mail_send_to);
            $ccs = $this->email_text_to_array($po->last_mail_cc_to);
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'reject_notes' => $request->reason,
                'po' => $po,
                'mail' => $mail_to,
                'po_upload_request' => $po_upload_request,
                'new_url' => url('/signpo/' . $po_upload_request->id)
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new POMail($data, 'posignedreject'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            if ($po->ticket_id != null) {
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $po->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Menolak tanda tangan Supplier PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->armada_ticket_id != null) {
                $monitor                 = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $po->armada_ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Menolak tanda tangan Supplier PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->security_ticket_id != null) {
                $monitor                    = new SecurityTicketMonitoring;
                $monitor->security_ticket_id         = $po->security_ticket->id;
                $monitor->employee_id       = Auth::user()->id;
                $monitor->employee_name     = Auth::user()->name;
                $monitor->message           = 'Menolak tanda tangan Supplier PO ' . $po->no_po_sap;
                $monitor->save();
            }

            DB::commit();
            return back()->with('success', 'Berhasil melakukan penolakan tanda tangan PO ' . $po->no_po_sap . ' link baru telah dikirim ke email ' . implode(", ", $mail_to ?? []) . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Reject Signed PO ' . $ex->getMessage() . $ex->getLine());
        }
    }

    public function sendEmail(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $po = Po::findOrFail($request->po_id);

            $old_po_upload_request = $po->po_upload_request;

            if ($old_po_upload_request) {
                $old_po_upload_request->isExpired = true;
                $old_po_upload_request->save();
                $old_po_upload_request->delete();
            }

            $po_upload_request               = new POUploadRequest;
            $po_upload_request->id           = (string) Str::uuid();
            $po_upload_request->po_id        = $po->id;
            if (strtolower(trim($po->sender_name)) == 'one time vendor') {
                $po_upload_request->vendor_name  = $po->sender_address;
            } else {
                $po_upload_request->vendor_name  = $po->sender_name;
            }
            if ($po->ticket_id != null) {
                $po_upload_request->vendor_pic   = $po->supplier_pic_name ?? "";
            }
            if ($po->armada_ticket_id != null || $po->security_ticket_id != null) {
                $po_upload_request->vendor_pic   = $po->sender_name;
            }
            $po_upload_request->save();

            $po->po_upload_request_id = $po_upload_request->id;
            $po->save();

            if (count($this->email_text_to_array($request->email)) < 1) {
                throw new \Exception("Minimal satu email yang dibutuhkan untuk mengirim PO (" . $request->email . ")");
            }
            $mail_to = $this->email_text_to_array($request->email);
            $ccs = $this->email_text_to_array($request->cc);
            $mail_subject = $request->mail_subject;
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'po' => $po,
                'mail' => $mail_to,
                'mail_subject' => $mail_subject,
                'email_text' => $request->email_text,
                'po_upload_request' => $po_upload_request,
                'needVendorConfirmation' => 1,
                'url' => url('/signpo/' . $po_upload_request->id),
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new POMail($data, 'posignedrequest'));
            } catch (\Exception $ex) {
                throw new \Exception("Terjadi kesalahan dalam pengiriman email. Silahkan coba kembali / hubungi developer - " . $ex->getMessage() . $ex->getLine());
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            $po->status = 1;
            $po->last_mail_send_to  = $request->email;
            $po->last_mail_cc_to    = $request->cc;
            $po->last_mail_text     = $request->email_text;
            $po->last_mail_subject  = $request->mail_subject;
            $po->save();
            DB::commit();


            if ($po->ticket_id != null) {
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $po->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Mengirim ulang email untuk PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->armada_ticket_id != null) {
                $monitor = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $po->armada_ticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Mengirim ulang email untuk PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->security_ticket_id != null) {
                $monitor = new SecurityTicketMonitoring;
                $monitor->security_ticket_id      = $po->security_ticket->id;
                $monitor->employee_id             = Auth::user()->id;
                $monitor->employee_name           = Auth::user()->name;
                $monitor->message                 = 'Mengirim ulang email untuk PO ' . $po->no_po_sap;
                $monitor->save();
            }
            return back()->with('success', 'berhasil mengirim ulang email untuk po ' . $po->no_po_sap . ' ke email ' . implode(",", $mail_to) . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Mengirimkan email (' . $ex->getMessage() . ')');
        }
    }

    public function poUploadRequestView($po_upload_request_id)
    {
        try {
            $poupload = POUploadRequest::where('id', $po_upload_request_id)->where('isExpired', false)->first();
            if (!$poupload) {
                throw new \Exception('Document expired or not found');
            }
            $po = Po::find($poupload->po_id);
            if ($po->status < 1) {
                throw new \Exception('Document expired or not found');
            }
            $active_po = Po::find($poupload->po_id);
            if ($active_po->po_upload_request_id != $poupload->id) {
                throw new \Exception('Link invalid');
            }
            $poupload->isOpened = true;
            $poupload->save();
            return view('Operational.poupload', compact('poupload'));
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function poUploadRequest(Request $request)
    {
        try {
            DB::beginTransaction();
            $pouploadrequest = POUploadRequest::findOrFail($request->po_upload_request_id);
            if ($request->file()) {
                $internal_signed_filepath = $pouploadrequest->po->internal_signed_filepath;
                $filepath = str_replace('INTERNAL_SIGNED', 'EXTERNAL_SIGNED', $internal_signed_filepath);
                $newext = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_EXTENSION);
                $filepath = $this->replace_extension($filepath, $newext);
                $file = pathinfo($filepath);
                $path = $request->file('file')->storeAs($file['dirname'], $file['basename'], 'public');
                $pouploadrequest->filepath = $path;
                $pouploadrequest->status = 1;
                $pouploadrequest->save();

                $po = $pouploadrequest->po;
                $po->status = 2;
                $po->save();

                if ($po->ticket_id != null) {
                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id      = $po->ticket->id;
                    $monitor->employee_id    = -1;
                    $monitor->employee_name  = $po->sender_name;
                    $monitor->message        = 'Supplier ' . $po->sender_name . ' Melakukan Upload tanda tangan PO ' . $po->no_po_sap;
                    $monitor->save();
                }

                if ($po->armada_ticket_id != null) {
                    $monitor = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $po->armada_ticket->id;
                    $monitor->employee_id           = -1;
                    $monitor->employee_name         = $po->sender_name;
                    $monitor->message                = 'Supplier ' . $po->sender_name . ' Melakukan Upload tanda tangan PO ' . $po->no_po_sap;
                    $monitor->save();
                }
                DB::commit();
                return back()->with('success', 'Berhasil upload file');
            } else {
                throw new \Exception("File tidak ditemukan");
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', $ex->getMessage());
        }
    }

    public function replace_extension($filename, $new_extension)
    {
        $info = pathinfo($filename);
        return $info['dirname'] . '/' . $info['filename'] . '.' . $new_extension;
    }

    public function getActivePO(Request $request)
    {
        // armada
        if ($request->type == 'armada') {
            // selain po yang mutasi
            $salespoint_id = $request->salespoint_id;
            $pos = Po::join('armada_ticket', 'po.armada_ticket_id', '=', 'armada_ticket.id')
                ->leftJoin('armada', 'armada_ticket.armada_id', '=', 'armada.id')
                ->leftJoin('mutasi_form', 'armada_ticket.id', '=', 'mutasi_form.armada_ticket_id')
                ->leftJoin('vendor', 'po.vendor_code', '=', 'vendor.code')
                ->where('po.status', 3)
                ->where('armada_ticket.isNiaga', $request->isNiaga)
                ->whereNotNull('po.start_date')
                ->whereNotNull('po.end_date')
                ->whereNotNull('armada_ticket.armada_id')
                ->where(function ($query) use ($salespoint_id) {
                    // pilih berdasarkan salespoint id dan jika dia ticketingnya mutasi salespoint id nya ambil dari form mutasi (receiver_salespoint_id)
                    $query->where(function ($query) use ($salespoint_id) {
                        $query->where('armada_ticket.salespoint_id', $salespoint_id)
                            ->where('armada_ticket.ticketing_type', '!=', 2);
                    })->orWhere(function ($query) use ($salespoint_id) {
                        $query->where('mutasi_form.receiver_salespoint_id', $salespoint_id)
                            ->where('armada_ticket.ticketing_type', 2);
                    })->orWhere(function ($query) use ($salespoint_id) {
                        $query->where('armada_ticket.mutation_salespoint_id', $salespoint_id)
                            ->where('armada_ticket.ticketing_type', '!=', 2);
                    });
                })
                ->select('po.no_po_sap AS po_number', 'armada.plate AS plate', 'armada_ticket.vendor_recommendation_name', 'armada_ticket.vendor_name', 'po.start_date as start_date', 'po.end_date as end_date', 'vendor.alias')
                ->get();

            foreach ($pos as $po) {
                $po->vendor = ($po->vendor_name != null) ? $po->vendor_name : $po->alias;
            }
            // filter po yang sedang dalam proses di pengadaan
            $pos = $pos->filter(function ($item) use ($request) {
                $selected = Po::where('no_po_sap', $item->po_number)->first();
                if ($selected->current_ticketing() == null) {
                    return true;
                } else {
                    return false;
                }
            });

            $salespoint_name = SalesPoint::find($request->salespoint_id)->name;
            $pomanual = PoManual::where('status', 3)
                ->where('category_name', 'armada')
                ->where('isNiaga', $request->isNiaga)
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->where('salespoint_name', trim(strtoupper($salespoint_name)))
                ->select('po_number', 'gs_plate', 'gt_plate', 'vendor_name as vendor', 'start_date', 'end_date')
                ->get();

            // filter po yang sedang dalam proses di pengadaan
            $pomanual = $pomanual->filter(function ($item) use ($request) {
                $selected = PoManual::where('po_number', $item->po_number)->first();
                if ($selected->current_ticketing() == null) {
                    return true;
                } else {
                    return false;
                }
            });

            foreach ($pomanual as $item) {
                $item->plate = ($item->gt_plate != "") ? $item->gt_plate : $item->gs_plate;
                $manual = new \stdClass();
                $manual->po_number    = $item->po_number;
                $manual->plate        = $item->plate;
                $manual->vendor       = $item->vendor;
                $manual->start_date   = $item->start_date;
                $manual->end_date     = $item->end_date;
                $pos->push($manual);
            }
            if ($request->pengadaan_type == 4) {
                // case percepatan
                // jika percepatan end Kontrak tampilkan end date yang diatas h-30
                $pos = $pos->filter(function ($po) {
                    if (CarbonImmutable::parse($po->end_date)->subDays(30) > now()) {
                        return true;
                    } else {
                        return false;
                    }
                });
            } else if ($request->pengadaan_type == 1) {
                // case perpanjangan
                $pos = $pos->filter(function ($po) {
                    // tampilkan data yang h-30 / lebih dari end kontrak
                    if (CarbonImmutable::parse($po->end_date)->subDays(30) <= now()) {
                        return true;
                    } else {
                        return false;
                    }
                });
            } else {
            }
            // converto from collection to array for proper json encoding
            $pos = $pos->toArray();
            return response()->json([
                "data" => array_values($pos),
            ]);
        }

        // security
        if ($request->type == 'security') {
            $pos = Po::join('security_ticket', 'po.security_ticket_id', '=', 'security_ticket.id')
                ->where('po.status', 3)
                ->where('security_ticket.salespoint_id', $request->salespoint_id)
                ->whereNotNull('po.start_date')
                ->whereNotNull('po.end_date')
                ->select('po.no_po_sap AS po_number', 'security_ticket.code AS code', 'po.start_date as start_date', 'po.end_date as end_date')
                ->get();
            // filter po yang sedang dalam proses di pengadaan
            $pos = $pos->filter(function ($item) use ($request) {
                $selected = Po::where('no_po_sap', $item->po_number)->first();
                if ($selected->current_ticketing() == null) {
                    return true;
                } else {
                    return false;
                }
            });
            $salespoint_name = SalesPoint::find($request->salespoint_id)->name;
            $pomanual = PoManual::where('status', 3)
                ->where('category_name', 'SECURITY')
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->where('salespoint_name', 'LIKE', '%' . strtoupper($salespoint_name) . '%')
                ->select('po_number', 'start_date', 'end_date')
                ->get();

            // filter po yang sedang dalam proses di pengadaan
            $pomanual = $pomanual->filter(function ($item) use ($request) {
                $selected = PoManual::where('po_number', $item->po_number)->first();
                if ($selected->current_ticketing() == null) {
                    return true;
                } else {
                    return false;
                }
            });
            foreach ($pomanual as $item) {
                $manual               = new \stdClass();
                $manual->po_number    = $item->po_number;
                $manual->code         = 'manual';
                $manual->start_date   = $item->start_date;
                $manual->end_date     = $item->end_date;
                $pos->push($manual);
            }
            // converto from collection to array for proper json encoding
            $pos = $pos->toArray();
            return response()->json([
                "data" => array_values($pos),
            ]);
        }

        if ($request->type == 'additional') {
            $salespoint = SalesPoint::find($request->salespoint_id);
            $pos = collect([]);
            $pos = Po::join('ticket', 'ticket.id', '=', 'po.ticket_id')
                ->join('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
                ->leftJoin('vendor', 'po.vendor_code', '=', 'vendor.code')
                ->leftJoin('salespoint', 'ticket.salespoint_id', '=', 'salespoint.id')
                ->where('po.status', 3)
                ->where('salespoint.id', $salespoint->id)
                ->where('ticket_item.name', 'LIKE', $request->ticket_type . '%')
                ->whereNotNull('po.start_date')
                ->whereNotNull('po.end_date')
                ->whereNotNull('ticket.id')
                ->select('po.no_po_sap AS po_number', 'po.start_date as start_date', 'po.end_date as end_date', 'vendor.alias as vendor_name', 'salespoint.name as salespoint_name')
                ->get();

            // filter po yang sedang dalam proses di pengadaan
            $pos = $pos->filter(function ($item) use ($request) {
                $selected = Po::where('no_po_sap', $item->po_number)->first();
                if ($selected->current_ticketing() == null) {
                    return true;
                } else {
                    return false;
                }
            });

            $pomanual = PoManual::where('status', 3)
                ->where('category_name', $request->ticket_type)
                ->where('salespoint_name', $salespoint->name)
                ->get();

            // filter po yang sedang dalam proses di pengadaan
            $pomanual = $pomanual->filter(function ($item) use ($request) {
                $selected = PoManual::where('po_number', $item->po_number)->first();
                if ($selected->current_ticketing() == null) {
                    return true;
                } else {
                    return false;
                }
            });

            foreach ($pomanual as $item) {
                $manual                  = new \stdClass();
                $manual->po_number       = $item->po_number;
                $manual->vendor_name     = $item->vendor_name;
                $manual->salespoint_name = $item->salespoint_name;
                $manual->code            = 'manual';
                $pos->push($manual);
            }
            // converto from collection to array for proper json encoding
            $pos = $pos->toArray();
            return response()->json([
                "data" => array_values($pos),
            ]);
        }
    }

    public function getPrSapbyTicketCode(Request $request)
    {
        $pods_code = $request->ticket_code;
        $parameters = [
            'MANDT' => "client",
            'BANFN' => "pr_number",
            'BNFPO' => "item_pr",
            'BSART' => "doc_type",
            'LOEKZ' => "deletion_indicator",
            'FRGZU' => "release_state",
            'EKGRP' => "pur_group",
            'ERNAM' => "created_by",
            'AFNAM' => "requisitioner",
            'MATNR' => "material_number",
            'TXZ01' => "short_text",
            'WERKS' => "plant",
            'MATKL' => "material_group",
            'MENGE' => "order_qty",
            'MEINS' => "uom",
            'LFDAT' => "delivery_date",
            'PREIS' => "valuation_price",
            'PEINH' => "price_unit",
            'PSTYP' => "item_category",
            'KNTTP' => "acct_ass_cat",
            'DISPO' => "mrp_controller",
            'EBELN' => "po_number",
            'EBELP' => "po_item",
            'BSMNG' => "qty_order",
            'CREATIONDATE' => "create_date",
            'CREATIONTIME' => "creation_time",
            'LT_EBAN-TEXT' => "pr_web",
            "TEXTNOTE" => "pods_code"
        ];

        // DB::table('pr_sap')->get()->toArray();
        $data = DB::table('pr_sap')->get()->pluck('data')->toArray();
        foreach ($data as $key => $item) {
            $item = json_decode($item);
            $item_in_array = (array) $item;
            $formatted_item = [];
            foreach ($item_in_array as $name => $value) {
                $default_name = $name;
                $formatted_name = $parameters[strtoupper($default_name)] ?? $default_name;
                $formatted_item[$formatted_name] = $value;
            }
            array_push($data, (object)$formatted_item);
        }
        $data = collect($data);
        // filter by pods code
        $data = $data->filter(function ($item) use ($pods_code) {
            // dd($item);
            if (($item->pods_code ?? 'undefined') == $pods_code) {
                return true;
            } else {
                return false;
            }
        });
        return response()->json([
            'error' => false,
            'data' => array_values($data->toArray()),
            'message' => "Menampilkan daftar pr untuk kode pengadaan " . $pods_code
        ], 200);
    }

    public function getPoSap(Request $request)
    {
        $po_numbers = $request->po_numbers;
        if ($po_numbers == null) {
            return response()->json([
                'error' => true,
                'data' => [],
                'message' => 'Nomor PO kosong'
            ], 500);
        }

        $po_numbers = array_unique($po_numbers);

        $parameters = [
            'MANDT'      => "client",
            'EBELN'      => "po_number",
            'BUKRS'      => "co_code",
            'BSTYP'      => "pur_doc_cat",
            'BSART'      => "pur_doc_type",
            'AEDAT'      => "create_on",
            'ERNAM'      => "created_by",
            'LIFNR'      => "vendor",
            'ZTERM'      => "payment_term",
            'EKORG'      => "pur_org",
            'EKGRP'      => "pur_group",
            'BEDAT'      => "doc_date",
            'FRGZU'      => "rel_state",
            'EBELP'      => "item_po",
            'MATNR'      => "material_number",
            'TXZ01'      => "material_short_text",
            'WERKS'      => "plant",
            'MATKL'      => "material_group",
            'MENGE'      => "total_qty_requested",
            'MENGE2'     => "scheduled_qty_requested",
            'MEINS'      => "order_unit",
            'NETPR'      => "net_order_price",
            'PEINH'      => "price_unit",
            'NETWR'      => "net_order_value",
            'PSTYP'      => "item_category",
            'KNTTP'      => "acct_ass_cat",
            'BANFN'      => "pr_number",
            'BNFPO'      => "item_pr",
            'LOEKZ'      => "del_indicator",
            'ELIKZ'      => "deliv_comp",
            'EINDT'      => "delv_date",
            'MAKTX'      => "material_desc",
            'NAME1'      => "vendor_name",
            'FRGKE'      => "release_indicator",
            'TEXTNOTE1'  => "header_text_po",
            'TEXTNOTE2'  => "item_text_po",
            'STRAS'      => "vendor_addr",
            'MCOD3'      => "vendor_city",
            'PSTLZ'      => "vendor_post_code",
            'TELF1'      => "vendor_tel",
            'TELFX'      => "vendor_fax",
            'LAND1'      => "vendor_country",
            'WERKSNM1'   => "plant_name_1",
            'WERKSNM2'   => "plant_name_2",
            'WERKSSTR'   => "plant_addrs",
            'WERKSCITY'  => "plant_city",
            'WERKSPC'    => "plant_post_code",
            'WERKSCNTRY' => "plant_country",
            'WERKSREGIO' => "plant_region",
            'ZBD1T'      =>    "payment_days"
        ];
        $data = [];
        foreach ($po_numbers as $po_number) {
            $po_data = DB::table('po_sap')
                ->where("data", "like", '%' . '"ebeln":"' . $po_number . '"' . '%')
                ->where("data", "like", '%' . '"frgke":"R"' . '%')
                ->get()
                ->pluck('data')
                ->toArray();
            foreach ($po_data as $key => $item) {
                $item = json_decode($item);
                $item_in_array = (array) $item;
                $formatted_item = [];
                foreach ($item_in_array as $name => $value) {
                    $default_name = $name;
                    $formatted_name = $parameters[strtoupper($default_name)] ?? $default_name;
                    $formatted_item[$formatted_name] = $value;
                }
                $object_formatted_item = (object) $formatted_item;
                if (!isset($object_formatted_item->del_indicator)) {
                    array_push($data, $object_formatted_item);
                }
            }
        }

        $data = collect($data);
        return response()->json([
            'error' => false,
            'data' => $data,
            'message' => "Menampilkan daftar po dengan kode PO " . implode(",", $po_numbers)
        ], 200);
    }

    public function email_text_to_array($text)
    {
        $emails = explode(',', $text);
        foreach ($emails as $key => $email) {
            // trim setiap email
            $emails[$key] = strtolower(trim($email));
        }
        // jika ada email yang sama makan hapus sisain salah satu
        $emails = array_unique($emails);
        // validate apakah format email sesuai
        $emails = array_filter($emails, function ($email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            } else {
                return true;
            }
        });
        // array_values fungsinya untuk ignore array keys yang bikin error saat json_encode
        return array_values($emails);
    }

    // untuk vendor reject
    public function poRejectSignedRequest(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $po_upload_request = POUploadRequest::find($request->po_upload_request_id);
            $po = Po::find($request->po_id);

            $po_upload_request->isExpired = true;
            $po_upload_request->reject_notes = $request->reject_reason;
            $po_upload_request->rejected_at = now();

            if (Auth::user()) {
                $po_upload_request->rejected_by = Auth::user()->name;
            } else {
                $po_upload_request->rejected_by = $po_upload_request->vendor_name;
            }
            $po_upload_request->save();

            $po->status       = -1;
            $po->reject_notes = $po_upload_request->reject_notes;
            $po->rejected_by  = $po_upload_request->rejected_by;
            $po->save();

            $employee_ids = $po->po_authorization->pluck('employee_id')->unique()->toArray();
            $employee_emails = Employee::whereIn('id', $employee_ids)->get()->pluck('email')->toArray();

            $mail_to = $employee_emails;
            $ccs = [];
            // dd(url('/signpo/'.$po_upload_request->id));
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'reject_notes' => $po->reject_notes,
                'rejected_by' => $po->rejected_by,
                'po_upload_request' => $po_upload_request,
                'po' => $po,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new POMail($data, 'vendorposignedreject'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }

            if (!$emailflag) {
                $emailmessage = "\n (Email sedang bermasalah)";
            }

            if ($po->ticket_id != null) {
                $monitor                  = new TicketMonitoring;
                $monitor->ticket_id       = $po->ticket->id;
                $monitor->employee_id     = -1;
                $monitor->employee_name   = $po_upload_request->rejected_by;
                $monitor->message         = 'Vendor menolak  PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->armada_ticket_id != null) {
                $monitor                    = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id  = $po->armada_ticket->id;
                $monitor->employee_id       = -1;
                $monitor->employee_name     = $po_upload_request->rejected_by;
                $monitor->message           = 'Vendor menolak  PO ' . $po->no_po_sap;
                $monitor->save();
            }
            if ($po->security_ticket_id != null) {
                $monitor                     = new SecurityTicketMonitoring;
                $monitor->security_ticket_id = $po->security_ticket->id;
                $monitor->employee_id        = -1;
                $monitor->employee_name      = $po_upload_request->rejected_by;
                $monitor->message            = 'Vendor menolak  PO ' . $po->no_po_sap;
                $monitor->save();
            }
            DB::commit();
            return "Berhasil melaporkan kesalahan dokumen dengan alasan. " . $request->reject_reason . ". Harap menunggu info untuk update kesalahan pada PO terkait. " . $emailmessage;
        } catch (\Exception $ex) {
            DB::rollback();
            return "Terjadi Kesalahan : " . $ex->getMessage();
        }
    }

    public function poCompareView($ticket_code)
    {
        $code = $ticket_code;
        $ticket = Ticket::where('code', $ticket_code)->first();
        $armadaticket = ArmadaTicket::where('code', $ticket_code)->first();
        $securityticket = SecurityTicket::where('code', $ticket_code)->first();
        if ($ticket) {
            return view('Operational.compareview', compact('ticket', 'code'));
        }
        if ($armadaticket) {
            return view('Operational.compareview', compact('armadaticket', 'code'));
        }
        if ($securityticket) {
            return view('Operational.compareview', compact('securityticket', 'code'));
        }
        return back()->with('error', 'Kode tiket tidak ditemukan');
    }

    public function poReminderUpdate(Request $request)
    {
        try {
            $po = PO::find($request->po_id);
            if (!$po) {
                throw new \Exception('PO ID tidak ditemukan.');
            }
            if ($request->has_reminder) {
                if (Carbon::parse($request->start_date) > Carbon::parse($request->end_date)) {
                    throw new \Exception('Start date harus sebelum atau sama dengan end date');
                }
            }

            DB::beginTransaction();
            if ($request->has_reminder) {
                $po->start_date = $request->start_date;
                $po->end_date = $request->end_date;
                $po->save();
            } else {
                $po->start_date = null;
                $po->end_date = null;
                $po->save();
            }
            DB::commit();
            return back()->with('success', "Berhasil update reminder terkait PO " . $po->no_po_sap);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', "Gagal update reminder (" . $ex->getMessage() . ")");
        }
    }
}
