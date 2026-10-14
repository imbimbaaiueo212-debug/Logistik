<?php

namespace App\Http\Controllers;

use App\Imports\UserExportBimbaShopImport;
use App\Models\UserExportBimbaShop;
use App\Support\BimbaCsvRepair;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class UserExportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 50); // default 50

        $users = UserExportBimbaShop::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ID', 'like', "%{$search}%")
                      ->orWhere('user_login', 'like', "%{$search}%")
                      ->orWhere('user_email', 'like', "%{$search}%")
                      ->orWhere('display_name', 'like', "%{$search}%")
                      ->orWhere('billing_email', 'like', "%{$search}%")
                      ->orWhere('billing_phone', 'like', "%{$search}%")
                      ->orWhere('billing_first_name', 'like', "%{$search}%")
                      ->orWhere('billing_last_name', 'like', "%{$search}%");
                });
            })
            ->latest('ID')
            ->paginate($perPage)
            ->appends(['search' => $search, 'per_page' => $perPage]);

        return view('user-export.index', compact('users', 'search', 'perPage'));
    }

    public function import(Request $request)
    {
        // Validasi dasar: file wajib ada, berhasil terupload, maksimal 10 MB.
        // 'mimes' sengaja tidak dipakai karena bergantung pada ekstensi PHP "fileinfo".
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->back()
                ->with('error', '❌ File harus berformat xlsx, xls, atau csv.');
        }

        $tmpPath = null;

        try {
            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');

            $source = $file;
            $type   = null;

            // CSV yang rusak (baris dibungkus kutip + ";;") diperbaiki otomatis
            if ($ext === 'csv') {
                $type = ExcelType::CSV;

                if (BimbaCsvRepair::isWrapped($file->getRealPath())) {
                    $tmpPath = BimbaCsvRepair::repairToTempFile($file->getRealPath());

                    $source = new UploadedFile(
                        $tmpPath,
                        'user_export_cleaned.csv',
                        'text/csv',
                        null,
                        true // mode test: izinkan file lokal non-upload
                    );
                }
            }

            Excel::import(new UserExportBimbaShopImport, $source, null, $type);

            return redirect()->route('user.export')
                ->with('success', '✅ Data berhasil diimport!');

        } catch (\Exception $e) {
            Log::error('Import Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', '❌ Gagal import: ' . $e->getMessage());

        } finally {
            if ($tmpPath && is_file($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }
}
