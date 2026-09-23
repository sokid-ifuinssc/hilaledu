<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Exports\SiswaExport;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'siswa')->with('kelas.jurusan');

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

        if ($request->filled('kelas_id')) {
            if ($request->kelas_id === 'none') {
                $query->whereNull('kelas_id');
            } else {
                $query->where('kelas_id', $request->kelas_id);
            }
        }

        $siswas    = $query->orderBy('name')->paginate(20)->withQueryString();
        $total     = User::where('role', 'siswa')->count();
        $aktif     = User::where('role', 'siswa')->where('is_active', true)->count();
        $kelasList = Kelas::with('jurusan')->get()->sortBy('nama_lengkap');

        return view('admin.siswa.index', compact('siswas', 'total', 'aktif', 'kelasList'));
    }

    public function create()
    {
        $kelas = Kelas::with('jurusan')->get()->sortBy('nama_lengkap');
        return view('admin.siswa.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'username'         => 'required|string|max:255|unique:users,username|alpha_dash',
            'email'            => 'required|email|max:255|unique:users,email',
            'nip'              => 'nullable|string|max:30',
            'kelas_id'         => 'nullable|exists:kelas,id',
            'no_hp'            => 'nullable|string|max:20',
            'jenis_kelamin'    => 'nullable|in:L,P',
            // Alamat
            'alamat'           => 'nullable|string',
            'desa'             => 'nullable|string|max:100',
            'kecamatan'        => 'nullable|string|max:100',
            'kabupaten'        => 'nullable|string|max:100',
            'provinsi'         => 'nullable|string|max:100',
            // Data Orang Tua / Wali
            'nama_ayah'        => 'nullable|string|max:150',
            'nama_ibu'         => 'nullable|string|max:150',
            'no_hp_ortu'       => 'nullable|string|max:30',
            // Riwayat Pendidikan
            'pendidikan_sd'    => 'nullable|string|max:150',
            'tahun_lulus_sd'   => 'nullable|string|max:10',
            'pendidikan_smp'   => 'nullable|string|max:150',
            'tahun_lulus_smp'  => 'nullable|string|max:10',
            'password'         => 'required|string|min:6|confirmed',
            'is_active'        => 'boolean',
        ]);

        $validated['role']           = 'siswa';
        $validated['is_active']      = $request->has('is_active');
        $validated['nama_lengkap']   = $validated['name'];
        $validated['password_plain'] = $validated['password'];

        $user = User::create($validated);

        // Sync legacy table if exists
        if (\Illuminate\Support\Facades\Schema::hasTable('siswas')) {
            \Illuminate\Support\Facades\DB::table('siswas')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'kelas_id' => $user->kelas_id,
                    'nis' => $user->nip ?? ('NIS-' . $user->id),
                    'nisn' => $user->nip,
                    'nama_lengkap' => $user->name,
                    'jenis_kelamin' => $user->jenis_kelamin ?? 'L',
                    'updated_at' => now()
                ]
            );
        }

        return redirect()->route('superadmin.siswa.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(User $siswa)
    {
        abort_if($siswa->role !== 'siswa', 404);
        $kelas = Kelas::with('jurusan')->get()->sortBy('nama_lengkap');
        return view('admin.siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(Request $request, User $siswa)
    {
        abort_if($siswa->role !== 'siswa', 404);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'username'         => ['required', 'alpha_dash', Rule::unique('users')->ignore($siswa->id)],
            'email'            => ['required', 'email', Rule::unique('users')->ignore($siswa->id)],
            'nip'              => ['nullable', 'string', 'max:30'],
            'kelas_id'         => 'nullable|exists:kelas,id',
            'no_hp'            => 'nullable|string|max:20',
            'jenis_kelamin'    => 'nullable|in:L,P',
            // Alamat
            'alamat'           => 'nullable|string',
            'desa'             => 'nullable|string|max:100',
            'kecamatan'        => 'nullable|string|max:100',
            'kabupaten'        => 'nullable|string|max:100',
            'provinsi'         => 'nullable|string|max:100',
            // Data Orang Tua / Wali
            'nama_ayah'        => 'nullable|string|max:150',
            'nama_ibu'         => 'nullable|string|max:150',
            'no_hp_ortu'       => 'nullable|string|max:30',
            // Riwayat Pendidikan
            'pendidikan_sd'    => 'nullable|string|max:150',
            'tahun_lulus_sd'   => 'nullable|string|max:10',
            'pendidikan_smp'   => 'nullable|string|max:150',
            'tahun_lulus_smp'  => 'nullable|string|max:10',
            'password'         => 'nullable|string|min:6|confirmed',
            'is_active'        => 'boolean',
        ]);

        $validated['is_active']    = $request->has('is_active');
        $validated['nama_lengkap'] = $validated['name'];

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password_plain'] = $validated['password'];
        }

        $siswa->update($validated);

        // Sync legacy table if exists
        if (\Illuminate\Support\Facades\Schema::hasTable('siswas')) {
            \Illuminate\Support\Facades\DB::table('siswas')->updateOrInsert(
                ['user_id' => $siswa->id],
                [
                    'kelas_id' => $siswa->kelas_id,
                    'nis' => $siswa->nip ?? ('NIS-' . $siswa->id),
                    'nisn' => $siswa->nip,
                    'nama_lengkap' => $siswa->name,
                    'jenis_kelamin' => $siswa->jenis_kelamin ?? 'L',
                    'updated_at' => now()
                ]
            );
        }

        return redirect()->route('superadmin.siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(User $siswa)
    {
        abort_if($siswa->role !== 'siswa', 404);
        $siswa->delete();
        return redirect()->route('superadmin.siswa.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    // ============== EXPORT ==============

    public function export(Request $request)
    {
        $search = $request->query('search');
        return Excel::download(new SiswaExport($search), 'data_siswa_' . date('Ymd_His') . '.xlsx');
    }

    // ============== IMPORT ==============

    public function importForm()
    {
        return view('admin.siswa.import');
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
            $import = new SiswaImport($duplicateAction);
            Excel::import($import, $request->file('file'));

            $created  = $import->getCreatedCount();
            $updated  = $import->getUpdatedCount();
            $skipped  = $import->getSkippedCount();
            $replaced = $import->getReplacedCount();
            $matched  = $import->getMatchedRecords();
            $errors   = $import->getErrors();

            $summary = [
                'type'             => 'Siswa',
                'created'          => $created,
                'updated'          => $updated,
                'skipped'          => $skipped,
                'replaced'         => $replaced,
                'duplicate_action' => $duplicateAction,
                'matched'          => $matched,
                'errors'           => $errors,
            ];

            $msgParts = [];
            if ($created > 0)  $msgParts[] = "{$created} siswa baru ditambahkan";
            if ($updated > 0)  $msgParts[] = "{$updated} siswa lama diperbarui/ditimpa";
            if ($replaced > 0) $msgParts[] = "{$replaced} siswa lama diganti baru";
            if ($skipped > 0)  $msgParts[] = "{$skipped} data sama dilewati";
            if (count($errors) > 0) $msgParts[] = count($errors) . " baris ditolak karena error";

            $mainMsg = "Proses import data siswa selesai! " . implode(', ', $msgParts) . ".";

            $redirect = redirect()->route('superadmin.siswa.index')->with('import_summary', $summary);

            if (count($errors) > 0 && ($created + $updated + $replaced) === 0) {
                return $redirect->with('error', $mainMsg);
            } elseif (count($errors) > 0) {
                return $redirect->with('warning', $mainMsg);
            }

            return $redirect->with('success', $mainMsg);
        } catch (\Exception $e) {
            return redirect()->route('superadmin.siswa.index')
                ->with('error', 'Gagal memproses file import: ' . $e->getMessage());
        }
    }

    // ============== DOWNLOAD TEMPLATE ==============

    public function downloadTemplate()
    {
        $headings = [
            'Nama Lengkap', 'Username', 'Email', 'NIS/NISN', 'No HP Siswa',
            'Nama Ayah', 'Nama Ibu', 'No HP Ortu', 'Jenis Kelamin',
            'Alamat', 'Desa', 'Kecamatan', 'Kabupaten', 'Provinsi',
            'Pendidikan SD', 'Tahun Lulus SD', 'Pendidikan SMP', 'Tahun Lulus SMP',
            'Password'
        ];

        $sample = [
            'Andi Pratama', 'siswa_andi', 'andi@sch.id', '0012345678', '085712345678',
            'Bambang Pratama', 'Siti Aminah', '081234567890', 'L',
            'Jl. Sukajadi No. 45', 'Sukajadi', 'Cibadak', 'Sukabumi', 'Jawa Barat',
            'SDN 1 Cibadak', '2021', 'SMPN 1 Cibadak', '2024',
            'password123'
        ];

        $export = new \App\Exports\TemplateExport(
            'Template Data Siswa',
            $headings,
            [$sample],
            'FF27AE60'
        );
        return Excel::download($export, 'template_siswa.xlsx');
    }

    public function poinIndex(\Illuminate\Http\Request $request)
    {
        $query = Siswa::with('kelas.jurusan')->aktif();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswas = $query->orderBy('poin', 'asc')->paginate(20)->withQueryString();
        $kelasList = \App\Models\Kelas::with('jurusan')->orderBy('nama')->get();

        return view('bk.poin.index', compact('siswas', 'kelasList'));
    }

    // ============== MIGRASI ==============

    public function migrasiProses(Request $request)
    {
        $request->validate([
            'siswa_ids'    => 'required|array|min:1',
            'siswa_ids.*'  => 'exists:users,id',
            'action_type'  => 'required|in:naik_kelas,lulus',
            'target_kelas' => 'required_if:action_type,naik_kelas|nullable|exists:kelas,id',
            'tahun_lulus'  => 'required_if:action_type,lulus|nullable|numeric',
        ]);

        $siswaIds = $request->siswa_ids;
        $action = $request->action_type;

        if ($action === 'naik_kelas') {
            User::whereIn('id', $siswaIds)->update(['kelas_id' => $request->target_kelas]);
            return redirect()->back()->with('success', count($siswaIds) . ' siswa berhasil dipindah kelas.');
        } elseif ($action === 'lulus') {
            foreach ($siswaIds as $id) {
                User::where('id', $id)->update([
                    'kelas_id' => null,
                    'is_active' => false
                ]);
                \App\Models\TracerAlumni::updateOrCreate(
                    ['user_id' => $id],
                    ['tahun_lulus' => $request->tahun_lulus]
                );
            }
            return redirect()->back()->with('success', count($siswaIds) . ' siswa berhasil diluluskan dan dipindah ke data alumni.');
        }

        return redirect()->back()->with('error', 'Aksi tidak valid.');
    }
}

