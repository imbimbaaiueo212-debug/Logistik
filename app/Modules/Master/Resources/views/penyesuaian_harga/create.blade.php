@extends('layouts.panel')

@section('title', 'Import Perubahan Harga')

@section('content')
@include('partials.page-header', [
    'title'     => 'Import Perubahan Harga',
    'subtitle'  => 'Upload surat permohonan dan data perubahan harga dari percetakan. Harga di Master Product baru berubah setelah di-ACC.',
    'back'      => route('penyesuaian-harga.index'),
    'backLabel' => 'Kembali',
])

@include('partials.flash')

@php
    $rp = fn ($v) => $v === null ? '-' : 'Rp ' . number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => $v === null ? '-' : ($v > 0 ? '+' : '') . number_format($v, 2, ',', '.') . '%';
    $badge = [
        'sama_beli'   => ['Sama dengan 2024', 'bg-green-100 text-green-800'],
        'sama_jual'   => ['Sama (Harga Jual)', 'bg-blue-100 text-blue-800'],
        'beda'        => ['Beda dari 2024', 'bg-red-100 text-red-800'],
        'belum_cocok' => ['Produk belum ketemu', 'bg-gray-100 text-gray-600'],
    ];
    $statusBatch = [
        'menunggu'    => 'bg-amber-100 text-amber-800',
        'dipublikasi' => 'bg-green-100 text-green-800',
        'ditolak'     => 'bg-red-100 text-red-800',
    ];
    $inp = 'border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40';
    $card = 'bg-white rounded-2xl shadow-sm border border-gray-100';
    $fmtPengali = fn ($v) => number_format($v, 2, ',', '.');
@endphp

{{-- ===================== IMPORT 3 FILE ===================== --}}
<div class="{{ $card }} mb-4">

    <form method="POST" action="{{ route('penyesuaian-harga.import') }}" enctype="multipart/form-data" class="px-4 pb-4 pt-1">
        @csrf

        <div class="mb-3 text-xs bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-3 py-2">
            File data boleh lebih dari satu. Kolom yang dibaca: No (boleh kosong), Nama produk, Harga 2024, Harga 2026; kolom persen diabaikan.
            Semua sheet dibaca. Kelompok (Modul &amp; Raport atau Seragam, Tas &amp; KPK) dikenali otomatis dari judul bagian, nama sheet, atau nama produk.
            PDF yang hurufnya dijadikan kurva dibaca dengan OCR (butuh Tesseract &amp; Poppler di server); Excel tidak butuh itu.
            Setelah import, cocokkan angka di tabel dengan dokumen sebelum ACC.
        </div>
        @if($punyaMenunggu)
            <div class="mb-3 text-sm bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-3 py-2">
                Masih ada import yang menunggu ACC. Setujui atau tolak dulu sebelum import baru.
            </div>
        @endif
        @if($errors->any())
            <div class="mb-3 text-sm bg-red-50 border border-red-200 text-red-700 rounded-xl px-3 py-2">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Supplier <span class="text-red-600">*</span></label>
                <select name="supplier_id" id="supplierSelect" required class="{{ $inp }} w-full">
                    <option value="">-- Pilih supplier --</option>
                    @foreach($suppliers as $sp)
                        <option value="{{ $sp->id }}" data-nama="{{ $sp->nama_supplier }}" @selected((string) old('supplier_id') === (string) $sp->id)>{{ $sp->nama_supplier }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1">RA ini milik supplier mana. Terpilih otomatis dari kop surat bila namanya cocok; bisa diganti.</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">No RA</label>
                <input type="text" value="Dibuat otomatis saat import (mis. RA261125-01)" class="{{ $inp }} w-full bg-gray-100 text-gray-500" disabled>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">1. Surat permohonan / bukti <span class="text-red-600">*</span></label>
                <input type="file" name="surat" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="{{ $inp }} w-full" required>
                <p class="text-[11px] text-gray-400 mt-1">Disimpan sebagai bukti. PDF, gambar, Word, atau Excel. Maks 10 MB.</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">2. Lampiran / bukti tambahan (opsional, boleh beberapa file)</label>
                <input type="file" name="lampiran[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="{{ $inp }} w-full">
                <p class="text-[11px] text-gray-400 mt-1">Surat lanjutan, lampiran, atau bukti lain. Bisa ditambah lagi nanti dari halaman detail.</p>
            </div>
        </div>

        <div class="mt-3">
            <label class="block text-xs font-medium text-gray-600 mb-1">3. Data perubahan harga (boleh beberapa file) <span class="text-red-600">*</span></label>
            <input type="file" name="data[]" multiple accept=".pdf,.xls,.xlsx,.csv,.txt" class="{{ $inp }} w-full" required>
            <p class="text-[11px] text-gray-400 mt-1">Pilih semua file sekaligus (tahan Ctrl): Modul &amp; Raport, Seragam/Tas/KPK, dst. Excel, CSV, atau PDF.</p>
        </div>

        <div class="mt-3">
            <label class="block text-xs font-medium text-gray-600 mb-1">Judul (opsional)</label>
            <input type="text" name="judul" value="{{ old('judul') }}" maxlength="150" class="{{ $inp }} w-full" placeholder="terbaca dari Perihal surat; kosong = otomatis, mis. Perubahan Harga Desember 2026">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nomor surat</label>
                <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}" class="{{ $inp }} w-full" placeholder="terbaca otomatis dari surat (periksa hasilnya)">
                <p id="ocrStatus" class="text-[11px] mt-1 text-gray-400">Pilih file surat: supplier, judul (Perihal), nomor, tanggal, dan tanggal berlaku dibaca otomatis.</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal surat</label>
                <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat') }}" class="{{ $inp }} w-full">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Berlaku mulai <span class="text-red-600">*</span></label>
                <input type="date" name="berlaku_mulai" value="{{ old('berlaku_mulai') }}" class="{{ $inp }} w-full" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                <input type="text" name="catatan" value="{{ old('catatan') }}" class="{{ $inp }} w-full" maxlength="1000">
            </div>
        </div>

        <div class="mt-3 flex items-center gap-3">
            <button class="bg-[#E85D2A] text-white px-4 py-2 rounded-xl text-sm disabled:opacity-50" @disabled($punyaMenunggu)>Import &amp; Tampilkan</button>
            <span class="text-xs text-gray-500">Data dibaca dulu dan ditampilkan di bawah. Harga di Master Product baru berubah setelah di-ACC.</span>
        </div>
    </form>
</div>


<script>
// ===== Baca nomor surat dari file surat langsung di browser (OCR), tanpa program tambahan di server =====
(function () {
    var BULAN = {januari:1,februari:2,maret:3,april:4,mei:5,juni:6,juli:7,agustus:8,september:9,oktober:10,november:11,desember:12};
    var NAMA_BULAN = Object.keys(BULAN).join('|');

    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function iso(d, m, y) { return y + '-' + pad(BULAN[m.toLowerCase()]) + '-' + pad(+d); }

    // Ekstrak nomor, tanggal surat, dan tanggal berlaku dari teks hasil OCR / teks PDF
    window.ekstrakInfoSurat = function (teks) {
        var mentah = String(teks || '');
        teks = mentah.replace(/\s+/g, ' ');
        var hasil = {nomor: null, tanggal: null, berlaku: null, perihal: null};

        // Perihal = sisa baris setelah kata "Perihal" (dibaca per baris agar tidak ikut baris berikutnya)
        var p = mentah.match(/Perihal\s*[:.]?[ \t]*([^\n\r]+)/i);
        if (!p) p = teks.match(/Perihal\s*[:.]?\s*(.+?)(?=\s+(?:Kepada|Yth|Dengan hormat)\b|$)/i);
        if (p) {
            var per = p[1].replace(/\s+/g, ' ').replace(/\s+(?:Kepada|Yth|Dengan hormat)\b.*$/i, '').replace(/^[:.\s]+|[\s.,;:]+$/g, '');
            if (per.length >= 5) hasil.perihal = per.slice(0, 150);
        }

        var m = teks.match(/\b(?:Nomor|No\.?)\s*[:.]?\s*([A-Za-z0-9][A-Za-z0-9.\-]*(?:\s?\/\s?[A-Za-z0-9.\-]+)+)/i);
        if (m) {
            var bagian = m[1].replace(/\s+/g, '').split('/');
            if (bagian.length >= 3) {
                var i = bagian.length - 2;   // bulan romawi, OCR sering salah baca "XI" jadi "X1"
                if (/^[XIVl1|]+$/.test(bagian[i])) bagian[i] = bagian[i].replace(/[1l|]/g, 'I');
            }
            hasil.nomor = bagian.join('/');
        }

        var t = teks.match(new RegExp('(?:Jakarta|Bogor|Depok|Tangerang|Bekasi)\\s*,?\\s*(\\d{1,2})\\s+(' + NAMA_BULAN + ')\\s+(\\d{4})', 'i'))
             || teks.match(new RegExp('\\b(\\d{1,2})\\s+(' + NAMA_BULAN + ')\\s+(\\d{4})\\b', 'i'));
        if (t) hasil.tanggal = iso(t[1], t[2], t[3]);

        var b = teks.match(new RegExp('(?:berlaku\\s+mulai|\\bper|mulai\\s+berlaku)\\s+(\\d{1,2})\\s+(' + NAMA_BULAN + ')\\s+(\\d{4})', 'i'));
        if (b) hasil.berlaku = iso(b[1], b[2], b[3]);

        return hasil;
    };

    function muatSkrip(src) {
        return new Promise(function (ok, gagal) {
            var s = document.createElement('script');
            s.src = src; s.onload = ok; s.onerror = function () { gagal(new Error('Gagal memuat ' + src)); };
            document.head.appendChild(s);
        });
    }

    async function keGambar(file) {
        if (/^image\//.test(file.type)) return file;
        if (file.type !== 'application/pdf' && !/\.pdf$/i.test(file.name)) return null;
        if (!window.pdfjsLib) {
            await muatSkrip('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js');
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
        var pdf = await window.pdfjsLib.getDocument({data: await file.arrayBuffer()}).promise;
        var hal = await pdf.getPage(1);
        var vp = hal.getViewport({scale: 2.5});
        var kanvas = document.createElement('canvas');
        kanvas.width = vp.width; kanvas.height = Math.round(vp.height * 0.5);   // separuh atas halaman cukup (nomor ada di kop surat)
        var ctx = kanvas.getContext('2d');
        ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, kanvas.width, kanvas.height);
        await hal.render({canvasContext: ctx, viewport: vp}).promise;
        return kanvas;
    }

    // Cocokkan nama supplier (Master Suplier) dengan kop surat hasil OCR; pilih nama terpanjang yang ditemukan
    function normal(t) {
        return ' ' + String(t || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').replace(/\b(pt|cv|ud|tbk)\b/g, ' ').replace(/\s+/g, ' ').trim() + ' ';
    }
    window.cocokSupplier = function (teks) {
        var sel = document.getElementById('supplierSelect');
        if (!sel) return null;
        var isiTeks = normal(teks), terbaik = null, panjang = 0;
        Array.prototype.forEach.call(sel.options, function (o) {
            if (!o.value) return;
            var nm = normal(o.dataset.nama);
            if (nm.trim().length >= 3 && isiTeks.indexOf(nm) !== -1 && nm.length > panjang) { terbaik = o; panjang = nm.length; }
        });
        return terbaik;
    };

    function isi(nama, nilai) {
        var el = document.querySelector('[name="' + nama + '"]');
        if (el && nilai && !el.value) { el.value = nilai; return true; }
        return false;
    }

    var input = document.querySelector('input[name="surat"]');
    var status = document.getElementById('ocrStatus');
    if (!input || !status) return;

    input.addEventListener('change', async function () {
        var file = input.files && input.files[0];
        if (!file) return;
        status.className = 'text-[11px] mt-1 text-blue-700';
        status.textContent = 'Membaca surat… (pertama kali butuh beberapa detik untuk memuat pembaca teks)';
        try {
            var gambar = await keGambar(file);
            if (!gambar) { status.textContent = 'Format ini tidak bisa dibaca otomatis; isi nomor surat manual.'; return; }
            if (!window.Tesseract) await muatSkrip('https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js');
            var hasil = await window.Tesseract.recognize(gambar, 'eng');
            var info = window.ekstrakInfoSurat(hasil.data.text);

            var terisi = [];
            var sel = document.getElementById('supplierSelect');
            var sup = window.cocokSupplier(hasil.data.text);
            if (sel && sup && !sel.value) { sel.value = sup.value; terisi.push('supplier ' + sup.dataset.nama); }
            if (isi('judul', info.perihal)) terisi.push('judul (dari Perihal)');
            if (isi('nomor_surat', info.nomor)) terisi.push('nomor ' + info.nomor);
            if (isi('tanggal_surat', info.tanggal)) terisi.push('tanggal surat');
            if (isi('berlaku_mulai', info.berlaku)) terisi.push('tanggal berlaku');

            if (terisi.length) {
                status.className = 'text-[11px] mt-1 text-green-700';
                status.textContent = 'Terbaca dari surat: ' + terisi.join(', ') + '. Periksa kembali sebelum import.';
            } else if (info.nomor) {
                status.className = 'text-[11px] mt-1 text-green-700';
                status.textContent = 'Nomor di surat: ' + info.nomor + ' (kolom sudah terisi).';
            } else {
                status.className = 'text-[11px] mt-1 text-amber-700';
                status.textContent = 'Nomor surat tidak ditemukan di surat; isi manual.';
            }
        } catch (e) {
            status.className = 'text-[11px] mt-1 text-amber-700';
            status.textContent = 'Surat tidak bisa dibaca otomatis (perlu koneksi internet untuk memuat pembaca). Isi nomor surat manual.';
        }
    });
})();
</script>

@endsection
