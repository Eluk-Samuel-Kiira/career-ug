@extends('layouts.admin')

@section('title', 'Submit CV for Review')
@section('page_title', 'Submit CV for Review')

@section('content')
@php
    $isAvailable = is_array($quote)
        && ($quote['available'] ?? false) === true
        && isset($quote['service'], $quote['price']);

    $service = $isAvailable ? $quote['service'] : null;
    $price   = $isAvailable ? $quote['price']   : null;
@endphp

<div class="container py-4 py-md-6">

    @if(!$isAvailable)
        {{-- Unavailable --}}
        <div class="alert alert-warning d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
            <i class="ki-duotone ki-information-5 fs-2x flex-shrink-0">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            <div class="flex-grow-1">
                <div class="fw-bold">CV Review is not available</div>
                <div class="text-muted fs-7">
                    {{ $quote['message'] ?? 'This service is not available in your region yet.' }}
                </div>
            </div>
            <a href="{{ route('cv-review.index') }}" class="btn btn-light w-100 w-sm-auto">Go Back</a>
        </div>
    @else

    {{-- ── PRICE BANNER ──────────────────────────────────────── --}}
    <div class="card card-flush bg-light-primary mb-4 mb-md-6 border-0">
        <div class="card-body py-4 py-md-5 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
            <div class="min-w-0">
                <h4 class="fw-bold mb-1">{{ $service['name'] }}</h4>
                <div class="text-muted fs-7">
                    <i class="ki-duotone ki-time fs-7 me-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    Turnaround: {{ $service['turnaround_label'] }}
                </div>
            </div>
            <div class="text-start text-sm-end">
                <div class="text-muted fs-8">Price in your region</div>
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

        {{-- ── STEP 1: CV PICKER ─────────────────────────────── --}}
        <div class="card card-flush mb-4 mb-md-6">
            <div class="card-header">
                <h3 class="card-title fs-5 fs-md-3">
                    <span class="badge badge-circle badge-primary me-2">1</span>
                    Choose Your CV
                </h3>
            </div>
            <div class="card-body pt-2 pt-md-4">

                @if(!empty($cvFiles))
                    <label class="fw-semibold mb-3 d-block fs-7 fs-md-6">Use one of your existing CVs</label>
                    <div class="row g-2 g-md-3 mb-4 mb-md-5">
                        @foreach($cvFiles as $cv)
                            <div class="col-12 col-md-6">
                                <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex align-items-center p-3 p-md-4 w-100 text-start existing-cv-option">
                                    <input type="radio"
                                           name="existing_cv_path"
                                           value="{{ $cv['path'] ?? '' }}"
                                           class="form-check-input me-3 flex-shrink-0 mt-0" />
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-bold text-truncate fs-7 fs-md-6">
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

                    <div class="separator separator-dashed my-4 my-md-5">
                        <span class="text-muted px-3 bg-white fs-7">OR</span>
                    </div>
                @endif

                <label class="fw-semibold mb-3 d-block fs-7 fs-md-6">Upload a new CV</label>
                <div class="border border-2 border-dashed border-gray-300 rounded-3 p-5 p-md-8 text-center cursor-pointer"
                     id="uploadDrop">
                    <input type="file" id="cvFileInput" name="cv_file"
                           accept=".pdf,.doc,.docx" class="d-none" />
                    <i class="ki-duotone ki-file-up fs-2x fs-md-3x text-primary mb-2 mb-md-3 d-block">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <div class="fw-bold mb-1 mb-md-2 fs-7 fs-md-6">Click to upload or drag &amp; drop</div>
                    <div class="text-muted fs-8 fs-md-7">PDF, DOC, or DOCX &mdash; up to 10MB</div>
                    <div class="mt-2 mt-md-3 fw-semibold text-primary d-none text-truncate fs-7" id="cvFileName"></div>
                </div>
            </div>
        </div>

        {{-- ── STEP 2: TARGET JOB ────────────────────────────── --}}
        <div class="card card-flush mb-4 mb-md-6">
            <div class="card-header">
                <h3 class="card-title fs-5 fs-md-3">
                    <span class="badge badge-circle badge-primary me-2">2</span>
                    Target Job
                    <span class="text-muted fw-normal fs-8 fs-md-7 ms-2 d-none d-sm-inline">(optional but recommended)</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="fv-row mb-4 mb-md-5">
                    <label class="fw-semibold mb-2 fs-7 fs-md-6">Job Title You're Applying For</label>
                    <input type="text" name="target_job_title"
                           class="form-control form-control-lg"
                           placeholder="e.g., Senior Software Engineer" />
                </div>
                <div class="fv-row">
                    <label class="fw-semibold mb-2 fs-7 fs-md-6">Job Description</label>
                    <textarea name="target_job_description" rows="5"
                              class="form-control form-control-lg"
                              placeholder="Paste the full job description here for a targeted review..."></textarea>
                    <div class="text-muted fs-8 fs-md-7 mt-1">
                        Leave blank for a general CV quality review.
                    </div>
                </div>
            </div>
        </div>

        {{-- ── STEP 3: SUBMIT ────────────────────────────────── --}}
        <div class="card card-flush bg-light-secondary">
            <div class="card-body d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3">
                <div class="text-center text-sm-start">
                    <div class="fw-bold fs-6">Ready when you are</div>
                    <div class="text-muted fs-8 fs-md-7">
                        You'll get an instant free AI gap review before paying anything.
                    </div>
                </div>
                <div class="d-flex flex-column-reverse flex-sm-row gap-2 gap-sm-3">
                    <a href="{{ route('cv-review.index') }}" class="btn btn-light btn-lg w-100 w-sm-auto">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg px-6 px-sm-8 w-100 w-sm-auto" id="submitBtn">
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

@push('styles')
<style>
    /* Full-width on mobile, auto on sm+ */
    @media (max-width: 575.98px) {
        .w-sm-auto { width: 100% !important; }
    }
    @media (min-width: 576px) {
        .w-sm-auto { width: auto !important; }
    }
    .cursor-pointer { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
              || '{{ csrf_token() }}';

    const fileInput = document.getElementById('cvFileInput');
    const drop      = document.getElementById('uploadDrop');
    const fileName  = document.getElementById('cvFileName');
    const form      = document.getElementById('cvReviewForm');

    if (!form || !drop || !fileInput) return;

    // ── File picker ──────────────────────────────────────────
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
            fileName.textContent = '';
        }
    });

    function showFileName(name) {
        fileName.textContent = '📎 ' + name;
        fileName.classList.remove('d-none');
    }

    // ── Existing CV radios ──────────────────────────────────
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

    // ── Submit ──────────────────────────────────────────────
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
                    window.showToast('success', data.message || 'CV received. Starting AI review…', 'Success');
                }
                const uuid = data.request?.uuid;
                setTimeout(() => {
                    window.location.href = uuid
                        ? '{{ url('cv-review') }}/' + uuid + '?auto_review=1'
                        : '{{ route('cv-review.index') }}';
                }, 900);
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