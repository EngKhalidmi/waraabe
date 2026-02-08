<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UploadExcelRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ExcelUploadController extends Controller
{
    public function upload(UploadExcelRequest $request)
    {
        $file = $request->file('file');

        try {
            Excel::import(new ProductImport, $file);
            return redirect()->back()->with('success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }
}
