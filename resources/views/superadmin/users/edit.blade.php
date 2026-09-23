@extends('layouts.app')

@section('title', 'Edit Pengguna - ' . $user->name)
@section('page-title', 'Edit Pengguna')

@section('dashboard-styles')
.form-page-header {
    display: flex; align-items: center; gap: 16px; margin-bottom: 24px;
}
.back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 10px;
    border: 1px solid var(--border-color); background: transparent;
    color: var(--text-muted); font-size: 0.82rem; font-weight: 500;
    text-decoration: none; transition: all 0.2s ease;
    font-family: 'Poppins', sans-serif; cursor: pointer;
}
.back-btn:hover { background: var(--bg-card); color: var(--text-light); border-color: rgba(255,255,255,0.12); }

.form-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 20px; padding: 32px;
    max-width: 720px;
}
.form-section-title {
    font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 1px; color: var(--text-muted);
    padding-bottom: 12px; border-bottom: 1px solid var(--border-color);
    margin-bottom: 20px; margin-top: 28px;
}
.form-section-title:first-child { margin-top: 0; }

.field-label { font-size: 0.82rem; font-weight: 500; color: var(--text-muted); margin-bottom: 7px; display: block; }
.field-required { color: var(--accent-gold); }

.form-input {
    width: 100%; background: rgba(255,255,255,0.05);
    border: 1px solid var(--border-color); color: var(--text-light);
    border-radius: 12px; padding: 11px 16px;
    font-family: 'Poppins', sans-serif; font-size: 0.88rem; transition: all 0.25s ease;
}
.form-input:focus {
    outline: none; border-color: var(--primary-light);
    background: rgba(255,255,255,0.07); box-shadow: 0 0 0 3px rgba(45,138,78,0.15);
}
.form-input::placeholder { color: var(--text-muted); }
.form-input.is-invalid { border-color: rgba(231,76,60,0.6); box-shadow: 0 0 0 3px rgba(231,76,60,0.1); }
.field-error { font-size: 0.78rem; color: #f1948a; margin-top: 6px; }
.field-hint  { font-size: 0.75rem; color: var(--text-muted); margin-top: 5px; }

select.form-input option { background: #1a2e24; color: var(--text-light); }

.toggle-switch {
    display: flex; align-items: center; gap: 12px; cursor: pointer;
    padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border-color);
    transition: all 0.2s ease;
}
.toggle-switch:hover { background: var(--bg-card-hover); }
.toggle-switch input[type=checkbox] { display: none; }
.toggle-track {
    width: 44px; height: 24px; border-radius: 12px;
    background: rgba(255,255,255,0.1); position: relative; flex-shrink: 0; transition: background 0.3s ease;
}
.toggle-track::after {
    content: ''; position: absolute; top: 3px; left: 3px;
    width: 18px; height: 18px; border-radius: 50%; background: white; transition: transform 0.3s ease;
}
.toggle-switch input:checked + .toggle-track { background: var(--primary-light); }
.toggle-switch input:checked + .toggle-track::after { transform: translateX(20px); }
.toggle-label { font-size: 0.88rem; font-weight: 500; }

.btn-save {
    display: inline-flex; align-items: center; gap: 8px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border: none; color: white; padding: 12px 28px;
    border-radius: 12px; font-size: 0.9rem; font-weight: 600;
    cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.3s ease;
}
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(26,86,50,0.4); }

.user-profile-badge {
    display: flex; align-items: center; gap: 16px;
    padding: 16px 20px; border-radius: 14px;
    background: rgba(255,255,255,0.03); border: 1px solid var(--border-color);
    margin-bottom: 24px;
}
.profile-avatar {
    width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; font-weight: 700; color: white;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
}

.password-wrapper { position: relative; }
.password-toggle {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1rem;
}
@endsection

@section('content')
<div class="form-page-header">
    <a href="{{ route('superadmin.users.index') }}" class="back-btn">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <div>
        <h4 style="margin:0;font-size:1.1rem;font-weight:700;">Edit Pengguna</h4>
        <p style="margin:0;font-size:0.78rem;color:var(--text-muted);">Perbarui data pengguna di sistem HilalEdu</p>
    </div>
</div>

<div class="user-profile-badge">
    <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
    <div>
        <div style="font-size:1rem;font-weight:700;">{{ $user->name }}</div>
        <div style="font-size:0.8rem;color:var(--text-muted);">ID #{{ $user->id }} · Terdaftar {{ $user->created_at->diffForHumans() }}</div>
    </div>
</div>

<form method="POST" action="{{ route('superadmin.users.update', $user) }}">
    @csrf @method('PUT')
    <div class="form-card">

        {{-- Info Dasar --}}
        <div class="form-section-title">Informasi Dasar</div>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="field-label">Nama Lengkap <span class="field-required">*</span></label>
                <input type="text" name="name" class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       value="{{ old('name', $user->name) }}">
                @error('name')<div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="field-label">Username <span class="field-required">*</span></label>
                <input type="text" name="username" class="form-input {{ $errors->has('username') ? 'is-invalid' : '' }}"
                       value="{{ old('username', $user->username) }}">
                @error('username')<div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="field-label">Email <span class="field-required">*</span></label>
                <input type="email" name="email" class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       value="{{ old('email', $user->email) }}">
                @error('email')<div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Ubah Password --}}
        <div class="form-section-title">Ubah Password</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="field-label">Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password"
                           class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Kosongkan jika tidak diubah">
                    <button type="button" class="password-toggle" onclick="togglePass('password','icon1')">
                        <i class="bi bi-eye-fill" id="icon1"></i>
                    </button>
                </div>
                @error('password')<div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                <div class="field-hint"><i class="bi bi-info-circle me-1"></i>Kosongkan jika tidak ingin mengubah password</div>
            </div>
            <div class="col-md-6">
                <label class="field-label">Konfirmasi Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="password2"
                           class="form-input" placeholder="Ulangi password baru">
                    <button type="button" class="password-toggle" onclick="togglePass('password2','icon2')">
                        <i class="bi bi-eye-fill" id="icon2"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Role & Status --}}
        <div class="form-section-title">Role & Status</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="field-label">Role <span class="field-required">*</span></label>
                <select name="role" class="form-input {{ $errors->has('role') ? 'is-invalid' : '' }}"
                        {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>
                            {{ ucfirst($role) }}
                        </option>
                    @endforeach
                </select>
                @if($user->id === auth()->id())
                    <input type="hidden" name="role" value="{{ $user->role }}">
                    <div class="field-hint"><i class="bi bi-lock-fill me-1"></i>Anda tidak dapat mengubah role sendiri</div>
                @endif
                @error('role')<div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="field-label">Status Akun</label>
                <label class="toggle-switch">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                           {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    <div class="toggle-track"></div>
                    <div class="toggle-label">
                        {{ $user->is_active ? 'Akun Aktif' : 'Akun Nonaktif' }}
                    </div>
                </label>
                @if($user->id === auth()->id())
                    <input type="hidden" name="is_active" value="1">
                    <div class="field-hint mt-2"><i class="bi bi-lock-fill me-1"></i>Anda tidak dapat menonaktifkan akun sendiri</div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-3 mt-4 pt-4" style="border-top: 1px solid var(--border-color);">
            <button type="submit" class="btn-save">
                <i class="bi bi-check-circle-fill"></i> Simpan Perubahan
            </button>
            <a href="{{ route('superadmin.users.index') }}" class="back-btn">Batal</a>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
function togglePass(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon  = document.getElementById(iconId);
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash-fill';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye-fill';
    }
}
</script>
@endsection
