<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\TicketVendor;
use App\Models\Ticket;
use App\Models\ArmadaTicket;
use App\Models\SecurityTicket;
use App\Models\VendorLogin;
use Illuminate\Http\Request;
use App\Models\SalesPoint;
use App\Models\Auction;
use App\Models\AuctionDetail;
use App\Models\AuctionVendorBidding;
use App\Models\VendorCompany;
use App\Models\Vendor;

use Auth;

class AuctionController extends Controller
{
    public function AuctionView()
    {
        $available_salespoints = SalesPoint::all();
        $available_salespoints = $available_salespoints->groupBy('region');
        $auctions = Auction::all();
        return view('Auction.auction', compact('available_salespoints', 'auctions'), ['title' => 'Auction Ticket']);
    }

    public function AuctionDetailView($code)
    {

        $auctionHeader = Auction::where('ticket_code', $code)->first();
        $auctionDetail = array();
        if ($auctionHeader->type == 'barangjasa') {
            $ticket = Ticket::where('code', $auctionHeader->ticket_code)->first();
            $auctionDetail = AuctionDetail::where('auction_header_id', $auctionHeader->id)->firstOrFail();

        } else if ($auctionHeader->type == 'armada') {
            $ticket = ArmadaTicket::where('code', $auctionHeader->ticket_code)->first();
        } else if ($auctionHeader->type == 'security') {
            $ticket = SecurityTicket::where('code', $auctionHeader->ticket_code)->first();
        }

        return view('Auction.auctiondetail', compact('ticket', 'auctionHeader', 'auctionDetail'), ['title' => 'Auction Ticket Detail']);
    }

    public function AuctionRegisterVendor()
    {
        $available_salespoints = SalesPoint::all();
        $available_salespoints = $available_salespoints->groupBy('region');
        $vendors = VendorLogin::where('status', 0)->all();
        return view('Auction.register', compact('vendors'), ['title' => 'Vendor Request List']);
    }

    public function RequestAuctionBidding(Request $request)
    {
        try {
            $company_code = Auth::guard('vendor')->user()->vendor_code_ref;
            $auction_header = Auction::where('ticket_code', $request->ticket_code)->first();
            $auction_details = AuctionDetail::where('auction_header_id', $auction_header->id)->first();
            $vendor = Vendor::where('code', $company_code)->first();
            $vendor_company = VendorCompany::where('code', $company_code)->first();

            $newAuctionVendorBidding = new AuctionVendorBidding;
            $newAuctionVendorBidding->auction_header_id = $auction_header->id;
            $newAuctionVendorBidding->ticket_id = $auction_details->ticket_id;
            $newAuctionVendorBidding->vendor_id = $vendor->id;
            $newAuctionVendorBidding->name = $vendor_company->company_name;
            $newAuctionVendorBidding->salesperson = $vendor_company->pic_name;
            $newAuctionVendorBidding->phone = $vendor_company->pic_phone;
            $newAuctionVendorBidding->type = 2;
            $newAuctionVendorBidding->added_on = 'bidding auction';
            $newAuctionVendorBidding->created_at = now();
            $newAuctionVendorBidding->updated_at = now();
            $newAuctionVendorBidding->save();

            //update qty vendor bidding table auction header
            $auction_header->is_booked = $auction_header->is_booked + 1;
            $auction_header->update();

            return back()->with('success', 'Berhasil request bidding untuk ticket ' . $request->ticket_code . '. Anda akan mendapatkan notifikasi email jika anda terpilih sebagai vendor kami, Terima kasih');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal request bidding "' . $ex->getMessage() . '"');
        }
    }

}
