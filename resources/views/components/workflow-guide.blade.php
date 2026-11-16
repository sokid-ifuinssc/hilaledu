@php
    $currentWorkflow = \App\Support\MenuWorkflows::forRoute(request()->route()?->getName());
    $overallFlow = \App\Support\MenuWorkflows::overallSystemFlow();
    $colorMap = [
        'rose'    => ['bg' => '#fff1f2', 'border' => '#fecdd3', 'text' => '#be123c', 'pill' => '#ffe4e6'],
        'indigo'  => ['bg' => '#eef2ff', 'border' => '#c7d2fe', 'text' => '#3730a3', 'pill' => '#e0e7ff'],
        'blue'    => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1d4ed8', 'pill' => '#dbeafe'],
        'amber'   => ['bg' => '#fffbeb', 'border' => '#fde68a', 'text' => '#b45309', 'pill' => '#fef3c7'],
        'emerald' => ['bg' => '#ecfdf5', 'border' => '#a7f3d0', 'text' => '#047857', 'pill' => '#d1fae5'],
        'purple'  => ['bg' => '#faf5ff', 'border' => '#e9d5ff', 'text' => '#7e22ce', 'pill' => '#f3e8ff'],
        'sky'     => ['bg' => '#f0f9ff', 'border' => '#bae6fd', 'text' => '#0369a1', 'pill' => '#e0f2fe'],
        'slate'   => ['bg' => '#f8fafc', 'border' => '#cbd5e1', 'text' => '#334155', 'pill' => '#e2e8f0'],
    ];
    $c = $colorMap[$currentWorkflow['color'] ?? 'emerald'] ?? $colorMap['emerald'];
@endphp

{{-- Banner Ringkas Alur Kerja Menu Saat Ini --}}
@if($showInline ?? true)
<div id="workflowInlineCard"
     style="background:#ffffff;border:1px solid {{ $c['border'] }};border-radius:14px;margin-bottom:18px;box-shadow:0 2px 8px rgba(15,23,42,0.04);overflow:hidden;">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;padding:10px 16px;background:{{ $c['bg'] }};border-bottom:1px solid {{ $c['border'] }};">
        <div style="display:flex;align-items:center;gap:10px;min-width:0;">
            <span style="width:28px;height:28px;border-radius:8px;background:{{ $c['pill'] }};color:{{ $c['text'] }};display:inline-flex;align-items:center;justify-content:center;font-size:0.9rem;flex-shrink:0;">
                <i class="bi {{ $currentWorkflow['icon'] }}"></i>
            </span>
            <div style="min-width:0;">
                <div style="font-size:0.8rem;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span>Alur Kerja: {{ $currentWorkflow['title'] }}</span>
                    <span style="font-size:0.68rem;font-weight:700;padding:1px 8px;border-radius:999px;background:#fff;color:{{ $c['text'] }};border:1px solid {{ $c['border'] }};">
                        {{ count($currentWorkflow['steps']) }} Tahap
                    </span>
                </div>
                <div style="font-size:0.72rem;color:#475569;margin-top:1px;">{{ $currentWorkflow['subtitle'] }}</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
            <button type="button" onclick="openWorkflowModal('current')"
                    style="background:#fff;color:{{ $c['text'] }};border:1px solid {{ $c['border'] }};border-radius:8px;padding:4px 10px;font-size:0.74rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                <i class="bi bi-info-circle-fill"></i> Detail & Hasil Akhir
            </button>
            <a href="{{ route('alur-kerja.index') }}"
               style="background:{{ $c['text'] }};color:#fff;border-radius:8px;padding:4px 10px;font-size:0.74rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                <i class="bi bi-diagram-3"></i> Peta Sistem
            </a>
            <button type="button" onclick="toggleWorkflowInline()" id="workflowToggleBtn"
                    style="background:transparent;color:#475569;border:1px solid #cbd5e1;border-radius:8px;padding:4px 9px;font-size:0.74rem;font-weight:600;cursor:pointer;"
                    title="Sembunyikan/Tampilkan ringkasan alur">
                <i class="bi bi-chevron-up" id="workflowToggleIcon"></i>
            </button>
        </div>
    </div>
    <div id="workflowInlineBody" style="padding:12px 16px;">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:10px;">
            @foreach($currentWorkflow['steps'] as $idx => $step)
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:9px 11px;position:relative;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:4px;">
                        <span style="font-size:0.68rem;font-weight:800;color:{{ $c['text'] }};background:{{ $c['pill'] }};padding:1px 7px;border-radius:999px;">
                            {{ $step['actor'] }}
                        </span>
                        @if(!$loop->last)
                            <i class="bi bi-arrow-right" style="color:#94a3b8;font-size:0.75rem;"></i>
                        @else
                            <i class="bi bi-flag-fill" style="color:#059669;font-size:0.72rem;" title="Tahap Akhir"></i>
                        @endif
                    </div>
                    <div style="font-size:0.77rem;font-weight:700;color:#0f172a;line-height:1.25;">{{ $step['title'] }}</div>
                    <div style="font-size:0.7rem;color:#64748b;margin-top:3px;line-height:1.35;">{{ $step['desc'] }}</div>
                </div>
            @endforeach
        </div>
        <div style="margin-top:10px;padding:8px 12px;border-radius:10px;background:#ecfdf5;border:1px solid #a7f3d0;display:flex;align-items:flex-start;gap:8px;font-size:0.75rem;color:#065f46;">
            <i class="bi bi-check-circle-fill" style="color:#059669;margin-top:2px;flex-shrink:0;"></i>
            <div><strong>Hasil Akhir Menu Ini:</strong> {{ $currentWorkflow['result'] }}</div>
        </div>
    </div>
</div>
@endif

{{-- Modal Detail Alur Menu + Gambaran Keseluruhan HilalEdu --}}
<div id="workflowGuideModal" style="display:none;position:fixed;inset:0;z-index:9998;background:rgba(15,23,42,0.58);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:780px;max-height:88vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,0.28);border:1px solid #e2e8f0;">
        <div style="padding:16px 20px;background:linear-gradient(135deg,#064e3b,#0f172a);color:#fff;display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <div>
                <div style="font-weight:800;font-size:1rem;display:flex;align-items:center;gap:8px;">
                    <i class="bi bi-diagram-3-fill" style="color:#34d399;"></i> Informasi Alur Kerja Sistem HilalEdu
                </div>
                <div style="font-size:0.76rem;color:#cbd5e1;margin-top:2px;">
                    Panduan proses tahap demi tahap menu saat ini serta gambaran utuh ekosistem sekolah.
                </div>
            </div>
            <button type="button" onclick="closeWorkflowModal()" style="background:rgba(255,255,255,0.14);border:none;color:#fff;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:1.1rem;">&times;</button>
        </div>

        <div style="padding:10px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;">
            <div style="display:flex;gap:8px;">
                <button type="button" id="wfTabCurrentBtn" onclick="switchWorkflowTab('current')"
                        style="padding:7px 14px;border-radius:10px;border:none;font-size:0.8rem;font-weight:700;cursor:pointer;background:#0f172a;color:#fff;">
                    <i class="bi {{ $currentWorkflow['icon'] }} me-1"></i> Alur Menu Ini
                </button>
                <button type="button" id="wfTabOverallBtn" onclick="switchWorkflowTab('overall')"
                        style="padding:7px 14px;border-radius:10px;border:1px solid #cbd5e1;font-size:0.8rem;font-weight:700;cursor:pointer;background:#fff;color:#334155;">
                    <i class="bi bi-globe2 me-1"></i> Gambaran Keseluruhan & Akhir HilalEdu
                </button>
            </div>
            <a href="{{ route('alur-kerja.index') }}" style="font-size:0.78rem;font-weight:700;color:#059669;text-decoration:none;">
                Buka Halaman Peta Lengkap →
            </a>
        </div>

        <div style="padding:18px 20px;overflow-y:auto;flex:1;">
            {{-- Tab 1: Menu Saat Ini --}}
            <div id="wfPanelCurrent" style="display:block;">
                <div style="margin-bottom:14px;">
                    <div style="font-size:1rem;font-weight:800;color:#0f172a;">{{ $currentWorkflow['title'] }}</div>
                    <div style="font-size:0.8rem;color:#64748b;margin-top:2px;">Pihak Terlibat: <strong>{{ $currentWorkflow['actors'] }}</strong></div>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach($currentWorkflow['steps'] as $step)
                        <div style="display:flex;gap:12px;align-items:flex-start;padding:12px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0;">
                            <div style="width:28px;height:28px;border-radius:999px;background:{{ $c['text'] }};color:#fff;font-weight:800;font-size:0.78rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                {{ $loop->iteration }}
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                                    <span style="font-weight:700;font-size:0.86rem;color:#0f172a;">{{ $step['title'] }}</span>
                                    <span style="font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:999px;background:{{ $c['pill'] }};color:{{ $c['text'] }};">
                                        {{ $step['actor'] }}
                                    </span>
                                </div>
                                <p style="font-size:0.78rem;color:#475569;margin:4px 0 0;line-height:1.45;">{{ $step['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div style="margin-top:14px;padding:12px 14px;border-radius:12px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-size:0.8rem;">
                    <strong><i class="bi bi-flag-fill me-1"></i> Akhir Proses Menu Ini:</strong>
                    <div style="margin-top:3px;">{{ $currentWorkflow['result'] }}</div>
                </div>
                @hasSection('helpContent')
                <div style="margin-top:16px;padding-top:14px;border-top:1px dashed #cbd5e1;font-size:0.8rem;color:#334155;">
                    <div style="font-weight:800;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:8px;">Catatan Tambahan Halaman</div>
                    @yield('helpContent')
                </div>
                @endif
            </div>

            {{-- Tab 2: Gambaran Keseluruhan HilalEdu --}}
            <div id="wfPanelOverall" style="display:none;">
                <div style="margin-bottom:14px;padding:12px 14px;border-radius:12px;background:#f0fdf4;border:1px solid #bbf7d0;color:#14532d;font-size:0.8rem;">
                    <strong>Ekosistem Terpadu HilalEdu:</strong> Menghubungkan persiapan data tahun ajaran, perencanaan program kerja & kalender, operasional KBM-BK-PKL-keuangan harian, hingga output nilai rapor, slip gaji, sertifikat digital, kelulusan, dan tracer alumni.
                </div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach($overallFlow as $phase)
                        <div style="padding:14px;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;">
                            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;">
                                <span style="font-weight:800;font-size:0.88rem;color:#0f172a;">{{ $phase['phase'] }}</span>
                                <span style="font-size:0.7rem;font-weight:700;padding:2px 9px;border-radius:999px;background:#e0f2fe;color:#0369a1;">{{ $phase['badge'] }}</span>
                            </div>
                            <ul style="margin:0 0 8px 18px;padding:0;font-size:0.78rem;color:#475569;line-height:1.5;">
                                @foreach($phase['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            <div style="font-size:0.76rem;color:#047857;font-weight:600;background:#ecfdf5;padding:6px 10px;border-radius:8px;border:1px solid #a7f3d0;">
                                <i class="bi bi-check2-all me-1"></i> Output Fase: {{ $phase['output'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const storageKey = 'hilaledu_wf_collapsed_' + @json($currentWorkflow['id']);
        const bodyEl = document.getElementById('workflowInlineBody');
        const iconEl = document.getElementById('workflowToggleIcon');

        function applyCollapsedState(collapsed) {
            if (!bodyEl || !iconEl) return;
            bodyEl.style.display = collapsed ? 'none' : 'block';
            iconEl.className = collapsed ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
        }

        try {
            const saved = localStorage.getItem(storageKey);
            if (saved === '1') applyCollapsedState(true);
        } catch (e) {}

        window.toggleWorkflowInline = function() {
            if (!bodyEl) return;
            const willCollapse = bodyEl.style.display !== 'none';
            applyCollapsedState(willCollapse);
            try { localStorage.setItem(storageKey, willCollapse ? '1' : '0'); } catch (e) {}
        };

        window.switchWorkflowTab = function(tab) {
            const curP = document.getElementById('wfPanelCurrent');
            const ovP = document.getElementById('wfPanelOverall');
            const curB = document.getElementById('wfTabCurrentBtn');
            const ovB = document.getElementById('wfTabOverallBtn');
            if (!curP || !ovP || !curB || !ovB) return;

            const isCurrent = tab !== 'overall';
            curP.style.display = isCurrent ? 'block' : 'none';
            ovP.style.display = isCurrent ? 'none' : 'block';
            curB.style.background = isCurrent ? '#0f172a' : '#fff';
            curB.style.color = isCurrent ? '#fff' : '#334155';
            curB.style.border = isCurrent ? 'none' : '1px solid #cbd5e1';
            ovB.style.background = !isCurrent ? '#0f172a' : '#fff';
            ovB.style.color = !isCurrent ? '#fff' : '#334155';
            ovB.style.border = !isCurrent ? 'none' : '1px solid #cbd5e1';
        };

        window.openWorkflowModal = function(tab) {
            const modal = document.getElementById('workflowGuideModal');
            if (!modal) return;
            modal.style.display = 'flex';
            window.switchWorkflowTab(tab || 'current');
        };

        window.closeWorkflowModal = function() {
            const modal = document.getElementById('workflowGuideModal');
            if (modal) modal.style.display = 'none';
        };

        document.getElementById('workflowGuideModal')?.addEventListener('click', function(e) {
            if (e.target === this) window.closeWorkflowModal();
        });
    })();
</script>
