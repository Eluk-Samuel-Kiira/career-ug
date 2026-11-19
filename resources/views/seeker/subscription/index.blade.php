@extends('layouts.admin')

@section('title', 'Subscription & Payments')
@section('page_title', 'Subscription & Payments')

@section('content')
<div class="container py-6">

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- SUMMARY STRIP                                          --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-6">
        <div class="col-md-3">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Total Paid (all services)</div>
                    @php
                        $currency = $active['currency'] ?? 'UGX';
                        $totalPaidRaw = $summary['total_paid_cents'] ?? 0;
                        $totalPaidDisplay = $currency === 'UGX'
                            ? number_format($totalPaidRaw, 0)              // UGX has 0 decimals — don't divide
                            : number_format($totalPaidRaw / 100, 2);       // USD etc. divide by 100
                    @endphp
                    <div class="fs-2 fw-bold text-gray-900">
                        {{ strtoupper($currency) }} {{ $totalPaidDisplay }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Payments Made</div>
                    <div class="fs-2 fw-bold text-gray-900">{{ $summary['total_payments'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Subscriptions</div>
                    <div class="fs-2 fw-bold text-gray-900">{{ $summary['subscription_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Active Since</div>
                    <div class="fs-3 fw-bold text-gray-900">
                        {{ !empty($summary['active_since']) ? \Carbon\Carbon::parse($summary['active_since'])->format('M d, Y') : '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- CURRENT PLAN                                           --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @if(!empty($active))
        @php
            $status = $active['status'] ?? 'active';
            $tone = match (true) {
                $status === 'past_due'                  => 'danger',
                $status === 'trialing'                  => 'info',
                !empty($active['cancel_at_period_end']) => 'warning',
                !empty($active['is_active'])            => 'success',
                default                                 => 'secondary',
            };
        @endphp

        <div class="card card-flush bg-light-{{ $tone }} mb-6">
            <div class="card-body py-6">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                    <div>
                        <div class="text-muted fs-8 text-uppercase">Current Plan</div>
                        <div class="fs-3 fw-bold text-gray-900 mt-1">{{ $active['service_name'] }}</div>
                        <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
                            <span class="badge badge-light-{{ $tone }}">{{ $active['status_label'] }}</span>
                            <span class="text-muted fs-7">{{ $active['amount_formatted'] }} / {{ $active['interval'] }}</span>
                            @if(!empty($active['current_period_end']))
                                <span class="text-muted fs-7">
                                    Renews {{ \Carbon\Carbon::parse($active['current_period_end'])->format('M d, Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
                        <button type="button"
                                class="btn btn-light-primary w-100 w-sm-auto"
                                id="openPerfBtn">
                            <i class="ki-duotone ki-chart-simple fs-4 me-1">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            View Performance
                        </button>
                        <a href="{{ $pricingUrl }}" class="btn btn-primary w-100 w-sm-auto">View Plan</a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card card-flush bg-light-primary mb-6">
            <div class="card-body py-6 d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div>
                    <div class="fs-3 fw-bold text-gray-900">You are not subscribed to any plan</div>
                    <div class="text-muted fs-7 mt-1">Choose a plan to unlock premium features.</div>
                </div>
                <a href="{{ $pricingUrl }}" class="btn btn-primary">See Pricing</a>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- PAYMENT HISTORY                                        --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @php
        $paymentsPagination = $pagination['payments'] ?? [
            'current_page' => 1, 'last_page' => 1, 'per_page' => 10,
            'total' => count($payments ?? []), 'has_more' => false,
        ];
        $paymentsTotal      = $paymentsPagination['total'];
        $paymentsCurrent    = $paymentsPagination['current_page'];
        $paymentsLast       = $paymentsPagination['last_page'];
    @endphp

    <div class="card card-flush mb-6">
        <div class="card-header align-items-center py-5">
            <h3 class="card-title fw-bold mb-0">Payment History</h3>
            <div class="card-toolbar">
                <span class="badge badge-light-secondary">{{ $paymentsTotal }} records</span>
            </div>
        </div>

        <div class="card-body pt-0">
            @if(empty($payments))
                <div class="text-center py-15">
                    <i class="ki-duotone ki-information-5 fs-5tx text-muted mb-4 d-block">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <h4 class="fw-bold text-gray-800 mb-2">No payments yet</h4>
                    <p class="text-muted">Your payment records will appear here once you subscribe.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 fs-7 gy-3">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="text-start min-w-180px">Purpose</th>
                                <th class="text-start min-w-140px">Amount</th>
                                <th class="text-start min-w-130px">Status</th>
                                <th class="text-start min-w-180px">Reference</th>
                                <th class="text-start min-w-160px">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $p)
                                @php
                                    $pStatus = $p['status'] ?? '';

                                    $pColor = match($pStatus) {
                                        'completed' => 'success',
                                        'pending'   => 'info',
                                        'failed'    => 'danger',
                                        'cancelled' => 'secondary',
                                        default     => 'secondary',
                                    };

                                    $purposeColor = match($p['purpose_type'] ?? '') {
                                        'subscription'     => 'primary',
                                        'cv_review'        => 'info',
                                        'cv_rewrite',
                                        'cv_rewrite_human' => 'success',
                                        'cover_letter'     => 'warning',
                                        'job_submission'   => 'dark',
                                        default            => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-start">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-light-{{ $purposeColor }}">
                                                {{ $p['purpose_label'] ?? '—' }}
                                            </span>
                                        </div>
                                        @if(!empty($p['description']))
                                            <div class="text-muted fs-8 mt-1">
                                                {{ \Illuminate\Support\Str::limit($p['description'], 60) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-start fw-bold">{{ $p['amount_formatted'] ?? '—' }}</td>
                                    <td class="text-start">
                                        <span class="badge badge-light-{{ $pColor }}">
                                            {{ $p['status_label'] ?? $pStatus }}
                                        </span>
                                    </td>
                                    <td class="text-start text-muted fs-8">
                                        <div>{{ $p['gateway'] ?? '—' }}</div>
                                        <div style="font-family:monospace;">
                                            {{ \Illuminate\Support\Str::limit($p['reference'] ?? '—', 16) }}
                                        </div>
                                    </td>
                                    <td class="text-start text-muted fs-8">
                                        @if(!empty($p['paid_at']))
                                            Paid {{ \Carbon\Carbon::parse($p['paid_at'])->format('M d, Y H:i') }}
                                        @elseif(!empty($p['created_at']))
                                            Attempted {{ \Carbon\Carbon::parse($p['created_at'])->format('M d, Y H:i') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ── Payments pagination ─────────────────────── --}}
                @if($paymentsLast > 1)
                    <div class="d-flex justify-content-between align-items-center mt-6 flex-wrap gap-2">
                        <div class="text-muted fs-8">
                            Page <span class="fw-bold">{{ $paymentsCurrent }}</span>
                            of <span class="fw-bold">{{ $paymentsLast }}</span>
                            — {{ $paymentsTotal }} total
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm m-0">
                                <li class="page-item {{ $paymentsCurrent <= 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['payments_page' => max(1, $paymentsCurrent - 1)]) }}">
                                        ‹ Prev
                                    </a>
                                </li>

                                @php
                                    $start = max(1, $paymentsCurrent - 2);
                                    $end   = min($paymentsLast, $paymentsCurrent + 2);
                                @endphp

                                @if($start > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['payments_page' => 1]) }}">1</a>
                                    </li>
                                    @if($start > 2)
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    @endif
                                @endif

                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $i === $paymentsCurrent ? 'active' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['payments_page' => $i]) }}">{{ $i }}</a>
                                    </li>
                                @endfor

                                @if($end < $paymentsLast)
                                    @if($end < $paymentsLast - 1)
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['payments_page' => $paymentsLast]) }}">
                                            {{ $paymentsLast }}
                                        </a>
                                    </li>
                                @endif

                                <li class="page-item {{ $paymentsCurrent >= $paymentsLast ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['payments_page' => min($paymentsLast, $paymentsCurrent + 1)]) }}">
                                        Next ›
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- SUBSCRIPTION HISTORY                                   --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @php
        $subsPagination = $pagination['subscriptions'] ?? [
            'current_page' => 1, 'last_page' => 1, 'per_page' => 10,
            'total' => count($subscriptions ?? []), 'has_more' => false,
        ];
        $subsTotal   = $subsPagination['total'];
        $subsCurrent = $subsPagination['current_page'];
        $subsLast    = $subsPagination['last_page'];
    @endphp

    <div class="card card-flush">
        <div class="card-header align-items-center py-5">
            <h3 class="card-title fw-bold mb-0">Subscription History</h3>
            <div class="card-toolbar">
                <span class="badge badge-light-secondary">{{ $subsTotal }} records</span>
            </div>
        </div>

        <div class="card-body pt-0">
            @if(empty($subscriptions))
                <div class="text-center py-15">
                    <i class="ki-duotone ki-information-5 fs-5tx text-muted mb-4 d-block">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <h4 class="fw-bold text-gray-800 mb-2">No subscriptions yet</h4>
                    <p class="text-muted">Once you subscribe to a plan, your history will appear here.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 fs-7 gy-3">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="text-start min-w-180px">Plan</th>
                                <th class="text-start min-w-140px">Amount</th>
                                <th class="text-start min-w-150px">Status</th>
                                <th class="text-start min-w-140px">Started</th>
                                <th class="text-start min-w-140px">Period Ends</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscriptions as $s)   <!-- ✅ SUBSCRIPTIONS, not payments -->
                                @php
                                    $sTone = match (true) {
                                        ($s['status'] ?? '') === 'past_due' => 'danger',
                                        ($s['status'] ?? '') === 'trialing' => 'info',
                                        !empty($s['cancel_at_period_end'])  => 'warning',
                                        !empty($s['is_active'])             => 'success',
                                        default                             => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-start">
                                        <div class="fw-bold text-gray-800">{{ $s['service_name'] ?? '—' }}</div>
                                        <div class="text-muted fs-8">{{ $s['service_key'] ?? '' }}</div>
                                    </td>
                                    <td class="text-start fw-bold">
                                        {{ $s['amount_formatted'] ?? '—' }}
                                        <div class="text-muted fs-8 fw-normal">/ {{ $s['interval'] ?? 'month' }}</div>
                                    </td>
                                    <td class="text-start">
                                        <span class="badge badge-light-{{ $sTone }}">{{ $s['status_label'] ?? ucfirst($s['status'] ?? '') }}</span>
                                    </td>
                                    <td class="text-start text-muted fs-8">
                                        {{ !empty($s['starts_at']) ? \Carbon\Carbon::parse($s['starts_at'])->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="text-start text-muted fs-8">
                                        {{ !empty($s['current_period_end']) ? \Carbon\Carbon::parse($s['current_period_end'])->format('M d, Y') : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Subscriptions pagination --}}
                @if($subsLast > 1)
                    <div class="d-flex justify-content-between align-items-center mt-6 flex-wrap gap-2">
                        <div class="text-muted fs-8">
                            Page <span class="fw-bold">{{ $subsCurrent }}</span>
                            of <span class="fw-bold">{{ $subsLast }}</span>
                            — {{ $subsTotal }} total
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm m-0">
                                <li class="page-item {{ $subsCurrent <= 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['subs_page' => max(1, $subsCurrent - 1)]) }}">‹ Prev</a>
                                </li>
                                @php
                                    $start = max(1, $subsCurrent - 2);
                                    $end   = min($subsLast, $subsCurrent + 2);
                                @endphp
                                @if($start > 1)
                                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['subs_page' => 1]) }}">1</a></li>
                                    @if($start > 2)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
                                @endif
                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $i === $subsCurrent ? 'active' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['subs_page' => $i]) }}">{{ $i }}</a>
                                    </li>
                                @endfor
                                @if($end < $subsLast)
                                    @if($end < $subsLast - 1)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
                                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['subs_page' => $subsLast]) }}">{{ $subsLast }}</a></li>
                                @endif
                                <li class="page-item {{ $subsCurrent >= $subsLast ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['subs_page' => min($subsLast, $subsCurrent + 1)]) }}">Next ›</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- PERFORMANCE MODAL                                      --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @if(!empty($active))
        <div class="modal fade" id="perfModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-sm-down">
                <div class="modal-content">

                    {{-- Header --}}
                    <div class="modal-header border-0 pb-2">
                        <div class="w-100">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <h3 class="fw-bold mb-1">
                                        <i class="ki-duotone ki-chart-simple fs-2 text-primary me-2">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        Your Performance
                                    </h3>
                                    <div class="text-muted fs-7" id="perfSubtitle">
                                        Your activity on {{ $active['service_name'] }}
                                    </div>
                                </div>
                                <button type="button" class="btn-close flex-shrink-0 mt-1" data-bs-dismiss="modal"></button>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="modal-body pt-2" id="perfModalBody">
                        <div class="text-center py-15">
                            <div class="spinner-border text-primary mb-3"></div>
                            <div class="text-muted fs-7">Loading your activity…</div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer border-0 pt-0">
                        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                            <span class="text-muted fs-8" id="perfUpdatedAt"></span>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<style>
    /* Compact, responsive tweaks for the perf modal */
    @media (max-width: 575.98px) {
        .perf-metric-value { font-size: 1.5rem !important; }
        .perf-trend-bar-wrap { height: 80px !important; }
    }
    .perf-trend-bar-wrap {
        height: 100px;
        display: flex;
        align-items: flex-end;
    }
    .perf-trend-bar {
        min-height: 4px;
        border-radius: 4px 4px 0 0;
        transition: height .2s ease;
    }
</style>
<script>
(function () {
    'use strict';

    const STATS_URL = '{{ url("subscription/stats") }}';
    const openBtn   = document.getElementById('openPerfBtn');
    const modalEl   = document.getElementById('perfModal');
    const bodyEl    = document.getElementById('perfModalBody');
    const stampEl   = document.getElementById('perfUpdatedAt');
    const subtitle  = document.getElementById('perfSubtitle');

    if (!openBtn || !modalEl || !bodyEl) return;

    const modal = new bootstrap.Modal(modalEl);
    let cached  = null;
    let loading = false;

    // ── Open button: show modal, fetch stats lazily ──────────
    openBtn.addEventListener('click', async () => {
        modal.show();

        if (cached && !loading) {
            render(cached);
            return;
        }

        loading = true;
        bodyEl.innerHTML = `
            <div class="text-center py-15">
                <div class="spinner-border text-primary mb-3"></div>
                <div class="text-muted fs-7">Loading your activity…</div>
            </div>`;

        try {
            const res = await fetch(STATS_URL, {
                headers: { 'Accept': 'application/json' },
                cache:   'no-store',
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Failed to load');

            cached = data;
            render(data);

            if (stampEl) {
                stampEl.textContent = 'Updated ' + new Date(data.generated_at).toLocaleTimeString();
            }
        } catch (e) {
            console.error('[perf]', e);
            bodyEl.innerHTML = `
                <div class="alert alert-light-warning mb-0 fs-7">
                    Could not load your performance right now. Close this modal and try again.
                </div>`;
        } finally {
            loading = false;
        }
    });

    // ── Refresh cache when the modal is re-opened later ──────
    // (Optional: comment out to keep the first load forever)
    modalEl.addEventListener('hidden.bs.modal', () => {
        // cached stays so reopening is instant. Delete if you want fresh data each open.
        // cached = null;
    });

    // ─────────────────────────────────────────────────────────
    // Rendering
    // ─────────────────────────────────────────────────────────

    function render(data) {
        const aa       = data.auto_apply || {};
        const lifetime = aa.lifetime || {};
        const cv       = aa.cv_delivery || {};
        const windows  = aa.windows || {};
        const trend    = Array.isArray(aa.trend) ? aa.trend : [];
        const recent   = Array.isArray(aa.recent) ? aa.recent : [];
        const cvLog    = Array.isArray(cv.log) ? cv.log : [];

        if (subtitle && data.plan) {
            subtitle.textContent = `${data.plan.service_name} · ${data.plan.status_label}` +
                (data.plan.renewal_label ? ` · ${data.plan.renewal_label}` : '');
        }

        bodyEl.innerHTML = `
            ${renderMetrics(lifetime, windows, cv)}
            ${renderChannelSplit(lifetime)}
            ${renderTrend(trend)}
            ${renderRecent(recent)}
            ${cvLog.length ? renderCvLog(cvLog) : ''}
        `;
    }

    function renderMetrics(lifetime, windows, cv) {
        const cells = [
            {
                label: 'Total Applications',
                value: lifetime.total || 0,
                icon:  'ki-send',
                color: 'primary',
                hint:  'All time',
            },
            {
                label: 'Last 7 Days',
                value: windows.last_7 || 0,
                icon:  'ki-calendar-8',
                color: 'info',
                hint:  'Applications',
            },
            {
                label: 'Last 30 Days',
                value: windows.last_30 || 0,
                icon:  'ki-calendar',
                color: 'success',
                hint:  'Applications',
            },
            {
                label: 'CVs Delivered',
                value: cv.total || 0,
                icon:  'ki-document',
                color: 'warning',
                hint:  'Paid CV reviews sent',
            },
        ];

        return `
            <div class="row g-3 g-md-4 mb-5 mb-md-6">
                ${cells.map(c => `
                    <div class="col-6 col-md-3">
                        <div class="card card-flush h-100">
                            <div class="card-body py-3 py-md-4 px-3 px-md-4">
                                <span class="symbol symbol-35px mb-2">
                                    <span class="symbol-label bg-light-${c.color}">
                                        <i class="ki-duotone ${c.icon} fs-3 text-${c.color}">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                    </span>
                                </span>
                                <div class="fs-2 fw-bold text-gray-900 perf-metric-value">${c.value}</div>
                                <div class="text-muted fs-8 lh-1">${c.label}</div>
                                <div class="text-muted fs-9 mt-1 d-none d-md-block">${c.hint}</div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function renderChannelSplit(lifetime) {
        const cells = [
            { label: 'Email',    value: lifetime.email    || 0, icon: 'ki-sms',                color: 'primary' },
            { label: 'WhatsApp', value: lifetime.whatsapp || 0, icon: 'ki-whatsapp',           color: 'success' },
            { label: 'External', value: lifetime.external || 0, icon: 'ki-exit-right-corner',  color: 'info'    },
        ];

        return `
            <div class="row g-3 g-md-4 mb-5 mb-md-6">
                ${cells.map(c => `
                    <div class="col-4">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex flex-column flex-md-row align-items-center align-items-md-center gap-2 gap-md-3 py-3 py-md-4 px-2 px-md-4 text-center text-md-start">
                                <span class="symbol symbol-40px flex-shrink-0">
                                    <span class="symbol-label bg-light-${c.color}">
                                        <i class="ki-duotone ${c.icon} fs-2 text-${c.color}">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                    </span>
                                </span>
                                <div>
                                    <div class="fs-3 fw-bold text-gray-900">${c.value}</div>
                                    <div class="text-muted fs-8">via ${c.label}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function renderTrend(trend) {
        if (!trend.length) {
            return `
                <div class="card card-flush bg-light-secondary mb-5 mb-md-6">
                    <div class="card-body py-4">
                        <div class="fw-bold fs-7 mb-3">Monthly activity</div>
                        <div class="text-muted fs-7 text-center py-4">No activity in the last 6 months.</div>
                    </div>
                </div>
            `;
        }

        const maxCount = Math.max(1, ...trend.map(t => t.count || 0));

        return `
            <div class="card card-flush bg-light-secondary mb-5 mb-md-6">
                <div class="card-body py-3 py-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold fs-7">Monthly activity</div>
                        <div class="text-muted fs-8">Last 6 months</div>
                    </div>
                    <div class="d-flex align-items-end gap-2 gap-md-3" style="height:150px;">
                        ${trend.map(t => {
                            const h = Math.max(4, Math.round(((t.count || 0) / maxCount) * 100));
                            return `
                                <div class="d-flex flex-column align-items-center flex-fill">
                                    <div class="text-muted fs-9 mb-1">${t.count || 0}</div>
                                    <div class="bg-light-primary rounded-top w-100 perf-trend-bar-wrap">
                                        <div class="bg-primary perf-trend-bar w-100"
                                             style="height:${h}%"
                                             title="${t.count || 0} applications"></div>
                                    </div>
                                    <div class="text-muted fs-9 mt-2">${t.month}</div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            </div>
        `;
    }

    function renderRecent(recent) {
        const rows = recent.length
            ? recent.map(r => `
                <tr>
                    <td>
                        <div class="fw-semibold fs-7">${escape(r.job_title)}</div>
                        <div class="text-muted fs-8">${escape(r.company)}</div>
                    </td>
                    <td class="d-none d-sm-table-cell">
                        <span class="badge badge-light-secondary fs-8">${escape(r.source)}</span>
                    </td>
                    <td class="text-muted fs-8 text-end text-sm-start">
                        ${r.applied_at ? new Date(r.applied_at).toLocaleDateString() : '—'}
                    </td>
                </tr>
            `).join('')
            : `<tr><td colspan="3" class="text-center text-muted py-6 fs-7">No applications yet.</td></tr>`;

        return `
            <div class="card card-flush mb-5 mb-md-6">
                <div class="card-header align-items-center py-3 py-md-4">
                    <div class="fw-bold fs-7">Recent applications</div>
                    <div class="text-muted fs-8">Latest ${recent.length || 0}</div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle fs-7 mb-0">
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    function renderCvLog(cvLog) {
        const rows = cvLog.map(row => `
            <tr>
                <td>
                    <div class="fw-semibold fs-8">#${escape(String(row.reference).substring(0, 8))}</div>
                    <div class="text-muted fs-9">${escape(row.status || '—')}</div>
                </td>
                <td>
                    <span class="badge badge-light-info fs-9">${escape(row.channel)}</span>
                </td>
                <td class="text-muted fs-8 text-end text-sm-start">
                    ${row.delivered_at ? new Date(row.delivered_at).toLocaleDateString() : '—'}
                </td>
            </tr>
        `).join('');

        return `
            <div class="card card-flush">
                <div class="card-header align-items-center py-3 py-md-4">
                    <div class="fw-bold fs-7">Recent CV deliveries</div>
                    <div class="text-muted fs-8">${cvLog.length} record${cvLog.length === 1 ? '' : 's'}</div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle fs-7 mb-0">
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    function escape(s) {
        if (s === null || s === undefined) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
})();
</script>
@endpush