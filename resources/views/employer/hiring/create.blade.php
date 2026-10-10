@extends('layouts.admin')

@section('title', 'New Screening Run')
@section('page_title', 'Screen CVs with AI')

@section('content')
<div class="container py-6">

    <div class="card card-flush">
        <div class="card-header py-5">
            <h3 class="fw-bold mb-0">Upload a job description and CVs</h3>
        </div>

        <div class="card-body">

            <form id="runForm" enctype="multipart/form-data">

                <div class="row g-5">
                    {{-- Title --}}
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Run Title</label>
                        <input type="text" name="title" class="form-control"
                               placeholder="e.g. Senior Accountant - Oct 2026" required>
                    </div>

                    {{-- Job post picker --}}
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Or pick from your job posts</label>
                        <select name="job_post_id" id="jobPostSelect" class="form-select">
                            <option value="">Not using an existing post</option>
                        </select>
                        <div class="text-muted fs-7 mt-1" id="jobPostHint">
                            Loading your job posts...
                        </div>
                    </div>

                    {{-- Job description --}}
                    <div class="col-12" id="jdPasteWrap">
                        <label class="fw-semibold fs-7 mb-2">Job Description</label>
                        <textarea name="job_description" class="form-control" rows="8"
                                  placeholder="Paste the full job description here..."></textarea>
                        <div class="text-muted fs-7 mt-1">
                            You can also upload a document instead.
                        </div>
                    </div>

                    {{-- JD file --}}
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Or upload the JD as a file</label>
                        <input type="file" name="job_description_file" class="form-control"
                               accept=".pdf,.doc,.docx,.txt">
                    </div>

                    {{-- CV uploads --}}
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Upload CVs</label>
                        <input type="file" name="cvs[]" class="form-control"
                               accept=".pdf,.doc,.docx,.txt" multiple>
                        <div class="text-muted fs-7 mt-1">Up to 500 files. PDF, Word, TXT.</div>
                    </div>

                    {{-- Zip upload --}}
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Or upload a ZIP of CVs</label>
                        <input type="file" name="cvs_zip" class="form-control" accept=".zip">
                        <div class="text-muted fs-7 mt-1">All PDFs and Word docs inside are extracted.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-8 pt-6 border-top">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class="ki-duotone ki-rocket fs-3 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Start Screening
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Progress modal --}}
<div class="modal fade" id="progressModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h3 class="fw-bold mb-0">Screening in progress</h3>
            </div>
            <div class="modal-body text-center py-8">
                <div class="mb-5">
                    <i class="ki-duotone ki-abstract-26 fs-3x text-primary mb-3 d-block">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <div class="fw-bold fs-4 mb-2" id="progressStatus">Queued</div>
                    <div class="text-muted fs-7" id="progressSub">Reading CVs and scoring matches</div>
                </div>
                <div class="progress h-10px mb-3">
                    <div class="progress-bar bg-primary" id="progressBar" style="width:0%"></div>
                </div>
                <div class="d-flex justify-content-between text-muted fs-7">
                    <span id="progressCount">0 / 0</span>
                    <span id="progressPercent">0%</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
(function () {
    'use strict';

    const CSRF = '{{ csrf_token() }}';
    const MY_JOB_POSTS_URL = '{{ route("employer.hiring.my-job-posts") }}';
    const STORE_URL        = '{{ route("employer.hiring.store") }}';
    const STATUS_BASE      = '{{ url("employer/hiring") }}';
    const SHOW_BASE        = '{{ url("employer/hiring") }}';

    let pollTimer = null;

    // ─────────────────────────────────────────────────────────────
    // Load the employer's job posts into the picker
    // ─────────────────────────────────────────────────────────────
    async function loadJobPosts() {
        const select = document.getElementById('jobPostSelect');
        const hint   = document.getElementById('jobPostHint');

        if (!select) {
            console.warn('[HiringRun] jobPostSelect not found in DOM');
            return;
        }

        try {
            const res = await fetch(MY_JOB_POSTS_URL, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            // console.log('[HiringRun] my-job-posts status:', res.status);

            if (!res.ok) {
                if (hint) hint.textContent = 'Could not load your job posts.';
                return;
            }

            const payload = await res.json();
            // console.log('[HiringRun] my-job-posts payload:', payload);

            // Accept any of these shapes:
            //   { success: true, data: [...] }
            //   { data: [...] }
            //   [...]
            let rows = [];
            if (Array.isArray(payload)) {
                rows = payload;
            } else if (payload && Array.isArray(payload.data)) {
                rows = payload.data;
            } else if (payload && Array.isArray(payload.jobs)) {
                rows = payload.jobs;
            }

            // console.log('[HiringRun] rows to render:', rows.length);

            if (rows.length === 0) {
                select.innerHTML = '<option value="">Not using an existing post</option>';
                if (hint) hint.textContent = 'You have no active job posts yet.';
                return;
            }

            select.innerHTML = [
                '<option value="">Not using an existing post</option>',
                ...rows.map(j => {
                    const label = j.label || j.job_title || 'Untitled';
                    return `<option value="${j.id}">${escapeHtml(label)}</option>`;
                }),
            ].join('');

            if (hint) hint.textContent = `${rows.length} job post${rows.length === 1 ? '' : 's'} loaded.`;

            // console.log('[HiringRun] picker options after render:', select.options.length);

        } catch (err) {
            console.error('[HiringRun] Failed to load job posts:', err);
            select.innerHTML = '<option value="">Not using an existing post</option>';
            if (hint) hint.textContent = 'Could not load your job posts.';
        }
    }

    // ─────────────────────────────────────────────────────────────
    // When a job post is picked, auto-fill the title and hide the
    // paste/upload description fields (the API resolves them).
    // ─────────────────────────────────────────────────────────────
    function bindJobPostChange() {
        const select = document.getElementById('jobPostSelect');
        if (!select) return;

        select.addEventListener('change', function () {
            const titleInput = document.querySelector('input[name="title"]');
            const jdPaste    = document.getElementById('jdPasteWrap');
            const jdFile     = document.querySelector('input[name="job_description_file"]');

            if (this.value) {
                if (titleInput && !titleInput.value.trim()) {
                    titleInput.value = this.options[this.selectedIndex].text
                        .replace(/\s*\([A-Z]{2}\)\s*$/, '');   // strip "(UG)" suffix
                }
                if (jdPaste) jdPaste.classList.add('d-none');
                if (jdFile)  jdFile.disabled = true;
            } else {
                if (jdPaste) jdPaste.classList.remove('d-none');
                if (jdFile)  jdFile.disabled = false;
            }
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Submit
    // ─────────────────────────────────────────────────────────────
    function bindSubmit() {
        const form = document.getElementById('runForm');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const btn = document.getElementById('submitBtn');
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';

            const formData = new FormData(this);

            try {
                const res = await fetch(STORE_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: formData,
                });
                const data = await res.json();

                if (!res.ok || !data.success) {
                    alert(data.message || 'Upload failed.');
                    btn.disabled = false;
                    btn.innerHTML = original;
                    return;
                }

                const uuid = data.run.uuid;
                openProgressModal();
                pollStatus(uuid);
            } catch (err) {
                alert('Network error: ' + err.message);
                btn.disabled = false;
                btn.innerHTML = original;
            }
        });
    }

    function openProgressModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('progressModal')).show();
    }

    function pollStatus(uuid) {
        if (pollTimer) clearInterval(pollTimer);

        const tick = async () => {
            try {
                const res = await fetch(`${STATUS_BASE}/${uuid}/status`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                const r = data.run;

                document.getElementById('progressCount').textContent = `${r.processed + r.failed} / ${r.total}`;
                document.getElementById('progressPercent').textContent = `${r.progress_percent}%`;
                document.getElementById('progressBar').style.width = `${r.progress_percent}%`;

                if (r.status === 'processing') {
                    document.getElementById('progressStatus').textContent = 'Screening CVs';
                } else if (r.status === 'completed') {
                    document.getElementById('progressStatus').textContent = 'Complete';
                    clearInterval(pollTimer);
                    setTimeout(() => {
                        window.location.href = `${SHOW_BASE}/${uuid}`;
                    }, 800);
                } else if (r.status === 'failed') {
                    document.getElementById('progressStatus').textContent = 'Failed';
                    clearInterval(pollTimer);
                }
            } catch (e) {
                // keep polling
            }
        };

        tick();
        pollTimer = setInterval(tick, 2500);
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    // ─────────────────────────────────────────────────────────────
    // Boot — run immediately if the DOM is already parsed.
    // By the time @stack('scripts') renders, DOMContentLoaded has
    // already fired in most Metronic layouts, so listening for it
    // would never call our loader.
    // ─────────────────────────────────────────────────────────────
    function boot() {
        console.log('[HiringRun] boot running, readyState =', document.readyState);
        loadJobPosts();
        bindJobPostChange();
        bindSubmit();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
@endpush