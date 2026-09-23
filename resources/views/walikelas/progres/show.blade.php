@extends('layouts.app')
@section('title', 'Detail Progres Pelanggaran')
@section('subtitle', $pelanggaran->siswa->nama_lengkap)

@section('content')
@php $progres = $pelanggaran->progresPelanggaran; @endphp
<div class="space-y-6 max-w-4xl">
    {{-- Info Pelanggaran --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Informasi Pelanggaran</h3>
            <div class="flex gap-2">
                <span class="badge text-sm {{ $pelanggaran->status_badge }}">{{ $pelanggaran->status_label }}</span>
                @if($progres)
                <span class="badge text-sm {{ $progres->jenis_tindakan_badge }}">{{ $progres->jenis_tindakan_label }}</span>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><p class="text-xs text-gray-500">Siswa</p><p class="text-sm font-medium">{{ $pelanggaran->siswa->nama_lengkap }}</p></div>
            <div><p class="text-xs text-gray-500">NIS</p><p class="text-sm font-medium">{{ $pelanggaran->siswa->nis }}</p></div>
            <div><p class="text-xs text-gray-500">Kelas</p><p class="text-sm font-medium">{{ $pelanggaran->siswa?->kelas?->nama ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-500">Jurusan</p><p class="text-sm font-medium">{{ $pelanggaran->siswa?->kelas?->jurusan->kode ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-500">Tanggal</p><p class="text-sm font-medium">{{ $pelanggaran->tanggal_pelanggaran->format('d M Y') }}</p></div>
            <div><p class="text-xs text-gray-500">Jenis</p><p class="text-sm font-medium">{{ $pelanggaran->jenisPelanggaran->nama }}</p></div>
            <div><p class="text-xs text-gray-500">Poin</p><p class="text-sm font-bold text-red-600">-{{ $pelanggaran->poin }}</p></div>
            <div><p class="text-xs text-gray-500">Dicatat Oleh</p><p class="text-sm font-medium">{{ $pelanggaran->pencatat->nama_lengkap ?? '-' }}</p></div>
            <div class="col-span-2 md:col-span-4"><p class="text-xs text-gray-500">Deskripsi</p><p class="text-sm">{{ $pelanggaran->deskripsi }}</p></div>
        </div>

        {{-- Kontak Ortu Siswa --}}
        <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4 bg-emerald-50/60 p-4 rounded-xl border border-emerald-100">
            <div>
                <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">👨‍👩‍👧‍👦 Orang Tua / Wali Siswa</p>
                <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $pelanggaran->siswa->nama_ortu_display }}</p>
                <p class="text-xs text-gray-600 mt-0.5">Alamat: {{ $pelanggaran->siswa->alamat_display }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">📞 Hubungi Ortu / Wali</p>
                @if($pelanggaran->siswa->no_hp_ortu_display !== '-')
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', str_starts_with($pelanggaran->siswa->no_hp_ortu_display, '0') ? '62' . substr($pelanggaran->siswa->no_hp_ortu_display, 1) : $pelanggaran->siswa->no_hp_ortu_display) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-emerald-700 hover:text-emerald-900 bg-white px-3 py-1.5 rounded-lg border border-emerald-200 shadow-sm hover:shadow transition-all gap-1.5 mt-0.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    WhatsApp {{ $pelanggaran->siswa->no_hp_ortu_display }}
                </a>
                @else
                <p class="text-xs text-gray-400 mt-0.5">-</p>
                @endif
            </div>
        </div>

        @if($pelanggaran->bukti)
        <div class="mt-4">
            <p class="text-xs text-gray-500 mb-1">Bukti</p>
            <img src="{{ asset('storage/' . $pelanggaran->bukti) }}" class="max-w-xs rounded-lg border">
        </div>
        @endif
    </div>

    @if($progres)
    {{-- Timeline Progres --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">📋 Timeline Progres</h3>

        <div class="space-y-4">
            {{-- Step 1: Input Pelanggaran (selalu selesai) --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white text-sm font-bold">✓</div>
                <div class="flex-1 pb-4 border-b border-gray-100">
                    <p class="font-medium text-gray-800">Pelanggaran Dicatat</p>
                    <p class="text-xs text-gray-500">{{ $progres->created_at->format('d M Y H:i') }} oleh {{ $progres->createdBy->nama_lengkap ?? '-' }}</p>
                </div>
            </div>

            {{-- Step 2: Approval (jika diperlukan) --}}
            @if(!empty($progres->getRequiredApprovalRoles()))
            @php
                $approvalRoles = $progres->getRequiredApprovalRoles();
                $roleLabels = ['walikelas' => 'Wali Kelas', 'kaprog' => 'Kaprog', 'waka' => 'Waka Kesiswaan', 'kepsek' => 'Kepala Sekolah'];
            @endphp
            <div class="flex items-start gap-4">
                @php $approvalDone = $progres->isApprovalComplete(); @endphp
                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $approvalDone ? 'bg-green-500' : 'bg-yellow-500' }} flex items-center justify-center text-white text-sm font-bold">
                    {{ $approvalDone ? '✓' : '2' }}
                </div>
                <div class="flex-1 pb-4 border-b border-gray-100">
                    <p class="font-medium text-gray-800">Persetujuan</p>
                    <div class="mt-2 space-y-2">
                        @foreach($approvalRoles as $role)
                        @php
                            $field = "approval_{$role}";
                            $atField = "approval_{$role}_at";
                            $catatanField = "catatan_{$role}";
                            $dokumenField = "dokumen_{$role}";
                        @endphp
                        <div class="flex items-center gap-2 text-sm">
                            @if($progres->$field === 'disetujui')
                                <span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs">✓</span>
                                <span class="text-green-700">{{ $roleLabels[$role] ?? $role }} — Disetujui</span>
                                <span class="text-xs text-gray-400">{{ $progres->$atField?->format('d/m/Y H:i') }}</span>
                            @elseif($progres->$field === 'ditolak')
                                <span class="w-5 h-5 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs">✗</span>
                                <span class="text-red-700">{{ $roleLabels[$role] ?? $role }} — Ditolak</span>
                            @else
                                <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-xs">⏳</span>
                                <span class="text-gray-500">{{ $roleLabels[$role] ?? $role }} — Menunggu</span>
                            @endif
                        </div>
                        @if($progres->$catatanField)
                            <p class="text-xs text-gray-500 ml-7 italic">Catatan: {{ $progres->$catatanField }}</p>
                        @endif
                        @if($progres->$dokumenField)
                            <a href="{{ asset('storage/' . $progres->$dokumenField) }}" target="_blank" class="text-xs text-indigo-600 ml-7">📎 Lihat Dokumen</a>
                        @endif
                        @endforeach
                    </div>

                    {{-- Form Approval Walikelas --}}
                    @if($progres->requiresApprovalFrom('walikelas') && $progres->approval_walikelas === 'belum')
                    <div class="mt-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                        <h4 class="text-sm font-semibold text-yellow-800 mb-3">⚡ Tindakan Anda: Persetujuan</h4>
                        <form method="POST" action="{{ route('walikelas.progres.approve', $pelanggaran) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="form-label text-sm">Catatan (opsional)</label>
                                    <textarea name="catatan_walikelas" rows="2" class="form-textarea text-sm w-full" placeholder="Tambahkan catatan persetujuan..."></textarea>
                                </div>
                                <div>
                                    <label class="form-label text-sm">Dokumen Pendukung (opsional)</label>
                                    <input type="file" name="dokumen_walikelas" class="form-input text-sm">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" name="aksi" value="disetujui" class="btn-primary text-sm bg-green-600 hover:bg-green-700">✅ Setujui</button>
                                    <button type="submit" name="aksi" value="ditolak" class="btn-primary text-sm bg-red-600 hover:bg-red-700" onclick="return confirm('Yakin ingin menolak?')">❌ Tolak</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Step 3: Cetak Surat (jika diperlukan) --}}
            @if($progres->requiresCetakSurat())
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $progres->surat_dicetak ? 'bg-green-500' : ($progres->isApprovalComplete() ? 'bg-blue-500' : 'bg-gray-300') }} flex items-center justify-center text-white text-sm font-bold">
                    {{ $progres->surat_dicetak ? '✓' : (empty($progres->getRequiredApprovalRoles()) ? '2' : '3') }}
                </div>
                <div class="flex-1 pb-4 border-b border-gray-100">
                    <p class="font-medium text-gray-800">Cetak Surat</p>
                    @if($progres->surat_dicetak)
                        <p class="text-xs text-green-600">Surat sudah dicetak pada {{ $progres->surat_dicetak_at?->format('d M Y H:i') }}</p>
                    @elseif($progres->isApprovalComplete())
                        <p class="text-xs text-blue-600">Menunggu BK mencetak surat</p>
                    @else
                        <p class="text-xs text-gray-400">Menunggu persetujuan selesai</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Step 4: Laporan --}}
            @if(!empty($progres->getRequiredLaporanRoles()))
            @php
                $laporanRoles = $progres->getRequiredLaporanRoles();
                $roleLabels = ['walikelas' => 'Wali Kelas', 'kaprog' => 'Kaprog', 'waka' => 'Waka Kesiswaan', 'kepsek' => 'Kepala Sekolah', 'bk' => 'BK'];
            @endphp
            <div class="flex items-start gap-4">
                @php $laporanDone = $progres->isLaporanComplete(); @endphp
                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $laporanDone ? 'bg-green-500' : 'bg-purple-500' }} flex items-center justify-center text-white text-sm font-bold">
                    {{ $laporanDone ? '✓' : ($progres->requiresCetakSurat() ? '4' : (empty($progres->getRequiredApprovalRoles()) ? '2' : '3')) }}
                </div>
                <div class="flex-1 pb-4 border-b border-gray-100">
                    <p class="font-medium text-gray-800">Laporan</p>
                    <div class="mt-2 space-y-2">
                        @foreach($laporanRoles as $role)
                        @php
                            $laporanField = "laporan_{$role}";
                            $dokLaporanField = "dokumen_laporan_{$role}";
                        @endphp
                        <div class="text-sm">
                            <div class="flex items-center gap-2">
                                @if(!empty($progres->$laporanField))
                                    <span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs">✓</span>
                                    <span class="text-green-700">{{ $roleLabels[$role] ?? $role }} — Sudah mengisi</span>
                                @else
                                    <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-xs">⏳</span>
                                    <span class="text-gray-500">{{ $roleLabels[$role] ?? $role }} — Belum mengisi</span>
                                @endif
                            </div>
                            @if(!empty($progres->$laporanField))
                                <p class="text-xs text-gray-600 ml-7 mt-1">{{ $progres->$laporanField }}</p>
                            @endif
                            @if(!empty($progres->$dokLaporanField))
                                <a href="{{ asset('storage/' . $progres->$dokLaporanField) }}" target="_blank" class="text-xs text-indigo-600 ml-7">📎 Lihat Dokumen</a>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    {{-- Form Laporan Walikelas --}}
                    @if($progres->requiresLaporanFrom('walikelas') && empty($progres->laporan_walikelas))
                        @php
                            // Tentukan apakah form laporan walikelas bisa ditampilkan
                            $canShowLaporanForm = true;
                            // Jika butuh approval, harus sudah disetujui dulu
                            if ($progres->requiresApprovalFrom('walikelas') && $progres->approval_walikelas !== 'disetujui') {
                                $canShowLaporanForm = false;
                            }
                            // Jika butuh cetak surat, surat harus sudah dicetak
                            if ($progres->requiresCetakSurat() && !$progres->surat_dicetak) {
                                $canShowLaporanForm = false;
                            }
                        @endphp
                        @if($canShowLaporanForm)
                        <div class="mt-4 p-4 bg-purple-50 rounded-lg border border-purple-200">
                            <h4 class="text-sm font-semibold text-purple-800 mb-2">📝 Isi Laporan Wali Kelas</h4>
                            @if($progres->jenis_tindakan === 'teguran_lisan')
                                <p class="text-xs text-purple-600 mb-3">Tuliskan hasil teguran yang telah disampaikan kepada siswa</p>
                            @elseif($progres->jenis_tindakan === 'home_visit')
                                <p class="text-xs text-purple-600 mb-3">Tuliskan laporan hasil kunjungan rumah (home visit)</p>
                            @elseif($progres->jenis_tindakan === 'pemanggilan_ortu')
                                <p class="text-xs text-purple-600 mb-3">Tuliskan laporan hasil pemanggilan orang tua</p>
                            @else
                                <p class="text-xs text-purple-600 mb-3">Tuliskan laporan hasil penyampaian {{ $progres->jenis_tindakan_label }}</p>
                            @endif
                            <form method="POST" action="{{ route('walikelas.progres.laporan', $pelanggaran) }}" enctype="multipart/form-data">
                                @csrf
                                <textarea name="laporan_walikelas" rows="3" class="form-textarea text-sm w-full mb-2" placeholder="Tulis laporan..." required></textarea>
                                <input type="file" name="dokumen_laporan_walikelas" class="form-input text-sm mb-2">
                                <button type="submit" class="btn-primary text-sm">Simpan Laporan</button>
                            </form>
                        </div>
                        @else
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">⏳ Form laporan akan tersedia setelah langkah sebelumnya selesai</p>
                        </div>
                        @endif
                    @elseif(!empty($progres->laporan_walikelas))
                    <div class="mt-3 p-3 bg-green-50 rounded-lg">
                        <p class="text-xs font-semibold text-green-800">✅ Laporan Anda sudah terisi</p>
                        <p class="text-xs text-green-700">{{ $progres->laporan_walikelas }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Step Kesimpulan (SP3) --}}
            @if($progres->jenis_tindakan === 'sp3')
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $progres->kesimpulan ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white text-sm font-bold">5</div>
                <div class="flex-1 pb-4 border-b border-gray-100">
                    <p class="font-medium text-gray-800">Kesimpulan</p>
                    @if($progres->kesimpulan)
                        <p class="text-sm text-gray-700 mt-1">{{ $progres->kesimpulan }}</p>
                    @else
                        <p class="text-xs text-gray-400">Belum ada kesimpulan</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Step Final: Status --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $progres->status === 'selesai' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white text-sm font-bold">
                    {{ $progres->status === 'selesai' ? '✓' : '→' }}
                </div>
                <div class="flex-1">
                    <p class="font-medium {{ $progres->status === 'selesai' ? 'text-green-700' : 'text-gray-500' }}">
                        {{ $progres->status === 'selesai' ? '✅ Selesai' : 'Menunggu Penyelesaian oleh BK' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Back Button --}}
    <div class="flex gap-3">
        <a href="{{ route('walikelas.progres.index') }}" class="btn-secondary">← Kembali ke Progres</a>
        <a href="{{ route('walikelas.pelanggaran.show', $pelanggaran) }}" class="btn-secondary">📋 Detail Pelanggaran</a>
    </div>
</div>
@endsection
