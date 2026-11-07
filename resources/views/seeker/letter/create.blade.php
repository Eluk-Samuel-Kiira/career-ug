@extends('layouts.admin')

@section('title', 'Generate Letter')
@section('page_title', 'Generate Cover / Application Letter')

@section('content')
@php
    // Fallback so this page renders even if the controller doesn't pass coverPrice
    $priceFormatted = $coverPrice['formatted']      ?? 'UGX 3,000';
    $priceCents     = $coverPrice['amount_cents']   ?? 3000;
    $priceCurrency  = $coverPrice['currency_code']  ?? 'UGX';
    $priceIsFree    = $coverPrice['is_free']        ?? false;
@endphp

@php
    $prefill       = $prefill ?? [];
    $prefillSource = in_array($prefill['job_source'] ?? null, ['db', 'manual', 'paste'], true)
                        ? $prefill['job_source']
                        : 'db';
@endphp

<div class="container py-4 py-md-6">
    <div class="card card-flush">
        <div class="card-body p-4 p-md-8">

            {{-- ── STEPPER ──────────────────────────────────────── --}}
            <div class="d-flex justify-content-between mb-6 mb-md-8">
                @foreach(['CV', 'Job', 'Type & Pay'] as $i => $label)
                    <div class="flex-grow-1 text-center step-indicator px-1" data-step="{{ $i + 1 }}">
                        <div class="step-circle mx-auto mb-2">{{ $i + 1 }}</div>
                        <div class="fs-8 fs-md-7 fw-semibold text-muted text-truncate">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            {{-- ── STEP 1: CV ───────────────────────────────────── --}}
            <div class="step-panel" data-step="1">
                <h3 class="fw-bold mb-3 mb-md-4 fs-4 fs-md-3">Choose the CV to use</h3>

                @if(!empty($cvFiles))
                    <div class="d-flex flex-column gap-2 gap-md-3 mb-3 mb-md-4">
                        @foreach($cvFiles as $i => $cv)
                            <label class="d-flex align-items-center gap-2 gap-md-3 p-3 p-md-4 bg-light rounded border border-gray-300 cursor-pointer">
                                <input type="radio" name="cv_choice" value="existing:{{ $cv['path'] }}"
                                       class="form-check-input flex-shrink-0 mt-0" {{ $i === 0 ? 'checked' : '' }}>
                                <i class="ki-duotone ki-file fs-2 text-primary flex-shrink-0 d-none d-sm-inline">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold fs-7 fs-md-6 text-truncate">{{ $cv['original_name'] ?? 'CV' }}</div>
                                    <div class="text-muted fs-8">
                                        @if(!empty($cv['size'])){{ round($cv['size'] / 1024, 2) }} KB @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach

                        <label class="d-flex align-items-center gap-2 gap-md-3 p-3 p-md-4 bg-light rounded border border-gray-300 cursor-pointer">
                            <input type="radio" name="cv_choice" value="uploaded" class="form-check-input flex-shrink-0 mt-0">
                            <i class="ki-duotone ki-folder-up fs-2 text-warning flex-shrink-0 d-none d-sm-inline">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold fs-7 fs-md-6">Upload a new CV for this letter only</div>
                                <div class="text-muted fs-8">PDF or Word, up to 5MB. Not saved to your profile.</div>
                            </div>
                        </label>
                    </div>
                @else
                    <div class="alert alert-light-info mb-3 mb-md-4 fs-7">
                        You have no CV on file. Upload one to continue.
                    </div>
                    <input type="radio" name="cv_choice" value="uploaded" class="form-check-input d-none" checked>
                @endif

                <div id="uploadBox" class="d-none mb-3 mb-md-4">
                    <input type="file" id="cv_file" name="cv_file"
                           class="form-control" accept=".pdf,.doc,.docx">
                    <div class="text-muted fs-8 fs-md-7 mt-2">Max 5MB. This CV is not stored on your profile.</div>
                </div>
            </div>

            {{-- ── STEP 2: JOB ──────────────────────────────────── --}}
            <div class="step-panel d-none" data-step="2">
                <h3 class="fw-bold mb-3 mb-md-4 fs-4 fs-md-3">Which job are you targeting?</h3>

                <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 gap-sm-4 mb-4 mb-md-6">
                    <label class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="job_source" value="db" checked>
                        <span class="form-check-label fs-7 fs-md-6">Search our jobs</span>
                    </label>
                    <label class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="job_source" value="manual">
                        <span class="form-check-label fs-7 fs-md-6">Enter title &amp; company</span>
                    </label>
                    <label class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="job_source" value="paste"
                            {{ $prefillSource === 'paste' ? 'checked' : '' }}>
                        <span class="form-check-label fs-7 fs-md-6">Paste full job description</span>
                    </label>
                </div>

                {{-- DB search --}}
                <div id="jobDbPanel">
                    <label class="fw-semibold fs-7 mb-2">Search job title or company</label>
                    <select id="job_search" class="form-select form-select-solid" style="width:100%"></select>
                    <input type="hidden" id="job_post_id" name="job_post_id">
                </div>

                {{-- Manual --}}
                <div id="jobManualPanel" class="d-none row g-3 g-md-4">
                    <div class="col-12 col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Job Title</label>
                        <input type="text" id="job_title_manual" name="job_title_manual" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Company Name</label>
                        <input type="text" id="company_name_manual" name="company_name_manual" class="form-control">
                    </div>
                </div>

                {{-- Paste --}}
                <div id="jobPastePanel" class="d-none row g-3 g-md-4">
                    <div class="col-12 col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Job Title</label>
                        <input type="text" id="job_title_paste" name="job_title_paste"
                            class="form-control"
                            value="{{ old('job_title_paste', $prefill['job_title'] ?? '') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Company Name</label>
                        <input type="text" id="company_name_paste" name="company_name_paste"
                            class="form-control"
                            value="{{ old('company_name_paste', $prefill['company_name'] ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="fw-semibold fs-7 mb-2">Full Job Description</label>
                        <textarea id="job_description_paste" name="job_description_paste" rows="6"
                            class="form-control">{{ old('job_description_paste', $prefill['job_description'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── STEP 3: TYPE & PAY ───────────────────────────── --}}
            <div class="step-panel d-none" data-step="3">
                <h3 class="fw-bold mb-3 mb-md-4 fs-4 fs-md-3">Letter type</h3>

                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-4 mb-4 mb-md-6">
                    <label class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="letter_type" value="cover" checked>
                        <span class="form-check-label fs-7 fs-md-6">Cover Letter</span>
                    </label>
                    <label class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="letter_type" value="application">
                        <span class="form-check-label fs-7 fs-md-6">Application Letter</span>
                    </label>
                </div>

                <div class="alert alert-light-primary d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 gap-sm-0 mb-3 mb-md-4">
                    <div class="min-w-0">
                        <div class="fw-bold fs-5 fs-md-4">One-time payment</div>
                        <div class="text-muted fs-7">{{ $priceFormatted }} per letter. This is not a subscription.</div>
                    </div>
                    <div class="fs-3 fs-md-2 fw-bold text-primary">{{ $priceFormatted }}</div>
                </div>

                <div class="alert alert-light-info fs-7 d-flex align-items-start">
                    <i class="ki-duotone ki-information-5 fs-3 text-info me-2 mt-1">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div>
                        <strong>How payment works.</strong>
                        Click Pay → enter the mobile money number you want to pay from (or use the
                        number on your profile) → approve the prompt on your phone.
                    </div>
                </div>
            </div>

            @if(!empty($inProgress))
                <div class="alert alert-light-info d-flex align-items-start mb-4 mb-md-6" id="inProgressBanner">
                    <i class="ki-duotone ki-information-5 fs-2x text-info me-3 mt-1 flex-shrink-0">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">
                            A letter is already being generated
                        </div>
                        <div class="fs-7 text-muted">
                            <strong>{{ $inProgress['job_title'] ?? 'Your letter' }}</strong>
                            @if(!empty($inProgress['company']))
                                at {{ $inProgress['company'] }}
                            @endif
                            — please be patient. You can track its progress on the letter page.
                        </div>
                        <div class="mt-2">
                            <a href="{{ url('letters/' . $inProgress['uuid']) }}"
                            class="btn btn-sm btn-primary">
                                <i class="ki-duotone ki-eye fs-4 me-1">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                                View progress
                            </a>
                            <a href="{{ route('letters.index') }}"
                            class="btn btn-sm btn-light">
                                My letters
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── NAVIGATION ───────────────────────────────────── --}}
            <div class="d-flex flex-column-reverse flex-sm-row justify-content-between gap-2 mt-6 mt-md-8 pt-4 pt-md-6 border-top">
                <button type="button" class="btn btn-light w-100 w-sm-auto" id="prevBtn" disabled>Back</button>
                <button type="button" class="btn btn-primary w-100 w-sm-auto" id="nextBtn">Next</button>
                <button type="button"
                        class="btn btn-success w-100 w-sm-auto {{ empty($inProgress) ? 'd-none' : '' }}"
                        id="submitBtn"
                        @if(!empty($inProgress)) disabled @endif
                        data-price-formatted="{{ $priceFormatted }}"
                        data-price-cents="{{ $priceCents }}"
                        data-price-currency="{{ $priceCurrency }}"
                        data-locked="{{ !empty($inProgress) ? '1' : '0' }}">
                    @if(!empty($inProgress))
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Generating your letter&hellip; please wait
                    @else
                        Pay {{ $priceFormatted }} &amp; Generate
                    @endif
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ── PAYMENT MODAL ─────────────────────────────────────────── --}}
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold mb-0 fs-4">Complete Payment</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary"
                        data-bs-dismiss="modal" id="paymentModalClose">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>

            <div class="modal-body">

                {{-- Amount summary --}}
                <div class="bg-light-primary rounded p-3 p-md-4 mb-4 mb-md-5 d-flex justify-content-between align-items-center gap-2">
                    <div class="min-w-0">
                        <div class="text-muted fs-8">You're paying for</div>
                        <div class="fw-bold fs-6 fs-md-5 text-truncate">Cover Letter</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="text-muted fs-8">Amount</div>
                        <div class="fw-bold fs-5 fs-md-4 text-primary" id="pm_amount">{{ $priceFormatted }}</div>
                    </div>
                </div>

                {{-- Method picker --}}
                <label class="fw-semibold fs-7 mb-2">Pay with</label>
                <div class="d-flex flex-column gap-2 mb-4 mb-md-5">
                    <label class="d-flex align-items-center gap-2 gap-md-3 p-3 border rounded cursor-pointer pm-method-option">
                        <input type="radio" name="pm_method" value="mobile_money" class="form-check-input mt-0 flex-shrink-0" checked>
                        <i class="ki-duotone ki-phone fs-2 text-primary flex-shrink-0">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold fs-7">Mobile Money</div>
                            <div class="text-muted fs-8">MTN or Airtel — you'll receive a prompt on your phone</div>
                        </div>
                    </label>

                    <label class="d-flex align-items-center gap-2 gap-md-3 p-3 border rounded cursor-pointer pm-method-option">
                        <input type="radio" name="pm_method" value="card" class="form-check-input mt-0 flex-shrink-0">
                        <i class="ki-duotone ki-credit-cart fs-2 text-success flex-shrink-0">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold fs-7">Card</div>
                            <div class="text-muted fs-8">Visa or Mastercard — redirect to a secure page</div>
                        </div>
                    </label>
                </div>

                {{-- Phone input (mobile money only) --}}
                <div id="pm_phone_wrap" class="mb-4">
                    <label class="fw-semibold fs-7 mb-2">Mobile Money Number</label>
                    <input type="tel"
                           id="pm_phone"
                           class="form-control form-control-lg"
                           placeholder="+256 754 428 612"
                           autocomplete="tel">
                    <div class="text-muted fs-8 mt-1">
                        Leave blank to use the number on your profile.
                    </div>
                </div>

                {{-- Credit / balance warning --}}
                <div class="alert alert-light-warning d-flex align-items-start mb-0" id="pm_warning">
                    <i class="ki-duotone ki-information-5 fs-3 text-warning me-2 mt-1 flex-shrink-0">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="fs-7" id="pm_warning_text">
                        <strong>Make sure you have enough mobile money credit.</strong>
                        Your balance must cover the amount above before you can approve the PIN prompt.
                        If the payment fails, contact support.
                    </div>
                </div>

                {{-- Error message --}}
                <div id="pm_error" class="alert alert-light-danger mt-4 mb-0 d-none"></div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="pm_submit_btn">
                    <span class="indicator-label">
                        <i class="ki-duotone ki-lock fs-3 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Pay Now
                    </span>
                    <span class="indicator-progress">
                        Processing... <span class="spinner-border spinner-border-sm ms-2"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .step-circle {
        width: 34px; height: 34px; line-height: 34px;
        border-radius: 50%; background: #e4e6ef;
        color: #7e8299; font-weight: 700; text-align: center;
        font-size: 0.85rem;
    }
    @media (min-width: 768px) {
        .step-circle { width: 40px; height: 40px; line-height: 40px; font-size: 1rem; }
    }
    .step-indicator.active .step-circle { background: #009ef7; color: #fff; }
    .step-indicator.done   .step-circle { background: #50cd89; color: #fff; }
    .cursor-pointer { cursor: pointer; }

    @media (max-width: 575.98px) {
        .w-sm-auto { width: 100% !important; }
    }
    @media (min-width: 576px) {
        .w-sm-auto { width: auto !important; }
    }

    /* Select2 — match Metronic form-select-solid */
    #job_search + .select2-container .select2-selection--single {
        display: flex; align-items: center; height: 44px;
        padding: 0 40px 0 12px;
        border: 1px solid var(--bs-gray-300);
        border-radius: 0.475rem;
        background-color: var(--bs-gray-100);
        transition: border-color .15s ease, background-color .15s ease;
    }
    #job_search + .select2-container .select2-selection--single:hover,
    #job_search + .select2-container.select2-container--focus .select2-selection--single {
        border-color: var(--bs-primary); background-color: var(--bs-white);
    }
    #job_search + .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: normal !important; padding: 0 !important; margin: 0 !important;
        color: var(--bs-gray-800); font-size: 0.95rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;
    }
    #job_search + .select2-container .select2-selection--single .select2-selection__placeholder {
        color: var(--bs-gray-500); line-height: normal !important;
    }
    #job_search + .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100% !important; top: 0; right: 8px;
        display: flex; align-items: center; justify-content: center; width: 24px;
    }
    #job_search + .select2-container.select2-container--focus .select2-selection--single .select2-selection__arrow b,
    #job_search + .select2-container .select2-selection--single:hover .select2-selection__arrow b {
        border-color: var(--bs-primary) transparent transparent transparent;
    }

    /* Modal method option styling */
    .pm-method-option { transition: border-color .15s ease, background .15s ease; }
    .pm-method-option:hover { border-color: var(--bs-primary) !important; background: var(--bs-light-primary); }
    .pm-method-option:has(input:checked) {
        border-color: var(--bs-primary) !important; background: var(--bs-light-primary);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    'use strict';

    const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const PRICE_CENTS   = {{ (int) $priceCents }};
    const PRICE_FORMAT  = @json($priceFormatted);
    const PRICE_IS_FREE = {{ $priceIsFree ? 'true' : 'false' }};

    const state = {
        step: 1,
        cvSource: null, cvPath: null, cvFile: null,
        jobSource: null, jobPostId: null,
        jobTitle: '', companyName: '', jobDescription: '',
        letterType: 'cover',
        createdUuid: null,   // ← set after letters.store succeeds
    };

    const els = {
        panels:     document.querySelectorAll('.step-panel'),
        indicators: document.querySelectorAll('.step-indicator'),
        prevBtn:    document.getElementById('prevBtn'),
        nextBtn:    document.getElementById('nextBtn'),
        submitBtn:  document.getElementById('submitBtn'),
    };

    function render() {
        els.panels.forEach(p => p.classList.toggle('d-none', Number(p.dataset.step) !== state.step));
        els.indicators.forEach(i => {
            const s = Number(i.dataset.step);
            i.classList.toggle('active', s === state.step);
            i.classList.toggle('done', s < state.step);
        });
        els.prevBtn.disabled = state.step === 1;
        els.nextBtn.classList.toggle('d-none', state.step === 3);
        els.submitBtn.classList.toggle('d-none', state.step !== 3);
    }

    // ── Step 1 ────────────────────────────────────────────────
    function bindCvStep() {
        document.querySelectorAll('input[name="cv_choice"]').forEach(r => {
            r.addEventListener('change', () => {
                state.cvSource = r.value === 'uploaded' ? 'uploaded' : 'existing';
                state.cvPath   = r.value.startsWith('existing:') ? r.value.slice('existing:'.length) : null;
                document.getElementById('uploadBox').classList.toggle('d-none', state.cvSource !== 'uploaded');
                if (state.cvSource === 'existing') {
                    state.cvFile = null;
                    const fi = document.getElementById('cv_file');
                    if (fi) fi.value = '';
                }
            });
        });

        document.getElementById('cv_file')?.addEventListener('change', function () {
            state.cvFile = this.files?.[0] || null;
        });

        const checked = document.querySelector('input[name="cv_choice"]:checked');
        if (checked) checked.dispatchEvent(new Event('change'));
    }

    function validateStep1() {
        if (!state.cvSource) { toast('error', 'Please choose which CV to use.'); return false; }
        if (state.cvSource === 'existing') {
            if (!state.cvPath) { toast('error', 'Please choose one of your existing CVs.'); return false; }
            return true;
        }
        if (!state.cvFile) { toast('error', 'Please choose a file to upload.'); return false; }
        if (state.cvFile.size > 5 * 1024 * 1024) { toast('error', 'The CV must be under 5MB.'); return false; }
        const ext = state.cvFile.name.split('.').pop().toLowerCase();
        if (!['pdf', 'doc', 'docx'].includes(ext)) {
            toast('error', 'Only PDF, DOC, or DOCX files are accepted.');
            return false;
        }
        return true;
    }

    // ── Step 2 ────────────────────────────────────────────────
    function bindJobStep() {
        // ── Hydrate state from the DOM ────────────────────────────
        function syncJobStateFromDom() {
            const src = state.jobSource || 'db';

            if (src === 'manual') {
                state.jobTitle    = (document.getElementById('job_title_manual')?.value    || '').trim();
                state.companyName = (document.getElementById('company_name_manual')?.value || '').trim();
                state.jobDescription = '';
            } else if (src === 'paste') {
                state.jobTitle    = (document.getElementById('job_title_paste')?.value    || '').trim();
                state.companyName = (document.getElementById('company_name_paste')?.value || '').trim();
                state.jobDescription = (document.getElementById('job_description_paste')?.value || '').trim();
            } else if (src === 'db') {
                state.jobPostId = document.getElementById('job_post_id')?.value || null;
            }
        }

        // ── Radio switching ──────────────────────────────────────
        document.querySelectorAll('input[name="job_source"]').forEach(r => {
            r.addEventListener('change', () => {
                state.jobSource = r.value;
                document.getElementById('jobDbPanel').classList.toggle('d-none',     r.value !== 'db');
                document.getElementById('jobManualPanel').classList.toggle('d-none', r.value !== 'manual');
                document.getElementById('jobPastePanel').classList.toggle('d-none',  r.value !== 'paste');

                syncJobStateFromDom();
            });
        });

        // ── Apply server prefill on load ─────────────────────────
        const serverPrefillSource = @json($prefillSource);

        document.querySelectorAll('input[name="job_source"]').forEach(r => {
            r.checked = (r.value === serverPrefillSource);
        });

        document.getElementById('jobDbPanel')?.classList.toggle('d-none',     serverPrefillSource !== 'db');
        document.getElementById('jobManualPanel')?.classList.toggle('d-none', serverPrefillSource !== 'manual');
        document.getElementById('jobPastePanel')?.classList.toggle('d-none',  serverPrefillSource !== 'paste');

        state.jobSource = serverPrefillSource;

        // ── Select2 for job search ───────────────────────────────
        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#job_search').select2({
                placeholder: 'Type at least 2 characters...',
                minimumInputLength: 2,
                width: '100%',
                ajax: {
                    url: '{{ route("letters.search-jobs") }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({
                        results: (data.data || []).map(j => ({ id: j.id, text: j.label })),
                    }),
                },
            }).on('select2:select', e => {
                state.jobPostId = e.params.data.id;
                document.getElementById('job_post_id').value = e.params.data.id;
            }).on('select2:clear', () => {
                state.jobPostId = null;
                document.getElementById('job_post_id').value = '';
            });
        }

        // ── Input → state ────────────────────────────────────────
        ['job_title_manual', 'company_name_manual',
        'job_title_paste', 'company_name_paste', 'job_description_paste'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', function () {
                if (id === 'job_title_manual')       state.jobTitle      = this.value.trim();
                if (id === 'company_name_manual')    state.companyName   = this.value.trim();
                if (id === 'job_title_paste')        state.jobTitle      = this.value.trim();
                if (id === 'company_name_paste')     state.companyName   = this.value.trim();
                if (id === 'job_description_paste')  state.jobDescription= this.value.trim();
            });
        });

        // ── Hydrate once on load (pre-filled + autofilled values) ─
        syncJobStateFromDom();
    }

    function validateStep2() {
        if (!state.jobSource) { toast('error', 'Please choose how you want to identify the job.'); return false; }
        if (state.jobSource === 'db') {
            if (!state.jobPostId) { toast('error', 'Please pick a job from the list.'); return false; }
            return true;
        }
        if (state.jobSource === 'manual') {
            if (!state.jobTitle || !state.companyName) {
                toast('error', 'Please enter both the job title and the company name.');
                return false;
            }
            return true;
        }
        if (state.jobSource === 'paste') {
            if (!state.jobTitle || !state.companyName || !state.jobDescription) {
                toast('error', 'Please fill in the job title, company, and job description.');
                return false;
            }
            return true;
        }
        return true;
    }

    // ── Step 3 ────────────────────────────────────────────────
    function bindLetterType() {
        document.querySelectorAll('input[name="letter_type"]').forEach(r => {
            r.addEventListener('change', () => { state.letterType = r.value; });
        });
    }

    // ── Navigation ────────────────────────────────────────────
    function goNext() {
        const ok = state.step === 1 ? validateStep1()
                 : state.step === 2 ? validateStep2()
                 : true;
        if (!ok) return;
        if (state.step < 3) { state.step++; render(); }
    }

    function goPrev() {
        if (state.step > 1) { state.step--; render(); }
    }

    // ── Create letter request (no payment yet) ────────────────
    async function createRequest() {
        if (!validateStep1() || !validateStep2()) return null;

        const fd = new FormData();
        fd.append('letter_type', state.letterType);

        if (state.cvSource === 'existing') {
            fd.append('cv_source', 'existing');
            fd.append('cv_path', state.cvPath);
        } else {
            fd.append('cv_source', 'uploaded');
            fd.append('cv_file', state.cvFile);
        }

        fd.append('job_source', state.jobSource);
        if (state.jobSource === 'db') {
            fd.append('job_post_id', state.jobPostId);
        } else {
            fd.append('job_title', state.jobTitle);
            fd.append('company_name', state.companyName);
            if (state.jobSource === 'paste') {
                fd.append('job_description', state.jobDescription);
            }
        }

        const res  = await fetch('{{ route("letters.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd,
        });
        const body = await res.json();

        if (!res.ok || !body.success) {
            throw new Error(body.message || 'Could not create the letter request.');
        }
        return body.data.uuid;
    }

    // ── Open payment modal ────────────────────────────────────
    function openPaymentModal(uuid) {
        state.createdUuid = uuid;
        const modalEl = document.getElementById('paymentModal');
        if (!modalEl) return;

        // Reset phone field every time
        const phone = document.getElementById('pm_phone');
        if (phone) phone.value = '';

        // Reset method to mobile money
        const mobile = modalEl.querySelector('input[name="pm_method"][value="mobile_money"]');
        if (mobile) mobile.checked = true;

        new bootstrap.Modal(modalEl).show();
    }

    // ── Submit flow: create request → open modal ──────────────
    async function submit() {
        // Hard guard — never allow a second submission
        if (els.submitBtn.disabled || els.submitBtn.dataset.locked === '1') {
            toast('error', 'A letter is already being generated. Please wait or view its progress.');
            return;
        }

        if (!validateStep1() || !validateStep2()) return;

        if (state.createdUuid) {
            openPaymentModal(state.createdUuid);
            return;
        }

        els.submitBtn.disabled = true;
        els.submitBtn.dataset.locked = '1';   // ← permanent lock
        els.submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating request...';

        try {
            const uuid = await createRequest();
            openPaymentModal(uuid);
        } catch (e) {
            toast('error', e.message);
            // Only release the lock if creation actually failed (nothing was created)
            els.submitBtn.disabled = false;
            els.submitBtn.dataset.locked = '0';
            els.submitBtn.innerHTML = 'Pay ' + PRICE_FORMAT + ' &amp; Generate';
        }
    }

    function restoreSubmitButton() {
        els.submitBtn.disabled = false;
        els.submitBtn.innerHTML = 'Pay ' + PRICE_FORMAT + ' &amp; Generate';
    }

    // ── Payment modal handlers ────────────────────────────────
    function bindPaymentModal() {
        const modalEl   = document.getElementById('paymentModal');
        if (!modalEl) return;

        const phoneWrap = document.getElementById('pm_phone_wrap');
        const phoneIn   = document.getElementById('pm_phone');
        const submitBtn = document.getElementById('pm_submit_btn');
        const errorEl   = document.getElementById('pm_error');
        const warning   = document.getElementById('pm_warning_text');

        function syncMethod() {
            const method = modalEl.querySelector('input[name="pm_method"]:checked')?.value || 'mobile_money';
            phoneWrap.classList.toggle('d-none', method === 'card');
            errorEl.classList.add('d-none');
            errorEl.textContent = '';

            if (method === 'card') {
                warning.innerHTML = '<strong>Have your card details ready.</strong> ' +
                    'You will be redirected to a secure payment page to complete the transaction.';
            } else {
                warning.innerHTML = '<strong>Make sure you have enough mobile money credit.</strong> ' +
                    'Your balance must cover the amount above before you can approve the PIN prompt. ' +
                    'If the payment fails, contact support.';
            }
        }

        modalEl.querySelectorAll('input[name="pm_method"]').forEach(r => {
            r.addEventListener('change', syncMethod);
        });

        modalEl.addEventListener('shown.bs.modal', () => {
            syncMethod();
            setTimeout(() => {
                if (!phoneWrap.classList.contains('d-none')) phoneIn.focus();
            }, 150);
        });

        submitBtn.addEventListener('click', async () => {
            if (!state.createdUuid) {
                showError('Missing request reference. Please try again.');
                return;
            }

            const method = modalEl.querySelector('input[name="pm_method"]:checked')?.value || 'mobile_money';
            const phone  = (phoneIn.value || '').replace(/[^\d+]/g, '').trim();

            if (method === 'mobile_money' && phone && !/^\+\d{9,15}$/.test(phone)) {
                showError('That phone number does not look right. Use the format +256 754 428 612.');
                return;
            }

            submitBtn.setAttribute('data-kt-indicator', 'on');
            submitBtn.disabled = true;
            errorEl.classList.add('d-none');

            try {
                const res = await fetch(`{{ url('letters') }}/${state.createdUuid}/pay`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        phone:  method === 'mobile_money' ? (phone || null) : null,
                        method,
                    }),
                });
                const data = await res.json();

                if (!data.success) {
                    showError(data.message || 'Payment could not be started.');
                    submitBtn.removeAttribute('data-kt-indicator');
                    submitBtn.disabled = false;
                    return;
                }

                if (data.amount_formatted) {
                    document.getElementById('pm_amount').textContent = data.amount_formatted;
                }

                // ── Free path ─────────────────────────────────────
                if (data.is_free) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                    toast('success', data.message || 'Letter is being generated.');
                    window.location.href = `{{ url('letters') }}/${state.createdUuid}`;
                    return;
                }

                // ── Card path: gateway gives us a redirect URL ────
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                    return;
                }

                // ── Mobile money: prompt sent, poll for confirmation ──
                showInfo(
                    '<strong>A payment prompt has been sent to your phone.</strong> ' +
                    'Enter your PIN to complete payment. Waiting for confirmation...'
                );

                // ── Lock the outer submit button too ──
                if (els.submitBtn) {
                    els.submitBtn.disabled = true;
                    els.submitBtn.dataset.locked = '1';
                    els.submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>' +
                        'Generating your letter&hellip; please wait';
                }

                submitBtn.removeAttribute('data-kt-indicator');
                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>Waiting for payment...';

                const uuid    = state.createdUuid;
                const POLL_MS = 3000;
                const MAX_MS  = 5 * 60 * 1000;
                const started = Date.now();

                const goToLetter = (reason) => {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                    if (reason === 'timeout') {
                        window.location.href = `{{ route('letters.index') }}?payment=pending&uuid=${uuid}`;
                    } else {
                        window.location.href = `{{ url('letters') }}/${uuid}`;
                    }
                };

                const poll = setInterval(async () => {
                    if (Date.now() - started > MAX_MS) {
                        clearInterval(poll);
                        goToLetter('timeout');
                        return;
                    }

                    try {
                        const r = await fetch(`{{ url('letters') }}/${uuid}/status`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const b = await r.json();
                        const status = b?.data?.status;

                        if (!status) return;
                        if (status === 'pending_payment') return;

                        clearInterval(poll);

                        if (status === 'failed') {
                            if (els.submitBtn) {
                                els.submitBtn.disabled = false;
                                els.submitBtn.dataset.locked = '0';
                            }
                            showError(
                                '<strong>Payment succeeded but generation failed.</strong> ' +
                                'Opening the letter so you can retry.'
                            );
                            setTimeout(() => goToLetter('terminal'), 1500);
                            return;
                        }

                        showInfo(
                            '<strong>Payment confirmed!</strong> ' +
                            (status === 'generated'
                                ? 'Your letter is ready. Opening it now...'
                                : 'Generating your letter. Opening it now...')
                        );
                        setTimeout(() => goToLetter('terminal'), 800);

                    } catch (e) {
                        // network blip — keep polling
                    }
                }, POLL_MS);

            } catch (e) {                                       // ← ONE catch only
                showError('Something went wrong: ' + e.message);
                submitBtn.removeAttribute('data-kt-indicator');
                submitBtn.disabled = false;
            }
        });

        document.getElementById('paymentModalClose')?.addEventListener('click', () => {
            submitBtn.removeAttribute('data-kt-indicator');
            submitBtn.disabled = false;
            errorEl.classList.add('d-none');
            errorEl.textContent = '';
        });

        function showError(msg) {
            errorEl.className = 'alert alert-light-danger mt-4 mb-0';
            errorEl.textContent = msg;
            errorEl.classList.remove('d-none');
        }

        function showInfo(html) {
            errorEl.className = 'alert alert-light-info mt-4 mb-0';
            errorEl.innerHTML = html;
            errorEl.classList.remove('d-none');
        }
    }

    function toast(type, msg) {
        if (typeof window.showToast === 'function') {
            window.showToast(type, msg);
        } else {
            alert(msg);
        }
    }

    // ── Boot ──────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        bindCvStep();
        bindJobStep();
        bindLetterType();
        bindPaymentModal();

        els.prevBtn?.addEventListener('click', goPrev);
        els.nextBtn?.addEventListener('click', goNext);
        els.submitBtn?.addEventListener('click', submit);

        render();
    });
})();
</script>
@endpush