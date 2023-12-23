<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Crypt;
use Storage;
use App\Models\Armada;
use App\Models\Ticket;
use App\Models\Vendor;
use App\Models\Bidding;

use App\Models\PrDetail;
use App\Models\ArmadaType;
use App\Models\SalesPoint;
use Illuminate\Http\Request;
use App\Models\TicketMonitoring;

class APIController extends Controller
{
    public function testAPI()
    {
        return response()->json([
            "error" => false,
            "message" => "connection success"
        ]);
    }
    public function updateAssetNumber(Request $request)
    {
        try {
            $authors = $request->authors;
            $items = $request->data;
            $ticket_code = $items[0]['ticket_code'] ?? null;

            DB::beginTransaction();
            $ticket = Ticket::where('code', $ticket_code)->first();
            if ($ticket == null) {
                throw new \Exception('Pr tidak valid');
            }

            $pr = $ticket->pr;
            if ($pr->status < 1) {
                // Kalau PR belum siap di update
                throw new \Exception('Status PR saat ini belum dapat diisi nomor asset.');
            }
            $pr->assetnumber_by = -1;
            if ($ticket != null) {
                foreach ($items as $key => $item) {
                    $pr_detail = PrDetail::where("asset_number_token", $item['asset_number_token'])->first();
                    if (!$pr_detail) {
                        // not found token to update throw error
                        throw new \Exception('Token Number ' . $item['asset_number_token'] . ' not found');
                    }
                    if ($item['isAsset'] == true) {
                        $has_asset_flag = false;
                        // jika asset_numbers ada file diisi maka set iAsAsset menjadi true
                        // simpan nomor
                        if (isset($item['no_asset'])) {
                            $asset_number_list = explode(',', $item['no_asset']);
                            $asset_number_list = array_map('trim', $asset_number_list);
                            $asset_number_list = array_filter($asset_number_list, function ($asset_number) {
                                if ($asset_number == "" || $asset_number == null) {
                                    return false;
                                } else {
                                    return true;
                                }
                            });
                            if (count($asset_number_list) > 0) {
                                $has_asset_flag = true;
                                $pr_detail->asset_number = json_encode(array_values($asset_number_list));
                            } else {
                                $pr_detail->asset_number = json_encode([]);
                            }
                        } else {
                            $pr_detail->asset_number = json_encode([]);
                        }
                        if (isset($item["asset_numbers_file"])) {
                            $file = file_get_contents($item["asset_numbers_file"]);
                            $ext = pathinfo($item["asset_numbers_file"], PATHINFO_EXTENSION);
                            $name = $pr_detail->name . "_" . $pr_detail->id . '_assetnumber.' . $ext;
                            $name = str_replace([" ", "/"], "_", $name);
                            $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/assetnumber/' . $name;
                            Storage::disk("public")->put($path, $file);

                            $has_asset_flag = true;
                            $pr_detail->asset_number_filepath = $path;
                        }
                    }
                    $pr_detail->isAsset = $item['isAsset'];
                    $pr_detail->save();
                }
                // update status kalo belom ke update aja
                if ($pr->status < 2) {
                    $pr->status = 2;
                }
                $pr->save();

                if ($ticket->status < 6) {
                    $ticket->status = 6;
                }
                $ticket->save();

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = -1;
                $monitor->employee_name  = "Web Asset (by system)";
                $monitor->message        = 'Update Nomor Asset di PR';
                $monitor->save();
            }

            DB::commit();
            return response()->json([
                'error' => false,
                'message' => 'successfully access update asset number function'
            ], 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'error' => true,
                'message' => $ex->getMessage() . " (" . $ex->getLine() . ")"
            ], 201);
        }
    }

    public function printBidding($encrypted_bidding_id)
    {
        try {
            $decrypted = Crypt::decryptString($encrypted_bidding_id);
        } catch (\Exception $ex) {
            abort(404);
        }
        $bidding = Bidding::find($decrypted);
        $ticket_item = $bidding->ticket_item;
        $ticket = $bidding->ticket;
        return view('Operational.biddingprintoutview', compact('ticket_item', 'ticket', 'bidding'));
    }

    public function armadaView()
    {
        return ArmadaType::all();
    }

    public function vendorView()
    {
        return Vendor::where('type', 'armada')->get();
    }

    public function salespointView()
    {
        return SalesPoint::all();
    }
}
