@extends('layouts.admin')

@section('title', 'Compliance Documents')
@section('page_title', 'Compliance Documents')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Employer</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Compliance</li>
@endsection

@section('content')
@php
    $status      = $data['compliance_status'] ?? 'incomplete';
    $progress    = $data['compliance_progress'] ?? 0;
    $documents   = $data['documents'] ?? [];
    $hasAll      = $data['has_all_required'] ?? false;
    $missing     = $data['missing_documents'] ?? [];
    $expiring    = $data['expiring_documents'] ?? [];
    $tierLabel   = $data['tier_label'] ?? 'Starter';

    $statusMeta = [
        'incomplete' => ['color' => 'warning', 'label' => 'Incomplete',   'icon' => 'ki-information-5', 'desc' => 'Upload all required documents to unlock a Verified badge.'],
        'submitted'  => ['color' => 'info',    'label' => 'Under Review', 'icon' => 'ki-time',          'desc' => 'Our team is reviewing your documents. Usually within 24 hours.'],
        'verified'   => ['color' => 'success', 'label' => 'Verified',     'icon' => 'ki-verify',        'desc' => 'Your compliance is verified. You now have the Verified Employer badge.'],
        'rejected'   => ['color' => 'danger',  'label' => 'Rejected',     'icon' => 'ki-cross-circle',  'desc' => 'Some documents were rejected. Check the notes below and re-upload.'],
    ];
    $sm = $statusMeta[$status] ?? $statusMeta['incomplete'];
@endphp

<div class="row g-6">

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- LEFT: status + progress                                  --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="col-lg-4">

        {{-- Status card --}}
        <div class="card card-flush mb-6">
            <div class="card-body text-center py-8">
                <div class="symbol symbol-80px symbol-circle mx-auto mb-4 bg-light-{{ $sm['color'] }}">
                    <i class="ki-duotone {{ $sm['icon'] }} fs-3x text-{{ $sm['color'] }}">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                    </i>
                </div>

                <div class="fs-4 fw-bold mb-1 text-{{ $sm['color'] }}">{{ $sm['label'] }}</div>
                <div class="text-muted fs-7 mb-4">{{ $sm['desc'] }}</div>

                @if($status === 'rejected' && !empty($data['compliance_notes']))
                    <div class="alert alert-light-danger text-start mb-4">
                        <div class="fw-bold fs-7 mb-1">Reviewer notes</div>
                        <div class="fs-7">{{ $data['compliance_notes'] }}</div>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold fs-7">Progress</div>
                    <div class="fw-bold fs-7 text-{{ $sm['color'] }}">{{ $progress }}%</div>
                </div>
                <div class="progress h-8px mb-4">
                    <div class="progress-bar bg-{{ $sm['color'] }}" style="width: {{ $progress }}%"></div>
                </div>

                <div class="d-flex justify-content-between fs-7 mb-2">
                    <span class="text-muted">Tier</span>
                    <span class="fw-bold">{{ $tierLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Required documents checklist --}}
        <div class="card card-flush mb-6">
            <div class="card-header"><h3 class="card-title">Required Checklist</h3></div>
            <div class="card-body pt-0">
                @if(!empty($missing))
                    <div class="fw-bold fs-7 text-muted mb-2">Still missing:</div>
                    <ul class="mb-4 ps-4">
                        @foreach($missing as $key => $label)
                            <li class="fs-7 text-warning">{{ $label }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="alert alert-light-success py-3 mb-4">
                        <i class="ki-duotone ki-check-circle fs-3 text-success me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <span class="fs-7 fw-semibold">All required documents uploaded</span>
                    </div>
                @endif

                @if(!empty($expiring))
                    <div class="fw-bold fs-7 text-muted mb-2">Expiring soon:</div>
                    <ul class="mb-0 ps-4">
                        @foreach($expiring as $field => $info)
                            <li class="fs-7 text-warning">
                                {{ $info['label'] }}
                                <span class="text-muted">
                                    ({{ $info['status'] === 'expired' ? 'expired' : 'expires' }}
                                    {{ \Carbon\Carbon::parse($info['expires_at'])->diffForHumans() }})
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Submit button --}}
        @if($hasAll && in_array($status, ['incomplete', 'rejected'], true))
            <div class="card card-flush bg-light-primary">
                <div class="card-body">
                    <div class="fw-bold mb-2">Ready to submit?</div>
                    <div class="text-muted fs-7 mb-4">
                        Our team will review your documents within 24 hours.
                    </div>
                    <button type="button" class="btn btn-primary w-100" id="submitComplianceBtn">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-send fs-4 me-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Submit for Review
                        </span>
                        <span class="indicator-progress">
                            Submitting... <span class="spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- RIGHT: document cards                                    --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="col-lg-8">
        <div class="card card-flush">
            <div class="card-header">
                <h3 class="card-title">Documents</h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary fs-7">
                        {{ count(array_filter($documents, fn($d) => $d['status'] !== 'missing')) }} / {{ count($documents) }} uploaded
                    </span>
                </div>
            </div>
            <div class="card-body pt-0">

                @foreach($documents as $doc)
                    @php
                        $uploaded = $doc['status'] !== 'missing';
                        $badgeColor = match($doc['status']) {
                            'uploaded'      => 'success',
                            'expiring_soon' => 'warning',
                            'expired'       => 'danger',
                            default         => 'secondary',
                        };
                        $badgeLabel = match($doc['status']) {
                            'uploaded'      => 'Uploaded',
                            'expiring_soon' => 'Expiring soon',
                            'expired'       => 'Expired',
                            default         => 'Missing',
                        };
                    @endphp

                    <div class="d-flex flex-wrap align-items-center justify-content-between p-4 border border-{{ $uploaded ? $badgeColor : 'gray-300' }} border-dashed rounded mb-3 gap-3"
                         data-document-row="{{ $doc['type'] }}">

                        <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-250px">
                            <div class="symbol symbol-45px bg-light-{{ $badgeColor }} flex-shrink-0">
                                <i class="ki-duotone ki-file fs-2x text-{{ $badgeColor }}">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <div class="fw-bold">{{ $doc['label'] }}</div>
                                    @if($doc['required'])
                                        <span class="badge badge-light-danger fs-8">Required</span>
                                    @endif
                                    <span class="badge badge-light-{{ $badgeColor }} fs-8">{{ $badgeLabel }}</span>
                                </div>

                                @if($uploaded)
                                    <div class="text-muted fs-7 text-truncate">{{ $doc['file_name'] }}</div>
                                    @if(!empty($doc['expires_at']))
                                        <div class="text-muted fs-8">
                                            Expires: {{ \Carbon\Carbon::parse($doc['expires_at'])->format('M d, Y') }}
                                        </div>
                                    @endif
                                @else
                                    <div class="text-muted fs-7">
                                        Upload to {{ $doc['required'] ? 'complete' : 'strengthen' }} your compliance.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-shrink-0">
                            @if($uploaded)
                                <a href="{{ $doc['file_url'] }}" target="_blank"
                                   class="btn btn-sm btn-light-primary" title="View">
                                    <i class="ki-duotone ki-eye fs-4">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                    </i>
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-light-danger"
                                        data-action="delete-doc"
                                        data-type="{{ $doc['type'] }}"
                                        data-label="{{ $doc['label'] }}"
                                        title="Delete">
                                    <i class="ki-duotone ki-trash fs-4">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                    </i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-light-warning"
                                        data-action="upload-doc"
                                        data-type="{{ $doc['type'] }}"
                                        data-label="{{ $doc['label'] }}"
                                        title="Replace">
                                    <i class="ki-duotone ki-arrows-circle fs-4">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </button>
                            @else
                                <button type="button"
                                        class="btn btn-sm btn-primary"
                                        data-action="upload-doc"
                                        data-type="{{ $doc['type'] }}"
                                        data-label="{{ $doc['label'] }}">
                                    <i class="ki-duotone ki-arrow-up fs-4 me-1">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    Upload
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>

{{-- Upload Modal --}}
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="fw-bold" id="uploadDocTitle">Upload Document</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadDocForm">
                @csrf
                <input type="hidden" name="doc_type" id="upload_doc_type">
                <div class="modal-body">

                    <div class="fv-row mb-5">
                        <label class="required fw-semibold mb-2">File</label>
                        <input type="file" class="form-control" name="file" id="upload_doc_file"
                               accept=".pdf,.jpg,.jpeg,.png" required />
                        <div class="text-muted fs-8 mt-1">PDF, JPG, or PNG. Max 10MB.</div>
                    </div>

                    <div class="fv-row mb-5">
                        <label class="fw-semibold mb-2">Expiry date (if applicable)</label>
                        <input type="date" class="form-control" name="expires_at" id="upload_doc_expires" />
                        <div class="text-muted fs-8 mt-1">
                            Leave blank if the document doesn't expire.
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadDocBtn">
                        <span class="indicator-label">Upload</span>
                        <span class="indicator-progress">
                            Uploading... <span class="spinner-border spinner-border-sm ms-2"></span>
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
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
          || '{{ csrf_token() }}';

let uploadModal;

document.addEventListener('DOMContentLoaded', function () {
    uploadModal = new bootstrap.Modal(document.getElementById('uploadDocModal'));
});

// ── Open upload modal ─────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-action="upload-doc"]');
    if (!btn) return;

    e.preventDefault();
    document.getElementById('upload_doc_type').value = btn.dataset.type;
    document.getElementById('uploadDocTitle').textContent = 'Upload ' + btn.dataset.label;
    document.getElementById('uploadDocForm').reset();
    document.getElementById('upload_doc_type').value = btn.dataset.type;
    uploadModal.show();
});

// ── Submit upload ─────────────────────────────────────────
document.getElementById('uploadDocForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const type = document.getElementById('upload_doc_type').value;
    const btn = document.getElementById('uploadDocBtn');
    window.showButtonSpinner(btn);

    const formData = new FormData(this);

    fetch(`{{ url('employer/compliance') }}/${type}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message || 'Document uploaded.');
            uploadModal.hide();
            setTimeout(() => window.location.reload(), 600);
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message || 'Upload failed.');
            window.showToast('error', msg);
        }
    })
    .catch(() => window.showToast('error', 'Upload failed.'))
    .finally(() => window.hideButtonSpinner(btn));
});

// ── Delete document ───────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-action="delete-doc"]');
    if (!btn) return;

    e.preventDefault();
    if (!confirm(`Delete "${btn.dataset.label}"? This cannot be undone.`)) return;

    fetch(`{{ url('employer/compliance') }}/${btn.dataset.type}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message || 'Deleted.');
            setTimeout(() => window.location.reload(), 500);
        } else {
            window.showToast('error', data.message || 'Delete failed.');
        }
    })
    .catch(() => window.showToast('error', 'Delete failed.'));
});

// ── Submit for review ─────────────────────────────────────
document.getElementById('submitComplianceBtn')?.addEventListener('click', function () {
    if (!confirm('Submit your documents for review? Our team will verify within 24 hours.')) return;

    const btn = this;
    window.showButtonSpinner(btn);

    fetch('{{ route('employer.compliance.submit') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message);
            setTimeout(() => window.location.reload(), 600);
        } else {
            window.showToast('error', data.message || 'Submit failed.');
        }
    })
    .catch(() => window.showToast('error', 'Submit failed.'))
    .finally(() => window.hideButtonSpinner(btn));
});
</script>
@endpush