<?php

namespace App\Http\Controllers\Auction;

use App\Models\Auction;
use App\Models\AuctionDetail;
use DB;
use PDF;
use Auth;
use Mail;
use Storage;
use App\Models\Pr;
use Carbon\Carbon;

use App\Models\Ticket;
use App\Models\IssuePO;
use App\Models\Employee;
use App\Models\PrDetail;
use App\Models\SalesPoint;
use App\Models\TicketItem;
use Illuminate\Support\Str;
use App\Models\ArmadaTicket;
use Illuminate\Http\Request;
use App\Models\Authorization;
use App\Mail\NotificationMail;
use App\Models\SecurityTicket;
use App\Models\PrAuthorization;
use App\Models\EmployeePosition;
use App\Models\TicketMonitoring;
use App\Models\AuthorizationDetail;
use App\Models\TicketAuthorization;
use App\Http\Controllers\Controller;

use App\Models\ArmadaTicketMonitoring;
use App\Models\SecurityTicketMonitoring;
use App\Models\ArmadaTicketAuthorization;
use App\Models\Bidding;
use App\Models\CustomBidding;
use App\Models\SecurityTicketAuthorization;
use App\Models\TicketVendor;
use App\Models\Vendor;
use Ramsey\Uuid\Uuid;

use function PHPSTORM_META\type;

class AuctionbeController extends Controller
{
    public function auctionView(Request $request)
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
        $tickets = array();
        $pengadaantickets = Ticket::whereIn('status', [2, 8])
            ->get();
        // pr hanya untuk pengadaan armada & niaga, non niaga & pr manual
        $armadatickets = ArmadaTicket::whereIn('status', [2, 8])
            ->get();
        // pr hanya untuk pengadaan security dan pengadaan lembur
        $securitytickets = SecurityTicket::whereIn('status', [2, 8])
            ->get();
        // dd($securitytickets);
        return view('Auction.auctionbe', compact('pengadaantickets', 'armadatickets', 'securitytickets'));
    }
    public function auctionDetailView($type, $ticket_code)
    {
        $trashed_ticket_vendors = '';
        if ($type == 'barangjasa') {
            $ticket = Ticket::where('code', $ticket_code)->first();
            $trashed_ticket_vendors = TicketVendor::where('ticket_id', $ticket->id)->onlyTrashed()->get();
        } else if ($type == 'armada') {
            $ticket = ArmadaTicket::where('code', $ticket_code)->first();
        } else if ($type == 'security') {
            $ticket = SecurityTicket::where('code', $ticket_code)->first();
        }
        $vendors = Vendor::all();
        return view('Auction.auctionbedetail', compact('ticket', 'vendors', 'trashed_ticket_vendors', 'type'));
    }

    public function publishAuction(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'id' => 'required|exists:ticket,id',
                'type' => 'required|in:barangjasa,armada,security',
            ]);
            if ($request->type == 'barangjasa') {
                $ticket = Ticket::find($request->id);
                $ticket_item = TicketItem::where('ticket_id', $request->id)->firstOrFail();
                if ($ticket) {
                    $ticket->status = 8;
                    $ticket->auction_status = 1;
                    $ticket->save();
                    $headerID = Uuid::uuid4()->toString();
                    $auctionHeader = new Auction;
                    $auctionHeader->id = $headerID;
                    $auctionHeader->ticket_code = $ticket->code;
                    $auctionHeader->salespoint_id = $ticket->salespoint_id;
                    $auctionHeader->posted_by = Auth::user()->id;
                    $auctionHeader->removed_by = null;
                    $auctionHeader->status = 0;
                    $auctionHeader->notes = $ticket_item->name . ' ' . $ticket_item->brand . ' ' . $ticket_item->type;
                    $auctionHeader->is_booked = $ticket->ticket_vendor->count();
                    $auctionHeader->type = 'barangjasa';
                    $auctionHeader->save();
                    $ticketDetail = TicketItem::select('ticket_item.id', 'ticket_vendor.id as vendor_id', 'ticket_item.name', 'ticket_vendor.salesperson')
                        ->leftjoin('ticket_vendor', 'ticket_item.ticket_id', '=', 'ticket_vendor.ticket_id')
                        ->where('ticket_item.ticket_id', '=', $ticket->id)
                        ->whereNull('ticket_item.deleted_at')
                        ->whereNull('ticket_vendor.deleted_at')
                        ->get();
                    foreach ($ticketDetail as $det) {
                        $aucDetail = new AuctionDetail;
                        $aucDetail->auction_header_id = $headerID;
                        $aucDetail->ticket_id = $ticket->id;
                        $aucDetail->ticket_item_id = $det->id;
                        $aucDetail->product_name = $det->name;
                        $aucDetail->salespoint_name = $det->salesperson;
                        $aucDetail->save();
                    }

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id = $ticket->id;
                    $monitor->employee_id = Auth::user()->id;
                    $monitor->employee_name = Auth::user()->name;
                    $monitor->message = 'Publish Auction "' . $ticket->code;
                    $monitor->save();
                    DB::commit();
                    return redirect('/auctionbe')->with('success', 'Berhasil Publish Auction ' . $ticket->code);
                } else {
                    return back()->with('error', 'data Ticket Tidak ditemukan)');
                }
            } else if ($request->type == 'armada') {
                $ticket = ArmadaTicket::find($request->id);
                if ($ticket) {
                    $ticket->status = 8;
                    $ticket->auction_status = 1;
                    $ticket->save();
                    $headerID = Uuid::uuid4()->toString();
                    $auctionHeader = new Auction();
                    $auctionHeader->id = $headerID;
                    $auctionHeader->ticket_code = $ticket->code;
                    $auctionHeader->salespoint_id = $ticket->salespoint_id;
                    $auctionHeader->posted_by = Auth::user()->id;
                    $auctionHeader->removed_by = null;
                    $auctionHeader->status = 0;
                    $auctionHeader->notes = 'Request Armada Baru';
                    $auctionHeader->type = 'armada';
                    $auctionHeader->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id = $ticket->id;
                    $monitor->employee_id = Auth::user()->id;
                    $monitor->employee_name = Auth::user()->name;
                    $monitor->message = 'Publish Auction "' . $ticket->code;
                    $monitor->save();
                    DB::commit();
                    return redirect('/auctionbe')->with('success', 'Berhasil Publish Auction ' . $ticket->code);
                } else {
                    return back()->with('error', 'data Ticket Tidak ditemukan)');
                }
            } else if ($request->type == 'security') {
                $ticket = SecurityTicket::find($request->id);
                if ($ticket) {
                    $ticket->status = 8;
                    $ticket->auction_status = 1;
                    $ticket->save();
                    $headerID = Uuid::uuid4()->toString();
                    $auctionHeader = new Auction();
                    $auctionHeader->id = $headerID;
                    $auctionHeader->ticket_code = $ticket->code;
                    $auctionHeader->salespoint_id = $ticket->salespoint_id;
                    $auctionHeader->posted_by = Auth::user()->id;
                    $auctionHeader->removed_by = null;
                    $auctionHeader->status = 0;
                    $auctionHeader->notes = 'Request Security Baru';
                    $auctionHeader->type = 'security';
                    $auctionHeader->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id = $ticket->id;
                    $monitor->employee_id = Auth::user()->id;
                    $monitor->employee_name = Auth::user()->name;
                    $monitor->message = 'Publish Auction "' . $ticket->code;
                    $monitor->save();
                    DB::commit();
                    return redirect('/auctionbe')->with('success', 'Berhasil Publish Auction ' . $ticket->code);
                } else {
                    return back()->with('error', 'data Ticket Tidak ditemukan)');
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Publish Auction');
        }
    }

    public function unpublishAuction(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'id' => 'required|exists:ticket,id',
                'type' => 'required|in:barangjasa,armada,security',
            ]);
            if ($request->type == 'barangjasa') {
                $ticket = Ticket::find($request->id);
                if ($ticket) {
                    $ticket->status = 2;
                    $ticket->auction_status = 0;
                    $ticket->save();
                    $auctionHeader = Auction::where('ticket_code', $ticket->code)->first();
                    $auctionHeader->status = 1;
                    $auctionHeader->removed_by = Auth::user()->id;
                    $auctionHeader->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id = $ticket->id;
                    $monitor->employee_id = Auth::user()->id;
                    $monitor->employee_name = Auth::user()->name;
                    $monitor->message = 'UnPublish Auction "' . $ticket->code;
                    $monitor->save();
                    DB::commit();
                    return redirect('/auctionbe')->with('success', 'Berhasil Stop Publish Auction ' . $ticket->code);
                } else {
                    return back()->with('error', 'data Ticket Tidak ditemukan)');
                }
            } else if ($request->type == 'armada') {
                $ticket = ArmadaTicket::find($request->id);
                if ($ticket) {
                    $ticket->status = 2;
                    $ticket->auction_status = 0;
                    $ticket->save();
                    $auctionHeader = Auction::where('ticket_code', $ticket->code)->first();
                    $auctionHeader->status = 1;
                    $auctionHeader->removed_by = Auth::user()->id;
                    $auctionHeader->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id = $ticket->id;
                    $monitor->employee_id = Auth::user()->id;
                    $monitor->employee_name = Auth::user()->name;
                    $monitor->message = 'UnPublish Auction "' . $ticket->code;
                    $monitor->save();
                    DB::commit();
                    return redirect('/auctionbe')->with('success', 'Berhasil Stop Publish Auction ' . $ticket->code);
                } else {
                    return back()->with('error', 'data Ticket Tidak ditemukan)');
                }
            } else if ($request->type == 'security') {
                $ticket = SecurityTicket::find($request->id);
                if ($ticket) {
                    $ticket->status = 2;
                    $ticket->auction_status = 0;
                    $ticket->save();
                    $auctionHeader = Auction::where('ticket_code', $ticket->code)->first();
                    $auctionHeader->status = 1;
                    $auctionHeader->removed_by = Auth::user()->id;
                    $auctionHeader->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id = $ticket->id;
                    $monitor->employee_id = Auth::user()->id;
                    $monitor->employee_name = Auth::user()->name;
                    $monitor->message = 'UnPublish Auction "' . $ticket->code;
                    $monitor->save();
                    DB::commit();
                    return redirect('/auctionbe')->with('success', 'Berhasil Stop Publish Auction ' . $ticket->code);
                } else {
                    return back()->with('error', 'data Ticket Tidak ditemukan)');
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Stop Publish Auction');
        }
    }
}
