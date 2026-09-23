@extends('layouts.app')
@section('title', 'Pengaturan Sekolah')
@section('subtitle', 'Kop surat & identitas sekolah')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.pengaturan-sekolah.update') }}">
            @csrf @method('PUT')
            <div class="space-y-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 pb-2 border-b">Identitas Sekolah</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="form-label">Nama Sekolah</label><input type="text" name="nama_sekolah" value="{{ $settings->firstWhere('key','nama_sekolah')?->value ?? 'SMK Plus Al Hilal' }}" class="form-input"></div>
                        <div><label class="form-label">NPSN</label><input type="text" name="npsn" value="{{ $settings->firstWhere('key','npsn')?->value }}" class="form-input" placeholder="20276xxx"></div>
                        <div class="md:col-span-2"><label class="form-label">Alamat Sekolah</label><textarea name="alamat_sekolah" rows="2" class="form-textarea">{{ $settings->firstWhere('key','alamat_sekolah')?->value ?? 'Jl. Cagak Rt 05/02, Ds. Pamulihan, Kec. Pamulihan, Kab. Sumedang' }}</textarea></div>
                        <div><label class="form-label">Telepon</label><input type="text" name="telepon_sekolah" value="{{ $settings->firstWhere('key','telepon_sekolah')?->value }}" class="form-input"></div>
                        <div><label class="form-label">Email Sekolah</label><input type="email" name="email_sekolah" value="{{ $settings->firstWhere('key','email_sekolah')?->value }}" class="form-input"></div>
                        <div><label class="form-label">Website</label><input type="text" name="website_sekolah" value="{{ $settings->firstWhere('key','website_sekolah')?->value }}" class="form-input"></div>
                        <div><label class="form-label">Nama Yayasan</label><input type="text" name="nama_yayasan" value="{{ $settings->firstWhere('key','nama_yayasan')?->value ?? 'Yayasan Pondok Pesantren Al Hilal' }}" class="form-input"></div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 pb-2 border-b">Kepala Sekolah (Tanda Tangan Surat)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="form-label">Nama Kepala Sekolah</label><input type="text" name="nama_kepala_sekolah" value="{{ $settings->firstWhere('key','nama_kepala_sekolah')?->value ?? 'Mukhammad Mansyur, S.Pt.' }}" class="form-input"></div>
                        <div><label class="form-label">NIP Kepala Sekolah</label><input type="text" name="nip_kepala_sekolah" value="{{ $settings->firstWhere('key','nip_kepala_sekolah')?->value }}" class="form-input"></div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 pb-2 border-b">Kop Surat (Baris Kustom)</h3>
                    <p class="text-xs text-gray-400 mb-3">Opsional. Jika diisi, akan digunakan sebagai kop surat pada cetakan laporan dan surat.</p>
                    <div class="space-y-3">
                        <div><label class="form-label">Baris 1 (Yayasan)</label><input type="text" name="kop_baris1" value="{{ $settings->firstWhere('key','kop_baris1')?->value ?? 'YAYASAN PONDOK PESANTREN AL HILAL' }}" class="form-input"></div>
                        <div><label class="form-label">Baris 2 (Nama Sekolah)</label><input type="text" name="kop_baris2" value="{{ $settings->firstWhere('key','kop_baris2')?->value ?? 'SMK PLUS AL HILAL' }}" class="form-input"></div>
                        <div><label class="form-label">Baris 3 (Alamat)</label><input type="text" name="kop_baris3" value="{{ $settings->firstWhere('key','kop_baris3')?->value ?? 'Jl. Cagak Rt 05/02, Ds. Pamulihan, Kec. Pamulihan, Kab. Sumedang' }}" class="form-input"></div>
                        <div><label class="form-label">Baris 4 (Kontak)</label><input type="text" name="kop_baris4" value="{{ $settings->firstWhere('key','kop_baris4')?->value }}" class="form-input" placeholder="Telp: (022) xxx | Email: info@smkalhilal.sch.id"></div>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">⚙️</span> Pengaturan Sekolah</h3>
    <p>Kelola identitas sekolah dan kop surat yang digunakan pada cetakan laporan, surat teguran, dan surat peringatan.</p>
</div>
<div class="help-result">
    <h4>✅ Data Yang Disimpan</h4>
    <p>Data ini akan otomatis muncul pada <strong>kop surat</strong> cetakan laporan dan surat-surat resmi yang dihasilkan sistem.</p>
</div>
@endsection
