<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Ticket;
use App\Models\ArmadaTicket;
use App\Models\SecurityTicket;
use App\Models\TicketItem;
use App\Models\TicketAuthorization;
use App\Models\TicketItemAttachment;
use App\Models\Authorization;
use DB;
use Auth;
use Carbon\Carbon;

class CustomTicketingController extends Controller
{
    public function customTicketingView()
    {
        $custom_ticketings = DB::table('custom_ticketing')->get();
        return view('Masterdata.customticketing', compact('custom_ticketings'));
    }

    public function createCustomTicket(Request $request)

    {
        try {
            $parameters['ticket_name'] = $request->ticket_name;
            $parameters['ticket_item_name'] = $request->ticket_item_name;
            $parameters['uom'] = $request->uom;
            $parameters['item_type'] = $request->item_type;
            $parameters['status'] = $request->status;
            if ($request->item_type == 'disposal') {
                $parameters['steps'] = array_filter($request->step, function ($perStep) {
                    if ($perStep != 'po_sap') {
                        return true;
                    }
                });
                array_push($parameters['steps'], 'bidding');
                $parameters['steps'] = array_values($parameters['steps']);
            } else {
                $parameters['steps'] = $request->step;
            }

            if (in_array("create_ticket", $request->step)) {
                if (count($this->filter_null_array($request->create_ticket_file)) < 1) {
                    throw new \Exception("Minimal satu file untuk step Upload buat tiket");
                }
                $parameters['create_ticket_file'] = $this->filter_null_array($request->create_ticket_file);
            }
            if (in_array("received_file_upload", $request->step)) {
                if (count($this->filter_null_array($request->received_file_name)) < 1) {
                    throw new \Exception("Minimal satu file untuk step Penerimaan");
                }
                $parameters['received_file_name'] = $this->filter_null_array($request->received_file_name);
            }

            DB::table('custom_ticketing')->insert([
                'settings' => json_encode($parameters),
                'is_active' => $request->status
            ]);
            return back()->with('success', 'Berhasil menambahkan custom ticket');
        } catch (\Exception $ex) {
            return back()->with('error', "Gagal membuat custom tiket (" . $ex->getMessage() . ")[" . $ex->getLine() . "]");
        }
    }

    public function updateCustomTicket(Request $request)
    {
        try {
            $custom_ticketing = DB::table('custom_ticketing')->find($request->custom_ticketing_id);
            if (!$custom_ticketing) {
                throw new \Exception('Ticket tidak ditemukan, silahkan coba kembali.');
            }
            $parameters['ticket_name'] = $request->ticket_name;
            $parameters['ticket_item_name'] = $request->ticket_item_name;
            $parameters['uom'] = $request->uom;
            $parameters['item_type'] = $request->item_type;
            $parameters['status'] = $request->status;

            if ($request->item_type == 'disposal') {
                $parameters['steps'] = array_filter($request->step, function ($perStep) {
                    if ($perStep != 'po_sap') {
                        return true;
                    }
                });
                array_push($parameters['steps'], 'bidding');
                $parameters['steps'] = array_values($parameters['steps']);
            } else {
                $parameters['steps'] = $request->step;
            }

            if (in_array("create_ticket", $request->step)) {
                if (count($this->filter_null_array($request->create_ticket_file)) < 1) {
                    throw new \Exception("Minimal satu file untuk step Upload buat tiket");
                }
                $parameters['create_ticket_file'] = $this->filter_null_array($request->create_ticket_file);
            }
            if (in_array("received_file_upload", $request->step)) {
                if (count($this->filter_null_array($request->received_file_name)) < 1) {
                    throw new \Exception("Minimal satu file untuk step Penerimaan");
                }
                $parameters['received_file_name'] = $this->filter_null_array($request->received_file_name);
            }

            DB::table('custom_ticketing')
                ->where('id', $request->custom_ticketing_id)
                ->update([
                    'settings' => json_encode($parameters),
                    'is_active' => $request->status
                ]);
            return back()->with('success', 'Berhasil update custom ticket');
        } catch (\Exception $ex) {
            return back()->with('error', "Gagal update custom ticket (" . $ex->getMessage() . ")[" . $ex->getLine() . "]");
        }
    }

    public function deleteCustomTicket(Request $request)
    {
        try {
            DB::beginTransaction();
            DB::table('custom_ticketing')->where('id', $request->custom_ticketing_id)->delete();
            DB::commit();
            return back()->with('success', "Berhasil hapus custom ticket");
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', "Gagal hapus custom ticket (" . $ex->getLine() . ") [" . $ex->getMessage() . "]");
        }
    }

    private function filter_null_array($array)
    {
        $filtered_array = [];
        foreach ($array as $a) {
            if ($a != null) {
                $filename = trim($a);
                if ($filename != "") {
                    array_push($filtered_array, $filename);
                }
            }
        }
        return $filtered_array;
    }
}
