<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>biMBA Logistik Apps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-blue-100 font-poppins">

    @include('partials.top-nav')

        <div class="p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Menu Utama</h1>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="group">
                    <div class="bg-white rounded-3xl shadow hover:shadow-xl transition-all p-6 text-center">
                        <div class="bg-blue-100 w-20 h-20 mx-auto rounded-2xl flex items-center justify-center text-4xl mb-4">📊</div>
                        <h3 class="font-semibold text-lg">DATABASE</h3>
                    </div>
                </a>

                <a href="{{ route('import.index') }}" class="group">
                    <div class="bg-white rounded-3xl shadow hover:shadow-xl transition-all p-6 text-center">
                        <div class="bg-orange-100 w-20 h-20 mx-auto rounded-2xl flex items-center justify-center text-4xl mb-4">📦</div>
                        <h3 class="font-semibold text-lg">DATA IMPORT</h3>
                    </div>
                </a>

                <!-- Tambahkan menu lain sesuai kebutuhan -->
                <a href="{{ route('order.index') }}" class="group">
                    <div class="bg-white rounded-3xl shadow hover:shadow-xl transition-all p-6 text-center">
                        <div class="bg-yellow-100 w-20 h-20 mx-auto rounded-2xl flex items-center justify-center text-4xl mb-4">🧾</div>
                        <h3 class="font-semibold text-lg">ORDER</h3>
                    </div>
                </a>

                <a href="{{ route('picking.index') }}" class="group">
                    <div class="bg-white rounded-3xl shadow hover:shadow-xl transition-all p-6 text-center">
                        <div class="bg-green-100 w-20 h-20 mx-auto rounded-2xl flex items-center justify-center text-4xl mb-4">📦</div>
                        <h3 class="font-semibold text-lg">PICKING</h3>
                    </div>
                </a>

                <a href="#" class="group">
                    <div class="bg-white rounded-3xl shadow hover:shadow-xl transition-all p-6 text-center">
                        <div class="bg-purple-100 w-20 h-20 mx-auto rounded-2xl flex items-center justify-center text-4xl mb-4">🚚</div>
                        <h3 class="font-semibold text-lg">QC OUTGOING</h3>
                    </div>
                </a>

                <a href="#" class="group">
                    <div class="bg-white rounded-3xl shadow hover:shadow-xl transition-all p-6 text-center">
                        <div class="bg-pink-100 w-20 h-20 mx-auto rounded-2xl flex items-center justify-center text-4xl mb-4">📋</div>
                        <h3 class="font-semibold text-lg">PACKING</h3>
                    </div>
                </a>

                <a href="#" class="group">
                    <div class="bg-white rounded-3xl shadow hover:shadow-xl transition-all p-6 text-center">
                        <div class="bg-red-100 w-20 h-20 mx-auto rounded-2xl flex items-center justify-center text-4xl mb-4">📦</div>
                        <h3 class="font-semibold text-lg">DISTRIBUTION</h3>
                    </div>
                </a>

                <a href="#" class="group">
                    <div class="bg-white rounded-3xl shadow hover:shadow-xl transition-all p-6 text-center">
                        <div class="bg-gray-100 w-20 h-20 mx-auto rounded-2xl flex items-center justify-center text-4xl mb-4">📊</div>
                        <h3 class="font-semibold text-lg">GUDANG LOGISTIK</h3>
                    </div>
                </a>

                @php
    $allowedEmails = [
        'robiensyah22@gmail.com', 
        'oktaviandaaria@gmail.com'
    ];
    
    $user = auth()->user();
    
    $isAllowed = $user && (
        // Cek apakah user admin (sesuaikan dengan kolom yang ada di tabel users kamu)
        ($user->is_admin ?? false) || 
        ($user->role == 'admin' ?? false) || 
        in_array($user->email, $allowedEmails)
    );
@endphp

@if($isAllowed)
    <a href="https://infinite-management.id/" 
       class="group" 
       target="_blank" 
       rel="noopener noreferrer">
        <div class="bg-white rounded-3xl shadow hover:shadow-xl transition-all p-6 text-center">
            <div class="bg-teal-100 w-20 h-20 mx-auto rounded-2xl flex items-center justify-center text-4xl mb-4">📈</div>
            <h3 class="font-semibold text-lg">Infinite Management</h3>
        </div>
    </a>
@endif

                <!-- Tambahkan menu lainnya sesuai aplikasi kamu -->
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

</body>
</html>