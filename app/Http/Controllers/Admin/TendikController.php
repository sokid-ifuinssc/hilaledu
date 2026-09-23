<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Exports\TendikExport;
use App\Imports\TendikImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class TendikController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'tendik');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $tendiks = $query->orderBy('name')->paginate(10)->withQueryString();

        $total      = User::where('role', 'tendik')->count();
        $aktif      = User::where('role', 'tendik')->where('is_active', true)->count();
        $nonaktif   = $total - $aktif;
        $pengelolas = \App\Models\PengelolaAkademik::with('user', 'ditunjukOleh')->latest()->get();

        return view('admin.tendik.index', compact('tendiks', 'total', 'aktif', 'nonaktif', 'pengelolas'));
    }

    public function create()
    {
        return view('admin.tendik.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'username'         => 'required|string|max:100|unique:users,username|alpha_dash',
            'email'            => 'required|email|max:255|unique:users,email',
            'password'         => 'required|string|min:6|confirmed',
            'nip'              => 'nullable|string|max:50',
            'no_hp'            => 'nullable|string|max:20',
            'jenis_kelamin'    => 'nullable|in:L,P',
            'tahun_masuk'      => 'nullable|string|max:10',
            'lulusan_tahun'    => 'nullable|string|max:10',
            'alamat'           => 'nullable|string',
            'desa'             => 'nullable|string|max:100',
            'kecamatan'        => 'nullable|string|max:100',
            'kabupaten'        => 'nullable|string|max:100',
            'provinsi'         => 'nullable|string|max:100',
            'pendidikan_sd'    => 'nullable|string|max:200',
            'tahun_lulus_sd'   => 'nullable|string|max:10',
            'pendidikan_smp'   => 'nullable|string|max:200',
            'tahun_lulus_smp'  => 'nullable|string|max:10',
            'pendidikan_sma'   => 'nullable|string|max:200',
            'tahun_lulus_sma'  => 'nullable|string|max:10',
            'pendidikan_s1'    => 'nullable|string|max:200',
            'tahun_lulus_s1'   => 'nullable|string|max:10',
            'pendidikan_s2'    => 'nullable|string|max:200',
            'tahun_lulus_s2'   => 'nullable|string|max:10',
            'is_active'        => 'boolean',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        $plainPassword = $validated['password'];
        $validated['password'] = Hash::make($plainPassword);
        $validated['password_plain'] = $plainPassword;
        $validated['role'] = 'tendik';
        $validated['jabatan_utama'] = 'Tendik';
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['nama_lengkap'] = $validated['name'];

        User::create($validated);

        return redirect()->route('superadmin.tendik.index')
            ->with('success', 'Data tenaga kependidikan berhasil ditambahkan.');
    }

    public function edit(User $tendik)
    {
        abort_if($tendik->role !== 'tendik', 404);
        return view('admin.tendik.edit', compact('tendik'));
    }

    public function update(Request $request, User $tendik)
    {
        abort_if($tendik->role !== 'tendik', 404);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'username'         => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('users')->ignore($tendik->id)],
            'email'            => ['required', 'email', 'max:255', Rule::unique('users')->ignore($tendik->id)],
            'password'         => 'nullable|string|min:6|confirmed',
            'nip'              => 'nullable|string|max:50',
            'no_hp'            => 'nullable|string|max:20',
            'jenis_kelamin'    => 'nullable|in:L,P',
            'tahun_masuk'      => 'nullable|string|max:10',
            'lulusan_tahun'    => 'nullable|string|max:10',
            'alamat'           => 'nullable|string',
            'desa'             => 'nullable|string|max:100',
            'kecamatan'        => 'nullable|string|max:100',
            'kabupaten'        => 'nullable|string|max:100',
            'provinsi'         => 'nullable|string|max:100',
            'pendidikan_sd'    => 'nullable|string|max:200',
            'tahun_lulus_sd'   => 'nullable|string|max:10',
            'pendidikan_smp'   => 'nullable|string|max:200',
            'tahun_lulus_smp'  => 'nullable|string|max:10',
            'pendidikan_sma'   => 'nullable|string|max:200',
            'tahun_lulus_sma'  => 'nullable|string|max:10',
            'pendidikan_s1'    => 'nullable|string|max:200',
            'tahun_lulus_s1'   => 'nullable|string|max:10',
            'pendidikan_s2'    => 'nullable|string|max:200',
            'tahun_lulus_s2'   => 'nullable|string|max:10',
            'is_active'        => 'boolean',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.min'      => 'Password minimal 6 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        if (!empty($validated['password'])) {
            $plainPassword = $validated['password'];
            $validated['password'] = Hash::make($plainPassword);
            $validated['password_plain'] = $plainPassword;
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', $tendik->is_active);
        $validated['nama_lengkap'] = $validated['name'];
        $validated['jabatan_utama'] = 'Tendik';

        $tendik->update($validated);

        return redirect()->route('superadmin.tendik.index')
            ->with('success', 'Data tenaga kependidikan berhasil diperbarui.');
    }

    public function destroy(User $tendik)
    {
        abort_if($tendik->role !== 'tendik', 404);
        $tendik->delete();
        return redirect()->route('superadmin.tendik.index')
            ->with('success', 'Data tenaga kependidikan berhasil dihapus.');
    }

    public function toggleActive(User $tendik)
    {
        abort_if($tendik->role !== 'tendik', 404);
        $tendik->update(['is_active' => !$tendik->is_active]);
        $status = $tendik->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('superadmin.tendik.index')
            ->with('success', "Tendik {$tendik->name} berhasil {$status}.");
    }

    // ============== EXPORT ==============

    public function export(Request $request)
    {
        $search = $request->query('search');
        return Excel::download(new TendikExport($search), 'data_tendik_' . date('Ymd_His') . '.xlsx');
    }

    // ============== IMPORT ==============

    public function importForm()
    {
        return view('admin.tendik.import');
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
            $import = new TendikImport($duplicateAction);
            Excel::import($import, $request->file('file'));

            $created  = $import->getCreatedCount();
            $updated  = $import->getUpdatedCount();
            $skipped  = $import->getSkippedCount();
            $replaced = $import->getReplacedCount();
            $matched  = $import->getMatchedRecords();
            $errors   = $import->getErrors();

            $summary = [
                'type'             => 'Tendik',
                'created'          => $created,
                'updated'          => $updated,
                'skipped'          => $skipped,
                'replaced'         => $replaced,
                'duplicate_action' => $duplicateAction,
                'matched'          => $matched,
                'errors'           => $errors,
            ];

            $msgParts = [];
            if ($created > 0)  $msgParts[] = "{$created} tendik baru ditambahkan";
            if ($updated > 0)  $msgParts[] = "{$updated} tendik lama diperbarui/ditimpa";
            if ($replaced > 0) $msgParts[] = "{$replaced} tendik lama diganti baru";
            if ($skipped > 0)  $msgParts[] = "{$skipped} data sama dilewati";
            if (count($errors) > 0) $msgParts[] = count($errors) . " baris ditolak karena error";

            $mainMsg = "Proses import data tendik selesai! " . implode(', ', $msgParts) . ".";

            $redirect = redirect()->route('superadmin.tendik.index')->with('import_summary', $summary);

            if (count($errors) > 0 && ($created + $updated + $replaced) === 0) {
                return $redirect->with('error', $mainMsg);
            } elseif (count($errors) > 0) {
                return $redirect->with('warning', $mainMsg);
            }

            return $redirect->with('success', $mainMsg);
        } catch (\Exception $e) {
            return redirect()->route('superadmin.tendik.index')
                ->with('error', 'Gagal memproses file import: ' . $e->getMessage());
        }
    }

    // ============== DOWNLOAD TEMPLATE ==============

    public function downloadTemplate()
    {
        $headings = [
            'Nama Lengkap', 'Username', 'Email', 'NUPTK', 'No HP', 'Jenis Kelamin',
            'Tahun Masuk', 'Lulusan Tahun', 'Alamat', 'Desa', 'Kecamatan', 'Kabupaten', 'Provinsi',
            'Pendidikan SD', 'Tahun Lulus SD', 'Pendidikan SMP', 'Tahun Lulus SMP',
            'Pendidikan SMA', 'Tahun Lulus SMA', 'Pendidikan S1', 'Tahun Lulus S1',
            'Pendidikan S2', 'Tahun Lulus S2', 'Password'
        ];

        $sample = [
            'Siti Nurhaliza, S.Kom', 'tendik_siti', 'siti@sch.id', '2345678901234567', '081234567891', 'P',
            '2019', '2016', 'Jl. Sukajadi No. 12', 'Cibadak', 'Cibadak', 'Sukabumi', 'Jawa Barat',
            'SDN 1 Sukajadi', '2004', 'SMPN 1 Cibadak', '2007',
            'SMKN 1 Sukabumi', '2010', 'Universitas Bina Sarana Informatika - Sistem Informasi', '2015',
            '', '', 'password123'
        ];

        $export = new \App\Exports\TemplateExport(
            'Template Data Tendik',
            $headings,
            [$sample],
            'FFD35400'
        );
        return Excel::download($export, 'template_tendik.xlsx');
    }
}
