@extends('layouts.app')
@section('title', 'Detail Progres Pelanggaran')
@section('subtitle', $pelanggaran->siswa->nama_lengkap)

@section('content')
@php
    $progres = $pelanggaran->progresPelanggaran;
    $routePrefix = $roleKey === 'waka' ? 'waka' : 'kepsek';
    $roleLabel = $roleKey === 'waka' ? 'Waka Kesiswaan' : 'Kepala Sekolah';
@endphp
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
            {{-- Step 1: Input Pelanggaran --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white text-sm font-bold">✓</div>
                <div class="flex-1 pb-4 border-b border-gray-100">
                    <p class="font-medium text-gray-800">Pelanggaran Dicatat</p>
                    <p class="text-xs text-gray-500">{{ $progres->created_at->format('d M Y H:i') }} oleh {{ $progres->createdBy->nama_lengkap ?? '-' }}</p>
                </div>
            </div>

            {{-- Step 2: Approval --}}
            @if(!empty($progres->getRequiredApprovalRoles()))
            @php
                $approvalRoles = $progres->getRequiredApprovalRoles();
                $roleLabelsMap = ['walikelas' => 'Wali Kelas', 'kaprog' => 'Kaprog', 'waka' => 'Waka Kesiswaan', 'kepsek' => 'Kepala Sekolah'];
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
                                <span class="text-green-700">{{ $roleLabelsMap[$role] ?? $role }} — Disetujui</span>
                                <span class="text-xs text-gray-400">{{ $progres->$atField?->format('d/m/Y H:i') }}</span>
                            @elseif($progres->$field === 'ditolak')
                                <span class="w-5 h-5 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs">✗</span>
                                <span class="text-red-700">{{ $roleLabelsMap[$role] ?? $role }} — Ditolak</span>
                            @else
                                <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-xs">⏳</span>
                                <span class="text-gray-500">{{ $roleLabelsMap[$role] ?? $role }} — Menunggu</span>
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

                    {{-- Form Approval untuk role ini --}}
                    @php $approvalFieldCurrent = "approval_{$roleKey}"; @endphp
                    @if($progres->requiresApprovalFrom($roleKey) && $progres->$approvalFieldCurrent === 'belum')
                    <div class="mt-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                        <h4 class="text-sm font-semibold text-yellow-800 mb-3">⚡ Tindakan Anda: Persetujuan {{ $roleLabel }}</h4>
                        <form method="POST" action="{{ route($routePrefix . '.progres.approve', $pelanggaran) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="form-label text-sm">Catatan (opsional)</label>
                                    <textarea name="catatan_{{ $roleKey }}" rows="2" class="form-textarea text-sm w-full" placeholder="Tambahkan catatan persetujuan..."></textarea>
                                </div>
                                <div>
                                    <label class="form-label text-sm">Dokumen Pendukung (opsional)</label>
                                    <input type="file" name="dokumen_{{ $roleKey }}" class="form-input text-sm">
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

            {{-- Step 3: Cetak Surat --}}
            @if($progres->requiresCetakSurat())
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $progres->surat_dicetak ? 'bg-green-500' : ($progres->isApprovalComplete() ? 'bg-blue-500' : 'bg-gray-300') }} flex items-center justify-center text-white text-sm font-bold">
                    {{ $progres->surat_dicetak ? '✓' : '3' }}
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
                $roleLabelsMap2 = ['walikelas' => 'Wali Kelas', 'kaprog' => 'Kaprog', 'waka' => 'Waka Kesiswaan', 'kepsek' => 'Kepala Sekolah', 'bk' => 'BK'];
            @endphp
            <div class="flex items-start gap-4">
                @php $laporanDone = $progres->isLaporanComplete(); @endphp
                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $laporanDone ? 'bg-green-500' : 'bg-purple-500' }} flex items-center justify-center text-white text-sm font-bold">
                    {{ $laporanDone ? '✓' : '4' }}
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
                                    <span class="text-green-700">{{ $roleLabelsMap2[$role] ?? $role }} — Sudah mengisi</span>
                                @else
                                    <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-xs">⏳</span>
                                    <span class="text-gray-500">{{ $roleLabelsMap2[$role] ?? $role }} — Belum mengisi</span>
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

                    {{-- Form Laporan untuk role ini --}}
                    @php $myLaporanField = "laporan_{$roleKey}"; @endphp
                    @if($progres->requiresLaporanFrom($roleKey) && empty($progres->$myLaporanField))
                        @php
                            $myApprovalField = "approval_{$roleKey}";
                            $canShowLaporanForm = true;
                            // Harus sudah approve
                            if ($progres->requiresApprovalFrom($roleKey) && $progres->$myApprovalField !== 'disetujui') {
                                $canShowLaporanForm = false;
                            }
                            // Surat harus sudah dicetak
                            if ($progres->requiresCetakSurat() && !$progres->surat_dicetak) {
                                $canShowLaporanForm = false;
                            }
                        @endphp
                        @if($canShowLaporanForm)
                        <div class="mt-4 p-4 bg-purple-50 rounded-lg border border-purple-200">
                            <h4 class="text-sm font-semibold text-purple-800 mb-2">📝 Isi Laporan {{ $roleLabel }}</h4>
                            <p class="text-xs text-purple-600 mb-3">Tuliskan laporan hasil penyampaian {{ $progres->jenis_tindakan_label }}</p>
                            <form method="POST" action="{{ route($routePrefix . '.progres.laporan', $pelanggaran) }}" enctype="multipart/form-data">
                                @csrf
                                <textarea name="laporan_{{ $roleKey }}" rows="3" class="form-textarea text-sm w-full mb-2" placeholder="Tulis laporan..." required></textarea>
                                <input type="file" name="dokumen_laporan_{{ $roleKey }}" class="form-input text-sm mb-2">
                                <button type="submit" class="btn-primary text-sm">Simpan Laporan</button>
                            </form>
                        </div>
                        @else
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">⏳ Form laporan akan tersedia setelah langkah sebelumnya selesai</p>
                        </div>
                        @endif
                    @elseif(!empty($progres->$myLaporanField))
                    <div class="mt-3 p-3 bg-green-50 rounded-lg">
                        <p class="text-xs font-semibold text-green-800">✅ Laporan Anda sudah terisi</p>
                        <p class="text-xs text-green-700">{{ $progres->$myLaporanField }}</p>
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

            {{-- Step Final --}}
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
        <a href="{{ route($routePrefix . '.progres.index') }}" class="btn-secondary">← Kembali ke Progres</a>
    </div>
</div>
@endsection
