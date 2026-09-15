@extends('layouts.admin')

@section('title', 'Submit CV for Review')
@section('page_title', 'Submit CV for Review')

@section('content')
@php
    // Guard against missing/partial quote payload
    $isAvailable = is_array($quote)
        && ($quote['available'] ?? false) === true
        && isset($quote['service'], $quote['price']);

    $service = $isAvailable ? $quote['service'] : null;
    $price   = $isAvailable ? $quote['price']   : null;
@endphp

<div class="container py-6">

    @if(!$isAvailable)
        {{-- Unavailable state --}}
        <div class="alert alert-warning d-flex align-items-center">
            <i class="ki-duotone ki-information-5 fs-2x me-4">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            <div>
                <div class="fw-bold">CV Review is not available</div>
                <div class="text-muted">
                    {{ $quote['message'] ?? 'This service is not available in your region yet.' }}
                </div>
            </div>
            <a href="{{ route('cv-review.index') }}" class="btn btn-light ms-auto">Go Back</a>
        </div>
    @else

    {{-- ── PRICE BANNER ──────────────────────────────────────────── --}}
    <div class="card card-flush bg-light-primary mb-6 border-0">
        <div class="card-body py-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1">{{ $service['name'] }}</h4>
                <div class="text-muted fs-7">
                    <i class="ki-duotone ki-time fs-6 me-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    Turnaround: {{ $service['turnaround_label'] }}
                </div>
            </div>
            <div class="text-end">
                <div class="text-muted fs-7">Price in your region</div>
                <div class="fs-2 fw-bold text-primary">
                    {{ ($price['is_free'] ?? false) ? 'FREE' : $price['formatted'] }}
                </div>
                @if(!empty($price['is_fallback']))
                    <div class="text-muted fs-8">Standard USD pricing</div>
                @endif
            </div>
        </div>
    </div>

    <form id="cvReviewForm" enctype="multipart/form-data">
        @csrf

        {{-- ── STEP 1: CV PICKER ─────────────────────────────────── --}}
        <div class="card card-flush mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge badge-circle badge-primary me-2">1</span>
                    Choose Your CV
                </h3>
            </div>
            <div class="card-body">

                @if(!empty($cvFiles))
                    <label class="fw-semibold mb-3 d-block">Use one of your existing CVs</label>
                    <div class="row g-3 mb-5">
                        @foreach($cvFiles as $cv)
                            <div class="col-md-6">
                                <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex align-items-center p-4 w-100 text-start existing-cv-option">
                                    <input type="radio"
                                           name="existing_cv_path"
                                           value="{{ $cv['path'] ?? '' }}"
                                           class="form-check-input me-3 flex-shrink-0" />
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-bold text-truncate">
                                            {{ $cv['original_name'] ?? 'CV' }}
                                        </div>
                                        <div class="text-muted fs-8">
                                            @if(!empty($cv['size']))
                                                {{ round($cv['size'] / 1024, 1) }} KB
                                            @endif
                                            @if(!empty($cv['uploaded_at']))
                                                &middot; {{ \Carbon\Carbon::parse($cv['uploaded_at'])->format('M d, Y') }}
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="separator separator-dashed my-5">
                        <span class="text-muted px-3 bg-white">OR</span>
                    </div>
                @endif

                <label class="fw-semibold mb-3 d-block">Upload a new CV</label>
                <div class="border border-2 border-dashed border-gray-300 rounded-3 p-8 text-center cursor-pointer"
                     id="uploadDrop">
                    <input type="file" id="cvFileInput" name="cv_file"
                           accept=".pdf,.doc,.docx" class="d-none" />
                    <i class="ki-duotone ki-file-up fs-3x text-primary mb-3 d-block">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <div class="fw-bold mb-2">Click to upload or drag &amp; drop</div>
                    <div class="text-muted fs-7">PDF, DOC, or DOCX &mdash; up to 10MB</div>
                    <div class="mt-3 fw-semibold text-primary d-none" id="cvFileName"></div>
                </div>
            </div>
        </div>

        {{-- ── STEP 2: TARGET JOB ────────────────────────────────── --}}
        <div class="card card-flush mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge badge-circle badge-primary me-2">2</span>
                    Target Job
                    <span class="text-muted fw-normal fs-7 ms-2">(optional but recommended)</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="fv-row mb-5">
                    <label class="fw-semibold mb-2">Job Title You're Applying For</label>
                    <input type="text" name="target_job_title"
                           class="form-control form-control-lg"
                           placeholder="e.g., Senior Software Engineer" />
                </div>
                <div class="fv-row">
                    <label class="fw-semibold mb-2">Job Description</label>
                    <textarea name="target_job_description" rows="6"
                              class="form-control form-control-lg"
                              placeholder="Paste the full job description here for a targeted review..."></textarea>
                    <div class="text-muted fs-7 mt-1">
                        Leave blank for a general CV quality review.
                    </div>
                </div>
            </div>
        </div>

        {{-- ── STEP 3: SUBMIT ────────────────────────────────────── --}}
        <div class="card card-flush bg-light-secondary">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="fw-bold">Ready when you are</div>
                    <div class="text-muted fs-7">
                        You'll get an instant free AI gap review before paying anything.
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <a href="{{ route('cv-review.index') }}" class="btn btn-light btn-lg">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg px-8" id="submitBtn">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-rocket fs-3 me-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Get My Free AI Review
                        </span>
                        <span class="indicator-progress">
                            Analyzing your CV...
                            <span class="spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
              || '{{ csrf_token() }}';

    const fileInput = document.getElementById('cvFileInput');
    const drop      = document.getElementById('uploadDrop');
    const fileName  = document.getElementById('cvFileName');
    const form      = document.getElementById('cvReviewForm');

    // If unavailable, no form on the page — bail
    if (!form || !drop || !fileInput) return;

    // ── File picker ──────────────────────────────────────────────
    drop.addEventListener('click', () => fileInput.click());

    drop.addEventListener('dragover', e => {
        e.preventDefault();
        drop.classList.add('border-primary');
    });
    drop.addEventListener('dragleave', () => drop.classList.remove('border-primary'));

    drop.addEventListener('drop', e => {
        e.preventDefault();
        drop.classList.remove('border-primary');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            showFileName(e.dataTransfer.files[0].name);
            clearExistingRadios();
        }
    });

    fileInput.addEventListener('change', function () {
        if (this.files.length) {
            showFileName(this.files[0].name);
            clearExistingRadios();
        } else {
            fileName.classList.add('d-none');
        }
    });

    function showFileName(name) {
        fileName.textContent = '📎 ' + name;
        fileName.classList.remove('d-none');
    }

    // ── Existing CV radios ───────────────────────────────────────
    document.querySelectorAll('input[name="existing_cv_path"]').forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                fileInput.value = '';
                fileName.classList.add('d-none');
                fileName.textContent = '';
            }
        });
    });

    function clearExistingRadios() {
        document.querySelectorAll('input[name="existing_cv_path"]').forEach(r => {
            r.checked = false;
        });
    }

    // ── Submit ───────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const hasFile = fileInput.files && fileInput.files.length > 0;
        const hasExisting = document.querySelector('input[name="existing_cv_path"]:checked');

        if (!hasFile && !hasExisting) {
            if (typeof window.showToast === 'function') {
                window.showToast('error', 'Please upload a CV or select one of your existing CVs.', 'Missing CV');
            } else {
                alert('Please upload a CV or select one of your existing CVs.');
            }
            return;
        }

        const btn = document.getElementById('submitBtn');
        if (typeof window.showButtonSpinner === 'function') {
            window.showButtonSpinner(btn);
        } else {
            btn.disabled = true;
        }

        const formData = new FormData(this);

        fetch('{{ route('cv-review.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof window.showToast === 'function') {
                    window.showToast('success', data.message, 'Success');
                }
                // Redirect to the detail page
                const uuid = data.request?.uuid;
                setTimeout(() => {
                    window.location.href = uuid
                        ? '{{ url('cv-review') }}/' + uuid
                        : '{{ route('cv-review.index') }}';
                }, 1000);
            } else {
                const msg = data.errors
                    ? Object.values(data.errors).flat().join('\n')
                    : (data.message || 'Failed to submit');
                if (typeof window.showToast === 'function') {
                    window.showToast('error', msg, 'Error');
                } else {
                    alert(msg);
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof window.showToast === 'function') {
                window.showToast('error', 'Something went wrong. Please try again.', 'Error');
            } else {
                alert('Something went wrong.');
            }
        })
        .finally(() => {
            if (typeof window.hideButtonSpinner === 'function') {
                window.hideButtonSpinner(btn);
            } else {
                btn.disabled = false;
            }
        });
    });
});
</script>
@endpush