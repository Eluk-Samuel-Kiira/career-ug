@php
    // De-duplicate contact details so the same number/email is never listed twice
    $phones = array_values(array_unique($phones ?? []));
    $emails = array_values(array_unique($emails ?? []));

    $needsResume   = !empty($job['is_resume_required']);
    $needsCover    = !empty($job['is_cover_letter_required']);
    $needsAcademic = !empty($job['is_academic_documents_required']);
@endphp

@push('styles')
<style>
    /* Apply modal: mobile-safe layout */
    #applyModal .modal-dialog{ margin-left:auto; margin-right:auto; }
    #applyModal .jp-min0{ min-width:0; }
    #applyModal .modal-header{ align-items:flex-start; gap:12px; }
    #applyModal .jp-modal-title{ font-size:1rem; color:var(--jp-ink); line-height:1.3; overflow-wrap:anywhere;
        display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    #applyModal .jp-modal-company{ color:var(--jp-muted); overflow-wrap:anywhere; }
    #applyModal .jp-apply-method{ min-width:0; }
    #applyModal .jp-apply-method .btn-sm{ white-space:nowrap; }
    #applyModal .jp-contact-row{ min-width:0; }
    #applyModal .jp-contact-chip{ max-width:100%; overflow-wrap:anywhere; word-break:break-word; }
    #applyModal .jp-contact-chip::after{ content:"tap to copy"; margin-left:8px; font-family:inherit; font-size:.68rem; color:var(--jp-muted); text-transform:uppercase; letter-spacing:.03em; }
    #applyModal .jp-contact-chip.jp-copied::after{ content:""; margin:0; }
    #applyModal .jp-docs-note{ background:var(--jp-bg-soft); border:1px dashed var(--jp-line); border-radius:12px; padding:12px 14px; font-size:.82rem; color:var(--jp-muted); }
    #applyModal .jp-modal-tip{ font-size:.78rem; color:var(--jp-muted); text-align:center; }

    @media (max-width: 575.98px){
        #applyModal .modal-dialog{ margin:.75rem; }
        #applyModal .modal-body{ padding:1rem; }
        #applyModal .modal-header{ padding:1rem 1rem 0; }
        #applyModal .jp-apply-method{ padding:14px; gap:12px; }
        #applyModal .jp-contact-row .jp-contact-chip{ flex:1 1 100%; }
        #applyModal .jp-contact-row .btn,
        #applyModal .jp-apply-method > .jp-min0 > a.btn{ flex:1 1 100%; width:100%; text-align:center; }
    }
</style>
@endpush

<div class="modal fade" id="applyModal" tabindex="-1" aria-labelledby="applyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3 jp-min0">
                    <div class="jp-logo-sq jp-logo-sq-sm">
                        @if($companyLogo)<img src="{{ $companyLogo }}" alt="{{ $companyName }} logo">@else{{ $initials }}@endif
                    </div>
                    <div class="jp-min0">
                        <h5 class="modal-title fw-bold mb-0 jp-modal-title" id="applyModalLabel">{{ \Illuminate\Support\Str::limit($jobTitle, 60) }}</h5>
                        <div class="fs-8 jp-modal-company">{{ $companyName }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-4">

                @if($hasRealDeadline && $daysLeft !== null && $daysLeft <= 5)
                    <div class="jp-deadline-banner jp-deadline-soon mb-4">
                        <i class="ki-duotone ki-timer fs-3"><span class="path1"></span><span class="path2"></span></i>
                        {{ $daysLeft < 0 ? 'This listing has expired' : ($daysLeft === 0 ? 'Closes today' : "Closes in {$daysLeft} day" . ($daysLeft === 1 ? '' : 's')) }}
                    </div>
                @endif

                @if($hasApplicationMethod)
                    <p class="fw-semibold mb-3" style="color:var(--jp-ink); font-size:.92rem;">Choose how you'd like to apply</p>
                @endif

                @if($hasApplicationMethod && ($needsResume || $needsCover || $needsAcademic))
                    <div class="jp-docs-note mb-3">
                        <strong style="color:var(--jp-ink);">Have ready:</strong>
                        {{ collect([
                            $needsResume ? 'your CV / resume' : null,
                            $needsCover ? 'a cover letter' : null,
                            $needsAcademic ? 'academic documents' : null,
                        ])->filter()->implode(', ') }}
                    </div>
                @endif

                <div class="d-flex flex-column gap-3">

                    @if($applyUrl)
                    <div class="jp-apply-method">
                        <div class="jp-apply-icon" style="background:#E7F1FB; color:#1D6FCC;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/></svg>
                        </div>
                        <div class="flex-grow-1 jp-min0">
                            <div class="jp-apply-title">Apply on the employer's site</div>
                            <div class="jp-apply-sub">You'll be redirected to complete your application</div>
                            <a href="{{ $applyUrl }}" target="_blank" rel="noopener noreferrer" class="btn jp-btn-primary btn-sm mt-1">Continue to Apply</a>
                        </div>
                    </div>
                    @endif

                    @if($hasWhatsapp && $phones)
                    <div class="jp-apply-method">
                        <div class="jp-apply-icon" style="background:#E7F9EF; color:#1DA851;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 6.32A8.86 8.86 0 0 0 12.02 3.5c-4.87 0-8.83 3.94-8.83 8.79 0 1.55.41 3.06 1.19 4.39L3.2 21l4.44-1.16a8.9 8.9 0 0 0 4.37 1.12h.01c4.87 0 8.83-3.94 8.83-8.79a8.7 8.7 0 0 0-2.25-5.85Zm-5.58 13.5h-.01c-1.36 0-2.7-.36-3.86-1.05l-.28-.16-2.87.75.77-2.79-.18-.29a7.3 7.3 0 0 1-1.13-3.9c0-4.03 3.3-7.32 7.36-7.32a7.3 7.3 0 0 1 5.2 2.15 7.24 7.24 0 0 1 2.16 5.17c0 4.03-3.3 7.32-7.36 7.32Zm4.03-5.48c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.58.71-.71.86-.26.16-.48.05a6.03 6.03 0 0 1-1.77-1.09 6.6 6.6 0 0 1-1.22-1.52c-.13-.22 0-.34.1-.45.1-.1.22-.26.33-.39.11-.13.15-.22.22-.37.07-.15.04-.28-.02-.39-.06-.11-.5-1.2-.68-1.65-.18-.43-.36-.37-.5-.38h-.43c-.15 0-.39.06-.6.28-.2.22-.79.77-.79 1.87 0 1.1.81 2.17.92 2.32.11.15 1.6 2.44 3.87 3.42.54.23.96.37 1.29.48.54.17 1.03.15 1.42.09.43-.06 1.3-.53 1.49-1.04.18-.51.18-.94.13-1.03-.05-.09-.2-.15-.42-.26Z"/></svg>
                        </div>
                        <div class="flex-grow-1 jp-min0">
                            <div class="jp-apply-title">Apply via WhatsApp</div>
                            <div class="jp-apply-sub">Message the employer directly</div>
                            <div class="d-flex flex-column gap-2 mt-2">
                                @foreach($phones as $phone)
                                    @php
                                        // wa.me needs digits only; "00" international prefix becomes the bare country code
                                        $waNum = ltrim(preg_replace('/[^0-9+]/', '', $phone), '+');
                                        $waNum = preg_replace('/^00/', '', $waNum);
                                        $waMsg = rawurlencode("Hello, I'm interested in the {$jobTitle} position" . ($companyName ? " at {$companyName}" : '') . ". I'd like to apply.");
                                    @endphp
                                    <div class="jp-contact-row">
                                        <span class="jp-contact-chip" role="button" tabindex="0" data-copy="{{ $phone }}" onclick="jpCopy(this, this.dataset.copy)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();jpCopy(this, this.dataset.copy);}" title="Click to copy">{{ $phone }}</span>
                                        <a href="https://wa.me/{{ $waNum }}?text={{ $waMsg }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm" style="background:#25D366; color:#fff; font-weight:700;">Open WhatsApp</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($hasPhoneCall && $phones)
                    <div class="jp-apply-method">
                        <div class="jp-apply-icon" style="background:var(--jp-bg-soft); color:var(--jp-navy); border:1px solid var(--jp-line);">
                            <i class="ki-duotone ki-phone fs-3"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                        <div class="flex-grow-1 jp-min0">
                            <div class="jp-apply-title">Call to Apply</div>
                            <div class="jp-apply-sub">Speak directly with the hiring team</div>
                            <div class="d-flex flex-column gap-2 mt-2">
                                @foreach($phones as $phone)
                                    <div class="jp-contact-row">
                                        <span class="jp-contact-chip" role="button" tabindex="0" data-copy="{{ $phone }}" onclick="jpCopy(this, this.dataset.copy)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();jpCopy(this, this.dataset.copy);}" title="Click to copy">{{ $phone }}</span>
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="btn jp-btn-primary btn-sm">Call Now</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($emails)
                    <div class="jp-apply-method">
                        <div class="jp-apply-icon" style="background:#FFF6E0; color:#B8860B;">
                            <i class="ki-duotone ki-sms fs-3"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                        <div class="flex-grow-1 jp-min0">
                            <div class="jp-apply-title">Apply via Email</div>
                            <div class="jp-apply-sub">Send your CV and cover letter</div>
                            <div class="d-flex flex-column gap-2 mt-2">
                                @foreach($emails as $email)
                                    <div class="jp-contact-row">
                                        <span class="jp-contact-chip" role="button" tabindex="0" data-copy="{{ $email }}" onclick="jpCopy(this, this.dataset.copy)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();jpCopy(this, this.dataset.copy);}" title="Click to copy">{{ $email }}</span>
                                        <a href="mailto:{{ $email }}?subject={{ rawurlencode('Application for ' . $jobTitle . ($companyName ? ' at ' . $companyName : '')) }}" class="btn jp-btn-primary btn-sm">Compose Email</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(!$hasApplicationMethod)
                        <div class="text-center py-6">
                            <i class="ki-duotone ki-information-5 fs-2x d-block mb-2" style="color:var(--jp-muted);"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <p class="mb-3" style="color:var(--jp-muted); font-size:.88rem;">No direct application method was provided for this listing.</p>
                            @if($companyWebsite)
                                <a href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer" class="btn jp-btn-outline btn-sm">Visit Company Website</a>
                            @endif
                        </div>
                    @endif

                </div>

                @if($hasApplicationMethod)
                    <p class="jp-modal-tip mt-4 mb-0">Tip: mention that you found this role on {{ app_name() }}.</p>
                @endif
            </div>
        </div>
    </div>
</div>


<script>
// ---------- Copy helper (used by the apply modal) ----------
function jpCopy(el, text) {
    const done = () => {
        const original = el.dataset.originalText || el.textContent;
        el.dataset.originalText = original;
        el.textContent = 'Copied!';
        el.classList.add('jp-copied');
        setTimeout(() => {
            el.textContent = original;
            el.classList.remove('jp-copied');
        }, 1200);
    };

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(() => {});
        return;
    }

    // Fallback for older browsers / non-secure contexts
    const t = document.createElement('textarea');
    t.value = text;
    t.style.position = 'fixed';
    t.style.opacity = '0';
    document.body.appendChild(t);
    t.select();
    try { document.execCommand('copy'); done(); } catch (e) {}
    document.body.removeChild(t);
}
</script>

<script>
(function () {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')?.content === 'true';
    const PAGE_JOB_ID = {!! json_encode($jobId ?? null) !!};

    // ─────────────────────────────────────────────────────────────
    // Shared toast helper — uses window.showToast when available
    // ─────────────────────────────────────────────────────────────
    function toast(type, msg, title) {
        if (typeof window.showToast === 'function') {
            window.showToast(type, msg, title || (type === 'success' ? 'Done' : type === 'error' ? 'Error' : 'Info'));
        } else {
            console[type === 'error' ? 'error' : 'log'](msg);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Login gate — reuses whatever login/guest modal this page has
    // ─────────────────────────────────────────────────────────────
    function showLoginGate() {
        if (typeof window.showSaveLoginModal === 'function') {
            window.showSaveLoginModal();
            return;
        }
        if (typeof window.showLoginOrGuestModal === 'function') {
            window.showLoginOrGuestModal();
            return;
        }
        // Fallback redirect
        const back = encodeURIComponent(window.location.href);
        window.location.href = '{{ route("login") }}?redirect=' + back;
    }

    // ─────────────────────────────────────────────────────────────
    // Flip every apply button on the page to the "Applied" state
    // ─────────────────────────────────────────────────────────────
    function markPageAsApplied() {
        document.querySelectorAll('.jp-easy-apply-btn').forEach(btn => {
            btn.dataset.applied = 'true';
            btn.classList.remove('jp-btn-primary');
            btn.classList.add('jp-btn-outline');
            btn.innerHTML = `
                <i class="ki-duotone ki-check-circle fs-3 me-2">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                Applied
            `;
        });

        document.querySelectorAll('[data-bs-target="#applyModal"]').forEach(btn => {
            btn.dataset.applied = 'true';
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Track application — the ONE function both buttons use
    // ─────────────────────────────────────────────────────────────
    async function trackApplication(jobId) {
        if (!jobId) {
            return { success: false, message: 'Missing job id.' };
        }

        try {
            const res = await fetch(`/jobs/${jobId}/track-application`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
            });

            const data = await res.json();
            return data;

        } catch (err) {
            console.error('trackApplication error:', err);
            return { success: false, message: 'Network error. Please try again.' };
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Unified click handler for BOTH button types
    // ─────────────────────────────────────────────────────────────
    async function handleApplyClick(btn, { useModal }) {
        // Already applied?
        if (btn.dataset.applied === 'true') {
            toast('info', 'You have already applied to this job.', 'Applied');
            return;
        }

        // Not logged in? Open the login gate (unless the modal itself handles it)
        if (!isLoggedIn && !useModal) {
            showLoginGate();
            return;
        }
        if (!isLoggedIn && useModal) {
            // Let the normal modal flow open; it has its own login check
            return false;
        }

        const jobId = btn.dataset.jobId;
        if (!jobId) {
            toast('error', 'Job id is missing. Please refresh the page.', 'Error');
            return;
        }

        // Visual feedback
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Applying...';

        const data = await trackApplication(jobId);

        // ── Success
        if (data.success) {
            markPageAsApplied();

            if (data.is_applied && data.message === 'Already applied') {
                toast('info', 'You have already applied to this job.', 'Applied');
            } else {
                toast('success', 'Application submitted successfully!', 'Applied');
            }

            btn.disabled = false;
            return;
        }

        // ── Not logged in (server said so)
        if (data.requires_login) {
            btn.disabled = false;
            btn.innerHTML = original;
            showLoginGate();
            return;
        }

        // ── Profile incomplete
        if (data.code === 'profile_incomplete') {
            btn.disabled = false;
            btn.innerHTML = original;
            toast('warning', data.message || 'Please complete your profile before applying.', 'Profile Incomplete');
            return;
        }

        // ── Any other error
        btn.disabled = false;
        btn.innerHTML = original;
        toast('error', data.message || 'Could not submit your application.', 'Error');
    }

    // ─────────────────────────────────────────────────────────────
    // Bind buttons
    // ─────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {

        // ── Easy Apply buttons — direct track, no modal
        document.querySelectorAll('.jp-easy-apply-btn').forEach(btn => {
            // If already in "Applied" state, bind only the info toast
            if (btn.dataset.applied === 'true') {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    toast('info', 'You have already applied to this job.', 'Applied');
                });
                return;
            }

            btn.addEventListener('click', e => {
                e.preventDefault();
                handleApplyClick(btn, { useModal: false });
            });
        });

        // ── Normal Apply Now buttons — open the modal, but pre-gate on login
        document.querySelectorAll('[data-bs-target="#applyModal"]').forEach(btn => {
            btn.addEventListener('click', e => {
                // If already applied via this session, don't open the modal
                if (btn.dataset.applied === 'true') {
                    e.preventDefault();
                    e.stopPropagation();
                    toast('info', 'You have already applied to this job.', 'Applied');
                    return;
                }

                if (!isLoggedIn) {
                    e.preventDefault();
                    e.stopPropagation();
                    showLoginGate();
                    return;
                }

                // Let the modal open — its own show handler will track
            });
        });

        // ── The modal's own show handler tracks intent
        const applyModal = document.getElementById('applyModal');
        if (applyModal) {
            let tracked = false;

            applyModal.addEventListener('show.bs.modal', function () {
                if (tracked || !isLoggedIn) return;
                tracked = true;

                // Prefer the id rendered by the server; fall back to the save button's id
                const jobId = PAGE_JOB_ID || document.querySelector('.jp-save-job-btn')?.dataset.jobId;

                trackApplication(jobId).then(data => {
                    if (data.success && data.is_applied) {
                        markPageAsApplied();
                    }
                });
            });
        }
    });
})();
</script>

<script>
    // ---------- Save Job Functionality ----------
    document.addEventListener('DOMContentLoaded', function () {
        const saveBtn = document.querySelector('.jp-save-job-btn');
        if (!saveBtn) return;

        saveBtn.addEventListener('click', function () {
            if (this.dataset.busy === 'true') return; // ignore double-taps while a request is running

            const jobId = this.dataset.jobId;
            const isSaved = this.dataset.isSaved === 'true';
            const icon = this.querySelector('i');
            const text = this.querySelector('span');
            const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')?.content === 'true';

            if (!isLoggedIn) {
                showSaveLoginModal();
                return;
            }

            toggleSaveJob(jobId, isSaved, icon, text, this);
        });
    });

    function toggleSaveJob(jobId, isSaved, icon, text, btn) {
        if (btn) btn.dataset.busy = 'true';

        fetch(`/jobs/${jobId}/save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                if (data.requires_login) {
                    showSaveLoginModal();
                } else if (typeof window.showToast === 'function') {
                    window.showToast('error', data.message || 'Could not update your saved jobs.', 'Error');
                }
                return;
            }
            const newState = data.is_saved;

            icon.className = newState ? 'bi bi-heart-fill fs-3 me-2 text-danger' : 'bi bi-heart fs-3 me-2';
            text.textContent = newState ? 'Saved' : 'Save';

            if (typeof window.showToast === 'function') {
                window.showToast(
                    newState ? 'success' : 'info',
                    newState ? 'Job saved successfully!' : 'Job removed from saved.',
                    newState ? 'Saved' : 'Unsaved'
                );
            }

            document.querySelector('.jp-save-job-btn').dataset.isSaved = newState;
        })
        .catch(() => {
            if (typeof window.showToast === 'function') {
                window.showToast('error', 'Something went wrong. Please try again.', 'Error');
            }
        })
        .finally(() => {
            if (btn) btn.dataset.busy = 'false';
        });
    }

    function showSaveLoginModal() {
        const back = encodeURIComponent(window.location.href);
        const modalHtml = `
            <div class="modal fade" id="saveLoginModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="margin-left:auto; margin-right:auto;">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-body text-center p-5">
                            <div class="mb-4">
                                <div class="symbol symbol-80px bg-light-warning rounded-3 d-flex align-items-center justify-content-center mx-auto">
                                    <i class="bi bi-heart fs-3x text-warning"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-2">Sign in to save jobs</h4>
                            <p class="text-muted mb-4">
                                Create a free account or sign in to save jobs, apply faster and track every application in one place.
                            </p>
                            <div class="d-flex flex-column gap-3">
                                <a href="{{ route('login') }}?redirect=${back}" class="btn jp-btn-primary py-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Sign In
                                </a>
                                <a href="{{ route('register') }}" class="btn jp-btn-outline py-3">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Create Account
                                </a>
                            </div>
                            <button type="button" class="btn btn-link text-muted mt-3 p-0" data-bs-dismiss="modal">Not now</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const existingModal = document.getElementById('saveLoginModal');
        if (existingModal) {
            existingModal.remove();
        }

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const el = document.getElementById('saveLoginModal');
        // Clean up the element once it has been closed
        el.addEventListener('hidden.bs.modal', () => el.remove());
        const modal = new bootstrap.Modal(el);
        modal.show();
    }
</script>