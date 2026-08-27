<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Spare Pasif 3% - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
@include('partials.top-nav')

<div class="flex h-screen">
    <div class="flex-1 p-8 overflow-auto">

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">
                {!! session('success') !!}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Spare Pasif 3%</h2>
                <p class="text-gray-500 mt-1">
                    (DLC + Unit Pasif + Bacaan Unit) × 3% → dibulatkan (half-up) → Lembar print (200/lembar)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                        Grup A
                    </span>
                </p>
            </div>

            <a href="{{ route('import.pasif.index') }}"
               class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-600 font-medium">
                ← Kembali ke Menu Pasif
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Edisi</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">DLC</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Pasif</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Bacaan</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Spare Raw</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Spare (dibulatkan)</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Lembar Print</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Grup</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No PS</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data as $row)
                            <tr class="hover:bg-amber-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-amber-700">{{ $row->edisi }}</span>
                                </td>
                                <td class="px-6 py-4 text-right text-gray-700">
                                    {{ number_format($row->dlc_total, 0, '.', ',') }}
                                </td>
                                <td class="px-6 py-4 text-right text-gray-700">
                                    {{ number_format($row->pasif_total, 0, '.', ',') }}
                                </td>
                                <td class="px-6 py-4 text-right text-gray-700">
                                    {{ number_format($row->bacaan_total, 0, '.', ',') }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900">
                                    {{ number_format($row->grand_total, 0, '.', ',') }}
                                </td>
                                <td class="px-6 py-4 text-right text-gray-500 text-sm">
                                    {{ number_format($row->spare_raw, 3, '.', ',') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold text-amber-700 text-lg">
                                        {{ number_format($row->spare, 0, '.', ',') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                                        {{ number_format($row->lembar, 0, '.', ',') }} lembar
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        {{ $row->grup }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <input type="text"
                                               id="no_ps_{{ $row->id }}"
                                               value="{{ $row->no_ps }}"
                                               placeholder="Isi No PS"
                                               class="w-32 px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none">
                                        <button type="button"
                                                onclick="saveNoPs({{ $row->id }})"
                                                class="px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                                            Simpan
                                        </button>
                                        <span id="msg_{{ $row->id }}" class="text-xs hidden"></span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-16 text-center text-gray-500">
                                    <div class="text-5xl mb-3">📦</div>
                                    Belum ada data Spare Pasif.<br>
                                    Data akan otomatis dihitung dari DLC + Pasif + Bacaan yang aktif.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 text-sm text-gray-500 space-y-1">
            <p>• Data disimpan di database <code class="bg-gray-100 px-1 rounded">spare_pasifs</code> dengan <strong>grup = A</strong>.</p>
            <p>• Pembulatan memakai <code class="bg-gray-100 px-1 rounded">round()</code> PHP (half-up).</p>
            <p>• 1 lembar print = 200 eksemplar → <code class="bg-gray-100 px-1 rounded">ceil(spare / 200)</code></p>
        </div>

    </div>
</div>

<script>
async function saveNoPs(id) {
    const input = document.getElementById('no_ps_' + id);
    const msg   = document.getElementById('msg_' + id);
    const noPs  = input.value.trim();

    msg.classList.add('hidden');
    msg.textContent = '';

    try {
        const res = await fetch(`{{ url('/import/pasif/spare') }}/${id}/no-ps`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ no_ps: noPs }),
        });

        const data = await res.json();

        if (data.success) {
            msg.textContent = '✓ Tersimpan';
            msg.className = 'text-xs text-green-600';
            msg.classList.remove('hidden');
            setTimeout(() => msg.classList.add('hidden'), 2000);
        } else {
            throw new Error(data.message || 'Gagal menyimpan');
        }
    } catch (e) {
        msg.textContent = '✗ ' + e.message;
        msg.className = 'text-xs text-red-600';
        msg.classList.remove('hidden');
    }
}
</script>
</body>
</html>