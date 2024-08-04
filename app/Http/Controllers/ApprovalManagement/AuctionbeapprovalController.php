<?php

namespace App\Http\Controllers\ApprovalManagement;

use App\Models\Auction;
use App\Models\AuctionDetail;
use DB;
use Auth;

use App\Models\Ticket;
use App\Models\TicketItem;
use App\Models\ArmadaTicket;
use Illuminate\Http\Request;
use App\Models\SecurityTicket;
use App\Models\TicketMonitoring;
use App\Http\Controllers\Controller;
use App\Models\AuctionVendorBidding;
use App\Models\TicketVendor;
use App\Models\Vendor;
use Ramsey\Uuid\Uuid;

class AuctionbeapprovalController extends Controller
{
    public function auctionView(Request $request)
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
        $tickets = array();
        $pengadaantickets = Auction::select('auction_header.*', 'auction_vendor_bidding.is_approve', 'vendor.name as nama_vendor', 'auction_vendor_bidding.id as id_auction_vendor')
            ->join('auction_vendor_bidding', 'auction_vendor_bidding.auction_header_id', '=', 'auction_header.id')
            ->join('vendor', 'vendor.id', '=', 'auction_vendor_bidding.vendor_id')
            ->where('auction_header.status', 0)
            ->where('auction_vendor_bidding.is_approve', "0")
            ->where("auction_header.type", 'barangjasa')
            ->get();
        // pr hanya untuk pengadaan armada & niaga, non niaga & pr manual
        $armadatickets = Auction::select('auction_header.*', 'auction_vendor_bidding.is_approve', 'vendor.name as nama_vendor', 'auction_vendor_bidding.id as id_auction_vendor')
            ->join('auction_vendor_bidding', 'auction_vendor_bidding.auction_header_id', '=', 'auction_header.id')
            ->join('vendor', 'vendor.id', '=', 'auction_vendor_bidding.vendor_id')
            ->where('auction_header.status', 0)
            ->where('auction_vendor_bidding.is_approve', "0")
            ->where("auction_header.type", 'armada')
            ->get();
        // pr hanya untuk pengadaan security dan pengadaan lembur
        $securitytickets = Auction::select('auction_header.*', 'auction_vendor_bidding.is_approve', 'vendor.name as nama_vendor', 'auction_vendor_bidding.id as id_auction_vendor')
            ->join('auction_vendor_bidding', 'auction_vendor_bidding.auction_header_id', '=', 'auction_header.id')
            ->join('vendor', 'vendor.id', '=', 'auction_vendor_bidding.vendor_id')
            ->where('auction_header.status', 0)
            ->where('auction_vendor_bidding.is_approve', "0")
            ->where("auction_header.type", 'security')
            ->get();
        return view('ApprovalManagement.auctionbe', compact('pengadaantickets', 'armadatickets', 'securitytickets'));
    }
    public function auctionDetailView($type, $id)
    {
        
        $dataAuctionVendor = AuctionVendorBidding::where('id', $id)->first();
        $dataVendor = Vendor::where('id', $dataAuctionVendor->vendor_id)->firstOrFail();
        $auctionHeader = Auction::where('id', $dataAuctionVendor->auction_header_id)->first();
        $auctionDetail = array();
        if ($type == 'barangjasa') {
            $ticket = Ticket::where('code', $auctionHeader->ticket_code)->first();
            $auctionDetail = AuctionDetail::where('auction_header_id', $dataAuctionVendor->auction_header_id)->firstOrFail();
        
        } else if ($type == 'armada') {
            $ticket = ArmadaTicket::where('code', $auctionHeader->ticket_code)->first();
        } else if ($type == 'security') {
            $ticket = SecurityTicket::where('code', $auctionHeader->ticket_code)->first();
        }
        
        return view('ApprovalManagement.auctionbedetail', compact('ticket', 'type', 'dataAuctionVendor', 'dataVendor', 'auctionHeader', 'auctionHeader', 'auctionDetail',));
    }

    public function approveAuction(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'id' => 'required',
                'type' => 'required|in:barangjasa,armada,security',
            ]);
            if ($request->type == 'barangjasa') {
                $dataAuctionVendor = AuctionVendorBidding::where('id', $request->id)->first();
                if ($dataAuctionVendor) {
                    $dataAuctionVendor->is_approve = "1";
                    $dataAuctionVendor->approved_by = Auth::user()->id;
                    $dataAuctionVendor->updated_at = now();
                    $dataAuctionVendor->save();
                    $ticketVendor = TicketVendor::where('ticket_id', $dataAuctionVendor->ticket_id)
                        ->where('vendor_id', $dataAuctionVendor->vendor_id)->first();
                    if (!$ticketVendor) {
                        $inTicVendor = new TicketVendor();
                        $inTicVendor->ticket_id = $dataAuctionVendor->ticket_id;
                        $inTicVendor->vendor_id = $dataAuctionVendor->vendor_id;
                        $inTicVendor->name = $dataAuctionVendor->name;
                        $inTicVendor->salesperson = $dataAuctionVendor->salesperson;
                        $inTicVendor->phone = $dataAuctionVendor->phone;
                        $inTicVendor->type = $dataAuctionVendor->type;
                        $inTicVendor->added_on = "bidding";
                        $inTicVendor->created_at = now();
                        $inTicVendor->updated_at = now();
                        $inTicVendor->save();
                    }
                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id      = $dataAuctionVendor->ticket_id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Approve Auction "' . $request->id;
                    $monitor->save();
                    DB::commit();
                    return redirect('/approve-auction-be')->with('success', 'Berhasil Approve Auction ' . $dataAuctionVendor->name);
                } else {
                    return back()->with('error', 'data Auction Tidak ditemukan)');
                }
            } else if ($request->type == 'armada') {
                $dataAuctionVendor = AuctionVendorBidding::where('id', $request->id)->first();
                if ($dataAuctionVendor) {
                    $dataAuctionVendor->is_approve = "1";
                    $dataAuctionVendor->approved_by = Auth::user()->id;
                    $dataAuctionVendor->updated_at = now();
                    $dataAuctionVendor->save();
                    $auctionHeader = Auction::where('id', $dataAuctionVendor->auction_header_id)->first();
                    $auctionHeader->status = 1;
                    $auctionHeader->save();
                    $vendor = Vendor::where('id', $dataAuctionVendor->vendor_id)->first();
                    $ticketVendor = ArmadaTicket::where('id', $dataAuctionVendor->ticket_id)->first();
                    if ($ticketVendor) {
                        $ticketVendor->vendor_name = $vendor->name;
                        $ticketVendor->vendor_recommendation_name = $vendor->alias;
                        $ticketVendor->save();
                    } else {
                        return back()->with('error', 'data Ticket Tidak ditemukan)');
                    }
                    // $monitor = new TicketMonitoring;
                    // $monitor->ticket_id      = $dataAuctionVendor->ticket_id;
                    // $monitor->employee_id    = Auth::user()->id;
                    // $monitor->employee_name  = Auth::user()->name;
                    // $monitor->message        = 'Approve Auction "' . $request->id;
                    // $monitor->save();
                    DB::commit();
                    return redirect('/approve-auction-be')->with('success', 'Berhasil Approve Auction ' . $dataAuctionVendor->name);
                } else {
                    return back()->with('error', 'data Auction Tidak ditemukan)');
                }
            } else if ($request->type == 'security') {
                $dataAuctionVendor = AuctionVendorBidding::where('id', $request->id)->first();
                if ($dataAuctionVendor) {
                    $dataAuctionVendor->is_approve = "1";
                    $dataAuctionVendor->approved_by = Auth::user()->id;
                    $dataAuctionVendor->updated_at = now();
                    $dataAuctionVendor->save();
                    $auctionHeader = Auction::where('id', $dataAuctionVendor->auction_header_id)->first();
                    $auctionHeader->status = 1;
                    $auctionHeader->save();
                    $vendor = Vendor::where('id', $dataAuctionVendor->vendor_id)->first();
                    $ticketVendor = SecurityTicket::where('id', $dataAuctionVendor->ticket_id)->first();
                    if ($ticketVendor) {
                        $ticketVendor->vendor_name = $vendor->name;
                        $ticketVendor->vendor_recommendation_name = $vendor->alias;
                        $ticketVendor->save();
                    } else {
                        return back()->with('error', 'data Ticket Tidak ditemukan)');
                    }
                    // $monitor = new TicketMonitoring;
                    // $monitor->ticket_id      = $dataAuctionVendor->ticket_id;
                    // $monitor->employee_id    = Auth::user()->id;
                    // $monitor->employee_name  = Auth::user()->name;
                    // $monitor->message        = 'Approve Auction "' . $request->id;
                    // $monitor->save();
                    DB::commit();
                    return redirect('/approve-auction-be')->with('success', 'Berhasil Approve Auction ' . $dataAuctionVendor->name);
                } else {
                    return back()->with('error', 'data Auction Tidak ditemukan)');
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex->getMessage());
            return back()->with('error', 'Gagal Approve Auction');
        }
    }

    public function rejectAuction(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'id' => 'required',
                'type' => 'required|in:barangjasa,armada,security',
                'reason' => 'required',
            ]);
            $dataAuctionVendor = AuctionVendorBidding::where('id', $request->id)->first();
            if ($dataAuctionVendor) {
                $dataAuctionVendor->is_approve = "-1";
                $dataAuctionVendor->rejected_by = Auth::user()->id;
                $dataAuctionVendor->updated_at = now();
                $dataAuctionVendor->reject_reason = $request->reason;
                $dataAuctionVendor->save();
                DB::commit();
                return redirect('/approve-auction-be')->with('success', 'Berhasil Reject Bidding ' . $dataAuctionVendor->name);
            } else {
                return back()->with('error', 'data Ticket Tidak ditemukan)');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Reject Auction');
        }
    }
}
