<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }
    public function bimbashop()
{
    return view('import.bimbashop');
}

public function bimbashopStore(Request $request)
{
    $request->validate([
        'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
    ]);

    $file = $request->file('import_file');
    $filename = time() . '_' . $file->getClientOriginalName();
    
    // Simpan file
    $path = $file->storeAs('imports/bimbashop', $filename, 'public');

    return back()->with('success', 'File berhasil diupload: ' . $filename . '. Sedang diproses...');
}
}