<?php

namespace App\Http\Controllers\Dashboard;

use DB;
use Auth;
use Hash;
use App\Models\Po;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Http\Controllers\Controller;

class DashboardPrManualAssetController extends Controller
{
    public function dashboardPrManualAssetView()
    {
        return view('Dashboard.prmanualasset');
    }

    public function getPrManualAsset()
    {
        $data = [];
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
        $pr_manual_assets = Ticket::where('status', '=', 5)
            ->whereIn('salespoint_id', $salespoint_ids)
            ->orderBy('created_at')
            ->get();
        $pr_manual_assets = $pr_manual_assets->unique('code');
        $pr_manual_assets->values()->all();

        foreach ($pr_manual_assets as $pr_manual_asset) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $pr_manual_asset->salespoint->name;
                $newAuth->ticket_code = $pr_manual_asset->code;
                $newAuth->created_at = $pr_manual_asset->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $pr_manual_asset->created_by_employee->name;
                $newAuth->transaction_type = 'PR Manual Asset';
                $newAuth->status = 'Perlu Submit Nomor Asset Manual';
                $newAuth->link = "/pr/" . $newAuth->ticket_code;

                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        $data = array_values(collect($data)->toArray());

        foreach ($data as $key => &$xxx) {
            $xxx->nomor = $key + 1;
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    public function getPrManualAssetCount()
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
        $pr_manual_assets = Ticket::where('status', '=', 5)
            ->whereIn('salespoint_id', $salespoint_ids)
            ->get();
        $pr_manual_assets = $pr_manual_assets->unique('code');
        $pr_manual_assets->values()->all();
        $pr_manual_asset = $pr_manual_assets->count();

        return $pr_manual_asset;
    }
}
