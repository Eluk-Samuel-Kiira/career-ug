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
@php
    $prefill       = $prefill ?? [];
    $prefillSource = in_array($prefill['job_source'] ?? null, ['db', 'manual', 'paste', 'none'], true)
                        ? $prefill['job_source']
                        : 'db';
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
            <a href="{{ route('cv-review.my') }}" class="btn btn-light w-100 w-sm-auto">Go Back</a>
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

                {{-- Source picker --}}
                <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 gap-sm-4 mb-4">
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
                        <span class="form-check-label fs-7 fs-md-6">Paste job post</span>
                    </label>
                    <label class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="job_source" value="none">
                        <span class="form-check-label fs-7 fs-md-6">Skip (general review)</span>
                    </label>
                </div>

                {{-- DB search --}}
                <div id="jobDbPanel">
                    <label class="fw-semibold mb-2 fs-7 fs-md-6">Search job title or company</label>
                    <select id="job_search" class="form-select form-select-solid" style="width:100%"></select>
                    <input type="hidden" id="job_post_id" name="job_post_id">
                    <div class="text-muted fs-8 mt-1">
                        We'll automatically pull the description, responsibilities, qualifications and skills
                        from the selected job to tailor your CV.
                    </div>
                </div>

                {{-- Manual --}}
                <div id="jobManualPanel" class="d-none row g-3">
                    <div class="col-12 col-md-6">
                        <label class="fw-semibold mb-2 fs-7 fs-md-6">Job Title</label>
                        <input type="text" id="job_title_manual" name="target_job_title" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="fw-semibold mb-2 fs-7 fs-md-6">Company Name</label>
                        <input type="text" id="company_name_manual" name="target_company_name" class="form-control">
                    </div>
                </div>

                {{-- Paste --}}
                <div id="jobPastePanel" class="d-none row g-3">
                    <div class="col-12 col-md-6">
                        <label class="fw-semibold mb-2 fs-7 fs-md-6">Job Title</label>
                        <input type="text" id="job_title_paste" name="target_job_title"
                            class="form-control"
                            value="{{ old('target_job_title', $prefill['target_job_title'] ?? '') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="fw-semibold mb-2 fs-7 fs-md-6">Company Name</label>
                        <input type="text" id="company_name_paste" name="target_company_name"
                            class="form-control"
                            value="{{ old('target_company_name', $prefill['target_company_name'] ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="fw-semibold mb-2 fs-7 fs-md-6">
                            Full Job Post
                            <span class="text-muted fw-normal fs-8">(description, responsibilities, qualifications, skills)</span>
                        </label>
                        <textarea id="job_description_paste" name="target_job_description" rows="8"
                            class="form-control">{{ old('target_job_description', $prefill['target_job_description'] ?? '') }}</textarea>
                        <div class="text-muted fs-8 mt-1">
                            Paste everything from the job advert: what the role does, what's required, and what skills they list.
                            This is what the AI reads when tailoring your CV.
                        </div>
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
                    <a href="{{ route('cv-review.my') }}" class="btn btn-light btn-lg w-100 w-sm-auto">Cancel</a>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
              || '{{ csrf_token() }}';

    const fileInput = document.getElementById('cvFileInput');
    const drop      = document.getElementById('uploadDrop');
    const fileName  = document.getElementById('cvFileName');
    const form      = document.getElementById('cvReviewForm');

    if (!form || !drop || !fileInput) return;

    // ── Server prefill ───────────────────────────────────────
    const SERVER_PREFILL_SOURCE = @json($prefillSource);

    // ── Job source switcher ──────────────────────────────────
    const dbPanel     = document.getElementById('jobDbPanel');
    const manualPanel = document.getElementById('jobManualPanel');
    const pastePanel  = document.getElementById('jobPastePanel');
    const jobPostIdIn = document.getElementById('job_post_id');

    function setPanelVisibility(source) {
        dbPanel.classList.toggle('d-none',     source !== 'db');
        manualPanel.classList.toggle('d-none', source !== 'manual');
        pastePanel.classList.toggle('d-none',  source !== 'paste');

        manualPanel.querySelectorAll('input, textarea, select')
            .forEach(el => el.disabled = source !== 'manual');
        pastePanel.querySelectorAll('input, textarea, select')
            .forEach(el => el.disabled = source !== 'paste');
        if (jobPostIdIn) jobPostIdIn.disabled = source !== 'db';

        // Sync radio states with the visible panel
        document.querySelectorAll('input[name="job_source"]').forEach(r => {
            r.checked = (r.value === source);
        });
    }

    document.querySelectorAll('input[name="job_source"]').forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) setPanelVisibility(this.value);
        });
    });

    // ── Apply prefill on load ───────────────────────────────
    // SERVER_PREFILL_SOURCE comes from the controller's $prefill['job_source'].
    // Falls back to 'db' when no query params were supplied.
    setPanelVisibility(SERVER_PREFILL_SOURCE || 'db');

    // ── Select2 for job search ──────────────────────────────
    if (window.jQuery && jQuery.fn.select2) {
        jQuery('#job_search').select2({
            placeholder: 'Type at least 2 characters...',
            minimumInputLength: 2,
            width: '100%',
            ajax: {
                url: '{{ route("cv-review.search-jobs") }}',
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({
                    results: (data.data || []).map(j => ({ id: j.id, text: j.label })),
                }),
            },
        }).on('select2:select', e => {
            if (jobPostIdIn) jobPostIdIn.value = e.params.data.id;
        }).on('select2:clear', () => {
            if (jobPostIdIn) jobPostIdIn.value = '';
        });
    }

    // ── File picker ─────────────────────────────────────────
    drop.addEventListener('click', () => fileInput.click());
    drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('border-primary'); });
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
        if (this.files.length) { showFileName(this.files[0].name); clearExistingRadios(); }
        else { fileName.classList.add('d-none'); fileName.textContent = ''; }
    });
    function showFileName(name) { fileName.textContent = '📎 ' + name; fileName.classList.remove('d-none'); }

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
        document.querySelectorAll('input[name="existing_cv_path"]').forEach(r => r.checked = false);
    }

    // ── Submit ──────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const hasFile     = fileInput.files && fileInput.files.length > 0;
        const hasExisting = document.querySelector('input[name="existing_cv_path"]:checked');
        const jobSource   = document.querySelector('input[name="job_source"]:checked')?.value;

        if (!hasFile && !hasExisting) {
            return toast('error', 'Please upload a CV or select one of your existing CVs.', 'Missing CV');
        }

        if (jobSource === 'db' && !jobPostIdIn?.value) {
            return toast('error', 'Please pick a job from the list, or choose another option.', 'Missing job');
        }
        if (jobSource === 'manual') {
            const t = document.getElementById('job_title_manual')?.value.trim();
            const c = document.getElementById('company_name_manual')?.value.trim();
            if (!t || !c) return toast('error', 'Please enter both job title and company.', 'Missing info');
        }
        if (jobSource === 'paste') {
            const t = document.getElementById('job_title_paste')?.value.trim();
            const d = document.getElementById('job_description_paste')?.value.trim();
            if (!t || !d) return toast('error', 'Please enter the job title and description.', 'Missing info');
        }

        const btn = document.getElementById('submitBtn');
        if (typeof window.showButtonSpinner === 'function') window.showButtonSpinner(btn);
        else btn.disabled = true;

        const formData = new FormData(this);

        fetch('{{ route('cv-review.store') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                toast('success', data.message || 'CV received. Starting AI review…', 'Success');
                const uuid = data.request?.uuid;
                setTimeout(() => {
                    window.location.href = uuid
                        ? '{{ url('cv-review') }}/' + uuid + '?auto_review=1'
                        : '{{ route('cv-review.my') }}';
                }, 900);
            } else {
                const msg = data.errors
                    ? Object.values(data.errors).flat().join('\n')
                    : (data.message || 'Failed to submit');
                toast('error', msg, 'Error');
            }
        })
        .catch(err => {
            console.error(err);
            toast('error', 'Something went wrong. Please try again.', 'Error');
        })
        .finally(() => {
            if (typeof window.hideButtonSpinner === 'function') window.hideButtonSpinner(btn);
            else btn.disabled = false;
        });
    });

    function toast(type, msg, title) {
        if (typeof window.showToast === 'function') window.showToast(type, msg, title);
        else alert(msg);
    }
});
</script>
@endpush