<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Uom;

class UOMController extends Controller
{
    public function uomView()
    {
        $uom = Uom::all();
        return view('Masterdata.uom', compact('uom'));
    }

    public function addUOM(Request $request)
    {
        try {
            $check = Uom::where('uom', strtolower($request->uom))
                ->whereNull('deleted_at')
                ->first();
            if ($check != null) {
                return back()->with('error', 'Uom tidak bisa sama / harus berbeda -- ' . $check->uom);
            }
            $newUom                  = new Uom;
            $newUom->uom            = ucwords($request->uom);
            $newUom->save();
            return back()->with('success', 'Berhasil menambahkan uom');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menambahkan uom  "' . $ex->getMessage() . '"');
        }
    }

    public function updateUOM(Request $request)
    {
        try {
            $uom = Uom::findOrFail($request->id_uom);
            $check = Uom::where('uom', strtolower($request->uom))
                ->whereNull('deleted_at')
                ->first();
            if ($check != null) {
                return back()->with('error', 'Uom tidak bisa sama / harus berbeda -- ' . $check->uom);
            }
            $uom->uom       = ucwords($request->uom);
            $uom->save();

            return back()->with('success', 'Berhasil memperbarui uom');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal memperbarui uom  "' . $ex->getMessage() . '"');
        }
    }

    public function deleteUOM(Request $request)
    {
        try {
            $uom = Uom::findOrFail($request->id);
            $uom->delete();

            return back()->with('success', 'Berhasil menghapus uom');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menghapus uom  "' . $ex->getMessage() . '"');
        }
    }
}
