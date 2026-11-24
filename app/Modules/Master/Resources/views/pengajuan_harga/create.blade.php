@extends('layouts.panel')

@section('title', 'Buat Pengajuan Harga')

@section('content')
@include('partials.page-header', [
    'title'    => 'Buat Pengajuan Perubahan Harga',
    'subtitle' => 'Pilih satu atau banyak produk, isi harga baru, lalu upload surat permohonan dan berkas pendukung (bisa lebih dari satu).',
])

@include('partials.flash')

@php
    $inp = 'w-full border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40';
@endphp

@if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-xl text-sm">
        <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('pengajuan-harga.store') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf

    {{-- 1. Produk --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold mb-1">1. Produk yang berubah harganya</h3>
        <p class="text-xs text-gray-500 mb-3">Kosongkan kolom harga yang tidak diubah. Harga jual bisa dihitung otomatis dari harga beli (modul ×1,49, lainnya ×1,20, dibulatkan ke atas per Rp 50).</p>

        <div class="relative mb-3 max-w-xl">
            <input type="text" id="cariProduk" autocomplete="off" placeholder="Ketik nama produk atau label (SKU) untuk menambah…" class="{{ $inp }}">
            <div id="hasilCari" class="hidden absolute z-20 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-72 overflow-y-auto">
                <button type="button" id="tambahSemua" class="hidden w-full text-left px-3 py-2 text-sm font-medium text-[#E85D2A] bg-orange-50 border-b hover:bg-orange-100"></button>
                <div id="isiHasil"></div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]" id="tabelItem">
                <thead class="bg-gray-50 text-xs text-gray-600">
                    <tr>
                        <th class="p-2 text-left">Produk</th>
                        <th class="p-2 text-right">Beli sekarang</th>
                        <th class="p-2 text-left w-40">Beli baru</th>
                        <th class="p-2 text-right">Jual sekarang</th>
                        <th class="p-2 text-left w-40">Jual baru</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody id="barisItem"></tbody>
            </table>
            <p id="kosong" class="text-center text-gray-400 text-sm py-6">Belum ada produk dipilih.</p>
        </div>
    </div>

    {{-- 2. Surat --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold mb-3">2. Surat permohonan &amp; berkas pendukung</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nomor surat</label>
                <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal surat <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat') }}" required class="{{ $inp }}">
            </div>
            <div class="md:col-span-3 grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Surat permohonan (PDF/JPG/PNG, maks 5 MB per file) <span class="text-red-500">*</span></label>
                    <input type="file" id="fSurat" name="surat[]" multiple accept=".pdf,.jpg,.jpeg,.png" class="{{ $inp }}">
                    <ul id="lSurat" class="mt-2 space-y-1 text-xs"></ul>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Berkas pendukung, mis. daftar produk yang berubah (PDF/JPG/PNG/XLS/XLSX/CSV)</label>
                    <input type="file" id="fPendukung" name="pendukung[]" multiple accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.csv" class="{{ $inp }}">
                    <ul id="lPendukung" class="mt-2 space-y-1 text-xs"></ul>
                </div>
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Alasan perubahan harga <span class="text-red-500">*</span></label>
                <textarea name="alasan" rows="3" required class="{{ $inp }}" placeholder="mis. Penyesuaian harga percetakan 2026 sesuai surat Mitra Bimba Abadi">{{ old('alasan') }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button class="bg-[#E85D2A] text-white px-6 py-2.5 rounded-xl font-medium hover:opacity-90">Kirim Pengajuan</button>
        <a href="{{ route('pengajuan-harga.index') }}" class="border border-gray-300 px-6 py-2.5 rounded-xl">Batal</a>
    </div>
</form>

<script>
(function () {
    const urlCari = @json(route('pengajuan-harga.cari-produk'));
    const input   = document.getElementById('cariProduk');
    const hasil   = document.getElementById('hasilCari');
    const isiHasil = document.getElementById('isiHasil');
    const btnSemua = document.getElementById('tambahSemua');
    let hasilTerakhir = [];
    const tbody   = document.getElementById('barisItem');
    const kosong  = document.getElementById('kosong');
    const dipilih = new Set();
    let idx = 0, timer = null;

    const rp  = v => (v === null || v === undefined || v === '') ? '-' : 'Rp ' + Number(v).toLocaleString('id-ID');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function hitungJual(p, beli) {
        const mult = String(p.jenis || '').toLowerCase().includes('modul') ? 1.49 : 1.20;
        return Math.ceil((beli * mult * Math.max(1, p.isi)) / 50) * 50;
    }

    function tambah(p) {
        if (dipilih.has(p.id)) return;
        dipilih.add(p.id);
        const i = idx++;
        const tr = document.createElement('tr');
        tr.className = 'border-t';
        tr.innerHTML = `
            <td class="p-2">
                <input type="hidden" name="items[${i}][product_id]" value="${p.id}">
                <div class="font-medium">${esc(p.name)}</div>
                <div class="text-xs text-gray-400">${esc(p.label || '')}</div>
            </td>
            <td class="p-2 text-right">${rp(p.harga_beli)}</td>
            <td class="p-2"><input type="number" step="any" min="0" name="items[${i}][harga_beli_baru]" class="beli w-full border border-gray-300 rounded-lg px-2 py-1.5"></td>
            <td class="p-2 text-right">${rp(p.harga_jual)}</td>
            <td class="p-2">
                <input type="number" step="any" min="0" name="items[${i}][harga_jual_baru]" class="jual w-full border border-gray-300 rounded-lg px-2 py-1.5">
                <button type="button" class="auto text-xs text-[#E85D2A] mt-1">Hitung dari harga beli</button>
            </td>
            <td class="p-2 text-right"><button type="button" class="hapus text-red-600 text-xs">Hapus</button></td>`;
        tr.querySelector('.auto').onclick = () => {
            const b = parseFloat(tr.querySelector('.beli').value);
            if (isNaN(b)) { alert('Isi harga beli baru dulu.'); return; }
            tr.querySelector('.jual').value = hitungJual(p, b);
        };
        tr.querySelector('.hapus').onclick = () => { dipilih.delete(p.id); tr.remove(); kosong.classList.toggle('hidden', dipilih.size > 0); };
        tbody.appendChild(tr);
        kosong.classList.add('hidden');
    }

    function cari() {
        fetch(urlCari + '?q=' + encodeURIComponent(input.value), {headers: {'Accept': 'application/json'}})
            .then(r => r.json())
            .then(list => {
                isiHasil.innerHTML = '';
                hasilTerakhir = list.filter(p => !p.pending && !dipilih.has(p.id));
                btnSemua.classList.toggle('hidden', hasilTerakhir.length < 2);
                btnSemua.textContent = '+ Tambah semua hasil (' + hasilTerakhir.length + ' produk)';
                if (!list.length) { isiHasil.innerHTML = '<div class="p-3 text-sm text-gray-500">Produk tidak ditemukan.</div>'; }
                list.forEach(p => {
                    const d = document.createElement('div');
                    d.className = 'px-3 py-2 text-sm border-b last:border-0 ' + (p.pending ? 'opacity-50' : 'cursor-pointer hover:bg-gray-50');
                    d.innerHTML = `<div class="font-medium">${esc(p.name)} <span class="text-xs text-gray-400">${esc(p.label || '')}</span></div>
                                   <div class="text-xs text-gray-500">Beli ${rp(p.harga_beli)} · Jual ${rp(p.harga_jual)}${p.pending ? ' · <b class="text-amber-700">masih ada pengajuan menunggu</b>' : ''}</div>`;
                    if (!p.pending) d.onclick = () => { tambah(p); hasil.classList.add('hidden'); input.value = ''; };
                    isiHasil.appendChild(d);
                });
                hasil.classList.remove('hidden');
            });
    }

    btnSemua.onclick = () => { hasilTerakhir.forEach(tambah); hasil.classList.add('hidden'); input.value = ''; };

    input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(cari, 250); });
    input.addEventListener('focus', cari);
    document.addEventListener('click', e => { if (!hasil.contains(e.target) && e.target !== input) hasil.classList.add('hidden'); });
})();

// Upload banyak file: pilihan berikutnya ditambahkan, bukan menimpa; tiap file bisa dihapus dari daftar
function kelolaFile(inputId, listId) {
    const el = document.getElementById(inputId), ul = document.getElementById(listId);
    const dt = new DataTransfer();
    const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    function gambar() {
        el.files = dt.files;
        ul.innerHTML = '';
        Array.from(dt.files).forEach((f, i) => {
            const li = document.createElement('li');
            li.className = 'flex items-center justify-between gap-2 bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5';
            li.innerHTML = '<span class="truncate" title="' + esc(f.name) + '">📎 ' + esc(f.name) + ' <span class="text-gray-400">(' + (f.size / 1024).toFixed(0) + ' KB)</span></span>'
                         + '<button type="button" class="text-red-600 shrink-0">Hapus</button>';
            li.querySelector('button').onclick = () => {
                const baru = new DataTransfer();
                Array.from(dt.files).forEach((x, j) => { if (j !== i) baru.items.add(x); });
                dt.items.clear(); Array.from(baru.files).forEach(x => dt.items.add(x));
                gambar();
            };
            ul.appendChild(li);
        });
    }
    el.addEventListener('change', () => {
        Array.from(el.files).forEach(f => {
            if (f.size > 5 * 1024 * 1024) { alert(f.name + ' lebih dari 5 MB, dilewati.'); return; }
            const ada = Array.from(dt.files).some(x => x.name === f.name && x.size === f.size);
            if (!ada && dt.files.length < 10) dt.items.add(f);
        });
        gambar();
    });
}
kelolaFile('fSurat', 'lSurat');
kelolaFile('fPendukung', 'lPendukung');

document.getElementById('fSurat').form.addEventListener('submit', e => {
    if (!document.getElementById('fSurat').files.length) { e.preventDefault(); alert('Upload minimal satu surat permohonan.'); }
});
</script>
@endsection
