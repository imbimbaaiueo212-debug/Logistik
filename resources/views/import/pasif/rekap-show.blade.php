<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Total - {{ $periode->edisi }}</title>
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

        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">{{ $periode->edisi }} — Rekap Total</h2>
                <p class="text-gray-500 mt-1">
                    {{ $periode->periode ?? ($periode->bulan . ' ' . $periode->tahun) }}
                    @if($periode->no_ps)
                        · No PS: <span class="font-medium text-gray-700">{{ $periode->no_ps }}</span>
                    @endif
                </p>
            </div>
            <div class="text-right space-y-1">
                <div>
                    <span class="text-sm text-gray-500">Total Unit</span>
                    <div class="text-2xl font-bold text-blue-700">{{ $periode->pesanan->count() }}</div>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Total Bacaan</span>
                    <div class="text-xl font-bold text-emerald-700">{{ number_format($totalBacaan) }}</div>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Total Qty Majalah</span>
                    <div class="text-xl font-bold text-blue-700">{{ number_format($totalMajalah) }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Cabang</th>
                        <th class="px-5 py-3">Nama Unit</th>
                        <th class="px-5 py-3 text-center">Bacaan Unit</th>
                        <th class="px-5 py-3 text-center">Qty Majalah</th>
                        <th class="px-5 py-3">Telepon</th>
                        <th class="px-5 py-3">Alamat</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($periode->pesanan as $i => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3 font-medium">{{ $item->no_cab ?? '-' }}</td>
                            <td class="px-5 py-3 font-semibold">{{ $item->nama_unit }}</td>
                            <td class="px-5 py-3 text-center font-medium text-emerald-700">
                                {{ number_format($item->bacaan_unit ?? 0) }}
                            </td>
                            <td class="px-5 py-3 text-center font-medium text-blue-700">
                                {{ number_format($item->qty) }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $item->telepon ?? '-' }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs max-w-xs truncate" title="{{ $item->alamat }}">
                                {{ $item->alamat ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            <a href="{{ route('import.pasif.rekap') }}"
               class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-600 font-medium">
                ← Kembali ke Rekap Total
            </a>
        </div>

    </div>
</div>
</body>
</html>