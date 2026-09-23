@extends('layouts.app')
@section('title', 'Data Kelas')
@section('content')
<div class="space-y-5">
    <div class="flex justify-between"><h3 class="font-semibold text-gray-800">Data Kelas</h3><a href="{{ route('admin.kelas.create') }}" class="btn-primary"><svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Tambah</a></div>
    <div class="table-container"><table><thead><tr><th>Nama</th><th>Tingkat</th><th>Jurusan</th><th>Wali Kelas</th><th>Tahun Ajaran</th><th>Aksi</th></tr></thead><tbody>
    @forelse($kelas as $k)
    <tr><td class="font-medium">{{ $k->nama }}</td><td>{{ $k->tingkat }}</td><td>{{ $k->jurusan->kode ?? '-' }}</td><td>{{ $k->waliKelas->nama_lengkap ?? '-' }}</td><td>{{ $k->tahunAjaran->nama ?? '-' }}</td><td class="flex gap-2"><a href="{{ route('admin.kelas.edit', $k) }}" class="text-amber-600 hover:underline text-sm">Edit</a><form method="POST" action="{{ route('admin.kelas.destroy', $k) }}" onsubmit="return confirm('Yakin?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-sm">Hapus</button></form></td></tr>
    @empty<tr><td colspan="6" class="text-center py-8 text-gray-500">Belum ada data</td></tr>@endforelse
    </tbody></table></div><div>{{ $kelas->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #ecfdf5; color: #059669;">📚</span> Halaman Data Kelas</h3>
    <p>Kelola data kelas. Setiap kelas terhubung ke jurusan, wali kelas, dan tahun ajaran. Siswa akan ditempatkan ke dalam kelas.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Klik <strong>"Tambah"</strong> untuk membuat kelas baru</li>
        <li>Isi <strong>nama kelas</strong> (contoh: X RPL 1), pilih <strong>tingkat</strong> (10, 11, 12)</li>
        <li>Pilih <strong>jurusan</strong> yang terkait dan <strong>wali kelas</strong> yang diamanahkan</li>
        <li>Pilih <strong>tahun ajaran</strong> untuk kelas ini</li>
    </ol>
</div>
<div class="help-tip">
    <span class="tip-icon"><svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
    <p>Pastikan wali kelas sudah memiliki akun pengguna dengan role <strong>Wali Kelas</strong> sebelum di-assign ke kelas.</p>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Wali kelas dapat melihat pelanggaran dan melakukan tindak lanjut untuk siswa di kelasnya.</p>
</div>
@endsection
