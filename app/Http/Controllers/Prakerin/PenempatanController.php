<?php

namespace App\Http\Controllers\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\Penempatan;
use App\Models\PeriodePrakerin;
use App\Models\Siswa;
use App\Models\Dudi;
use App\Models\Guru;
use App\Models\PembimbingDudi;
use Illuminate\Http\Request;

/**
 * Controller Penempatan Prakerin Siswa
 * Mengatur alokasi siswa ke mitra industri, guru pembimbing internal, dan instruktur DU/DI
 */
class PenempatanController extends Controller
{
    /**
     * Menampilkan daftar penempatan siswa prakerin
     */
    public function index(Request $request)
    {
        $periodeList = PeriodePrakerin::with('tahunAjaran')->orderBy('tanggal_mulai', 'desc')->get();
        $periodeId   = $request->get('periode_id', $periodeList->where('aktif', true)->first()?->id ?? $periodeList->first()?->id);

        $query = Penempatan::with([
            'periodePrakerin.tahunAjaran',
            'siswa.kelas.jurusan',
            'dudi',
            'guru',
            'pembimbingDudi',
        ]);

        if ($periodeId) {
            $query->where('periode_prakerin_id', $periodeId);
        }

        if ($request->filled('dudi_id')) {
            $query->where('dudi_id', $request->dudi_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('siswa', function ($sq) use ($s) {
                $sq->where('nama_lengkap', 'like', "%{$s}%")
                   ->orWhere('nis', 'like', "%{$s}%");
            });
        }

        $penempatan = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $dudiList   = Dudi::where('status', true)->orderBy('nama')->get();

        return view('prakerin.penempatan.index', compact('penempatan', 'periodeList', 'periodeId', 'dudiList'));
    }

    /**
     * Form tambah penempatan siswa
     */
    public function create()
    {
        $periodeList    = PeriodePrakerin::with('tahunAjaran')->orderBy('tanggal_mulai', 'desc')->get();
        $siswaList      = Siswa::with('kelas.jurusan')->where('status', 'aktif')->orderBy('nama_lengkap')->get();
        $dudiList       = Dudi::where('status', true)->orderBy('nama')->get();
        $guruList       = Guru::where('is_active', true)->orderBy('nama_lengkap')->get();
        $pembimbingList = PembimbingDudi::with('dudi')->where('status', true)->orderBy('nama')->get();

        return view('prakerin.penempatan.create', compact(
            'periodeList', 'siswaList', 'dudiList', 'guruList', 'pembimbingList'
        ));
    }

    /**
     * Simpan penempatan siswa baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'periode_prakerin_id' => 'required|exists:periode_prakerin,id',
            'siswa_id'            => 'required|exists:siswas,id',
            'dudi_id'             => 'required|exists:dudi,id',
            'guru_id'             => 'required|exists:gurus,id',
            'pembimbing_dudi_id'  => 'required|exists:pembimbing_dudi,id',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
            'status'              => 'required|in:belum_mulai,aktif,selesai',
            'keterangan'          => 'nullable|max:500',
        ], [
            'periode_prakerin_id.required' => 'Periode prakerin wajib dipilih.',
            'siswa_id.required'            => 'Siswa wajib dipilih.',
            'dudi_id.required'             => 'DU/DI wajib dipilih.',
            'guru_id.required'             => 'Guru pembimbing wajib dipilih.',
            'pembimbing_dudi_id.required'  => 'Pembimbing DU/DI wajib dipilih.',
            'tanggal_mulai.required'       => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required'     => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        // Cek duplikasi: satu siswa satu penempatan per periode
        $exists = Penempatan::where('periode_prakerin_id', $request->periode_prakerin_id)
            ->where('siswa_id', $request->siswa_id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'Siswa tersebut sudah memiliki penempatan di periode yang dipilih.');
        }

        Penempatan::create($request->only(
            'periode_prakerin_id', 'siswa_id', 'dudi_id', 'guru_id',
            'pembimbing_dudi_id', 'tanggal_mulai', 'tanggal_selesai', 'status', 'keterangan'
        ));

        return redirect()->route('prakerin.penempatan.index', ['periode_id' => $request->periode_prakerin_id])
            ->with('success', 'Penempatan siswa prakerin berhasil disimpan.');
    }

    /**
     * Detail penempatan siswa
     */
    public function show(Penempatan $penempatan)
    {
        $penempatan->load([
            'periodePrakerin.tahunAjaran',
            'siswa.kelas.jurusan',
            'dudi',
            'guru',
            'pembimbingDudi',
        ]);

        return view('prakerin.penempatan.show', compact('penempatan'));
    }

    /**
     * Form edit penempatan siswa
     */
    public function edit(Penempatan $penempatan)
    {
        $periodeList    = PeriodePrakerin::with('tahunAjaran')->orderBy('tanggal_mulai', 'desc')->get();
        $siswaList      = Siswa::with('kelas.jurusan')->where('status', 'aktif')->orderBy('nama_lengkap')->get();
        $dudiList       = Dudi::where('status', true)->orderBy('nama')->get();
        $guruList       = Guru::where('is_active', true)->orderBy('nama_lengkap')->get();
        $pembimbingList = PembimbingDudi::with('dudi')->where('status', true)->orderBy('nama')->get();

        return view('prakerin.penempatan.edit', compact(
            'penempatan', 'periodeList', 'siswaList', 'dudiList', 'guruList', 'pembimbingList'
        ));
    }

    /**
     * Perbarui data penempatan siswa
     */
    public function update(Request $request, Penempatan $penempatan)
    {
        $request->validate([
            'periode_prakerin_id' => 'required|exists:periode_prakerin,id',
            'siswa_id'            => 'required|exists:siswas,id',
            'dudi_id'             => 'required|exists:dudi,id',
            'guru_id'             => 'required|exists:gurus,id',
            'pembimbing_dudi_id'  => 'required|exists:pembimbing_dudi,id',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
            'status'              => 'required|in:belum_mulai,aktif,selesai',
            'keterangan'          => 'nullable|max:500',
        ], [
            'periode_prakerin_id.required' => 'Periode prakerin wajib dipilih.',
            'siswa_id.required'            => 'Siswa wajib dipilih.',
            'dudi_id.required'             => 'DU/DI wajib dipilih.',
            'guru_id.required'             => 'Guru pembimbing wajib dipilih.',
            'pembimbing_dudi_id.required'  => 'Pembimbing DU/DI wajib dipilih.',
            'tanggal_mulai.required'       => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required'     => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        // Cek duplikasi (kecuali dirinya sendiri)
        $exists = Penempatan::where('periode_prakerin_id', $request->periode_prakerin_id)
            ->where('siswa_id', $request->siswa_id)
            ->where('id', '!=', $penempatan->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'Siswa tersebut sudah memiliki penempatan di periode yang dipilih.');
        }

        $penempatan->update($request->only(
            'periode_prakerin_id', 'siswa_id', 'dudi_id', 'guru_id',
            'pembimbing_dudi_id', 'tanggal_mulai', 'tanggal_selesai', 'status', 'keterangan'
        ));

        return redirect()->route('prakerin.penempatan.index', ['periode_id' => $request->periode_prakerin_id])
            ->with('success', 'Data penempatan siswa prakerin berhasil diperbarui.');
    }

    /**
     * Hapus penempatan siswa
     */
    public function destroy(Penempatan $penempatan)
    {
        $periodeId = $penempatan->periode_prakerin_id;
        $penempatan->delete();

        return redirect()->route('prakerin.penempatan.index', ['periode_id' => $periodeId])
            ->with('success', 'Data penempatan siswa prakerin berhasil dihapus.');
    }
}
