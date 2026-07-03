<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Packing Jakarta Aktif</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">
            Packing Jakarta Aktif
        </h1>

        <a href="{{ route('packing.index') }}"
           class="bg-gray-600 text-white px-5 py-3 rounded-xl">
            ← Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-hidden">

        <table class="w-full">

            <thead class="bg-gray-100">

            <tr>

                <th class="p-3">No</th>
                <th>No PL</th>
                <th>Nama Unit</th>
                <th>Nama Barang</th>
                <th>Status QC</th>
                <th>PIC QC</th>
                <th>Kode QC</th>

            </tr>

            </thead>

            <tbody>

            @forelse($data as $i => $item)

                <tr class="border-b">

                    <td class="p-3">{{ $i+1 }}</td>

                    <td>{{ $item->no_pl }}</td>

                    <td>{{ $item->nama_unit }}</td>

                    <td>{{ $item->nama_barang }}</td>

                    <td>{{ $item->status_qc }}</td>

                    <td>{{ preg_replace('/^\d+\s*-\s*/','',$item->pic_qc) }}</td>

                    <td>{{ $item->kode_qc }}</td>

                </tr>

            @empty

                <tr>

                    <td colspan="7" class="text-center py-10">

                        Belum ada data.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

</body>
</html>