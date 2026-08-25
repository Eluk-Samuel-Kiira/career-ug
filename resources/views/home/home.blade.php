@extends('layouts.app')

@section('title', 'Hire Top Talent & Find Your Next Job — AI-Powered Recruitment - Jobs in ' . country_name())
@section('meta_description', country_name() . '\'s leading job portal. Job seekers get AI-matched roles, CV review and WhatsApp alerts. Employers post jobs, screen CVs with AI, and find verified blue-collar and casual talent in ' . country_name() . '.')
@section('keywords', 'jobs in ' . strtolower(country_name()) . ', recruitment ' . strtolower(country_name()) . ', AI hiring, CV review, job posting, talent matching, blue-collar jobs, casual jobs, ' . strtolower(country_name()) . ' careers')
@section('og_title', 'Hire Top Talent & Find Your Next Job — AI-Powered Recruitment - Jobs in ' . country_name())
@section('og_description', country_name() . '\'s leading job portal. Job seekers get AI-matched roles, CV review and WhatsApp alerts. Employers post jobs, screen CVs with AI, and find verified blue-collar and casual talent.')
@section('twitter_title', 'Hire Top Talent & Find Your Next Job — AI-Powered Recruitment - Jobs in ' . country_name())
@section('twitter_description', country_name() . '\'s leading job portal. Job seekers get AI-matched roles, CV review and WhatsApp alerts. Employers post jobs, screen CVs with AI, and find verified blue-collar and casual talent.')

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
		--jp-line: rgba(15,27,45,0.08);
	}

	/* Hero */
	.jp-hero{ background: radial-gradient(120% 140% at 15% 0%, #16304A 0%, var(--jp-navy) 55%, #081420 100%); position:relative; overflow:hidden; }
	.jp-hero::after{ content:""; position:absolute; inset:0; background-image:
		linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
		linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
		background-size: 42px 42px; mask-image: linear-gradient(to bottom, black, transparent 75%); pointer-events:none; }
	.jp-eyebrow{ display:inline-flex; align-items:center; gap:8px; padding:6px 16px; border-radius:50px; font-size:12.5px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#BFF7D2; background:rgba(32,170,62,0.14); border:1px solid rgba(32,170,62,0.35); }
	.jp-hero h1{ font-size:2.55rem; line-height:1.15; font-weight:800; color:#fff; letter-spacing:-0.01em; }
	.jp-hero h1 span{ background:var(--jp-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
	.jp-hero p.lead{ color:#B9C6D6; font-size:1.08rem; max-width:520px; }
	.jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; padding:13px 26px; border-radius:10px; box-shadow:0 10px 24px rgba(3,165,136,0.28); }
	.jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); transform:translateY(-1px); }
	.jp-btn-ghost{ background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.18); color:#fff; font-weight:700; padding:13px 26px; border-radius:10px; }
	.jp-btn-ghost:hover{ background:rgba(255,255,255,0.12); color:#fff; }

	/* Signature: WhatsApp alert mockup */
	.jp-phone{ background:#fff; border-radius:22px; box-shadow:0 30px 60px rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.08); overflow:hidden; max-width:340px; margin-left:auto; }
	.jp-phone-head{ background:var(--jp-gradient); padding:14px 16px; display:flex; align-items:center; gap:10px; color:#fff; }
	.jp-phone-head .dot{ width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,0.22); display:flex; align-items:center; justify-content:center; font-weight:800; }
	.jp-phone-body{ background:#E9EEF1; padding:16px; min-height:290px; }
	.jp-bubble{ background:#fff; border-radius:12px; padding:12px 14px; font-size:13.5px; color:#1c2b3a; box-shadow:0 1px 2px rgba(0,0,0,0.06); margin-bottom:10px; max-width:88%; }
	.jp-bubble.me{ background:#DCF8C6; margin-left:auto; }
	.jp-bubble .tag{ display:inline-block; font-size:10.5px; font-weight:700; color:var(--jp-teal); background:rgba(3,165,136,0.1); padding:2px 8px; border-radius:20px; margin-bottom:6px; }
	.jp-bubble .job-title{ font-weight:700; }
	.jp-bubble .meta{ color:var(--jp-muted); font-size:12px; }

	/* Stat strip */
	.jp-stat-strip-wrap{ margin-top: 24px; position: relative; z-index: 3; }
	@media (min-width: 992px){ .jp-stat-strip-wrap{ margin-top: -40px; } }
	.jp-stat-strip{ background:#fff; border-radius:18px; box-shadow:0 20px 50px rgba(11,28,46,0.10); border:1px solid var(--jp-line); }
	.jp-stat-strip .stat-num{ font-weight:800; font-size:1.7rem; color:var(--jp-ink); }
	.jp-stat-strip .stat-num span{ color:var(--jp-teal); }
	.jp-stat-strip .stat-label{ color:var(--jp-muted); font-size:.85rem; font-weight:600; }

	/* Section heading */
	.jp-kicker{ color:var(--jp-teal); font-weight:800; letter-spacing:.08em; text-transform:uppercase; font-size:12.5px; }
	.jp-h2{ font-size:2rem; font-weight:800; color:var(--jp-ink); letter-spacing:-0.01em; }
	.jp-sub{ color:var(--jp-muted); font-size:1.02rem; }

	/* Audience cards */
	.jp-audience{ border-radius:20px; padding:36px; height:100%; border:1px solid var(--jp-line); }
	.jp-audience.seekers{ background:linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%); }
	.jp-audience.employers{ background:linear-gradient(180deg, var(--jp-navy) 0%, var(--jp-navy-2) 100%); color:#fff; }
	.jp-audience .icon-badge{ width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px; margin-bottom:18px; }
	.jp-audience.seekers .icon-badge{ background:var(--jp-gradient); color:#fff; }
	.jp-audience.employers .icon-badge{ background:rgba(255,255,255,0.1); color:#5EE29B; border:1px solid rgba(255,255,255,0.14); }
	.jp-check-list{ list-style:none; padding:0; margin:22px 0 26px; }
	.jp-check-list li{ display:flex; align-items:flex-start; gap:10px; padding:7px 0; font-weight:600; font-size:.96rem; }
	.jp-audience.seekers .jp-check-list li{ color:#243244; }
	.jp-audience.employers .jp-check-list li{ color:#DCE6EF; }
	.jp-check-list li i{ color:var(--jp-teal); margin-top:3px; }
	.jp-audience.employers .jp-check-list li i{ color:#5EE29B; }

	/* Service tiles */
	.jp-service{ border:1px solid var(--jp-line); border-radius:16px; padding:26px 22px; height:100%; background:#fff; transition:.25s; }
	.jp-service:hover{ transform:translateY(-4px); box-shadow:0 16px 34px rgba(11,28,46,0.08); border-color:transparent; }
	.jp-service .num{ font-weight:800; color:var(--jp-line); font-size:1.6rem; }
	.jp-service .icon-sq{ width:44px; height:44px; border-radius:12px; background:var(--jp-bg-soft); color:var(--jp-teal); display:flex; align-items:center; justify-content:center; margin-bottom:14px; font-size:19px; }

	/* Featured jobs */
	.jp-job-card{ border:1px solid var(--jp-line); border-radius:16px; padding:22px; background:#fff; height:100%; transition:.25s; }
	.jp-job-card:hover{ transform:translateY(-3px); box-shadow:0 14px 30px rgba(11,28,46,0.08); }
	.jp-job-card.featured{ border-left:4px solid var(--jp-green); }
	.jp-logo-sq{ width:52px; height:52px; border-radius:12px; background:var(--jp-bg-soft); display:flex; align-items:center; justify-content:center; font-weight:800; color:var(--jp-teal); }
	.jp-pill{ background:var(--jp-bg-soft); color:#3B5166; font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:20px; }

	/* CTA band */
	.jp-cta{ background: var(--jp-gradient); border-radius:22px; overflow:hidden; }
</style>
@endpush

@section('content')

<!-- ====================== HERO ====================== -->
<div class="jp-hero pt-10 pt-lg-15 pb-20 pb-lg-25">
	<div class="container position-relative">
		<div class="row align-items-center g-8 g-lg-12">
			<div class="col-lg-6">
				<span class="jp-eyebrow mb-5"><i class="ki-duotone ki-abstract-14 fs-6"><span class="path1"></span><span class="path2"></span></i> AI-powered job search $ hiring, built for {{ country_name() }}</span>
				<h1 class="mt-5 mb-5">Every great hire starts with the right <span>match</span></h1>
				<p class="lead mb-8">One platform for job seekers and employers — AI matching, CV review and rewriting, cover letters, and job alerts delivered straight to WhatsApp.</p>
				<div class="d-flex flex-wrap gap-3">
					<a href="{{ route('jobs.index') }}" class="btn jp-btn-primary"><i class="ki-duotone ki-magnifier fs-4 me-1"></i> Find a Job</a>
					<a href="{{ route('register') }}?as=employer" class="btn jp-btn-ghost"><i class="ki-duotone ki-briefcase fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Post a Job</a>
				</div>
				<div class="d-flex align-items-center gap-6 mt-10 flex-wrap opacity-75">
					<img src="{{ asset('assets/media/svg/brand-logos/fujifilm.svg') }}" class="mh-20px" alt="" />
					<img src="{{ asset('assets/media/svg/brand-logos/vodafone.svg') }}" class="mh-20px" alt="" />
					<img src="{{ asset('assets/media/svg/brand-logos/kpmg.svg') }}" class="mh-20px" alt="" />
					<img src="{{ asset('assets/media/svg/brand-logos/aon.svg') }}" class="mh-20px" alt="" />
				</div>
			</div>

			<div class="col-lg-6 d-none d-lg-block">
				<div class="jp-phone">
					<div class="jp-phone-head">
						<div class="dot"><i class="ki-duotone ki-abstract-14 fs-3"><span class="path1"></span><span class="path2"></span></i></div>
						<div>
							<div class="fw-bold fs-6">{{ app_name() }} Alerts</div>
							<div class="fs-8 opacity-75">via WhatsApp</div>
						</div>
					</div>
					<div class="jp-phone-body">
						<div class="jp-bubble">
							<span class="tag">New Match · 96%</span>
							<div class="job-title">Warehouse Supervisor</div>
							<div class="meta">Coles Group · Western Sydney · $34/hr</div>
						</div>
						<div class="jp-bubble me">Yes please, send the application link 🙌</div>
						<div class="jp-bubble">
							<span class="tag">Easy Apply</span>
							<div class="job-title">Registered Nurse — Casual</div>
							<div class="meta">St Vincent's Health · Melbourne</div>
						</div>
						<div class="jp-bubble">Your CV was reviewed — 3 quick edits will boost your match score. Reply "review" to see them.</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- ====================== STAT STRIP ====================== -->
<div class="container jp-stat-strip-wrap">
	<div class="jp-stat-strip py-8 px-6 px-lg-10">
		<div class="row text-center g-4">
			<div class="col-6 col-lg-3">
				<div class="stat-num">50K<span>+</span></div>
				<div class="stat-label">Active Candidates</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="stat-num">15K<span>+</span></div>
				<div class="stat-label">Jobs Posted</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="stat-num">8K<span>+</span></div>
				<div class="stat-label">Successful Hires</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="stat-num">98<span>%</span></div>
				<div class="stat-label">Satisfaction Rate</div>
			</div>
		</div>
	</div>
</div>

<!-- ====================== DUAL AUDIENCE ====================== -->
<div class="container py-20">
	<div class="text-center mb-14">
		<div class="jp-kicker mb-2">Built for both sides of hiring</div>
		<h2 class="jp-h2 mb-3">Whichever side of the desk you're on</h2>
		<p class="jp-sub">A single platform, two purpose-built experiences.</p>
	</div>

	<div class="row g-6 g-lg-8">
		<div class="col-lg-6">
			<div class="jp-audience seekers">
				<div class="icon-badge"><i class="ki-duotone ki-profile-circle fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></div>
				<h3 class="fw-bold fs-2 mb-2">For Job Seekers</h3>
				<p class="text-muted mb-0">Search, apply, and get discovered — with AI doing the heavy lifting on your CV.</p>
				<ul class="jp-check-list">
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Search and apply to thousands of live roles</li>
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> A public CV profile employers can find</li>
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Job alerts delivered straight to WhatsApp</li>
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> AI CV review, rewriting and formatting</li>
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Cover letters and application writing on demand</li>
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> One-tap Easy Apply — no repeat forms</li>
				</ul>
				<a href="{{ route('register') }}?as=seeker" class="btn jp-btn-primary">Create Your Profile</a>
			</div>
		</div>

		<div class="col-lg-6">
			<div class="jp-audience employers">
				<div class="icon-badge"><i class="ki-duotone ki-briefcase fs-1"><span class="path1"></span><span class="path2"></span></i></div>
				<h3 class="fw-bold fs-2 mb-2" style="color: white;">For Employers</h3>
				<p class="mb-0" style="color:#B9C6D6;">Post, screen, and hire faster with AI-ranked candidates and a verified talent pool.</p>
				<ul class="jp-check-list">
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Post jobs in minutes with smart templates</li>
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> AI-powered CV scanning and ranking</li>
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Browse trending and top-rated CVs</li>
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Access verified blue-collar and casual workers</li>
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> One dashboard for every applicant and job</li>
					<li><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Message candidates directly on WhatsApp</li>
				</ul>
				<a href="{{ route('register') }}?as=employer" class="btn btn-light fw-bold">Post Your First Job</a>
			</div>
		</div>
	</div>
</div>

<!-- ====================== SERVICES ====================== -->
<div style="background:var(--jp-bg-soft);">
	<div class="container py-20">
		<div class="text-center mb-14">
			<div class="jp-kicker mb-2">Candidate Services</div>
			<h2 class="jp-h2 mb-3">Everything your application needs, done for you</h2>
			<p class="jp-sub">Professional writing services backed by AI, reviewed for quality.</p>
		</div>
		<div class="row g-5">
			<div class="col-6 col-lg-3">
				<div class="jp-service">
					<div class="icon-sq"><i class="ki-duotone ki-search-list fs-2x"><span class="path1"></span><span class="path2"></span></i></div>
					<h5 class="fw-bold mb-2">CV Review</h5>
					<p class="text-muted fs-7 mb-0">Get an honest, detailed assessment of your CV's strengths and gaps.</p>
				</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="jp-service">
					<div class="icon-sq"><i class="ki-duotone ki-pencil fs-2x"><span class="path1"></span><span class="path2"></span></i></div>
					<h5 class="fw-bold mb-2">CV Rewriting</h5>
					<p class="text-muted fs-7 mb-0">A professionally rewritten CV, formatted to pass employer screening.</p>
				</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="jp-service">
					<div class="icon-sq"><i class="ki-duotone ki-document fs-2x"><span class="path1"></span><span class="path2"></span></i></div>
					<h5 class="fw-bold mb-2">Cover Letters</h5>
					<p class="text-muted fs-7 mb-0">Tailored cover letters written for the specific role you're chasing.</p>
				</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="jp-service">
					<div class="icon-sq"><i class="ki-duotone ki-rocket fs-2x"><span class="path1"></span><span class="path2"></span></i></div>
					<h5 class="fw-bold mb-2">Easy Apply</h5>
					<p class="text-muted fs-7 mb-0">Apply to any listing in one tap using your saved profile and CV.</p>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- ====================== HOW IT WORKS ====================== -->
<div class="container py-20">
	<div class="text-center mb-14">
		<div class="jp-kicker mb-2">The Process</div>
		<h2 class="jp-h2 mb-3">How JobMatch works</h2>
		<p class="jp-sub">Three steps, whichever side you're on.</p>
	</div>
	<div class="row g-8">
		<div class="col-md-4 text-center">
			<div class="mx-auto mb-5" style="width:64px;height:64px;border-radius:16px;background:var(--jp-gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:22px;">1</div>
			<h5 class="fw-bold mb-2">Create your profile</h5>
			<p class="text-muted fs-7 px-lg-4">Job seekers upload a CV; employers set up a company profile in minutes.</p>
		</div>
		<div class="col-md-4 text-center">
			<div class="mx-auto mb-5" style="width:64px;height:64px;border-radius:16px;background:var(--jp-gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:22px;">2</div>
			<h5 class="fw-bold mb-2">Let AI do the matching</h5>
			<p class="text-muted fs-7 px-lg-4">Our engine scores fit on skills, experience and location for both sides.</p>
		</div>
		<div class="col-md-4 text-center">
			<div class="mx-auto mb-5" style="width:64px;height:64px;border-radius:16px;background:var(--jp-gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:22px;">3</div>
			<h5 class="fw-bold mb-2">Connect and hire</h5>
			<p class="text-muted fs-7 px-lg-4">Apply, message, and close the loop — with alerts pushed to WhatsApp.</p>
		</div>
	</div>
</div>

<!-- ====================== FEATURED JOBS ====================== -->
<div style="background:var(--jp-bg-soft);">
    <div class="container py-20">
        <div class="d-flex align-items-end justify-content-between mb-10 flex-wrap gap-3">
            <div>
                <div class="jp-kicker mb-2">Live Right Now</div>
                <h2 class="jp-h2 mb-0">Featured jobs</h2>
            </div>
            <a href="{{ route('jobs.index') }}" class="btn btn-outline btn-outline-primary">View All Jobs <i class="ki-duotone ki-arrow-right fs-4 ms-1"><span class="path1"></span><span class="path2"></span></i></a>
        </div>
        
        @php
            // Get featured jobs data
            $featuredJobsList = $featuredJobs['data'] ?? [];
            $hasFeaturedJobs = count($featuredJobsList) > 0;
        @endphp

        @if($hasFeaturedJobs)
        <div class="row g-5">
            @foreach($featuredJobsList as $job)
                @php
                    $jobCompanyName = $job['company']['name'] ?? 'Company';
                    $jobTitle = $job['job_title'] ?? 'Job Title';
                    $jobSlug = $job['slug'] ?? ($job['id'] ?? 1);
                    $jobLocation = $job['job_location']['name'] ?? $job['duty_station'] ?? 'Location';
                    $jobTypeName = $job['job_type']['name'] ?? ucfirst(str_replace('-', ' ', $job['employment_type'] ?? 'Full-time'));
                    $jobCategory = $job['job_category']['name'] ?? 'General';
                    $jobSalary = $job['formatted_salary'] ?? 'Competitive';
                    $companyLogo = $job['company']['logo'] ?? null;
                    $initials = $jobCompanyName !== '' ? strtoupper(substr($jobCompanyName, 0, 2)) : 'JM';
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="jp-job-card featured">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="jp-logo-sq">
                                @if($companyLogo)
                                    <img src="{{ $companyLogo }}" alt="{{ $jobCompanyName }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    {{ $initials }}
                                @endif
                            </div>
                            <span class="jp-pill jp-pill-featured">
                                <i class="ki-duotone ki-star fs-7 me-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Featured
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1">
                            <a href="{{ route('jobs.show', $jobSlug) }}" class="text-gray-900 text-decoration-none">{{ $jobTitle }}</a>
                        </h5>
                        <div class="text-muted fs-7 mb-3">{{ $jobCompanyName }} · {{ $jobLocation }}</div>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <span class="jp-pill">{{ $jobTypeName }}</span>
                            <span class="jp-pill">{{ $jobCategory }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold" style="color:var(--jp-teal);">{{ $jobSalary }}</span>
                            <a href="{{ route('jobs.show', $jobSlug) }}" class="btn btn-sm jp-btn-primary">View</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
    
    <!-- CTA Banner -->
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

<style>
    .jp-pill-featured {
        background: #E7F1FB;
        color: #1D6FCC;
        border-color: rgba(29, 111, 204, 0.15);
        font-size: 11px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        border: 1px solid var(--jp-line);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
</style> 


@endsection