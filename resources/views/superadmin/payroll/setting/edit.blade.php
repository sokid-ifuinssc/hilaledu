@extends('layouts.app')

@section('title', 'Sesuaikan Gaji: ' . $user->name . ' - HilalPay')
@section('page-title', 'HilalPay: Sesuaikan Gaji Pegawai')

@section('dashboard-styles')
.section-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 18px;
    overflow: hidden;
    padding: 28px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.form-control, .form-select {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    border-radius: 10px;
}
.form-control:focus, .form-select:focus {
    background: #ffffff;
    border-color: #10b981;
    color: #0f172a;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}
.duty-item-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
}
.duty-item-row:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb & Back --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('superadmin.payroll.setting.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 10px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pegawai
        </a>
        <h5 class="text-slate-900 fw-bold mb-0 ms-2">Pengaturan Gaji: {{ $user->name }}</h5>
    </div>

    <div class="row">
        {{-- Sisi Kiri: Profil & Ringkasan Penugasan --}}
        <div class="col-lg-4 mb-4">
            <div class="section-card">
                <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 72px; height: 72px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 2rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h5 class="text-slate-900 fw-bold mb-1">{{ $user->name }}</h5>
                    <p class="text-slate-500 small mb-2">NIP: {{ $user->nip ?? '-' }} &bull; {{ $user->username }}</p>
                    @if($user->role === 'guru')
                        <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300 px-3 py-1">Guru Pendidik</span>
                    @else
                        <span class="badge bg-amber-100 text-amber-800 border border-amber-300 px-3 py-1">Tenaga Kependidikan</span>
                    @endif
                </div>

                <hr class="border-slate-200 my-3">

                @if($user->role === 'guru')
                    <div class="p-3 mb-3 bg-blue-50 rounded-3 border border-blue-200">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small fw-semibold text-blue-800"><i class="bi bi-clock-history me-1"></i> Jam Mengajar Database:</span>
                            <span class="badge bg-primary fs-6">{{ $user->total_jam_mengajar }} Jam</span>
                        </div>
                        <small class="text-blue-600 d-block mt-1">Terhubung dari alokasi kurikulum & penugasan mengajar aktif.</small>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="text-slate-700 fw-semibold small d-block mb-2">Daftar Tugas Tambahan Terdaftar:</label>
                    @php $jabatanList = $user->daftar_jabatan; @endphp
                    @if(count($jabatanList) > 0)
                        <div class="d-flex flex-column gap-1">
                            @foreach($jabatanList as $tugas)
                                @php
                                    $nom = $setting->getNominalTugas($tugas);
                                @endphp
                                <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-light border border-slate-200">
                                    <span class="small text-slate-800"><i class="bi bi-check-circle-fill text-success me-1"></i> {{ $tugas }}</span>
                                    @if($nom > 0)
                                        <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300">Rp {{ number_format($nom, 0, ',', '.') }}</span>
                                    @else
                                        <span class="badge bg-slate-100 text-slate-500 border border-slate-200">Non-rutin (0)</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-slate-400 small">Tidak ada tugas tambahan terdaftar.</span>
                    @endif
                </div>

                <div class="p-3 mt-3 bg-emerald-50 rounded-3 border border-emerald-200">
                    <small class="text-emerald-800 fw-medium d-block">Estimasi Take Home Pay Saat Ini:</small>
                    <div class="fw-bold text-emerald-700 fs-4 mt-1" id="display-thp">
                        Rp {{ number_format($setting->estimasiGajiBersihAttribute(), 0, ',', '.') }}
                    </div>
                    <small class="text-emerald-600 mt-1 d-block" style="font-size: 0.75rem;">
                        * Estimasi bulanan berdasarkan jam penugasan, tunjangan tugas, dan asumsi 20 hari transport.
                    </small>
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Form Penyesuaian Gaji --}}
        <div class="col-lg-8">
            <div class="section-card">
                <form action="{{ route('superadmin.payroll.setting.update', $user) }}" method="POST" id="payrollForm">
                    @csrf
                    @method('PUT')

                    <h6 class="text-slate-900 fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-cash-stack text-success me-2 fs-5"></i>Komponen Penerimaan Pokok & Mengajar
                    </h6>

                    @if($user->role === 'guru')
                        <input type="hidden" name="gaji_pokok" id="input_gaji_pokok" value="0">

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label text-slate-700 fw-medium small">Honor per Jam (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-600">Rp</span>
                                    <input type="number" name="honor_per_jam" id="input_honor_per_jam" class="form-control calc-trigger" value="{{ old('honor_per_jam', (int)$setting->honor_per_jam) }}" required min="0">
                                </div>
                                <small class="text-slate-500">Standar honor per jam tatap muka</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-slate-700 fw-medium small">Jumlah Jam Mengajar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="jam_mengajar_default" id="input_jam_mengajar" class="form-control calc-trigger" value="{{ old('jam_mengajar_default', $setting->jam_mengajar_default) }}" required min="0">
                                    <span class="input-group-text bg-light text-slate-600">Jam</span>
                                </div>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.72rem;">
                                        <i class="bi bi-link-45deg me-1"></i>Database: {{ $user->total_jam_mengajar }} Jam
                                    </span>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.72rem;" onclick="document.getElementById('input_jam_mengajar').value = {{ $user->total_jam_mengajar }}; calculateTotals();">
                                        Reset ke DB
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-slate-700 fw-medium small">Uang Transport per Hari Hadir (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-600">Rp</span>
                                    <input type="number" name="transport_per_hari" id="input_transport_per_hari" class="form-control calc-trigger" value="{{ old('transport_per_hari', (int)($setting->transport_per_hari ?? 20000)) }}" required min="0">
                                </div>
                                <small class="text-slate-500">Dihitung otomatis dari tanggal unik kehadiran & laporan KBM</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-slate-700 fw-medium small">Tunjangan Lainnya / Insentif (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-600">Rp</span>
                                    <input type="number" name="tunjangan_lain" id="input_tunjangan_lain" class="form-control calc-trigger" value="{{ old('tunjangan_lain', (int)$setting->tunjangan_lain) }}" min="0">
                                </div>
                                <small class="text-slate-500">Tunjangan khusus opsional</small>
                            </div>
                        </div>
                    @else
                        {{-- Tenaga Kependidikan (Tendik) --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-slate-700 fw-medium small">Gaji Pokok (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-600">Rp</span>
                                    <input type="number" name="gaji_pokok" id="input_gaji_pokok" class="form-control calc-trigger" value="{{ old('gaji_pokok', (int)$setting->gaji_pokok) }}" required min="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-slate-700 fw-medium small">Tunjangan Kehadiran / Transport (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-600">Rp</span>
                                    <input type="number" name="tunjangan_kehadiran" id="input_tunjangan_kehadiran" class="form-control calc-trigger" value="{{ old('tunjangan_kehadiran', (int)$setting->tunjangan_kehadiran) }}" min="0">
                                </div>
                            </div>

                            <input type="hidden" name="honor_per_jam" id="input_honor_per_jam" value="0">
                            <input type="hidden" name="jam_mengajar_default" id="input_jam_mengajar" value="0">
                            <input type="hidden" name="transport_per_hari" id="input_transport_per_hari" value="0">

                            <div class="col-md-6">
                                <label class="form-label text-slate-700 fw-medium small">Tunjangan Lainnya (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-600">Rp</span>
                                    <input type="number" name="tunjangan_lain" id="input_tunjangan_lain" class="form-control calc-trigger" value="{{ old('tunjangan_lain', (int)$setting->tunjangan_lain) }}" min="0">
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Bagian Tunjangan Tugas Tambahan Diisi Per-Tugas Tambahan --}}
                    <div class="pt-3 border-top border-slate-200">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="text-slate-900 fw-bold mb-0 d-flex align-items-center">
                                <i class="bi bi-award text-primary me-2 fs-5"></i>Tunjangan Tugas Tambahan & Jabatan
                            </h6>
                            <span class="small text-slate-500">Nominal diisi per-tugas</span>
                        </div>
                        <p class="text-slate-500 small mb-3">
                            Tentukan nominal tunjangan bulanan untuk masing-masing tugas tambahan (misal: Wakasek, Kaprog, Wali Kelas).
                            Untuk penugasan yang bukan penghasilan rutin seperti <strong>Panitia UTS, Panitia Kegiatan, atau Ad-hoc</strong>, silakan <strong>diisi 0</strong> agar tidak dimasukkan ke dalam slip gaji rutin.
                        </p>

                        <div id="tugasTambahanContainer">
                            @if(count($daftarTugas) > 0)
                                @foreach($daftarTugas as $tugas)
                                    @php
                                        $currentNom = old('tugas_tambahan_nominal.' . $tugas, (int)$setting->getNominalTugas($tugas));
                                    @endphp
                                    <div class="duty-item-row">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-2">
                                                        <i class="bi bi-briefcase"></i>
                                                    </span>
                                                    <div>
                                                        <div class="fw-semibold text-slate-800 small">{{ $tugas }}</div>
                                                        <small class="text-slate-400" style="font-size: 0.72rem;">Penugasan resmi terdaftar</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white">Rp</span>
                                                    <input type="number" 
                                                           name="tugas_tambahan_nominal[{{ $tugas }}]" 
                                                           class="form-control input-tugas-nominal calc-trigger" 
                                                           value="{{ $currentNom }}" 
                                                           placeholder="0 (isi 0 jika non-rutin)" 
                                                           min="0">
                                                </div>
                                                <small class="text-slate-400 d-block mt-1" style="font-size: 0.70rem;">Isi 0 jika bukan tunjangan bulanan rutin</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-light border border-slate-200 text-slate-500 small mb-3">
                                    <i class="bi bi-info-circle me-1"></i> Tidak ada tugas tambahan bawaan terdaftar pada profil pegawai ini. Anda dapat menambahkan tugas tambahan kustom di bawah ini jika diperlukan.
                                </div>
                            @endif

                            {{-- Wadah untuk Tugas Kustom Tambahan --}}
                            <div id="customTugasContainer"></div>

                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addCustomTugasRow()" style="border-radius: 8px;">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Penugasan Lainnya
                            </button>
                        </div>

                        {{-- Total Tunjangan Tugas Tambahan --}}
                        <div class="d-flex align-items-center justify-content-between p-3 mt-3 bg-light rounded-3 border border-slate-200">
                            <span class="fw-semibold text-slate-700 small">Total Tunjangan Tugas Tambahan Rutin:</span>
                            <span class="fw-bold text-primary fs-5" id="display-total-tugas">
                                Rp {{ number_format($setting->tunjangan_jabatan, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- Komponen Potongan --}}
                    <div class="pt-4 mt-4 border-top border-slate-200">
                        <h6 class="text-slate-900 fw-bold mb-3 d-flex align-items-center">
                            <i class="bi bi-dash-circle text-danger me-2 fs-5"></i>Komponen Potongan Rutin
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label text-slate-700 fw-medium small">Potongan BPJS (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-600">Rp</span>
                                    <input type="number" name="potongan_bpjs" id="input_potongan_bpjs" class="form-control calc-trigger" value="{{ old('potongan_bpjs', (int)$setting->potongan_bpjs) }}" min="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-slate-700 fw-medium small">Simpanan Koperasi (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-600">Rp</span>
                                    <input type="number" name="potongan_koperasi" id="input_potongan_koperasi" class="form-control calc-trigger" value="{{ old('potongan_koperasi', (int)$setting->potongan_koperasi) }}" min="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-slate-700 fw-medium small">Infaq / Kas Yayasan (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-600">Rp</span>
                                    <input type="number" name="potongan_lain" id="input_potongan_lain" class="form-control calc-trigger" value="{{ old('potongan_lain', (int)$setting->potongan_lain) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Rekening Bank --}}
                    <div class="pt-4 border-top border-slate-200">
                        <h6 class="text-slate-900 fw-bold mb-3 d-flex align-items-center">
                            <i class="bi bi-bank text-secondary me-2 fs-5"></i>Informasi Rekening Bank Pegawai
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label text-slate-700 fw-medium small">Nama Bank</label>
                                <input type="text" name="rekening_bank" class="form-control" value="{{ old('rekening_bank', $setting->rekening_bank ?? 'BSI (Bank Syariah Indonesia)') }}" placeholder="Contoh: BSI, BRI, BCA">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-slate-700 fw-medium small">Nomor Rekening</label>
                                <input type="text" name="nomor_rekening" class="form-control" value="{{ old('nomor_rekening', $setting->nomor_rekening) }}" placeholder="Nomor rekening">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-slate-700 fw-medium small">Atas Nama Rekening</label>
                                <input type="text" name="atas_nama_rekening" class="form-control" value="{{ old('atas_nama_rekening', $setting->atas_nama_rekening ?? $user->name) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label text-slate-700 fw-medium small">Catatan Khusus</label>
                                <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan opsional untuk penggajian">{{ old('catatan', $setting->catatan) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top border-slate-200">
                        <a href="{{ route('superadmin.payroll.setting.index') }}" class="btn btn-secondary px-4" style="border-radius: 10px;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success fw-semibold px-4 shadow-sm" style="border-radius: 10px; background: #10b981; border: none;">
                            <i class="bi bi-check-lg me-1"></i> Simpan Pengaturan Gaji
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function formatRupiah(num) {
    return 'Rp ' + Math.max(0, Math.round(num)).toLocaleString('id-ID');
}

function calculateTotals() {
    const isGuru = "{{ $user->role }}" === 'guru';

    // 1. Hitung total tugas tambahan
    let totalTugas = 0;
    document.querySelectorAll('.input-tugas-nominal').forEach(input => {
        const val = parseFloat(input.value) || 0;
        totalTugas += Math.max(0, val);
    });

    const displayTugas = document.getElementById('display-total-tugas');
    if (displayTugas) {
        displayTugas.textContent = formatRupiah(totalTugas);
    }

    // 2. Hitung penerimaan
    let totalPenerimaan = 0;
    if (isGuru) {
        const honorPerJam = parseFloat(document.getElementById('input_honor_per_jam')?.value) || 0;
        const jamMengajar = parseFloat(document.getElementById('input_jam_mengajar')?.value) || 0;
        const transportPerHari = parseFloat(document.getElementById('input_transport_per_hari')?.value) || 0;
        const tunjanganLain = parseFloat(document.getElementById('input_tunjangan_lain')?.value) || 0;

        // Honor jam + transport (estimasi 20 hari) + tunjangan tugas + tunjangan lain
        totalPenerimaan = (honorPerJam * jamMengajar) + (transportPerHari * 20) + totalTugas + tunjanganLain;
    } else {
        const gajiPokok = parseFloat(document.getElementById('input_gaji_pokok')?.value) || 0;
        const tunjanganKehadiran = parseFloat(document.getElementById('input_tunjangan_kehadiran')?.value) || 0;
        const tunjanganLain = parseFloat(document.getElementById('input_tunjangan_lain')?.value) || 0;

        totalPenerimaan = gajiPokok + tunjanganKehadiran + totalTugas + tunjanganLain;
    }

    // 3. Hitung potongan
    const bpjs = parseFloat(document.getElementById('input_potongan_bpjs')?.value) || 0;
    const koperasi = parseFloat(document.getElementById('input_potongan_koperasi')?.value) || 0;
    const infaq = parseFloat(document.getElementById('input_potongan_lain')?.value) || 0;
    const totalPotongan = bpjs + koperasi + infaq;

    // 4. Estimasi Take Home Pay
    const thp = Math.max(0, totalPenerimaan - totalPotongan);
    const displayThp = document.getElementById('display-thp');
    if (displayThp) {
        displayThp.textContent = formatRupiah(thp);
    }
}

function addCustomTugasRow() {
    const container = document.getElementById('customTugasContainer');
    const idx = container.children.length;

    const row = document.createElement('div');
    row.className = 'duty-item-row';
    row.innerHTML = `
        <div class="row align-items-center g-2">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-tag"></i></span>
                    <input type="text" name="custom_tugas_nama[]" class="form-control form-control-sm" placeholder="Nama tugas tambahan / kepanitiaan..." required>
                </div>
            </div>
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white">Rp</span>
                    <input type="number" name="custom_tugas_nominal[]" class="form-control form-control-sm input-tugas-nominal calc-trigger" placeholder="0" min="0" value="0">
                </div>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.duty-item-row').remove(); calculateTotals();">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(row);

    row.querySelector('.calc-trigger').addEventListener('input', calculateTotals);
    calculateTotals();
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.calc-trigger').forEach(el => {
        el.addEventListener('input', calculateTotals);
    });
    calculateTotals();
});
</script>
@endsection
