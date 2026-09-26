@extends('layouts.admin')

@section('title', 'Post a Job')
@section('page_title', 'Post a Job')

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
    <li class="breadcrumb-item text-muted">New Job</li>
@endsection

@section('content')
@php
    $countryCode = $countryCode ?? 'XX';
@endphp

<div class="container py-6" style="max-width:900px;">

    <form id="jobSubmissionForm">
        @csrf
        <input type="hidden" name="service_key" id="selected_service_key">
        <input type="hidden" name="target_country" value="{{ $countryCode }}">

        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- STEP 1: PICK A PACKAGE                                --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <div class="card card-flush mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge badge-circle badge-primary me-2">1</span>
                    Choose a Package
                </h3>
            </div>
            <div class="card-body">

                @if(empty($packages))
                    <div class="alert alert-warning">
                        No packages are currently available in your region.
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($packages as $pkg)
                            @php
                                $meta = $pkg['meta'] ?? [];
                                $badge = $meta['badge'] ?? null;
                                $tagline = $meta['tagline'] ?? null;
                                $isFree = $pkg['price']['is_free'] ?? false;
                            @endphp

                            <div class="col-md-6">
                                <label class="package-card d-block h-100 p-5 border border-2 border-gray-300 rounded cursor-pointer position-relative"
                                       data-key="{{ $pkg['key'] }}">

                                    <input type="radio" name="package_pick" value="{{ $pkg['key'] }}"
                                           class="d-none package-radio" />

                                    @if($badge === 'popular')
                                        <span class="badge badge-warning position-absolute top-0 end-0 m-3">⭐ POPULAR</span>
                                    @elseif($badge === 'urgent')
                                        <span class="badge badge-danger position-absolute top-0 end-0 m-3">🔥 URGENT</span>
                                    @elseif($badge === 'enterprise')
                                        <span class="badge badge-dark position-absolute top-0 end-0 m-3">🏢 ENTERPRISE</span>
                                    @endif

                                    <div class="mb-3">
                                        <div class="fs-4 fw-bold mb-1">{{ $pkg['name'] }}</div>
                                        @if($tagline)
                                            <div class="text-muted fs-7">{{ $tagline }}</div>
                                        @endif
                                    </div>

                                    <div class="mb-4">
                                        <span class="fs-2 fw-bold text-primary">
                                            {{ $isFree ? 'FREE' : $pkg['price']['formatted'] }}
                                        </span>
                                    </div>

                                    <div class="text-muted fs-7">
                                        {{ $pkg['description'] }}
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- STEP 2: JOB DETAILS                                    --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <div class="card card-flush mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge badge-circle badge-primary me-2">2</span>
                    Tell Us About the Job
                </h3>
            </div>
            <div class="card-body">

                <div class="fv-row mb-6">
                    <label class="required fw-semibold mb-2">Job Title</label>
                    <input type="text" name="job_title" class="form-control form-control-lg"
                           placeholder="e.g., Senior Software Engineer"
                           required maxlength="255" />
                    <div class="text-muted fs-8 mt-1">
                        A clear, specific title — what would a job seeker search for?
                    </div>
                </div>

                <div class="fv-row">
                    <label class="required fw-semibold mb-2">Full Job Details</label>
                    <textarea name="content" rows="16" class="form-control font-monospace"
                              placeholder="Paste the full job posting here — description, responsibilities, qualifications, skills, how to apply, contact info. Our team will organize it into the proper format before publishing."
                              required minlength="30" maxlength="50000"></textarea>
                    <div class="d-flex justify-content-between mt-2">
                        <div class="text-muted fs-8">
                            Paste everything. We'll format it professionally for you.
                        </div>
                        <div class="text-muted fs-8">
                            <span id="contentCounter">0</span> / 50000 characters
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- STEP 3: SUBMIT                                         --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <div class="card card-flush bg-light-primary">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="fw-bold">Ready to submit?</div>
                    <div class="text-muted fs-7">
                        Our team reviews every job before publishing. You'll be notified on progress.
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <a href="{{ route('employer.jobs.index') }}" class="btn btn-light btn-lg">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg px-8" id="submitBtn">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-send fs-3 me-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Submit Job
                        </span>
                        <span class="indicator-progress">
                            Submitting... <span class="spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .package-card {
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .package-card:hover {
        border-color: #0d6efd !important;
        background: #f8f9fa;
    }
    .package-card.selected {
        border-color: #0d6efd !important;
        background: #eef6ff;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }
</style>
@endpush

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

document.addEventListener('DOMContentLoaded', function () {
    const contentField = document.querySelector('textarea[name="content"]');
    const counter = document.getElementById('contentCounter');

    contentField?.addEventListener('input', function () {
        counter.textContent = this.value.length;
    });

    // Package card selection
    document.querySelectorAll('.package-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.package-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            const key = this.dataset.key;
            document.getElementById('selected_service_key').value = key;
            this.querySelector('.package-radio').checked = true;
        });
    });
});

// Submit
document.getElementById('jobSubmissionForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const serviceKey = document.getElementById('selected_service_key').value;

    if (!serviceKey) {
        window.showToast('error', 'Please select a package first.');
        return;
    }

    const btn = document.getElementById('submitBtn');
    window.showButtonSpinner(btn);

    const formData = new FormData(this);
    const payload = {};
    formData.forEach((v, k) => payload[k] = v);

    fetch('{{ route('employer.jobs.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message || 'Submitted.');

            const uuid = data.submission?.uuid;
            const nextStep = data.next_step;

            setTimeout(() => {
                if (nextStep === 'payment' && uuid) {
                    window.location.href = '/employer/jobs/' + uuid;
                } else {
                    window.location.href = '{{ route('employer.jobs.index') }}';
                }
            }, 800);
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message || 'Submission failed.');
            window.showToast('error', msg);
        }
    })
    .catch(err => {
        console.error(err);
        window.showToast('error', 'Submission failed.');
    })
    .finally(() => window.hideButtonSpinner(btn));
});
</script>
@endpush