<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Jurusan;
use App\Exports\GuruExport;
use App\Imports\GuruImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'guru');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('username', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('nip', 'like', "%$s%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $gurus     = $query->orderBy('name')->paginate(15)->withQueryString();
        $totalGuru = User::where('role', 'guru')->count();
        $guruAktif = User::where('role', 'guru')->where('is_active', true)->count();

        return view('admin.guru.index', compact('gurus', 'totalGuru', 'guruAktif'));
    }

    public static function getDaftarTugasTambahan(): array
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('master_tugas_tambahan')) {
            $fromDb = \App\Models\TugasTambahan::where('is_aktif', true)->orderBy('nama')->pluck('nama')->toArray();
            if (!empty($fromDb)) {
                return $fromDb;
            }
        }
        return [
            'Bendahara Sekolah / BOS',
            'Guru Bimbingan Konseling (BK)',
            'Guru Pembimbing Prakerin / PKL',
            'Kepala Laboratorium',
            'Kepala Perpustakaan',
            'Kepala Sekolah',
            'Ketua Program Keahlian (Kaprog)',
            'Koordinator Prakerin / PKL',
            'Pembina Ekstrakulikuler',
            'Pembina OSIS',
            'Wakasek Humas & Hubungan Industri',
            'Wakasek Kesiswaan',
            'Wakasek Kurikulum & Akademik',
            'Wakasek Sarana & Prasarana',
            'Wali Kelas',
        ];
    }

    public function create()
    {
        $jurusans = Jurusan::where('is_aktif', true)->orderBy('nama')->get();
        $kelases  = \App\Models\Kelas::where('is_aktif', true)->orderBy('nama_kelas')->get();
        $daftarTugasTambahan = self::getDaftarTugasTambahan();

        return view('admin.guru.create', compact('jurusans', 'kelases', 'daftarTugasTambahan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'username'             => 'required|string|max:255|unique:users,username|alpha_dash',
            'email'                => 'required|email|max:255|unique:users,email',
            'nip'                  => ['nullable', 'string', 'max:30', Rule::unique('users')->where(fn($q) => $q->whereNotIn('nip', ['0000000000000000', '0', '-']))],
            'no_hp'                => 'nullable|string|max:20',
            'jenis_kelamin'        => 'nullable|in:L,P',
            'jabatan_utama'        => 'nullable|string|max:100',
            'tugas_tambahan'       => 'nullable|array',
            'tugas_tambahan.*'     => 'string|max:150',
            'tahun_masuk'          => 'nullable|string|max:10',
            'lulusan_tahun'        => 'nullable|string|max:10',
            // Alamat
            'alamat'               => 'nullable|string',
            'desa'                 => 'nullable|string|max:100',
            'kecamatan'            => 'nullable|string|max:100',
            'kabupaten'            => 'nullable|string|max:100',
            'provinsi'             => 'nullable|string|max:100',
            // Riwayat Pendidikan
            'pendidikan_sd'        => 'nullable|string|max:150',
            'tahun_lulus_sd'       => 'nullable|string|max:10',
            'pendidikan_smp'       => 'nullable|string|max:150',
            'tahun_lulus_smp'      => 'nullable|string|max:10',
            'pendidikan_sma'       => 'nullable|string|max:150',
            'tahun_lulus_sma'      => 'nullable|string|max:10',
            'pendidikan_s1'        => 'nullable|string|max:150',
            'tahun_lulus_s1'       => 'nullable|string|max:10',
            'pendidikan_s2'        => 'nullable|string|max:150',
            'tahun_lulus_s2'       => 'nullable|string|max:10',
            'password'             => 'required|string|min:6|confirmed',
            'is_active'            => 'boolean',
        ], [
            'username.unique'   => 'Username sudah digunakan.',
            'email.unique'      => 'Email sudah digunakan.',
            'nip.unique'        => 'NIP/NUPTK sudah terdaftar.',
            'password.min'      => 'Password minimal 6 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        $validated['role']           = 'guru';
        $validated['is_active']      = $request->has('is_active');
        $validated['nama_lengkap']   = $validated['name'];
        $validated['password_plain'] = $validated['password'];

        // Proses Multi Tugas Tambahan
        $tugas = $request->input('tugas_tambahan', []);
        if (!is_array($tugas)) {
            $tugas = !empty($tugas) ? [$tugas] : [];
        }
        $validated['tugas_tambahan'] = array_values(array_unique(array_filter(array_map('trim', $tugas))));
        $validated['jabatan_utama']  = $request->filled('jabatan_utama') ? $request->jabatan_utama : 'Guru';

        $guru = User::create($validated);

        return redirect()->route('superadmin.guru.index')
            ->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(User $guru)
    {
        abort_if($guru->role !== 'guru', 404);
        $jurusans = Jurusan::where('is_aktif', true)->orderBy('nama')->get();
        $kelases  = \App\Models\Kelas::where('is_aktif', true)->orderBy('nama_kelas')->get();
        $daftarTugasTambahan = self::getDaftarTugasTambahan();

        return view('admin.guru.edit', compact('guru', 'jurusans', 'kelases', 'daftarTugasTambahan'));
    }

    public function update(Request $request, User $guru)
    {
        abort_if($guru->role !== 'guru', 404);

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'username'             => ['required', 'alpha_dash', Rule::unique('users')->ignore($guru->id)],
            'email'                => ['required', 'email', Rule::unique('users')->ignore($guru->id)],
            'nip'                  => ['nullable', 'string', 'max:30', Rule::unique('users')->where(fn($q) => $q->whereNotIn('nip', ['0000000000000000', '0', '-']))->ignore($guru->id)],
            'no_hp'                => 'nullable|string|max:20',
            'jenis_kelamin'        => 'nullable|in:L,P',
            'jabatan_utama'        => 'nullable|string|max:100',
            'tugas_tambahan'       => 'nullable|array',
            'tugas_tambahan.*'     => 'string|max:150',
            'tahun_masuk'          => 'nullable|string|max:10',
            'lulusan_tahun'        => 'nullable|string|max:10',
            // Alamat
            'alamat'               => 'nullable|string',
            'desa'                 => 'nullable|string|max:100',
            'kecamatan'            => 'nullable|string|max:100',
            'kabupaten'            => 'nullable|string|max:100',
            'provinsi'             => 'nullable|string|max:100',
            // Riwayat Pendidikan
            'pendidikan_sd'        => 'nullable|string|max:150',
            'tahun_lulus_sd'       => 'nullable|string|max:10',
            'pendidikan_smp'       => 'nullable|string|max:150',
            'tahun_lulus_smp'      => 'nullable|string|max:10',
            'pendidikan_sma'       => 'nullable|string|max:150',
            'tahun_lulus_sma'      => 'nullable|string|max:10',
            'pendidikan_s1'        => 'nullable|string|max:150',
            'tahun_lulus_s1'       => 'nullable|string|max:10',
            'pendidikan_s2'        => 'nullable|string|max:150',
            'tahun_lulus_s2'       => 'nullable|string|max:10',
            'password'             => 'nullable|string|min:6|confirmed',
            'is_active'            => 'boolean',
        ]);

        $validated['is_active']    = $request->has('is_active');
        $validated['nama_lengkap'] = $validated['name'];

        // Proses Multi Tugas Tambahan
        $tugas = $request->input('tugas_tambahan', []);
        if (!is_array($tugas)) {
            $tugas = !empty($tugas) ? [$tugas] : [];
        }
        $validated['tugas_tambahan'] = array_values(array_unique(array_filter(array_map('trim', $tugas))));
        $validated['jabatan_utama']  = $request->filled('jabatan_utama') ? $request->jabatan_utama : 'Guru';

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password_plain'] = $validated['password'];
        }

        $guru->update($validated);

        return redirect()->route('superadmin.guru.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(User $guru)
    {
        abort_if($guru->role !== 'guru', 404);
        $guru->delete();
        return redirect()->route('superadmin.guru.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }

    public function toggleActive(User $guru)
    {
        abort_if($guru->role !== 'guru', 404);
        $guru->update(['is_active' => !$guru->is_active]);
        $status = $guru->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('superadmin.guru.index')
            ->with('success', "Guru {$guru->name} berhasil {$status}.");
    }

    // ============== EXPORT ==============

    public function export(Request $request)
    {
        $search = $request->query('search');
        return Excel::download(new GuruExport($search), 'data_guru_' . date('Ymd_His') . '.xlsx');
    }

    // ============== IMPORT ==============

    public function importForm()
    {
        return view('admin.guru.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'             => 'required|mimes:xlsx,xls,csv|max:5120',
            'duplicate_action' => 'nullable|in:update,skip,replace',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $duplicateAction = $request->input('duplicate_action', 'update');

        try {
            $import = new GuruImport($duplicateAction);
            Excel::import($import, $request->file('file'));

            $created  = $import->getCreatedCount();
            $updated  = $import->getUpdatedCount();
            $skipped  = $import->getSkippedCount();
            $replaced = $import->getReplacedCount();
            $matched  = $import->getMatchedRecords();
            $errors   = $import->getErrors();

            $summary = [
                'type'             => 'Guru',
                'created'          => $created,
                'updated'          => $updated,
                'skipped'          => $skipped,
                'replaced'         => $replaced,
                'duplicate_action' => $duplicateAction,
                'matched'          => $matched,
                'errors'           => $errors,
            ];

            $msgParts = [];
            if ($created > 0)  $msgParts[] = "{$created} guru baru ditambahkan";
            if ($updated > 0)  $msgParts[] = "{$updated} guru lama diperbarui/ditimpa";
            if ($replaced > 0) $msgParts[] = "{$replaced} guru lama diganti baru";
            if ($skipped > 0)  $msgParts[] = "{$skipped} data sama dilewati";
            if (count($errors) > 0) $msgParts[] = count($errors) . " baris ditolak karena error";

            $mainMsg = "Proses import data guru selesai! " . implode(', ', $msgParts) . ".";

            $redirect = redirect()->route('superadmin.guru.index')->with('import_summary', $summary);

            if (count($errors) > 0 && ($created + $updated + $replaced) === 0) {
                return $redirect->with('error', $mainMsg);
            } elseif (count($errors) > 0) {
                return $redirect->with('warning', $mainMsg);
            }

            return $redirect->with('success', $mainMsg);
        } catch (\Exception $e) {
            return redirect()->route('superadmin.guru.index')
                ->with('error', 'Gagal memproses file import: ' . $e->getMessage());
        }
    }

    // ============== DOWNLOAD TEMPLATE ==============

    public function downloadTemplate()
    {
        $headings = [
            'Nama Lengkap', 'Username', 'Email', 'NUPTK', 'No HP', 'Jenis Kelamin',
            'Tugas Tambahan / Jabatan',
            'Tahun Masuk', 'Lulusan Tahun', 'Alamat', 'Desa', 'Kecamatan', 'Kabupaten', 'Provinsi',
            'Pendidikan SD', 'Tahun Lulus SD', 'Pendidikan SMP', 'Tahun Lulus SMP',
            'Pendidikan SMA', 'Tahun Lulus SMA', 'Pendidikan S1', 'Tahun Lulus S1',
            'Pendidikan S2', 'Tahun Lulus S2', 'Password'
        ];

        $sample = [
            'ALI MUSTOPA, S.Pd', 'alimustopa', 'alimustopa@hilaledu.sch.id', '1543779680130060', '081234567890', 'L',
            'Wali Kelas, Guru Pembimbing Prakerin / PKL',
            '2018', '2014', 'Jl. Pendidikan No. 10', 'Sukamaju', 'Cibadak', 'Sukabumi', 'Jawa Barat',
            'SDN 1 Sukamaju', '2003', 'SMPN 1 Cibadak', '2006',
            'SMAN 1 Cibadak', '2009', 'Universitas Pendidikan Indonesia - Pend. Matematika', '2014',
            '', '', 'password123'
        ];

        $export = new \App\Exports\TemplateExport(
            'Template Data Guru',
            $headings,
            [$sample],
            'FF1A5632'
        );
        return Excel::download($export, 'template_guru.xlsx');
    }
}

