<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Material;
use App\Models\Salespoint;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Uom;

class MaterialController extends Controller
{
    public function materialView()
    {
        $material = Material::all();
        $salespoint = Salespoint::all();
        $provinces = Province::all();
        $regency = Regency::inRandomOrder()->first()->name;
        $uom = Uom::all();
        $uom2 = Uom::all();
        return view('Masterdata.material', compact('material', 'salespoint', 'provinces', 'uom', 'uom2'));
    }

    public function addMaterial(Request $request)
    {
        try {
            $check = Material::where('code', $request->kode)->first();
            if ($check != null) {
                return back()->with('error', 'Kode material tidak bisa sama / harus berbeda -- ' . $request->code . ' ' . $check->name);
            }
            
            $newMaterial                     = new Material;
            $newMaterial->code               = $request->kode;
            $newMaterial->material           = $request->nama;
            $newMaterial->alias              = $request->alias;
            $newMaterial->dimension          = $request->dimension;
            $newMaterial->salespoint         = $request->city;
            $newMaterial->uom_id             = $request->uom;
            $newMaterial->save();

            return back()->with('success', 'Berhasil menambahkan material');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menambahkan material  "' . $ex->getMessage() . '"');
        }
    }

    public function updateMaterial(Request $request)
    {
        try {
            $material = Material::where('code', $request->kode)->first();
            $material->material           = $request->nama;
            $material->alias              = $request->alias;
            $material->dimension          = $request->dimension;
            $material->salespoint         = $request->city;
            $material->uom_id             = $request->uom;
            $material->save();

            return back()->with('success', 'Berhasil memperbarui material');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal memperbarui material  "' . $ex->getMessage() . '"');
        }
    }

    public function deleteMaterial(Request $request)
    {
        try {
            $material = Material::where('code', $request->kode)->first();
            $material->delete();

            return back()->with('success', 'Berhasil menghapus material');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menghapus material  "' . $ex->getMessage() . '"');
        }
    }
}
