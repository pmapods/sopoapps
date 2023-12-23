<?php

namespace App\Http\Controllers\Dashboard;

use DB;
use Auth;
use Hash;
use App\Models\Po;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\PoManual;
use App\Models\SalesPoint;
use App\Models\ArmadaTicket;
use App\Models\BudgetUpload;
use Illuminate\Http\Request;
use App\Models\SecurityTicket;
use App\Models\PoAuthorization;
use App\Models\PrAuthorization;
use App\Models\VendorEvaluation;
use App\Models\TicketAuthorization;
use App\Http\Controllers\Controller;
use App\Models\BiddingAuthorization;
use App\Models\EmployeeLocationAccess;
use App\Models\MutasiFormAuthorization;
use App\Models\ArmadaTicketAuthorization;
use App\Models\BudgetUploadAuthorization;
use App\Models\EvaluasiFormAuthorization;
use App\Models\FacilityFormAuthorization;
use App\Models\TicketingBlockOpenRequest;
use App\Models\SecurityTicketAuthorization;
use App\Models\PerpanjanganFormAuthorization;
use App\Models\VendorEvaluationAuthorization;

class DashboardRequestApprovalController extends Controller
{
    public function dashboardRequestApprovalView()
    {
        return view('Dashboard.requestapproval');
    }

    public function getCurrentAuthorization()
    {
        $data = [];
        // Budget Upload Authorization
        $budget_upload_authorization = BudgetUploadAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->get();
        foreach ($budget_upload_authorization as $author) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;
                $budget_upload = $author->budget_upload;

                if ($budget_upload->status != 0) {
                    continue;
                }

                if ($budget_upload->current_authorization()->employee_id == Auth::user()->id) {
                    $newAuth->needApproval = true;
                } else {
                    $newAuth->needApproval = false;
                }

                $newAuth->salespoint = $budget_upload->salespoint->name;
                $newAuth->code = $budget_upload->code;
                $newAuth->created_at = $budget_upload->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = "-";
                $newAuth->transaction_type = 'Upload Budget';
                $newAuth->status = $budget_upload->status();
                if ($budget_upload->type == 'inventory') {
                    $newAuth->link = "/inventorybudget/" . $budget_upload->code;
                }
                if ($budget_upload->type == 'armada') {
                    $newAuth->link = "/armadabudget/" . $budget_upload->code;
                }
                if ($budget_upload->type == 'assumption') {
                    $newAuth->link = "/assumptionbudget/" . $budget_upload->code;
                }
                if ($budget_upload->type == 'ho') {
                    $newAuth->link = "/ho_budget/" . $budget_upload->code;
                }
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        // Pengadaan Authorization
        //ticketing
        $ticketauthorization = TicketAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->get();
        $armadaticketauthorization = ArmadaTicketAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->get();
        $securityticketauthorization = SecurityTicketAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->get();

        foreach ($ticketauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;
                $ticket = $author->ticket;

                if (!in_array($ticket->status ?? -1, [1])) {
                    continue;
                }

                if ($ticket->current_authorization()->employee_id == Auth::user()->id) {
                    $newAuth->needApproval = true;
                } else {
                    $newAuth->needApproval = false;
                }

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                // $newAuth->created_by = $ticket->ticket_authorization->where('as','Pengaju')->first()->employee_name ?? '';
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Barang Jasa (ticketing)';
                $newAuth->status = $ticket->status();
                $newAuth->link = "/ticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }
        foreach ($armadaticketauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;
                $armada_ticket = $author->armada_ticket;
                if (!$armada_ticket->status == -1) {
                    continue;
                }

                if ($armada_ticket->current_authorization()->employee_id == Auth::user()->id) {
                    $newAuth->needApproval = true;
                } else {
                    $newAuth->needApproval = false;
                }

                $newAuth->salespoint = $armada_ticket->salespoint->name;
                $newAuth->code = $armada_ticket->code;
                $newAuth->created_at = $armada_ticket->created_at->translatedFormat('d F Y (H:i)');
                // $newAuth->created_by = $armada_ticket->authorizations->where('as','Pengaju')->first()->employee_name ?? '';
                $newAuth->created_by = $armada_ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Armada (ticketing)';
                $newAuth->status = $armada_ticket->status();
                $newAuth->link = "/armadaticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }
        foreach ($securityticketauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;
                $security_ticket = $author->security_ticket;
                if (!in_array($security_ticket->status ?? -1, [1])) {
                    continue;
                }

                if ($security_ticket->current_authorization()->employee_id == Auth::user()->id) {
                    $newAuth->needApproval = true;
                } else {
                    $newAuth->needApproval = false;
                }

                $newAuth->salespoint = $security_ticket->salespoint->name;
                $newAuth->code = $security_ticket->code;
                $newAuth->created_at = $security_ticket->created_at->translatedFormat('d F Y (H:i)');
                // $newAuth->created_by = $security_ticket->authorizations->where('as','Pengaju')->first()->employee_name ?? '';
                $newAuth->created_by = $security_ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Security (ticketing)';
                $newAuth->status = $security_ticket->status();
                $newAuth->link = "/securityticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        // barangjasa bidding
        $biddingauthorization = BiddingAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->get();

        foreach ($biddingauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;

                $bidding = $author->bidding;
                $ticket = $author->bidding->ticket;
                $ticket_item = $author->bidding->ticket_item;
                if (!in_array($ticket->status ?? -1, [2])) {
                    continue;
                }

                if ($bidding->current_authorization()->employee_id == Auth::user()->id) {
                    $newAuth->needApproval = true;
                } else {
                    $newAuth->needApproval = false;
                }
                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                // $newAuth->created_by = $ticket->ticket_authorization->where('as','Pengaju')->first()->employee_name ?? '';
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Barang Jasa (bidding)';
                $newAuth->status = $author->bidding->status();
                $newAuth->link = "/bidding/" . $newAuth->code . "/" . $ticket_item->id;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        // setup pr
        $setup_pr_list            = [];
        $setup_pr_ticket          = Ticket::where('status', '3')->get();
        $setup_pr_armada_ticket   = ArmadaTicket::where('status', '2')->get();
        $setup_pr_security_ticket = SecurityTicket::where('status', '2')->get();
        foreach ($setup_pr_ticket as $ticket) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;

                $authors_employee_id = $ticket->ticket_authorization->sortByDesc('level')->take(2)->pluck('employee_id')->toArray();
                if (!in_array(Auth::user()->id, $authors_employee_id)) {
                    // dashboard create PR hanya untuk 2 orang pembuat
                    continue;
                }
                $newAuth->needApproval = true;

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;

                $newAuth->transaction_type = 'Barang Jasa (SETUP PR)';
                $newAuth->status = $ticket->status();
                $newAuth->link = "/pr/" . $ticket->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }
        foreach ($setup_pr_armada_ticket as $armadaticket) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;

                $authors_employee_id = $armadaticket->authorizations->sortByDesc('level')->take(2)->pluck('employee_id')->toArray();
                if (!in_array(Auth::user()->id, $authors_employee_id)) {
                    // dashboard create PR hanya untuk 2 orang pembuat
                    continue;
                }
                $newAuth->needApproval = true;

                $newAuth->salespoint = $armadaticket->salespoint->name;
                $newAuth->code = $armadaticket->code;
                $newAuth->created_at = $armadaticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $armadaticket->created_by_employee->name;

                $newAuth->transaction_type = 'Barang Jasa (SETUP PR)';
                $newAuth->status = $armadaticket->status();
                $newAuth->link = "/pr/" . $armadaticket->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }
        foreach ($setup_pr_security_ticket as $securityticket) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;

                $authors_employee_id = $securityticket->authorizations->sortByDesc('level')->take(2)->pluck('employee_id')->toArray();
                if (!in_array(Auth::user()->id, $authors_employee_id)) {
                    // dashboard create PR hanya untuk 2 orang pembuat
                    continue;
                }
                $newAuth->needApproval = true;

                $newAuth->salespoint = $securityticket->salespoint->name;
                $newAuth->code = $securityticket->code;
                $newAuth->created_at = $securityticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $securityticket->created_by_employee->name;

                $newAuth->transaction_type = 'Barang Jasa (SETUP PR)';
                $newAuth->status = $securityticket->status();
                $newAuth->link = "/pr/" . $securityticket->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        // pr
        $prauthorization = PrAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->get();

        foreach ($prauthorization as $author) {
            try {
                $pr = $author->pr;
                $ticket = $author->pr->ticket;
                $armadaticket = $author->pr->armada_ticket;
                $securityticket = $author->pr->security_ticket;
                if ($ticket) {
                    $newAuth = new \stdClass();
                    $newAuth->canQuickApprove = false;

                    if (!in_array($ticket->status ?? -1, [4])) {
                        continue;
                    }
                    if ($pr->current_authorization()->employee_id == Auth::user()->id) {
                        $newAuth->needApproval = true;
                    } else {
                        $newAuth->needApproval = false;
                    }

                    $newAuth->salespoint = $ticket->salespoint->name;
                    $newAuth->code = $ticket->code;
                    $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                    $newAuth->created_by = $ticket->created_by_employee->name;
                    // $newAuth->created_by = $ticket->ticket_authorization->where('as','Pengaju')->first()->employee_name ?? '';

                    $newAuth->transaction_type = 'Barang Jasa (PR)';
                    $newAuth->status = $ticket->status();
                    $newAuth->link = "/pr/" . $ticket->code;
                    array_push($data, $newAuth);
                }
                if ($armadaticket) {
                    $newAuth = new \stdClass();
                    $newAuth->canQuickApprove = false;

                    if (!in_array($armadaticket->status ?? -1, [3])) {
                        continue;
                    }
                    if ($pr->current_authorization()->employee_id == Auth::user()->id) {
                        $newAuth->needApproval = true;
                    } else {
                        $newAuth->needApproval = false;
                    }
                    $newAuth->salespoint = $armadaticket->salespoint->name;
                    $newAuth->code = $armadaticket->code;
                    $newAuth->created_at = $armadaticket->created_at->translatedFormat('d F Y (H:i)');
                    // $newAuth->created_by = $armadaticket->authorizations->where('as','Pengaju')->first()->employee_name ?? '';
                    $newAuth->created_by = $armadaticket->created_by_employee->name;

                    $newAuth->transaction_type = 'Armada (PR)';
                    $newAuth->status = $armadaticket->status();
                    $newAuth->link = "/pr/" . $armadaticket->code;
                    array_push($data, $newAuth);
                }
                if ($securityticket) {
                    $newAuth = new \stdClass();
                    $newAuth->canQuickApprove = false;

                    if (!in_array($securityticket->status ?? -1, [3])) {
                        continue;
                    }

                    if ($pr->current_authorization()->employee_id == Auth::user()->id) {
                        $newAuth->needApproval = true;
                    } else {
                        $newAuth->needApproval = false;
                    }
                    $newAuth->salespoint = $securityticket->salespoint->name;
                    $newAuth->code = $securityticket->code;
                    $newAuth->created_at = $securityticket->created_at->translatedFormat('d F Y (H:i)');
                    // $newAuth->created_by = $securityticket->authorizations->where('as','Pengaju')->first()->employee_name ?? '';
                    $newAuth->created_by = $securityticket->created_by_employee->name;

                    $newAuth->transaction_type = 'Security (PR)';
                    $newAuth->status = $securityticket->status();
                    $newAuth->link = "/pr/" . $securityticket->code;
                    array_push($data, $newAuth);
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        // form evaluasi
        $evaluasiauthorization = EvaluasiFormAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->get();

        foreach ($evaluasiauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;

                $security_ticket = $author->evaluasi_form->security_ticket;
                if (!in_array($security_ticket->status ?? -1, [0])) {
                    continue;
                }

                if ($author->evaluasi_form->current_authorization()->employee_id == Auth::user()->id) {
                    $newAuth->needApproval = true;
                } else {
                    $newAuth->needApproval = false;
                }

                $newAuth->salespoint = $security_ticket->salespoint->name;
                $newAuth->code = $security_ticket->code;
                $newAuth->created_at = $security_ticket->created_at->translatedFormat('d F Y (H:i)');
                // $newAuth->created_by = $security_ticket->authorizations->where('as','Pengaju')->first()->employee_name ?? '';
                $newAuth->created_by = $security_ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Security (form evaluasi)';
                $newAuth->status = $security_ticket->status();
                $newAuth->link = "/securityticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        // form fasilitas
        $facilityauthorization = FacilityFormAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->get();

        foreach ($facilityauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;

                $armada_ticket = $author->facility_form->armada_ticket;
                if ($armada_ticket->status == -1) {
                    continue;
                }

                if ($author->facility_form->current_authorization()->employee_id == Auth::user()->id) {
                    $newAuth->needApproval = true;
                } else {
                    $newAuth->needApproval = false;
                }

                $newAuth->salespoint = $armada_ticket->salespoint->name;
                $newAuth->code = $armada_ticket->code;
                $newAuth->created_at = $armada_ticket->created_at->translatedFormat('d F Y (H:i)');
                // $newAuth->created_by = $armada_ticket->authorizations->where('as','Pengaju')->first()->employee_name ?? '';
                $newAuth->created_by = $armada_ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Armada (form fasilitas)';
                $newAuth->status = $armada_ticket->status();
                $newAuth->link = "/armadaticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        // form perpanjang
        $perpanjanganauthorization = PerpanjanganFormAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->get();

        foreach ($perpanjanganauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = true;

                $armada_ticket = $author->perpanjangan_form->armada_ticket;
                if ($armada_ticket->status == -1) {
                    continue;
                }

                if ($author->perpanjangan_form->current_authorization()->employee_id == Auth::user()->id) {
                    $newAuth->needApproval = true;
                } else {
                    $newAuth->needApproval = false;
                }

                $newAuth->salespoint = $armada_ticket->salespoint->name;
                $newAuth->code = $armada_ticket->code;
                $newAuth->created_at = $armada_ticket->created_at->translatedFormat('d F Y (H:i)');
                // $newAuth->created_by = $armada_ticket->authorizations->where('as','Pengaju')->first()->employee_name ?? '';
                $newAuth->created_by = $armada_ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Armada (form perpanjangan)';
                $newAuth->status = $armada_ticket->status();
                $newAuth->link = "/armadaticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        $mutasiauthorization = MutasiFormAuthorization::where('employee_id', Auth::user()->id)
            ->where('status', 0)
            ->get();

        foreach ($mutasiauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $newAuth->canQuickApprove = false;

                $armada_ticket = $author->mutasi_form->armada_ticket;
                if ($armada_ticket->status == -1) {
                    continue;
                }

                if ($author->mutasi_form->current_authorization()->employee_id == Auth::user()->id) {
                    $newAuth->needApproval = true;
                } else {
                    $newAuth->needApproval = false;
                }

                $newAuth->salespoint = $armada_ticket->salespoint->name;
                $newAuth->code = $armada_ticket->code;
                $newAuth->created_at = $armada_ticket->created_at->translatedFormat('d F Y (H:i)');
                // $newAuth->created_by = $armada_ticket->authorizations->where('as','Pengaju')->first()->employee_name ?? '';
                $newAuth->created_by = $armada_ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Armada (form mutasi)';
                $newAuth->status = $armada_ticket->status();
                $newAuth->link = "/armadaticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }
        $data = array_values(collect($data)->sortByDesc('needApproval')->toArray());

        foreach ($data as $key => &$xxx) {
            $xxx->nomor = $key + 1;
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    public function quickApproval(Request $request)
    {
        // budget upload menu
        $budget_upload = BudgetUpload::where('code', $request->code)->first();
        if ($budget_upload) {
            switch ($budget_upload->type) {
                case 'inventory':
                    $newrequest = new Request;
                    if ($request->approval_type == "approve") {
                        $newrequest->replace([
                            'budget_upload_id' => $budget_upload->id,
                        ]);
                        $response = app('App\Http\Controllers\Budget\BudgetUploadController')->approveBudgetAuthorization($newrequest, "api");
                    } else {
                        $newrequest->replace([
                            'budget_upload_id' => $budget_upload->id,
                            'reason' => $request->reason
                        ]);
                        $response = app('App\Http\Controllers\Budget\BudgetUploadController')->rejectBudgetAuthorization($newrequest, "api");
                    }
                    return $response;
                    break;

                case 'armada':
                    $newrequest = new Request;
                    if ($request->approval_type == "approve") {
                        $newrequest->replace([
                            'budget_upload_id' => $budget_upload->id,
                        ]);
                        $response = app('App\Http\Controllers\Budget\ArmadaBudgetUploadController')->approveBudgetAuthorization($newrequest, "api");
                    } else {
                        $newrequest->replace([
                            'budget_upload_id' => $budget_upload->id,
                            'reason' => $request->reason
                        ]);
                        $response = app('App\Http\Controllers\Budget\ArmadaBudgetUploadController')->rejectBudgetAuthorization($newrequest, "api");
                    }
                    return $response;
                    break;

                case 'assumption':
                    $newrequest = new Request;
                    if ($request->approval_type == "approve") {
                        $newrequest->replace([
                            'budget_upload_id' => $budget_upload->id,
                        ]);
                        $response = app('App\Http\Controllers\Budget\AssumptionBudgetUploadController')->approveBudgetAuthorization($newrequest, "api");
                    } else {
                        $newrequest->replace([
                            'budget_upload_id' => $budget_upload->id,
                            'reason' => $request->reason
                        ]);
                        $response = app('App\Http\Controllers\Budget\AssumptionBudgetUploadController')->rejectBudgetAuthorization($newrequest, "api");
                    }
                    return $response;
                    break;

                case 'ho':
                    $newrequest = new Request;
                    if ($request->approval_type == "approve") {
                        $newrequest->replace([
                            'budget_upload_id' => $budget_upload->id,
                        ]);
                        $response = app('App\Http\Controllers\Budget\HOBudgetUploadController')->approveBudgetAuthorization($newrequest, "api");
                    } else {
                        $newrequest->replace([
                            'budget_upload_id' => $budget_upload->id,
                            'reason' => $request->reason
                        ]);
                        $response = app('App\Http\Controllers\Budget\HOBudgetUploadController')->rejectBudgetAuthorization($newrequest, "api");
                    }
                    return $response;
                    break;
                default:
                    break;
            }
        }

        $ticket = Ticket::where('code', $request->code)->first();
        if ($ticket) {
            // ticket approval
            if ($request->transaction_type == "Barang Jasa (ticketing)") {
                $newrequest = new Request;
                if ($request->approval_type == "approve") {
                    $newrequest->replace([
                        'id' => $ticket->id,
                        'updated_at' => $ticket->updated_at,
                    ]);
                    $response = app('App\Http\Controllers\Operational\TicketingController')->approveTicket($newrequest, "api");
                } else {
                    $newrequest->replace([
                        'id' => $ticket->id,
                        'updated_at' => $ticket->updated_at,
                        'reason' => $request->reason
                    ]);
                    $response = app('App\Http\Controllers\Operational\TicketingController')->rejectTicket($newrequest, "api");
                }
                return $response;
            }
        }

        $vendor_evaluation = VendorEvaluation::where('code', $request->code)->first();
        // vendor evaluation approval
        if ($vendor_evaluation) {
            // vendor_evaluation approval
            if ($request->transaction_type == "Vendor Evaluation") {
                $newrequest = new Request;
                if ($request->approval_type == "approve") {
                    $newrequest->replace([
                        'id' => $vendor_evaluation->id,
                        'updated_at' => $vendor_evaluation->updated_at,
                    ]);
                    $response = app('App\Http\Controllers\Operational\VendorEvaluationController')->approveTicket($newrequest, "api");
                } else {
                    $newrequest->replace([
                        'id' => $ticket->id,
                        'updated_at' => $ticket->updated_at,
                        'reason' => $request->reason
                    ]);
                    $response = app('App\Http\Controllers\Operational\VendorEvaluationController')->rejectTicket($newrequest, "api");
                }
                return $response;
            }
        }

        // bidding approval
        if ($request->transaction_type == "Barang Jasa (bidding)") {
            $response = response()->json([
                "error" => true,
                "message" => 'Tidak dapat melakukan quick approval untuk form seleksi vendor / bidding.'
            ]);
            return $response;
        }

        // PR
        if ($request->transaction_type == "Barang Jasa (PR)") {
            $newrequest = new Request;
            if ($request->approval_type == "approve") {
                $newrequest->replace([
                    'pr_id' => $ticket->pr->id,
                ]);
                $response = app('App\Http\Controllers\Operational\PRController')->approvePR($newrequest, "api");
            } else {
                $newrequest->replace([
                    'pr_id' => $ticket->pr->id,
                    'reason' => $request->reason
                ]);
                $response = app('App\Http\Controllers\Operational\PRController')->rejectPR($newrequest, "api");
            }
            return $response;
        }

        $armadaticket = ArmadaTicket::where('code', $request->code)->first();
        if ($armadaticket) {
            // ticket approval
            if ($request->transaction_type == "Armada (ticketing)") {
                $newrequest = new Request;
                if ($request->approval_type == "approve") {
                    $newrequest->replace([
                        'id' => $armadaticket->id,
                        'updated_at' => $armadaticket->updated_at,
                    ]);
                    $response = app('App\Http\Controllers\Operational\ArmadaTicketingController')->approveTicket($newrequest, "api");
                } else {
                    $newrequest->replace([
                        'id' => $armadaticket->id,
                        'updated_at' => $armadaticket->updated_at,
                        'reason' => $request->reason
                    ]);
                    $response = app('App\Http\Controllers\Operational\ArmadaTicketingController')->rejectTicket($newrequest, "api");
                }
                return $response;
            }

            // Form Perpanjangan
            if ($request->transaction_type == "Armada (form perpanjangan)") {
                $newrequest = new Request;
                if ($request->approval_type == "approve") {
                    $newrequest->replace([
                        'perpanjangan_form_id' => $armadaticket->perpanjangan_form->id,
                    ]);
                    $response = app('App\Http\Controllers\Operational\ArmadaTicketingController')->approvePerpanjanganForm($newrequest, "api");
                } else {
                    $newrequest->replace([
                        'perpanjangan_form_id' => $armadaticket->perpanjangan_form->id,
                        'reason' => $request->reason
                    ]);
                    $response = app('App\Http\Controllers\Operational\ArmadaTicketingController')->rejectPerpanjanganForm($newrequest, "api");
                }
                return $response;
            }

            // PR
            if ($request->transaction_type == "Armada (PR)") {
                $newrequest = new Request;
                if ($request->approval_type == "approve") {
                    $newrequest->replace([
                        'pr_id' => $armadaticket->pr->id,
                    ]);
                    $response = app('App\Http\Controllers\Operational\PRController')->approvePR($newrequest, "api");
                } else {
                    $newrequest->replace([
                        'pr_id' => $armadaticket->pr->id,
                        'reason' => $request->reason
                    ]);
                    $response = app('App\Http\Controllers\Operational\PRController')->rejectPR($newrequest, "api");
                }
                return $response;
            }
        }

        $securityticket = SecurityTicket::where('code', $request->code)->first();
        if ($securityticket) {
            // ticket approval
            if ($request->transaction_type == "Security (ticketing)") {
                $newrequest = new Request;
                if ($request->approval_type == "approve") {
                    $newrequest->replace([
                        'id' => $securityticket->id,
                        'updated_at' => $securityticket->updated_at,
                    ]);
                    $response = app('App\Http\Controllers\Operational\SecurityTicketingController')->approveTicket($newrequest, "api");
                } else {
                    $newrequest->replace([
                        'id' => $securityticket->id,
                        'updated_at' => $securityticket->updated_at,
                        'reason' => $request->reason
                    ]);
                    $response = app('App\Http\Controllers\Operational\SecurityTicketingController')->rejectTicket($newrequest, "api");
                }
                return $response;
            }

            // PR
            if ($request->transaction_type == "Security (PR)") {
                $newrequest = new Request;
                if ($request->approval_type == "approve") {
                    $newrequest->replace([
                        'pr_id' => $securityticket->pr->id,
                    ]);
                    $response = app('App\Http\Controllers\Operational\PRController')->approvePR($newrequest, "api");
                } else {
                    $newrequest->replace([
                        'pr_id' => $securityticket->pr->id,
                        'reason' => $request->reason
                    ]);
                    $response = app('App\Http\Controllers\Operational\PRController')->rejectPR($newrequest, "api");
                }
                return $response;
            }
        }
        return response()->json([
            "error" => true,
            "message" => "Kode tidak ditemukan"
        ]);
    }

    public function getCurrentAuthorizationwithType($approval_type)
    {
        $data = [];

        // form perpanjangan
        if ($approval_type == "perpanjangan_form") {
            $perpanjanganauthorization = PerpanjanganFormAuthorization::where('employee_id', Auth::user()->id)
                ->where('status', 0)
                ->get();

            foreach ($perpanjanganauthorization as $author) {
                try {
                    $newAuth = new \stdClass();
                    $armada_ticket = $author->perpanjangan_form->armada_ticket;
                    $perpanjangan_form = $author->perpanjangan_form;
                    if ($armada_ticket->status == -1) {
                        continue;
                    }
                    // jika bukan otorisasi saat ini skip data
                    if ($author->perpanjangan_form->current_authorization()->employee_id != Auth::user()->id) {
                        continue;
                    }
                    $newAuth->perpanjangan_form_id = $perpanjangan_form->id;
                    $newAuth->salespoint = $armada_ticket->salespoint->name;
                    $newAuth->salespoint = $armada_ticket->salespoint->name;
                    $newAuth->isNiaga = $armada_ticket->armada_type->isNiaga;
                    $newAuth->armada_type_name = $armada_ticket->armada_type->name;
                    $newAuth->nopol = $perpanjangan_form->nopol;
                    $newAuth->vendor_name = $perpanjangan_form->nama_vendor;
                    $newAuth->form_type = $perpanjangan_form->form_type;
                    $newAuth->perpanjangan_length = $perpanjangan_form->perpanjangan_length;
                    $newAuth->stopsewa_date = $perpanjangan_form->stopsewa_date;
                    $newAuth->stopsewa_reason = $perpanjangan_form->stopsewa_reason;
                    array_push($data, $newAuth);
                } catch (\Throwable $e) {
                    continue;
                }
            }

            $data = array_values(collect($data)->toArray());
            return response()->json([
                'data' => $data,
            ]);
        }
        return response()->json([
            'data' => [],
        ]);
    }

    public function multiApprove(Request $request)
    {
        try {
            if ($request->approval_type == "perpanjangan_form") {
                $success_count = 0;
                $error_count = 0;
                foreach ($request->perpanjangan_form_ids as $perpanjangan_form_id) {
                    $newrequest = new Request;
                    $newrequest->replace([
                        'perpanjangan_form_id' => $perpanjangan_form_id,
                    ]);
                    $response = app('App\Http\Controllers\Operational\ArmadaTicketingController')->approvePerpanjanganForm($newrequest, "api");
                    $result = json_decode($response->content());
                    if ($result->error) {
                        $error_count++;
                    } else {
                        $success_count++;
                    }
                }
                return back()->with('success', 'Berhasil Melakukan Approval Form Perpanjangan. (Success : ' . $success_count . ' Error : ' . $error_count . ')');
            }
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal Melakukan Approval (' . $ex->getMessage() . $ex->getLine() . ')');
        }
    }

    public function getCurrentAuthorizationCount()
    {
        // Budget Upload Authorization
        $budget_upload_authorization = BudgetUploadAuthorization::leftJoin('budget_upload', 'budget_upload_authorization.budget_upload_id', '=', 'budget_upload.id')
            ->where('budget_upload_authorization.status', 0)
            ->where('budget_upload_authorization.employee_id', Auth::user()->id)
            ->where('budget_upload.status', '!=', 0)
            ->where('budget_upload.deleted_at', null)
            ->get();

        $budget = 0;
        foreach ($budget_upload_authorization as $budget_upload_authorizations) {
            if ($budget_upload_authorizations->level == 1) {
                $budget_before = BudgetUploadAuthorization::where('budget_upload_id', $budget_upload_authorizations->budget_upload_id)
                    ->where('level', 1)
                    ->count();
            } else {
                $budget_before = BudgetUploadAuthorization::where('budget_upload_id', $budget_upload_authorizations->budget_upload_id)
                    ->where('level', $budget_upload_authorizations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($budget_before) {
                    $budget++;
                }
            }
        }

        // Pengadaan Barang Jasa Ticketing Authorization
        $ticketauthorization = TicketAuthorization::leftJoin('ticket', 'ticket_authorization.ticket_id', '=', 'ticket.id')
            ->where('ticket_authorization.status', 0)
            ->where('ticket_authorization.employee_id', Auth::user()->id)
            ->where('ticket.status', '!=', -1)
            ->where('ticket.deleted_at', null)
            ->get();

        $ticket = 0;
        foreach ($ticketauthorization as $ticketauthorizations) {
            if ($ticketauthorizations->level == 1) {
                $ticket_before = TicketAuthorization::where('ticket_id', $ticketauthorizations->ticket_id)
                    ->where('level', 1)
                    ->count();
            } else {
                $ticket_before = TicketAuthorization::where('ticket_id', $ticketauthorizations->ticket_id)
                    ->where('level', $ticketauthorizations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($ticket_before) {
                    $ticket++;
                }
            }
        }

        // Pengadaan Armada Ticketing Authorization
        $armadaticketauthorization = ArmadaTicketAuthorization::leftJoin('armada_ticket', 'armada_ticket_authorization.armada_ticket_id', '=', 'armada_ticket.id')
            ->where('armada_ticket_authorization.status', 0)
            ->where('armada_ticket_authorization.employee_id', Auth::user()->id)
            ->where('armada_ticket.status', '!=', -1)
            ->where('armada_ticket.deleted_at', null)
            ->get();

        $armadaticket = 0;
        foreach ($armadaticketauthorization as $armadaticketauthorizations) {
            if ($armadaticketauthorizations->level == 1) {
                $armadaticket_before = ArmadaTicketAuthorization::where('armada_ticket_id', $armadaticketauthorizations->armada_ticket_id)
                    ->where('level', 1)
                    ->count();
            } else {
                $armadaticket_before = ArmadaTicketAuthorization::where('armada_ticket_id', $armadaticketauthorizations->armada_ticket_id)
                    ->where('level', $armadaticketauthorizations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($armadaticket_before) {
                    $armadaticket++;
                }
            }
        }

        // Pengadaan Security Ticketing Authorization
        $securityticketauthorization = SecurityTicketAuthorization::leftJoin('security_ticket', 'security_ticket_authorization.security_ticket_id', '=', 'security_ticket.id')
            ->where('security_ticket_authorization.status', 0)
            ->where('security_ticket_authorization.employee_id', Auth::user()->id)
            ->where('security_ticket.status', '!=', -1)
            ->where('security_ticket.deleted_at', null)
            ->get();

        $securityticket = 0;
        foreach ($securityticketauthorization as $securityticketauthorizations) {
            if ($securityticketauthorizations->level == 1) {
                $securityticket_before = SecurityTicketAuthorization::where('security_ticket_id', $securityticketauthorizations->security_ticket_id)
                    ->where('level', 1)
                    ->count();
            } else {
                $securityticket_before = SecurityTicketAuthorization::where('security_ticket_id', $securityticketauthorizations->security_ticket_id)
                    ->where('level', $securityticketauthorizations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($securityticket_before) {
                    $securityticket++;
                }
            }
        }

        // Barang Jasa Bidding
        $biddingauthorization = BiddingAuthorization::leftJoin('bidding', 'bidding_authorization.bidding_id', '=', 'bidding.id')
            ->leftJoin('ticket', 'bidding.ticket_id', '=', 'ticket.id')
            ->where('bidding_authorization.status', 0)
            ->where('bidding_authorization.employee_id', Auth::user()->id)
            ->where('bidding.status', '!=', -1)
            ->where('bidding.deleted_at', null)
            ->where('ticket.status', '!=', -1)
            ->where('ticket.deleted_at', null)
            ->get();

        $bidding = 0;
        foreach ($biddingauthorization as $biddingauthorizations) {
            if ($biddingauthorizations->level == 1) {
                $bidding_before = BiddingAuthorization::where('bidding_id', $biddingauthorizations->bidding_id)
                    ->where('level', 1)
                    ->count();
            } else {
                $bidding_before = BiddingAuthorization::where('bidding_id', $biddingauthorizations->bidding_id)
                    ->where('level', $biddingauthorizations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($bidding_before) {
                    $bidding++;
                }
            }
        }

        // PR
        $prauthorization = PrAuthorization::leftJoin('pr', 'pr_authorization.pr_id', '=', 'pr.id')
            ->leftJoin('ticket', 'pr.ticket_id', '=', 'ticket.id')
            ->leftJoin('security_ticket', 'pr.security_ticket_id', '=', 'security_ticket.id')
            ->leftJoin('armada_ticket', 'pr.armada_ticket_id', '=', 'armada_ticket.id')
            ->where('ticket.status', '!=', -1)
            ->where('ticket.deleted_at', null)
            ->where('security_ticket.status', '!=', -1)
            ->where('security_ticket.deleted_at', null)
            ->where('armada_ticket.status', '!=', -1)
            ->where('armada_ticket.deleted_at', null)
            ->where('pr_authorization.status', 0)
            ->where('pr_authorization.employee_id', Auth::user()->id)
            ->where('pr.status', '!=', -1)
            ->where('pr.deleted_at', null)
            ->get();

        $pr = 0;
        foreach ($prauthorization as $prauthorizations) {
            if ($prauthorizations->level == 1) {
                $pr_before = PrAuthorization::where('pr_id', $prauthorizations->pr_id)
                    ->where('level', 1)
                    ->count();
            } else {
                $pr_before = PrAuthorization::where('pr_id', $prauthorizations->pr_id)
                    ->where('level', $prauthorizations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($pr_before) {
                    $pr++;
                }
            }
        }

        // Form Evaluasi
        $evaluasiauthorization = EvaluasiFormAuthorization::leftJoin('evaluasi_form', 'evaluasi_form_authorization.evaluasi_form_id', '=', 'evaluasi_form.id')
            ->leftJoin('security_ticket', 'evaluasi_form.security_ticket_id', '=', 'security_ticket.id')
            ->where('evaluasi_form_authorization.status', 0)
            ->where('evaluasi_form_authorization.employee_id', Auth::user()->id)
            ->where('evaluasi_form_authorization.deleted_at', null)
            ->where('evaluasi_form.deleted_at', null)
            ->where('evaluasi_form.status', '!=', -1)
            ->where('security_ticket.status', '!=', -1)
            ->where('security_ticket.deleted_at', null)
            ->get();

        $evaluasi = 0;
        foreach ($evaluasiauthorization as $evaluasiauthorizations) {
            if ($evaluasiauthorizations->level == 1) {
                $evaluasi_before = EvaluasiFormAuthorization::where('evaluasi_form_id', $evaluasiauthorizations->evaluasi_form_id)
                    ->where('level', 1)
                    ->count();
            } else {
                $evaluasi_before = EvaluasiFormAuthorization::where('evaluasi_form_id', $evaluasiauthorizations->evaluasi_form_id)
                    ->where('level', $evaluasiauthorizations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($evaluasi_before) {
                    $evaluasi++;
                }
            }
        }

        //Form Facility
        $facilityauthorization = FacilityFormAuthorization::leftJoin('facility_form', 'facility_form_authorization.facility_form_id', '=', 'facility_form.id')
            ->leftJoin('armada_ticket', 'facility_form.armada_ticket_id', '=', 'armada_ticket.id')
            ->where('facility_form_authorization.status', 0)
            ->where('employee_id', Auth::user()->id)
            ->where('facility_form.status', '!=', -1)
            ->where('facility_form.deleted_at', null)
            ->where('armada_ticket.status', '!=', -1)
            ->where('armada_ticket.deleted_at', null)
            ->get();

        $facility = 0;
        foreach ($facilityauthorization as $facilityauthorizations) {
            if ($facilityauthorizations->level == 1) {
                $facility_before = FacilityFormAuthorization::where('facility_form_id', $facilityauthorizations->facility_form_id)
                    ->where('level', 1)
                    ->count();
            } else {
                $facility_before = FacilityFormAuthorization::where('facility_form_id', $facilityauthorizations->facility_form_id)
                    ->where('level', $facilityauthorizations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($facility_before) {
                    $facility++;
                }
            }
        }

        // Perpanjangan Form
        $perpanjanganauthorization = PerpanjanganFormAuthorization::leftJoin('perpanjangan_form', 'perpanjangan_form_authorization.perpanjangan_form_id', '=', 'perpanjangan_form.id')
            ->leftJoin('armada_ticket', 'perpanjangan_form.armada_ticket_id', '=', 'armada_ticket.id')
            ->where('perpanjangan_form_authorization.status', 0)
            ->where('employee_id', Auth::user()->id)
            ->where('perpanjangan_form.status', '!=', -1)
            ->where('perpanjangan_form.deleted_at', null)
            ->where('armada_ticket.status', '!=', -1)
            ->where('armada_ticket.deleted_at', null)
            ->get();

        $perpanjangan = 0;
        foreach ($perpanjanganauthorization as $perpanjanganauthorizations) {
            if ($perpanjanganauthorizations->level == 1) {
                $perpanjangan_before = PerpanjanganFormAuthorization::where('perpanjangan_form_id', $perpanjanganauthorizations->perpanjangan_form_id)
                    ->where('level', 1)
                    ->count();
            } else {
                $perpanjangan_before = PerpanjanganFormAuthorization::where('perpanjangan_form_id', $perpanjanganauthorizations->perpanjangan_form_id)
                    ->where('level', $perpanjanganauthorizations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($perpanjangan_before) {
                    $perpanjangan++;
                }
            }
        }

        // Mutasi Form
        $mutasiauthorization = MutasiFormAuthorization::leftJoin('mutasi_form', 'mutasi_form_authorization.mutasi_form_id', '=', 'mutasi_form.id')
            ->leftJoin('armada_ticket', 'mutasi_form.armada_ticket_id', '=', 'armada_ticket.id')
            ->where('mutasi_form_authorization.status', 0)
            ->where('employee_id', Auth::user()->id)
            ->where('mutasi_form.status', '!=', -1)
            ->where('mutasi_form.deleted_at', null)
            ->where('armada_ticket.status', '!=', -1)
            ->where('armada_ticket.deleted_at', null)
            ->get();

        $mutasi = 0;
        foreach ($mutasiauthorization as $mutasiauthorizations) {
            if ($mutasiauthorizations->level == 1) {
                $mutasi_before = MutasiFormAuthorization::where('mutasi_form_id', $mutasiauthorizations->mutasi_form_id)
                    ->where('level', 1)
                    ->count();
                if ($mutasi_before) {
                    $mutasi++;
                }
            } else {
                $mutasi_before = MutasiFormAuthorization::where('mutasi_form_id', $mutasiauthorizations->mutasi_form_id)
                    ->where('level', $mutasiauthorizations->level - 1)
                    ->where('status', 1)
                    ->count();
                if ($mutasi_before) {
                    $mutasi++;
                }
            }
        }

        $request_approval =  $budget + $ticket + $armadaticket + $securityticket + $bidding + $pr + $evaluasi + $facility + $perpanjangan + $mutasi;

        return $request_approval;
    }
}
