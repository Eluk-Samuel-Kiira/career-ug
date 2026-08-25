@extends('layouts.app')

@section('title', 'Browse Jobs — Search '.country_name().'\'s Latest Openings')
@section('meta_description', 'Search thousands of live jobs across '.country_name().'. Filter by location, category, job type and salary. Apply in one tap with Easy Apply.')

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

	/* Search band — pale, light background instead of the dark navy gradient */
	.jp-search-band{ background: linear-gradient(120deg, #EAF7F5 0%, #E7F1FB 100%); border-bottom:1px solid var(--jp-line); }
	.jp-search-band h1{ color:var(--jp-ink); font-weight:800; font-size:1.7rem; margin-bottom:.35rem; }
	.jp-search-band p{ color:var(--jp-muted); margin-bottom:1.5rem; }
	.jp-search-box{ background:#fff; border-radius:14px; padding:8px; box-shadow:0 12px 30px rgba(11,28,46,0.08); border:1px solid var(--jp-line); }
	.jp-search-box .form-control, .jp-search-box .form-select{ border:none; box-shadow:none; }
	.jp-search-box .divider{ width:1px; background:var(--jp-line); }
	.jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
	.jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }

	/* Page backdrop — clearly tinted so white cards actually stand out */
	.jp-listing-bg{
		background:var(--jp-bg-page);
		background-image:
			radial-gradient(65% 55% at 100% 0%, rgba(3,165,136,0.14) 0%, transparent 60%),
			radial-gradient(55% 45% at 0% 15%, rgba(11,28,46,0.10) 0%, transparent 60%);
		border-top:3px solid var(--jp-teal);
	}

	.jp-filter-card{ background:#fff; border:1px solid var(--jp-line); border-top:3px solid var(--jp-teal); border-radius:16px; padding:22px; box-shadow:0 10px 26px rgba(11,28,46,0.06); }
	.jp-filter-card h6{ font-weight:800; color:var(--jp-ink); font-size:.8rem; letter-spacing:.05em; text-transform:uppercase; margin-bottom:14px; }
	.jp-filter-card .form-check{ margin-bottom:9px; }
	.jp-filter-card .form-check-label{ font-size:.9rem; color:#33475B; font-weight:500; }
	.jp-filter-divider{ border-top:1px solid var(--jp-line); margin:18px 0; }
	.jp-chip{ display:inline-flex; align-items:center; gap:6px; background:#fff; color:#33475B; font-weight:700; font-size:.78rem; padding:6px 12px; border-radius:20px; border:1px solid var(--jp-line); }
	.jp-chip:hover{ background:var(--jp-bg-soft); color:var(--jp-teal); border-color:var(--jp-teal); }
	.jp-chip button{ border:none; background:none; color:var(--jp-muted); line-height:0; }

	.jp-kicker{ color:var(--jp-teal); font-weight:800; letter-spacing:.06em; text-transform:uppercase; font-size:11.5px; }

	/* ===== Featured jobs — one per line, navy blending into green ===== */
	.jp-featured-stack{ display:flex; flex-direction:column; gap:16px; }
	.jp-featured-card{
		background: linear-gradient(120deg, var(--jp-navy) 0%, var(--jp-navy-2) 45%, var(--jp-teal) 100%);
		border-radius:16px; padding:22px 26px; position:relative; overflow:hidden;
		display:flex; align-items:center; gap:20px; flex-wrap:wrap;
		box-shadow:0 16px 34px rgba(11,28,46,0.18);
	}
	.jp-featured-card::before{
		content:""; position:absolute; inset:0;
		background-image: linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
		background-size: 34px 34px; mask-image: linear-gradient(to right, black, transparent 70%); pointer-events:none;
	}
	.jp-featured-card::after{
		content:""; position:absolute; left:0; top:0; bottom:0; width:5px; background:var(--jp-gradient);
	}
	/* Logo square on the dark featured card — plain white tile, no green glow */
	.jp-featured-card .jp-logo-sq{
		width:56px; height:56px; border-radius:14px; background:#fff; color:var(--jp-navy);
		display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1rem; flex-shrink:0;
		box-shadow:0 4px 12px rgba(0,0,0,0.2); position:relative; z-index:1; overflow:hidden;
	}
	.jp-featured-card .jp-logo-sq img{ width:100%; height:100%; object-fit:cover; }
	.jp-featured-card .title{ color:#fff; font-weight:800; font-size:1.08rem; }
	.jp-featured-card .title:hover{ color:#5EE29B; }
	.jp-featured-card .sub{ color:#AFC0D2; font-size:.85rem; font-weight:600; }
	.jp-featured-card .jp-salary{ color:#7CF0C2; font-weight:800; font-size:1.05rem; white-space:nowrap; }
	.jp-featured-card .body{ flex:1 1 320px; min-width:0; position:relative; z-index:1; }
	.jp-featured-card .side{ display:flex; align-items:center; gap:16px; flex-shrink:0; position:relative; z-index:1; margin-left:auto; }
	.jp-pill-featured{ background:rgba(255,255,255,0.16); color:#fff; border:1px solid rgba(255,255,255,0.3); font-size:11px; font-weight:800; letter-spacing:.03em; text-transform:uppercase; padding:5px 12px; border-radius:20px; }

	/* ===== Regular jobs — two per line ===== */
	.jp-job-grid .jp-job-col{ display:flex; }
	.jp-job-row{ background:linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%); border:1px solid var(--jp-line); border-radius:14px; padding:22px; transition:.2s; box-shadow:0 6px 18px rgba(11,28,46,0.06); width:100%; display:flex; flex-direction:column; }
	.jp-job-row:hover{ border-color:var(--jp-teal); box-shadow:0 14px 30px rgba(11,28,46,0.1); transform:translateY(-2px); }
	.jp-job-row .title{ font-weight:700; color:var(--jp-ink); }
	.jp-job-row .title:hover{ color:var(--jp-teal); }
	.jp-job-row .meta i{ color:#94A3B8; margin-right:5px; }
	.jp-salary{ color:var(--jp-teal); font-weight:800; }
	.jp-sort-select{ border-radius:10px; }

	/* Plain, flat logo tile — no gradient shine */
	.jp-logo-sq{ width:48px; height:48px; border-radius:12px; background:var(--jp-bg-soft); color:var(--jp-navy); border:1px solid var(--jp-line); display:flex; align-items:center; justify-content:center; font-weight:800; overflow:hidden; }
	.jp-logo-sq img{ width:100%; height:100%; object-fit:cover; }
	.jp-pill{ background:var(--jp-bg-soft); color:#3B5166; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; border:1px solid var(--jp-line); }
	.jp-pill-urgent{ background:#FEECEC; color:#C0392B; border-color:rgba(192,57,43,0.15); }
	.jp-pill-legacy{ background:#F1F3F5; color:#8A94A0; }

	.jp-empty-card{ background:#fff; border:1px dashed var(--jp-line); border-radius:16px; }

	.jp-filter-toggle{ width:34px; height:34px; border-radius:9px; border:1px solid var(--jp-line); background:var(--jp-bg-soft); color:var(--jp-teal); display:flex; align-items:center; justify-content:center; font-size:1.1rem; font-weight:800; line-height:1; }
	.jp-filter-toggle .icon-minus{ display:none; }
	.jp-filter-toggle[aria-expanded="true"] .icon-plus{ display:none; }
	.jp-filter-toggle[aria-expanded="true"] .icon-minus{ display:inline; }
</style>
@endpush

@section('content')

@php
    // Both the featured row and the main list read from the same real API shape now:
    // job_title, company.name/logo, job_location.name, job_type.name, formatted_salary,
    // job_category.name, published_at, is_legacy, has_real_deadline, deadline.
    $jobList = is_array($jobs) ? ($jobs['data'] ?? []) : [];

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

<!-- ====================== SEARCH BAND ====================== -->
<div class="jp-search-band py-8 py-lg-10">
	<div class="container">
		<h1 class="mb-2">Find your next role</h1>
		<p>{{ number_format($totalJobs ?? 0) }}+ live jobs across {{ country_name() }}, updated daily.</p>

		<form action="{{ route('jobs.index') }}" method="GET" class="jp-search-box d-flex flex-column flex-lg-row align-items-stretch">
			<div class="flex-grow-1 d-flex align-items-center px-4 py-3 py-lg-2">
				<i class="ki-duotone ki-magnifier fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
				<input type="text" name="search" class="form-control" placeholder="Job title, keyword or company" value="{{ request('search') }}" />
			</div>
			<div class="divider d-none d-lg-block my-2"></div>
			<div class="flex-grow-1 d-flex align-items-center px-4 py-3 py-lg-2">
				<i class="ki-duotone ki-geolocation fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
				<input type="text" name="location" class="form-control" placeholder="City, state or postcode" value="{{ request('location') }}" />
			</div>
			<div class="divider d-none d-lg-block my-2"></div>
			<div class="px-2 py-2 d-flex align-items-center">
				<button type="submit" class="btn jp-btn-primary w-100 px-6 py-3">Search Jobs</button>
			</div>
		</form>

		<div class="d-flex gap-2 flex-wrap mt-4">
			<span class="fs-8 fw-semibold me-1 pt-1" style="color:var(--jp-muted);">Popular:</span>
			@foreach(['Warehouse','Nursing','Hospitality','Retail','Construction','Admin'] as $tag)
			<a href="{{ route('jobs.index') }}?search={{ urlencode($tag) }}" class="jp-chip text-decoration-none">{{ $tag }}</a>
			@endforeach
		</div>
	</div>
</div>

<!-- ====================== LISTINGS ====================== -->
<div class="jp-listing-bg">
	<div class="container py-10 py-lg-12">
		<div class="row g-6 g-lg-10">

			<!-- ====================== FILTERS SIDEBAR ====================== -->
			<div class="col-lg-3">
				<div class="jp-filter-card">
					<div class="d-flex align-items-center justify-content-between mb-2">
						<h6 class="mb-0">Filters</h6>
						<div class="d-flex align-items-center gap-3">
							<a href="{{ route('jobs.index') }}" class="fs-8 fw-semibold text-muted">Reset all</a>
							<button type="button" class="jp-filter-toggle d-lg-none" data-bs-toggle="collapse" data-bs-target="#jpFilterBody" aria-expanded="false" aria-controls="jpFilterBody">
								<span class="icon-plus">+</span>
								<span class="icon-minus">&minus;</span>
							</button>
						</div>
					</div>

					<div class="collapse d-lg-block" id="jpFilterBody">
						<form action="{{ route('jobs.index') }}" method="GET" id="filterForm">
							<div class="jp-filter-divider"></div>
							<h6>Job Type</h6>
							@foreach(['Full-time','Part-time','Contract','Casual','Internship'] as $type)
							<div class="form-check form-check-custom">
								<input class="form-check-input" type="checkbox" name="job_type[]" value="{{ $type }}" id="jt-{{ $loop->index }}" {{ in_array($type, (array) request('job_type', [])) ? 'checked' : '' }} />
								<label class="form-check-label" for="jt-{{ $loop->index }}">{{ $type }}</label>
							</div>
							@endforeach

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
							<h6>Salary Range (AUD)</h6>
							<div class="d-flex gap-2 mb-2">
								<input type="number" class="form-control form-control-sm" placeholder="Min" name="min_salary" value="{{ request('min_salary') }}" />
								<input type="number" class="form-control form-control-sm" placeholder="Max" name="max_salary" value="{{ request('max_salary') }}" />
							</div>

							<div class="jp-filter-divider"></div>
							<h6>Work Setting</h6>
							<div class="form-check form-check-custom">
								<input class="form-check-input" type="checkbox" name="setting[]" value="on-site" id="st-1" {{ in_array('on-site', (array) request('setting', [])) ? 'checked' : '' }} />
								<label class="form-check-label" for="st-1">On-site</label>
							</div>
							<div class="form-check form-check-custom">
								<input class="form-check-input" type="checkbox" name="setting[]" value="remote" id="st-2" {{ in_array('remote', (array) request('setting', [])) ? 'checked' : '' }} />
								<label class="form-check-label" for="st-2">Remote</label>
							</div>
							<div class="form-check form-check-custom">
								<input class="form-check-input" type="checkbox" name="setting[]" value="hybrid" id="st-3" {{ in_array('hybrid', (array) request('setting', [])) ? 'checked' : '' }} />
								<label class="form-check-label" for="st-3">Hybrid</label>
							</div>

							<!-- Preserve search, location, and sort parameters -->
							@if(request('search'))
								<input type="hidden" name="search" value="{{ request('search') }}">
							@endif
							@if(request('location'))
								<input type="hidden" name="location" value="{{ request('location') }}">
							@endif
							@if(request('sort'))
								<input type="hidden" name="sort" value="{{ request('sort') }}">
							@endif

							<button type="submit" class="btn jp-btn-primary w-100 mt-6">Apply Filters</button>
						</form>
					</div>
				</div>
			</div>

			<!-- ====================== JOB LISTINGS ====================== -->
			<div class="col-lg-9">

				<!-- Featured jobs: ONE per line -->
				@if(!empty($featuredJobs))
				<div class="mb-10">
					<div class="jp-kicker mb-2">Featured</div>
					<div class="jp-featured-stack">
						@foreach($featuredJobs as $job)
						<div class="jp-featured-card">
							<div class="jp-logo-sq">
								@if(!empty($job['company']['logo']))
									<img src="{{ $job['company']['logo'] }}" alt="{{ $job['company']['name'] ?? '' }}">
								@else
									{{ $jobInitials($job) }}
								@endif
							</div>
							<div class="body">
								<a href="{{ route('jobs.show', $job['slug'] ?? $job['id']) }}" class="title d-block mb-1 text-decoration-none">
									{{ $job['job_title'] ?? 'Job Title' }}
								</a>
								<div class="sub">
									{{ $job['company']['name'] ?? 'Company' }} · {{ $job['job_location']['name'] ?? $job['duty_station'] ?? 'Location' }}
								</div>
							</div>
							<div class="side">
								<span class="jp-pill-featured d-none d-sm-inline-block">Featured</span>
								<div class="jp-salary">{{ $job['formatted_salary'] ?? 'Negotiable' }}</div>
								<a href="{{ route('jobs.show', $job['slug'] ?? $job['id']) }}" class="btn btn-sm jp-btn-primary">View</a>
							</div>
						</div>
						@endforeach
					</div>
				</div>
				@endif

				<!-- Regular listing header -->
				<div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
					<div class="fw-semibold text-gray-700">Showing <span class="fw-bold text-gray-900">{{ count($jobList) }}</span> of {{ number_format($totalJobs ?? 0) }} jobs</div>
					<select class="form-select form-select-sm jp-sort-select w-auto" onchange="window.location.href = updateQueryParam('sort', this.value)">
						<option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
						<option value="salary_high" {{ request('sort') === 'salary_high' ? 'selected' : '' }}>Highest Salary</option>
						<option value="salary_low" {{ request('sort') === 'salary_low' ? 'selected' : '' }}>Lowest Salary</option>
					</select>
				</div>

				<!-- Job Grid - No form wrapping it -->
				<div class="row g-4 jp-job-grid">
					@forelse($jobList as $job)
						@php
							$isLegacy = (bool) ($job['is_legacy'] ?? false);
							$companyName = $job['company']['name'] ?? 'Company Name';
							$title = $job['job_title'] ?? 'Job Title';
							$slug = $job['slug'] ?? ($job['id'] ?? 1);
							$location = $job['job_location']['name'] ?? ($job['duty_station'] ?: 'Location not specified');
							$jobTypeName = $job['job_type']['name'] ?? ucfirst(str_replace('-', ' ', $job['employment_type'] ?? 'Full-time'));
							$salary = $job['formatted_salary'] ?? 'Negotiable';
							$category = $job['job_category']['name'] ?? 'General';
						@endphp
						<div class="col-md-6 jp-job-col">
							<div class="jp-job-row">
								<div class="d-flex gap-4">
									<div class="jp-logo-sq flex-shrink-0">
										@if(!empty($job['company']['logo']))
											<img src="{{ $job['company']['logo'] }}" alt="{{ $companyName }}">
										@else
											{{ $jobInitials($job) }}
										@endif
									</div>
									<div class="flex-grow-1">
										<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
											<div>
												<a href="{{ route('jobs.show', $slug) }}" class="title fs-5">{{ $title }}</a>
												<div class="text-muted fs-7 mt-1">{{ $companyName }}</div>
											</div>
											<span class="jp-salary fs-6">{{ $salary }}</span>
										</div>
										<div class="d-flex flex-wrap gap-4 mt-3 meta fs-8 text-muted">
											<span><i class="ki-duotone ki-geolocation fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $location }}</span>
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
												<span class="jp-pill">{{ $category }}</span>
												@if(!empty($job['is_urgent']))
													<span class="jp-pill jp-pill-urgent">Urgent</span>
												@endif
											</div>
											<a href="{{ route('jobs.show', $slug) }}" class="btn btn-sm btn-outline btn-outline-primary">View & Apply</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					@empty
						<div class="col-12">
							<div class="jp-empty-card text-center py-10">
								<i class="ki-duotone ki-file-deleted fs-3x text-muted d-block mb-3"><span class="path1"></span><span class="path2"></span></i>
								<p class="fw-semibold fs-5 mb-1">No jobs found</p>
								<p class="text-muted">Try broadening your search or clearing some filters.</p>
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
				@endphp
				<div class="d-flex justify-content-center mt-10">
					<nav>
						<ul class="pagination">
							<li class="page-item {{ $current <= 1 ? 'disabled' : '' }}">
								<a class="page-link" href="{{ url()->current() }}?page={{ $current - 1 }}&{{ http_build_query(request()->except('page')) }}">Prev</a>
							</li>

							@if($start > 1)
								<li class="page-item"><a class="page-link" href="{{ url()->current() }}?page=1&{{ http_build_query(request()->except('page')) }}">1</a></li>
								@if($start > 2)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
							@endif

							@for($i = $start; $i <= $end; $i++)
								<li class="page-item {{ $i == $current ? 'active' : '' }}">
									<a class="page-link" href="{{ url()->current() }}?page={{ $i }}&{{ http_build_query(request()->except('page')) }}">{{ $i }}</a>
								</li>
							@endfor

							@if($end < $last)
								@if($end < $last - 1)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
								<li class="page-item"><a class="page-link" href="{{ url()->current() }}?page={{ $last }}&{{ http_build_query(request()->except('page')) }}">{{ $last }}</a></li>
							@endif

							<li class="page-item {{ $current >= $last ? 'disabled' : '' }}">
								<a class="page-link" href="{{ url()->current() }}?page={{ $current + 1 }}&{{ http_build_query(request()->except('page')) }}">Next</a>
							</li>
						</ul>
					</nav>
				</div>
				@endif

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

// Auto-submit filter form when checkbox changes (optional)
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        // You can enable auto-submit by uncommenting below
        // const checkboxes = filterForm.querySelectorAll('input[type="checkbox"]');
        // checkboxes.forEach(checkbox => {
        //     checkbox.addEventListener('change', function() {
        //         filterForm.submit();
        //     });
        // });
    }
});
</script>
@endpush