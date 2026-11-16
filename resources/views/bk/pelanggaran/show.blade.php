@extends('layouts.app')
@section('title', 'Detail Pelanggaran')
@section('page-title', 'Detail Pelanggaran & Penetapan Progres')
@section('subtitle', $pelanggaran->siswa->nama_lengkap)

@section('content')
<div class="space-y-6 max-w-4xl">
    {{-- Pelanggaran Info --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Informasi Pelanggaran</h3>
            <span class="badge text-sm {{ $pelanggaran->status_badge }}">{{ $pelanggaran->status_label }}</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><p class="text-xs text-gray-500">Siswa</p><p class="text-sm font-medium">{{ $pelanggaran->siswa->nama_lengkap }}</p></div>
            <div><p class="text-xs text-gray-500">NIS</p><p class="text-sm font-medium">{{ $pelanggaran->siswa->nis }}</p></div>
            <div><p class="text-xs text-gray-500">Kelas</p><p class="text-sm font-medium">{{ $pelanggaran->siswa?->kelas?->nama ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-500">Jurusan</p><p class="text-sm font-medium">{{ $pelanggaran->siswa?->kelas?->jurusan->kode ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-500">Tanggal</p><p class="text-sm font-medium">{{ $pelanggaran->tanggal_pelanggaran->format('d M Y') }}</p></div>
            <div><p class="text-xs text-gray-500">Jenis</p><p class="text-sm font-medium">{{ $pelanggaran->jenisPelanggaran->nama }}</p></div>
            <div><p class="text-xs text-gray-500">Poin</p><p class="text-sm font-bold text-red-600">-{{ $pelanggaran->poin }}</p></div>
            <div><p class="text-xs text-gray-500">Poin Siswa Saat Ini</p><p class="text-sm font-bold {{ $pelanggaran->siswa->poin >= 70 ? 'text-green-600' : ($pelanggaran->siswa->poin >= 40 ? 'text-yellow-600' : 'text-red-600') }}">{{ $pelanggaran->siswa->poin }}/100</p></div>
            <div><p class="text-xs text-gray-500">Dicatat Oleh</p><p class="text-sm font-medium">{{ $pelanggaran->pencatat->nama_lengkap ?? '-' }}</p></div>
            <div class="col-span-2"><p class="text-xs text-gray-500">Deskripsi</p><p class="text-sm">{{ $pelanggaran->deskripsi }}</p></div>
        </div>
        @if($pelanggaran->bukti)
        <div class="mt-4">
            <p class="text-xs text-gray-500 mb-1">Bukti</p>
            <img src="{{ asset('storage/' . $pelanggaran->bukti) }}" class="max-w-xs rounded-lg border">
        </div>
        @endif
    </div>

    {{-- Progres Pelanggaran --}}
    @php $progres = $pelanggaran->progresPelanggaran; @endphp
    <div id="mulai-progres" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Progres Penanganan</h3>
        @if($progres)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div><p class="text-xs text-gray-500">Jenis Tindakan</p><span class="badge text-sm {{ $progres->jenis_tindakan_badge }}">{{ $progres->jenis_tindakan_label }}</span></div>
                <div><p class="text-xs text-gray-500">Status Progres</p><span class="badge text-sm {{ $progres->status_badge }}">{{ $progres->status_label }}</span></div>
                <div><p class="text-xs text-gray-500">Tanggal Dibuat</p><p class="text-sm font-medium">{{ $progres->created_at->format('d M Y H:i') }}</p></div>
                <div><p class="text-xs text-gray-500">Dibuat Oleh</p><p class="text-sm font-medium">{{ $progres->createdBy->nama_lengkap ?: ($progres->createdBy->name ?? '-') }}</p></div>
            </div>
            <a href="{{ route('bk.progres.show', $pelanggaran) }}" class="btn-primary text-sm">📋 Buka Timeline Progres →</a>
        @else
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 mb-4">
                <p class="text-sm font-semibold text-amber-900">⚠️ Pelanggaran ini belum memiliki tindak lanjut progres.</p>
                <p class="text-xs text-amber-700 mt-0.5">Pilih jenis tindakan di bawah untuk memulai alur progres penanganan dan mengirim notifikasi ke pihak terkait.</p>
            </div>
            <form method="POST" action="{{ route('bk.pelanggaran.mulai-progres', $pelanggaran) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach(\App\Models\ProgresPelanggaran::jenisTindakanOptions() as $value => $label)
                    <label class="flex items-start gap-2.5 p-3 bg-slate-50 hover:bg-indigo-50/50 rounded-xl border border-slate-200 cursor-pointer transition">
                        <input type="radio" name="jenis_tindakan" value="{{ $value }}" class="mt-0.5 text-indigo-600 focus:ring-indigo-500" required>
                        <div class="text-xs">
                            <span class="font-bold text-slate-800 block">{{ $label }}</span>
                            <span class="text-slate-500">
                                @if($value === 'peringatan_lisan') Langsung selesai (juga cocok untuk menutup kasus lama yang sudah tuntas dibina)
                                @elseif($value === 'teguran_lisan') Wali Kelas mengisi laporan teguran
                                @elseif($value === 'home_visit') Laporan Home Visit Wali Kelas
                                @elseif($value === 'pemanggilan_ortu') BK cetak surat → Laporan Wali Kelas
                                @elseif($value === 'sp1') Approve Wali Kelas & Kaprog → Cetak Surat → Laporan
                                @elseif($value === 'sp2') Approve Wali Kelas, Kaprog & Waka Kesiswaan → Surat → Laporan
                                @elseif($value === 'sp3') Approve Semua + Kepsek → Surat → Kesimpulan & Laporan
                                @endif
                            </span>
                        </div>
                    </label>
                    @endforeach
                </div>
                <button type="submit" class="btn-primary text-sm">🚀 Mulai Progres Tindakan</button>
            </form>
        @endif
    </div>

    {{-- Histori Pelanggaran Siswa (Lintas Tahun Ajaran) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">📋 Histori Pelanggaran Siswa (Semua Tahun)</h3>
            <span class="text-sm text-gray-500">Total: {{ $historiPelanggaran->count() }} pelanggaran, {{ $historiPelanggaran->sum('poin') }} poin dikurangi</span>
        </div>
        @if($historiPelanggaran->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 px-2 text-xs text-gray-500">Tanggal</th>
                        <th class="text-left py-2 px-2 text-xs text-gray-500">Pelanggaran</th>
                        <th class="text-left py-2 px-2 text-xs text-gray-500">Poin</th>
                        <th class="text-left py-2 px-2 text-xs text-gray-500">Tindakan</th>
                        <th class="text-left py-2 px-2 text-xs text-gray-500">Tahun Ajaran</th>
                        <th class="text-left py-2 px-2 text-xs text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($historiPelanggaran as $hp)
                    <tr class="border-b border-gray-50 {{ $hp->id == $pelanggaran->id ? 'bg-indigo-50' : '' }}">
                        <td class="py-2 px-2">{{ $hp->tanggal_pelanggaran->format('d/m/Y') }}</td>
                        <td class="py-2 px-2">{{ $hp->jenisPelanggaran->nama }}</td>
                        <td class="py-2 px-2 text-red-600 font-semibold">-{{ $hp->poin }}</td>
                        <td class="py-2 px-2">
                            @if($hp->progresPelanggaran)
                            <span class="badge text-xs {{ $hp->progresPelanggaran->jenis_tindakan_badge }}">{{ $hp->progresPelanggaran->jenis_tindakan_label }}</span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-2 px-2 text-xs text-gray-500">{{ $hp->tahunAjaran->nama ?? '-' }}</td>
                        <td class="py-2 px-2"><span class="badge text-xs {{ $hp->status_badge }}">{{ $hp->status_label }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-4">Tidak ada histori pelanggaran</p>
        @endif
    </div>

    {{-- Action Buttons --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Aksi</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('bk.pelanggaran.index') }}" class="btn-secondary">← Kembali</a>
            @if($pelanggaran->status == 'dicatat')
            <a href="{{ route('bk.pelanggaran.edit', $pelanggaran) }}" class="btn-secondary">✏️ Edit</a>
            @endif
            @if($progres)
            <a href="{{ route('bk.progres.show', $pelanggaran) }}" class="btn-primary">📋 Detail Progres</a>
            @endif
        </div>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">🔍</span> Detail Pelanggaran</h3>
    <p>Halaman ini menampilkan informasi lengkap satu pelanggaran, termasuk data siswa, jenis pelanggaran, poin, dan progres penanganan.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Informasi yang Tersedia</h3>
    <ul>
        <li><strong>Data Siswa</strong> — Nama, NIS, kelas, dan poin saat ini</li>
        <li><strong>Detail Pelanggaran</strong> — Jenis, tanggal, poin dikurangi, keterangan</li>
        <li><strong>Progres Penanganan</strong> — Jenis tindakan dan status progres</li>
        <li><strong>Histori</strong> — Seluruh histori pelanggaran siswa lintas tahun ajaran</li>
    </ul>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Guru BK dapat memantau seluruh proses penanganan pelanggaran dari pencatatan hingga penyelesaian.</p>
</div>
@endsection
