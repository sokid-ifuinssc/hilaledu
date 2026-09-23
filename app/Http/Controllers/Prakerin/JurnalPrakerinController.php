<?php

namespace App\Http\Controllers\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\Industri;
use App\Models\JurnalPrakerin;
use App\Models\User;
use Illuminate\Http\Request;

class JurnalPrakerinController extends Controller
{
    /**
     * Menampilkan daftar jurnal kegiatan harian siswa prakerin.
     */
    public function index(Request $request)
    {
        $query = JurnalPrakerin::with(['siswa', 'industri']);

        // Jika user adalah siswa, hanya tampilkan jurnal miliknya
        if (auth()->user()->role === 'siswa') {
            $query->where('siswa_id', auth()->id());
        } elseif ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        if ($request->filled('industri_id')) {
            $query->where('industri_id', $request->industri_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $jurnals = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();
        $industris = Industri::orderBy('nama', 'asc')->get();
        $siswas = User::where('role', 'siswa')->where('is_active', true)->orderBy('name', 'asc')->get();

        return view('prakerin.jurnal.index', compact('jurnals', 'industris', 'siswas'));
    }

    /**
     * Simpan jurnal kegiatan harian.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'nullable|exists:users,id',
            'industri_id' => 'required|exists:industris,id',
            'tanggal' => 'required|date',
            'kegiatan' => 'required|string',
        ]);

        // Jika siswa mengisi sendiri
        if (auth()->user()->role === 'siswa') {
            $validated['siswa_id'] = auth()->id();
            $validated['status'] = 'pending';
        } else {
            // Jika admin mengisi, default langsung approved atau sesuai form
            $validated['siswa_id'] = $request->siswa_id ?? auth()->id();
            $validated['status'] = $request->status ?? 'approved';
        }

        JurnalPrakerin::create($validated);

        return redirect()->route('prakerin.jurnal.index')->with('success', 'Jurnal Prakerin berhasil dicatat.');
    }

    /**
     * Update data jurnal.
     */
    public function update(Request $request, JurnalPrakerin $jurnal)
    {
        $validated = $request->validate([
            'industri_id' => 'required|exists:industris,id',
            'tanggal' => 'required|date',
            'kegiatan' => 'required|string',
            'status' => 'nullable|string|in:pending,approved,rejected',
        ]);

        $jurnal->update($validated);

        return redirect()->route('prakerin.jurnal.index')->with('success', 'Jurnal Prakerin berhasil diperbarui.');
    }

    /**
     * Setujui (Approve) jurnal prakerin siswa.
     */
    public function approve(JurnalPrakerin $jurnal)
    {
        $jurnal->update(['status' => 'approved']);
        return back()->with('success', 'Jurnal kegiatan siswa berhasil disetujui.');
    }

    /**
     * Tolak (Reject) jurnal prakerin siswa.
     */
    public function reject(JurnalPrakerin $jurnal)
    {
        $jurnal->update(['status' => 'rejected']);
        return back()->with('info', 'Jurnal kegiatan siswa ditolak.');
    }

    /**
     * Hapus jurnal kegiatan.
     */
    public function destroy(JurnalPrakerin $jurnal)
    {
        $jurnal->delete();
        return redirect()->route('prakerin.jurnal.index')->with('success', 'Jurnal kegiatan berhasil dihapus.');
    }
}
