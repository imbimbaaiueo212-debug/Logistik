@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')

<style>
    .tuf-wrap {
        max-width: 760px;
        margin: 32px auto;
        padding: 0 16px;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Arial, sans-serif;
    }

    .tuf-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 1px 2px rgba(15,27,51,0.04), 0 12px 32px -12px rgba(15,27,51,0.12);
        overflow: hidden;
    }

    .tuf-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 28px 32px 22px;
        border-bottom: 1px solid #EEF0F4;
    }
    .tuf-header-icon {
        width: 46px; height: 46px;
        border-radius: 14px;
        background: linear-gradient(135deg, #28447F, #1D3361);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .tuf-header-icon svg { width: 24px !important; height: 24px !important; color: #fff; display: block; }
    .tuf-header h1 { margin: 0; font-size: 20px; font-weight: 700; color: #0F1B33; line-height: 1.3; }
    .tuf-header p { margin: 2px 0 0; font-size: 13px; color: #0F1B3380; }

    .tuf-body { padding: 28px 32px 32px; }

    .tuf-section-label {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #0F1B3355;
        margin: 0 0 14px;
    }
    .tuf-section-label.mt { margin-top: 28px; }

    .tuf-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-bottom: 4px;
    }
    @media (max-width: 560px) {
        .tuf-grid-2 { grid-template-columns: 1fr; }
    }

    .tuf-field label {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        color: #0F1B33CC;
        margin-bottom: 6px;
    }
    .tuf-field input {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 13px;
        font-size: 13.5px;
        border: 1.5px solid #E4E7EC;
        border-radius: 10px;
        color: #0F1B33;
        background: #fff;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .tuf-field input::placeholder { color: #0F1B3355; }
    .tuf-field input:focus {
        border-color: #28447F;
        box-shadow: 0 0 0 3.5px rgba(40,68,127,0.12);
    }
    .tuf-error {
        color: #DC2626;
        font-size: 11.5px;
        margin: 5px 0 0;
    }

    /* ===== Role cards ===== */
    .tuf-role-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    @media (max-width: 560px) {
        .tuf-role-grid { grid-template-columns: 1fr; }
    }
    .tuf-role-card input { position: absolute; opacity: 0; width: 0; height: 0; }
    .tuf-role-card label {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border: 1.5px solid #E4E7EC;
        border-radius: 14px;
        padding: 14px 15px;
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
    }
    .tuf-role-card label:hover { border-color: #C7CEDC; }
    .tuf-role-card input:checked + label {
        border-color: #28447F;
        background-color: #F1F4FA;
        box-shadow: 0 0 0 1px #28447F;
    }
    .tuf-role-dot {
        width: 17px; height: 17px;
        border-radius: 50%;
        border: 2px solid #C7CEDC;
        flex-shrink: 0;
        margin-top: 1px;
        transition: border-color .15s ease, background-color .15s ease;
        position: relative;
    }
    .tuf-role-card input:checked + label .tuf-role-dot {
        border-color: #28447F;
        background-color: #28447F;
    }
    .tuf-role-card input:checked + label .tuf-role-dot::after {
        content: '';
        position: absolute;
        top: 50%; left: 50%;
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #fff;
        transform: translate(-50%, -50%);
    }
    .tuf-role-title { font-size: 13.5px; font-weight: 700; color: #0F1B33; display: block; }
    .tuf-role-desc { font-size: 12px; color: #0F1B3388; display: block; margin-top: 2px; line-height: 1.4; }

    /* ===== Module chips ===== */
    .tuf-module-hintrow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .tuf-module-hint { font-size: 12px; color: #0F1B3366; font-style: italic; }
    .tuf-module-box {
        border: 1.5px solid #E4E7EC;
        border-radius: 14px;
        padding: 14px;
        background: #FAFBFC;
        max-height: 230px;
        overflow-y: auto;
    }
    .tuf-module-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 9px;
    }
    @media (max-width: 620px) {
        .tuf-module-grid { grid-template-columns: 1fr 1fr; }
    }
    .tuf-module-chip input { position: absolute; opacity: 0; width: 0; height: 0; }
    .tuf-module-chip label {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1.5px solid #E4E7EC;
        background: #fff;
        border-radius: 10px;
        padding: 9px 11px;
        font-size: 12.5px;
        font-weight: 500;
        color: #0F1B3399;
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease, color .15s ease;
    }
    .tuf-module-chip label:hover { border-color: #C7CEDC; }
    .tuf-module-chip input:checked + label {
        border-color: #28447F;
        background-color: #F1F4FA;
        color: #162749;
    }
    .tuf-module-check {
        width: 15px !important; height: 15px !important;
        flex-shrink: 0;
        color: #28447F;
        opacity: 0;
        transition: opacity .15s ease;
        display: block;
    }
    .tuf-module-chip input:checked + label .tuf-module-check { opacity: 1; }
    .tuf-module-chip label span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* ===== Submit ===== */
    .tuf-submit {
        width: 100%;
        margin-top: 26px;
        background: #E85D2A;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        padding: 13px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: background-color .15s ease, transform .1s ease;
    }
    .tuf-submit:hover { background: #D14E1F; }
    .tuf-submit:active { transform: scale(0.99); }

    .tuf-back {
        text-align: center;
        padding: 20px 0 26px;
    }
    .tuf-back a {
        font-size: 13px;
        color: #28447F;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .tuf-back a:hover { color: #E85D2A; }
    .tuf-back svg { width: 13px !important; height: 13px !important; display: block; }
</style>

    @include('partials.flash')

    <div class="tuf-wrap">
        <div class="tuf-card">

            {{-- ============ HEADER ============ --}}
            <div class="tuf-header">
                <div class="tuf-header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c0-3.5 2.7-6 6-6s6 2.5 6 6"/><path d="M17 8h4M19 6v4"/></svg>
                </div>
                <div>
                    <h1>Tambah Akun User</h1>
                    <p>biMBA Logistik &middot; khusus admin</p>
                </div>
            </div>

            <form method="POST" action="{{ route('register.post') }}" class="tuf-body">
                @csrf

                {{-- ============ DATA AKUN ============ --}}
                <p class="tuf-section-label">Data Akun</p>

                <div class="tuf-grid-2">
                    <div class="tuf-field">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkap" required>
                        @error('name')<p class="tuf-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="tuf-field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
                        @error('email')<p class="tuf-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="tuf-field">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Minimal 6 karakter" required>
                        @error('password')<p class="tuf-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="tuf-field">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
                    </div>
                </div>

                {{-- ============ ROLE ============ --}}
                <p class="tuf-section-label mt">Role</p>

                @php
                    $roles = [
                        'admin'      => ['Admin', 'Akses penuh ke seluruh modul'],
                        'customer'   => ['Customer', 'Hanya 1 modul yang ditentukan admin'],
                        'pic'        => ['PIC', 'Hanya 1 modul yang ditentukan admin'],
                        'pic_khusus' => ['PIC Khusus', 'Boleh diberi beberapa modul'],
                    ];
                @endphp

                <div class="tuf-role-grid">
                    @foreach($roles as $val => [$label, $desc])
                        <div class="tuf-role-card">
                            <input type="radio" name="role" id="role_{{ $val }}" value="{{ $val }}"
                                   {{ old('role')==$val ? 'checked' : '' }} required>
                            <label for="role_{{ $val }}">
                                <span class="tuf-role-dot"></span>
                                <span>
                                    <span class="tuf-role-title">{{ $label }}</span>
                                    <span class="tuf-role-desc">{{ $desc }}</span>
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('role')<p class="tuf-error">{{ $message }}</p>@enderror

                {{-- ============ AKSES MODUL ============ --}}
                <div id="moduleWrap" class="tuf-section-label mt" style="display:block;">
                    <div class="tuf-module-hintrow">
                        <span class="tuf-section-label" style="margin:0;">Akses Modul</span>
                        <span class="tuf-module-hint" id="moduleHint">Pilih role terlebih dahulu</span>
                    </div>

                    <div class="tuf-module-box">
                        <div class="tuf-module-grid">
                            @foreach($modules as $m)
                                <div class="tuf-module-chip">
                                    <input type="checkbox" name="modules[]" value="{{ $m->id }}"
                                           id="mod_{{ $m->id }}"
                                           {{ collect(old('modules'))->contains($m->id) ? 'checked' : '' }}>
                                    <label for="mod_{{ $m->id }}">
                                        <svg class="tuf-module-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>
                                        <span>{{ $m->name }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('modules')<p class="tuf-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="tuf-submit">Buat Akun</button>
            </form>

        </div>
    </div>

<script>
    const roleInputs   = document.querySelectorAll('input[name="role"]');
    const moduleWrap   = document.getElementById('moduleWrap');
    const moduleHint   = document.getElementById('moduleHint');
    const moduleInputs = () => moduleWrap.querySelectorAll('input[name="modules[]"]');

    function updateModuleField() {
        const checked = document.querySelector('input[name="role"]:checked');
        if (!checked) {
            moduleWrap.style.display = 'block';
            moduleHint.textContent = 'Pilih role terlebih dahulu';
            return;
        }

        const role = checked.value;

        if (role === 'admin') {
            moduleWrap.style.display = 'none';
            moduleInputs().forEach(el => el.checked = false);
            return;
        }

        moduleWrap.style.display = 'block';
        const single = ['customer', 'pic'].includes(role);

        moduleInputs().forEach(el => { el.type = single ? 'radio' : 'checkbox'; });

        moduleHint.textContent = single
            ? 'Pilih 1 akses modul'
            : 'Boleh pilih lebih dari 1 akses modul';
    }

    roleInputs.forEach(el => el.addEventListener('change', updateModuleField));
    updateModuleField();
</script>

@endsection