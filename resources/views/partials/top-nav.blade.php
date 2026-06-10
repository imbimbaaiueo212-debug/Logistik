<!-- resources/views/partials/top-nav.blade.php -->

<nav class="bg-white border-b border-gray-200 py-4 px-6 flex items-center justify-between shadow-sm">
    <div class="flex items-center gap-3">
        <h1 class="text-3xl font-bold tracking-[-0.04em] leading-none" style="font-family: 'Fredoka', sans-serif;">
            <span style="color: #000e8e;">b</span>
            <span style="color: #000e8e;">i</span>
            <span style="color: #f44040;">M</span>
            <span style="color: #000e8e;">B</span>
            <span style="color: #000e8e;">A</span>
            <span style="color: #1e40af;">-AIUEO</span>
        </h1>
    </div>

    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-700 font-medium">
            Halo, {{ Auth::user()->name ?? 'Admin' }}
        </span>
        <a href="{{ route('logout') }}" 
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="bg-red-600 hover:bg-red-700 px-5 py-2 rounded-xl text-sm font-medium text-white transition-all">
            LOGOUT
        </a>
    </div>
</nav>

<!-- Logout Form (wajib ada agar tombol logout berfungsi) -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>