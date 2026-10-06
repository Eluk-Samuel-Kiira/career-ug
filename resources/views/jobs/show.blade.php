@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    /* ---------------------------------------------------------------
     | 1. Core data (computed once, up top, so SEO sections can use it)
     * --------------------------------------------------------------- */
    $jobTitle    = $job['job_title'] ?? 'Job';
    $companyName = $job['company']['name'] ?? 'Company';

    $isLegacy      = (bool) ($job['is_legacy'] ?? false);
    $hasStructured = $job['has_structured_content'] ?? true;

    $companyLogo        = $job['company']['logo'] ?? null;
    $companyWebsite     = $job['company']['website'] ?? null;
    $companySlug        = $job['company']['slug'] ?? null;
    $companyDescription = $job['company']['description'] ?? '';

    $jobId           = $job['id'] ?? null;
    $location        = $job['job_location']['name'] ?? ($job['duty_station'] ?? null);
    $jobTypeName     = $job['job_type']['name'] ?? ucfirst(str_replace('-', ' ', $job['employment_type'] ?? 'Full-time'));
    $category        = $job['job_category']['name'] ?? null;
    $industry        = $job['industry']['name'] ?? null;
    $experienceLevel = $job['experience_level']['name'] ?? null;
    $educationLevel  = $job['education_level']['name'] ?? null;
    $salary          = $job['formatted_salary'] ?? 'Negotiable';
    $hasSalary       = trim($salary) !== '' && strtolower(trim($salary)) !== 'negotiable';

    $initials = trim($companyName) !== '' ? strtoupper(substr($companyName, 0, 2)) : 'JM';

    $postedAgo = null;
    if (!empty($job['published_at'])) {
        try { $postedAgo = \Carbon\Carbon::parse($job['published_at'])->diffForHumans(); } catch (\Throwable $e) {}
    }

    $hasRealDeadline = !empty($job['has_real_deadline']) && !empty($job['deadline']);
    $deadlineLabel   = null;
    $daysLeft        = null;
    if ($hasRealDeadline) {
        try {
            $deadlineCarbon = \Carbon\Carbon::parse($job['deadline']);
            $deadlineLabel  = $deadlineCarbon->format('d M Y');
            $daysLeft       = (int) now()->diffInDays($deadlineCarbon, false);
        } catch (\Throwable $e) {}
    }

    $easyApply = (bool) ($job['easy_apply'] ?? false);
    $isApplied = (bool) ($job['is_applied'] ?? false);
    $isSaved   = (bool) ($job['is_saved'] ?? false);

    /* ---------------------------------------------------------------
     | 2. Application contact + apply link (one canonical extraction)
     * --------------------------------------------------------------- */
    $applicationProcedure = trim(strip_tags($job['application_procedure'] ?? '')) !== '' ? $job['application_procedure'] : null;

    $applyUrl = null;
    if ($applicationProcedure) {
        if (preg_match('/href=["\']([^"\']+)["\']/i', $applicationProcedure, $m)) {
            $applyUrl = $m[1];
        } elseif (preg_match('/https?:\/\/[^\s<>"\']+/i', $applicationProcedure, $m)) {
            $applyUrl = $m[0];
        }
    }

    $emails = !empty($job['email']) ? array_values(array_filter(array_map('trim', explode(',', $job['email'])))) : [];
    $phones = !empty($job['telephone']) ? array_values(array_filter(array_map('trim', explode(',', $job['telephone'])))) : [];
    $hasWhatsapp  = in_array($job['is_whatsapp_contact'] ?? false, [true, 1, '1'], true);
    $hasPhoneCall = in_array($job['is_telephone_call'] ?? false, [true, 1, '1'], true);
    $hasApplicationMethod = $applyUrl || ($hasWhatsapp && $phones) || ($hasPhoneCall && $phones) || $emails;

    /* ---------------------------------------------------------------
     | 3. SEO + social share
     * --------------------------------------------------------------- */
    $cleanText = function ($html) {
        $t = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $t));
    };

    // Title: "Job Title at Company in Location — Country"
    $seoTitle = !empty($job['meta_title'])
        ? $job['meta_title']
        : $jobTitle . ' at ' . $companyName . ($location ? ' in ' . $location : '');
    $seoTitle = $seoTitle . ' — ' . country_name();

    // Description: lead + key facts + deadline + snippet, capped at ~158 chars
    $descSource = $cleanText(!empty($job['meta_description']) ? $job['meta_description'] : ($job['job_description'] ?? ''));
    $lead  = $companyName . ' is hiring: ' . $jobTitle . ($location ? ' in ' . $location : '') . '.';
    $facts = array_filter([$jobTypeName, $hasSalary ? $salary : null, $category]);
    $factsLine = $facts ? ' ' . implode(' · ', $facts) . '.' : '';
    $deadlineLine = $deadlineLabel && $daysLeft !== null && $daysLeft >= 0 ? ' Apply by ' . $deadlineLabel . '.' : '';
    $seoDescription = Str::limit(trim($lead . $factsLine . $deadlineLine . ' ' . $descSource . ' Apply today.'), 158, '…');

    $seoKeywords = strtolower(implode(', ', array_filter([
        $jobTitle, $jobTitle . ' jobs', $companyName . ' jobs', $companyName . ' careers',
        $location ? 'jobs in ' . $location : null,
        $category ? $category . ' jobs' : null,
        $jobTypeName . ' jobs',
        'jobs in ' . country_name(),
    ])));

    // Share image: company logo (absolute URL). Falls back to the site default.
    $toAbsolute = function ($url) {
        if (empty($url)) return null;
        if (Str::startsWith($url, ['http://', 'https://'])) return $url;
        if (Str::startsWith($url, '//')) return 'https:' . $url;
        return url($url);
    };
    $shareImage   = $toAbsolute($companyLogo) ?: asset('assets/media/og-default.png');
    $shareHasLogo = !empty($companyLogo);
    $shareImageAlt = $shareHasLogo ? $companyName . ' logo' : $jobTitle . ' — ' . $companyName;
    $canonicalUrl = url()->current();

    /* ---------------------------------------------------------------
     | 4. Structured data (JobPosting + Breadcrumbs), nulls stripped
     * --------------------------------------------------------------- */
    $employmentName = strtolower($jobTypeName);
    $employmentType = 'FULL_TIME';
    if (Str::contains($employmentName, 'part'))                          $employmentType = 'PART_TIME';
    elseif (Str::contains($employmentName, ['contract', 'freelance']))   $employmentType = 'CONTRACTOR';
    elseif (Str::contains($employmentName, ['casual', 'temp', 'seasonal'])) $employmentType = 'TEMPORARY';
    elseif (Str::contains($employmentName, 'intern'))                    $employmentType = 'INTERN';

    $isoDate = function ($d) {
        try { return \Carbon\Carbon::parse($d)->toIso8601String(); } catch (\Throwable $e) { return null; }
    };

    $organization = ['@type' => 'Organization', 'name' => $companyName];
    if ($companyWebsite) $organization['sameAs'] = $companyWebsite;
    if ($companyLogo)    $organization['logo']   = $toAbsolute($companyLogo);

    $jobPosting = [
        '@context'           => 'https://schema.org/',
        '@type'              => 'JobPosting',
        'title'              => $jobTitle,
        'description'        => $job['job_description'] ?? $cleanText(''),
        'datePosted'         => !empty($job['published_at']) ? $isoDate($job['published_at']) : null,
        'validThrough'       => $hasRealDeadline ? $isoDate($job['deadline']) : null,
        'employmentType'     => $employmentType,
        'hiringOrganization' => $organization,
        'directApply'        => $easyApply ? true : null,
        'url'                => $canonicalUrl,
    ];
    if ($location) {
        $jobPosting['jobLocation'] = [
            '@type'   => 'Place',
            'address' => [
                '@type'           => 'PostalAddress',
                'addressLocality' => $location,
                'addressCountry'  => $job['job_location']['country'] ?? null,
            ],
        ];
    }
    if (!empty($job['salary_amount'])) {
        $jobPosting['baseSalary'] = [
            '@type'    => 'MonetaryAmount',
            'currency' => $job['currency'] ?? 'AUD',
            'value'    => [
                '@type'    => 'QuantitativeValue',
                'value'    => $job['salary_amount'],
                'unitText' => strtoupper($job['payment_period'] ?? 'MONTH'),
            ],
        ];
    }

    $stripNulls = function ($arr) use (&$stripNulls) {
        foreach ($arr as $k => $v) {
            if (is_array($v)) $arr[$k] = $v = $stripNulls($v);
            if ($v === null || $v === '' || $v === []) unset($arr[$k]);
        }
        return $arr;
    };
    $jobPosting = $stripNulls($jobPosting);

    $breadcrumbItems = [['name' => 'Jobs', 'url' => route('jobs.index')]];
    if ($category) {
        $breadcrumbItems[] = ['name' => $category, 'url' => route('jobs.index') . '?category_id=' . ($job['job_category']['id'] ?? '')];
    }
    $breadcrumbItems[] = ['name' => $jobTitle, 'url' => $canonicalUrl];
    $breadcrumbLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($breadcrumbItems)->values()->map(fn ($item, $i) => [
            '@type' => 'ListItem', 'position' => $i + 1, 'name' => $item['name'], 'item' => $item['url'],
        ])->all(),
    ];

    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP;

    // Skills
    $skills = collect();
    if (!empty($job['skills'])) {
        $skills = collect(array_map('trim', explode(',', strip_tags($job['skills']))))
            ->filter(fn ($s) => $s !== '')->values();
    }
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('keywords', $seoKeywords)
@section('canonical', $canonicalUrl)

@section('og_type', 'website')
@section('og_url', $canonicalUrl)
@section('og_title', $seoTitle)
@section('og_description', $seoDescription)
@section('og_image', $shareImage)
@section('og_image_alt', $shareImageAlt)

{{-- Square logos look best in the compact "summary" card; the default image uses the large card --}}
@section('twitter_card', $shareHasLogo ? 'summary' : 'summary_large_image')
@section('twitter_title', $seoTitle)
@section('twitter_description', $seoDescription)
@section('twitter_image', $shareImage)
@section('twitter_image_alt', $shareImageAlt)

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

	/* Page guard: no sideways scroll (clip keeps the CTA overhang visible) */
	.jp-page{ overflow-x:clip; width:100%; max-width:100%; }
	.jp-page *, .jp-page *::before, .jp-page *::after{ box-sizing:border-box; }
	.jp-page img{ max-width:100%; }

	.jp-page-bg{
		background:var(--jp-bg-page);
		background-image:
			radial-gradient(65% 45% at 100% 0%, rgba(3,165,136,0.12) 0%, transparent 60%),
			radial-gradient(50% 40% at 0% 10%, rgba(11,28,46,0.08) 0%, transparent 60%);
		border-top:3px solid var(--jp-teal);
	}

	/* Breadcrumb */
	.jp-breadcrumb{ font-size:.82rem; color:var(--jp-muted); overflow-wrap:anywhere; }
	.jp-breadcrumb a{ color:var(--jp-muted); text-decoration:none; }
	.jp-breadcrumb a:hover{ color:var(--jp-teal); }

	/* Buttons */
	.jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
	.jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }
	.jp-btn-outline{ border:1.5px solid var(--jp-line) !important; color:var(--jp-ink); font-weight:700; border-radius:10px; background:#fff; }
	.jp-btn-outline:hover{ border-color:var(--jp-teal); color:var(--jp-teal); background:rgba(3,165,136,0.05); }

	/* Logo tile: logos always shown whole (contain), never cropped */
	.jp-logo-sq{ width:64px; height:64px; border-radius:14px; background:#fff; color:var(--jp-navy); border:1px solid var(--jp-line); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.3rem; overflow:hidden; flex:0 0 auto; padding:6px; }
	.jp-logo-sq img{ width:100%; height:100%; object-fit:contain; object-position:center; display:block; }
	.jp-logo-sq-sm{ width:44px; height:44px; border-radius:10px; font-size:.95rem; padding:4px; }

	/* Pills */
	.jp-pill{ background:var(--jp-bg-soft); color:#3B5166; font-size:11px; font-weight:700; padding:5px 12px; border-radius:20px; border:1px solid var(--jp-line); display:inline-flex; align-items:center; gap:5px; }
	.jp-pill-urgent{ background:#FEECEC; color:#C0392B; border-color:rgba(192,57,43,0.15); }
	.jp-pill-featured{ background:#FFF6E0; color:#B8860B; border-color:rgba(184,134,11,0.15); }
	.jp-pill-verified{ background:#E9F9EF; color:#1E9E4C; border-color:rgba(30,158,76,0.15); }
	.jp-pill-legacy{ background:#F1F3F5; color:#8A94A0; }
	.jp-skill-tag{ background:#fff; border:1px solid var(--jp-line); color:var(--jp-ink); font-size:.82rem; font-weight:600; padding:6px 13px; border-radius:8px; overflow-wrap:anywhere; }

	/* Header card */
	.jp-header-card{ background:linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%); border:1px solid var(--jp-line); border-radius:16px; padding:28px; box-shadow:0 10px 26px rgba(11,28,46,0.06); }
	.jp-header-top{ display:flex; flex-wrap:wrap; gap:20px; align-items:flex-start; justify-content:space-between; }
	.jp-header-id{ display:flex; gap:16px; min-width:0; flex:1 1 380px; }
	.jp-header-id .info{ min-width:0; flex:1 1 auto; }
	.jp-header-card h1{ font-size:clamp(1.25rem, 4.6vw, 1.55rem); font-weight:800; color:var(--jp-ink); margin-bottom:.35rem; line-height:1.25; overflow-wrap:anywhere; }
	.jp-header-side{ display:flex; flex-direction:column; gap:8px; align-items:flex-end; flex:0 1 auto; min-width:0; max-width:100%; }
	.jp-header-salary{ color:var(--jp-teal); font-weight:800; font-size:1.35rem; text-align:right; overflow-wrap:anywhere; }
	.jp-meta-row{ display:flex; flex-wrap:wrap; gap:6px 18px; font-size:.88rem; color:var(--jp-muted); }
	.jp-meta-row > span{ display:inline-flex; align-items:center; min-width:0; max-width:100%; overflow-wrap:anywhere; }
	.jp-meta-row i{ color:#94A3B8; margin-right:5px; flex:0 0 auto; }

	.jp-header-actions{ display:flex; flex-wrap:wrap; align-items:center; gap:12px; margin-top:24px; padding-top:24px; border-top:1px solid var(--jp-line); }
	.jp-share-row{ display:flex; flex-wrap:wrap; gap:8px; margin-left:auto; }
	.jp-share-btn{ width:36px; height:36px; border-radius:9px; border:1px solid var(--jp-line); background:#fff; display:flex; align-items:center; justify-content:center; color:var(--jp-muted); padding:0; cursor:pointer; }
	.jp-share-btn:hover{ color:var(--jp-teal); border-color:var(--jp-teal); }
	.jp-share-btn .icon-done{ display:none; }
	.jp-share-btn.jp-copied{ color:#1E9E4C; border-color:#1E9E4C; }
	.jp-share-btn.jp-copied .icon-copy{ display:none; }
	.jp-share-btn.jp-copied .icon-done{ display:inline-flex; }

	/* Content cards */
	.jp-content-card{ background:#fff; border:1px solid var(--jp-line); border-radius:16px; padding:28px; box-shadow:0 6px 18px rgba(11,28,46,0.05); min-width:0; }
	.jp-content-card h2{ font-size:1.05rem; font-weight:800; color:var(--jp-ink); margin-bottom:1rem; display:flex; align-items:center; gap:8px; }
	.jp-content-card h2 i{ color:var(--jp-teal); }

	/* Rich text from the API: never allowed to break the layout */
	.jp-prose{ color:#33475B; font-size:.96rem; line-height:1.75; overflow-wrap:anywhere; word-break:break-word; min-width:0; }
	.jp-prose p{ margin-bottom:1em; }
	.jp-prose ul, .jp-prose ol{ padding-left:1.3em; margin-bottom:1em; }
	.jp-prose li{ margin-bottom:.4em; }
	.jp-prose a{ color:var(--jp-teal); font-weight:600; }
	.jp-prose strong{ color:var(--jp-ink); }
	.jp-prose img, .jp-prose video, .jp-prose iframe{ max-width:100%; height:auto; }
	.jp-prose pre{ max-width:100%; overflow-x:auto; white-space:pre-wrap; }
	.jp-prose table{ display:block; max-width:100%; overflow-x:auto; border-collapse:collapse; margin-bottom:1em; }
	.jp-prose table td, .jp-prose table th{ border:1px solid var(--jp-line); padding:8px 10px; font-size:.9rem; }

	/* Sidebar */
	.jp-sidebar-card{ background:#fff; border:1px solid var(--jp-line); border-radius:16px; padding:24px; box-shadow:0 6px 18px rgba(11,28,46,0.05); min-width:0; }
	.jp-sidebar-card + .jp-sidebar-card{ margin-top:20px; }
	.jp-fact-row{ display:flex; align-items:flex-start; gap:10px; padding:9px 0; border-bottom:1px solid var(--jp-line); font-size:.87rem; min-width:0; }
	.jp-fact-row:last-child{ border-bottom:none; }
	.jp-fact-row > div{ min-width:0; }
	.jp-fact-row i{ color:var(--jp-teal); margin-top:2px; flex:0 0 auto; }
	.jp-fact-label{ color:var(--jp-muted); font-weight:600; display:block; font-size:.75rem; text-transform:uppercase; letter-spacing:.03em; }
	.jp-fact-value{ color:var(--jp-ink); font-weight:700; overflow-wrap:anywhere; }
	.jp-sidebar-apply{ padding-top:16px; }
	.jp-sidebar-apply .btn{ width:100%; }

	.jp-deadline-banner{ border-radius:12px; padding:12px 14px; font-size:.85rem; font-weight:700; display:flex; align-items:center; gap:8px; }
	.jp-deadline-ok{ background:#E9F9EF; color:#1E9E4C; }
	.jp-deadline-soon{ background:#FFF3E0; color:#C77700; }
	.jp-deadline-ongoing{ background:var(--jp-bg-soft); color:var(--jp-muted); }

	.jp-similar-item{ display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--jp-line); text-decoration:none; min-width:0; }
	.jp-similar-item:last-child{ border-bottom:none; }
	.jp-similar-item .title{ color:var(--jp-ink); font-weight:700; font-size:.88rem; }
	.jp-similar-item:hover .title{ color:var(--jp-teal); }
	.jp-similar-item .sub{ color:var(--jp-muted); font-size:.78rem; }
	.jp-similar-item .jp-similar-body{ min-width:0; flex:1 1 auto; }
	.jp-similar-item .title, .jp-similar-item .sub{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

	.jp-company-desc{ max-height:130px; overflow:hidden; position:relative; }
	.jp-company-desc::after{ content:""; position:absolute; left:0; right:0; bottom:0; height:36px; background:linear-gradient(180deg, transparent, #fff); }

	/* Apply modal helpers (kept for the included modal) */
	.jp-apply-method{ display:flex; gap:14px; align-items:flex-start; background:var(--jp-bg-soft); border:1px solid var(--jp-line); border-radius:14px; padding:16px; }
	.jp-apply-icon{ width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
	.jp-apply-title{ font-weight:800; color:var(--jp-ink); font-size:.92rem; }
	.jp-apply-sub{ color:var(--jp-muted); font-size:.8rem; margin-bottom:4px; }
	.jp-contact-row{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
	.jp-contact-chip{ background:#fff; border:1px solid var(--jp-line); border-radius:8px; padding:6px 10px; font-family:monospace; font-size:.82rem; color:var(--jp-ink); cursor:pointer; user-select:none; overflow-wrap:anywhere; }
	.jp-contact-chip:hover{ border-color:var(--jp-teal); }
	.jp-contact-chip.jp-copied{ background:#E9F9EF; border-color:#1E9E4C; color:#1E9E4C; font-family:inherit; }
	#applyModal .modal-content{ border-radius:18px; }

	/* CTA banner */
	.jp-cta-wrap{ position:relative; z-index:2; margin-top:2.5rem; margin-bottom:-5rem; }
	.jp-cta-banner{ background:linear-gradient(90deg, #20AA3E 0%, #03A588 100%); border-radius:22px; padding:2rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap; box-shadow:0 20px 50px rgba(11,28,46,0.15); }
	.jp-cta-banner .cta-text{ flex:1 1 320px; min-width:0; }
	.jp-cta-banner .cta-actions{ display:flex; gap:12px; flex-wrap:wrap; flex:0 0 auto; }

	@media (min-width: 992px){
		.jp-sticky-col{ position:sticky; top:24px; align-self:flex-start; }
		.jp-cta-banner{ padding:3rem; }
	}

	/* ===== Tablet and below ===== */
	@media (max-width: 767.98px){
		.jp-header-side{ align-items:flex-start; width:100%; }
		.jp-header-salary{ text-align:left; }
	}

	/* ===== Mobile ===== */
	@media (max-width: 575.98px){
		.jp-header-card{ padding:18px; }
		.jp-header-id{ gap:12px; flex-basis:100%; }
		.jp-logo-sq{ width:52px; height:52px; padding:5px; font-size:1.1rem; }
		.jp-header-actions{ margin-top:18px; padding-top:18px; gap:10px; }
		.jp-header-actions .jp-apply-btn{ flex:1 1 100%; justify-content:center; }
		.jp-header-actions .jp-save-job-btn{ flex:1 1 auto; justify-content:center; }
		.jp-share-row{ margin-left:0; flex:0 0 auto; }
		.jp-content-card{ padding:20px 18px; }
		.jp-sidebar-card{ padding:20px 18px; }
		.jp-prose{ font-size:.94rem; }
		.jp-content-card .jp-apply-btn{ width:100%; justify-content:center; }
		.jp-cta-banner{ padding:1.5rem 1.25rem; border-radius:18px; }
		.jp-cta-banner .cta-actions{ width:100%; flex-direction:column; }
		.jp-cta-banner .cta-actions .btn{ width:100%; }
	}
</style>
@endpush

@section('content')

<div class="jp-page">

<!-- ====================== BREADCRUMB ====================== -->
<div class="border-bottom" style="border-color:var(--jp-line) !important; background:#fff;">
	<div class="container py-3">
		<nav class="jp-breadcrumb" aria-label="Breadcrumb">
			<a href="{{ route('jobs.index') }}">Jobs</a>
			@if($category)
				<span class="mx-1">/</span>
				<a href="{{ route('jobs.index') }}?category_id={{ $job['job_category']['id'] ?? '' }}">{{ $category }}</a>
			@endif
			<span class="mx-1">/</span>
			<span class="text-gray-700">{{ $jobTitle }}</span>
		</nav>
	</div>
</div>

<div class="jp-page-bg">
	<div class="container py-10 py-lg-12">

		<!-- ====================== HEADER ====================== -->
		<div class="jp-header-card mb-6">
			<div class="jp-header-top">
				<div class="jp-header-id">
					<div class="jp-logo-sq">
						@if($companyLogo)
							<img src="{{ $companyLogo }}" alt="{{ $companyName }} logo">
						@else
							{{ $initials }}
						@endif
					</div>
					<div class="info">
						<h1>{{ $jobTitle }}</h1>
						<div class="fw-semibold text-gray-700 mb-2" style="overflow-wrap:anywhere;">
							{{ $companyName }}
							@if($companyWebsite)
								· <a href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none" style="color:var(--jp-teal);">Visit website</a>
							@endif
						</div>
						<div class="jp-meta-row">
							@if($location)
								<span><i class="ki-duotone ki-geolocation fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $location }}</span>
							@endif
							<span><i class="ki-duotone ki-briefcase fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $jobTypeName }}</span>
							@if($postedAgo)
								<span><i class="ki-duotone ki-calendar fs-6"><span class="path1"></span><span class="path2"></span></i>Posted {{ $postedAgo }}</span>
							@endif
							@if(!empty($job['view_count']))
								<span><i class="ki-duotone ki-eye fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>{{ number_format($job['view_count']) }} views</span>
							@endif
						</div>
					</div>
				</div>

				<div class="jp-header-side">
					<div class="jp-header-salary">{{ $salary }}</div>
					<div class="d-flex gap-2 flex-wrap">
						@if(!empty($job['is_featured']))<span class="jp-pill jp-pill-featured"><i class="ki-duotone ki-star"><span class="path1"></span><span class="path2"></span></i>Featured</span>@endif
						@if(!empty($job['is_urgent']))<span class="jp-pill jp-pill-urgent"><i class="ki-duotone ki-flash"><span class="path1"></span><span class="path2"></span></i>Urgent</span>@endif
						@if(!empty($job['is_verified']))<span class="jp-pill jp-pill-verified"><i class="ki-duotone ki-verify"><span class="path1"></span><span class="path2"></span></i>Verified</span>@endif
					</div>
				</div>
			</div>

			<div class="jp-header-actions">
				@if($easyApply)
					@if($isApplied)
						<button type="button" class="btn jp-btn-outline px-8 py-3 jp-apply-btn jp-easy-apply-btn" data-job-id="{{ $jobId }}" data-applied="true" disabled>
							<i class="ki-duotone ki-check-circle fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
							Applied
						</button>
					@else
						<button type="button" class="btn jp-btn-primary px-8 py-3 jp-apply-btn jp-easy-apply-btn" data-job-id="{{ $jobId }}" data-job-title="{{ $jobTitle }}" data-company-name="{{ $companyName }}" data-applied="false">
							<i class="ki-duotone ki-flash fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
							Easy Apply
						</button>
					@endif
				@else
					<button type="button" class="btn jp-btn-primary px-8 py-3 jp-apply-btn" data-bs-toggle="modal" data-bs-target="#applyModal">
						<i class="ki-duotone ki-send fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>Apply Now
					</button>
				@endif

				<button type="button" class="btn jp-btn-outline px-6 py-3 jp-save-job-btn"
						data-job-id="{{ $jobId ?? '' }}"
						data-is-saved="{{ $isSaved ? 'true' : 'false' }}">
					<i class="bi bi-{{ $isSaved ? 'heart-fill text-danger' : 'heart' }} fs-3 me-2"></i>
					<span>{{ $isSaved ? 'Saved' : 'Save' }}</span>
				</button>

				<div class="jp-share-row">
					<a class="jp-share-btn" href="mailto:?subject={{ urlencode($jobTitle . ' at ' . $companyName) }}&body={{ urlencode(request()->fullUrl()) }}" title="Share by email" aria-label="Share by email">
						<i class="ki-duotone ki-sms fs-4"><span class="path1"></span><span class="path2"></span></i>
					</a>
					<a class="jp-share-btn" href="https://wa.me/?text={{ urlencode($jobTitle . ' at ' . $companyName . ' - ' . request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer" title="Share on WhatsApp" aria-label="Share on WhatsApp">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 6.32A8.86 8.86 0 0 0 12.02 3.5c-4.87 0-8.83 3.94-8.83 8.79 0 1.55.41 3.06 1.19 4.39L3.2 21l4.44-1.16a8.9 8.9 0 0 0 4.37 1.12h.01c4.87 0 8.83-3.94 8.83-8.79a8.7 8.7 0 0 0-2.25-5.85Zm-5.58 13.5h-.01c-1.36 0-2.7-.36-3.86-1.05l-.28-.16-2.87.75.77-2.79-.18-.29a7.3 7.3 0 0 1-1.13-3.9c0-4.03 3.3-7.32 7.36-7.32a7.3 7.3 0 0 1 5.2 2.15 7.24 7.24 0 0 1 2.16 5.17c0 4.03-3.3 7.32-7.36 7.32Zm4.03-5.48c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.58.71-.71.86-.26.16-.48.05a6.03 6.03 0 0 1-1.77-1.09 6.6 6.6 0 0 1-1.22-1.52c-.13-.22 0-.34.1-.45.1-.1.22-.26.33-.39.11-.13.15-.22.22-.37.07-.15.04-.28-.02-.39-.06-.11-.5-1.2-.68-1.65-.18-.43-.36-.37-.5-.38h-.43c-.15 0-.39.06-.6.28-.2.22-.79.77-.79 1.87 0 1.1.81 2.17.92 2.32.11.15 1.6 2.44 3.87 3.42.54.23.96.37 1.29.48.54.17 1.03.15 1.42.09.43-.06 1.3-.53 1.49-1.04.18-.51.18-.94.13-1.03-.05-.09-.2-.15-.42-.26Z"/></svg>
					</a>
					<a class="jp-share-btn" href="https://twitter.com/intent/tweet?text={{ urlencode($jobTitle . ' at ' . $companyName) }}&url={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer" title="Share on X" aria-label="Share on X">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-7.6 8.7L23 22h-7l-5.5-6.9L4.2 22H1l8.1-9.3L1 2h7.2l5 6.3L18.9 2Zm-1.2 18h1.7L7.4 4H5.6l12.1 16Z"/></svg>
					</a>
					<button type="button" class="jp-share-btn" title="Copy link" aria-label="Copy link" data-copy-link>
						<i class="ki-duotone ki-copy fs-4 icon-copy"><span class="path1"></span><span class="path2"></span></i>
						<i class="ki-duotone ki-check fs-4 icon-done"><span class="path1"></span><span class="path2"></span></i>
					</button>
				</div>
			</div>
		</div>

		<div class="row gy-6 gx-lg-6">
			<!-- ====================== MAIN CONTENT ====================== -->
			<div class="col-lg-8" style="min-width:0;">
				<div class="d-flex flex-column gap-5">

					<div class="jp-content-card">
						<h2>
							<i class="ki-duotone ki-document fs-3"><span class="path1"></span><span class="path2"></span></i>
							Job Description
						</h2>
						<div class="jp-prose">{!! $job['job_description'] ?? '<p>No description provided.</p>' !!}</div>
					</div>

					{{-- Legacy-migrated postings have everything folded into job_description
					     and empty responsibilities/qualifications/skills; has_structured_content
					     stops us rendering empty section headers for those. --}}
					@if($hasStructured && trim(strip_tags($job['responsibilities'] ?? '')) !== '')
					<div class="jp-content-card">
						<h2>
							<i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i>
							Key Responsibilities
						</h2>
						<div class="jp-prose">{!! $job['responsibilities'] !!}</div>
					</div>
					@endif

					@if($hasStructured && trim(strip_tags($job['qualifications'] ?? '')) !== '')
					<div class="jp-content-card">
						<h2>
							<i class="ki-duotone ki-shield-tick fs-3"><span class="path1"></span><span class="path2"></span></i>
							Qualifications
						</h2>
						<div class="jp-prose">{!! $job['qualifications'] !!}</div>
					</div>
					@endif

					@if($skills->isNotEmpty())
					<div class="jp-content-card">
						<h2>
							<i class="ki-duotone ki-gear fs-3"><span class="path1"></span><span class="path2"></span></i>
							Skills
						</h2>
						<div class="d-flex flex-wrap gap-2">
							@foreach($skills as $skill)
								<span class="jp-skill-tag">{{ $skill }}</span>
							@endforeach
						</div>
					</div>
					@endif

					<div class="jp-content-card" id="apply">
						<h2><i class="ki-duotone ki-send fs-3"><span class="path1"></span><span class="path2"></span></i>How to Apply</h2>

						@if($easyApply)
							<p class="text-muted mb-4">Apply in one tap using your saved profile and CV.</p>
							@if($isApplied)
								<button type="button" class="btn jp-btn-outline px-8 py-3 jp-apply-btn jp-easy-apply-btn" data-job-id="{{ $jobId }}" data-applied="true" disabled>
									<i class="ki-duotone ki-check-circle fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
									Applied
								</button>
							@else
								<button type="button" class="btn jp-btn-primary px-8 py-3 jp-apply-btn jp-easy-apply-btn" data-job-id="{{ $jobId }}" data-job-title="{{ $jobTitle }}" data-company-name="{{ $companyName }}" data-applied="false">
									<i class="ki-duotone ki-flash fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
									Easy Apply
								</button>
							@endif
						@else
							@if($applicationProcedure)
								<div class="jp-prose">{!! $applicationProcedure !!}</div>
							@else
								<div class="jp-prose">
									<p>To apply for this role{{ $companyName ? ' at ' . $companyName : '' }}, use one of the contact options below.</p>
								</div>
							@endif

							@if(!empty($job['is_resume_required']) || !empty($job['is_cover_letter_required']) || !empty($job['is_academic_documents_required']))
							<div class="d-flex flex-wrap gap-2 mt-3">
								@if(!empty($job['is_resume_required']))<span class="jp-pill">Resume / CV required</span>@endif
								@if(!empty($job['is_cover_letter_required']))<span class="jp-pill">Cover letter required</span>@endif
								@if(!empty($job['is_academic_documents_required']))<span class="jp-pill">Academic documents required</span>@endif
							</div>
							@endif

							<button type="button" class="btn jp-btn-primary jp-apply-btn mt-5" data-bs-toggle="modal" data-bs-target="#applyModal">
								<i class="ki-duotone ki-send fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
								{{ $hasApplicationMethod ? 'Choose How to Apply' : 'View Application Options' }}
							</button>
						@endif
					</div>
				</div>
			</div>

			<!-- ====================== SIDEBAR ====================== -->
			<div class="col-lg-4" style="min-width:0;">
				<div class="jp-sticky-col">

					<div class="jp-sidebar-card">
						@if($hasRealDeadline)
							@if($daysLeft !== null && $daysLeft < 0)
								<div class="jp-deadline-banner jp-deadline-soon mb-4">
									<i class="ki-duotone ki-information-5 fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>This listing has expired
								</div>
							@elseif($daysLeft !== null && $daysLeft <= 5)
								<div class="jp-deadline-banner jp-deadline-soon mb-4">
									<i class="ki-duotone ki-timer fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>{{ $daysLeft === 0 ? 'Closes today' : "Closes in {$daysLeft} day" . ($daysLeft === 1 ? '' : 's') }}
								</div>
							@else
								<div class="jp-deadline-banner jp-deadline-ok mb-4">
									<i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i>Apply by {{ $deadlineLabel }}
								</div>
							@endif
						@else
							<div class="jp-deadline-banner jp-deadline-ongoing mb-4">
								<i class="ki-duotone ki-infinity fs-3"><span class="path1"></span><span class="path2"></span></i>Ongoing listing
							</div>
						@endif

						<div class="jp-fact-row">
							<i class="ki-duotone ki-dollar fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
							<div><span class="jp-fact-label">Salary</span><span class="jp-fact-value">{{ $salary }}</span></div>
						</div>
						<div class="jp-fact-row">
							<i class="ki-duotone ki-briefcase fs-3"><span class="path1"></span><span class="path2"></span></i>
							<div><span class="jp-fact-label">Job Type</span><span class="jp-fact-value">{{ $jobTypeName }}</span></div>
						</div>
						@if($location)
						<div class="jp-fact-row">
							<i class="ki-duotone ki-geolocation fs-3"><span class="path1"></span><span class="path2"></span></i>
							<div><span class="jp-fact-label">Location</span><span class="jp-fact-value">{{ $location }}</span></div>
						</div>
						@endif
						@if($category)
						<div class="jp-fact-row">
							<i class="ki-duotone ki-category fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
							<div><span class="jp-fact-label">Category</span><span class="jp-fact-value">{{ $category }}</span></div>
						</div>
						@endif
						@if($industry)
						<div class="jp-fact-row">
							<i class="ki-duotone ki-building fs-3"><span class="path1"></span><span class="path2"></span></i>
							<div><span class="jp-fact-label">Industry</span><span class="jp-fact-value">{{ $industry }}</span></div>
						</div>
						@endif
						@if($experienceLevel)
						<div class="jp-fact-row">
							<i class="ki-duotone ki-medal-star fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
							<div><span class="jp-fact-label">Experience</span><span class="jp-fact-value">{{ $experienceLevel }}</span></div>
						</div>
						@endif
						@if($educationLevel)
						<div class="jp-fact-row">
							<i class="ki-duotone ki-teacher fs-3"><span class="path1"></span><span class="path2"></span></i>
							<div><span class="jp-fact-label">Education</span><span class="jp-fact-value">{{ $educationLevel }}</span></div>
						</div>
						@endif
						@if(!empty($job['work_hours']))
						<div class="jp-fact-row">
							<i class="ki-duotone ki-time fs-3"><span class="path1"></span><span class="path2"></span></i>
							<div><span class="jp-fact-label">Work Hours</span><span class="jp-fact-value">{{ $job['work_hours'] }}</span></div>
						</div>
						@endif

						<div class="jp-sidebar-apply">
							@if($easyApply)
								@if($isApplied)
									<button type="button" class="btn jp-btn-outline px-8 py-3 jp-easy-apply-btn" data-job-id="{{ $jobId }}" data-applied="true" disabled>
										<i class="ki-duotone ki-check-circle fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
										Applied
									</button>
								@else
									<button type="button" class="btn jp-btn-primary px-8 py-3 jp-easy-apply-btn" data-job-id="{{ $jobId }}" data-job-title="{{ $jobTitle }}" data-company-name="{{ $companyName }}" data-applied="false">
										<i class="ki-duotone ki-flash fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
										Easy Apply
									</button>
								@endif
							@else
								<button type="button" class="btn jp-btn-primary px-8 py-3" data-bs-toggle="modal" data-bs-target="#applyModal">
									<i class="ki-duotone ki-send fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>Apply Now
								</button>
							@endif
						</div>
					</div>

					@if($companyName)
					<div class="jp-sidebar-card">
						<div class="d-flex align-items-center gap-3 mb-3">
							<div class="jp-logo-sq jp-logo-sq-sm">
								@if($companyLogo)<img src="{{ $companyLogo }}" alt="{{ $companyName }} logo">@else{{ $initials }}@endif
							</div>
							<div style="min-width:0;">
								<div class="fw-bold" style="color:var(--jp-ink); overflow-wrap:anywhere;">{{ $companyName }}</div>
								@if($companyWebsite)<a href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer" class="fs-8 text-decoration-none" style="color:var(--jp-teal);">Visit website</a>@endif
							</div>
						</div>
						@if(trim(strip_tags($companyDescription)) !== '')
							<div class="jp-company-desc jp-prose fs-8">{!! $companyDescription !!}</div>
						@endif
						@if($companySlug)
							<a href="{{ route('companies.show', $companySlug) }}" class="btn btn-sm btn-outline btn-outline-primary w-100 mt-4">View company profile</a>
						@endif
					</div>
					@endif

					@if(!empty($job['similar_jobs']) && count($job['similar_jobs']) > 0)
					<div class="jp-sidebar-card">
						<h2 class="fs-7 fw-bold mb-3" style="color:var(--jp-ink);">Similar Jobs</h2>
						@foreach($job['similar_jobs'] as $similar)
							@php
								$simInitials = strtoupper(substr($similar['company']['name'] ?? 'JM', 0, 2));
								$simDutyStation = $similar['duty_station'] ?? '';
							@endphp
							<a href="{{ route('jobs.show', $similar['slug'] ?? $similar['id']) }}" class="jp-similar-item">
								<div class="jp-logo-sq jp-logo-sq-sm">
									@if(!empty($similar['company']['logo']))<img src="{{ $similar['company']['logo'] }}" alt="">@else{{ $simInitials }}@endif
								</div>
								<div class="jp-similar-body">
									<div class="title">{{ $similar['job_title'] ?? 'Job' }}</div>
									<div class="sub">{{ $similar['company']['name'] ?? 'Company' }}@if(!empty($simDutyStation)) · {{ $simDutyStation }}@endif</div>
									<div class="sub fw-bold" style="color:var(--jp-teal);">{{ $similar['formatted_salary'] ?? 'Negotiable' }}</div>
								</div>
							</a>
						@endforeach
					</div>
					@endif

				</div>
			</div>
		</div>
	</div>

	<!-- CTA Banner -->
	<div class="jp-cta-wrap">
		<div class="container">
			<div class="jp-cta-banner">
				<div class="cta-text">
					<div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Ready to make your next move?</div>
					<div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Join thousands of {{ country_citizens() }} hiring and getting hired faster with AI-powered matching.</div>
				</div>
				<div class="cta-actions">
					<a href="{{ route('register') }}?as=seeker" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">Find a Job</a>
					<a href="{{ route('register') }}?as=employer" class="btn btn-lg btn-light fw-bold">Post a Job</a>
				</div>
			</div>
		</div>
	</div>
</div>

</div>{{-- /.jp-page --}}

@include('jobs.apply-modal')

{{-- Structured data for search engines --}}
<script type="application/ld+json">{!! json_encode($jobPosting, $jsonFlags) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbLd, $jsonFlags) !!}</script>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-copy-link]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var done = function () {
                btn.classList.add('jp-copied');
                setTimeout(function () { btn.classList.remove('jp-copied'); }, 2000);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(window.location.href).then(done);
            } else {
                var t = document.createElement('textarea');
                t.value = window.location.href;
                document.body.appendChild(t);
                t.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                document.body.removeChild(t);
            }
        });
    });
});
</script>
@endpush