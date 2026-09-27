<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pembayarans = Pembayaran::with(['tagihan.siswa', 'penerima'])
            ->orderBy('tanggal_bayar', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('keuangan.pembayaran.index', compact('pembayarans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tagihan_id' => 'required|exists:tagihans,id',
            'nominal_bayar' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date',
            'metode_pembayaran' => 'required|string'
        ]);

        $tagihan = Tagihan::findOrFail($request->tagihan_id);

        // Validate max nominal
        $sisaTagihan = $tagihan->nominal - $tagihan->terbayar;
        if ($request->nominal_bayar > $sisaTagihan) {
            return back()->with('error', 'Nominal bayar melebihi sisa tagihan.');
        }

        // Create transaction
        Pembayaran::create([
            'kode_transaksi' => 'TRX-' . strtoupper(Str::random(8)),
            'tagihan_id' => $tagihan->id,
            'nominal_bayar' => $request->nominal_bayar,
            'tanggal_bayar' => $request->tanggal_bayar,
            'metode_pembayaran' => $request->metode_pembayaran,
            'catatan' => $request->catatan,
            'penerima_id' => auth()->id(),
        ]);

        // Update tagihan
        $tagihan->terbayar += $request->nominal_bayar;
        if ($tagihan->terbayar >= $tagihan->nominal) {
            $tagihan->status = 'lunas';
        }
        $tagihan->save();

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $tagihan = $pembayaran->tagihan;
        
        $tagihan->terbayar -= $pembayaran->nominal_bayar;
        $tagihan->status = 'belum_lunas'; // revert status
        $tagihan->save();

        $pembayaran->delete();

        return back()->with('success', 'Transaksi pembayaran berhasil dibatalkan.');
    }
}

