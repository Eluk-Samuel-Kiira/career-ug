@extends('layouts.admin')

@section('title', 'Job Submission')
@section('page_title', 'Job Submission')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Employer</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item">
        <a href="{{ route('employer.jobs.index') }}" class="text-muted text-hover-primary">My Job Posts</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">#{{ substr($submission['uuid'], 0, 8) }}</li>
@endsection

@section('content')
@php
    $s = $submission;
    $needsPayment = $s['status'] === 'pending_payment';
@endphp

<div class="container py-6" style="max-width:900px;">

    {{-- Header --}}
    <div class="card card-flush mb-6">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="fs-3 fw-bold mb-1">{{ $s['job_title'] }}</div>
                    <div class="text-muted fs-7">
                        #{{ substr($s['uuid'], 0, 8) }}
                        · {{ \Carbon\Carbon::parse($s['created_at'])->format('M d, Y H:i') }}
                    </div>
                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        {!! $s['status_badge'] !!}
                        {!! $s['payment_badge'] !!}
                    </div>
                </div>
                <div class="text-end">
                    <div class="fs-4 fw-bold text-primary">
                        {{ $s['is_free'] ? 'FREE' : $s['formatted_amount'] }}
                    </div>
                    <div class="text-muted fs-7">{{ $s['service_name'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment section --}}
    @if($needsPayment)
    <div class="card card-flush mb-6 border border-warning">
        <div class="card-body text-center py-10">
            <i class="ki-duotone ki-dollar fs-3x text-warning mb-3 d-block">
                <span class="path1"></span><span class="path2"></span>
            </i>
            <h3 class="fw-bold mb-2">Complete Payment</h3>
            <p class="text-muted mb-4">
                Pay <strong>{{ $s['formatted_amount'] }}</strong> to proceed.
            </p>

            <div class="alert alert-light-warning text-start mb-5 mx-auto" style="max-width:400px;">
                <div class="fw-bold mb-2">Payment methods</div>
                <ul class="mb-0 ps-4">
                    <li>MTN Mobile Money: <strong>+256 776263482 (Eluk Samuel Kiira)</strong></li>
                    <li>Airtel Money: <strong>+256 754 428612(Eluk Samuel Kiira)</strong></li>
                    <li>Bank Transfer: <strong>Bank of Africa, A/C 04644350008 (Eluk Samuel Kiira)</strong></li>
                </ul>
            </div>

            <button class="btn btn-success btn-lg px-10" data-bs-toggle="modal" data-bs-target="#paymentModal">
                I've Paid — Enter Reference
            </button>
        </div>
    </div>
    @endif

    {{-- Rejection reason --}}
    @if($s['status'] === 'rejected' && !empty($s['rejection_reason']))
    <div class="card card-flush mb-6 border border-danger">
        <div class="card-body">
            <div class="fw-bold text-danger mb-2">Rejection Reason</div>
            <div>{{ $s['rejection_reason'] }}</div>
        </div>
    </div>
    @endif

    {{-- Content --}}
    <div class="card card-flush">
        <div class="card-header">
            <h3 class="card-title">Submitted Content</h3>
        </div>
        <div class="card-body">
            <div class="p-4 bg-light rounded font-monospace" style="white-space:pre-wrap;">{{ $s['content'] }}</div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex justify-content-between mt-6">
        <a href="{{ route('employer.jobs.index') }}" class="btn btn-light">Back</a>

        @if($s['can_delete'])
        <button class="btn btn-danger" onclick="deleteSubmission('{{ $s['uuid'] }}')">
            <i class="ki-duotone ki-trash fs-4 me-1">
                <span class="path1"></span><span class="path2"></span>
                <span class="path3"></span><span class="path4"></span><span class="path5"></span>
            </i>
            Delete
        </button>
        @endif
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="fw-bold">Record Payment</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                @csrf
                <div class="modal-body">
                    <div class="fv-row mb-5">
                        <label class="required fw-semibold mb-2">Payment Reference / Transaction ID</label>
                        <input type="text" name="payment_reference" class="form-control"
                               placeholder="e.g., MTN-89432, Airtel-1234"
                               required maxlength="255" />
                        <div class="text-muted fs-8 mt-1">
                            Paste the transaction ID from your payment confirmation message.
                            Our team will verify before publishing your job.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="paymentBtn">
                        <span class="indicator-label">Submit Reference</span>
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

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const UUID = '{{ $s['uuid'] }}';

// Payment form
document.getElementById('paymentForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = document.getElementById('paymentBtn');
    window.showButtonSpinner(btn);

    const reference = this.querySelector('input[name="payment_reference"]').value;

    fetch(`/employer/jobs/${UUID}/payment`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ payment_reference: reference })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('paymentModal'))?.hide();
            setTimeout(() => window.location.reload(), 700);
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message || 'Failed.');
            window.showToast('error', msg);
        }
    })
    .catch(() => window.showToast('error', 'Failed.'))
    .finally(() => window.hideButtonSpinner(btn));
});

// Delete
window.deleteSubmission = function (uuid) {
    if (!confirm('Delete this submission? This cannot be undone.')) return;

    fetch(`/employer/jobs/${uuid}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message);
            setTimeout(() => window.location.href = '{{ route('employer.jobs.index') }}', 600);
        } else {
            window.showToast('error', data.message);
        }
    })
    .catch(() => window.showToast('error', 'Failed.'));
};
</script>
@endpush