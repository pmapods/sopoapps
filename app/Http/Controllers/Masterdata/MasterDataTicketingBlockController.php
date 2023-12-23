<?php

namespace App\Http\Controllers\Masterdata;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TicketingBlock;

class MasterDataTicketingBlockController extends Controller
{
    public function ticketingBlockView()
    {
        $ticketing_blocks = TicketingBlock::with(['created_by_employee', 'last_update_by_employee'])->get();

        return view('Masterdata.ticketingblock', compact('ticketing_blocks'));
    }

    public function createTicketingBlock(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticketing_block = DB::table('ticketing_block');

            $ticketing_block                            = new TicketingBlock;
            $ticketing_block->ticketing_type_name       = $request->ticketing_type_name;
            $ticketing_block->block_day                 = $request->block_day;
            $ticketing_block->max_block_day             = $request->max_block_day;
            $ticketing_block->max_pr_sap_day            = $request->max_pr_sap_day;
            $ticketing_block->max_validation_reject_day = $request->max_validation_reject_day;
            $ticketing_block->created_by                = Auth::user()->id;
            $ticketing_block->save();
            DB::commit();

            return back()->with('success', 'Berhasil menambahkan ticketing block');
        } catch (\Exception $ex) {
            return back()->with('error', "Gagal membuat ticketing block (" . $ex->getMessage() . ")[" . $ex->getLine() . "]");
        }
    }

    public function updateTicketingBlock(Request $request)
    {
        try {
            $ticketing_block = DB::table('ticketing_block');

            $ticketing_block                            = TicketingBlock::findOrFail($request->id);
            $ticketing_block->ticketing_type_name       = $request->ticketing_type_name;
            $ticketing_block->block_day                 = $request->block_day;
            $ticketing_block->max_block_day             = $request->max_block_day;
            $ticketing_block->max_pr_sap_day            = $request->max_pr_sap_day;
            $ticketing_block->max_validation_reject_day = $request->max_validation_reject_day;
            $ticketing_block->last_update_by                = Auth::user()->id;
            $ticketing_block->save();
            DB::commit();

            return back()->with('success', 'Berhasil update ticketing block');
        } catch (\Exception $ex) {
            return back()->with('error', "Gagal update ticketing block (" . $ex->getMessage() . ")[" . $ex->getLine() . "]");
        }
    }

    public function resetTicketingBlock()
    {
        try {
            $armada_block                            = DB::table('ticketing_block');
            $armada_block                            = TicketingBlock::where('id', '=', '1')->first();
            $armada_block->block_day                 = 10;
            $armada_block->max_block_day             = 15;
            $armada_block->max_pr_sap_day            = 15;
            $armada_block->max_validation_reject_day = 18;
            $armada_block->last_update_by            = Auth::user()->id;
            $armada_block->save();

            $security_block                            = DB::table('ticketing_block');
            $security_block                            = TicketingBlock::where('id', '=', '2')->first();
            $security_block->block_day                 = 5;
            $security_block->max_block_day             = 9;
            $security_block->max_pr_sap_day            = 10;
            $security_block->max_validation_reject_day = 13;
            $security_block->last_update_by            = Auth::user()->id;
            $security_block->save();

            $cit_block                            = DB::table('ticketing_block');
            $cit_block                            = TicketingBlock::where('id', '=', '3')->first();
            $cit_block->block_day                 = 5;
            $cit_block->max_block_day             = 9;
            $cit_block->max_pr_sap_day            = 10;
            $cit_block->max_validation_reject_day = 13;
            $cit_block->last_update_by            = Auth::user()->id;
            $cit_block->save();

            $pest_block                            = DB::table('ticketing_block');
            $pest_block                            = TicketingBlock::where('id', '=', '4')->first();
            $pest_block->block_day                 = 10;
            $pest_block->max_block_day             = 14;
            $pest_block->max_pr_sap_day            = 15;
            $pest_block->max_validation_reject_day = 18;
            $pest_block->last_update_by            = Auth::user()->id;
            $pest_block->save();

            $vendor_block                            = DB::table('ticketing_block');
            $vendor_block                            = TicketingBlock::where('id', '=', '5')->first();
            $vendor_block->block_day                 = 5;
            $vendor_block->max_block_day             = 10;
            $vendor_block->max_pr_sap_day            = 10;
            $vendor_block->max_validation_reject_day = 13;
            $vendor_block->last_update_by            = Auth::user()->id;
            $vendor_block->save();

            $merchen_block                            = DB::table('ticketing_block');
            $merchen_block                            = TicketingBlock::where('id', '=', '6')->first();
            $merchen_block->block_day                 = 10;
            $merchen_block->max_block_day             = 14;
            $merchen_block->max_pr_sap_day            = 15;
            $merchen_block->max_validation_reject_day = 18;
            $merchen_block->last_update_by            = Auth::user()->id;
            $merchen_block->save();

            DB::commit();

            return back()->with('success', 'Berhasil reset ticketing block');
        } catch (\Exception $ex) {
            return back()->with('error', "Gagal reset ticketing block (" . $ex->getMessage() . ")[" . $ex->getLine() . "]");
        }
    }
}
