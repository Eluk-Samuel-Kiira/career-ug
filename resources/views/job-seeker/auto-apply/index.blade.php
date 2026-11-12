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

    {{-- ═══════════════════════════════════════════════════════════
         STATE 1: LOCKED (No active subscription)
         ═══════════════════════════════════════════════════════════ --}}
    @if(!$feature_enabled)
        <div class="card card-flush bg-light-primary border-primary border border-dashed overflow-hidden position-relative">
            <div class="card-body p-10 text-center position-relative z-index-1">
                <div class="symbol symbol-80px symbol-circle mb-5 mx-auto">
                    <div class="symbol-label bg-white shadow-sm">
                        <i class="ki-duotone ki-crown fs-3x text-warning">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </div>
                </div>

                <h2 class="fw-bolder text-gray-900 mb-3">Sit like a boss. Let AI do the work.</h2>
                <p class="fs-5 text-gray-600 mb-8 mx-auto" style="max-width: 600px;">
                    Stop hunting for jobs. Our AI Agent finds roles that match your CV, tailors your documents to the job description,
                    and submits them on your behalf — 24/7. <strong>You only show up for the interviews.</strong>
                </p>

                @if($subscriptionPrice)
                    <div class="d-flex justify-content-center align-items-center gap-4 mb-8">
                        <div class="text-end border-end border-gray-300 pe-4">
                            <div class="text-muted fs-7 text-uppercase fw-bold">Investment</div>
                            <div class="fs-2hx fw-bolder text-primary">{{ $subscriptionPrice['formatted'] }}</div>
                        </div>
                        <div class="text-start">
                            <div class="text-muted fs-7 text-uppercase fw-bold">Per</div>
                            <div class="fs-3 fw-bold text-gray-800">Month</div>
                        </div>
                    </div>

                    <button class="btn btn-primary btn-lg px-10 shadow-lg" id="upgradeAutoApplyBtn">
                        <i class="ki-duotone ki-rocket fs-2 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Activate My Agent
                    </button>
                    <div class="text-muted fs-8 mt-3">Cancel anytime. Instant activation.</div>
                @else
                    <div class="alert alert-warning">Pricing is not available in your region yet.</div>
                @endif
            </div>

            {{-- Decorative background --}}
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 10% 90%, rgba(32,170,62,0.05) 0%, transparent 40%);"></div>
        </div>

        {{-- Payment Modal --}}
        @if($subscriptionPrice)
            <div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <h3 class="fw-bold mb-0">Activate Auto Apply Agent</h3>
                            <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" id="paymentModalClose">
                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="bg-light-primary rounded p-4 mb-5 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted fs-8">Premium Subscription</div>
                                    <div class="fw-bold fs-5">Auto Apply Agent</div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted fs-8">Amount</div>
                                    <div class="fw-bold fs-4 text-primary" id="pm_amount">{{ $subscriptionPrice['formatted'] }}</div>
                                </div>
                            </div>

                            <label class="fw-semibold fs-7 mb-2">Pay with</label>
                            <div class="d-flex flex-column gap-2 mb-5">
                                <label class="d-flex align-items-center gap-3 p-3 border rounded cursor-pointer pm-method-option">
                                    <input type="radio" name="pm_method" value="mobile_money" class="form-check-input mt-0" checked>
                                    <i class="ki-duotone ki-phone fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold fs-7">Mobile Money</div>
                                        <div class="text-muted fs-8">MTN or Airtel — prompt on your phone</div>
                                    </div>
                                </label>
                                <label class="d-flex align-items-center gap-3 p-3 border rounded cursor-pointer pm-method-option">
                                    <input type="radio" name="pm_method" value="card" class="form-check-input mt-0">
                                    <i class="ki-duotone ki-credit-cart fs-2 text-success"><span class="path1"></span><span class="path2"></span></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold fs-7">Card</div>
                                        <div class="text-muted fs-8">Visa or Mastercard</div>
                                    </div>
                                </label>
                            </div>

                            <div id="pm_phone_wrap" class="mb-4">
                                <label class="fw-semibold fs-7 mb-2">Mobile Money Number</label>
                                <input type="tel" id="pm_phone" class="form-control form-control-lg" placeholder="+256 754 428 612">
                            </div>

                            <div id="pm_error" class="alert alert-light-danger mt-4 mb-0 d-none"></div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="pm_submit_btn" data-kt-indicator="off">
                                <span class="indicator-label">
                                    <i class="ki-duotone ki-lock fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Pay Now
                                </span>
                                <span class="indicator-progress">
                                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    {{-- ═══════════════════════════════════════════════════════════
         STATE 2: ACTIVE but no runs yet
         ═══════════════════════════════════════════════════════════ --}}
    @elseif(empty($runs))
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

    {{-- ═══════════════════════════════════════════════════════════
         STATE 3: ACTIVE and has runs
         ═══════════════════════════════════════════════════════════ --}}
    @else
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
    // ── Seeker documents drawer ──
    const seekerDrawer = new bootstrap.Offcanvas(document.getElementById('seekerDrawer'));

    window.openSeekerDrawer = function(relay) {
        document.getElementById('sdTitle').textContent = relay.job_title || 'Application Documents';
        document.getElementById('sdCompany').textContent = relay.company || '';

        document.getElementById('sdCvContent').innerHTML = relay.cv_html || '<p class="text-muted">No CV generated.</p>';
        document.getElementById('sdCoverContent').innerHTML = relay.cover_html || '<p class="text-muted">No cover letter generated.</p>';

        new bootstrap.Tab(document.querySelector('#sdTabs a[href="#sd_cv"]')).show();
        seekerDrawer.show();
    };

    // ── Payment modal (only mounted if $subscriptionPrice is set) ──
    document.addEventListener('DOMContentLoaded', function () {
        const upgradeBtn = document.getElementById('upgradeAutoApplyBtn');
        const modalEl    = document.getElementById('paymentModal');
        if (!upgradeBtn || !modalEl) return;

        const modal      = new bootstrap.Modal(modalEl);
        const submitBtn  = document.getElementById('pm_submit_btn');
        const phoneWrap  = document.getElementById('pm_phone_wrap');
        const phoneInput = document.getElementById('pm_phone');
        const errorEl    = document.getElementById('pm_error');
        const csrf       = document.querySelector('meta[name="csrf-token"]')?.content;

        upgradeBtn.addEventListener('click', () => modal.show());

        // Toggle phone input based on method
        modalEl.querySelectorAll('input[name="pm_method"]').forEach(radio => {
            radio.addEventListener('change', function () {
                phoneWrap.classList.toggle('d-none', this.value === 'card');
                errorEl.classList.add('d-none');
            });
        });

        submitBtn.addEventListener('click', async function () {
            const method = modalEl.querySelector('input[name="pm_method"]:checked')?.value || 'mobile_money';
            const phone  = (phoneInput?.value || '').trim();

            if (method === 'mobile_money' && !phone) {
                errorEl.textContent = 'Please enter your mobile money number.';
                errorEl.classList.remove('d-none');
                return;
            }

            this.disabled = true;
            this.setAttribute('data-kt-indicator', 'on');
            errorEl.classList.add('d-none');

            try {
                const res = await fetch('{{ route("auto-apply.subscribe") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ phone: method === 'mobile_money' ? phone : null, method }),
                });
                const data = await res.json();

                if (data.success) {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }

                    // ✅ PROMPT SENT: Start polling for success
                    this.setAttribute('data-kt-indicator', 'off');
                    this.disabled = false;
                    
                    // Change modal content to show "Waiting for PIN"
                    document.getElementById('pm_amount').textContent = "Check your phone";
                    errorEl.className = 'alert alert-light-primary mt-4 mb-0';
                    errorEl.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Please enter your PIN on your phone. Waiting for confirmation...';
                    errorEl.classList.remove('d-none');

                    // Hide the form fields
                    modalEl.querySelector('.modal-body').querySelectorAll('.mb-4, .mb-5').forEach(el => el.classList.add('d-none'));
                    submitBtn.classList.add('d-none'); // Hide the Pay button
                    modalEl.querySelector('.modal-footer .btn-light').classList.add('d-none'); // Hide Cancel

                    // Poll every 3 seconds
                    let attempts = 0;
                    const maxAttempts = 40; // 2 minutes max
                    
                    const poll = setInterval(async () => {
                        attempts++;
                        
                        try {
                            const statusRes = await fetch('{{ route("auto-apply.check-status") }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                                body: JSON.stringify({ reference: data.deposit_ref })
                            });
                            const statusData = await statusRes.json();

                            if (statusData.success && statusData.status === 'completed') {
                                clearInterval(poll);
                                errorEl.className = 'alert alert-success mt-4 mb-0';
                                errorEl.innerHTML = '✅ Payment confirmed! Activating your agent...';
                                setTimeout(() => window.location.reload(), 1500);
                                return;
                            }

                            if (statusData.status === 'failed') {
                                clearInterval(poll);
                                errorEl.className = 'alert alert-danger mt-4 mb-0';
                                errorEl.innerHTML = '❌ Payment failed or was cancelled. Please try again.';
                                setTimeout(() => window.location.reload(), 3000);
                                return;
                            }

                            // Update text with attempt count
                            errorEl.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Waiting for confirmation... (${attempts * 3}s)`;

                        } catch (e) {
                            // Network blip, keep trying
                        }

                        if (attempts >= maxAttempts) {
                            clearInterval(poll);
                            errorEl.className = 'alert alert-warning mt-4 mb-0';
                            errorEl.innerHTML = '⏱️ Timed out. If you paid, please refresh the page.';
                            setTimeout(() => window.location.reload(), 3000);
                        }
                    }, 3000);
                } else {
                    errorEl.textContent = data.message || 'Payment failed. Please try again.';
                    errorEl.classList.remove('d-none');
                    this.disabled = false;
                    this.setAttribute('data-kt-indicator', 'off');
                }
            } catch (e) {
                errorEl.textContent = 'Network error. Please check your connection.';
                errorEl.classList.remove('d-none');
                this.disabled = false;
                this.setAttribute('data-kt-indicator', 'off');
            }
        });
        
    });
</script>
@endpush