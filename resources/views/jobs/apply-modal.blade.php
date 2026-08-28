
<div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="jp-logo-sq jp-logo-sq-sm">
                        @if($companyLogo)<img src="{{ $companyLogo }}" alt="{{ $companyName }}">@else{{ $initials }}@endif
                    </div>
                    <div class="min-w-0">
                        <h5 class="modal-title fw-bold mb-0 text-truncate" style="font-size:1rem; color:var(--jp-ink);">{{ \Illuminate\Support\Str::limit($jobTitle, 40) }}</h5>
                        <div class="fs-8" style="color:var(--jp-muted);">{{ $companyName }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-4">

                @if($hasRealDeadline && $daysLeft !== null && $daysLeft <= 5)
                    <div class="jp-deadline-banner jp-deadline-soon mb-4">
                        <i class="ki-duotone ki-timer fs-3"><span class="path1"></span><span class="path2"></span></i>
                        {{ $daysLeft < 0 ? 'This listing has expired' : ($daysLeft === 0 ? 'Closes today' : "Closes in {$daysLeft} day" . ($daysLeft === 1 ? '' : 's')) }}
                    </div>
                @endif

                <div class="d-flex flex-column gap-3">

                    @if($applyUrl)
                    <div class="jp-apply-method">
                        <div class="jp-apply-icon" style="background:#E7F1FB; color:#1D6FCC;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/></svg>
                        </div>
                        <div class="flex-grow-1 min-w-0">
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
                        <div class="flex-grow-1 min-w-0">
                            <div class="jp-apply-title">Apply via WhatsApp</div>
                            <div class="jp-apply-sub">Message the employer directly</div>
                            <div class="d-flex flex-column gap-2 mt-2">
                                @foreach($phones as $phone)
                                    @php
                                        $waNum = ltrim(preg_replace('/[^0-9+]/', '', $phone), '+');
                                        $waMsg = rawurlencode("Hello, I'm interested in the {$jobTitle} position" . ($companyName ? " at {$companyName}" : '') . ". I'd like to apply.");
                                    @endphp
                                    <div class="jp-contact-row">
                                        <span class="jp-contact-chip" onclick="jpCopy(this, '{{ $phone }}')" title="Click to copy">{{ $phone }}</span>
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
                        <div class="flex-grow-1 min-w-0">
                            <div class="jp-apply-title">Call to Apply</div>
                            <div class="jp-apply-sub">Speak directly with the hiring team</div>
                            <div class="d-flex flex-column gap-2 mt-2">
                                @foreach($phones as $phone)
                                    <div class="jp-contact-row">
                                        <span class="jp-contact-chip" onclick="jpCopy(this, '{{ $phone }}')" title="Click to copy">{{ $phone }}</span>
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
                        <div class="flex-grow-1 min-w-0">
                            <div class="jp-apply-title">Apply via Email</div>
                            <div class="jp-apply-sub">Send your CV and cover letter</div>
                            <div class="d-flex flex-column gap-2 mt-2">
                                @foreach($emails as $email)
                                    <div class="jp-contact-row">
                                        <span class="jp-contact-chip" onclick="jpCopy(this, '{{ $email }}')" title="Click to copy">{{ $email }}</span>
                                        <a href="mailto:{{ $email }}?subject={{ rawurlencode('Application for ' . $jobTitle . ($companyName ? ' at ' . $companyName : '')) }}" class="btn jp-btn-primary btn-sm">Compose Email</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(!$hasApplicationMethod)
                        <div class="text-center py-6">
                            <i class="ki-duotone ki-information-5 fs-2x d-block mb-2" style="color:var(--jp-muted);"><span class="path1"></span><span class="path2"></span></i>
                            <p class="mb-3" style="color:var(--jp-muted); font-size:.88rem;">No direct application method was provided for this listing.</p>
                            @if($companyWebsite)
                                <a href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer" class="btn jp-btn-outline btn-sm">Visit Company Website</a>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function jpCopy(el, text) {
    navigator.clipboard.writeText(text).then(() => {
        const original = el.textContent;
        el.textContent = 'Copied!';
        el.classList.add('jp-copied');
        setTimeout(() => { el.textContent = original; el.classList.remove('jp-copied'); }, 1200);
    });
}
</script>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show toast or alert
        alert('Copied to clipboard!');
    }).catch(() => {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        alert('Copied to clipboard!');
    });
}

function reportMissingApplicationLink(jobId, jobTitle, companyName) {
    if (confirm(`Report missing application link for "${jobTitle}"?`)) {
        // Your reporting logic here
        alert('Thank you! Admin has been notified.');
    }
}
</script>

<script>
    // ---------- Track Application ----------
    document.addEventListener('DOMContentLoaded', function () {
        const applyModal = document.getElementById('applyModal');
        if (!applyModal) return;
    
        let tracked = false;
        let isGuestContinue = false;

        applyModal.addEventListener('show.bs.modal', function (e) {
            // If continuing as guest, skip the login check
            if (isGuestContinue) {
                isGuestContinue = false;
                if (!tracked) {
                    tracked = true;
                    trackApplication();
                }
                return;
            }
            
            // Check if user is logged in
            const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')?.content === 'true';
            
            if (!isLoggedIn) {
                e.preventDefault();
                e.stopPropagation();
                showLoginOrGuestModal();
                return;
            }
            
            if (tracked) return;
            
            // User is logged in, track the application
            tracked = true;
            trackApplication();
        });

        function showLoginOrGuestModal() {
            const modalHtml = `
                <div class="modal fade" id="loginOrGuestModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Apply for Job</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center p-5">
                                <div class="mb-4">
                                    <div class="symbol symbol-80px bg-light-primary rounded-3 d-flex align-items-center justify-content-center mx-auto">
                                        <i class="bi bi-person-check fs-3x text-primary"></i>
                                    </div>
                                </div>
                                <h4 class="fw-bold mb-2">Ready to Apply?</h4>
                                <p class="text-muted mb-4">
                                    Sign in to track your application status or continue as a guest.
                                </p>
                                
                                <div class="d-flex flex-column gap-3">
                                    <a href="{{ route('login') }}" class="btn jp-btn-primary py-3">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        Sign In & Apply
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary py-3" id="continueAsGuestBtn">
                                        <i class="bi bi-person me-2"></i>
                                        Continue as Guest
                                    </button>
                                </div>
                                
                                <p class="text-muted fs-7 mt-4 mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Your application will not be tracked anonymously.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const existingModal = document.getElementById('loginOrGuestModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('loginOrGuestModal'));
            modal.show();
            
            // Handle Continue as Guest
            document.getElementById('continueAsGuestBtn').addEventListener('click', function() {
                // Set the flag to bypass login check
                isGuestContinue = true;
                
                // Hide the login/guest modal
                modal.hide();
                
                // Track as guest
                trackGuestApplication();
                
                // Show the actual apply modal after a short delay
                setTimeout(() => {
                    const applyModalEl = document.getElementById('applyModal');
                    if (applyModalEl) {
                        // Clean up any existing backdrop
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(b => b.remove());
                        document.body.classList.remove('modal-open');
                        
                        // Show the apply modal
                        const bsModal = new bootstrap.Modal(applyModalEl);
                        bsModal.show();
                    }
                }, 200);
            });
        }

        function trackApplication() {
            const jobId = document.querySelector('.jp-save-job-btn')?.dataset.jobId;
            if (!jobId) return;
            
            fetch(`/jobs/${jobId}/track-application`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Application tracked successfully');
                }
            })
            .catch(error => {
                console.error('Tracking error:', error);
            });
        }

        function trackGuestApplication() {
            const jobId = document.querySelector('.jp-save-job-btn')?.dataset.jobId;
            if (!jobId) return;
            
            fetch('/guest/track-application', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ job_id: jobId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Guest application tracked in session');
                }
            })
            .catch(error => {
                console.error('Guest tracking error:', error);
            });
        }
    });
</script>

<script>
    // ---------- Save Job Functionality ----------
    document.addEventListener('DOMContentLoaded', function() {
        const saveBtn = document.querySelector('.jp-save-job-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const jobId = this.dataset.jobId;
                const isSaved = this.dataset.isSaved === 'true';
                const icon = this.querySelector('i');
                const text = this.querySelector('span');
                
                // Check if user is logged in via session
                const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')?.content === 'true';
                
                if (!isLoggedIn) {
                    showSaveLoginModal();
                    return;
                }
                
                // Toggle save via web controller
                toggleSaveJob(jobId, isSaved, icon, text);
            });
        }
    });

    function toggleSaveJob(jobId, isSaved, icon, text) {
        const url = `/jobs/${jobId}/save`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const newState = data.is_saved;
                if (newState) {
                    icon.className = 'bi bi-heart-fill fs-3 me-2 text-danger';
                    text.textContent = 'Saved';
                    if (typeof window.showToast === 'function') {
                        window.showToast('success', 'Job saved successfully!', 'Saved');
                    }
                } else {
                    icon.className = 'bi bi-heart fs-3 me-2';
                    text.textContent = 'Save';
                    if (typeof window.showToast === 'function') {
                        window.showToast('info', 'Job removed from saved.', 'Unsaved');
                    }
                }
                // Update data attribute
                document.querySelector('.jp-save-job-btn').dataset.isSaved = newState;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof window.showToast === 'function') {
                window.showToast('error', 'Something went wrong. Please try again.', 'Error');
            }
        });
    }

    function showSaveLoginModal() {
        const modalHtml = `
            <div class="modal fade" id="saveLoginModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-body text-center p-5">
                            <div class="mb-4">
                                <div class="symbol symbol-80px bg-light-warning rounded-3 d-flex align-items-center justify-content-center mx-auto">
                                    <i class="bi bi-heart fs-3x text-warning"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-2">Login to Save Jobs</h4>
                            <p class="text-muted mb-4">
                                Create an account or sign in to save jobs and track your applications.
                            </p>
                            <div class="d-flex flex-column gap-3">
                                <a href="{{ route('login') }}" class="btn jp-btn-primary py-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Sign In
                                </a>
                                <a href="{{ route('register') }}" class="btn jp-btn-outline py-3">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Create Account
                                </a>
                            </div>
                            <p class="text-muted fs-7 mt-4 mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Save jobs to your profile and track your applications.
                            </p>
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
        const modal = new bootstrap.Modal(document.getElementById('saveLoginModal'));
        modal.show();
    }
</script>
