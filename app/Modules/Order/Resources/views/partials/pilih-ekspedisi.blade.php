{{-- Pilihan RA Ekspedisi: Diambil / Dikirim (JNE, TIKI, Lion Parcel). Dipakai halaman "printed". --}}
<div id="modalEkspedisi" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-5">
        <h3 class="text-lg font-semibold mb-1">RA Ekspedisi</h3>
        <p class="text-sm text-gray-500 mb-4">Pilih jenis pengiriman yang mau dicetak.</p>

        <div class="grid grid-cols-2 gap-3 mb-3">
            <button type="button" data-jenis="ambil" class="eksJenis border-2 border-gray-200 rounded-xl py-3 text-sm font-medium">Diambil</button>
            <button type="button" data-jenis="kirim" class="eksJenis border-2 border-gray-200 rounded-xl py-3 text-sm font-medium">Dikirim</button>
        </div>

        <div id="eksKurirBox" class="hidden mb-3">
            <div class="text-xs text-gray-500 mb-2">Pilih ekspedisi:</div>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" data-kurir="jne" class="eksKurir border-2 border-gray-200 rounded-xl py-2 text-sm font-medium">JNE</button>
                <button type="button" data-kurir="tiki" class="eksKurir border-2 border-gray-200 rounded-xl py-2 text-sm font-medium">TIKI</button>
                <button type="button" data-kurir="lion" class="eksKurir border-2 border-gray-200 rounded-xl py-2 text-sm font-medium">LION PARCEL</button>
            </div>
        </div>

        <div id="eksInfo" class="text-xs text-gray-500 mb-3 min-h-[1rem]"></div>

        <div class="flex gap-3">
            <button type="button" id="eksBatal" class="flex-1 bg-gray-500 text-white py-2 rounded-xl text-sm">Batal</button>
            <button type="button" id="eksCetak" class="flex-1 bg-purple-600 text-white py-2 rounded-xl text-sm font-medium">Cetak</button>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('modalEkspedisi');
    var box = null, cb = null, jenis = null, kurir = null;

    function cocok(tr) {
        var t = (tr.dataset.pengiriman || '').toLowerCase();
        var diambil = t.indexOf('diambil') !== -1 || t.indexOf('ambil') !== -1;
        if (jenis === 'ambil') return diambil;
        if (diambil) return false;
        return t.indexOf(kurir) !== -1;
    }

    function ids() {
        if (!jenis || (jenis === 'kirim' && !kurir)) return [];
        return Array.from(box.querySelectorAll('tr[data-id]')).filter(cocok).map(function (r) { return r.dataset.id; });
    }

    function segarkan() {
        document.querySelectorAll('.eksJenis').forEach(function (b) {
            b.classList.toggle('border-purple-600', b.dataset.jenis === jenis);
            b.classList.toggle('bg-purple-50', b.dataset.jenis === jenis);
        });
        document.getElementById('eksKurirBox').classList.toggle('hidden', jenis !== 'kirim');
        document.querySelectorAll('.eksKurir').forEach(function (b) {
            b.classList.toggle('border-purple-600', b.dataset.kurir === kurir);
            b.classList.toggle('bg-purple-50', b.dataset.kurir === kurir);
        });
        var info = document.getElementById('eksInfo');
        if (!jenis || (jenis === 'kirim' && !kurir)) { info.textContent = ''; return; }
        info.textContent = ids().length + ' data akan dicetak.';
    }

    document.querySelectorAll('.eksJenis').forEach(function (b) {
        b.addEventListener('click', function () { jenis = b.dataset.jenis; kurir = null; segarkan(); });
    });
    document.querySelectorAll('.eksKurir').forEach(function (b) {
        b.addEventListener('click', function () { kurir = b.dataset.kurir; segarkan(); });
    });
    document.getElementById('eksBatal').addEventListener('click', function () { modal.classList.add('hidden'); });
    document.getElementById('eksCetak').addEventListener('click', function () {
        if (!jenis) { alert('Pilih Diambil atau Dikirim dulu.'); return; }
        if (jenis === 'kirim' && !kurir) { alert('Pilih ekspedisi: JNE, TIKI atau Lion Parcel.'); return; }
        var hasil = ids();
        if (hasil.length === 0) { alert('Tidak ada data dengan pilihan itu pada tanggal ini.'); return; }
        modal.classList.add('hidden');
        cb(hasil.join(','));
    });

    window.pilihEkspedisi = function (kontainer, callback) {
        box = kontainer; cb = callback; jenis = null; kurir = null;
        segarkan();
        modal.classList.remove('hidden');
    };
})();
</script>
