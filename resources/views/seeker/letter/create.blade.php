@extends('layouts.admin')

@section('title', 'Generate Letter')
@section('page_title', 'Generate Cover / Application Letter')

@section('content')
<div class="container py-6">
    <div class="card card-flush">
        <div class="card-body p-8">

            {{-- Stepper --}}
            <div class="d-flex justify-content-between mb-8">
                @foreach(['CV', 'Job', 'Type & Pay'] as $i => $label)
                    <div class="flex-grow-1 text-center step-indicator" data-step="{{ $i + 1 }}">
                        <div class="step-circle mx-auto mb-2">{{ $i + 1 }}</div>
                        <div class="fs-7 fw-semibold text-muted">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            {{-- ── STEP 1: CV ── --}}
            <div class="step-panel" data-step="1">
                <h3 class="fw-bold mb-4">Choose the CV to use</h3>

                @if(!empty($cvFiles))
                    <div class="d-flex flex-column gap-3 mb-4">
                        @foreach($cvFiles as $i => $cv)
                            <label class="d-flex align-items-center gap-3 p-4 bg-light rounded border border-gray-300 cursor-pointer">
                                <input type="radio" name="cv_choice" value="existing:{{ $cv['path'] }}"
                                       class="form-check-input" {{ $i === 0 ? 'checked' : '' }}>
                                <i class="ki-duotone ki-file fs-2 text-primary">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $cv['original_name'] ?? 'CV' }}</div>
                                    <div class="text-muted fs-7">
                                        @if(!empty($cv['size'])){{ round($cv['size'] / 1024, 2) }} KB @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach

                        <label class="d-flex align-items-center gap-3 p-4 bg-light rounded border border-gray-300 cursor-pointer">
                            <input type="radio" name="cv_choice" value="uploaded" class="form-check-input">
                            <i class="ki-duotone ki-folder-up fs-2 text-warning">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Upload a new CV for this letter only</div>
                                <div class="text-muted fs-7">PDF or Word, up to 5MB. Not saved to your profile.</div>
                            </div>
                        </label>
                    </div>
                @else
                    <div class="alert alert-light-info mb-4">
                        You have no CV on file. Upload one to continue.
                    </div>
                    <input type="radio" name="cv_choice" value="uploaded" class="form-check-input d-none" checked>
                @endif

                <div id="uploadBox" class="d-none mb-4">
                    <input type="file" id="cv_file" name="cv_file"
                           class="form-control" accept=".pdf,.doc,.docx">
                    <div class="text-muted fs-7 mt-2">Max 5MB. This CV is not stored on your profile.</div>
                </div>
            </div>

            {{-- ── STEP 2: Job ── --}}
            <div class="step-panel d-none" data-step="2">
                <h3 class="fw-bold mb-4">Which job are you targeting?</h3>

                <div class="d-flex flex-wrap gap-4 mb-6">
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="job_source" value="db" checked>
                        <span class="form-check-label">Search our jobs</span>
                    </label>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="job_source" value="manual">
                        <span class="form-check-label">Enter title & company</span>
                    </label>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="job_source" value="paste">
                        <span class="form-check-label">Paste full job description</span>
                    </label>
                </div>

                {{-- DB search --}}
                <div id="jobDbPanel">
                    <label class="fw-semibold fs-7 mb-2">Search job title or company</label>
                    <select id="job_search" class="form-select form-select-solid" style="width:100%"></select>
                    <input type="hidden" id="job_post_id" name="job_post_id">
                </div>

                <style>
                    /* Container — matches form-select-solid, centers content vertically */
                    #job_search + .select2-container .select2-selection--single {
                        display: flex;
                        align-items: center;
                        height: 44px;
                        padding: 0 40px 0 12px; /* right padding leaves room for the arrow */
                        border: 1px solid var(--bs-gray-300);
                        border-radius: 0.475rem;
                        background-color: var(--bs-gray-100);
                        transition: border-color .15s ease, background-color .15s ease;
                    }

                    #job_search + .select2-container .select2-selection--single:hover,
                    #job_search + .select2-container.select2-container--focus .select2-selection--single {
                        border-color: var(--bs-primary);
                        background-color: var(--bs-white);
                    }

                    /* Selected text — normalize Select2's line-height and paddings */
                    #job_search + .select2-container .select2-selection--single .select2-selection__rendered {
                        line-height: normal !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        color: var(--bs-gray-800);
                        font-size: 0.95rem;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        width: 100%;
                    }

                    /* Placeholder */
                    #job_search + .select2-container .select2-selection--single .select2-selection__placeholder {
                        color: var(--bs-gray-500);
                        line-height: normal !important;
                    }

                    /* Arrow — vertically centered, sized to the box */
                    #job_search + .select2-container .select2-selection--single .select2-selection__arrow {
                        height: 100% !important;
                        top: 0;
                        right: 8px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 24px;
                    }

                    /* Arrow glyph color on hover/focus */
                    #job_search + .select2-container.select2-container--focus .select2-selection--single .select2-selection__arrow b,
                    #job_search + .select2-container .select2-selection--single:hover .select2-selection__arrow b {
                        border-color: var(--bs-primary) transparent transparent transparent;
                    }
                </style>

                {{-- Manual --}}
                <div id="jobManualPanel" class="d-none row g-4">
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Job Title</label>
                        <input type="text" id="job_title_manual" name="job_title_manual" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Company Name</label>
                        <input type="text" id="company_name_manual" name="company_name_manual" class="form-control">
                    </div>
                </div>

                {{-- Paste --}}
                <div id="jobPastePanel" class="d-none row g-4">
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Job Title</label>
                        <input type="text" id="job_title_paste" name="job_title_paste" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Company Name</label>
                        <input type="text" id="company_name_paste" name="company_name_paste" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="fw-semibold fs-7 mb-2">Full Job Description</label>
                        <textarea id="job_description_paste" name="job_description_paste"
                                  class="form-control" rows="8"
                                  placeholder="Paste the full job description here..."></textarea>
                    </div>
                </div>
            </div>

            {{-- ── STEP 3: Type & Pay ── --}}
            <div class="step-panel d-none" data-step="3">
                <h3 class="fw-bold mb-4">Letter type</h3>

                <div class="d-flex gap-4 mb-6">
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="letter_type" value="cover" checked>
                        <span class="form-check-label">Cover Letter</span>
                    </label>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="letter_type" value="application">
                        <span class="form-check-label">Application Letter</span>
                    </label>
                </div>

                <div class="alert alert-light-primary d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold fs-4">One-time payment</div>
                        <div class="text-muted">UGX 3,000 per letter. This is not a subscription.</div>
                    </div>
                    <div class="fs-2 fw-bold text-primary">UGX 3,000</div>
                </div>

                <div class="alert alert-light-warning">
                    Payment gateway integration is coming soon. Clicking Pay now will
                    generate your letter immediately for testing.
                </div>
            </div>

            {{-- Navigation --}}
            <div class="d-flex justify-content-between mt-8 pt-6 border-top">
                <button type="button" class="btn btn-light" id="prevBtn" disabled>Back</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                <button type="button" class="btn btn-success d-none" id="submitBtn">
                    Pay UGX 3,000 & Generate
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
        width: 40px; height: 40px; line-height: 40px;
        border-radius: 50%; background: #e4e6ef;
        color: #7e8299; font-weight: 700; text-align: center;
    }
    .step-indicator.active .step-circle { background: #009ef7; color: #fff; }
    .step-indicator.done .step-circle { background: #50cd89; color: #fff; }
    .cursor-pointer { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    // ─────────────────────────────────────────────────────────────
    // State — single source of truth
    // ─────────────────────────────────────────────────────────────
    const state = {
        step: 1,
        // Step 1
        cvSource: null,           // 'existing' | 'uploaded'
        cvPath: null,             // string when source = existing
        cvFile: null,             // File when source = uploaded
        // Step 2
        jobSource: null,          // 'db' | 'manual' | 'paste'
        jobPostId: null,
        jobTitle: '',
        companyName: '',
        jobDescription: '',
        // Step 3
        letterType: 'cover',
    };

    const els = {
        panels:     document.querySelectorAll('.step-panel'),
        indicators: document.querySelectorAll('.step-indicator'),
        prevBtn:    document.getElementById('prevBtn'),
        nextBtn:    document.getElementById('nextBtn'),
        submitBtn:  document.getElementById('submitBtn'),
    };

    // ─────────────────────────────────────────────────────────────
    // Render
    // ─────────────────────────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────
    // Step 1 — CV
    // ─────────────────────────────────────────────────────────────
    function bindCvStep() {
        const radios = document.querySelectorAll('input[name="cv_choice"]');
        const fileInput = document.getElementById('cv_file');

        radios.forEach(r => {
            r.addEventListener('change', () => {
                state.cvSource = r.value === 'uploaded' ? 'uploaded' : 'existing';
                state.cvPath   = r.value.startsWith('existing:') ? r.value.slice('existing:'.length) : null;

                // Toggle upload box
                document.getElementById('uploadBox').classList.toggle('d-none', state.cvSource !== 'uploaded');

                // If we switched away from upload, keep the file in state but
                // don't force the user to re-pick if they switch back.
                if (state.cvSource === 'existing') {
                    state.cvFile = null;
                    if (fileInput) fileInput.value = '';
                }
            });
        });

        fileInput?.addEventListener('change', function () {
            state.cvFile = this.files?.[0] || null;
        });

        // Initialize from whatever is pre-checked in the blade
        const checked = document.querySelector('input[name="cv_choice"]:checked');
        if (checked) {
            checked.dispatchEvent(new Event('change'));
        }
    }

    function validateStep1() {
        if (!state.cvSource) {
            toast('error', 'Please choose which CV to use.');
            return false;
        }
        if (state.cvSource === 'existing') {
            if (!state.cvPath) {
                toast('error', 'Please choose one of your existing CVs.');
                return false;
            }
            return true;
        }
        // uploaded
        if (!state.cvFile) {
            toast('error', 'Please choose a file to upload.');
            return false;
        }
        if (state.cvFile.size > 5 * 1024 * 1024) {
            toast('error', 'The CV must be under 5MB.');
            return false;
        }
        const ext = state.cvFile.name.split('.').pop().toLowerCase();
        if (!['pdf', 'doc', 'docx'].includes(ext)) {
            toast('error', 'Only PDF, DOC, or DOCX files are accepted.');
            return false;
        }
        return true;
    }

    // ─────────────────────────────────────────────────────────────
    // Step 2 — Job
    // ─────────────────────────────────────────────────────────────
    function bindJobStep() {
        document.querySelectorAll('input[name="job_source"]').forEach(r => {
            r.addEventListener('change', () => {
                state.jobSource = r.value;
                document.getElementById('jobDbPanel').classList.toggle('d-none',     r.value !== 'db');
                document.getElementById('jobManualPanel').classList.toggle('d-none', r.value !== 'manual');
                document.getElementById('jobPastePanel').classList.toggle('d-none',  r.value !== 'paste');
            });
        });

        const checked = document.querySelector('input[name="job_source"]:checked');
        if (checked) {
            state.jobSource = checked.value;
        }

        // Select2 remote search
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
                        results: (data.data || []).map(j => ({
                            id: j.id,
                            text: j.label,
                        })),
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

        // Track manual / paste inputs as the user types
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
    }

    function validateStep2() {
        if (!state.jobSource) {
            toast('error', 'Please choose how you want to identify the job.');
            return false;
        }

        if (state.jobSource === 'db') {
            if (!state.jobPostId) {
                toast('error', 'Please pick a job from the list.');
                return false;
            }
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

    // ─────────────────────────────────────────────────────────────
    // Step 3 — Type & Pay
    // ─────────────────────────────────────────────────────────────
    function bindLetterType() {
        document.querySelectorAll('input[name="letter_type"]').forEach(r => {
            r.addEventListener('change', () => {
                state.letterType = r.value;
            });
        });
    }

    function validateStep3() {
        if (!state.letterType) {
            toast('error', 'Please choose the letter type.');
            return false;
        }
        return true;
    }

    // ─────────────────────────────────────────────────────────────
    // Navigation
    // ─────────────────────────────────────────────────────────────
    function goNext() {
        const ok = state.step === 1 ? validateStep1()
                 : state.step === 2 ? validateStep2()
                 : true;
        if (!ok) return;
        if (state.step < 3) {
            state.step++;
            render();
        }
    }

    function goPrev() {
        if (state.step > 1) {
            state.step--;
            render();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Submit — reads from state, never from the DOM
    // ─────────────────────────────────────────────────────────────
    async function submit() {
        if (!validateStep1() || !validateStep2() || !validateStep3()) return;

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

        els.submitBtn.disabled = true;
        els.submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating request...';

        try {
            const res  = await fetch('{{ route("letters.store") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            const body = await res.json();

            if (!res.ok || !body.success) {
                toast('error', body.message || 'Could not create the letter request.');
                restoreSubmitButton();
                return;
            }

            const uuid = body.data.uuid;
            els.submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing payment...';

            const payRes  = await fetch(`{{ url('letters') }}/${uuid}/pay`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const payBody = await payRes.json();

            if (!payBody.success) {
                toast('error', payBody.message || 'Payment failed.');
                restoreSubmitButton();
                return;
            }

            window.location.href = `{{ url('letters') }}/${uuid}`;

        } catch (e) {
            toast('error', 'Network error: ' + e.message);
            restoreSubmitButton();
        }
    }

    function restoreSubmitButton() {
        els.submitBtn.disabled = false;
        els.submitBtn.innerHTML = 'Pay UGX 3,000 & Generate';
    }

    // ─────────────────────────────────────────────────────────────
    // Toasts
    // ─────────────────────────────────────────────────────────────
    function toast(type, msg) {
        if (typeof window.showToast === 'function') {
            window.showToast(type, msg);
        } else {
            alert(msg);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Boot
    // ─────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        bindCvStep();
        bindJobStep();
        bindLetterType();

        els.prevBtn?.addEventListener('click', goPrev);
        els.nextBtn?.addEventListener('click', goNext);
        els.submitBtn?.addEventListener('click', submit);

        render();
    });
})();
</script>
@endpush