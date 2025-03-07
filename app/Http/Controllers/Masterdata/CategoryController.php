<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Category;

class CategoryController extends Controller
{
    public function categoryView()
    {
        $category = Category::all();
        return view('Masterdata.category', compact('category'));
    }

    public function addCategory(Request $request)
    {
        try {
            $check = Category::where('category', strtolower($request->category))
                ->whereNull('deleted_at')
                ->first();
            if ($check != null) {
                return back()->with('error', 'Category tidak bisa sama / harus berbeda -- ' . $check->category);
            }
            $newCategory                  = new Category;
            $newCategory->category        = ucwords($request->category);
            $newCategory->save();

            return back()->with('success', 'Berhasil menambahkan category');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menambahkan category  "' . $ex->getMessage() . '"');
        }
    }

    public function updateCategory(Request $request)
    {
        try {
            $category = Category::findOrFail($request->id_cat);
            $check = Category::where('category', $request->category)
                ->whereNull('deleted_at')
                ->first();
            if ($check != null) {
                return back()->with('error', 'Category tidak bisa sama / harus berbeda -- ' . $check->category);
            }
            $category->category       = ucwords($request->category);
            $category->save();

            return back()->with('success', 'Berhasil memperbarui category');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal memperbarui category  "' . $ex->getMessage() . '"');
        }
    }

    public function deleteCategory(Request $request)
    {
        try {
            $category = Category::findOrFail($request->id_cat);
            $category->delete();

            return back()->with('success', 'Berhasil menghapus category');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menghapus category  "' . $ex->getMessage() . '"');
        }
    }
}
