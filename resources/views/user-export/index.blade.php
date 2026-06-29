<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User Export - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        
        .table-container {
            overflow-x: auto;
            max-height: 70vh;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 2000px; /* ditambah agar tidak terlalu sempit */
        }
        
        th, td {
            padding: 12px 8px;           /* padding lebih besar */
            font-size: 0.875rem;         /* 14px - lebih besar */
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            background-color: #f8fafc;
            font-weight: 600;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 20;
            font-size: 0.8rem;
        }
        
        tr:hover { background-color: #f1f5f9; }
        
        .truncate {
            max-width: 160px;            /* sedikit lebih lebar */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .wide { min-width: 180px; }
        .medium { min-width: 130px; }
        .narrow { min-width: 80px; }
    </style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Data User Export (biMBA Shop)</h1>
                <p class="text-gray-600">Semua kolom dari tabel user_export_bimba_shop</p>
            </div>
            <button onclick="document.getElementById('importForm').classList.toggle('hidden')"
                    class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-blue-700 flex items-center gap-2">
                📤 Import Data Baru
            </button>
        </div>

        <!-- Search + Per Page -->
        <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
            <form method="GET" class="flex gap-3">
                <input type="text" name="search" value="{{ request('search') ?? '' }}"
                       placeholder="Cari ID, Email, Nama..." 
                       class="border border-gray-300 rounded-2xl px-5 py-3 focus:outline-none focus:border-blue-500 text-base w-80">
                <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-2xl font-semibold">🔍 Cari</button>
                @if(request('search'))
                    <a href="{{ route('user.export') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-2xl font-semibold">Reset</a>
                @endif
            </form>

            <form method="GET" class="flex items-center gap-2">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <label class="text-sm text-gray-600">Tampilkan:</label>
                <select name="per_page" onchange="this.form.submit()" 
                        class="border border-gray-300 rounded-2xl px-4 py-3 text-base">
                    <option value="5"  {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
            </form>
        </div>

        <!-- TABEL -->
        <div class="bg-white rounded-3xl shadow table-container">
            <table class="text-sm">   <!-- text-sm = 14px -->
                <thead>
                    <tr class="bg-gray-100">
                        <th class="narrow">ID</th>
                        <th class="narrow">Cust ID</th>
                        <th class="medium">User Login</th>
                        <th class="wide">User Email</th>
                        <th class="medium">Display Name</th>
                        <th class="narrow">First Name</th>
                        <th class="narrow">Last Name</th>
                        <th class="narrow">Roles</th>
                        <th class="narrow text-center">Orders</th>
                        <th class="narrow text-right">Total Spent</th>
                        <th class="narrow text-right">AOV</th>
                        <th class="truncate">Billing First</th>
                        <th class="truncate">Billing Last</th>
                        <th class="truncate">Billing Phone</th>
                        <th class="wide truncate">Billing Address</th>
                        <th class="medium">City</th>
                        <th class="medium">Shipping First</th>
                        <th class="medium">Shipping Last</th>
                        <th class="wide truncate">Shipping Address</th>
                        <th class="medium">Registered</th>
                        <th class="medium">Last Update</th>
                        <th class="narrow text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="font-medium">{{ $user->ID }}</td>
                        <td>{{ $user->customer_id ?? '-' }}</td>
                        <td class="truncate">{{ $user->user_login ?? '-' }}</td>
                        <td class="text-blue-600 hover:underline">{{ $user->user_email ?? '-' }}</td>
                        <td class="truncate">{{ $user->display_name ?? '-' }}</td>
                        <td>{{ $user->first_name ?? '-' }}</td>
                        <td>{{ $user->last_name ?? '-' }}</td>
                        <td>{{ $user->roles ?? '-' }}</td>
                        <td class="text-center">{{ $user->orders ?? 0 }}</td>
                        <td class="text-right">Rp {{ number_format($user->total_spent ?? 0) }}</td>
                        <td class="text-right">Rp {{ number_format($user->aov ?? 0) }}</td>
                        <td class="truncate">{{ $user->billing_first_name ?? '-' }}</td>
                        <td class="truncate">{{ $user->billing_last_name ?? '-' }}</td>
                        <td class="truncate">{{ $user->billing_phone ?? '-' }}</td>
                        <td class="truncate">{{ $user->billing_address_1 ?? '-' }}</td>
                        <td>{{ $user->billing_city ?? '-' }}</td>
                        <td class="truncate">{{ $user->shipping_first_name ?? '-' }}</td>
                        <td class="truncate">{{ $user->shipping_last_name ?? '-' }}</td>
                        <td class="truncate">{{ $user->shipping_address_1 ?? '-' }}</td>
                        <td>{{ $user->user_registered ? $user->user_registered->format('d/m/Y') : '-' }}</td>
                        <td>{{ $user->last_update ? $user->last_update->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">
                            <button onclick="if(confirm('Yakin hapus?')) window.location.href='{{ route('user-export.destroy', $user->ID) }}'" 
                                    class="text-red-600 hover:text-red-700 text-lg">🗑</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="22" class="text-center py-16 text-gray-500">
                            Belum ada data. Silakan import file Excel.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-between items-center text-base">
            <div class="text-gray-600">
                Menampilkan <span class="font-semibold">{{ $users->firstItem() ?? 0 }}</span> 
                sampai <span class="font-semibold">{{ $users->lastItem() ?? 0 }}</span> 
                dari total <span class="font-semibold">{{ $users->total() }}</span> data
            </div>
            <div>
                {{ $users->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</body>
</html>