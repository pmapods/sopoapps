<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\ArmadaTicket;
use App\Models\SecurityTicket;
use App\Models\TicketMonitoring;
use App\Models\ArmadaTicketMonitoring;
use App\Models\SecurityTicketMonitoring;
use App\Models\Po;
use App\Models\PoManual;
use App\Models\SalesPoint;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DB;
use Auth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonitoringController extends Controller
{
    public function ticketMonitoringView()
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
        $tickets = Ticket::whereIn('salespoint_id', $salespoint_ids)
            ->get();

        $po_manuals = PoManual::where('category_name', 'BARANG JASA')
            ->get();

        return view('Monitoring.ticketmonitoring', compact('tickets', 'po_manuals'));
    }

    public function ticketMonitoringLogs($ticket_id)
    {
        $logs = TicketMonitoring::where('ticket_id', $ticket_id)
            ->get()
            ->sortBy('created_at');
        $ticket = Ticket::find($ticket_id);
        $data = [];
        foreach ($logs as $log) {
            $item = new \stdClass();
            $item->message = $log->message;
            $item->employee_name = $log->employee_name;
            $item->date = $log->created_at->translatedFormat('d F Y (H:i)');
            array_push($data, $item);
        }
        return response()->json([
            'data' => $data,
            'status' =>  $ticket->status() ?? null,
        ]);
    }

    public function armadaMonitoringView(Request $request)
    {
        set_time_limit(0);
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id')->toArray();
        $tickets = ArmadaTicket::whereNotIn('status', [-1])
            ->whereIn('salespoint_id', $salespoint_ids)
            ->get();
        // cari po yang statusnya sedang aktif saat in
        $show_status = [1, 2, 3];
        if ($request->status == 4) {
            $show_status = [4];
        }
        $datas = [];
        $pos = Po::whereIn('status', $show_status)
            ->where('armada_ticket_id', '!=', null)
            ->get();
        $pos = $pos->filter(function ($item) use ($salespoint_ids) {
            if (in_array($item->armada_ticket->salespoint_id, $salespoint_ids)) {
                return true;
            } else {
                return false;
            }
        });

        $po_manuals = PoManual::whereIn('status', $show_status)
            ->where('category_name', 'ARMADA')
            ->get();
        $po_manuals = $po_manuals->filter(function ($item) use ($salespoint_ids) {
            try {
                $check_salespoint = SalesPoint::where('name', $item->salespoint_name)->first();
                if (in_array($check_salespoint->id, $salespoint_ids)) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        });

        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->ticket_type = $po->armada_ticket->type();
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->armada_ticket->vendor_name;
            if ($po->armada_ticket->mutation_salespoint_id != null) {
                $data->salespoint_name = $po->armada_ticket->mutation_salespoint->name;
            } else if ($po->armada_ticket->ticketing_type == 2) {
                // jika mutasi munculkan salespoint name dari penerima
                $data->salespoint_name = $po->armada_ticket->mutasi_form->receiver_salespoint_name;
            } else {
                $data->salespoint_name = $po->armada_ticket->salespoint->name;
            }
            $data->gs_plate = $po->armada_ticket->gs_plate;
            $data->gt_plate = $po->armada_ticket->gt_plate;
            if ($po->armada_ticket->armada_id != null) {
                $data->gs_plate = $po->armada_ticket->armada->plate;
                $data->gt_plate = $po->armada_ticket->armada->plate;
            }
            $data->brand_name = $po->armada_ticket->armada_type->brand_name;
            $data->type_name = $po->armada_ticket->armada_type->name;
            $data->status = $po->status;
            $data->current_ticketing = $po->current_ticketing();
            $data->status_name = $po->status();
            array_push($datas, $data);
        }
        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number = $po->po_number;
            $data->ticket_type = 'Manual';
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->vendor_name;
            $data->salespoint_name = $po->salespoint_name;
            $data->gs_plate = $po->gs_plate;
            $data->gt_plate = $po->gt_plate;
            $data->brand_name = $po->armada_brand_name;
            $data->type_name = $po->armada_name;
            $data->status = $po->status;
            $data->current_ticketing = $po->current_ticketing();
            $data->status_name = $po->status();
            $data->armada_pr_manual_filepath = $po->armada_pr_manual_filepath;
            $data->armada_po_filepath = $po->armada_po_filepath;
            $data->armada_bastk_filepath = $po->armada_bastk_filepath;
            array_push($datas, $data);
        }

        $pos = collect($datas)->sort(function ($a, $b) {
            if (!$a->end_date) {
                return !$b->end_date ? 0 : 1;
            }
            if (!$b->end_date) {
                return -1;
            }
            if ($a->end_date == $b->end_date) {
                return 0;
            }
            return $a->end_date < $b->end_date ? -1 : 1;
        });
        return view('Monitoring.armadamonitoring', compact('pos', 'tickets'));
    }

    public function armadaMonitoringTicketLogs($code)
    {
        $armadaticket = ArmadaTicket::where('code', $code)->first();
        $logs = ArmadaTicketMonitoring::where('armada_ticket_id', $armadaticket->id)
            ->get()
            ->sortBy('created_at');
        $data = [];
        foreach ($logs as $log) {
            $item = new \stdClass();
            $item->message = $log->message;
            $item->employee_name = $log->employee_name;
            $item->date = $log->created_at->translatedFormat('d F Y (H:i)');
            array_push($data, $item);
        }
        return response()->json([
            'data' => $data,
            'status' =>  $armadaticket->status(),
        ]);
    }

    public function armadaMonitoringPOLogs($po_number)
    {
        $selected_po = Po::where('no_po_sap', $po_number)->first();
        $selected_pomanual = PoManual::where('po_number', $po_number)->first();
        if ($selected_po) {
            // $salespoint_name = $selected_po->armada_ticket->salespoint->name;
            // $plates = [];
            // array_push($plates,$selected_po->armada_ticket->gs_plate);
            // array_push($plates,$selected_po->armada_ticket->gt_plate);
            $selected_pos = [];
            array_push($selected_pos, $selected_po->armada_ticket->po_reference_number);
            array_push($selected_pos, $selected_po->no_po_sap);
        }
        if ($selected_pomanual) {
            // $salespoint_name = $selected_pomanual->salespoint_name;
            // $plates = [];
            // array_push($plates,$selected_pomanual->gs_plate);
            // array_push($plates,$selected_pomanual->gt_plate);
            $selected_pos = [];
            array_push($selected_pos, $selected_pomanual->po_number);
            array_push($selected_pos, $selected_pomanual->po_reference_number);
        }
        $selected_pos = array_filter($selected_pos, function ($po) {
            if ($po == "" || $po == null) {
                return false;
            } else {
                return true;
            }
        });

        $data = [];

        $pos = Po::join('armada_ticket', 'armada_ticket.id', '=', 'po.armada_ticket_id')
            ->whereIn('armada_ticket.po_reference_number', $selected_pos)
            ->orWhereIn('po.no_po_sap', $selected_pos)
            ->select('po.*')
            ->get();

        foreach ($pos as $po) {
            $temp = new \stdClass();
            $temp->po_number = $po->no_po_sap;
            $temp->date = Carbon::parse($po->start_date)->translatedFormat('d F Y');
            $temp->type = $po->armada_ticket->type();
            array_push($data, $temp);
        }

        $pos_manual = PoManual::whereIn('po_reference_number', $selected_pos)
            ->orWhereIn('po_number', $selected_pos)
            ->get();

        // $pos_manual = PoManual::whereIn('gs_plate', $plates)
        //     ->orWhereIn('gt_plate',$plates)
        //     ->get();

        foreach ($pos_manual as $po) {
            $temp = new \stdClass();
            $temp->po_number = $po->po_number;
            $temp->date = Carbon::parse($po->start_date)->translatedFormat('d F Y');
            $temp->type = 'Manual';
            array_push($data, $temp);
        }

        $data = collect($data)->sortByDesc('po_number');

        return response()->json([
            'data' => array_values($data->toArray()),
        ]);
    }

    public function updateGTPlate(Request $request)
    {
        $pomanual = PoManual::where('po_number', $request->po_number)->get();
        if ($pomanual->count() > 0) {
            foreach ($pomanual as $po) {
                $po->gt_plate = str_replace(' ', '', strtoupper($request->gt_plate));
                $po->save();
            }
            return back()->with('success', 'Berhasil update Nomor Polisi GT dengan nomor PO ' . $request->po_number);
        } else {
            return back()->with('error', 'PO tidak ditemukan');
        }
    }

    public function securityMonitoringView(Request $request)
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id')->toArray();
        $tickets = SecurityTicket::whereNotIn('status', [-1, 0])
            ->whereIn('salespoint_id', $salespoint_ids)
            ->get();
        // cari po yang statusnya sedang aktif saat ini
        $show_status = [1, 2, 3];
        if ($request->status == 4) {
            $show_status = [4];
        }
        $datas = [];
        $pos = Po::whereIn('status', $show_status)
            ->where('security_ticket_id', '!=', null)
            ->get();

        $pos = $pos->filter(function ($item) use ($salespoint_ids) {
            try {
                if (in_array($item->security_ticket->salespoint_id, $salespoint_ids)) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Exception $ex) {
            }
        });

        $po_manuals = PoManual::whereIn('status', $show_status)
            ->where('category_name', 'SECURITY')
            ->get();

        $po_manuals = $po_manuals->filter(function ($item) use ($salespoint_ids) {
            try {
                // dd($salespoint_ids->toArray());
                $check_salespoint = SalesPoint::where('name', $item->salespoint_name)->first();
                if (in_array($check_salespoint->id, $salespoint_ids)) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        });

        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->ticket_type = $po->security_ticket->type();
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->security_ticket->vendor_name;
            $data->salespoint_name = $po->security_ticket->salespoint->name;
            $data->current_ticketing = $po->current_ticketing();
            $data->status = $po->status;
            $data->status_name = $po->status();
            array_push($datas, $data);
        }
        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number = $po->po_number;
            $data->ticket_type = "Manual";
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->vendor_name;
            $data->salespoint_name = $po->salespoint_name;
            $data->current_ticketing = $po->current_ticketing();
            $data->status = $po->status;
            $data->status_name = $po->status();
            $data->security_cit_pestcontrol_merchandiser_pr_manual_filepath = $po->security_cit_pestcontrol_merchandiser_pr_manual_filepath;
            $data->security_cit_pestcontrol_merchandiser_po_filepath = $po->security_cit_pestcontrol_merchandiser_po_filepath;
            array_push($datas, $data);
        }

        $pos = collect($datas)->sort(function ($a, $b) {
            if (!$a->end_date) {
                return !$b->end_date ? 0 : 1;
            }
            if (!$b->end_date) {
                return -1;
            }
            if ($a->end_date == $b->end_date) {
                return 0;
            }
            return $a->end_date < $b->end_date ? -1 : 1;
        });

        return view('Monitoring.securitymonitoring', compact('pos', 'tickets'));
    }

    public function securityMonitoringTicketLogs($code)
    {
        $securityticket = SecurityTicket::where('code', $code)->first();
        $logs = SecurityTicketMonitoring::where('security_ticket_id', $securityticket->id)
            ->get()
            ->sortBy('created_at');
        $data = [];
        foreach ($logs as $log) {
            $item = new \stdClass();
            $item->message = $log->message;
            $item->employee_name = $log->employee_name;
            $item->date = $log->created_at->translatedFormat('d F Y (H:i)');
            array_push($data, $item);
        }
        return response()->json([
            'data' => $data,
            'status' =>  $securityticket->status() ?? null,
        ]);
    }

    public function securityMonitoringPOLogs($po_number)
    {
        $selected_po = Po::where('no_po_sap', $po_number)->first();
        $selected_pomanual = PoManual::where('po_number', $po_number)->first();
        if ($selected_po) {
            $salespoint_name = $selected_po->security_ticket->salespoint->name;
        }
        if ($selected_pomanual) {
            $salespoint_name = $selected_pomanual->salespoint_name;
        }
        // dd($selected_po,$selected_pomanual,$salespoint_name);
        $data = [];
        $pos = Po::join('security_ticket', 'security_ticket.id', '=', 'po.security_ticket_id')
            ->join('salespoint', 'salespoint.id', '=', 'security_ticket.salespoint_id')
            ->where('salespoint.name', $salespoint_name)
            ->where('po.no_po_sap', '!=', 'unset')
            ->select('po.*')
            ->get();
        foreach ($pos as $po) {
            $temp = new \stdClass();
            $temp->po_number = $po->no_po_sap;
            $temp->date = Carbon::parse($po->start_date)->translatedFormat('d F Y');
            $temp->type = $po->security_ticket->type();
            array_push($data, $temp);
        }

        $pos_manual = PoManual::where('salespoint_name', $salespoint_name)
            ->where('category_name', 'security')
            ->get();

        foreach ($pos_manual as $po) {
            $temp = new \stdClass();
            $temp->po_number = $po->po_number;
            $temp->date = Carbon::parse($po->start_date)->translatedFormat('d F Y');
            $temp->type = 'Manual';
            array_push($data, $temp);
        }

        $data = collect($data)->sortByDesc('po_number');

        return response()->json([
            'data' => array_values($data->toArray()),
        ]);
    }

    public function citMonitoringView(Request $request)
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');

        $show_status = [1, 2, 3];
        if ($request->status == 4) {
            $show_status = [4];
        }

        $datas = [];
        $pos = Po::join('ticket', 'ticket.id', '=', 'po.ticket_id')
            ->join('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->whereIn('po.status', $show_status)
            ->where('po.ticket_id', '!=', null)
            ->where('ticket_item.name', 'LIKE', "CIT" . '%')
            ->select('po.*')
            ->distinct('ticket.id')
            ->get();

        $pos = $pos->filter(function ($item) use ($salespoint_ids) {
            if (in_array($item->ticket->salespoint_id, $salespoint_ids->toArray())) {
                return true;
            } else {
                return false;
            }
        });

        $po_manuals = PoManual::whereIn('status', $show_status)
            ->where('category_name', 'CIT')
            ->get();

        $po_manuals = $po_manuals->filter(function ($item) use ($salespoint_ids) {
            try {
                $check_salespoint = SalesPoint::where('name', $item->salespoint_name)->first();
                if (in_array($check_salespoint->id, $salespoint_ids->toArray())) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        });

        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->ticket_type = $po->ticket->request_type();
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->send_name;
            $data->salespoint_name = $po->ticket->salespoint->name;
            $data->status = $po->status;
            $data->current_ticketing = $po->current_ticketing();
            $data->status_name = $po->status();
            array_push($datas, $data);
        }

        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number = $po->po_number;
            $data->ticket_type = 'Manual';
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->vendor_name;
            $data->salespoint_name = $po->salespoint_name;
            $data->status = $po->status;
            $data->current_ticketing = $po->current_ticketing();
            $data->status_name = $po->status();
            $data->security_cit_pestcontrol_merchandiser_pr_manual_filepath = $po->security_cit_pestcontrol_merchandiser_pr_manual_filepath;
            $data->security_cit_pestcontrol_merchandiser_po_filepath = $po->security_cit_pestcontrol_merchandiser_po_filepath;
            array_push($datas, $data);
        }

        $pos = collect($datas)->sort(function ($a, $b) {
            if (!$a->end_date) {
                return !$b->end_date ? 0 : 1;
            }
            if (!$b->end_date) {
                return -1;
            }
            if ($a->end_date == $b->end_date) {
                return 0;
            }
            return $a->end_date < $b->end_date ? -1 : 1;
        });
        return view('Monitoring.citmonitoring', compact('pos'));
    }

    public function pestMonitoringView(Request $request)
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');

        $show_status = [1, 2, 3];
        if ($request->status == 4) {
            $show_status = [4];
        }
        $datas = [];
        $pos = Po::join('ticket', 'ticket.id', '=', 'po.ticket_id')
            ->join('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->whereIn('po.status', $show_status)
            ->where('po.ticket_id', '!=', null)
            ->where('ticket_item.name', 'LIKE', "Pest Control" . '%')
            ->select('po.*')
            ->distinct('ticket.id')
            ->get();

        $pos = $pos->filter(function ($item) use ($salespoint_ids) {
            if (in_array($item->ticket->salespoint_id, $salespoint_ids->toArray())) {
                return true;
            } else {
                return false;
            }
        });
        $po_manuals = PoManual::whereIn('status', $show_status)
            ->where('category_name', 'PEST CONTROL')
            ->get();

        $po_manuals = $po_manuals->filter(function ($item) use ($salespoint_ids) {
            try {
                $check_salespoint = SalesPoint::where('name', $item->salespoint_name)->first();
                if (in_array($check_salespoint->id, $salespoint_ids->toArray())) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        });
        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->ticket_type = $po->ticket->request_type();
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->send_name;
            $data->salespoint_name = $po->ticket->salespoint->name;
            $data->status = $po->status;
            $data->current_ticketing = $po->current_ticketing();
            $data->status_name = $po->status();
            array_push($datas, $data);
        }

        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number = $po->po_number;
            $data->ticket_type = 'Manual';
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->vendor_name;
            $data->salespoint_name = $po->salespoint_name;
            $data->status = $po->status;
            $data->current_ticketing = $po->current_ticketing();
            $data->status_name = $po->status();
            array_push($datas, $data);
            $data->security_cit_pestcontrol_merchandiser_pr_manual_filepath = $po->security_cit_pestcontrol_merchandiser_pr_manual_filepath;
            $data->security_cit_pestcontrol_merchandiser_po_filepath = $po->security_cit_pestcontrol_merchandiser_po_filepath;
        }

        $pos = collect($datas)->sort(function ($a, $b) {
            if (!$a->end_date) {
                return !$b->end_date ? 0 : 1;
            }
            if (!$b->end_date) {
                return -1;
            }
            if ($a->end_date == $b->end_date) {
                return 0;
            }
            return $a->end_date < $b->end_date ? -1 : 1;
        });
        return view('Monitoring.pestmonitoring', compact('pos'));
    }

    public function merchandiserMonitoringView(Request $request)
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');

        $show_status = [1, 2, 3];
        if ($request->status == 4) {
            $show_status = [4];
        }

        $datas = [];
        $pos = Po::join('ticket', 'ticket.id', '=', 'po.ticket_id')
            ->join('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->whereIn('po.status', $show_status)
            ->where('po.ticket_id', '!=', null)
            ->where('ticket_item.name', 'LIKE', "Merchandiser" . '%')
            ->select('po.*')
            ->distinct('ticket.id')
            ->get();

        $pos = $pos->filter(function ($item) use ($salespoint_ids) {
            if (in_array($item->ticket->salespoint_id, $salespoint_ids->toArray())) {
                return true;
            } else {
                return false;
            }
        });

        $po_manuals = PoManual::whereIn('status', $show_status)
            ->where('category_name', 'MERCHENDISER')
            ->get();

        $po_manuals = $po_manuals->filter(function ($item) use ($salespoint_ids) {
            try {
                $check_salespoint = SalesPoint::where('name', $item->salespoint_name)->first();
                if (in_array($check_salespoint->id, $salespoint_ids->toArray())) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        });

        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->ticket_type = $po->ticket->request_type();
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->send_name;
            $data->salespoint_name = $po->ticket->salespoint->name;
            $data->status = $po->status;
            $data->current_ticketing = $po->current_ticketing();
            $data->status_name = $po->status();
            array_push($datas, $data);
        }

        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number = $po->po_number;
            $data->ticket_type = 'Manual';
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->vendor_name;
            $data->salespoint_name = $po->salespoint_name;
            $data->status = $po->status;
            $data->current_ticketing = $po->current_ticketing();
            $data->status_name = $po->status();
            $data->security_cit_pestcontrol_merchandiser_pr_manual_filepath = $po->security_cit_pestcontrol_merchandiser_pr_manual_filepath;
            $data->security_cit_pestcontrol_merchandiser_po_filepath = $po->security_cit_pestcontrol_merchandiser_po_filepath;
            array_push($datas, $data);
        }

        $pos = collect($datas)->sort(function ($a, $b) {
            if (!$a->end_date) {
                return !$b->end_date ? 0 : 1;
            }
            if (!$b->end_date) {
                return -1;
            }
            if ($a->end_date == $b->end_date) {
                return 0;
            }
            return $a->end_date < $b->end_date ? -1 : 1;
        });

        return view('Monitoring.merchandisermonitoring', compact('pos'));
    }

    public function updatePO(Request $request)
    {
        try {
            DB::beginTransaction();
            // validate start po harus sebelum end po
            if (Carbon::parse($request->end_period) < Carbon::parse($request->start_period)) {
                return back()->with('error', 'Tanggal Start Period harus lebih awal dari End Period');
            }
            // validate nomor po baru tidak exist sebelumnya
            $check_po = PoManual::where('po_number', $request->new_po_number)->first();
            $check_po2 = Po::where('no_po_sap', $request->new_po_number)->first();
            if ($check_po || $check_po2) {
                return back()->with('error', 'Nomor PO sudah exist');
            }
            // tutup po Lama
            $old_po = PoManual::where('po_number', $request->old_po_number)->where('category_name', $request->type)->first();
            if ($old_po->status == 4) {
                return back()->with('error', 'PO sudah ditutup sebelumnya');
            }
            $old_po->status = 4;
            $old_po->save();

            $new_po = new PoManual;
            $new_po->po_number            = $request->new_po_number;
            $new_po->po_reference_number  = $request->old_po_number;
            $new_po->category_name        = $request->type;
            $new_po->salespoint_name      = $request->salespoint_name;
            $new_po->vendor_name          = $request->vendor;
            $new_po->start_date           = $request->start_period;
            $new_po->end_date             = $request->end_period;
            $new_po->keterangan           = $old_po->keterangan ?? null;
            $new_po->save();

            DB::commit();
            return back()->with('success', 'Berhasil update PO.  Status PO sebelumnya ' . $old_po->po_number . ' menjadi closed');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Update PO');
        }
    }

    public function uploadFileAttachmentTicket(Request $request)
    {
        try {
            DB::beginTransaction();
            $po_manual = PoManual::where('po_number', $request->po_number)->where('category_name', '=', 'BARANG JASA')->first();

            $bidding_file     = $request->file('file_bidding');
            $pr_manual_file   = $request->file('file_pr_manual');
            $po_file          = $request->file('file_po');
            $lpb_file         = $request->file('file_lpb');
            $invoice_file     = $request->file('file_invoice');

            if ($bidding_file) {
                $bidding_file     = $request->file('file_bidding');
                $bidding_ext      = pathinfo($bidding_file->getClientOriginalName(), PATHINFO_EXTENSION);
                $salespointname   = $po_manual->salespoint_name;
                $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_Bidding_' . $request->po_number . '_' . $salespointname . '.' . $bidding_ext;
                $info             = pathinfo($path);
                $bidding_path     = $bidding_file->storeAs($info['dirname'], $info['basename'], 'public');

                $po_manual->barang_jasa_form_bidding_filepath = $bidding_path;
                $po_manual->save();

                DB::commit();
            }

            if ($pr_manual_file) {
                $pr_manual_file   = $request->file('file_pr_manual');
                $pr_manual_ext    = pathinfo($pr_manual_file->getClientOriginalName(), PATHINFO_EXTENSION);
                $salespointname   = $po_manual->salespoint_name;
                $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PR_Manual_' . $request->po_number . '_' . $salespointname . '.' . $pr_manual_ext;
                $info             = pathinfo($path);
                $pr_manual_path   = $pr_manual_file->storeAs($info['dirname'], $info['basename'], 'public');

                $po_manual->barang_jasa_pr_manual_filepath    = $pr_manual_path;
                $po_manual->save();

                DB::commit();
            }

            if ($po_file) {
                $po_file          = $request->file('file_po');
                $po_ext           = pathinfo($po_file->getClientOriginalName(), PATHINFO_EXTENSION);
                $salespointname   = $po_manual->salespoint_name;
                $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PO_' . $request->po_number . '_' . $salespointname . '.' . $po_ext;
                $info             = pathinfo($path);
                $po_path          = $po_file->storeAs($info['dirname'], $info['basename'], 'public');

                $po_manual->barang_jasa_po_filepath           = $po_path;
                $po_manual->save();

                DB::commit();
            }

            if ($lpb_file) {
                $lpb_file         = $request->file('file_lpb');
                $lpb_ext          = pathinfo($lpb_file->getClientOriginalName(), PATHINFO_EXTENSION);
                $salespointname   = $po_manual->salespoint_name;
                $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_LPB_' . $request->po_number . '_' . $salespointname . '.' . $lpb_ext;
                $info             = pathinfo($path);
                $lpb_path         = $lpb_file->storeAs($info['dirname'], $info['basename'], 'public');

                $po_manual->barang_jasa_lpb_filepath          = $lpb_path;
                $po_manual->save();

                DB::commit();
            }

            if ($invoice_file) {
                $invoice_file     = $request->file('file_invoice');
                $invoice_ext      = pathinfo($invoice_file->getClientOriginalName(), PATHINFO_EXTENSION);
                $salespointname   = $po_manual->salespoint_name;
                $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_Invoice_' . $request->po_number . '_' . $salespointname . '.' . $invoice_ext;
                $info             = pathinfo($path);
                $invoice_path     = $invoice_file->storeAs($info['dirname'], $info['basename'], 'public');

                $po_manual->barang_jasa_invoice_filepath      = $invoice_path;
                $po_manual->save();

                DB::commit();
            }

            return back()->with('success', 'Berhasil Upload File Attachment');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload File Attachment ' . $ex->getMessage());
        }
    }

    public function uploadFileAttachmentArmada(Request $request)
    {
        try {
            DB::beginTransaction();
            $po_manual = PoManual::where('po_number', $request->po_number)->where('category_name', '=', 'ARMADA')->first();

            $pr_manual_file   = $request->file('file_pr_manual');
            $po_file          = $request->file('file_po');
            $bastk_file       = $request->file('file_bastk');

            $pr_manual_ext    = pathinfo($pr_manual_file->getClientOriginalName(), PATHINFO_EXTENSION);
            $po_ext           = pathinfo($po_file->getClientOriginalName(), PATHINFO_EXTENSION);
            $bastk_ext        = pathinfo($bastk_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespointname   = $po_manual->salespoint_name;

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PR_Manual_' . $request->po_number . '_' . $salespointname . '.' . $pr_manual_ext;
            $info             = pathinfo($path);
            $pr_manual_path   = $pr_manual_file->storeAs($info['dirname'], $info['basename'], 'public');

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PO_' . $request->po_number . '_' . $salespointname . '.' . $po_ext;
            $info             = pathinfo($path);
            $po_path          = $po_file->storeAs($info['dirname'], $info['basename'], 'public');

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_BASTK_' . $request->po_number . '_' . $salespointname . '.' . $bastk_ext;
            $info             = pathinfo($path);
            $bastk_path       = $bastk_file->storeAs($info['dirname'], $info['basename'], 'public');

            $po_manual->armada_pr_manual_filepath = $pr_manual_path;
            $po_manual->armada_po_filepath        = $po_path;
            $po_manual->armada_bastk_filepath     = $bastk_path;

            $po_manual->save();

            DB::commit();
            return back()->with('success', 'Berhasil Upload File Attachment');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload File Attachment ' . $ex->getMessage());
        }
    }

    public function uploadFileAttachmentSecurity(Request $request)
    {
        try {
            DB::beginTransaction();
            $po_manual = PoManual::where('po_number', $request->po_number)->where('category_name', '=', 'SECURITY')->first();

            $pr_manual_file   = $request->file('file_pr_manual');
            $po_file          = $request->file('file_po');

            $pr_manual_ext    = pathinfo($pr_manual_file->getClientOriginalName(), PATHINFO_EXTENSION);
            $po_ext           = pathinfo($po_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespointname   = $po_manual->salespoint_name;

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PR_Manual_' . $request->po_number . '_' . $salespointname . '.' . $pr_manual_ext;
            $info             = pathinfo($path);
            $pr_manual_path   = $pr_manual_file->storeAs($info['dirname'], $info['basename'], 'public');

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PO_' . $request->po_number . '_' . $salespointname . '.' . $po_ext;
            $info             = pathinfo($path);
            $po_path          = $po_file->storeAs($info['dirname'], $info['basename'], 'public');

            $po_manual->security_cit_pestcontrol_merchandiser_pr_manual_filepath = $pr_manual_path;
            $po_manual->security_cit_pestcontrol_merchandiser_po_filepath        = $po_path;

            $po_manual->save();

            DB::commit();
            return back()->with('success', 'Berhasil Upload File Attachment');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload File Attachment ' . $ex->getMessage());
        }
    }

    public function uploadFileAttachmentCit(Request $request)
    {
        try {
            DB::beginTransaction();
            $po_manual = PoManual::where('po_number', $request->po_number)->where('category_name', '=', 'CIT')->first();

            $pr_manual_file   = $request->file('file_pr_manual');
            $po_file          = $request->file('file_po');

            $pr_manual_ext    = pathinfo($pr_manual_file->getClientOriginalName(), PATHINFO_EXTENSION);
            $po_ext           = pathinfo($po_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespointname   = $po_manual->salespoint_name;

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PR_Manual_' . $request->po_number . '_' . $salespointname . '.' . $pr_manual_ext;
            $info             = pathinfo($path);
            $pr_manual_path   = $pr_manual_file->storeAs($info['dirname'], $info['basename'], 'public');

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PO_' . $request->po_number . '_' . $salespointname . '.' . $po_ext;
            $info             = pathinfo($path);
            $po_path          = $po_file->storeAs($info['dirname'], $info['basename'], 'public');

            $po_manual->security_cit_pestcontrol_merchandiser_pr_manual_filepath = $pr_manual_path;
            $po_manual->security_cit_pestcontrol_merchandiser_po_filepath        = $po_path;

            $po_manual->save();

            DB::commit();
            return back()->with('success', 'Berhasil Upload File Attachment');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload File Attachment ' . $ex->getMessage());
        }
    }

    public function uploadFileAttachmentPest(Request $request)
    {
        try {
            DB::beginTransaction();
            $po_manual = PoManual::where('po_number', $request->po_number)->where('category_name', '=', 'PEST CONTROL')->first();

            $pr_manual_file   = $request->file('file_pr_manual');
            $po_file          = $request->file('file_po');

            $pr_manual_ext    = pathinfo($pr_manual_file->getClientOriginalName(), PATHINFO_EXTENSION);
            $po_ext           = pathinfo($po_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespointname   = $po_manual->salespoint_name;

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PR_Manual_' . $request->po_number . '_' . $salespointname . '.' . $pr_manual_ext;
            $info             = pathinfo($path);
            $pr_manual_path   = $pr_manual_file->storeAs($info['dirname'], $info['basename'], 'public');

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PO_' . $request->po_number . '_' . $salespointname . '.' . $po_ext;
            $info             = pathinfo($path);
            $po_path          = $po_file->storeAs($info['dirname'], $info['basename'], 'public');

            $po_manual->security_cit_pestcontrol_merchandiser_pr_manual_filepath = $pr_manual_path;
            $po_manual->security_cit_pestcontrol_merchandiser_po_filepath        = $po_path;

            $po_manual->save();

            DB::commit();
            return back()->with('success', 'Berhasil Upload File Attachment');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload File Attachment ' . $ex->getMessage());
        }
    }

    public function uploadFileAttachmentMerchendiser(Request $request)
    {
        try {
            DB::beginTransaction();
            $po_manual = PoManual::where('po_number', $request->po_number)->where('category_name', '=', 'MERCHENDISER')->first();

            $pr_manual_file   = $request->file('file_pr_manual');
            $po_file          = $request->file('file_po');

            $pr_manual_ext    = pathinfo($pr_manual_file->getClientOriginalName(), PATHINFO_EXTENSION);
            $po_ext           = pathinfo($po_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespointname   = $po_manual->salespoint_name;

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PR_Manual_' . $request->po_number . '_' . $salespointname . '.' . $pr_manual_ext;
            $info             = pathinfo($path);
            $pr_manual_path   = $pr_manual_file->storeAs($info['dirname'], $info['basename'], 'public');

            $path             = 'attachments/ticketing/po_manual/' . $request->po_number . '/File_PO_' . $request->po_number . '_' . $salespointname . '.' . $po_ext;
            $info             = pathinfo($path);
            $po_path          = $po_file->storeAs($info['dirname'], $info['basename'], 'public');

            $po_manual->security_cit_pestcontrol_merchandiser_pr_manual_filepath = $pr_manual_path;
            $po_manual->security_cit_pestcontrol_merchandiser_po_filepath        = $po_path;

            $po_manual->save();

            DB::commit();
            return back()->with('success', 'Berhasil Upload File Attachment');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Upload File Attachment ' . $ex->getMessage());
        }
    }

    public function armadaGSExport()
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
        $tickets = ArmadaTicket::whereNotIn('status', [-1])
            ->whereIn('salespoint_id', $salespoint_ids)
            ->get();
        // cari po yang statusnya sedang aktif saat in
        $datas = [];
        $pos = Po::whereIn('status', [3, 4])
            ->where('armada_ticket_id', '!=', null)
            ->get();
        $pos = $pos->filter(function ($item) use ($salespoint_ids) {
            if (in_array($item->armada_ticket->salespoint_id, $salespoint_ids)) {
                return true;
            } else {
                return false;
            }
        });

        $po_manuals = PoManual::whereIn('status', [3, 4])
            ->where('category_name', 'ARMADA')
            ->get();

        $po_manuals = $po_manuals->filter(function ($item) use ($salespoint_ids) {
            try {
                // dd($salespoint_ids->toArray());
                $check_salespoint = SalesPoint::where('name', $item->salespoint_name)->first();
                if (in_array($check_salespoint->id, $salespoint_ids->toArray())) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        });
        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->ticket_type = $po->armada_ticket->type();
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->armada_ticket->vendor_name;
            $data->salespoint_name = $po->armada_ticket->salespoint->name;
            $data->gs_plate = $po->armada_ticket->gs_plate;
            $data->gt_plate = $po->armada_ticket->gt_plate;
            $data->brand_name = $po->armada_ticket->armada_type->brand_name;
            $data->type_name = $po->armada_ticket->armada_type->name;
            $data->status = $po->status;
            $data->current_ticketing = $po->current_ticketing();
            array_push($datas, $data);
        }
        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number = $po->po_number;
            $data->ticket_type = 'Manual';
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->vendor_name;
            $data->salespoint_name = $po->salespoint_name;
            $data->gs_plate = $po->gs_plate;
            $data->gt_plate = $po->gt_plate;
            $data->brand_name = $po->armada_brand_name;
            $data->type_name = $po->armada_name;
            $data->status = $po->status;
            $data->current_ticketing = $po->current_ticketing();
            array_push($datas, $data);
        }
        $filtered = collect($datas)->filter(function ($item) {
            // cari yang sudah ada gs plate tapi belum ada gt_plate
            if ($item->gs_plate != "" && $item->gs_plate != null && ($item->gt_plate == "" || $item->gt_plate == null)) {
                return true;
            } else {
                return false;
            }
        });
        $spreadsheet = new Spreadsheet();

        // full data
        $sheet = $spreadsheet->getActiveSheet();
        $this->getGSArmadaExcelSetting($sheet, $filtered);

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="GS Armada (' . now()->format('Y-m-d') . ').xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function armadaMonthlyReport(Request $request)
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id')->toArray();
        $selected_date = CarbonImmutable::parse($request->year . '-' . $request->month . '-1');
        // dd($selected_date);
        $pos = Po::whereIn('status', [3, 4])
            ->where('armada_ticket_id', '!=', null)
            ->get();
        $pos = $pos->filter(function ($item) use ($salespoint_ids) {
            if (in_array($item->armada_ticket->salespoint_id, $salespoint_ids)) {
                return true;
            } else {
                return false;
            }
        });

        $po_manuals = PoManual::whereIn('status', [3, 4])
            ->where('category_name', 'ARMADA')
            ->get();
        $po_manuals = $po_manuals->filter(function ($item) use ($salespoint_ids) {
            try {
                // dd($salespoint_ids->toArray());
                $check_salespoint = SalesPoint::where('name', $item->salespoint_name)->first();
                if (in_array($check_salespoint->id, $salespoint_ids->toArray())) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        });
        $datas = [];

        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->ticket_type = $po->armada_ticket->type();
            $data->start_date = $po->created_at->format('Y-m-d');
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->sender_name;
            $data->salespoint_name = $po->armada_ticket->salespoint->name;
            $data->brand_name = $po->armada_ticket->armada_type->brand_name;
            $data->type_name = $po->armada_ticket->armada_type->name;
            $data->isNiaga = $po->armada_ticket->isNiaga;
            $data->status = $po->status;
            array_push($datas, $data);
        }
        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number = $po->po_number;
            $data->ticket_type = 'Manual';
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->vendor_name;
            $data->salespoint_name = $po->salespoint_name;
            $data->brand_name = $po->armada_brand_name;
            $data->type_name = $po->armada_name;
            $data->isNiaga = $po->isNiaga;
            $data->status = $po->status;
            array_push($datas, $data);
        }
        $pos = collect($datas)->sortBy(function ($obj, $key) {
            return Carbon::parse($obj->end_date);
        });

        $pos_before_date = $pos->filter(function ($po) use ($selected_date) {
            if (CarbonImmutable::parse($po->start_date) <= $selected_date) {
                return true;
            } else {
                return false;
            }
        });
        $pos_between_date = $pos->filter(function ($po) use ($selected_date) {
            if (CarbonImmutable::parse($po->start_date)->between($selected_date->addDays(1), $selected_date->endOfMonth()->addDays(1))) {
                return true;
            } else {
                return false;
            }
        });
        $pos_after_date = $pos->filter(function ($po) use ($selected_date) {
            if (CarbonImmutable::parse($po->start_date) <= $selected_date->endOfMonth()->addDays(1)) {
                return true;
            } else {
                return false;
            }
        });

        // groupby vendor
        $pos_before_date_groupBy_vendor = $pos_before_date->groupBy('vendor_name');
        $pos_between_date_groupBy_vendor = $pos_between_date->groupBy('vendor_name');
        $pos_after_date_groupBy_vendor = $pos_after_date->groupBy('vendor_name');

        $vendor_name_list = $pos->pluck('vendor_name')->unique();
        $ticket_types = $pos->pluck('ticket_type')->unique();
        $data = [];
        foreach ($pos_before_date_groupBy_vendor as $vendor_name => $po) {
            $new                            = new \stdClass();
            $new->vendor_name               = $vendor_name;
            $new->po_groupBy_ticket_type    = $po->groupBy('ticket_type')->map->count();
            $new->total_count               = $po->count();
            array_push($data, $new);
        }
        $pos_group_by_ticket_type_start = collect($data);
        $data = [];
        foreach ($pos_between_date_groupBy_vendor as $vendor_name => $po) {
            $new                            = new \stdClass();
            $new->vendor_name               = $vendor_name;
            $new->po_groupBy_ticket_type    = $po->groupBy('ticket_type')->map->count();
            $new->total_count               = $po->count();
            array_push($data, $new);
        }
        $pos_group_by_ticket_type_between = collect($data);

        $data = [];
        foreach ($pos_after_date_groupBy_vendor as $vendor_name => $po) {
            $new                          = new \stdClass();
            $new->vendor_name             = $vendor_name;
            $new->po_groupBy_ticket_type  = $po->groupBy('ticket_type')->map->count();
            $new->total_count             = $po->count();
            array_push($data, $new);
        }
        $pos_group_by_ticket_type_end = collect($data);

        // groupby armada niaga & non niaga
        $data = [];
        foreach ($pos_after_date_groupBy_vendor as $vendor_name => $po) {
            $new               = new \stdClass();
            $new->vendor_name  = $vendor_name;
            $new->niaga        = $po->where('isNiaga', 1)->count();
            $new->non_niaga    = $po->where('isNiaga', 0)->count();
            array_push($data, $new);
        }
        $pos_groupby_niaga = collect($data);
        return view('Monitoring.monthlyArmadaReport', compact('vendor_name_list', 'ticket_types', 'pos_group_by_ticket_type_start', 'pos_group_by_ticket_type_between', 'pos_group_by_ticket_type_end', 'pos_groupby_niaga', 'selected_date'));
    }

    public function getGSArmadaExcelSetting($sheet, $pos)
    {
        // set lebar per field
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(13);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(28);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(14);
        $sheet->getColumnDimension('J')->setWidth(20);

        $start_row = 1;
        $count_row = 0;
        // ALL DATA
        $row_po_number         = "A" . strval($start_row + $count_row);
        $row_ticket_type       = "B" . strval($start_row + $count_row);
        $row_start_date        = "C" . strval($start_row + $count_row);
        $row_end_date          = "D" . strval($start_row + $count_row);
        $row_vendor_name       = "E" . strval($start_row + $count_row);
        $row_salespoint_name   = "F" . strval($start_row + $count_row);
        $row_gs_plate          = "G" . strval($start_row + $count_row);
        $row_gt_plate          = "H" . strval($start_row + $count_row);
        $row_brand_name        = "I" . strval($start_row + $count_row);
        $row_type_name         = "J" . strval($start_row + $count_row);

        // header
        $sheet->setCellValue($row_po_number, 'Nomor PO');
        $sheet->setCellValue($row_ticket_type, 'Tipe');
        $sheet->setCellValue($row_start_date, 'Start Date');
        $sheet->setCellValue($row_end_date, 'End Date');
        $sheet->setCellValue($row_vendor_name, 'Vendor');
        $sheet->setCellValue($row_salespoint_name, 'Salespoint');
        $sheet->setCellValue($row_gs_plate, 'Nopol GS');
        $sheet->setCellValue($row_gt_plate, 'Nopol GT');
        $sheet->setCellValue($row_brand_name, 'Brand');
        $sheet->setCellValue($row_type_name, 'Jenis');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $count_row++;

        foreach ($pos as $po) {
            $row_po_number         = "A" . strval($start_row + $count_row);
            $row_ticket_type       = "B" . strval($start_row + $count_row);
            $row_start_date        = "C" . strval($start_row + $count_row);
            $row_end_date          = "D" . strval($start_row + $count_row);
            $row_vendor_name       = "E" . strval($start_row + $count_row);
            $row_salespoint_name   = "F" . strval($start_row + $count_row);
            $row_gs_plate          = "G" . strval($start_row + $count_row);
            $row_gt_plate          = "H" . strval($start_row + $count_row);
            $row_brand_name        = "I" . strval($start_row + $count_row);
            $row_type_name         = "J" . strval($start_row + $count_row);

            $sheet->setCellValue($row_po_number, $po->po_number);
            $sheet->setCellValue($row_ticket_type, $po->ticket_type);
            $sheet->setCellValue($row_start_date, $po->start_date);
            $sheet->setCellValue($row_end_date, $po->end_date);
            $sheet->setCellValue($row_vendor_name, $po->vendor_name);
            $sheet->setCellValue($row_salespoint_name, $po->salespoint_name);
            $sheet->setCellValue($row_gs_plate, $po->gs_plate);
            $sheet->setCellValue($row_gt_plate, $po->gt_plate);
            $sheet->setCellValue($row_brand_name, $po->brand_name);
            $sheet->setCellValue($row_type_name, $po->type_name);
            $count_row++;
        }
    }

    public function securityPOByArea()
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id')->toArray();
        // get all security po
        $datas = [];
        $pos = Po::whereIn('status', [3, 4])
            ->where('security_ticket_id', '!=', null)
            ->get();

        $pos = $pos->filter(function ($item) use ($salespoint_ids) {
            if (in_array($item->security_ticket->salespoint_id, $salespoint_ids)) {
                return true;
            } else {
                return false;
            }
        });

        $po_manuals = PoManual::whereIn('status', [3, 4])
            ->where('category_name', 'SECURITY')
            ->get();
        $po_manuals = $po_manuals->filter(function ($item) use ($salespoint_ids) {
            try {
                // dd($salespoint_ids->toArray());
                $check_salespoint = SalesPoint::where('name', $item->salespoint_name)->first();
                if (in_array($check_salespoint->id, $salespoint_ids->toArray())) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        });
        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->ticket_type = $po->security_ticket->type();
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->security_ticket->vendor_name;
            $data->salespoint_name = $po->security_ticket->salespoint->name;
            $data->current_ticketing = $po->current_ticketing();
            array_push($datas, $data);
        }
        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number = $po->po_number;
            $data->ticket_type = "Manual";
            $data->start_date = $po->start_date;
            $data->end_date = $po->end_date;
            $data->vendor_name = $po->vendor_name;
            $data->salespoint_name = $po->salespoint_name;
            $data->current_ticketing = $po->current_ticketing();
            array_push($datas, $data);
        }
        $datas = collect($datas);
        // $pos = collect($datas)->sortBy(function ($obj, $key) {
        //     return Carbon::parse($obj->end_date);
        // });
        $spreadsheet = new Spreadsheet();

        // full data
        $sheet = $spreadsheet->getActiveSheet();
        $this->getSecurityPObyArea($sheet, $datas);

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Security PO Monitoring (' . now()->format('Y-m-d') . ').xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function getSecurityPObyArea($sheet, $datas)
    {
        $row = 1;
        $max_col = 1;
        foreach ($datas->groupBy('salespoint_name') as $salespoint => $data) {
            $temp_col = $data->count();
            // dd($temp_col);
            if ($temp_col > $max_col) {
                $max_col = $temp_col;
            }
        }
        // create header
        $col = 1;
        for ($i = 0; $i < $max_col; $i++) {
            $sheet->setCellValueByColumnAndRow($col, $row, "PO " . ($i + 1));
            $col++;
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, "Salespoint");
        $row++;
        foreach ($datas->groupBy('salespoint_name') as $salespoint => $data) {
            // dd($salespoint,$data);
            $col = 1;
            // if($salespoint == "BANJAR"){
            //     dd($salespoint,$data->pluck('po_number'));
            // }
            foreach ($data as $po) {
                $sheet->setCellValueByColumnAndRow($col, $row, $po->po_number);
                $col++;
            }
            // skip column by difference
            $col = $max_col + 2;
            $sheet->setCellValueByColumnAndRow($col, $row, $salespoint);
            $row++;
        }
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
    }
}
