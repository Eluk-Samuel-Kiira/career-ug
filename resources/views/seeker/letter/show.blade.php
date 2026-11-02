@extends('layouts.admin')

@section('title', $letter['letter_type_label'])
@section('page_title', $letter['letter_type_label'])

@section('content')
@php
    $payAmountFormatted = $letter['price_label']
        ?? (($letter['currency'] ?? 'UGX') . ' ' . number_format($letter['price'] ?? 3000));

    $attempts      = $letter['regeneration_count'] ?? 0;
    $maxAttempts   = $letter['max_regenerations']  ?? 2;
    $canRegenerate = $letter['status'] === 'failed'
        ? ($attempts < $maxAttempts)
        : ($letter['status'] === 'generated' && $attempts < $maxAttempts);
    $canEdit       = $letter['status'] === 'failed' && !$canRegenerate;
@endphp

<div class="container py-4 py-md-6">
    <div class="card card-flush">

        {{-- ── HEADER ───────────────────────────────────────────── --}}
        <div class="card-header border-0 pt-4 pt-md-5 pb-3 pb-md-4">
            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 gap-sm-3 w-100">

                {{-- Title block --}}
                <div class="flex-grow-1 min-w-0" style="min-width: 0;">
                    <h3 class="fw-bold mb-1 mb-sm-0 fs-4 fs-md-3 text-truncate">
                        {{ $letter['letter_type_label'] }}
                    </h3>
                    <div class="text-muted fs-7 text-truncate">
                        {{ $letter['job_title'] }} — {{ $letter['company_name'] }}
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex flex-column flex-sm-row gap-2 flex-shrink-0">
                    @if($letter['status'] === 'generated')
                        <a href="{{ route('letters.download', $letter['uuid']) }}"
                        class="btn btn-sm btn-primary">
                            <i class="ki-duotone ki-file-down fs-3 me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Download PDF
                        </a>
                    @endif
                    <a href="{{ route('letters.index') }}" class="btn btn-sm btn-light">
                        Back
                    </a>
                </div>

            </div>
        </div>

        <div class="card-body pt-0">

            {{-- STATUS BADGES --}}
            <div class="d-flex flex-wrap gap-2 mb-4 mb-md-6">
                {!! $letter['status_badge'] !!}
                @if($letter['is_paid'])
                    <span class="badge badge-light-success">Paid</span>
                @endif
                @if($letter['status'] === 'failed' && $attempts > 0)
                    <span class="badge badge-light-danger">Attempt {{ $attempts }} of {{ $maxAttempts }}</span>
                @endif
                @if(!empty($letter['is_user_edited']))
                    <span class="badge badge-light-primary">
                        <i class="ki-duotone ki-pencil fs-6 me-1">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        Edited by you
                        @if(!empty($letter['user_edited_at']))
                            &middot; {{ \Carbon\Carbon::parse($letter['user_edited_at'])->diffForHumans() }}
                        @endif
                    </span>
                @endif
            </div>

            {{-- ── PAY CTA ──────────────────────────────────────── --}}
            @if($letter['status'] === 'pending_payment')
                <div class="alert alert-light-warning d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3">
                    <div class="min-w-0">
                        <div class="fw-bold fs-7 fs-md-6">Complete your payment to generate the letter</div>
                        <div class="text-muted fs-8 fs-md-7">{{ $payAmountFormatted }} one-time. Not a subscription.</div>
                    </div>
                    <button type="button" class="btn btn-warning w-100 w-sm-auto" id="openPayBtn">
                        <i class="ki-duotone ki-dollar fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Pay {{ $payAmountFormatted }}
                    </button>
                </div>
            @endif

            {{-- ── PAID / PROCESSING — no content yet ────────────── --}}
            @if(in_array($letter['status'], ['paid', 'processing'], true) && empty($letter['content']))
                @php
                    $paidAgo = !empty($letter['paid_at'])
                        ? \Carbon\Carbon::parse($letter['paid_at'])->diffInSeconds(now())
                        : 0;
                    $showGenerateBtn = $paidAgo > 60;   // only after 60s of no result
                @endphp

                <div class="alert alert-light-info d-flex align-items-start mb-4">
                    <div class="spinner-border spinner-border-sm text-info me-3 mt-1 flex-shrink-0"></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Generating your letter&hellip;</div>
                        <div class="fs-7 text-muted">
                            Payment received. This usually takes 30&ndash;60 seconds.
                            This page will update automatically &mdash; no need to refresh.
                        </div>
                    </div>
                </div>

                @if($showGenerateBtn)
                    <div class="alert alert-light-warning d-flex align-items-start mb-4">
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-7">Taking longer than expected</div>
                            <div class="fs-8 text-muted">
                                If you've been waiting more than a minute, you can trigger a retry below.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                        <button type="button" class="btn btn-primary w-100 w-sm-auto" id="generateNowBtn" disabled>
                            <span class="indicator-label">
                                <i class="ki-duotone ki-rocket fs-4 me-1">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                Retry Generation
                            </span>
                            <span class="indicator-progress">
                                Generating...
                                <span class="spinner-border spinner-border-sm ms-2"></span>
                            </span>
                        </button>
                        <a href="{{ route('letters.index') }}" class="btn btn-light w-100 w-sm-auto">Back to Letters</a>
                    </div>
                @else
                    <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                        <a href="{{ route('letters.index') }}" class="btn btn-light w-100 w-sm-auto">Back to Letters</a>
                    </div>
                @endif
            @endif

            {{-- ── FAILED ───────────────────────────────────────── --}}
            @if($letter['status'] === 'failed')
                <div class="alert alert-danger d-flex align-items-start mb-4">
                    <i class="ki-duotone ki-information-5 fs-2x text-danger me-3 mt-1 flex-shrink-0">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="flex-grow-1">
                        <div class="fw-bold mb-1">Generation failed</div>
                        <div class="fs-7 text-break">
                            {{ $letter['error_message'] ?? 'Unknown error. Please try again.' }}
                        </div>
                        @if($canRegenerate)
                            <div class="text-muted fs-8 mt-2">
                                You can retry {{ $maxAttempts - $attempts }} more
                                time{{ ($maxAttempts - $attempts) === 1 ? '' : 's' }}.
                            </div>
                        @else
                            <div class="text-muted fs-8 mt-2">
                                You have used all {{ $maxAttempts }} retries. Please upload a different CV or update the job details below.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                    @if($canRegenerate)
                        <button type="button" class="btn btn-warning w-100 w-sm-auto" id="regenerateBtn">
                            <span class="indicator-label">
                                <i class="ki-duotone ki-arrows-circle fs-4 me-1">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                Try Again
                            </span>
                            <span class="indicator-progress">
                                Retrying...
                                <span class="spinner-border spinner-border-sm ms-2"></span>
                            </span>
                        </button>
                    @else
                        <button type="button" class="btn btn-primary w-100 w-sm-auto" id="toggleEditBtn">
                            <i class="ki-duotone ki-pencil fs-4 me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Change CV / Job Details
                        </button>
                    @endif
                    <a href="{{ route('letters.index') }}" class="btn btn-light w-100 w-sm-auto">Back to Letters</a>
                </div>

                @if($canEdit)
                <div id="editForm" class="border rounded p-3 p-md-4 bg-light">
                    <h5 class="fw-bold mb-3 fs-6">Update the letter inputs</h5>
                    <form id="updateLetterForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="fw-semibold fs-7 mb-2">Job Title</label>
                            <input type="text" name="job_title" class="form-control"
                                value="{{ $letter['job_title'] }}">
                        </div>
                        <div class="mb-3">
                            <label class="fw-semibold fs-7 mb-2">Company</label>
                            <input type="text" name="company_name" class="form-control"
                                value="{{ $letter['company_name'] }}">
                        </div>
                        <div class="mb-3">
                            <label class="fw-semibold fs-7 mb-2">Upload a different CV (PDF/DOC/DOCX, max 5MB)</label>
                            <input type="file" name="cv_file" accept=".pdf,.doc,.docx" class="form-control">
                            <div class="text-muted fs-8 mt-1">Leave blank to keep the current CV.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 w-sm-auto" id="updateBtn">
                            <span class="indicator-label">Save &amp; Retry</span>
                            <span class="indicator-progress">
                                Saving... <span class="spinner-border spinner-border-sm ms-2"></span>
                            </span>
                        </button>
                        <div id="updateError" class="alert alert-light-danger mt-3 mb-0 d-none" role="alert"></div>
                    </form>
                </div>
                @endif
            @endif

            {{-- ── GENERATED ─────────────────────────────────────── --}}
            @if($letter['status'] === 'generated' && !empty($letter['content']))
                {{-- View mode --}}
                <div id="letterView">
                    <div class="border rounded p-3 p-md-8 bg-white letter-preview" id="letterContent">
                        {!! $letter['content'] !!}
                    </div>

                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 mt-4">
                        <button type="button" class="btn btn-light-primary w-100 w-sm-auto" id="editLetterBtn">
                            <i class="ki-duotone ki-pencil fs-4 me-1">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            Edit Letter
                        </button>

                        <button type="button" class="btn btn-light w-100 w-sm-auto" id="copyLetterBtn">
                            <i class="ki-duotone ki-copy fs-4 me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Copy Text
                        </button>

                        @if($canRegenerate)
                            <button type="button" class="btn btn-light-warning w-100 w-sm-auto" id="regenerateBtn">
                                <span class="indicator-label">
                                    <i class="ki-duotone ki-arrows-circle fs-4 me-1">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    Regenerate with AI ({{ $maxAttempts - $attempts }} left)
                                </span>
                                <span class="indicator-progress">
                                    Regenerating...
                                    <span class="spinner-border spinner-border-sm ms-2"></span>
                                </span>
                            </button>
                        @endif
                    </div>

                    @if(!$canRegenerate)
                        <div class="alert alert-light-info mt-4 fs-7 mb-0">
                            You've reached the regeneration limit ({{ $maxAttempts }}). You can still edit the letter manually.
                        </div>
                    @endif
                </div>

                {{-- Edit mode (hidden until clicked) --}}
                <div id="letterEdit" class="d-none">
                    <div class="alert alert-light-info d-flex align-items-start mb-3 py-2">
                        <i class="ki-duotone ki-information-5 fs-4 text-info me-2 mt-1 flex-shrink-0">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="fs-8 text-muted">
                            Edit the letter as you like. Your saved version replaces the AI version — the PDF download uses your text.
                        </div>
                    </div>

                    <div class="border rounded bg-white p-2">
                        <textarea id="letterEditor" class="form-control" rows="22"></textarea>
                    </div>

                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 mt-3">
                        <button type="button" class="btn btn-primary w-100 w-sm-auto" id="saveEditsBtn">
                            <span class="indicator-label">
                                <i class="ki-duotone ki-check-circle fs-4 me-1">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                Save Changes
                            </span>
                            <span class="indicator-progress">
                                Saving...
                                <span class="spinner-border spinner-border-sm ms-2"></span>
                            </span>
                        </button>

                        <button type="button" class="btn btn-light w-100 w-sm-auto" id="cancelEditsBtn">Cancel</button>

                        @if(!empty($letter['has_original']))
                            <button type="button" class="btn btn-light-danger w-100 w-sm-auto" id="resetToAiBtn">
                                <i class="ki-duotone ki-arrows-circle fs-4 me-1">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                Reset to AI version
                            </button>
                        @endif
                    </div>
                </div>
            @endif

        </div>

    </div>
</div>


{{-- Confirm action modal --}}
<div class="modal fade" id="confirmActionModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold mb-0 fs-5" id="confirmActionTitle">Confirm Action</h3>
                <button type="button" class="btn btn-icon btn-sm" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>
            <div class="modal-body pt-2 pb-4">
                <p class="text-gray-700 mb-0" id="confirmActionMessage">Are you sure?</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="confirmActionCancel">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmActionOk">
                    <span class="indicator-label">Confirm</span>
                    <span class="indicator-progress">
                        Please wait... <span class="spinner-border spinner-border-sm ms-2"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>


@include('seeker.letter._payment_modal', ['payAmountFormatted' => $payAmountFormatted, 'letter' => $letter])
@endsection


@push('styles')
{{-- TinyMCE styles get injected automatically by the script --}}
<style>
    @media (max-width: 575.98px) {
        .w-sm-auto { width: 100% !important; }
    }
    @media (min-width: 576px) {
        .w-sm-auto { width: auto !important; }
    }

    .letter-preview {
        max-width: 800px;
        margin: 0 auto;
        font-size: 0.9rem;
        line-height: 1.55;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    @media (min-width: 768px) {
        .letter-preview { font-size: 1rem; line-height: 1.6; }
    }
    .letter-preview img,
    .letter-preview table,
    .letter-preview pre,
    .letter-preview iframe,
    .letter-preview video {
        max-width: 100% !important;
        height: auto;
    }
    .letter-preview table {
        display: block;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .letter-preview pre { white-space: pre-wrap; word-break: break-word; }
    .letter-preview h1 { font-size: 1.25rem; }
    .letter-preview h2 { font-size: 1.15rem; }
    .letter-preview h3 { font-size: 1.05rem; }
    @media (min-width: 768px) {
        .letter-preview h1 { font-size: 1.75rem; }
        .letter-preview h2 { font-size: 1.5rem; }
        .letter-preview h3 { font-size: 1.25rem; }
    }

    .pm-method-option { transition: border-color .15s ease, background .15s ease; }
    .pm-method-option:hover { border-color: var(--bs-primary) !important; background: var(--bs-light-primary); }
    .pm-method-option:has(input:checked) {
        border-color: var(--bs-primary) !important; background: var(--bs-light-primary);
    }

    /* Make TinyMCE blend with Metronic */
    .tox-tinymce {
        border-radius: 0.475rem !important;
        border-color: var(--bs-gray-300) !important;
    }

    @media (max-width: 400px) {
        .card-header .btn {
            font-size: 0.8rem;
            padding-left: 0.6rem;
            padding-right: 0.6rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>

<script>
(function () {
    'use strict';

    const STATUS         = @json($letter['status']);
    const UUID           = @json($letter['uuid']);
    const IS_USER_EDITED = @json($letter['is_user_edited'] ?? false);
    const CSRF           = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    // ── Toast helper ────────────────────────────────────────────
    function toast(type, message, title) {
        if (typeof window.showToast === 'function') {
            window.showToast(type, message, title);
            return;
        }
        const host = document.createElement('div');
        host.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
        host.innerHTML = `
            <div class="alert alert-${type === 'error' ? 'danger' : 'success'} shadow d-flex align-items-center gap-2 mb-2"
                 style="min-width:260px;">
                <i class="ki-duotone ki-${type === 'error' ? 'information-5' : 'check-circle'} fs-2">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
                <div>
                    <div class="fw-bold fs-7">${title || (type === 'error' ? 'Error' : 'Success')}</div>
                    <div class="fs-8">${message}</div>
                </div>
            </div>`;
        document.body.appendChild(host);
        setTimeout(() => host.remove(), 3500);
    }

    // ── Button spinner helpers ─────────────────────────────────
    function spinButton(btn) {
        if (!btn) return;
        btn.disabled = true;
        btn.setAttribute('data-kt-indicator', 'on');
        const label = btn.querySelector('.indicator-label');
        const progress = btn.querySelector('.indicator-progress');
        if (label) label.classList.add('d-none');
        if (progress) progress.classList.remove('d-none');
    }

    function unspinButton(btn) {
        if (!btn) return;
        btn.disabled = false;
        btn.removeAttribute('data-kt-indicator');
        const label = btn.querySelector('.indicator-label');
        const progress = btn.querySelector('.indicator-progress');
        if (label) label.classList.remove('d-none');
        if (progress) progress.classList.add('d-none');
    }

    // ── Confirm modal helper ───────────────────────────────────
    const confirmEl    = document.getElementById('confirmActionModal');
    const confirmModal = confirmEl ? new bootstrap.Modal(confirmEl) : null;

    function confirmAction({ title = 'Confirm', message = 'Are you sure?', okText = 'Confirm', okClass = 'btn-primary' } = {}) {
        return new Promise(resolve => {
            if (!confirmModal) { resolve(window.confirm(message)); return; }

            const titleEl   = document.getElementById('confirmActionTitle');
            const msgEl     = document.getElementById('confirmActionMessage');
            const okBtn     = document.getElementById('confirmActionOk');
            const cancelBtn = document.getElementById('confirmActionCancel');

            titleEl.textContent = title;
            msgEl.textContent   = message;
            okBtn.className     = `btn ${okClass}`;
            okBtn.querySelector('.indicator-label').textContent = okText;
            okBtn.disabled      = false;
            okBtn.removeAttribute('data-kt-indicator');

            let resolved = false;
            const cleanup = (result) => {
                if (resolved) return;
                resolved = true;
                confirmModal.hide();
                resolve(result);
            };

            okBtn.addEventListener('click', () => cleanup(true), { once: true });
            cancelBtn.addEventListener('click', () => cleanup(false), { once: true });
            confirmEl.addEventListener('hidden.bs.modal', () => cleanup(false), { once: true });

            confirmModal.show();
        });
    }

    // ── Background status polling (no full page reload until state changes) ──
    (function setupStatusPolling() {
        // Only poll if we're actively generating
        if (STATUS !== 'processing' && STATUS !== 'paid') {
            sessionStorage.removeItem('letter_poll_' + UUID);
            return;
        }

        // Disable the "Generate Now" button immediately so it can't be spammed
        const genBtn = document.getElementById('generateNowBtn');
        if (genBtn) {
            genBtn.disabled = true;
            genBtn.classList.add('disabled');
            const label = genBtn.querySelector('.indicator-label');
            const progress = genBtn.querySelector('.indicator-progress');
            if (label) label.classList.add('d-none');
            if (progress) progress.classList.remove('d-none');
        }

        const KEY       = 'letter_poll_' + UUID;
        const startedAt = parseInt(sessionStorage.getItem(KEY + '_started') || '0', 10) || Date.now();
        sessionStorage.setItem(KEY + '_started', String(startedAt));

        const POLL_MS = 3000;
        const MAX_MS  = 5 * 60 * 1000;   // give up after 5 min

        const tick = async () => {
            if (Date.now() - startedAt > MAX_MS) {
                sessionStorage.removeItem(KEY + '_started');
                // Show a soft timeout message instead of looping forever
                const wrap = document.querySelector('.card-body');
                if (wrap && !document.getElementById('pollTimeoutNotice')) {
                    const notice = document.createElement('div');
                    notice.id = 'pollTimeoutNotice';
                    notice.className = 'alert alert-light-warning mt-3 fs-7';
                    notice.innerHTML =
                        '<div class="fw-bold">Still working…</div>' +
                        '<div class="text-muted">Generation is taking longer than usual. ' +
                        'You can safely leave this page — we\'ll email you when it\'s ready, ' +
                        'or refresh to check.</div>';
                    wrap.appendChild(notice);
                }
                return;
            }

            try {
                const r = await fetch(`{{ url('letters') }}/${UUID}/status`, {
                    headers: { 'Accept': 'application/json' },
                });
                const b = await r.json();
                const current = b?.data?.status;

                if (!current) {
                    setTimeout(tick, POLL_MS);
                    return;
                }

                // Still in the same in-flight state — keep waiting
                if (current === 'paid' || current === 'processing') {
                    setTimeout(tick, POLL_MS);
                    return;
                }

                // State changed (generated / failed / pending_payment) →
                // reload ONCE so the page re-renders with the new state
                sessionStorage.removeItem(KEY + '_started');
                window.location.reload();

            } catch (e) {
                // Network blip — try again
                setTimeout(tick, POLL_MS);
            }
        };

        // Small delay so the initial page render isn't blocked
        setTimeout(tick, POLL_MS);
    })();

    // ── Payment modal ──────────────────────────────────────────
    const modalEl = document.getElementById('paymentModal');
    if (modalEl) {
        const modal      = new bootstrap.Modal(modalEl);
        const openBtn    = document.getElementById('openPayBtn');
        const phoneWrap  = document.getElementById('pm_phone_wrap');
        const phoneIn    = document.getElementById('pm_phone');
        const submitBtn  = document.getElementById('pm_submit_btn');
        const errorEl    = document.getElementById('pm_error');
        const warningTxt = document.getElementById('pm_warning_text');
        const closeBtn   = document.getElementById('paymentModalClose');

        function setWarning(method) {
            if (method === 'card') {
                warningTxt.innerHTML =
                    '<strong>Have your card details ready.</strong> ' +
                    'You will be redirected to a secure payment page to complete the transaction.';
            } else {
                warningTxt.innerHTML =
                    '<strong>Make sure you have enough mobile money credit.</strong> ' +
                    'Your balance must cover the amount above before you can approve the PIN prompt. ' +
                    'If the payment fails, contact support.';
            }
        }

        function syncMethod() {
            const method = modalEl.querySelector('input[name="pm_method"]:checked')?.value || 'mobile_money';
            phoneWrap.classList.toggle('d-none', method === 'card');
            errorEl.classList.add('d-none');
            errorEl.textContent = '';
            setWarning(method);
        }

        modalEl.querySelectorAll('input[name="pm_method"]').forEach(r => {
            r.addEventListener('change', syncMethod);
        });

        openBtn?.addEventListener('click', () => {
            if (phoneIn) phoneIn.value = '';
            const mobile = modalEl.querySelector('input[name="pm_method"][value="mobile_money"]');
            if (mobile) mobile.checked = true;
            syncMethod();
            modal.show();
            setTimeout(() => {
                if (phoneWrap && !phoneWrap.classList.contains('d-none')) phoneIn.focus();
            }, 300);
        });

        submitBtn.addEventListener('click', async () => {
            const method = modalEl.querySelector('input[name="pm_method"]:checked')?.value || 'mobile_money';
            const phone  = (phoneIn.value || '').replace(/[^\d+]/g, '').trim();

            if (method === 'mobile_money' && phone && !/^\+\d{9,15}$/.test(phone)) {
                showError('That phone number does not look right. Use the format +256 754 428 612.');
                return;
            }

            spinButton(submitBtn);
            errorEl.classList.add('d-none');

            try {
                const res = await fetch(`{{ url('letters') }}/${UUID}/pay`, {
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
                    const msg = data.message || 'Payment could not be started.';
                    if (/phone number/i.test(msg) && !phoneWrap.classList.contains('d-none')) {
                        phoneIn.classList.add('is-invalid');
                        phoneIn.focus();
                        phoneIn.addEventListener('input', () => phoneIn.classList.remove('is-invalid'), { once: true });
                    }
                    showError(msg);
                    unspinButton(submitBtn);
                    return;
                }

                if (data.amount_formatted) {
                    document.getElementById('pm_amount').textContent = data.amount_formatted;
                }

                if (data.is_free) {
                    modal.hide();
                    toast('success', data.message || 'Letter is being generated.', 'Success');
                    setTimeout(() => window.location.reload(), 1000);
                    return;
                }

                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                    return;
                }

                modal.hide();
                toast('success', data.message || 'Prompt sent. Approve on your phone.', 'Sent');
                setTimeout(() => window.location.reload(), 900);

            } catch (e) {
                showError('Something went wrong: ' + e.message);
                unspinButton(submitBtn);
            }
        });

        closeBtn?.addEventListener('click', () => {
            unspinButton(submitBtn);
            errorEl.classList.add('d-none');
            errorEl.textContent = '';
        });

        function showError(msg) {
            errorEl.className = 'alert alert-light-danger mt-4 mb-0';
            errorEl.textContent = msg;
            errorEl.classList.remove('d-none');
        }
    }

    // ── Generate Now ───────────────────────────────────────────
    document.getElementById('generateNowBtn')?.addEventListener('click', async function () {
        if (this.disabled) return;   // double guard

        const ok = await confirmAction({
            title: 'Regenerate letter',
            message: 'This will request a fresh generation. Continue?',
            okText: 'Retry',
            okClass: 'btn-primary',
        });
        if (!ok) return;

        const btn = this;
        btn.disabled = true;              // keep it locked
        spinButton(btn);

        try {
            const res = await fetch(`{{ url('letters') }}/{{ $letter['uuid'] }}/regenerate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const body = await res.json();

            if (!body.success) {
                toast('error', body.message || 'Could not start generation.', 'Generation failed');
                unspinButton(btn);
                return;
            }

            toast('success', body.message || 'Generating…', 'Started');
            setTimeout(() => window.location.reload(), 800);
        } catch (e) {
            toast('error', 'Network error: ' + e.message, 'Error');
            unspinButton(btn);
        }
    });

    // ── Regenerate ─────────────────────────────────────────────
    document.getElementById('regenerateBtn')?.addEventListener('click', async function () {
        const ok = await confirmAction({
            title: 'Regenerate letter',
            message: IS_USER_EDITED
                ? 'This will replace your manual edits with a fresh AI version. Continue?'
                : 'Try generating again? This uses one of your retries.',
            okText: 'Regenerate',
            okClass: 'btn-warning',
        });
        if (!ok) return;

        const btn = this;
        spinButton(btn);

        try {
            const res = await fetch(`{{ url('letters') }}/{{ $letter['uuid'] }}/regenerate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });
            const body = await res.json();

            if (!body.success) {
                toast('error', body.message || 'Could not start regeneration.', 'Regeneration failed');
                unspinButton(btn);
                return;
            }

            toast('success', body.message || 'Regenerating…', 'Started');
            setTimeout(() => window.location.reload(), 800);
        } catch (e) {
            toast('error', 'Network error: ' + e.message, 'Error');
            unspinButton(btn);
        }
    });

    // ── Update CV/Job then retry ───────────────────────────────
    document.getElementById('updateLetterForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('updateBtn');
        if (!btn) return;

        const errEl = document.getElementById('updateError');
        if (errEl) { errEl.classList.add('d-none'); errEl.textContent = ''; }

        const fd = new FormData(this);

        // Nothing to submit?
        const jobTitle    = (fd.get('job_title')    || '').toString().trim();
        const companyName = (fd.get('company_name') || '').toString().trim();
        const cvFile      = fd.get('cv_file');
        const hasFile     = cvFile instanceof File && cvFile.size > 0;

        if (!hasFile && !jobTitle && !companyName) {
            if (errEl) {
                errEl.className = 'alert alert-light-warning mt-3 mb-0';
                errEl.textContent = 'Change the job details or upload a new CV before saving.';
                errEl.classList.remove('d-none');
            } else {
                toast('error', 'Change the job details or upload a new CV before saving.', 'Nothing to update');
            }
            return;
        }

        spinButton(btn);

        try {
            const res = await fetch(`{{ url('letters') }}/{{ $letter['uuid'] }}/update`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });

            // ── Route missing (404) ────────────────────────────
            if (res.status === 404) {
                const msg = 'Update endpoint not found. Contact support.';
                if (errEl) {
                    errEl.className = 'alert alert-light-danger mt-3 mb-0';
                    errEl.textContent = msg;
                    errEl.classList.remove('d-none');
                    errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    toast('error', msg, 'Route not found');
                }
                unspinButton(btn);
                return;
            }

            // ── Non-JSON response ──────────────────────────────
            const body = await res.json().catch(() => ({
                success: false,
                message: 'The server returned an unexpected response. Please try again.',
            }));

            // ── Real failure: show inline, scroll to it ────────
            if (!res.ok || !body.success) {
                const msg = body.message || 'Could not update the letter.';
                if (errEl) {
                    errEl.className = 'alert alert-light-danger mt-3 mb-0';
                    errEl.textContent = msg;
                    errEl.classList.remove('d-none');
                    errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    toast('error', msg, 'Update failed');
                }
                unspinButton(btn);
                return;
            }

            // ── Success ────────────────────────────────────────
            toast('success', body.message || 'Updated. Regenerating…', 'Saved');
            setTimeout(() => window.location.reload(), 800);

        } catch (err) {
            const msg = 'Network error: ' + err.message;
            if (errEl) {
                errEl.className = 'alert alert-light-danger mt-3 mb-0';
                errEl.textContent = msg;
                errEl.classList.remove('d-none');
                errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                toast('error', msg, 'Error');
            }
            unspinButton(btn);
        }
    });

    // ── Toggle edit form for failed inputs ─────────────────────
    document.getElementById('toggleEditBtn')?.addEventListener('click', function () {
        const form = document.getElementById('editForm');
        if (form) {
            form.classList.toggle('d-none');
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    // ── Copy letter ────────────────────────────────────────────
    document.getElementById('copyLetterBtn')?.addEventListener('click', async function () {
        const content = document.getElementById('letterContent');
        if (!content) return;

        const text = content.innerText || content.textContent || '';
        if (!text.trim()) {
            toast('error', 'Nothing to copy.', 'Empty letter');
            return;
        }

        try {
            await navigator.clipboard.writeText(text);
            toast('success', 'Letter copied to clipboard.', 'Copied');

            const btn = this;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="ki-duotone ki-check fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Copied!';
            setTimeout(() => { btn.innerHTML = originalHTML; }, 1800);
        } catch (err) {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                toast('success', 'Letter copied.', 'Copied');
            } catch (e) {
                toast('error', 'Your browser blocked the copy. Select the text and press Ctrl+C.', 'Copy failed');
            }
            document.body.removeChild(ta);
        }
    });

    // ══════════════════════════════════════════════════════════
    // EDIT LETTER — TinyMCE
    // ══════════════════════════════════════════════════════════
    const letterView    = document.getElementById('letterView');
    const letterEdit    = document.getElementById('letterEdit');
    const letterEditor  = document.getElementById('letterEditor');
    const letterContent = document.getElementById('letterContent');

    let editorInstance = null;
    let editorReadyPromise = null;

    function initEditor(initialHtml) {
        if (!letterEditor) return Promise.resolve(null);

        // Already initialised → just reset content
        if (editorInstance && editorReadyPromise) {
            return editorReadyPromise.then(editor => {
                if (editor && editor.setContent) {
                    editor.setContent(initialHtml || '');
                }
                return editor;
            });
        }

        // No TinyMCE → fall back to plain textarea
        if (typeof tinymce === 'undefined') {
            letterEditor.value = htmlToPlainText(initialHtml);
            return Promise.resolve(null);
        }

        // Seed the textarea so TinyMCE picks it up on init
        letterEditor.value = initialHtml || '';

        // ⚠️ KEY: build a promise that resolves to the REAL editor instance
        editorReadyPromise = new Promise((resolve) => {
            tinymce.init({
                target: letterEditor,
                height: 520,
                menubar: false,
                statusbar: true,
                branding: false,
                promotion: false,
                plugins: 'lists link table code wordcount',
                toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link | alignleft aligncenter alignright | removeformat | code',
                block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3',
                content_style: `
                    body {
                        font-family: Georgia, "Times New Roman", serif;
                        font-size: 14px;
                        line-height: 1.6;
                        padding: 16px;
                    }
                    p { margin: 0 0 0.6em; }
                    h1, h2, h3 { margin: 0.8em 0 0.4em; font-weight: bold; }
                    ul, ol { margin: 0.4em 0 0.6em 1.4em; }
                    li { margin: 0.2em 0; }
                `,
                setup: (editor) => {
                    // 'init' fires once the editor is truly ready
                    editor.on('init', () => {
                        editorInstance = editor;   // ← store the REAL instance here
                        if (initialHtml && (!editor.getContent() || !editor.getContent().trim())) {
                            editor.setContent(initialHtml);
                        }
                        resolve(editor);
                    });
                },
            }).catch(err => {
                console.warn('TinyMCE failed to load, falling back to plain textarea:', err);
                letterEditor.value = htmlToPlainText(initialHtml);
                resolve(null);
            });
        });

        return editorReadyPromise;
    }

    function getEditorContent() {
        if (editorInstance && typeof editorInstance.getContent === 'function') {
            return editorInstance.getContent();
        }
        return letterEditor?.value || '';
    }

    function htmlToPlainText(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        tmp.querySelectorAll('p, li, h1, h2, h3').forEach(el => {
            el.appendChild(document.createTextNode('\n\n'));
        });
        return (tmp.innerText || tmp.textContent || '').trim();
    }

    function getCurrentLetterHtml() {
        if (!letterContent) return '';
        return letterContent.innerHTML.trim();
    }

    document.getElementById('editLetterBtn')?.addEventListener('click', () => {
        if (!letterEdit || !letterView) return;

        letterView.classList.add('d-none');
        letterEdit.classList.remove('d-none');
        letterEdit.scrollIntoView({ behavior: 'smooth', block: 'start' });

        initEditor(getCurrentLetterHtml());
    });

    document.getElementById('cancelEditsBtn')?.addEventListener('click', () => {
        // Keep editorInstance alive so re-opening is instant.
        // If you want to fully destroy it, uncomment the two lines below.
        // if (editorInstance) { editorInstance.remove(); editorInstance = null; editorReadyPromise = null; }
        letterEdit?.classList.add('d-none');
        letterView?.classList.remove('d-none');
    });

    document.getElementById('saveEditsBtn')?.addEventListener('click', async function () {
        const btn = this;
        spinButton(btn);

        try {
            await editorReadyPromise;                       // ensure editor is ready
            const html = getEditorContent().trim();

            if (!html || html.replace(/<[^>]*>/g, '').trim().length < 50) {
                toast('error', 'The letter looks too short. Please keep at least a few sentences.', 'Too short');
                unspinButton(btn);
                return;
            }

            const res = await fetch(`{{ url('letters') }}/{{ $letter['uuid'] }}/save-edited`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ content: html }),
            });
            const body = await res.json();

            if (!body.success) {
                toast('error', body.message || 'Could not save your edits.', 'Save failed');
                unspinButton(btn);
                return;
            }

            toast('success', 'Your edits have been saved.', 'Saved');
            setTimeout(() => window.location.reload(), 700);
        } catch (e) {
            toast('error', 'Network error: ' + e.message, 'Error');
            unspinButton(btn);
        }
    });

    // ── Reset to AI version ───────────────────────────────────
    document.getElementById('resetToAiBtn')?.addEventListener('click', async function () {
        const ok = await confirmAction({
            title: 'Reset to AI version',
            message: 'Discard all your edits and restore the last AI-generated version?',
            okText: 'Reset',
            okClass: 'btn-danger',
        });
        if (!ok) return;

        const btn = this;
        spinButton(btn);

        try {
            const res = await fetch(`{{ url('letters') }}/{{ $letter['uuid'] }}/reset-edited`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const body = await res.json();

            if (!body.success) {
                toast('error', body.message || 'Could not reset to AI version.', 'Reset failed');
                unspinButton(btn);
                return;
            }

            toast('success', 'Restored to the AI version.', 'Reset');
            setTimeout(() => window.location.reload(), 700);
        } catch (e) {
            toast('error', 'Network error: ' + e.message, 'Error');
            unspinButton(btn);
        }
    });

})();
</script>
@endpush