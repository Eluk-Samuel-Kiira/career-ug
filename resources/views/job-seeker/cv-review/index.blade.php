@extends('layouts.admin')

@section('title', 'Submit CV for Review')
@section('page_title', 'Submit CV for Review')

@section('content')
<div class="container py-6">

    {{-- Price banner --}}
    <div class="card card-flush bg-light-primary mb-6 border-0">
        <div class="card-body py-5 d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="fw-bold mb-1">{{ $quote['service']['name'] }}</h4>
                <div class="text-muted fs-7">Turnaround: {{ $quote['service']['turnaround_label'] }}</div>
            </div>
            <div class="text-end">
                <div class="text-muted fs-7">Price in your region</div>
                <div class="fs-2 fw-bold text-primary">
                    {{ $quote['price']['is_free'] ? 'FREE' : $quote['price']['formatted'] }}
                </div>
            </div>
        </div>
    </div>

    <form id="cvReviewForm" enctype="multipart/form-data">
        @csrf

        {{-- CV picker --}}
        <div class="card card-flush mb-6">
            <div class="card-header"><h3 class="card-title">1. Choose Your CV</h3></div>
            <div class="card-body">
                @if(!empty($cvFiles))
                <label class="fw-semibold mb-3">Use one of your existing CVs</label>
                <div class="row g-3 mb-5">
                    @foreach($cvFiles as $cv)
                    <div class="col-md-6">
                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex align-items-center p-4 w-100 text-start existing-cv-option">
                            <input type="radio" name="existing_cv_path" value="{{ $cv['path'] }}" class="form-check-input me-3" />
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $cv['original_name'] }}</div>
                                <div class="text-muted fs-8">
                                    {{ isset($cv['size']) ? round($cv['size'] / 1024, 1) . ' KB' : '' }}
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

                <label class="fw-semibold mb-3">Upload a new CV</label>
                <div class="border border-2 border-dashed border-gray-300 rounded-3 p-8 text-center" id="uploadDrop">
                    <input type="file" id="cvFileInput" name="cv_file" accept=".pdf,.doc,.docx" class="d-none" />
                    <i class="ki-duotone ki-file-up fs-3x text-primary mb-3 d-block">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <div class="fw-bold mb-2">Click to upload or drag & drop</div>
                    <div class="text-muted fs-7">PDF, DOC, or DOCX — up to 10MB</div>
                    <div class="mt-3 fw-semibold text-primary" id="cvFileName"></div>
                </div>
            </div>
        </div>

        {{-- Target job --}}
        <div class="card card-flush mb-6">
            <div class="card-header"><h3 class="card-title">2. Target Job (optional but recommended)</h3></div>
            <div class="card-body">
                <div class="fv-row mb-5">
                    <label class="fw-semibold mb-2">Job Title You're Applying For</label>
                    <input type="text" name="target_job_title" class="form-control form-control-lg"
                           placeholder="e.g., Senior Software Engineer" />
                </div>
                <div class="fv-row">
                    <label class="fw-semibold mb-2">Job Description (paste the posting)</label>
                    <textarea name="target_job_description" rows="6" class="form-control form-control-lg"
                              placeholder="Paste the full job description here for a targeted review..."></textarea>
                    <div class="text-muted fs-7 mt-1">
                        Leave blank for a general CV quality review.
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="d-flex justify-content-end gap-3">
            <a href="{{ route('cv-review.index') }}" class="btn btn-light btn-lg">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg px-8" id="submitBtn">
                <span class="indicator-label">
                    <i class="ki-duotone ki-rocket fs-3 me-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    Submit CV for Review
                </span>
                <span class="indicator-progress">
                    Analyzing your CV...
                    <span class="spinner-border spinner-border-sm ms-2"></span>
                </span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const fileInput = document.getElementById('cvFileInput');
    const drop = document.getElementById('uploadDrop');
    const fileName = document.getElementById('cvFileName');

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
            fileName.textContent = e.dataTransfer.files[0].name;
            clearExistingRadio();
        }
    });

    fileInput.addEventListener('change', function () {
        if (this.files.length) {
            fileName.textContent = this.files[0].name;
            clearExistingRadio();
        }
    });

    document.querySelectorAll('input[name="existing_cv_path"]').forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                fileInput.value = '';
                fileName.textContent = '';
            }
        });
    });

    function clearExistingRadio() {
        document.querySelectorAll('input[name="existing_cv_path"]').forEach(r => r.checked = false);
    }

    // Submit
    document.getElementById('cvReviewForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        window.showButtonSpinner(btn);

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
                window.showToast('success', data.message, 'Success');
                setTimeout(() => {
                    window.location.href = '/cv-review/' + data.request.uuid;
                }, 1200);
            } else {
                const msg = data.errors
                    ? Object.values(data.errors).flat().join('\n')
                    : (data.message || 'Failed to submit');
                window.showToast('error', msg, 'Error');
            }
        })
        .catch(err => {
            console.error(err);
            window.showToast('error', 'Something went wrong. Please try again.', 'Error');
        })
        .finally(() => window.hideButtonSpinner(btn));
    });
});
</script>
@endpush