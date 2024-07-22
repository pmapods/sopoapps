<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\VendorLogin;
use Illuminate\Http\Request;

use Ramsey\Uuid\Uuid;
use App\Models\SalesPoint;
use App\Models\Auction;
use App\Models\AuctionDetail;
use App\Models\Ticket;
use App\Models\TicketItem;
use App\Models\TicketVendor;

use DB;
use Auth;
use Log;

class AuctionController extends Controller
{
    public function AuctionView()
    {
        $available_salespoints = SalesPoint::all();
        $available_salespoints = $available_salespoints->groupBy('region');
        $auctions = Auction::all();
        return view('Auction.auction', compact('available_salespoints', 'auctions'), ['title' => 'Auction Ticket']);
    }

    public function AuctionRegisterVendor()
    {
        $available_salespoints = SalesPoint::all();
        $available_salespoints = $available_salespoints->groupBy('region');
        $vendors = VendorLogin::where('status',0)->all();
        return view('Auction.register', compact('vendors'), ['title' => 'Vendor Request List']);
    }

    public function addAuctionTicket(Request $request)
    {
        try {

            // Cari tiket
            $ticket = Ticket::where('id', $request->ticket_id)->firstOrFail();
            $ticket_item = TicketItem::where('ticket_id', $request->ticket_id)->firstOrFail();
            $vendor_ticket = TicketVendor::where('ticket_id', $request->ticket_id)->get();
            $uuid = Uuid::uuid4();

            DB::beginTransaction();

            $newAuction = new Auction;
            $newAuction->id             = $uuid->toString();
            $newAuction->ticket_id      = $request->ticket_id;
            $newAuction->ticket_code    = $ticket->code;
            $newAuction->salespoint_id  = $ticket->salespoint_id;
            $newAuction->type           = 'barangjasa';
            $newAuction->notes          = $ticket_item->name . ' ' . $ticket_item->brand . ' ' . $ticket_item->type;
            $newAuction->status         = 0;
            $newAuction->is_booked      = $vendor_ticket->count();
            $newAuction->deleted_at     = null;
            $newAuction->created_at     = now();
            $newAuction->updated_at     = now();
            $newAuction->save();

            $newAuctionDetail = new AuctionDetail;
            $newAuctionDetail->auction_header_id = $newAuction->id;
            $newAuctionDetail->ticket_id         = $request->ticket_id;
            $newAuctionDetail->ticket_item_id    = $ticket_item->id;
            $newAuctionDetail->product_name      = $ticket_item->name;
            $newAuctionDetail->salespoint_name   = $ticket_item->name . ' ' . $ticket_item->brand . ' ' . $ticket_item->type;
            $newAuctionDetail->posted_by         = Auth::user()->id;
            $newAuctionDetail->removed_by        = null;
            $newAuctionDetail->deleted_at        = null;
            $newAuctionDetail->created_at        = now();
            $newAuctionDetail->updated_at        = now();
            $newAuctionDetail->save();

            DB::commit();

            return redirect('/bidding/')->with('success', 'Berhasil dimasukan ke dalam list lelang.');
        } catch (\Exception $ex) {
            DB::rollback();
            // Tambahkan logging untuk menangkap pesan error
            Log::error('Error adding auction ticket: ' . $ex->getMessage(), ['exception' => $ex]);
            return redirect('/bidding/')->with('error', 'Gagal memasukan ticket ke dalam list lelang. Silahkan coba kembali atau hubungi developer');
        }
    }
}
