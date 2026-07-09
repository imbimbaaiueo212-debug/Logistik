<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RealisasiAktif;
use App\Models\JakartaAktif;
use Illuminate\Support\Facades\DB;

class Majalah2026Controller extends Controller
{
    public function index()
{
    $edisiTersedia = JakartaAktif::query()
        ->select('pesanan')
        ->whereNotNull('pesanan')
        ->get()
        ->map(function ($item) {

            if (preg_match('/M(\d+)/i', $item->pesanan, $match)) {
                return (int) $match[1];
            }

            return null;
        })
        ->filter()
        ->unique()
        ->sort()
        ->values();

    return view(
        'realisasi.majalah.2026.index',
        compact('edisiTersedia')
    );
}

    public function show($edisi)
{
    return view(
        'realisasi.majalah.2026.show',
        compact('edisi')
    );
}
    public function diproses($edisi)
{
    return view('realisasi.majalah.2026.diproses', compact('edisi'));
}

public function kategori($edisi, $kategori)
{
    $query = JakartaAktif::query();

    // Ambil hanya edisi yang dipilih
    $query->where('pesanan', 'like', "%M{$edisi}%");

    switch ($kategori) {

        case 'jkt':
            // Tidak perlu filter lagi
            // Karena Jakarta Aktif memang hasil sync SKU JKT
            break;

        case 'logistik':
            $query->where('pesanan', 'like', '%-LG%');
            break;

        case 'pua':
            $query->where(function ($q) {
                $q->where('pesanan', 'like', '%PUA1%')
                  ->orWhere('pesanan', 'like', '%PUA2%')
                  ->orWhere('pesanan', 'like', '%PUA3%');
            });
            break;

        case 'dlc':
            $query->where('pesanan', 'like', '%DLC%');
            break;

        case 'ops2':
            // nanti kita tambahkan aturan OPS II
            break;
    }

    $data = $query
        ->orderBy('payment_date', 'asc')   // atau tgl_pesan
        ->paginate(200);

    return view(
        'realisasi.majalah.2026.kategori',
        compact('edisi', 'kategori', 'data')
    );
}
}