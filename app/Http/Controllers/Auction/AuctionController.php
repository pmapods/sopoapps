<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\VendorLogin;
use Illuminate\Http\Request;


use App\Models\SalesPoint;
use App\Models\Auction;

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

}
