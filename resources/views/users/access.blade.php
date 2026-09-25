@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-2xl shadow p-8 mt-8">
    <h1 class="text-xl font-bold mb-6">Atur Hak Akses: {{ $user->name }}</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('users.access.update', $user->id) }}">
        @csrf
        @method('PUT')

        <label class="block text-sm font-medium mb-2">Role</label>
        <select name="role" id="roleSelect" class="w-full border rounded-xl px-4 py-2 mb-4" required>
            @foreach(['admin'=>'Admin','customer'=>'Customer','pic'=>'PIC','pic_khusus'=>'PIC Khusus'] as $val=>$label)
                <option value="{{ $val }}" {{ $user->role==$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>

        <label class="flex items-center gap-2 mb-4">
            <input type="checkbox" name="is_approved" value="1" {{ $user->is_approved ? 'checked' : '' }}>
            Akun disetujui / aktif
        </label>

        <div id="moduleWrap">
            <label class="block text-sm font-medium mb-2">Akses Modul</label>
            @foreach($modules as $m)
                <label class="flex items-center gap-2 mb-1">
                    <input type="{{ in_array($user->role, ['customer','pic']) ? 'radio' : 'checkbox' }}"
                           name="modules[]" value="{{ $m->id }}"
                           {{ $user->modules->contains($m->id) ? 'checked' : '' }}>
                    {{ $m->name }}
                </label>
            @endforeach
        </div>

        <button type="submit" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl">
            Simpan
        </button>
    </form>
</div>

<script>
document.getElementById('roleSelect').addEventListener('change', function () {
    const single = ['customer', 'pic'].includes(this.value);
    document.querySelectorAll('#moduleWrap input[name="modules[]"]').forEach(el => {
        el.type = single ? 'radio' : 'checkbox';
        if (single) el.checked = false; // reset supaya tidak nyangkut multi-check lama
    });
});
</script>
@endsection