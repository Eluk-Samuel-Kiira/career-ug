@extends('layouts.app')

@section('title', $company['name'] . ' — Company Profile in ' . country_name())
@section('meta_description', $company['meta_description'] ?? 'View jobs and careers at ' . $company['name'] . ' in ' . country_name() . '. Learn about the company culture, benefits, and apply for open positions.')

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

	/* Header band (same gradient as the job index search band) */
	.jp-search-band{ background: linear-gradient(120deg, #EAF7F5 0%, #E7F1FB 100%); border-bottom:1px solid var(--jp-line); }
	.jp-breadcrumb{ font-size:.82rem; color:var(--jp-muted); margin-bottom:1.25rem; overflow-wrap:anywhere; }
	.jp-breadcrumb a{ color:var(--jp-muted); text-decoration:none; }
	.jp-breadcrumb a:hover{ color:var(--jp-teal); }

	.jp-company-head{ display:flex; align-items:center; gap:18px 22px; flex-wrap:wrap; min-width:0; }
	.jp-company-head .jp-logo-sq{ width:88px; height:88px; border-radius:18px; font-size:1.8rem; padding:8px; box-shadow:0 8px 22px rgba(11,28,46,0.08); }
	.jp-company-head .info{ flex:1 1 280px; min-width:0; }
	.jp-company-head h1{ color:var(--jp-ink); font-weight:800; font-size:clamp(1.4rem, 5vw, 1.8rem); margin:0; overflow-wrap:anywhere; }
	.jp-company-meta{ display:flex; flex-wrap:wrap; gap:6px 18px; margin-top:.6rem; color:var(--jp-muted); font-size:.88rem; min-width:0; }
	.jp-company-meta > span{ display:inline-flex; align-items:center; min-width:0; overflow-wrap:anywhere; }
	.jp-company-meta i{ color:#94A3B8; margin-right:5px; flex:0 0 auto; }

	.jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
	.jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }
	.jp-btn-outline{ border:1.5px solid var(--jp-line); color:var(--jp-ink); font-weight:700; border-radius:10px; background:transparent; }
	.jp-btn-outline:hover{ border-color:var(--jp-teal); color:var(--jp-teal); background:rgba(3,165,136,0.05); }

	/* Search / filter bar (same look as the job index search box) */
	.jp-search-box{ background:#fff; border-radius:14px; padding:8px; box-shadow:0 12px 30px rgba(11,28,46,0.08); border:1px solid var(--jp-line); }
	.jp-search-box .form-control, .jp-search-box .form-select{ border:none; box-shadow:none; min-width:0; }
	.jp-search-box > div{ min-width:0; }
	.jp-search-box .divider{ width:1px; background:var(--jp-line); }

	/* Page backdrop */
	.jp-listing-bg{
		background:var(--jp-bg-page);
		background-image:
			radial-gradient(65% 55% at 100% 0%, rgba(3,165,136,0.14) 0%, transparent 60%),
			radial-gradient(55% 45% at 0% 15%, rgba(11,28,46,0.10) 0%, transparent 60%);
		border-top:3px solid var(--jp-teal);
	}

	/* Left sidebar card (same as the filter card) */
	.jp-filter-card{ background:#fff; border:1px solid var(--jp-line); border-top:3px solid var(--jp-teal); border-radius:16px; padding:22px; box-shadow:0 10px 26px rgba(11,28,46,0.06); }
	.jp-filter-card h6{ font-weight:800; color:var(--jp-ink); font-size:.8rem; letter-spacing:.05em; text-transform:uppercase; margin-bottom:14px; }
	.jp-filter-divider{ border-top:1px solid var(--jp-line); margin:18px 0; }
	.jp-sticky-col{ align-self:flex-start; }
	@media (min-width: 992px){ .jp-sticky-col{ position:sticky; top:24px; } }

	.jp-fact-row{ display:flex; align-items:flex-start; gap:10px; padding:9px 0; border-bottom:1px solid var(--jp-line); font-size:.87rem; min-width:0; }
	.jp-fact-row:first-child{ padding-top:0; }
	.jp-fact-row:last-child{ border-bottom:none; }
	.jp-fact-row i{ color:var(--jp-teal); margin-top:2px; flex:0 0 auto; }
	.jp-fact-row > div{ min-width:0; }
	.jp-fact-label{ color:var(--jp-muted); font-weight:600; display:block; font-size:.72rem; text-transform:uppercase; letter-spacing:.03em; }
	.jp-fact-value{ color:var(--jp-ink); font-weight:700; overflow-wrap:anywhere; }

	.jp-filter-toggle{ width:34px; height:34px; border-radius:9px; border:1px solid var(--jp-line); background:var(--jp-bg-soft); color:var(--jp-teal); display:flex; align-items:center; justify-content:center; font-size:1.1rem; font-weight:800; line-height:1; }
	.jp-filter-toggle .icon-minus{ display:none; }
	.jp-filter-toggle[aria-expanded="true"] .icon-plus{ display:none; }
	.jp-filter-toggle[aria-expanded="true"] .icon-minus{ display:inline; }

	.jp-share-btn{ width:38px; height:38px; border-radius:9px; border:1px solid var(--jp-line); background:#fff; display:inline-flex; align-items:center; justify-content:center; color:var(--jp-muted); transition:all .2s ease; text-decoration:none; flex:1; min-width:36px; padding:0; }
	.jp-share-btn:hover{ color:var(--jp-teal); border-color:var(--jp-teal); background:rgba(3,165,136,0.05); transform:translateY(-2px); box-shadow:0 4px 12px rgba(3,165,136,0.1); }
	.jp-share-btn svg{ width:18px; height:18px; flex-shrink:0; }

	/* About card */
	.jp-content-card{ background:#fff; border:1px solid var(--jp-line); border-radius:16px; padding:24px; box-shadow:0 6px 18px rgba(11,28,46,0.05); }
	.jp-content-card h2{ font-size:1.05rem; font-weight:800; color:var(--jp-ink); margin-bottom:1rem; display:flex; align-items:center; gap:8px; }
	.jp-content-card h2 i{ color:var(--jp-teal); }
	.jp-prose{ color:#33475B; font-size:.96rem; line-height:1.75; overflow-wrap:anywhere; }

	.jp-kicker{ color:var(--jp-teal); font-weight:800; letter-spacing:.06em; text-transform:uppercase; font-size:11.5px; }

	/* Shared logo tile: logos are always shown whole (contain), never cropped */
	.jp-logo-sq{ width:56px; height:56px; border-radius:12px; background:#fff; color:var(--jp-navy); border:1px solid var(--jp-line); display:flex; align-items:center; justify-content:center; font-weight:800; overflow:hidden; flex:0 0 auto; padding:5px; }
	.jp-logo-sq img{ width:100%; height:100%; object-fit:contain; object-position:center; display:block; }

	/* ===== Jobs: two per line ===== */
	.jp-job-grid .jp-job-col{ display:flex; min-width:0; }
	.jp-job-row{ background:linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%); border:1px solid var(--jp-line); border-radius:14px; padding:20px; transition:.2s; box-shadow:0 6px 18px rgba(11,28,46,0.06); width:100%; min-width:0; display:flex; flex-direction:column; overflow:hidden; }
	.jp-job-row:hover{ border-color:var(--jp-teal); box-shadow:0 14px 30px rgba(11,28,46,0.1); transform:translateY(-2px); }
	.jp-job-row .card-head{ display:flex; align-items:flex-start; gap:14px; min-width:0; }
	.jp-job-row .card-title-wrap{ flex:1 1 auto; min-width:0; }
	.jp-job-row .title{ font-weight:700; color:var(--jp-ink); line-height:1.3; overflow-wrap:anywhere; word-break:break-word; }
	.jp-job-row .title:hover{ color:var(--jp-teal); }
	.jp-job-row .company{ overflow-wrap:anywhere; }
	.jp-job-row .jp-salary{ display:block; margin-top:6px; overflow-wrap:anywhere; }
	.jp-job-row .meta{ display:flex; flex-wrap:wrap; gap:6px 16px; min-width:0; }
	.jp-job-row .meta > span{ display:inline-flex; align-items:center; min-width:0; max-width:100%; overflow-wrap:anywhere; }
	.jp-job-row .meta i{ color:#94A3B8; margin-right:5px; flex:0 0 auto; }
	.jp-job-row .card-foot{ margin-top:auto; padding-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
	.jp-salary{ color:var(--jp-teal); font-weight:800; }
	.jp-sort-select{ border-radius:10px; }

	.jp-pill{ background:var(--jp-bg-soft); color:#3B5166; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; border:1px solid var(--jp-line); display:inline-flex; align-items:center; gap:5px; }
	.jp-pill-urgent{ background:#FEECEC; color:#C0392B; border-color:rgba(192,57,43,0.15); }
	.jp-pill-verified{ background:#E9F9EF; color:#1E9E4C; border-color:rgba(30,158,76,0.15); }
	.jp-pill-featured{ background:#E7F1FB; color:#1D6FCC; border-color:rgba(29,111,204,0.15); }
	.jp-pill-gold{ background:#FFF6E0; color:#B8860B; border-color:rgba(184,134,11,0.15); }

	.jp-empty-card{ background:#fff; border:1px dashed var(--jp-line); border-radius:16px; }
	.jp-pagination{ flex-wrap:wrap; justify-content:center; row-gap:6px; }

	/* CTA banner */
	.jp-cta-wrap{ position:relative; z-index:2; margin-top:2.5rem; margin-bottom:-5rem; }
	.jp-cta-banner{ background:linear-gradient(90deg, #20AA3E 0%, #03A588 100%); border-radius:22px; padding:2rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap; box-shadow:0 20px 50px rgba(11,28,46,0.15); }
	.jp-cta-banner .cta-text{ flex:1 1 320px; min-width:0; }
	.jp-cta-banner .cta-actions{ display:flex; gap:12px; flex-wrap:wrap; flex:0 0 auto; }
	@media (min-width: 992px){ .jp-cta-banner{ padding:3rem; } }

	/* ===== Mobile ===== */
	@media (max-width: 575.98px){
		.jp-filter-card{ padding:18px; }
		.jp-company-head .jp-logo-sq{ width:72px; height:72px; }
		.jp-content-card{ padding:18px; }
		.jp-job-row{ padding:16px; }
		.jp-job-row .card-head{ gap:12px; }
		.jp-job-row .jp-logo-sq{ width:48px; height:48px; padding:4px; }
		.jp-job-row .card-foot .btn{ width:100%; }
		.jp-job-row .card-foot .pills{ width:100%; }
		.jp-cta-banner{ padding:1.5rem 1.25rem; border-radius:18px; }
		.jp-cta-banner .cta-actions{ width:100%; flex-direction:column; }
		.jp-cta-banner .cta-actions .btn{ width:100%; }
	}
</style>
@endpush

@section('content')

@php
	$companyName        = $company['name'] ?? 'Company Name';
	$companyLogo        = $company['logo_url'] ?? null;
	$companyDescription = $company['description'] ?? '';
	$companyWebsite     = $company['website'] ?? null;
	$companyIndustry    = $company['industry']['name'] ?? null;
	$companyLocation    = $company['location']['name'] ?? null;
	$companySize        = $company['company_size_label'] ?? null;
	$companyEmail       = $company['contact_email'] ?? null;
	$companyPhone       = $company['contact_phone'] ?? null;
	$companyAddress     = $company['address1'] ?? null;
	$isVerified         = $company['is_verified'] ?? false;
	$isFeatured         = $company['is_featured'] ?? false;
	$isGold             = $company['is_gold'] ?? false;
	$jobsCount          = $company['jobs_count'] ?? 0;

	$jobList   = is_array($jobs) ? ($jobs['data'] ?? []) : [];
	$totalJobs = $jobs['pagination']['total'] ?? 0;

	$initials = $companyName !== '' ? strtoupper(substr($companyName, 0, 2)) : 'CO';

	$jobInitials = function ($job) {
		$name = $job['company']['name'] ?? $job['job_title'] ?? 'JM';
		$name = trim($name);
		return $name !== '' ? strtoupper(substr($name, 0, 2)) : 'JM';
	};

	$postedAgo = function ($job) {
		if (empty($job['published_at'])) return '';
		try {
			return \Carbon\Carbon::parse($job['published_at'])->diffForHumans();
		} catch (\Throwable $e) {
			return '';
		}
	};
@endphp

<div class="jp-page">

<!-- ====================== COMPANY HEADER BAND ====================== -->
<div class="jp-search-band py-8 py-lg-10">
	<div class="container">
		<div class="jp-breadcrumb">
			<a href="{{ route('jobs.index') }}">Jobs</a>
			<span class="mx-1">/</span>
			<a href="{{ route('companies.index') }}">Companies</a>
			<span class="mx-1">/</span>
			<span class="text-gray-700">{{ $companyName }}</span>
		</div>

		<div class="jp-company-head">
			<div class="jp-logo-sq">
				@if($companyLogo)
					<img src="{{ $companyLogo }}" alt="{{ $companyName }}">
				@else
					{{ $initials }}
				@endif
			</div>
			<div class="info">
				<div class="d-flex flex-wrap align-items-center gap-3">
					<h1>{{ $companyName }}</h1>
					<div class="d-flex flex-wrap gap-1">
						@if($isVerified)
							<span class="jp-pill jp-pill-verified"><i class="ki-duotone ki-verify fs-7"><span class="path1"></span><span class="path2"></span></i>Verified</span>
						@endif
						@if($isFeatured)
							<span class="jp-pill jp-pill-featured"><i class="ki-duotone ki-star fs-7"><span class="path1"></span><span class="path2"></span></i>Featured</span>
						@endif
						@if($isGold)
							<span class="jp-pill jp-pill-gold"><i class="ki-duotone ki-crown fs-7"><span class="path1"></span><span class="path2"></span></i>Gold</span>
						@endif
					</div>
				</div>

				<div class="jp-company-meta">
					@if($companyIndustry)
						<span><i class="ki-duotone ki-building-factory fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $companyIndustry }}</span>
					@endif
					@if($companyLocation)
						<span><i class="ki-duotone ki-geolocation fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $companyLocation }}</span>
					@endif
					@if($companySize)
						<span><i class="ki-duotone ki-users fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $companySize }}</span>
					@endif
					@if($jobsCount > 0)
						<span><i class="ki-duotone ki-briefcase fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $jobsCount }} open position{{ $jobsCount > 1 ? 's' : '' }}</span>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

<!-- ====================== LISTINGS ====================== -->
<div class="jp-listing-bg">
	<div class="container py-10 py-lg-12">
		<div class="row gy-6 gx-lg-10">

			<!-- ====================== LEFT: COMPANY INFORMATION ====================== -->
			<div class="col-lg-3">
				<div class="jp-sticky-col">
					<div class="jp-filter-card">
						<div class="d-flex align-items-center justify-content-between mb-2">
							<h6 class="mb-0">Company Information</h6>
							<button type="button" class="jp-filter-toggle d-lg-none" data-bs-toggle="collapse" data-bs-target="#jpCompanyBody" aria-expanded="false" aria-controls="jpCompanyBody">
								<span class="icon-plus">+</span>
								<span class="icon-minus">&minus;</span>
							</button>
						</div>

						<div class="collapse d-lg-block" id="jpCompanyBody">
							<div class="jp-filter-divider"></div>

							@if($companyAddress)
							<div class="jp-fact-row">
								<i class="ki-duotone ki-geolocation fs-4"><span class="path1"></span><span class="path2"></span></i>
								<div><span class="jp-fact-label">Address</span><span class="jp-fact-value">{{ $companyAddress }}</span></div>
							</div>
							@endif

							@if($companyEmail)
							<div class="jp-fact-row">
								<i class="ki-duotone ki-sms fs-4"><span class="path1"></span><span class="path2"></span></i>
								<div><span class="jp-fact-label">Email</span><span class="jp-fact-value"><a href="mailto:{{ $companyEmail }}" class="text-decoration-none text-body">{{ $companyEmail }}</a></span></div>
							</div>
							@endif

							@if($companyPhone)
							<div class="jp-fact-row">
								<i class="ki-duotone ki-call fs-4"><span class="path1"></span><span class="path2"></span></i>
								<div><span class="jp-fact-label">Phone</span><span class="jp-fact-value"><a href="tel:{{ $companyPhone }}" class="text-decoration-none text-body">{{ $companyPhone }}</a></span></div>
							</div>
							@endif

							@if($companyIndustry)
							<div class="jp-fact-row">
								<i class="ki-duotone ki-building-factory fs-4"><span class="path1"></span><span class="path2"></span></i>
								<div><span class="jp-fact-label">Industry</span><span class="jp-fact-value">{{ $companyIndustry }}</span></div>
							</div>
							@endif

							@if($companyLocation)
							<div class="jp-fact-row">
								<i class="ki-duotone ki-geolocation fs-4"><span class="path1"></span><span class="path2"></span></i>
								<div><span class="jp-fact-label">Location</span><span class="jp-fact-value">{{ $companyLocation }}</span></div>
							</div>
							@endif

							@if($companySize)
							<div class="jp-fact-row">
								<i class="ki-duotone ki-users fs-4"><span class="path1"></span><span class="path2"></span></i>
								<div><span class="jp-fact-label">Company Size</span><span class="jp-fact-value">{{ $companySize }}</span></div>
							</div>
							@endif

							@if($jobsCount > 0)
							<div class="jp-fact-row">
								<i class="ki-duotone ki-briefcase fs-4"><span class="path1"></span><span class="path2"></span></i>
								<div><span class="jp-fact-label">Open Positions</span><span class="jp-fact-value">{{ $jobsCount }}</span></div>
							</div>
							@endif

							@if($companyWebsite)
							<div class="jp-filter-divider"></div>
							<h6>Quick Actions</h6>
							<div class="d-grid gap-2">
								<a href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer" class="btn jp-btn-outline btn-sm">
									<i class="ki-duotone ki-link fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
									Visit Website
								</a>
							</div>
							@endif

							<div class="jp-filter-divider"></div>
							<h6>Share</h6>
							<div class="d-flex gap-2">
								<a class="jp-share-btn" href="mailto:?subject={{ urlencode($companyName . ' - Company Profile') }}&body={{ urlencode(request()->fullUrl()) }}" title="Share by email">
									<i class="ki-duotone ki-sms fs-4"><span class="path1"></span><span class="path2"></span></i>
								</a>
								<a class="jp-share-btn" href="https://wa.me/?text={{ urlencode($companyName . ' - ' . request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer" title="Share on WhatsApp">
									<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 6.32A8.86 8.86 0 0 0 12.02 3.5c-4.87 0-8.83 3.94-8.83 8.79 0 1.55.41 3.06 1.19 4.39L3.2 21l4.44-1.16a8.9 8.9 0 0 0 4.37 1.12h.01c4.87 0 8.83-3.94 8.83-8.79a8.7 8.7 0 0 0-2.25-5.85Zm-5.58 13.5h-.01c-1.36 0-2.7-.36-3.86-1.05l-.28-.16-2.87.75.77-2.79-.18-.29a7.3 7.3 0 0 1-1.13-3.9c0-4.03 3.3-7.32 7.36-7.32a7.3 7.3 0 0 1 5.2 2.15 7.24 7.24 0 0 1 2.16 5.17c0 4.03-3.3 7.32-7.36 7.32Zm4.03-5.48c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.58.71-.71.86-.26.16-.48.05a6.03 6.03 0 0 1-1.77-1.09 6.6 6.6 0 0 1-1.22-1.52c-.13-.22 0-.34.1-.45.1-.1.22-.26.33-.39.11-.13.15-.22.22-.37.07-.15.04-.28-.02-.39-.06-.11-.5-1.2-.68-1.65-.18-.43-.36-.37-.5-.38h-.43c-.15 0-.39.06-.6.28-.2.22-.79.77-.79 1.87 0 1.1.81 2.17.92 2.32.11.15 1.6 2.44 3.87 3.42.54.23.96.37 1.29.48.54.17 1.03.15 1.42.09.43-.06 1.3-.53 1.49-1.04.18-.51.18-.94.13-1.03-.05-.09-.2-.15-.42-.26Z"/></svg>
								</a>
								<a class="jp-share-btn" href="https://twitter.com/intent/tweet?text={{ urlencode($companyName . ' is hiring! ') }}&url={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer" title="Share on X">
									<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-7.6 8.7L23 22h-7l-5.5-6.9L4.2 22H1l8.1-9.3L1 2h7.2l5 6.3L18.9 2Zm-1.2 18h1.7L7.4 4H5.6l12.1 16Z"/></svg>
								</a>
								<button type="button" class="jp-share-btn" title="Copy link" onclick="copyLink()">
									<i class="ki-duotone ki-copy fs-4"><span class="path1"></span><span class="path2"></span></i>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- ====================== RIGHT: ABOUT + JOBS ====================== -->
			<div class="col-lg-9">

				@if($companyDescription)
				<div class="jp-content-card mb-8">
					<h2><i class="ki-duotone ki-information-5 fs-3"><span class="path1"></span><span class="path2"></span></i>About {{ $companyName }}</h2>
					<div class="jp-prose">{!! nl2br(e($companyDescription)) !!}</div>
				</div>
				@endif

				<div class="jp-kicker mb-2">Open Positions</div>

				<!-- Search + filters -->
				<form action="{{ route('companies.show', $company['id']) }}" method="GET" class="jp-search-box d-flex flex-column flex-lg-row align-items-stretch mb-6">
					<input type="hidden" name="company_id" value="{{ $company['id'] }}">

					<div class="flex-grow-1 d-flex align-items-center px-4 py-3 py-lg-2">
						<i class="ki-duotone ki-magnifier fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
						<input type="text" name="search" class="form-control" placeholder="Search jobs..." value="{{ request('search') }}" />
					</div>
					<div class="divider d-none d-lg-block my-2"></div>
					<div class="d-flex align-items-center px-3 py-2">
						<select name="category_id" class="form-select">
							<option value="">All Categories</option>
							@if(!empty($categories) && count($categories) > 0)
								@foreach($categories as $category)
									@php $catId = is_array($category) ? $category['id'] : $category->id; @endphp
									<option value="{{ $catId }}" {{ request('category_id') == $catId ? 'selected' : '' }}>
										{{ is_array($category) ? $category['name'] : $category->name }}
									</option>
								@endforeach
							@endif
						</select>
					</div>
					<div class="divider d-none d-lg-block my-2"></div>
					<div class="d-flex align-items-center px-3 py-2">
						<select name="job_type_id" class="form-select">
							<option value="">All Types</option>
							@if(!empty($jobTypes) && count($jobTypes) > 0)
								@foreach($jobTypes as $type)
									@php $typeId = is_array($type) ? $type['id'] : $type->id; @endphp
									<option value="{{ $typeId }}" {{ request('job_type_id') == $typeId ? 'selected' : '' }}>
										{{ is_array($type) ? $type['name'] : $type->name }}
									</option>
								@endforeach
							@endif
						</select>
					</div>
					<div class="px-2 py-2 d-flex align-items-center">
						<button type="submit" class="btn jp-btn-primary w-100 px-6 py-3">Search</button>
					</div>
				</form>

				<!-- Listing header -->
				<div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
					<div class="fw-semibold text-gray-700">Showing <span class="fw-bold text-gray-900">{{ count($jobList) }}</span> of {{ number_format($totalJobs) }} jobs</div>
					<select class="form-select form-select-sm jp-sort-select w-auto" onchange="window.location.href = updateQueryParam('sort', this.value)">
						<option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
						<option value="salary_high" {{ request('sort') === 'salary_high' ? 'selected' : '' }}>Highest Salary</option>
						<option value="salary_low" {{ request('sort') === 'salary_low' ? 'selected' : '' }}>Lowest Salary</option>
					</select>
				</div>

				<!-- Job Grid -->
				<div class="row g-4 jp-job-grid">
					@forelse($jobList as $job)
						@php
							$jobCompanyName = $job['company']['name'] ?? $companyName;
							$jobTitle       = $job['job_title'] ?? 'Job Title';
							$jobSlug        = $job['slug'] ?? ($job['id'] ?? 1);
							$jobLocation    = $job['job_location']['name'] ?? ($job['duty_station'] ?: 'Location not specified');
							$jobTypeName    = $job['job_type']['name'] ?? ucfirst(str_replace('-', ' ', $job['employment_type'] ?? 'Full-time'));
							$jobSalary      = $job['formatted_salary'] ?? 'Negotiable';
							$jobCategory    = $job['job_category']['name'] ?? 'General';
							$jobLogo        = $job['company']['logo'] ?? $companyLogo;
						@endphp
						<div class="col-md-6 jp-job-col">
							<div class="jp-job-row">

								<!-- Header: logo, title, company, salary -->
								<div class="card-head">
									<div class="jp-logo-sq">
										@if(!empty($jobLogo))
											<img src="{{ $jobLogo }}" alt="{{ $jobCompanyName }}" loading="lazy">
										@else
											{{ $jobInitials($job) }}
										@endif
									</div>
									<div class="card-title-wrap">
										<a href="{{ route('jobs.show', $jobSlug) }}" class="title fs-5 d-block">{{ $jobTitle }}</a>
										<div class="company text-muted fs-7 mt-1">{{ $jobCompanyName }}</div>
										<span class="jp-salary fs-6">{{ $jobSalary }}</span>
									</div>
								</div>

								<!-- Meta: full card width -->
								<div class="meta mt-3 fs-8 text-muted">
									<span><i class="ki-duotone ki-geolocation fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $jobLocation }}</span>
									<span><i class="ki-duotone ki-briefcase fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $jobTypeName }}</span>
									@if($postedAgo($job))
										<span><i class="ki-duotone ki-calendar fs-6"><span class="path1"></span><span class="path2"></span></i>Posted {{ $postedAgo($job) }}</span>
									@endif
									@if(!empty($job['has_real_deadline']) && !empty($job['deadline']))
										<span><i class="ki-duotone ki-timer fs-6"><span class="path1"></span><span class="path2"></span></i>Apply by {{ \Carbon\Carbon::parse($job['deadline'])->format('d M Y') }}</span>
									@endif
								</div>

								<div class="card-foot">
									<div class="pills d-flex gap-2 flex-wrap">
										<span class="jp-pill">{{ $jobCategory }}</span>
										@if(!empty($job['is_urgent']))
											<span class="jp-pill jp-pill-urgent">Urgent</span>
										@endif
									</div>
									<a href="{{ route('jobs.show', $jobSlug) }}" class="btn btn-sm btn-outline btn-outline-primary">View &amp; Apply</a>
								</div>
							</div>
						</div>
					@empty
						<div class="col-12">
							<div class="jp-empty-card text-center py-10">
								<i class="ki-duotone ki-briefcase fs-3x text-muted d-block mb-3"><span class="path1"></span><span class="path2"></span></i>
								<p class="fw-semibold fs-5 mb-1">No open positions right now</p>
								<p class="text-muted">Check back later for new opportunities at {{ $companyName }}.</p>
							</div>
						</div>
					@endforelse
				</div>

				<!-- Pagination -->
				@if(!empty($jobs['pagination']) && ($jobs['pagination']['last_page'] ?? 1) > 1)
				@php
					$current = (int) ($jobs['pagination']['current_page'] ?? 1);
					$last    = (int) ($jobs['pagination']['last_page'] ?? 1);
					$window  = 2;
					$start   = max(1, $current - $window);
					$end     = min($last, $current + $window);
					$queryParams = http_build_query(request()->except(['page', '_token']));
				@endphp
				<div class="d-flex justify-content-center mt-10">
					<nav>
						<ul class="pagination jp-pagination">
							<li class="page-item {{ $current <= 1 ? 'disabled' : '' }}">
								<a class="page-link" href="{{ url()->current() }}?page={{ $current - 1 }}&{{ $queryParams }}">Prev</a>
							</li>

							@if($start > 1)
								<li class="page-item"><a class="page-link" href="{{ url()->current() }}?page=1&{{ $queryParams }}">1</a></li>
								@if($start > 2)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
							@endif

							@for($i = $start; $i <= $end; $i++)
								<li class="page-item {{ $i == $current ? 'active' : '' }}">
									<a class="page-link" href="{{ url()->current() }}?page={{ $i }}&{{ $queryParams }}">{{ $i }}</a>
								</li>
							@endfor

							@if($end < $last)
								@if($end < $last - 1)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
								<li class="page-item"><a class="page-link" href="{{ url()->current() }}?page={{ $last }}&{{ $queryParams }}">{{ $last }}</a></li>
							@endif

							<li class="page-item {{ $current >= $last ? 'disabled' : '' }}">
								<a class="page-link" href="{{ url()->current() }}?page={{ $current + 1 }}&{{ $queryParams }}">Next</a>
							</li>
						</ul>
					</nav>
				</div>
				@endif

			</div>
		</div>
	</div>

	<!-- CTA Banner -->
	<div class="jp-cta-wrap">
		<div class="container">
			<div class="jp-cta-banner">
				<div class="cta-text">
					<div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Looking for more opportunities?</div>
					<div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Browse thousands of jobs across {{ country_name() }} and find your next role.</div>
				</div>
				<div class="cta-actions">
					<a href="{{ route('jobs.index') }}" class="btn btn-lg btn-light fw-bold">Browse Jobs</a>
					<a href="{{ route('companies.index') }}" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">View All Companies</a>
				</div>
			</div>
		</div>
	</div>
</div>

</div>{{-- /.jp-page --}}

@endsection

@push('scripts')
<script>
function updateQueryParam(key, value) {
    const url = new URL(window.location.href);
    if (value) {
        url.searchParams.set(key, value);
    } else {
        url.searchParams.delete(key);
    }
    url.searchParams.delete('page');
    return url.toString();
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = 9999;
    toast.style.maxWidth = '400px';
    toast.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        showToast('Link copied to clipboard!', 'success');
    }).catch(() => {
        const input = document.createElement('input');
        input.value = window.location.href;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showToast('Link copied to clipboard!', 'success');
    });
}
</script>
@endpush