@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow max-w-md mx-auto">

    <h2 class="text-xl font-semibold mb-6 text-center">
        Reset Password
    </h2>

    <!-- ERROR -->
    @if ($errors->any())
        <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
            <ul class="text-sm">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST">
        @csrf

        <!-- PASSWORD -->
        <div class="mb-4">
            <label class="block mb-1 font-medium">Password Baru</label>
            <input type="password" name="password"
                   class="w-full border p-2 rounded-lg"
                   required>
        </div>

        <!-- CONFIRM -->
        <div class="mb-6">
            <label class="block mb-1 font-medium">Konfirmasi Password</label>
            <input type="password" name="password_confirmation"
                   class="w-full border p-2 rounded-lg"
                   required>
        </div>

        <!-- BUTTON -->
        <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">
            Simpan Password
        </button>

    </form>

</div>

@endsection