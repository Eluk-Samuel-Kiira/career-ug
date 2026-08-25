@extends('layouts.app')

@php
    $jobTitle = $job['job_title'] ?? 'Job';
    $companyName = $job['company']['name'] ?? 'Company';
@endphp

@section('title', ($job['meta_title'] ?? ($jobTitle . ' at ' . $companyName)) . ' — ' . country_name())
@section('meta_description', $job['meta_description'] ?? \Illuminate\Support\Str::limit(trim(strip_tags($job['job_description'] ?? '')), 160))

@push('styles')
<style>
	:root{
		--jp-navy: #0B1C2E;
		--jp-navy-2: #13273D;
		--jp-green: #20AA3E;
		--jp-teal: #03A588;
		--jp-gradient: linear-gradient(135deg, #20AA3E 0%, #03A588 100%);
		--jp-ink: #0F1B2D;
		--jp-muted: #64748B;
		--jp-bg-soft: #F4F8F7;
		--jp-bg-page: #E7EFEF;
		--jp-line: rgba(15,27,45,0.08);
	}

	.jp-page-bg{
		background:var(--jp-bg-page);
		background-image:
			radial-gradient(65% 45% at 100% 0%, rgba(3,165,136,0.12) 0%, transparent 60%),
			radial-gradient(50% 40% at 0% 10%, rgba(11,28,46,0.08) 0%, transparent 60%);
		border-top:3px solid var(--jp-teal);
	}

	.jp-breadcrumb{ font-size:.82rem; color:var(--jp-muted); }
	.jp-breadcrumb a{ color:var(--jp-muted); text-decoration:none; }
	.jp-breadcrumb a:hover{ color:var(--jp-teal); }

	.jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
	.jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }
	.jp-btn-outline{ border:1.5px solid var(--jp-line) !important; color:var(--jp-ink); font-weight:700; border-radius:10px; background:#fff; }
	.jp-btn-outline:hover{ border-color:var(--jp-teal); color:var(--jp-teal);     background: rgba(3, 165, 136, 0.05); }

	.jp-logo-sq{ width:64px; height:64px; border-radius:14px; background:var(--jp-bg-soft); color:var(--jp-navy); border:1px solid var(--jp-line); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.3rem; overflow:hidden; flex-shrink:0; }
	.jp-logo-sq img{ width:100%; height:100%; object-fit:cover; }
	.jp-logo-sq-sm{ width:44px; height:44px; border-radius:10px; font-size:.95rem; }

	.jp-pill{ background:var(--jp-bg-soft); color:#3B5166; font-size:11px; font-weight:700; padding:5px 12px; border-radius:20px; border:1px solid var(--jp-line); display:inline-flex; align-items:center; gap:5px; }
	.jp-pill-urgent{ background:#FEECEC; color:#C0392B; border-color:rgba(192,57,43,0.15); }
	.jp-pill-featured{ background:#FFF6E0; color:#B8860B; border-color:rgba(184,134,11,0.15); }
	.jp-pill-verified{ background:#E9F9EF; color:#1E9E4C; border-color:rgba(30,158,76,0.15); }
	.jp-pill-legacy{ background:#F1F3F5; color:#8A94A0; }
	.jp-skill-tag{ background:#fff; border:1px solid var(--jp-line); color:var(--jp-ink); font-size:.82rem; font-weight:600; padding:6px 13px; border-radius:8px; }

	.jp-header-card{ background:linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%); border:1px solid var(--jp-line); border-radius:16px; padding:28px; box-shadow:0 10px 26px rgba(11,28,46,0.06); }
	.jp-header-card h1{ font-size:1.55rem; font-weight:800; color:var(--jp-ink); margin-bottom:.35rem; }
	.jp-meta-row{ font-size:.88rem; color:var(--jp-muted); }
	.jp-meta-row i{ color:#94A3B8; margin-right:5px; }

	.jp-content-card{ background:#fff; border:1px solid var(--jp-line); border-radius:16px; padding:28px; box-shadow:0 6px 18px rgba(11,28,46,0.05); }
	.jp-content-card h2{ font-size:1.05rem; font-weight:800; color:var(--jp-ink); margin-bottom:1rem; display:flex; align-items:center; gap:8px; }
	.jp-content-card h2 i{ color:var(--jp-teal); }

	.jp-prose{ color:#33475B; font-size:.96rem; line-height:1.75; }
	.jp-prose p{ margin-bottom:1em; }
	.jp-prose ul, .jp-prose ol{ padding-left:1.3em; margin-bottom:1em; }
	.jp-prose li{ margin-bottom:.4em; }
	.jp-prose a{ color:var(--jp-teal); font-weight:600; }
	.jp-prose strong{ color:var(--jp-ink); }
	.jp-prose table{ width:100%; border-collapse:collapse; margin-bottom:1em; }
	.jp-prose table td, .jp-prose table th{ border:1px solid var(--jp-line); padding:8px 10px; font-size:.9rem; }

	.jp-sidebar-card{ background:#fff; border:1px solid var(--jp-line); border-radius:16px; padding:24px; box-shadow:0 6px 18px rgba(11,28,46,0.05); }
	.jp-sidebar-card + .jp-sidebar-card{ margin-top:20px; }
	.jp-fact-row{ display:flex; align-items:flex-start; gap:10px; padding:9px 0; border-bottom:1px solid var(--jp-line); font-size:.87rem; }
	.jp-fact-row:last-child{ border-bottom:none; }
	.jp-fact-row i{ color:var(--jp-teal); margin-top:2px; }
	.jp-fact-label{ color:var(--jp-muted); font-weight:600; display:block; font-size:.75rem; text-transform:uppercase; letter-spacing:.03em; }
	.jp-fact-value{ color:var(--jp-ink); font-weight:700; }

	.jp-deadline-banner{ border-radius:12px; padding:12px 14px; font-size:.85rem; font-weight:700; display:flex; align-items:center; gap:8px; }
	.jp-deadline-ok{ background:#E9F9EF; color:#1E9E4C; }
	.jp-deadline-soon{ background:#FFF3E0; color:#C77700; }
	.jp-deadline-ongoing{ background:var(--jp-bg-soft); color:var(--jp-muted); }

	.jp-similar-item{ display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--jp-line); text-decoration:none; }
	.jp-similar-item:last-child{ border-bottom:none; }
	.jp-similar-item .title{ color:var(--jp-ink); font-weight:700; font-size:.88rem; }
	.jp-similar-item:hover .title{ color:var(--jp-teal); }
	.jp-similar-item .sub{ color:var(--jp-muted); font-size:.78rem; }

    .jp-similar-item .jp-similar-body{ min-width:0; flex:1 1 auto; }
    .jp-similar-item .title,
    .jp-similar-item .sub{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

	.jp-company-desc{ max-height:130px; overflow:hidden; position:relative; }
	.jp-company-desc::after{ content:""; position:absolute; left:0; right:0; bottom:0; height:36px; background:linear-gradient(180deg, transparent, #fff); }

	.jp-share-btn{ width:36px; height:36px; border-radius:9px; border:1px solid var(--jp-line); background:#fff; display:flex; align-items:center; justify-content:center; color:var(--jp-muted); }
	.jp-share-btn:hover{ color:var(--jp-teal); border-color:var(--jp-teal); }

    .jp-apply-method{ display:flex; gap:14px; align-items:flex-start; background:var(--jp-bg-soft); border:1px solid var(--jp-line); border-radius:14px; padding:16px; }
    .jp-apply-icon{ width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .jp-apply-title{ font-weight:800; color:var(--jp-ink); font-size:.92rem; }
    .jp-apply-sub{ color:var(--jp-muted); font-size:.8rem; margin-bottom:4px; }
    .jp-contact-row{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .jp-contact-chip{ background:#fff; border:1px solid var(--jp-line); border-radius:8px; padding:6px 10px; font-family:monospace; font-size:.82rem; color:var(--jp-ink); cursor:pointer; user-select:none; }
    .jp-contact-chip:hover{ border-color:var(--jp-teal); }
    .jp-contact-chip.jp-copied{ background:#E9F9EF; border-color:#1E9E4C; color:#1E9E4C; font-family:inherit; }

    #applyModal .modal-content{ border-radius:18px; }

    
	@media (min-width: 992px){
		.jp-sticky-col{ position:sticky; top:24px; align-self:flex-start; }
	}
</style>


@endpush

@section('content')

@php
    $isLegacy = (bool) ($job['is_legacy'] ?? false);
    $hasStructured = $job['has_structured_content'] ?? true;

    $companyLogo = $job['company']['logo'] ?? null;
    $companyWebsite = $job['company']['website'] ?? null;
    $companyDescription = $job['company']['description'] ?? '';

    $location = $job['job_location']['name'] ?? ($job['duty_station'] ?: null);
    $jobTypeName = $job['job_type']['name'] ?? ucfirst(str_replace('-', ' ', $job['employment_type'] ?? 'Full-time'));
    $category = $job['job_category']['name'] ?? null;
    $industry = $job['industry']['name'] ?? null;
    $experienceLevel = $job['experience_level']['name'] ?? null;
    $educationLevel = $job['education_level']['name'] ?? null;
    $salary = $job['formatted_salary'] ?? 'Negotiable';

    $initials = trim($companyName) !== '' ? strtoupper(substr($companyName, 0, 2)) : 'JM';

    $postedAgo = null;
    if (!empty($job['published_at'])) {
        try { $postedAgo = \Carbon\Carbon::parse($job['published_at'])->diffForHumans(); } catch (\Throwable $e) {}
    }

    $hasRealDeadline = !empty($job['has_real_deadline']) && !empty($job['deadline']);
    $daysLeft = null;
    if ($hasRealDeadline) {
        try { $daysLeft = (int) now()->diffInDays(\Carbon\Carbon::parse($job['deadline']), false); } catch (\Throwable $e) {}
    }

    $applicationProcedure = trim(strip_tags($job['application_procedure'] ?? '')) !== '' ? $job['application_procedure'] : null;

    // Best-effort: pull the real apply URL out of application_procedure's markup
    // (both the AI-generated flow and most legacy content already embed a proper
    // <a href> - reuse it for the header CTA instead of guessing a new one).
    $applyUrl = null;
    if ($applicationProcedure && preg_match('/href=["\']([^"\']+)["\']/i', $applicationProcedure, $m)) {
        $applyUrl = $m[1];
    }
    $applyMailto = !$applyUrl && !empty($job['email']) ? 'mailto:' . $job['email'] : null;
@endphp

<!-- ====================== BREADCRUMB ====================== -->
<div class="border-bottom" style="border-color:var(--jp-line) !important; background:#fff;">
	<div class="container py-3">
		<div class="jp-breadcrumb">
			<a href="{{ route('jobs.index') }}">Jobs</a>
			@if($category)
				<span class="mx-1">/</span>
				<a href="{{ route('jobs.index') }}?category_id={{ $job['job_category']['id'] ?? '' }}">{{ $category }}</a>
			@endif
			<span class="mx-1">/</span>
			<span class="text-gray-700">{{ $jobTitle }}</span>
		</div>
	</div>
</div>

<div class="jp-page-bg">
	<div class="container py-10 py-lg-12">

		<!-- ====================== HEADER ====================== -->
		<div class="jp-header-card mb-6">
			<div class="d-flex flex-wrap gap-4 align-items-start justify-content-between">
				<div class="d-flex gap-4">
					<div class="jp-logo-sq">
						@if($companyLogo)
							<img src="{{ $companyLogo }}" alt="{{ $companyName }}">
						@else
							{{ $initials }}
						@endif
					</div>
					<div>
						<h1>{{ $jobTitle }}</h1>
						<div class="fw-semibold text-gray-700 mb-2">
							{{ $companyName }}
							@if($companyWebsite)
								· <a href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none" style="color:var(--jp-teal);">Visit website</a>
							@endif
						</div>
						<div class="d-flex flex-wrap gap-4 jp-meta-row">
							@if($location)
								<span><i class="ki-duotone ki-geolocation fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $location }}</span>
							@endif
							<span><i class="ki-duotone ki-briefcase fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $jobTypeName }}</span>
							@if($postedAgo)
								<span><i class="ki-duotone ki-calendar fs-6"><span class="path1"></span><span class="path2"></span></i>Posted {{ $postedAgo }}</span>
							@endif
							@if(!empty($job['view_count']))
								<span><i class="ki-duotone ki-eye fs-6"><span class="path1"></span><span class="path2"></span></i>{{ number_format($job['view_count']) }} views</span>
							@endif
						</div>
					</div>
				</div>

				<div class="d-flex flex-column align-items-lg-end gap-2">
					<div class="jp-salary fs-4 fw-bold" style="color:var(--jp-teal);">{{ $salary }}</div>
					<div class="d-flex gap-2 flex-wrap">
						@if(!empty($job['is_featured']))<span class="jp-pill jp-pill-featured"><i class="ki-duotone ki-star"></i>Featured</span>@endif
						@if(!empty($job['is_urgent']))<span class="jp-pill jp-pill-urgent"><i class="ki-duotone ki-flash"></i>Urgent</span>@endif
						@if(!empty($job['is_verified']))<span class="jp-pill jp-pill-verified"><i class="ki-duotone ki-verify"></i>Verified</span>@endif
					</div>
				</div>
			</div>

			<div class="d-flex flex-wrap align-items-center gap-3 mt-6 pt-6" style="border-top:1px solid var(--jp-line);">
				<button type="button" class="btn jp-btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal">
                    <i class="ki-duotone ki-send fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>Apply Now
                </button>
				<button type="button" class="btn jp-btn-outline px-6 py-3">
					<i class="ki-duotone ki-heart fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>Save
				</button>
                <div class="d-flex gap-2 ms-auto">
                    <a class="jp-share-btn" href="mailto:?subject={{ urlencode($jobTitle) }}&body={{ urlencode(request()->fullUrl()) }}" title="Share by email">
                        <i class="ki-duotone ki-sms fs-4"><span class="path1"></span><span class="path2"></span></i>
                    </a>
                    <a class="jp-share-btn" href="https://wa.me/?text={{ urlencode($jobTitle . ' - ' . request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer" title="Share on WhatsApp">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 6.32A8.86 8.86 0 0 0 12.02 3.5c-4.87 0-8.83 3.94-8.83 8.79 0 1.55.41 3.06 1.19 4.39L3.2 21l4.44-1.16a8.9 8.9 0 0 0 4.37 1.12h.01c4.87 0 8.83-3.94 8.83-8.79a8.7 8.7 0 0 0-2.25-5.85Zm-5.58 13.5h-.01c-1.36 0-2.7-.36-3.86-1.05l-.28-.16-2.87.75.77-2.79-.18-.29a7.3 7.3 0 0 1-1.13-3.9c0-4.03 3.3-7.32 7.36-7.32a7.3 7.3 0 0 1 5.2 2.15 7.24 7.24 0 0 1 2.16 5.17c0 4.03-3.3 7.32-7.36 7.32Zm4.03-5.48c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.58.71-.71.86-.26.16-.48.05a6.03 6.03 0 0 1-1.77-1.09 6.6 6.6 0 0 1-1.22-1.52c-.13-.22 0-.34.1-.45.1-.1.22-.26.33-.39.11-.13.15-.22.22-.37.07-.15.04-.28-.02-.39-.06-.11-.5-1.2-.68-1.65-.18-.43-.36-.37-.5-.38h-.43c-.15 0-.39.06-.6.28-.2.22-.79.77-.79 1.87 0 1.1.81 2.17.92 2.32.11.15 1.6 2.44 3.87 3.42.54.23.96.37 1.29.48.54.17 1.03.15 1.42.09.43-.06 1.3-.53 1.49-1.04.18-.51.18-.94.13-1.03-.05-.09-.2-.15-.42-.26Z"/></svg>
                    </a>
                    <a class="jp-share-btn" href="https://twitter.com/intent/tweet?text={{ urlencode($jobTitle) }}&url={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer" title="Share on X">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-7.6 8.7L23 22h-7l-5.5-6.9L4.2 22H1l8.1-9.3L1 2h7.2l5 6.3L18.9 2Zm-1.2 18h1.7L7.4 4H5.6l12.1 16Z"/></svg>
                    </a>
                    <button type="button" class="jp-share-btn" title="Copy link" onclick="navigator.clipboard.writeText(window.location.href); this.querySelector('i').outerHTML='&lt;i class=\'ki-duotone ki-check fs-4\'&gt;&lt;span class=\'path1\'&gt;&lt;/span&gt;&lt;span class=\'path2\'&gt;&lt;/span&gt;&lt;/i&gt;';">
                        <i class="ki-duotone ki-copy fs-4"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                </div>
			</div>
		</div>

		<div class="row g-6">
			<!-- ====================== MAIN CONTENT ====================== -->
			<div class="col-lg-8">
				<div class="d-flex flex-column gap-5">

					<div class="jp-content-card">
						<h2><i class="ki-duotone ki-document fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Job Description
                        </h2>
						<div class="jp-prose">{!! $job['job_description'] ?? '<p>No description provided.</p>' !!}</div>
					</div>

					{{-- Legacy-migrated postings have everything folded into job_description
					     above and empty responsibilities/qualifications/skills - has_structured_content
					     tells us not to render empty section headers for those. --}}
					@if($hasStructured && trim(strip_tags($job['responsibilities'] ?? '')) !== '')
					<div class="jp-content-card">
                        <h2>
                            <i class="ki-duotone ki-check-circle fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Key Responsibilities
                        </h2>
						<div class="jp-prose">{!! $job['responsibilities'] !!}</div>
					</div>
					@endif

					@if($hasStructured && trim(strip_tags($job['qualifications'] ?? '')) !== '')
					<div class="jp-content-card">
						<h2><i class="ki-duotone ki-shield-tick fs-3"><span class="path1"></span>
                            <span class="path2"></span></i>Qualifications</h2>
						<div class="jp-prose">{!! $job['qualifications'] !!}</div>
					</div>
					@endif

                    @php
                        $skills = collect();
                        
                        if (!empty($job['skills'])) {
                            // Strip HTML tags to get clean text
                            $skillsText = strip_tags($job['skills']);
                            
                            // Split by commas and clean up each skill
                            $skillsArray = array_map('trim', explode(',', $skillsText));
                            $skills = collect($skillsArray)
                                ->filter(fn($s) => !empty($s))
                                ->values();
                        }
                    @endphp

                    @if($skills->isNotEmpty())
                    <div class="jp-content-card">
                        <h2>
                            <i class="ki-duotone ki-gear fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Skills
                        </h2>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($skills as $skill)
                                <span class="jp-skill-tag" style="font-family: Arial, Helvetica, sans-serif; font-size: 11pt; font-weight: normal;">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @php
                        $emails = !empty($job['email']) ? array_values(array_filter(array_map('trim', explode(',', $job['email'])))) : [];
                        $phones = !empty($job['telephone']) ? array_values(array_filter(array_map('trim', explode(',', $job['telephone'])))) : [];
                        $hasWhatsapp = in_array($job['is_whatsapp_contact'] ?? false, [true, 1, '1'], true);
                        $hasPhoneCall = in_array($job['is_telephone_call'] ?? false, [true, 1, '1'], true);

                        // One canonical apply-link extraction, used everywhere instead of three different regexes
                        $applyUrl = null;
                        if ($applicationProcedure) {
                            if (preg_match('/href=["\']([^"\']+)["\']/i', $applicationProcedure, $m)) {
                                $applyUrl = $m[1];
                            } elseif (preg_match('/https?:\/\/[^\s<>"\']+/i', $applicationProcedure, $m)) {
                                $applyUrl = $m[0];
                            }
                        }

                        $hasApplicationMethod = $applyUrl || ($hasWhatsapp && $phones) || ($hasPhoneCall && $phones) || $emails;
                    @endphp
                    <div class="jp-content-card" id="apply">
                        <h2><i class="ki-duotone ki-send fs-3"><span class="path1"></span><span class="path2"></span></i>How to Apply</h2>

                        @if($applicationProcedure)
                            <div class="jp-prose">{!! $applicationProcedure !!}</div>
                        @else
                            <div class="jp-prose">
                                <p>To apply for this role{{ $companyName ? ' at ' . $companyName : '' }}, use one of the contact options below.</p>
                            </div>
                        @endif

                        @if(!empty($job['is_resume_required']) || !empty($job['is_cover_letter_required']) || !empty($job['is_academic_documents_required']))
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            @if($job['is_resume_required'])<span class="jp-pill">Resume / CV required</span>@endif
                            @if($job['is_cover_letter_required'])<span class="jp-pill">Cover letter required</span>@endif
                            @if($job['is_academic_documents_required'])<span class="jp-pill">Academic documents required</span>@endif
                        </div>
                        @endif

                        <button type="button" class="btn jp-btn-primary mt-5" data-bs-toggle="modal" data-bs-target="#applyModal">
                            <i class="ki-duotone ki-send fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                            {{ $hasApplicationMethod ? 'Choose How to Apply' : 'View Application Options' }}
                        </button>
                    </div>
				</div>
			</div>

			<!-- ====================== SIDEBAR ====================== -->
			<div class="col-lg-4">
				<div class="jp-sticky-col">

					<div class="jp-sidebar-card">
						@if($hasRealDeadline)
							@if($daysLeft !== null && $daysLeft < 0)
								<div class="jp-deadline-banner jp-deadline-soon mb-4">
									<i class="ki-duotone ki-information-5 fs-3"></i>This listing has expired
								</div>
							@elseif($daysLeft !== null && $daysLeft <= 5)
								<div class="jp-deadline-banner jp-deadline-soon mb-4">
									<i class="ki-duotone ki-timer fs-3"></i>{{ $daysLeft === 0 ? 'Closes today' : "Closes in {$daysLeft} day" . ($daysLeft === 1 ? '' : 's') }}
								</div>
							@else
								<div class="jp-deadline-banner jp-deadline-ok mb-4">
									<i class="ki-duotone ki-check-circle fs-3"></i>Apply by {{ \Carbon\Carbon::parse($job['deadline'])->format('d M Y') }}
								</div>
							@endif
						@else
							<div class="jp-deadline-banner jp-deadline-ongoing mb-4">
								<i class="ki-duotone ki-infinity fs-3"></i>Ongoing listing
							</div>
						@endif

						<div class="jp-fact-row">
							<i class="ki-duotone ki-dollar fs-3"></i>
							<div><span class="jp-fact-label">Salary</span><span class="jp-fact-value">{{ $salary }}</span></div>
						</div>
						<div class="jp-fact-row">
							<i class="ki-duotone ki-briefcase fs-3"></i>
							<div><span class="jp-fact-label">Job Type</span><span class="jp-fact-value">{{ $jobTypeName }}</span></div>
						</div>
						@if($location)
						<div class="jp-fact-row">
							<i class="ki-duotone ki-geolocation fs-3"></i>
							<div><span class="jp-fact-label">Location</span><span class="jp-fact-value">{{ $location }}</span></div>
						</div>
						@endif
						@if($category)
						<div class="jp-fact-row">
							<i class="ki-duotone ki-category fs-3"></i>
							<div><span class="jp-fact-label">Category</span><span class="jp-fact-value">{{ $category }}</span></div>
						</div>
						@endif
						@if($industry)
						<div class="jp-fact-row">
							<i class="ki-duotone ki-building fs-3"></i>
							<div><span class="jp-fact-label">Industry</span><span class="jp-fact-value">{{ $industry }}</span></div>
						</div>
						@endif
						@if($experienceLevel)
						<div class="jp-fact-row">
							<i class="ki-duotone ki-medal-star fs-3"></i>
							<div><span class="jp-fact-label">Experience</span><span class="jp-fact-value">{{ $experienceLevel }}</span></div>
						</div>
						@endif
						@if($educationLevel)
						<div class="jp-fact-row">
							<i class="ki-duotone ki-teacher fs-3"></i>
							<div><span class="jp-fact-label">Education</span><span class="jp-fact-value">{{ $educationLevel }}</span></div>
						</div>
						@endif
						@if(!empty($job['work_hours']))
						<div class="jp-fact-row">
							<i class="ki-duotone ki-time fs-3"></i>
							<div><span class="jp-fact-label">Work Hours</span><span class="jp-fact-value">{{ $job['work_hours'] }}</span></div>
						</div>
						@endif

						<div class="jp-fact-row">
							<button type="button" class="btn jp-btn-primary px-8 py-3" data-bs-toggle="modal" data-bs-target="#applyModal">
								<i class="ki-duotone ki-send fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>Apply Now
							</button>
						</div>
					</div>

					@if($companyName)
					<div class="jp-sidebar-card">
						<div class="d-flex align-items-center gap-3 mb-3">
							<div class="jp-logo-sq jp-logo-sq-sm">
								@if($companyLogo)<img src="{{ $companyLogo }}" alt="{{ $companyName }}">@else{{ $initials }}@endif
							</div>
							<div>
								<div class="fw-bold" style="color:var(--jp-ink);">{{ $companyName }}</div>
								@if($companyWebsite)<a href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer" class="fs-8 text-decoration-none" style="color:var(--jp-teal);">Visit website</a>@endif
							</div>
						</div>
						@if(trim(strip_tags($companyDescription)) !== '')
							<div class="jp-company-desc jp-prose fs-8">{!! $companyDescription !!}</div>
						@endif
					</div>
					@endif

                    @if(!empty($job['similar_jobs']) && count($job['similar_jobs']) > 0)
                    <div class="jp-sidebar-card">
                        <h2 class="fs-7 fw-bold mb-3" style="color:var(--jp-ink);">Similar Jobs</h2>
                        @foreach($job['similar_jobs'] as $similar)
                            @php $simInitials = strtoupper(substr($similar['company']['name'] ?? 'JM', 0, 2)); @endphp
                            <a href="{{ route('jobs.show', $similar['slug'] ?? $similar['id']) }}" class="jp-similar-item">
                                <div class="jp-logo-sq jp-logo-sq-sm">
                                    @if(!empty($similar['company']['logo']))<img src="{{ $similar['company']['logo'] }}" alt="">@else{{ $simInitials }}@endif
                                </div>
                                <div class="jp-similar-body">
                                    <div class="title text-truncate">{{ $similar['job_title'] ?? 'Job' }}</div>
                                    <div class="sub text-truncate">{{ $similar['company']['name'] ?? 'Company' }} @if(!empty($similar['duty_station'])) · {{ $similar['duty_station'] }} @endif</div>
                                    <div class="sub fw-bold text-truncate" style="color:var(--jp-teal);">{{ $similar['formatted_salary'] ?? 'Negotiable' }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @endif

				</div>
			</div>
		</div>
	</div>
	<div class="mt-10 mb-n20 position-relative z-index-2">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-lg-10 col-xl-12">  
					<div class="d-flex flex-stack flex-wrap flex-md-nowrap card-rounded shadow p-8 p-lg-12" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
						<div class="my-2 me-5">
							<div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Ready to make your next move?</div>
							<div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Join thousands of {{ country_citizens() }} hiring and getting hired faster with AI-powered matching.</div>
						</div>
						<div class="d-flex flex-column flex-sm-row gap-3 flex-shrink-0 my-2">
							<a href="{{ route('register') }}?as=seeker" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">Find a Job</a>
							<a href="{{ route('register') }}?as=employer" class="btn btn-lg btn-light fw-bold">Post a Job</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>




   
@include('jobs.apply-modal')
{{-- Structured data for search engines --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org/',
    '@type' => 'JobPosting',
    'title' => $jobTitle,
    'description' => trim(strip_tags($job['job_description'] ?? '')),
    'datePosted' => $job['published_at'] ?? null,
    'validThrough' => $hasRealDeadline ? $job['deadline'] : null,
    'employmentType' => strtoupper(str_replace('-', '_', $job['employment_type'] ?? 'FULL_TIME')),
    'hiringOrganization' => [
        '@type' => 'Organization',
        'name' => $companyName,
        'sameAs' => $companyWebsite,
        'logo' => $companyLogo,
    ],
    'jobLocation' => [
        '@type' => 'Place',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $location,
            'addressCountry' => $job['job_location']['country'] ?? null,
        ],
    ],
    'baseSalary' => !empty($job['salary_amount']) ? [
        '@type' => 'MonetaryAmount',
        'currency' => $job['currency'] ?? 'AUD',
        'value' => [
            '@type' => 'QuantitativeValue',
            'value' => $job['salary_amount'],
            'unitText' => strtoupper($job['payment_period'] ?? 'MONTH'),
        ],
    ] : null,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

@endsection