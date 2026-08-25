@extends('layouts.app')

@section('title', $location['display_name'] . ' Jobs — ' . country_name())
@section('meta_description', 'Browse ' . $location['display_name'] . ' jobs in ' . country_name() . '. Find the latest jobs in ' . $location['display_name'] . ' and apply today.')

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

    .jp-job-grid .jp-job-col{ display:flex; }
    .jp-job-row{ background:linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%); border:1px solid var(--jp-line); border-radius:14px; padding:22px; transition:.2s; box-shadow:0 6px 18px rgba(11,28,46,0.06); width:100%; display:flex; flex-direction:column; }
    .jp-job-row:hover{ border-color:var(--jp-teal); box-shadow:0 14px 30px rgba(11,28,46,0.1); transform:translateY(-2px); }
    .jp-job-row .title{ font-weight:700; color:var(--jp-ink); }
    .jp-job-row .title:hover{ color:var(--jp-teal); }
    .jp-job-row .meta i{ color:#94A3B8; margin-right:5px; }
    .jp-salary{ color:var(--jp-teal); font-weight:800; }

    .jp-logo-sq{ width:48px; height:48px; border-radius:12px; background:var(--jp-bg-soft); color:var(--jp-navy); border:1px solid var(--jp-line); display:flex; align-items:center; justify-content:center; font-weight:800; overflow:hidden; flex-shrink:0; }
    .jp-logo-sq img{ width:100%; height:100%; object-fit:cover; }
    .jp-pill{ background:var(--jp-bg-soft); color:#3B5166; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; border:1px solid var(--jp-line); }
    .jp-pill-urgent{ background:#FEECEC; color:#C0392B; border-color:rgba(192,57,43,0.15); }

    .jp-empty-card{ background:#fff; border:1px dashed var(--jp-line); border-radius:16px; }
    .jp-sort-select{ border-radius:10px; }
    .jp-breadcrumb{ font-size:.82rem; color:var(--jp-muted); }
    .jp-breadcrumb a{ color:var(--jp-muted); text-decoration:none; }
    .jp-breadcrumb a:hover{ color:var(--jp-teal); }
    .jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
    .jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }

    .jp-filter-card{ background:#fff; border:1px solid var(--jp-line); border-top:3px solid var(--jp-teal); border-radius:16px; padding:22px; box-shadow:0 10px 26px rgba(11,28,46,0.06); }
    .jp-filter-card h6{ font-weight:800; color:var(--jp-ink); font-size:.8rem; letter-spacing:.05em; text-transform:uppercase; margin-bottom:14px; }
    .jp-filter-card .form-check{ margin-bottom:9px; }
    .jp-filter-card .form-check-label{ font-size:.9rem; color:#33475B; font-weight:500; }
    .jp-filter-divider{ border-top:1px solid var(--jp-line); margin:18px 0; }

    .jp-chip{ display:inline-flex; align-items:center; gap:6px; background:#fff; color:#33475B; font-weight:700; font-size:.78rem; padding:6px 12px; border-radius:20px; border:1px solid var(--jp-line); text-decoration:none; }
    .jp-chip:hover{ background:var(--jp-bg-soft); color:var(--jp-teal); border-color:var(--jp-teal); }
    
    .jp-filter-toggle{ width:34px; height:34px; border-radius:9px; border:1px solid var(--jp-line); background:var(--jp-bg-soft); color:var(--jp-teal); display:flex; align-items:center; justify-content:center; font-size:1.1rem; font-weight:800; line-height:1; }
    .jp-filter-toggle .icon-minus{ display:none; }
    .jp-filter-toggle[aria-expanded="true"] .icon-plus{ display:none; }
    .jp-filter-toggle[aria-expanded="true"] .icon-minus{ display:inline; }
</style>
@endpush

@section('content')

@php
    $jobList = is_array($jobs) ? ($jobs['data'] ?? []) : [];
    $totalJobs = $jobs['pagination']['total'] ?? 0;
    $locationName = $location['display_name'] ?? $location['district'] ?? 'Location';
    $locationSlug = $location['slug'] ?? strtolower(str_replace(' ', '-', $location['district'] ?? ''));

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

<div class="jp-page-bg">
    <div class="container py-10 py-lg-12">

        <!-- Breadcrumb -->
        <div class="jp-breadcrumb mb-5">
            <a href="{{ route('jobs.index') }}">Jobs</a>
            <span class="mx-1">/</span>
            <a href="{{ route('jobs.index') }}?location={{ urlencode($location['district'] ?? '') }}">Locations</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700">{{ $locationName }}</span>
        </div>

        <!-- Location Header -->
        <div class="mb-6">
            <h1 class="display-6 fw-bold text-gray-900">Jobs in {{ $locationName }}</h1>
            <p class="text-muted fs-5">{{ number_format($totalJobs) }} open position{{ $totalJobs > 1 ? 's' : '' }} in {{ $locationName }}, {{ country_name() }}</p>
        </div>

        <!-- Search and Filters -->
        <div class="row g-6 g-lg-10">
            <!-- Filter Sidebar -->
            <div class="col-lg-3">
                <div class="jp-filter-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Filters</h6>
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('locations.show', $locationSlug) }}" class="fs-8 fw-semibold text-muted">Reset all</a>
                            <button type="button" class="jp-filter-toggle d-lg-none" data-bs-toggle="collapse" data-bs-target="#jpFilterBody" aria-expanded="false" aria-controls="jpFilterBody">
                                <span class="icon-plus">+</span>
                                <span class="icon-minus">&minus;</span>
                            </button>
                        </div>
                    </div>

                    <div class="collapse d-lg-block" id="jpFilterBody">
                        <form action="{{ route('locations.show', $locationSlug) }}" method="GET" id="filterForm">
                            <!-- Search -->
                            <div class="mb-3">
                                <label class="fw-semibold fs-8 mb-2">Search</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search jobs..." value="{{ request('search') }}">
                            </div>

                            <div class="jp-filter-divider"></div>
                            <h6>Category</h6>
                            @if(!empty($categories) && count($categories) > 0)
                                @foreach($categories as $category)
                                <div class="form-check form-check-custom">
                                    <input class="form-check-input" type="checkbox" name="category_id[]" value="{{ is_array($category) ? $category['id'] : $category->id }}" id="cat-{{ $loop->index }}" {{ in_array(is_array($category) ? $category['id'] : $category->id, (array) request('category_id', [])) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="cat-{{ $loop->index }}">{{ is_array($category) ? $category['name'] : $category->name }}</label>
                                </div>
                                @endforeach
                            @else
                                <p class="text-muted fs-8">No categories available.</p>
                            @endif

                            <div class="jp-filter-divider"></div>
                            <h6>Job Type</h6>
                            @if(!empty($jobTypes) && count($jobTypes) > 0)
                                @foreach($jobTypes as $type)
                                <div class="form-check form-check-custom">
                                    <input class="form-check-input" type="checkbox" name="job_type_id[]" value="{{ is_array($type) ? $type['id'] : $type->id }}" id="jt-{{ $loop->index }}" {{ in_array(is_array($type) ? $type['id'] : $type->id, (array) request('job_type_id', [])) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="jt-{{ $loop->index }}">{{ is_array($type) ? $type['name'] : $type->name }}</label>
                                </div>
                                @endforeach
                            @else
                                <p class="text-muted fs-8">No job types available.</p>
                            @endif

                            <div class="jp-filter-divider"></div>
                            <h6>Salary Range</h6>
                            <div class="d-flex gap-2 mb-2">
                                <input type="number" class="form-control form-control-sm" placeholder="Min" name="min_salary" value="{{ request('min_salary') }}" />
                                <input type="number" class="form-control form-control-sm" placeholder="Max" name="max_salary" value="{{ request('max_salary') }}" />
                            </div>

                            <div class="jp-filter-divider"></div>
                            <div class="d-flex gap-2">
                                <select name="sort" class="form-select form-select-sm">
                                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest</option>
                                    <option value="salary_high" {{ request('sort') === 'salary_high' ? 'selected' : '' }}>Highest Salary</option>
                                    <option value="salary_low" {{ request('sort') === 'salary_low' ? 'selected' : '' }}>Lowest Salary</option>
                                </select>
                                <button type="submit" class="btn jp-btn-primary btn-sm">Apply</button>
                            </div>

                            <!-- Preserve location -->
                            <input type="hidden" name="location_id" value="{{ $location['id'] ?? '' }}">
                        </form>
                    </div>
                </div>
            </div>

            <!-- Job Listings -->
            <div class="col-lg-9">

                <div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
                    <div class="fw-semibold text-gray-700">Showing <span class="fw-bold text-gray-900">{{ count($jobList) }}</span> of {{ number_format($totalJobs) }} jobs</div>
                </div>

                <div class="row g-4 jp-job-grid">
                    @forelse($jobList as $job)
                        @php
                            $jobCompanyName = $job['company']['name'] ?? 'Company Name';
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
                                                <a href="{{ route('jobs.show', $jobSlug) }}" class="title fs-5">{{ $jobTitle }}</a>
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
                    @empty
                        <div class="col-12">
                            <div class="jp-empty-card text-center py-10">
                                <i class="ki-duotone ki-file-deleted fs-3x text-muted d-block mb-3"><span class="path1"></span><span class="path2"></span></i>
                                <p class="fw-semibold fs-5 mb-1">No jobs found in this location</p>
                                <p class="text-muted">Try adjusting your filters or browse other locations.</p>
                                <a href="{{ route('jobs.index') }}" class="btn jp-btn-primary mt-3">Browse All Jobs</a>
                            </div>
                        </div>
                    @endforelse
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
                <div class="d-flex justify-content-center mt-10">
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
                            <a href="{{ route('jobs.index') }}" class="btn btn-lg btn-light fw-bold">Browse All Jobs</a>
                            <a href="{{ route('companies.index') }}" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">View Companies</a>
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

document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        const inputs = filterForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }
});
</script>
@endpush