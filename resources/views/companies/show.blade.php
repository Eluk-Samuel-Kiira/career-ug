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
    .jp-btn-outline{ border:1.5px solid var(--jp-line); color:var(--jp-ink); font-weight:700; border-radius:10px; background:transparent; }
    .jp-btn-outline:hover{ border-color:var(--jp-teal); color:var(--jp-teal); background:rgba(3,165,136,0.05); }

    /* Company Header */
    .jp-company-header{
        background:#fff;
        border:1px solid var(--jp-line);
        border-radius:16px;
        padding:32px;
        box-shadow:0 10px 26px rgba(11,28,46,0.06);
    }
    .jp-company-header .company-logo{
        width:100px;
        height:100px;
        border-radius:18px;
        background:var(--jp-bg-soft);
        border:1px solid var(--jp-line);
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:800;
        font-size:2rem;
        overflow:hidden;
        flex-shrink:0;
        color:var(--jp-navy);
    }
    .jp-company-header .company-logo img{ width:100%; height:100%; object-fit:cover; }
    .jp-company-header .company-name{ font-weight:800; color:var(--jp-ink); font-size:1.8rem; }
    .jp-company-header .company-meta{ color:var(--jp-muted); font-size:.9rem; }
    .jp-company-header .company-meta i{ color:#94A3B8; margin-right:5px; }

    .jp-pill{ background:var(--jp-bg-soft); color:#3B5166; font-size:11px; font-weight:700; padding:5px 12px; border-radius:20px; border:1px solid var(--jp-line); display:inline-flex; align-items:center; gap:5px; }
    .jp-pill-gold{ background:#FFF6E0; color:#B8860B; border-color:rgba(184,134,11,0.15); }
    .jp-pill-verified{ background:#E9F9EF; color:#1E9E4C; border-color:rgba(30,158,76,0.15); }
    .jp-pill-featured{ background:#E7F1FB; color:#1D6FCC; border-color:rgba(29,111,204,0.15); }
    .jp-pill-urgent{ background:#FEECEC; color:#C0392B; border-color:rgba(192,57,43,0.15); }

    /* Content Card */
    .jp-content-card{
        background:#fff;
        border:1px solid var(--jp-line);
        border-radius:16px;
        padding:28px;
        box-shadow:0 6px 18px rgba(11,28,46,0.05);
    }
    .jp-content-card h2{
        font-size:1.05rem;
        font-weight:800;
        color:var(--jp-ink);
        margin-bottom:1rem;
        display:flex;
        align-items:center;
        gap:8px;
    }
    .jp-content-card h2 i{ color:var(--jp-teal); }

    .jp-prose{ color:#33475B; font-size:.96rem; line-height:1.75; }
    .jp-prose p{ margin-bottom:1em; }
    .jp-prose ul, .jp-prose ol{ padding-left:1.3em; margin-bottom:1em; }
    .jp-prose li{ margin-bottom:.4em; }

    .jp-fact-row{ display:flex; align-items:flex-start; gap:10px; padding:9px 0; border-bottom:1px solid var(--jp-line); font-size:.87rem; }
    .jp-fact-row:last-child{ border-bottom:none; }
    .jp-fact-row i{ color:var(--jp-teal); margin-top:2px; }
    .jp-fact-label{ color:var(--jp-muted); font-weight:600; display:block; font-size:.75rem; text-transform:uppercase; letter-spacing:.03em; }
    .jp-fact-value{ color:var(--jp-ink); font-weight:700; }

    .jp-sidebar-card{ background:#fff; border:1px solid var(--jp-line); border-radius:16px; padding:24px; box-shadow:0 6px 18px rgba(11,28,46,0.05); }
    .jp-sidebar-card + .jp-sidebar-card{ margin-top:20px; }

    /* Job cards - same as job index */
    .jp-job-grid .jp-job-col{ display:flex; }
    .jp-job-row{ background:linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%); border:1px solid var(--jp-line); border-radius:14px; padding:22px; transition:.2s; box-shadow:0 6px 18px rgba(11,28,46,0.06); width:100%; display:flex; flex-direction:column; }
    .jp-job-row:hover{ border-color:var(--jp-teal); box-shadow:0 14px 30px rgba(11,28,46,0.1); transform:translateY(-2px); }
    .jp-job-row .title{ font-weight:700; color:var(--jp-ink); }
    .jp-job-row .title:hover{ color:var(--jp-teal); }
    .jp-job-row .meta i{ color:#94A3B8; margin-right:5px; }
    .jp-salary{ color:var(--jp-teal); font-weight:800; }

    .jp-logo-sq{ width:48px; height:48px; border-radius:12px; background:var(--jp-bg-soft); color:var(--jp-navy); border:1px solid var(--jp-line); display:flex; align-items:center; justify-content:center; font-weight:800; overflow:hidden; flex-shrink:0; }
    .jp-logo-sq img{ width:100%; height:100%; object-fit:cover; }

    .jp-empty-card{ background:#fff; border:1px dashed var(--jp-line); border-radius:16px; }
    .jp-sort-select{ border-radius:10px; }

    .jp-sticky-col{ position:sticky; top:24px; align-self:flex-start; }

    .jp-share-btn {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        border: 1px solid var(--jp-line, #e4e6ef);
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--jp-muted, #64748B);
        transition: all 0.2s ease;
        text-decoration: none;
        flex: 1;
        min-width: 36px;
        padding: 0;
    }
    .jp-share-btn:hover {
        color: var(--jp-teal, #03A588);
        border-color: var(--jp-teal, #03A588);
        background: rgba(3, 165, 136, 0.05);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(3, 165, 136, 0.1);
    }
    .jp-share-btn svg {
        width: 18px;
        height: 18px;
        transition: color 0.2s ease;
        flex-shrink: 0;
    }
    .jp-share-btn:hover svg {
        color: var(--jp-teal, #03A588);
    }
    .jp-share-btn i {
        font-size: 1.1rem;
        line-height: 1;
    }

    @media (min-width: 992px){
        .jp-sticky-col{ position:sticky; top:24px; align-self:flex-start; }
    }
</style>
@endpush

@section('content')

@php
    $companyName = $company['name'] ?? 'Company Name';
    $companyLogo = $company['logo_url'] ?? null;
    $companyDescription = $company['description'] ?? '';
    $companyWebsite = $company['website'] ?? null;
    $companyIndustry = $company['industry']['name'] ?? null;
    $companyLocation = $company['location']['name'] ?? null;
    $companySize = $company['company_size_label'] ?? null;
    $companyEmail = $company['contact_email'] ?? null;
    $companyPhone = $company['contact_phone'] ?? null;
    $companyAddress = $company['address1'] ?? null;
    $isVerified = $company['is_verified'] ?? false;
    $isFeatured = $company['is_featured'] ?? false;
    $isGold = $company['is_gold'] ?? false;
    $jobsCount = $company['jobs_count'] ?? 0;
    
    // Jobs data from paginated response
    $jobList = is_array($jobs) ? ($jobs['data'] ?? []) : [];
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

<!-- ====================== BREADCRUMB ====================== -->
<div class="border-bottom" style="border-color:var(--jp-line) !important; background:#fff;">
    <div class="container py-3">
        <div class="jp-breadcrumb">
            <a href="{{ route('jobs.index') }}">Jobs</a>
            <span class="mx-1">/</span>
            <a href="{{ route('companies.index') }}">Companies</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700">{{ $companyName }}</span>
        </div>
    </div>
</div>

<!-- ====================== COMPANY PAGE ====================== -->
<div class="jp-page-bg">
    <div class="container py-10 py-lg-12">

        <!-- ====================== COMPANY HEADER ====================== -->
        <div class="jp-company-header mb-6">
            <div class="d-flex flex-wrap gap-4 align-items-start">
                <div class="company-logo">
                    @if($companyLogo)
                        <img src="{{ $companyLogo }}" alt="{{ $companyName }}">
                    @else
                        {{ $initials }}
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <h1 class="company-name mb-0">{{ $companyName }}</h1>
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

                    <div class="company-meta mt-2 d-flex flex-wrap gap-4">
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

                    @if($companyWebsite)
                        <div class="mt-3">
                            <a href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer" class="btn jp-btn-outline btn-sm">
                                <i class="ki-duotone ki-link fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Visit Website
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-6">

            <!-- ====================== MAIN CONTENT ====================== -->
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-5">

                    <!-- About Section -->
                    @if($companyDescription)
                    <div class="jp-content-card">
                        <h2><i class="ki-duotone ki-information-5 fs-3"><span class="path1"></span><span class="path2"></span></i>About {{ $companyName }}</h2>
                        <div class="jp-prose">{!! nl2br($companyDescription) !!}</div>
                    </div>
                    @endif

                    <!-- Open Positions with Search and Filters -->
                    <div class="jp-content-card">
                        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                            <h2 class="mb-0"><i class="ki-duotone ki-briefcase fs-3"><span class="path1"></span><span class="path2"></span></i>Open Positions</h2>
                            <div class="d-flex align-items-center gap-3">
                                @if($totalJobs > 0)
                                    <span class="jp-pill">{{ $totalJobs }} job{{ $totalJobs > 1 ? 's' : '' }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Search and Filter Bar -->
                        <form action="{{ route('companies.show', $company['id']) }}" method="GET" class="mb-4">
                            <div class="jp-inline-filter-bar">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="jp-search-input">
                                            <i class="ki-duotone ki-magnifier fs-5"><span class="path1"></span><span class="path2"></span></i>
                                            <input type="text" name="search" placeholder="Search jobs..." value="{{ request('search') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <select name="category_id" class="jp-select" data-control="select2">
                                            <option value="">All Categories</option>
                                            @if(!empty($categories) && count($categories) > 0)
                                                @foreach($categories as $category)
                                                    <option value="{{ is_array($category) ? $category['id'] : $category->id }}"
                                                        {{ request('category_id') == (is_array($category) ? $category['id'] : $category->id) ? 'selected' : '' }}>
                                                        {{ is_array($category) ? $category['name'] : $category->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <select name="job_type_id" class="jp-select" data-control="select2">
                                            <option value="">All Types</option>
                                            @if(!empty($jobTypes) && count($jobTypes) > 0)
                                                @foreach($jobTypes as $type)
                                                    <option value="{{ is_array($type) ? $type['id'] : $type->id }}"
                                                        {{ request('job_type_id') == (is_array($type) ? $type['id'] : $type->id) ? 'selected' : '' }}>
                                                        {{ is_array($type) ? $type['name'] : $type->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="d-flex gap-2">
                                            <select name="sort" class="jp-select flex-grow-1">
                                                <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                                                <option value="salary_high" {{ request('sort') === 'salary_high' ? 'selected' : '' }}>Highest Salary</option>
                                                <option value="salary_low" {{ request('sort') === 'salary_low' ? 'selected' : '' }}>Lowest Salary</option>
                                            </select>
                                            <button type="submit" class="jp-filter-submit" title="Apply filters">
                                                <i class="ki-duotone ki-filter fs-5"><span class="path1"></span><span class="path2"></span></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preserve company_id -->
                            <input type="hidden" name="company_id" value="{{ $company['id'] }}">
                        </form>

                        @if(!empty($jobList) && count($jobList) > 0)
                            <div class="row g-4 jp-job-grid">
                                @foreach($jobList as $job)
                                    @php
                                        $jobCompanyName = $job['company']['name'] ?? $companyName;
                                        $jobTitle = $job['job_title'] ?? 'Job Title';
                                        $jobSlug = $job['slug'] ?? ($job['id'] ?? 1);
                                        $jobLocation = $job['job_location']['name'] ?? ($job['duty_station'] ?: 'Location not specified');
                                        $jobTypeName = $job['job_type']['name'] ?? ucfirst(str_replace('-', ' ', $job['employment_type'] ?? 'Full-time'));
                                        $jobSalary = $job['formatted_salary'] ?? 'Negotiable';
                                        $jobCategory = $job['job_category']['name'] ?? 'General';
                                        $isUrgent = $job['is_urgent'] ?? false;
                                        $companyLogo = $job['company']['logo'] ?? null;
                                    @endphp
                                    <div class="col-md-6 jp-job-col">
                                        <div class="jp-job-row">
                                            <div class="d-flex gap-4">
                                                <div class="jp-logo-sq flex-shrink-0">
                                                    @if($companyLogo)
                                                        <img src="{{ $companyLogo }}" alt="{{ $jobCompanyName }}">
                                                    @else
                                                        {{ $jobInitials($job) }}
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                                                        <div>
                                                            <a href="{{ route('jobs.show', $jobSlug) }}" class="title fs-5 text-decoration-none">{{ $jobTitle }}</a>
                                                            <div class="text-muted fs-7 mt-1">{{ $jobCompanyName }}</div>
                                                        </div>
                                                        <span class="jp-salary fs-6">{{ $jobSalary }}</span>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-4 mt-3 meta fs-8 text-muted">
                                                        <span><i class="ki-duotone ki-geolocation fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $jobLocation }}</span>
                                                        <span><i class="ki-duotone ki-briefcase fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $jobTypeName }}</span>
                                                        @if($postedAgo($job))
                                                            <span><i class="ki-duotone ki-calendar fs-6"><span class="path1"></span><span class="path2"></span></i>Posted {{ $postedAgo($job) }}</span>
                                                        @endif
                                                        @if(!empty($job['has_real_deadline']) && !empty($job['deadline']))
                                                            <span><i class="ki-duotone ki-timer fs-6"><span class="path1"></span><span class="path2"></span></i>Apply by {{ \Carbon\Carbon::parse($job['deadline'])->format('d M Y') }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between mt-4 flex-wrap gap-2">
                                                        <div class="d-flex gap-2 flex-wrap">
                                                            <span class="jp-pill">{{ $jobCategory }}</span>
                                                            @if($isUrgent)
                                                                <span class="jp-pill jp-pill-urgent">Urgent</span>
                                                            @endif
                                                        </div>
                                                        <a href="{{ route('jobs.show', $jobSlug) }}" class="btn btn-sm btn-outline btn-outline-primary">View & Apply</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if(!empty($jobs['pagination']) && ($jobs['pagination']['last_page'] ?? 1) > 1)
                            @php
                                $current = (int) ($jobs['pagination']['current_page'] ?? 1);
                                $last = (int) ($jobs['pagination']['last_page'] ?? 1);
                                $window = 2;
                                $start = max(1, $current - $window);
                                $end = min($last, $current + $window);
                                $queryParams = http_build_query(request()->except(['page', '_token']));
                            @endphp
                            <div class="d-flex justify-content-center mt-8">
                                <nav>
                                    <ul class="pagination">
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

                        @else
                            <div class="jp-empty-card text-center py-10">
                                <i class="ki-duotone ki-briefcase fs-3x text-muted d-block mb-3"><span class="path1"></span><span class="path2"></span></i>
                                <p class="fw-semibold fs-5 mb-1">No open positions right now</p>
                                <p class="text-muted">Check back later for new opportunities at {{ $companyName }}.</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            <!-- ====================== SIDEBAR ====================== -->
            <div class="col-lg-4">
                <div class="jp-sticky-col">

                    <!-- Company Info Card -->
                    <div class="jp-sidebar-card">
                        <h6 class="fw-bold text-uppercase fs-8 text-muted mb-3" style="letter-spacing:.05em;">Company Information</h6>

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
                    </div>

                    <!-- Quick Actions -->
                    <div class="jp-sidebar-card">
                        <h6 class="fw-bold text-uppercase fs-8 text-muted mb-3" style="letter-spacing:.05em;">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('jobs.index') }}?company_id={{ $company['id'] ?? '' }}" class="btn jp-btn-primary">
                                <i class="ki-duotone ki-briefcase fs-4 me-2"><span class="path1"></span><span class="path2"></span></i>
                                View All Jobs
                            </a>
                            @if($companyWebsite)
                                <a href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer" class="btn jp-btn-outline">
                                    <i class="ki-duotone ki-link fs-4 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Visit Company Website
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Share -->
                    <div class="jp-sidebar-card">
                        <h6 class="fw-bold text-uppercase fs-8 text-muted mb-3" style="letter-spacing:.05em;">Share</h6>
                        <div class="d-flex gap-2">
                            <a class="jp-share-btn flex-grow-1 d-flex align-items-center justify-content-center" 
                            href="mailto:?subject={{ urlencode($companyName . ' - Company Profile') }}&body={{ urlencode(request()->fullUrl()) }}" 
                            title="Share by email">
                                <i class="ki-duotone ki-sms fs-4"><span class="path1"></span><span class="path2"></span></i>
                            </a>
                            <a class="jp-share-btn flex-grow-1 d-flex align-items-center justify-content-center" 
                            href="https://wa.me/?text={{ urlencode($companyName . ' - ' . request()->fullUrl()) }}" 
                            target="_blank" rel="noopener noreferrer" 
                            title="Share on WhatsApp">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.6 6.32A8.86 8.86 0 0 0 12.02 3.5c-4.87 0-8.83 3.94-8.83 8.79 0 1.55.41 3.06 1.19 4.39L3.2 21l4.44-1.16a8.9 8.9 0 0 0 4.37 1.12h.01c4.87 0 8.83-3.94 8.83-8.79a8.7 8.7 0 0 0-2.25-5.85Zm-5.58 13.5h-.01c-1.36 0-2.7-.36-3.86-1.05l-.28-.16-2.87.75.77-2.79-.18-.29a7.3 7.3 0 0 1-1.13-3.9c0-4.03 3.3-7.32 7.36-7.32a7.3 7.3 0 0 1 5.2 2.15 7.24 7.24 0 0 1 2.16 5.17c0 4.03-3.3 7.32-7.36 7.32Zm4.03-5.48c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.58.71-.71.86-.26.16-.48.05a6.03 6.03 0 0 1-1.77-1.09 6.6 6.6 0 0 1-1.22-1.52c-.13-.22 0-.34.1-.45.1-.1.22-.26.33-.39.11-.13.15-.22.22-.37.07-.15.04-.28-.02-.39-.06-.11-.5-1.2-.68-1.65-.18-.43-.36-.37-.5-.38h-.43c-.15 0-.39.06-.6.28-.2.22-.79.77-.79 1.87 0 1.1.81 2.17.92 2.32.11.15 1.6 2.44 3.87 3.42.54.23.96.37 1.29.48.54.17 1.03.15 1.42.09.43-.06 1.3-.53 1.49-1.04.18-.51.18-.94.13-1.03-.05-.09-.2-.15-.42-.26Z"/>
                                </svg>
                            </a>
                            <a class="jp-share-btn flex-grow-1 d-flex align-items-center justify-content-center" 
                            href="https://twitter.com/intent/tweet?text={{ urlencode($companyName . ' is hiring! ') }}&url={{ urlencode(request()->fullUrl()) }}" 
                            target="_blank" rel="noopener noreferrer" 
                            title="Share on X">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18.9 2H22l-7.6 8.7L23 22h-7l-5.5-6.9L4.2 22H1l8.1-9.3L1 2h7.2l5 6.3L18.9 2Zm-1.2 18h1.7L7.4 4H5.6l12.1 16Z"/>
                                </svg>
                            </a>
                            <button type="button" class="jp-share-btn flex-grow-1 d-flex align-items-center justify-content-center" 
                                    title="Copy link" 
                                    onclick="navigator.clipboard.writeText(window.location.href); this.querySelector('i').outerHTML='<i class=\'ki-duotone ki-check fs-4\'><span class=\'path1\'></span><span class=\'path2\'></span></i>'; showToast('Link copied!', 'success');">
                                <i class="ki-duotone ki-copy fs-4"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
    
    <!-- CTA Banner -->
    <div class="mt-10 mb-n20 position-relative z-index-2">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-12">  
                    <div class="d-flex flex-stack flex-wrap flex-md-nowrap card-rounded shadow p-8 p-lg-12" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
                        <div class="my-2 me-5">
                            <div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Looking for more opportunities?</div>
                            <div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Browse thousands of jobs across {{ country_name() }} and find your next role.</div>
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-3 flex-shrink-0 my-2">
                            <a href="{{ route('jobs.index') }}" class="btn btn-lg btn-light fw-bold">Browse Jobs</a>
                            <a href="{{ route('companies.index') }}" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">View All Companies</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


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