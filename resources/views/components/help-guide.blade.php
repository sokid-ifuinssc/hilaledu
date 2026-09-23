{{-- Floating Help Button + Modal --}}
<div x-data="{ helpOpen: false }" class="help-guide-wrapper">
    {{-- Floating Help Button --}}
    <button @click="helpOpen = true"
            class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-xl group"
            style="background: linear-gradient(135deg, #4f46e5, #7c3aed);"
            title="Petunjuk Penggunaan">
        <svg class="w-7 h-7 text-white transition-transform duration-300 group-hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="absolute -top-1 -right-1 w-5 h-5 bg-amber-400 rounded-full flex items-center justify-center animate-pulse">
            <span class="text-[9px] font-bold text-amber-900">?</span>
        </span>
    </button>

    {{-- Modal Overlay --}}
    <div x-show="helpOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="helpOpen = false"
         class="fixed inset-0 z-[60] bg-black/40 backdrop-blur-sm"
         style="display: none;">
    </div>

    {{-- Modal Panel --}}
    <div x-show="helpOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-full"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-full"
         class="fixed top-0 right-0 z-[70] h-full w-full max-w-xl bg-white shadow-2xl overflow-hidden flex flex-col"
         style="display: none;">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-100 flex-shrink-0" style="background: linear-gradient(135deg, #312e81, #4f46e5);">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Petunjuk Penggunaan</h2>
                        <p class="text-indigo-200 text-xs">Panduan halaman ini</p>
                    </div>
                </div>
                <button @click="helpOpen = false" class="p-2 rounded-lg hover:bg-white/10 text-white/70 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto px-6 py-5">
            <div class="help-content space-y-4">
                {{ $slot }}
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/80 flex-shrink-0">
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Klik tombol <strong class="text-indigo-500">?</strong> di pojok kanan bawah untuk membuka panduan ini kapan saja.</span>
            </div>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
    .help-content h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e1b4b;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .help-content h3 .help-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
    }
    .help-content p, .help-content li {
        font-size: 0.8rem;
        color: #4b5563;
        line-height: 1.6;
    }
    .help-content ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .help-content ul li {
        position: relative;
        padding-left: 1.25rem;
        margin-bottom: 0.35rem;
    }
    .help-content ul li::before {
        content: '✦';
        position: absolute;
        left: 0;
        color: #6366f1;
        font-size: 0.65rem;
        top: 3px;
    }
    .help-content ol {
        padding-left: 0;
        counter-reset: help-step;
        list-style: none;
    }
    .help-content ol li {
        counter-increment: help-step;
        position: relative;
        padding-left: 2rem;
        margin-bottom: 0.5rem;
        font-size: 0.8rem;
        color: #4b5563;
        line-height: 1.6;
    }
    .help-content ol li::before {
        content: counter(help-step);
        position: absolute;
        left: 0;
        top: 1px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .help-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
    }
    .help-section + .help-section {
        margin-top: 0.75rem;
    }
    .help-tip {
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        border: 1px solid #c7d2fe;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .help-tip .tip-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        background: #6366f1;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1px;
    }
    .help-tip p {
        font-size: 0.78rem;
        color: #3730a3;
        margin: 0;
    }
    .help-warning {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border: 1px solid #fcd34d;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .help-warning p {
        font-size: 0.78rem;
        color: #92400e;
        margin: 0;
    }
    .help-flow {
        display: flex;
        flex-direction: column;
        gap: 0;
        align-items: center;
    }
    .help-flow-step {
        width: 100%;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
    }
    .help-flow-arrow {
        display: flex;
        justify-content: center;
        padding: 0.15rem 0;
        color: #a5b4fc;
    }
    .help-flow-step .flow-number {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        flex-shrink: 0;
    }
    .help-flow-step .flow-content h4 {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1e1b4b;
        margin: 0;
    }
    .help-flow-step .flow-content p {
        font-size: 0.72rem;
        color: #6b7280;
        margin: 0;
        margin-top: 2px;
    }
    .help-result {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #6ee7b7;
        border-radius: 10px;
        padding: 0.75rem 1rem;
    }
    .help-result h4 {
        font-size: 0.82rem;
        font-weight: 700;
        color: #065f46;
        margin: 0 0 0.25rem 0;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .help-result p, .help-result li {
        font-size: 0.78rem;
        color: #047857;
        margin: 0;
    }
</style>
@endpush
@endonce
