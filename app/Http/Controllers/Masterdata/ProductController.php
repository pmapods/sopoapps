<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Salespoint;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Uom;
use App\Models\Category;

class ProductController extends Controller
{
    public function productView()
    {
        $product = Product::all();
        $salespoint = Salespoint::all();
        $provinces = Province::all();
        $regency = Regency::inRandomOrder()->first()->name;
        $uom = Uom::all();
        $uom2 = Uom::all();
        $category = Category::all();
        $category2 = Category::all();
        return view('Masterdata.product', compact('product', 'salespoint', 'provinces', 'uom', 'category', 'uom2', 'category2'));
    }

    public function addProduct(Request $request)
    {
        try {
            $check = Product::where('code', $request->kode)->first();
            if ($check != null) {
                return back()->with('error', 'Kode product tidak bisa sama / harus berbeda -- ' . $request->code . ' ' . $check->name);
            }
            
            $newProduct                     = new Product;
            $newProduct->code               = $request->kode;
            $newProduct->nama_barang        = $request->nama;
            $newProduct->alias              = $request->alias;
            $newProduct->dimension          = $request->dimension;
            $newProduct->harga_jual         = $request->harga_jual;
            $newProduct->harga_sewa_harian  = $request->harga_sewa;
            $newProduct->salespoint         = $request->city;
            $newProduct->category_id        = $request->category;
            $newProduct->uom_id             = $request->uom;
            $newProduct->save();

            return back()->with('success', 'Berhasil menambahkan product');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menambahkan product  "' . $ex->getMessage() . '"');
        }
    }

    public function updateProduct(Request $request)
    {
        try {
            $product = Product::where('code', $request->kode)->first();
            $product->nama_barang        = $request->nama;
            $product->alias              = $request->alias;
            $product->dimension          = $request->dimension;
            $product->harga_jual         = $request->harga_jual;
            $product->harga_sewa_harian  = $request->harga_sewa;
            $product->salespoint         = $request->city;
            $product->category_id        = $request->category;
            $product->uom_id             = $request->uom;
            $product->save();

            return back()->with('success', 'Berhasil memperbarui product');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal memperbarui product  "' . $ex->getMessage() . '"');
        }
    }

    public function deleteProduct(Request $request)
    {
        try {
            $product = Product::where('code', $request->kode)->first();
            $product->delete();

            return back()->with('success', 'Berhasil menghapus product');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menghapus product  "' . $ex->getMessage() . '"');
        }
    }
}
