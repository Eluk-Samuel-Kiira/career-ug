@extends('layouts.admin')

@section('title', 'Auto Apply Agent')
@section('page_title', 'Auto Apply Agent')

@section('content')
@php
    $pagination  = $pagination ?? [
        'current_page' => 1, 'last_page' => 1, 'per_page' => 10,
        'total' => count($runs ?? []),
    ];
    $currentPage = $pagination['current_page'];
    $lastPage    = $pagination['last_page'];
    $totalRuns   = $pagination['total'];
@endphp

<div class="container py-6">

    {{-- ── Header ──────────────────────────────────────── --}}
    <div class="card card-flush mb-6 bg-light-primary">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="symbol symbol-60px">
                    <div class="symbol-label bg-white">
                        <i class="ki-duotone ki-abstract-26 fs-2x text-primary">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div>
                    <h2 class="fw-bold mb-1">Auto Apply Agent</h2>
                    <div class="text-muted fs-7">
                        Every day, we find jobs that match your preferences, tailor your CV and cover letter,
                        and apply on your behalf.
                    </div>
                </div>
            </div>
            @if(!empty($stats))
                <div class="d-flex gap-4 text-end">
                    <div>
                        <div class="fs-2 fw-bold text-primary">{{ $stats['total_applied'] ?? 0 }}</div>
                        <div class="text-muted fs-8">Total applied</div>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-warning">{{ $stats['total_relayed'] ?? 0 }}</div>
                        <div class="text-muted fs-8">Sent to you</div>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-success">{{ $stats['this_month'] ?? 0 }}</div>
                        <div class="text-muted fs-8">This month</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(!$feature_enabled)
        {{-- Locked state --}}
        <div class="card card-flush">
            <div class="card-body py-15 text-center">
                <i class="ki-duotone ki-lock-2 fs-5tx text-warning mb-5 d-block">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
                <h3 class="fw-bold text-gray-800 mb-3">Auto Apply is not active on your plan</h3>
                <p class="text-muted fs-6 mb-6" style="max-width:560px; margin:0 auto 24px;">
                    Upgrade your subscription to let the Auto Apply Agent apply to matching jobs on your behalf,
                    with a CV and cover letter tailored for each role.
                </p>
                <a href="{{ route('pricing.index') }}" class="btn btn-primary">View Plans</a>
            </div>
        </div>
    @elseif(empty($runs))
        {{-- Nothing yet --}}
        <div class="card card-flush">
            <div class="card-body py-15 text-center">
                <i class="ki-duotone ki-rocket fs-5tx text-muted mb-5 d-block">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <h3 class="fw-bold text-gray-800 mb-3">Your agent is warming up</h3>
                <p class="text-muted fs-6" style="max-width:560px; margin:0 auto;">
                    Make sure your CV is uploaded and your job preferences are set.
                    Your first auto-apply run will happen overnight.
                </p>
                <div class="d-flex justify-content-center gap-3 mt-6">
                    <a href="{{ route('cv.edit') }}" class="btn btn-light-primary">Manage CV</a>
                    <a href="{{ route('alert.preferences') }}" class="btn btn-light-primary">Job Preferences</a>
                </div>
            </div>
        </div>
    @else
        {{-- Runs list --}}
        <div class="d-flex flex-column gap-5">
            @foreach($runs as $run)
                <div class="card card-flush">
                    <div class="card-header align-items-center py-5 border-0">
                        <div class="d-flex align-items-center gap-4">
                            <div class="d-flex flex-column text-center" style="min-width:70px;">
                                <div class="fs-2 fw-bold text-gray-800">
                                    {{ \Carbon\Carbon::parse($run['run_date'])->format('d') }}
                                </div>
                                <div class="text-muted fs-8 text-uppercase">
                                    {{ \Carbon\Carbon::parse($run['run_date'])->format('M Y') }}
                                </div>
                            </div>
                            <div class="border-start border-2 border-gray-300 ps-4">
                                <div class="fw-bold text-gray-800">
                                    {{ \Carbon\Carbon::parse($run['run_date'])->format('l') }}
                                </div>
                                <div class="text-muted fs-7">
                                    @if(($run['applied_count'] ?? 0) > 0)
                                        We applied to <strong>{{ $run['applied_count'] }}</strong>
                                        {{ \Illuminate\Support\Str::plural('job', $run['applied_count']) }} for you
                                    @else
                                        No automatic applications on this day
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-toolbar gap-2">
                            @if(($run['applied_count'] ?? 0) > 0)
                                <span class="badge badge-light-success">
                                    {{ $run['applied_count'] }} applied
                                </span>
                            @endif
                            @if(($run['relayed_count'] ?? 0) > 0)
                                <span class="badge badge-light-warning">
                                    {{ $run['relayed_count'] }} to apply
                                </span>
                            @endif
                            <span class="badge badge-light-secondary">{{ $run['status_label'] ?? 'Done' }}</span>
                        </div>
                    </div>

                    @if(!empty($run['relays']))
                        <div class="card-body pt-0">
                            <div class="separator my-4"></div>
                            
                            @foreach($run['relays'] as $relay)
                                <div class="d-flex flex-wrap justify-content-between align-items-center p-4 bg-light-warning rounded mb-3">
                                    <div>
                                        <div class="fw-bold">{{ $relay['job_title'] }}</div>
                                        <div class="text-muted fs-7">{{ $relay['company'] }}</div>
                                    </div>
                                    <div class="d-flex gap-2 mt-2 mt-sm-0">
                                        {{-- NEW: View Documents Button --}}
                                        @if(!empty($relay['cv_html']) || !empty($relay['cover_html']))
                                            <button class="btn btn-sm btn-light-primary" onclick='openSeekerDrawer(@json($relay))'>
                                                <i class="ki-duotone ki-eye fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                View Documents
                                            </button>
                                        @endif

                                        @if($relay['status'] === 'applied')
                                            <span class="badge badge-success py-3 px-4">Applied ✓</span>
                                        @else
                                            <a href="{{ url('/job/' . $relay['job_slug']) }}" class="btn btn-sm btn-primary">Apply Now</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ── Pagination ──────────────────────────────── --}}
        @if($lastPage > 1)
            <div class="d-flex justify-content-between align-items-center mt-6 flex-wrap gap-2">
                <div class="text-muted fs-7">
                    Page <span class="fw-bold">{{ $currentPage }}</span>
                    of <span class="fw-bold">{{ $lastPage }}</span>
                    — {{ $totalRuns }} {{ \Illuminate\Support\Str::plural('run', $totalRuns) }}
                </div>
                <nav>
                    <ul class="pagination pagination-sm m-0">
                        <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => max(1, $currentPage - 1)]) }}">
                                ‹ Prev
                            </a>
                        </li>

                        @php
                            $start = max(1, $currentPage - 2);
                            $end   = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i === $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $lastPage]) }}">
                                    {{ $lastPage }}
                                </a>
                            </li>
                        @endif

                        <li class="page-item {{ $currentPage >= $lastPage ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => min($lastPage, $currentPage + 1)]) }}">
                                Next ›
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    @endif
</div>

{{-- ══════════════════ SEEKER DOCUMENTS DRAWER ══════════════════ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="seekerDrawer" style="width: 800px;">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title mb-0" id="sdTitle">Application Documents</h5>
            <div class="text-muted fs-8" id="sdCompany"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-4" id="sdTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#sd_cv">CV Used</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#sd_cover">Cover Letter Used</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="sd_cv">
                <div id="sdCvContent" class="bg-white p-4 rounded shadow-sm border" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.6; color: #0F1B2D; min-height: 200px;">
                    <p class="text-muted text-center py-10">Loading...</p>
                </div>
            </div>
            <div class="tab-pane fade" id="sd_cover">
                <div id="sdCoverContent" class="bg-white p-4 rounded shadow-sm border" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.6; color: #0F1B2D; min-height: 200px;">
                    <p class="text-muted text-center py-10">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const seekerDrawer = new bootstrap.Offcanvas(document.getElementById('seekerDrawer'));

    window.openSeekerDrawer = function(relay) {
        document.getElementById('sdTitle').textContent = relay.job_title || 'Application Documents';
        document.getElementById('sdCompany').textContent = relay.company || '';

        document.getElementById('sdCvContent').innerHTML = relay.cv_html || '<p class="text-muted">No CV generated.</p>';
        document.getElementById('sdCoverContent').innerHTML = relay.cover_html || '<p class="text-muted">No cover letter generated.</p>';

        // Reset tabs to first
        new bootstrap.Tab(document.querySelector('#sdTabs a[href="#sd_cv"]')).show();

        seekerDrawer.show();
    };
</script>
@endpush