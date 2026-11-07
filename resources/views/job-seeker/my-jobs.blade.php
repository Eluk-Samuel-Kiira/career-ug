@extends('layouts.admin')

@section('title', $tab === 'skills' ? 'My Jobs — By Skills' : 'My Jobs — By Category')
@section('page_title', $tab === 'skills' ? 'My Jobs — By Skills' : 'My Jobs — By Category')

@push('styles')
<style>
    .mj-page { --mj-teal:#03A588; --mj-line:rgba(15,27,45,0.08); --mj-muted:#64748B; --mj-ink:#0F1B2D; }

    .mj-tabs { display:flex; gap:6px; border-bottom:1px solid var(--mj-line); margin-bottom:20px; }
    .mj-tab { padding:10px 18px; font-weight:700; font-size:.92rem; color:var(--mj-muted); border-bottom:2px solid transparent; text-decoration:none; }
    .mj-tab:hover { color:var(--mj-ink); }
    .mj-tab.active { color:var(--mj-teal); border-bottom-color:var(--mj-teal); }

    .mj-header { background: linear-gradient(120deg, #EAF7F5 0%, #E7F1FB 100%); border-radius:16px; padding:20px 24px; margin-bottom:20px; }
    .mj-header h2 { font-weight:800; color:var(--mj-ink); margin:0 0 4px; }
    .mj-header p  { color:var(--mj-muted); margin:0; }

    .mj-section-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px; flex-wrap:wrap; }
    .mj-section-title { font-size:1rem; font-weight:800; color:var(--mj-ink); margin:0; }
    .mj-badge-pill { display:inline-block; background:#F4F8F7; color:#3B5166; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; border:1px solid var(--mj-line); }
    .mj-badge-pill.mj-match { background:#E9F9EF; color:#1E9E4C; border-color:rgba(30,158,76,0.15); }

    .mj-job-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:16px; }
    .mj-job-card { background:#fff; border:1px solid var(--mj-line); border-radius:14px; padding:18px; display:flex; flex-direction:column; gap:10px; transition:.15s; }
    .mj-job-card:hover { border-color:var(--mj-teal); box-shadow:0 10px 24px rgba(11,28,46,0.08); transform:translateY(-2px); }

    .mj-card-head { display:flex; gap:12px; align-items:flex-start; }
    .mj-logo { width:48px; height:48px; border-radius:10px; border:1px solid var(--mj-line); background:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; padding:4px; }
    .mj-logo img { max-width:100%; max-height:100%; object-fit:contain; }
    .mj-card-title { font-weight:700; color:var(--mj-ink); line-height:1.3; word-break:break-word; }
    .mj-card-company { color:var(--mj-muted); font-size:.8rem; }
    .mj-card-meta { display:flex; flex-wrap:wrap; gap:6px 14px; font-size:.78rem; color:var(--mj-muted); }
    .mj-card-meta > span { display:inline-flex; align-items:center; gap:4px; }
    .mj-card-salary { color:var(--mj-teal); font-weight:800; font-size:.92rem; }

    .mj-card-foot { display:flex; align-items:center; justify-content:space-between; gap:10px; padding-top:10px; border-top:1px solid var(--mj-line); margin-top:auto; }

    .mj-empty { background:#fff; border:1px dashed var(--mj-line); border-radius:16px; padding:60px 24px; text-align:center; }
    .mj-empty h4 { font-weight:800; color:var(--mj-ink); margin:16px 0 8px; }
    .mj-empty p { color:var(--mj-muted); max-width:520px; margin:0 auto 20px; }

    .mj-pagination { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:16px; flex-wrap:wrap; }
    .mj-pagination button { border:1px solid var(--mj-line); background:#fff; color:#3B5166; font-weight:600; font-size:.82rem; padding:6px 12px; border-radius:8px; transition:.15s; min-width:36px; }
    .mj-pagination button:hover:not(:disabled) { border-color:var(--mj-teal); color:var(--mj-teal); }
    .mj-pagination button.active { background:var(--mj-teal); color:#fff; border-color:var(--mj-teal); }
    .mj-pagination button:disabled { opacity:.4; cursor:not-allowed; }
    .mj-pagination .mj-page-info { font-size:.8rem; color:var(--mj-muted); margin:0 8px; }

    .mj-skill-cta { background:#fff; border:1px solid var(--mj-line); border-radius:14px; padding:28px 24px; text-align:center; }
    .mj-skill-cta h5 { font-weight:800; color:var(--mj-ink); margin:12px 0 6px; }
    .mj-skill-cta p { color:var(--mj-muted); max-width:480px; margin:0 auto 16px; font-size:.9rem; }

    @media (max-width: 575.98px) {
        .mj-job-grid { grid-template-columns:1fr; }
        .mj-card-foot { flex-direction:column; align-items:stretch; }
        .mj-card-foot .btn { width:100%; }
    }
</style>
@endpush

@section('content')
<div class="container py-6 mj-page">

    {{-- Tabs --}}
    <div class="mj-tabs">
        <a href="{{ route('my-jobs.category') }}"
           class="mj-tab {{ $tab === 'category' ? 'active' : '' }}">
            By Category
        </a>
        <a href="{{ route('my-jobs.skills') }}"
           class="mj-tab {{ $tab === 'skills' ? 'active' : '' }}">
            By Skills
        </a>
    </div>

    {{-- Header --}}
    <div class="mj-header">
        <h2>{{ $tab === 'skills' ? 'Jobs by Skill Match' : 'Jobs by Category' }}</h2>
        <p>
            @if($tab === 'skills')
                Jobs where your skills match what the employer is looking for.
            @else
                Jobs in the categories you follow.
            @endif
        </p>
    </div>

    {{-- Loading --}}
    <div id="mjLoading">
        <div class="mj-job-grid">
            @for($i=0; $i<3; $i++)
                <div class="mj-job-card" style="opacity:.5;">
                    <div style="height:14px; background:#eee; border-radius:4px; width:60%;"></div>
                    <div style="height:10px; background:#eee; border-radius:4px; width:40%;"></div>
                    <div style="height:10px; background:#eee; border-radius:4px; width:80%;"></div>
                </div>
            @endfor
        </div>
    </div>

    {{-- Not configured at all --}}
    <div id="mjNotConfigured" class="d-none">
        <div class="mj-empty">
            <i class="ki-duotone ki-notification-status fs-3x" style="color:var(--mj-teal);">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
            </i>
            <h4>Set your alert preferences</h4>
            <p>Tell us which job categories interest you and what skills you have. We'll show you jobs that actually match — nothing else.</p>
            <a href="{{ route('alert.preferences') }}" class="btn btn-primary btn-lg">
                <i class="ki-duotone ki-setting-2 fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                Set My Alert Preferences
            </a>
        </div>
    </div>

    {{-- Category tab — no categories picked --}}
    <div id="mjCategoryNotSet" class="d-none">
        <div class="mj-empty">
            <i class="ki-duotone ki-category fs-3x" style="color:var(--mj-teal);">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
            </i>
            <h4>No categories selected</h4>
            <p>You haven't picked any categories to follow yet. Select the ones that interest you to see matching jobs.</p>
            <a href="{{ route('alert.preferences') }}" class="btn btn-primary btn-lg">
                <i class="ki-duotone ki-setting-2 fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                Choose Categories
            </a>
        </div>
    </div>

    {{-- Category tab — no matches for picked categories --}}
    <div id="mjCategoryEmpty" class="d-none">
        <div class="mj-empty">
            <i class="ki-duotone ki-magnifier fs-3x" style="color:var(--mj-muted);">
                <span class="path1"></span><span class="path2"></span>
            </i>
            <h4>No jobs in your categories yet</h4>
            <p>We'll show new jobs here as soon as they're posted in your chosen categories.</p>
            <a href="{{ route('alert.preferences') }}" class="btn btn-light btn-lg">Adjust Categories</a>
        </div>
    </div>

    {{-- Skill tab — no skills AND no CV --}}
    <div id="mjSkillsNotSet" class="d-none">
        <div class="mj-skill-cta">
            <i class="ki-duotone ki-document fs-3x" style="color:var(--mj-teal);">
                <span class="path1"></span><span class="path2"></span>
            </i>
            <h5>Add skills or upload your CV</h5>
            <p>You haven't uploaded a CV or added any skills yet. Do either one to unlock skill-based matches.</p>
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                <a href="{{ route('cv.edit') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-file-up fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                    Upload My CV
                </a>
                <a href="{{ route('profile.show') }}" class="btn btn-light">
                    <i class="ki-duotone ki-user fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                    Add Skills Manually
                </a>
            </div>
        </div>
    </div>

    {{-- Skill tab — skills exist but no matches --}}
    <div id="mjSkillsEmpty" class="d-none">
        <div class="mj-empty">
            <i class="ki-duotone ki-magnifier fs-3x" style="color:var(--mj-muted);">
                <span class="path1"></span><span class="path2"></span>
            </i>
            <h4>No skill matches right now</h4>
            <p>You have skills listed, but no live jobs match them yet. We'll show matches here as soon as they're posted.</p>
            <a href="{{ route('profile.show') }}" class="btn btn-light btn-lg">Add More Skills</a>
        </div>
    </div>

    {{-- Jobs grid (used by whichever tab is active) --}}
    <div id="mjJobsSection" class="d-none">
        <div class="mj-section-head">
            <h3 class="mj-section-title" id="mjJobsTitle">Jobs</h3>
            <span class="mj-badge-pill" id="mjJobsCount">0 jobs</span>
        </div>
        <div class="mj-job-grid" id="mjJobsGrid"></div>
        <div class="mj-pagination" id="mjJobsPagination"></div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const DATA_URL      = '{{ route("my-jobs.data") }}';
    const JOB_SHOW_BASE = '{{ url("/job") }}';
    const APPLY_ENDPOINT = (id) => `/jobs/${id}/track-application`;
    const SAVE_ENDPOINT  = (id) => `/jobs/${id}/save`;

    const TAB = @json($tab);   // 'category' | 'skills'

    const els = {
        loading:           document.getElementById('mjLoading'),
        notConfigured:     document.getElementById('mjNotConfigured'),
        categoryNotSet:    document.getElementById('mjCategoryNotSet'),
        categoryEmpty:     document.getElementById('mjCategoryEmpty'),
        skillsNotSet:      document.getElementById('mjSkillsNotSet'),
        skillsEmpty:       document.getElementById('mjSkillsEmpty'),
        jobsSection:       document.getElementById('mjJobsSection'),
        jobsTitle:         document.getElementById('mjJobsTitle'),
        jobsCount:         document.getElementById('mjJobsCount'),
        jobsGrid:          document.getElementById('mjJobsGrid'),
        jobsPagination:    document.getElementById('mjJobsPagination'),
    };

    const state = {
        catPage: 1,
        skillPage: 1,
    };

    function toast(type, msg, title) {
        if (typeof window.showToast === 'function') window.showToast(type, msg, title);
        else console.log(`[${type}] ${msg}`);
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[c]));
    }

    function initials(name) {
        return (name || 'JM').trim().substring(0, 2).toUpperCase();
    }

    function jobCard(job) {
        const company  = job.company || {};
        const location = job.location || {};
        const locationText = [location.district, location.city].filter(Boolean).join(', ') || '—';
        const category = job.category?.name || '—';

        const matchBadge = (typeof job.match_score === 'number')
            ? `<span class="mj-badge-pill mj-match">${job.match_score}% skill match</span>`
            : '';

        const savedClass = job.is_saved ? 'bi-heart-fill text-danger' : 'bi-heart';
        const savedLabel = job.is_saved ? 'Saved' : 'Save';

        return `
            <div class="mj-job-card" data-job-id="${job.id}">
                <div class="mj-card-head">
                    <div class="mj-logo">
                        ${company.logo
                            ? `<img src="${escapeHtml(company.logo)}" alt="${escapeHtml(company.name)}">`
                            : `<span style="color:#0F1B2D;font-weight:800;">${escapeHtml(initials(company.name))}</span>`}
                    </div>
                    <div style="min-width:0;flex:1;">
                        <a href="${JOB_SHOW_BASE}/${escapeHtml(job.slug || job.id)}" class="mj-card-title text-decoration-none d-block">
                            ${escapeHtml(job.job_title)}
                        </a>
                        <div class="mj-card-company">${escapeHtml(company.name || '')}</div>
                    </div>
                </div>
                <div class="mj-card-meta">
                    <span>${escapeHtml(locationText)}</span>
                    <span>${escapeHtml(category)}</span>
                </div>
                <div class="mj-card-salary">${escapeHtml(job.formatted_salary || 'Negotiable')}</div>
                ${matchBadge ? `<div>${matchBadge}</div>` : ''}
                <div class="mj-card-foot">
                    <button type="button"
                            class="btn btn-sm btn-light-primary mj-apply-btn"
                            data-job-id="${job.id}"
                            data-easy="${job.easy_apply ? '1' : '0'}"
                            data-applied="${job.is_applied ? '1' : '0'}"
                            ${job.is_applied ? 'disabled' : ''}>
                        ${job.is_applied
                            ? 'Applied'
                            : (job.easy_apply ? 'Easy Apply' : 'Apply Now')}
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-light mj-save-btn"
                            data-job-id="${job.id}"
                            data-saved="${job.is_saved ? '1' : '0'}">
                        <i class="bi ${savedClass} me-1"></i> ${savedLabel}
                    </button>
                </div>
            </div>
        `;
    }

    function renderPagination(el, pagination, onPageClick) {
        el.innerHTML = '';
        if (!pagination || pagination.last_page <= 1) return;

        const { current_page, last_page } = pagination;

        const makeBtn = (label, page, opts = {}) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = label;
            if (opts.active) btn.classList.add('active');
            if (opts.disabled) btn.disabled = true;
            btn.addEventListener('click', () => onPageClick(page));
            return btn;
        };

        el.appendChild(makeBtn('‹', current_page - 1, { disabled: current_page <= 1 }));

        const win = 2;
        const start = Math.max(1, current_page - win);
        const end   = Math.min(last_page, current_page + win);

        if (start > 1) {
            el.appendChild(makeBtn('1', 1, { active: current_page === 1 }));
            if (start > 2) {
                const sp = document.createElement('span');
                sp.textContent = '…';
                sp.className = 'mj-page-info';
                el.appendChild(sp);
            }
        }

        for (let i = start; i <= end; i++) {
            el.appendChild(makeBtn(String(i), i, { active: i === current_page }));
        }

        if (end < last_page) {
            if (end < last_page - 1) {
                const sp = document.createElement('span');
                sp.textContent = '…';
                sp.className = 'mj-page-info';
                el.appendChild(sp);
            }
            el.appendChild(makeBtn(String(last_page), last_page, { active: current_page === last_page }));
        }

        el.appendChild(makeBtn('›', current_page + 1, { disabled: current_page >= last_page }));
    }

    function renderJobs(section, onPageClick) {
        const items = section?.data || [];
        const pagination = section?.pagination;

        if (items.length === 0) {
            els.jobsSection.classList.add('d-none');
            return false;
        }

        els.jobsGrid.innerHTML = items.map(jobCard).join('');
        els.jobsCount.textContent = (pagination?.total ?? items.length) + ' job' + ((pagination?.total ?? items.length) === 1 ? '' : 's');
        els.jobsSection.classList.remove('d-none');

        renderPagination(els.jobsPagination, pagination, onPageClick);
        bindCardActions(els.jobsGrid);
        return true;
    }

    function bindCardActions(scope) {
        scope.querySelectorAll('.mj-apply-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (btn.dataset.applied === '1') {
                    toast('info', 'You have already applied to this job.', 'Applied');
                    return;
                }

                const jobId   = btn.dataset.jobId;
                const wasEasy = btn.dataset.easy === '1';

                if (wasEasy) {
                    const original = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = 'Applying…';

                    const data = await trackApply(jobId);

                    if (data.success) {
                        btn.dataset.applied = '1';
                        btn.disabled = true;
                        btn.innerHTML = 'Applied';
                        toast('success', 'Application submitted!', 'Applied');
                    } else {
                        toast('error', data.message || 'Could not apply.', 'Error');
                        btn.disabled = false;
                        btn.innerHTML = original;
                    }
                    return;
                }

                const link = btn.closest('.mj-job-card').querySelector('a.mj-card-title');
                if (link) window.open(link.getAttribute('href'), '_blank');
            });
        });

        scope.querySelectorAll('.mj-save-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const jobId = btn.dataset.jobId;
                const original = btn.innerHTML;
                btn.disabled = true;

                try {
                    const res = await fetch(SAVE_ENDPOINT(jobId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({}),
                    });
                    const data = await res.json();

                    if (data.success) {
                        const nowSaved = !!data.is_saved;
                        btn.dataset.saved = nowSaved ? '1' : '0';
                        btn.innerHTML = `<i class="bi ${nowSaved ? 'bi-heart-fill text-danger' : 'bi-heart'} me-1"></i> ${nowSaved ? 'Saved' : 'Save'}`;
                        toast('success', nowSaved ? 'Job saved.' : 'Job removed.', nowSaved ? 'Saved' : 'Unsaved');
                    } else {
                        btn.innerHTML = original;
                        toast('error', data.message || 'Could not update.', 'Error');
                    }
                } catch (e) {
                    btn.innerHTML = original;
                    toast('error', 'Network error.', 'Error');
                } finally {
                    btn.disabled = false;
                }
            });
        });
    }

    async function trackApply(jobId) {
        try {
            const res = await fetch(APPLY_ENDPOINT(jobId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ source: 'my_jobs' }),
            });
            return await res.json();
        } catch (e) {
            return { success: false, message: 'Network error.' };
        }
    }

    async function load(showSpinner = false) {
        if (showSpinner) els.loading.classList.remove('d-none');

        // Hide everything first
        ['notConfigured', 'categoryNotSet', 'categoryEmpty', 'skillsNotSet', 'skillsEmpty', 'jobsSection'].forEach(k => {
            els[k].classList.add('d-none');
        });

        try {
            const url = new URL(DATA_URL, window.location.origin);
            url.searchParams.set('cat_page', state.catPage);
            url.searchParams.set('skill_page', state.skillPage);

            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            els.loading.classList.add('d-none');

            // ── Case 1: nothing configured at all
            if (!data.configured) {
                els.notConfigured.classList.remove('d-none');
                return;
            }

            const filters = data.filters || {};

            // ════════════════════════════════════════════════════
            // CATEGORY TAB
            // ════════════════════════════════════════════════════
            if (TAB === 'category') {
                // Category filter isn't on → CTA to set preferences
                if (!filters.by_category) {
                    els.categoryNotSet.classList.remove('d-none');
                    return;
                }

                const section = data.by_category || { data: [], pagination: null };

                if (!renderJobs(section, (page) => {
                    state.catPage = page;
                    load();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                })) {
                    // No jobs in chosen categories
                    els.categoryEmpty.classList.remove('d-none');
                }
                return;
            }

            // ════════════════════════════════════════════════════
            // SKILLS TAB
            // ════════════════════════════════════════════════════
            if (TAB === 'skills') {
                // ── Skills exist but skill-match alerts are OFF → tell them to enable it
                if (filters.has_skills && !filters.by_skill_match) {
                    els.skillsNotSet.classList.remove('d-none');
                    // Change text to be about enabling alerts, not about uploading CV
                    const ctaTitle = els.skillsNotSet.querySelector('h5');
                    const ctaBody  = els.skillsNotSet.querySelector('p');
                    const btnPrimary = els.skillsNotSet.querySelector('.btn-primary');
                    const btnLight   = els.skillsNotSet.querySelector('.btn-light');

                    if (ctaTitle) ctaTitle.textContent = 'Turn on skill match alerts';
                    if (ctaBody)  ctaBody.textContent  = 'You have skills saved on your profile, but skill-match alerts are turned off. Enable them to see jobs matched to your skills.';
                    if (btnPrimary) {
                        btnPrimary.innerHTML = '<i class="ki-duotone ki-setting-2 fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Enable Skill Alerts';
                        btnPrimary.href = '{{ route('alert.preferences') }}';
                    }
                    if (btnLight) btnLight.style.display = 'none';
                    return;
                }

                // ── No skills at all → CTA to add skills (or upload CV as a shortcut)
                if (!filters.has_skills) {
                    els.skillsNotSet.classList.remove('d-none');

                    const ctaTitle = els.skillsNotSet.querySelector('h5');
                    const ctaBody  = els.skillsNotSet.querySelector('p');

                    if (ctaTitle) ctaTitle.textContent = 'Add skills to see matching jobs';
                    if (ctaBody) {
                        ctaBody.textContent = filters.has_cv_file
                            ? 'Your CV is uploaded but your skills list is empty. Add your skills to unlock skill-based matches.'
                            : 'Add skills to your profile to unlock skill-based matches. You can add them manually or upload a CV to auto-fill them.';
                    }
                    return;
                }

                // ── Skills exist AND alerts are on → render matches
                const section = data.by_skill_match || { data: [], pagination: null };

                if (!renderJobs(section, (page) => {
                    state.skillPage = page;
                    load();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                })) {
                    els.skillsEmpty.classList.remove('d-none');
                }
                return;
            }

        } catch (e) {
            console.error('[my-jobs] load failed:', e);
            els.loading.classList.add('d-none');
            els.notConfigured.classList.remove('d-none');
            toast('error', 'Could not load your jobs.', 'Error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => load(true));
})();
</script>
@endpush