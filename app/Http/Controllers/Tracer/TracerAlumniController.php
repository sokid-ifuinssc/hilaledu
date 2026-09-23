<?php

namespace App\Http\Controllers\Tracer;

use App\Http\Controllers\Controller;
use App\Models\TracerAlumni;
use Illuminate\Http\Request;

class TracerAlumniController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TracerAlumni::with('user');

        if ($request->filled('tahun_lulus')) {
            $query->where('tahun_lulus', $request->tahun_lulus);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $alumnis = $query->orderBy('tahun_lulus', 'desc')->paginate(20)->withQueryString();
        $tahunList = TracerAlumni::select('tahun_lulus')->distinct()->orderBy('tahun_lulus', 'desc')->pluck('tahun_lulus');

        return view('tracer.alumni.index', compact('alumnis', 'tahunList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TracerAlumni $tracerAlumni)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TracerAlumni $alumni)
    {
        $alumni->load('user');
        return view('tracer.alumni.edit', compact('alumni'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TracerAlumni $alumni)
    {
        $request->validate([
            'status_saat_ini' => 'nullable|string|max:255',
            'nama_instansi'   => 'nullable|string|max:255',
            'tahun_lulus'     => 'required|numeric',
        ]);

        $alumni->update($request->only('status_saat_ini', 'nama_instansi', 'tahun_lulus'));

        return redirect()->route('tracer.alumni.index')->with('success', 'Data Tracer Study berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TracerAlumni $tracerAlumni)
    {
        //
    }
}

