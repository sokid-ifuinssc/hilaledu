@extends('layouts.app')
@section('title', 'Input Pelanggaran Baru')
@section('subtitle', 'Catat pelanggaran siswa')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('bk.pelanggaran.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div x-data="siswaHistori()">
                    <label class="form-label">Filter Kelas</label>
                    <select x-model="kelasId" class="form-select mb-3" id="kelasFilter">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>

                    <label class="form-label">Siswa *</label>
                    <select name="siswa_id" x-model="siswaId" @change="loadHistori()" class="form-select" required id="siswaSelect">
                        <option value="">Pilih Siswa</option>
                        @foreach($siswas as $s)
                        <option value="{{ $s->id }}" data-kelas="{{ $s->kelas_id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nis }} - {{ $s->nama_lengkap }} ({{ $s->kelas?->nama ?? '-' }})
                        </option>
                        @endforeach
                    </select>
                    @error('siswa_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

                    {{-- Histori Pelanggaran Siswa (muncul setelah pilih siswa) --}}
                    <div x-show="histori.length > 0" x-transition class="mt-3 p-4 bg-amber-50 rounded-lg border border-amber-200">
                        <h4 class="text-sm font-semibold text-amber-800 mb-2">⚠️ Histori Pelanggaran Siswa Ini</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b border-amber-200">
                                        <th class="text-left py-1 px-2 text-amber-700">Tanggal</th>
                                        <th class="text-left py-1 px-2 text-amber-700">Pelanggaran</th>
                                        <th class="text-left py-1 px-2 text-amber-700">Poin</th>
                                        <th class="text-left py-1 px-2 text-amber-700">Tindakan</th>
                                        <th class="text-left py-1 px-2 text-amber-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="h in histori" :key="h.tanggal + h.pelanggaran">
                                        <tr class="border-b border-amber-100">
                                            <td class="py-1 px-2" x-text="h.tanggal"></td>
                                            <td class="py-1 px-2" x-text="h.pelanggaran"></td>
                                            <td class="py-1 px-2 text-red-600 font-semibold" x-text="'-' + h.poin"></td>
                                            <td class="py-1 px-2" x-text="h.jenis_tindakan"></td>
                                            <td class="py-1 px-2" x-text="h.status"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <p x-show="suggestedTindakan" class="mt-2 text-xs text-amber-800">
                            💡 <strong>Rekomendasi eskalasi:</strong> <span x-text="suggestedLabel" class="font-semibold"></span>
                        </p>
                    </div>
                </div>

                <div>
                    <label class="form-label">Jenis Pelanggaran *</label>
                    <select name="jenis_pelanggaran_id" class="form-select" required>
                        <option value="">Pilih Jenis Pelanggaran</option>
                        @foreach($jenisPelanggarans as $jp)
                        <option value="{{ $jp->id }}" {{ old('jenis_pelanggaran_id') == $jp->id ? 'selected' : '' }}>
                            [{{ $jp->kode }}] {{ $jp->nama }} ({{ $jp->kategori->nama }} - {{ $jp->poin }} poin)
                        </option>
                        @endforeach
                    </select>
                    @error('jenis_pelanggaran_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Tanggal *</label><input type="date" name="tanggal_pelanggaran" value="{{ old('tanggal_pelanggaran', date('Y-m-d')) }}" class="form-input" required>@error('tanggal_pelanggaran')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label">Waktu</label><input type="time" name="waktu_pelanggaran" value="{{ old('waktu_pelanggaran') }}" class="form-input"></div>
                </div>

                <div><label class="form-label">Deskripsi / Kronologi *</label><textarea name="deskripsi" rows="4" class="form-textarea" required placeholder="Jelaskan kronologi pelanggaran...">{{ old('deskripsi') }}</textarea>@error('deskripsi')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>

                <div><label class="form-label">Bukti Foto (opsional)</label><input type="file" name="bukti" class="form-input" accept="image/*">@error('bukti')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>

                {{-- JENIS TINDAKAN --}}
                <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                    <label class="form-label text-indigo-800 font-semibold text-base">Jenis Tindakan *</label>
                    <p class="text-xs text-indigo-600 mb-3">Pilih jenis tindakan yang akan diterapkan untuk pelanggaran ini</p>
                    <div class="space-y-2">
                        @foreach($jenisTindakanOptions as $value => $label)
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-indigo-100 cursor-pointer hover:border-indigo-300 transition-colors">
                            <input type="radio" name="jenis_tindakan" value="{{ $value }}" class="text-indigo-600 focus:ring-indigo-500"
                                {{ old('jenis_tindakan', $suggestedTindakan ?? '') == $value ? 'checked' : '' }} required>
                            <div>
                                <span class="text-sm font-medium text-gray-800">{{ $label }}</span>
                                @if($value === 'peringatan_lisan')
                                    <span class="text-xs text-gray-500 block">Langsung selesai</span>
                                @elseif($value === 'teguran_lisan')
                                    <span class="text-xs text-gray-500 block">Walikelas mengisi laporan teguran</span>
                                @elseif($value === 'home_visit')
                                    <span class="text-xs text-gray-500 block">Walikelas mengisi laporan home visit</span>
                                @elseif($value === 'pemanggilan_ortu')
                                    <span class="text-xs text-gray-500 block">BK cetak surat → Walikelas isi laporan</span>
                                @elseif($value === 'sp1')
                                    <span class="text-xs text-gray-500 block">Walikelas & Kaprog approve → BK cetak surat → Semua isi laporan</span>
                                @elseif($value === 'sp2')
                                    <span class="text-xs text-gray-500 block">Walikelas, Kaprog & Waka approve → BK cetak surat → Semua isi laporan</span>
                                @elseif($value === 'sp3')
                                    <span class="text-xs text-gray-500 block">Semua approve (termasuk Kepsek) → BK cetak surat → Semua isi kesimpulan & laporan</span>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('jenis_tindakan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary">Simpan Pelanggaran</button>
                <a href="{{ route('bk.pelanggaran.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function siswaHistori() {
    return {
        kelasId: '{{ old('kelas_filter', '') }}',
        siswaId: '{{ old('siswa_id', '') }}',
        histori: @json($historiSiswa ?? []),
        suggestedTindakan: '{{ $suggestedTindakan ?? '' }}',
        tindakanLabels: @json(\App\Models\ProgresPelanggaran::jenisTindakanOptions()),

        get suggestedLabel() {
            return this.tindakanLabels[this.suggestedTindakan] || '';
        },

        init() {
            this.$watch('kelasId', (val) => {
                const options = document.querySelectorAll('#siswaSelect option[data-kelas]');
                options.forEach(opt => {
                    opt.style.display = (!val || opt.dataset.kelas === val) ? '' : 'none';
                });
            });
        },

        async loadHistori() {
            if (!this.siswaId) {
                this.histori = [];
                this.suggestedTindakan = '';
                return;
            }
            try {
                const response = await fetch(`{{ route('bk.pelanggaran.histori-siswa') }}?siswa_id=${this.siswaId}`);
                const data = await response.json();
                this.histori = data.histori || [];
                this.suggestedTindakan = data.suggested || '';

                // Auto-select suggested tindakan jika ada
                if (this.suggestedTindakan) {
                    const radio = document.querySelector(`input[name="jenis_tindakan"][value="${this.suggestedTindakan}"]`);
                    if (radio) radio.checked = true;
                }
            } catch (e) {
                console.error('Error loading histori:', e);
            }
        }
    };
}
</script>
@endpush
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #fef2f2; color: #dc2626;">✏️</span> Input Pelanggaran Baru</h3>
    <p>Form untuk mencatat pelanggaran siswa baru. Pastikan data diisi dengan benar sebelum disimpan.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Langkah-langkah</h3>
    <ol>
        <li>Pilih <strong>siswa</strong> yang melakukan pelanggaran — histori akan muncul otomatis</li>
        <li>Pilih <strong>jenis pelanggaran</strong> yang sesuai — poin otomatis terisi</li>
        <li>Isi <strong>tanggal pelanggaran</strong> (default hari ini)</li>
        <li>Pilih <strong>jenis tindakan</strong> yang sesuai — sistem akan menyarankan eskalasi berdasarkan histori</li>
        <li>Klik <strong>"Simpan"</strong> untuk menyimpan data pelanggaran</li>
    </ol>
</div>
<div class="help-warning">
    <span>⚠️</span>
    <p><strong>Perhatian:</strong> Setelah disimpan, poin siswa akan otomatis berkurang dan progres tindakan akan dibuat sesuai pilihan jenis tindakan.</p>
</div>
@endsection
