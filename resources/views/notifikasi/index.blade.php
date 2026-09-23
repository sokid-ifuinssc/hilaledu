@extends('layouts.app')
@section('title', 'Notifikasi')
@section('subtitle', 'Daftar notifikasi Anda')

@section('content')
<div class="space-y-4 max-w-3xl">
    <div class="flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Notifikasi</h3>
        @if($notifikasis->where('is_read', false)->count())
        <form method="POST" action="{{ route('notifikasi.readAll') }}">@csrf<button type="submit" class="text-sm text-indigo-600 hover:underline">Tandai semua dibaca</button></form>
        @endif
    </div>

    @forelse($notifikasis as $n)
    <div class="bg-white rounded-xl shadow-sm border {{ $n->is_read ? 'border-gray-100' : 'border-indigo-200 bg-indigo-50/30' }} p-4">
        <div class="flex items-start justify-between">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                    {{ $n->tipe == 'pelanggaran_baru' ? 'bg-red-100 text-red-600' : ($n->tipe == 'rekomendasi_baru' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600') }}">
                    @if($n->tipe == 'pelanggaran_baru')
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @else
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $n->judul }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $n->pesan }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ $n->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @if(!$n->is_read)
            <form method="POST" action="{{ route('notifikasi.read', $n) }}">@csrf<button type="submit" class="text-xs text-indigo-600 hover:underline whitespace-nowrap">Tandai dibaca</button></form>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
        <p class="text-gray-500">Tidak ada notifikasi</p>
    </div>
    @endforelse

    <div>{{ $notifikasis->links() }}</div>
</div>
@endsection
