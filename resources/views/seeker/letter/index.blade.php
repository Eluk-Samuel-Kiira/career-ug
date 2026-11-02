@extends('layouts.admin')

@section('title', 'My Letters')
@section('page_title', 'Cover / Application Letters')

@section('content')
<div class="container py-4 py-md-6">

    @if(request('payment') === 'pending' && request('uuid'))
        <div class="alert alert-light-warning d-flex align-items-center mb-4">
            <i class="ki-duotone ki-time fs-2x text-warning me-3">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            <div class="flex-grow-1">
                <div class="fw-bold">Payment still pending</div>
                <div class="text-muted fs-7">
                    We haven't received confirmation from your mobile money provider yet.
                    If you approved the prompt, it may take a moment. Otherwise,
                    <a href="{{ route('letters.show', request('uuid')) }}" class="fw-semibold">open the letter</a>
                    to retry or check its status.
                </div>
            </div>
        </div>
    @endif

    <div class="card card-flush">
        <div class="card-header align-items-center py-4 py-md-5 flex-wrap gap-2">
            <h3 class="card-title fw-bold text-gray-800 fs-4 fs-md-3">
                My Letters
                <span class="badge badge-light-primary ms-2">{{ count($letters) }}</span>
            </h3>
            <div class="card-toolbar w-100 w-sm-auto">
                <a href="{{ route('letters.create') }}" class="btn btn-sm btn-primary w-100 w-sm-auto">
                    <i class="ki-duotone ki-plus fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                    Generate a New Letter
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            @if(empty($letters))
                <div class="text-center py-10 py-md-15">
                    <i class="ki-duotone ki-document fs-3x fs-md-5tx text-muted mb-3 mb-md-4 d-block">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <h4 class="fw-bold text-gray-800 mb-2 fs-5 fs-md-4">No letters yet</h4>
                    <p class="text-muted mb-4 mb-md-5 fs-7">
                        Generate a professional cover or application letter from your CV in under a minute.
                    </p>
                    <a href="{{ route('letters.create') }}" class="btn btn-primary w-100 w-sm-auto">
                        Get Started
                    </a>
                </div>
            @else

                {{-- ─── DESKTOP: table ───────────────────────────────── --}}
                <div class="d-none d-md-block table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>Type</th>
                                <th>Target Role</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($letters as $l)
                                @php
                                    $attempts    = (int) ($l['regeneration_count'] ?? 0);
                                    $maxAttempts = (int) ($l['max_regenerations']  ?? 2);
                                    $canRetry    = $attempts < $maxAttempts;
                                    $isPaid      = !empty($l['is_paid']);
                                    $isPending   = $l['status'] === 'pending_payment';

                                    $canRegenerate = $isPaid
                                        && in_array($l['status'], ['failed', 'generated'], true)
                                        && $canRetry;

                                    $canDelete = !$isPaid
                                        && !in_array($l['status'], ['processing', 'paid', 'generated'], true);

                                    $canEditPending = $isPending;
                                @endphp
                                <tr>
                                    <td><span class="badge badge-light-info">{{ $l['letter_type_label'] }}</span></td>
                                    <td class="fw-semibold">{{ $l['job_title'] }}</td>
                                    <td>{{ $l['company_name'] }}</td>
                                    <td>
                                        {!! $l['status_badge'] !!}
                                        @if($l['status'] === 'failed' && $attempts > 0)
                                            <span class="badge badge-light-danger fs-8 ms-1">
                                                {{ $attempts }}/{{ $maxAttempts }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-7">
                                        {{ \Carbon\Carbon::parse($l['created_at'])->format('M d, Y') }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">

                                            {{-- View / status entry --}}
                                            @if($l['status'] === 'generated')
                                                <a href="{{ route('letters.show', $l['uuid']) }}"
                                                   class="btn btn-sm btn-icon btn-light" title="View">
                                                    <i class="ki-duotone ki-eye fs-4">
                                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                                    </i>
                                                </a>
                                            @elseif($l['status'] === 'processing' || $l['status'] === 'paid')
                                                <a href="{{ route('letters.show', $l['uuid']) }}"
                                                   class="btn btn-sm btn-light-primary">
                                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                                    View
                                                </a>
                                            @elseif($l['status'] === 'failed')
                                                <a href="{{ route('letters.show', $l['uuid']) }}"
                                                   class="btn btn-sm btn-icon btn-light-danger" title="View error">
                                                    <i class="ki-duotone ki-information-5 fs-4">
                                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                                    </i>
                                                </a>
                                            @endif

                                            {{-- Pay Now (pending_payment) --}}
                                            @if($isPending)
                                                <a href="{{ route('letters.show', $l['uuid']) }}"
                                                   class="btn btn-sm btn-warning">
                                                    <i class="ki-duotone ki-dollar fs-4 me-1">
                                                        <span class="path1"></span><span class="path2"></span>
                                                    </i>
                                                    Pay Now
                                                </a>
                                            @endif

                                            {{-- Edit (pending_payment) --}}
                                            @if($canEditPending)
                                                <button type="button"
                                                        class="btn btn-sm btn-icon btn-light-warning edit-pending-btn"
                                                        data-uuid="{{ $l['uuid'] }}"
                                                        data-title="{{ $l['job_title'] }}"
                                                        title="Edit before paying">
                                                    <i class="ki-duotone ki-pencil fs-4">
                                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                                    </i>
                                                </button>
                                            @endif

                                            {{-- Regenerate --}}
                                            @if($canRegenerate)
                                                <button type="button"
                                                        class="btn btn-sm btn-warning regenerate-letter-btn"
                                                        data-uuid="{{ $l['uuid'] }}"
                                                        data-title="{{ $l['job_title'] }}"
                                                        data-remaining="{{ $maxAttempts - $attempts }}"
                                                        title="Try generating again">
                                                    <i class="ki-duotone ki-arrows-circle fs-4 me-1">
                                                        <span class="path1"></span><span class="path2"></span>
                                                    </i>
                                                    {{ $l['status'] === 'failed' ? 'Retry' : 'Regenerate' }}
                                                </button>
                                            @endif

                                            {{-- Delete --}}
                                            @if($canDelete)
                                                <button type="button"
                                                        class="btn btn-sm btn-icon btn-light-danger delete-letter-btn"
                                                        data-uuid="{{ $l['uuid'] }}"
                                                        data-title="{{ $l['job_title'] }}"
                                                        title="Delete">
                                                    <i class="ki-duotone ki-trash fs-4">
                                                        <span class="path1"></span><span class="path2"></span>
                                                        <span class="path3"></span><span class="path4"></span>
                                                    </i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ─── MOBILE: stacked cards ────────────────────────── --}}
                <div class="d-md-none d-flex flex-column gap-3">
                    @foreach($letters as $l)
                        @php
                            $attempts    = (int) ($l['regeneration_count'] ?? 0);
                            $maxAttempts = (int) ($l['max_regenerations']  ?? 2);
                            $canRetry    = $attempts < $maxAttempts;
                            $isPaid      = !empty($l['is_paid']);
                            $isPending   = $l['status'] === 'pending_payment';

                            $canRegenerate = $isPaid
                                && in_array($l['status'], ['failed', 'generated'], true)
                                && $canRetry;

                            $canDelete = !$isPaid
                                && !in_array($l['status'], ['processing', 'paid', 'generated'], true);

                            $canEditPending = $isPending;
                        @endphp
                        <div class="border border-gray-300 border-dashed rounded p-3 bg-light" data-letter-uuid="{{ $l['uuid'] }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="min-w-0">
                                    <div class="fw-bold text-gray-800 text-truncate fs-6">
                                        {{ $l['job_title'] }}
                                    </div>
                                    <div class="text-muted fs-8 text-truncate">
                                        {{ $l['company_name'] }}
                                    </div>
                                </div>
                                <span class="badge badge-light-info flex-shrink-0">
                                    {{ $l['letter_type_label'] }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                                <div>
                                    {!! $l['status_badge'] !!}
                                    @if($l['status'] === 'failed' && $attempts > 0)
                                        <span class="badge badge-light-danger fs-8 ms-1">
                                            {{ $attempts }}/{{ $maxAttempts }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-muted fs-8 text-end">
                                    {{ \Carbon\Carbon::parse($l['created_at'])->format('M d, Y') }}
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-xs-row gap-2 flex-wrap">

                                {{-- Status entry --}}
                                @if($l['status'] === 'generated')
                                    <a href="{{ route('letters.show', $l['uuid']) }}"
                                       class="btn btn-sm btn-light-primary w-100 w-sm-auto">
                                        <i class="ki-duotone ki-eye fs-4 me-1">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        View
                                    </a>
                                @elseif($l['status'] === 'failed')
                                    <a href="{{ route('letters.show', $l['uuid']) }}"
                                       class="btn btn-sm btn-light-danger w-100 w-sm-auto">
                                        <i class="ki-duotone ki-information-5 fs-4 me-1">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        View Error
                                    </a>
                                @elseif($l['status'] === 'processing' || $l['status'] === 'paid')
                                    <a href="{{ route('letters.show', $l['uuid']) }}"
                                       class="btn btn-sm btn-light-primary w-100 w-sm-auto">
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                        View
                                    </a>
                                @endif

                                {{-- Pay Now --}}
                                @if($isPending)
                                    <a href="{{ route('letters.show', $l['uuid']) }}"
                                       class="btn btn-sm btn-warning w-100 w-sm-auto">
                                        <i class="ki-duotone ki-dollar fs-4 me-1">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        Pay Now
                                    </a>
                                @endif

                                {{-- Edit --}}
                                @if($canEditPending)
                                    <button type="button"
                                            class="btn btn-sm btn-light-warning edit-pending-btn w-100 w-sm-auto"
                                            data-uuid="{{ $l['uuid'] }}"
                                            data-title="{{ $l['job_title'] }}">
                                        <i class="ki-duotone ki-pencil fs-4 me-1">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        Edit
                                    </button>
                                @endif

                                {{-- Regenerate --}}
                                @if($canRegenerate)
                                    <button type="button"
                                            class="btn btn-sm btn-warning regenerate-letter-btn w-100 w-sm-auto"
                                            data-uuid="{{ $l['uuid'] }}"
                                            data-title="{{ $l['job_title'] }}"
                                            data-remaining="{{ $maxAttempts - $attempts }}">
                                        <i class="ki-duotone ki-arrows-circle fs-4 me-1">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        {{ $l['status'] === 'failed' ? 'Retry' : 'Regenerate' }}
                                    </button>
                                @endif

                                {{-- Delete --}}
                                @if($canDelete)
                                    <button type="button"
                                            class="btn btn-sm btn-light-danger delete-letter-btn w-100 w-sm-auto"
                                            data-uuid="{{ $l['uuid'] }}"
                                            data-title="{{ $l['job_title'] }}">
                                        <i class="ki-duotone ki-trash fs-4 me-1">
                                            <span class="path1"></span><span class="path2"></span>
                                            <span class="path3"></span><span class="path4"></span>
                                        </i>
                                        Delete
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            @endif
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     EDIT PENDING MODAL
     Lets the seeker adjust the job title, company, or swap the CV
     before paying. Only shown for pending_payment letters.
════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editPendingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold mb-0 fs-4">Edit Letter Request</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary"
                        data-bs-dismiss="modal" id="editPendingClose">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>

            <form id="editPendingForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="edit_pending_uuid">

                <div class="modal-body">
                    <div class="alert alert-light-info fs-7 mb-4 d-flex align-items-start">
                        <i class="ki-duotone ki-information-5 fs-3 text-info me-2 mt-1">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div>
                            <strong>Nothing has been charged yet.</strong>
                            You can update the job details or upload a different CV before paying.
                        </div>
                    </div>

                    <div class="fv-row mb-4">
                        <label class="fw-semibold fs-7 mb-2">Job Title</label>
                        <input type="text" name="job_title" id="edit_pending_job_title"
                               class="form-control form-control-lg">
                    </div>

                    <div class="fv-row mb-4">
                        <label class="fw-semibold fs-7 mb-2">Company</label>
                        <input type="text" name="company_name" id="edit_pending_company"
                               class="form-control form-control-lg">
                    </div>

                    <div class="fv-row mb-2">
                        <label class="fw-semibold fs-7 mb-2">
                            Upload a different CV
                            <span class="text-muted fw-normal fs-8">(optional — max 5MB)</span>
                        </label>
                        <input type="file" name="cv_file" id="edit_pending_cv"
                               class="form-control" accept=".pdf,.doc,.docx">
                        <div class="text-muted fs-8 mt-1">
                            Leave blank to keep the CV you originally chose.
                        </div>
                    </div>

                    <div id="edit_pending_error" class="alert alert-light-danger mt-3 mb-0 d-none"></div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="edit_pending_submit">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-check-circle fs-3 me-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Save Changes
                        </span>
                        <span class="indicator-progress">
                            Saving... <span class="spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media (max-width: 575.98px) {
        .w-sm-auto { width: 100% !important; }
    }
    @media (min-width: 576px) {
        .w-sm-auto { width: auto !important; }
    }
    @media (min-width: 480px) {
        .flex-xs-row { flex-direction: row !important; }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    const CSRF = '{{ csrf_token() }}';

    // ────────────────────────────────────────────────────────
    // Delete
    // ────────────────────────────────────────────────────────
    document.querySelectorAll('.delete-letter-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const uuid  = this.dataset.uuid;
            const title = this.dataset.title || 'this letter';

            if (!confirm(`Delete the letter for "${title}"? This cannot be undone.`)) return;

            const original = this.innerHTML;
            this.disabled  = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const res  = await fetch(`{{ url('letters') }}/${uuid}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });
                const body = await res.json();

                if (!res.ok || !body.success) {
                    toast('error', body.message || 'Failed to delete the letter.');
                    this.disabled = false;
                    this.innerHTML = original;
                    return;
                }

                toast('success', 'Letter deleted.');

                const target = this.closest('tr') || this.closest('[data-letter-uuid]');
                if (target) {
                    target.style.transition = 'opacity .2s ease';
                    target.style.opacity    = '0';
                    setTimeout(() => {
                        target.remove();
                        if (document.querySelectorAll('.delete-letter-btn').length === 0) {
                            window.location.reload();
                        }
                    }, 200);
                }
            } catch (e) {
                toast('error', 'Network error: ' + e.message);
                this.disabled = false;
                this.innerHTML = original;
            }
        });
    });

    // ────────────────────────────────────────────────────────
    // Regenerate
    // ────────────────────────────────────────────────────────
    document.querySelectorAll('.regenerate-letter-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const uuid      = this.dataset.uuid;
            const title     = this.dataset.title || 'this letter';
            const remaining = parseInt(this.dataset.remaining || '0', 10);

            const msg = remaining > 1
                ? `Regenerate the letter for "${title}"? You have ${remaining} attempts left.`
                : `Regenerate the letter for "${title}"? This is your last attempt.`;

            if (!confirm(msg)) return;

            const original = this.innerHTML;
            this.disabled  = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Starting...';

            try {
                const res  = await fetch(`{{ url('letters') }}/${uuid}/regenerate`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });
                const body = await res.json();

                if (!res.ok || !body.success) {
                    if (body.code === 'max_regenerations_reached') {
                        toast('error', body.message || 'Regeneration limit reached.');
                        setTimeout(() => window.location.reload(), 1200);
                        return;
                    }
                    toast('error', body.message || 'Could not start regeneration.');
                    this.disabled = false;
                    this.innerHTML = original;
                    return;
                }

                toast('success', body.message || 'Regenerating...');
                setTimeout(() => {
                    window.location.href = `{{ url('letters') }}/${uuid}`;
                }, 600);
            } catch (e) {
                toast('error', 'Network error: ' + e.message);
                this.disabled = false;
                this.innerHTML = original;
            }
        });
    });

    // ────────────────────────────────────────────────────────
    // Edit pending — open modal, prefill from the row
    // ────────────────────────────────────────────────────────
    const editModalEl = document.getElementById('editPendingModal');
    const editModal   = editModalEl ? new bootstrap.Modal(editModalEl) : null;

    document.querySelectorAll('.edit-pending-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const uuid  = this.dataset.uuid;
            const title = this.dataset.title || '';

            // Find the row/card to pull the current company name
            const row  = this.closest('tr');
            const card = this.closest('[data-letter-uuid]');
            const holder = row || card;

            let jobTitle = title;
            let company  = '';

            if (row) {
                // Desktop: columns are Type | Target Role | Company | Status | Created | Actions
                const cells = row.querySelectorAll('td');
                if (cells.length >= 3) {
                    jobTitle = cells[1]?.textContent?.trim() || jobTitle;
                    company  = cells[2]?.textContent?.trim() || '';
                }
            } else if (card) {
                jobTitle = card.querySelector('.fw-bold.text-gray-800')?.textContent?.trim() || jobTitle;
                company  = card.querySelector('.text-muted.fs-8')?.textContent?.trim() || '';
            }

            document.getElementById('edit_pending_uuid').value       = uuid;
            document.getElementById('edit_pending_job_title').value = jobTitle;
            document.getElementById('edit_pending_company').value   = company;
            document.getElementById('edit_pending_cv').value        = '';
            document.getElementById('edit_pending_error').classList.add('d-none');

            editModal?.show();
        });
    });

    // ────────────────────────────────────────────────────────
    // Edit pending — submit
    // ────────────────────────────────────────────────────────
    document.getElementById('editPendingForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const uuid    = document.getElementById('edit_pending_uuid').value;
        const submit  = document.getElementById('edit_pending_submit');
        const errorEl = document.getElementById('edit_pending_error');

        if (!uuid) {
            showEditError('Missing letter reference.');
            return;
        }

        submit.disabled = true;
        submit.setAttribute('data-kt-indicator', 'on');
        errorEl.classList.add('d-none');

        try {
            const fd = new FormData(this);

            const res  = await fetch(`{{ url('letters') }}/${uuid}/update`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            const body = await res.json();

            if (!res.ok || !body.success) {
                showEditError(body.message || 'Could not save changes.');
                submit.disabled = false;
                submit.removeAttribute('data-kt-indicator');
                return;
            }

            toast('success', body.message || 'Changes saved.');
            editModal?.hide();

            // Reload the page so the updated row reflects the new title/company
            setTimeout(() => window.location.reload(), 500);

        } catch (err) {
            showEditError('Network error: ' + err.message);
            submit.disabled = false;
            submit.removeAttribute('data-kt-indicator');
        }
    });

    function showEditError(msg) {
        const errorEl = document.getElementById('edit_pending_error');
        errorEl.className = 'alert alert-light-danger mt-3 mb-0';
        errorEl.textContent = msg;
        errorEl.classList.remove('d-none');
    }

    // ────────────────────────────────────────────────────────
    // Toast helper
    // ────────────────────────────────────────────────────────
    function toast(type, msg) {
        if (typeof window.showToast === 'function') {
            window.showToast(type, msg);
        } else {
            alert(msg);
        }
    }
})();
</script>
@endpush