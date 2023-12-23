<?php

namespace App\Http\Controllers\Masterdata;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FileCategory;

class FileCompletementController extends Controller
{
    public function fileCompletementView(){
        $categories = FileCategory::all();
        return view('Masterdata.filecompletement',compact('categories'));
    }
}
