@extends('layouts.app')

@section('title', 'Browse Jobs — Search Australia\'s Latest Openings')
@section('meta_description', 'Search thousands of live jobs across Australia. Filter by location, category, job type and salary. Apply in one tap with Easy Apply.')

@push('styles')
<style>
	:root{
		--jp-navy: #0B1C2E;
		--jp-green: #20AA3E;
		--jp-teal: #03A588;
		--jp-gradient: linear-gradient(135deg, #20AA3E 0%, #03A588 100%);
		--jp-ink: #0F1B2D;
		--jp-muted: #64748B;
		--jp-bg-soft: #F4F8F7;
		--jp-line: rgba(15,27,45,0.08);
	}

	.jp-search-band{ background: linear-gradient(120% 120% at 10% 0%, #16304A 0%, var(--jp-navy) 60%, #081420 100%); }
	.jp-search-band h1{ color:#fff; font-weight:800; font-size:1.9rem; }
	.jp-search-band p{ color:#B9C6D6; }
	.jp-search-box{ background:#fff; border-radius:14px; padding:8px; box-shadow:0 20px 46px rgba(0,0,0,0.28); }
	.jp-search-box .form-control, .jp-search-box .form-select{ border:none; box-shadow:none; }
	.jp-search-box .divider{ width:1px; background:var(--jp-line); }
	.jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
	.jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }

	.jp-filter-card{ background:#fff; border:1px solid var(--jp-line); border-radius:16px; padding:22px; }
	.jp-filter-card h6{ font-weight:800; color:var(--jp-ink); font-size:.8rem; letter-spacing:.05em; text-transform:uppercase; margin-bottom:14px; }
	.jp-filter-card .form-check{ margin-bottom:9px; }
	.jp-filter-card .form-check-label{ font-size:.9rem; color:#33475B; font-weight:500; }
	.jp-filter-divider{ border-top:1px solid var(--jp-line); margin:18px 0; }
	.jp-chip{ display:inline-flex; align-items:center; gap:6px; background:var(--jp-bg-soft); color:#33475B; font-weight:700; font-size:.78rem; padding:6px 12px; border-radius:20px; border:1px solid var(--jp-line); }
	.jp-chip button{ border:none; background:none; color:var(--jp-muted); line-height:0; }

	.jp-kicker{ color:var(--jp-teal); font-weight:800; letter-spacing:.06em; text-transform:uppercase; font-size:11.5px; }

	.jp-featured-row{ display:flex; gap:18px; overflow-x:auto; padding-bottom:6px; }
	.jp-featured-card{ min-width:300px; max-width:300px; background:#fff; border:1px solid var(--jp-line); border-left:4px solid var(--jp-green); border-radius:14px; padding:20px; }
	.jp-logo-sq{ width:48px; height:48px; border-radius:12px; background:var(--jp-bg-soft); display:flex; align-items:center; justify-content:center; font-weight:800; color:var(--jp-teal); }
	.jp-pill{ background:var(--jp-bg-soft); color:#3B5166; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; }

	.jp-job-row{ background:#fff; border:1px solid var(--jp-line); border-radius:14px; padding:22px; transition:.2s; }
	.jp-job-row:hover{ border-color:var(--jp-teal); box-shadow:0 10px 26px rgba(11,28,46,0.06); }
	.jp-job-row .title{ font-weight:700; color:var(--jp-ink); }
	.jp-job-row .title:hover{ color:var(--jp-teal); }
	.jp-job-row .meta i{ color:#94A3B8; margin-right:5px; }
	.jp-salary{ color:var(--jp-teal); font-weight:800; }
	.jp-sort-select{ border-radius:10px; }
</style>
@endpush

@section('content')

<!-- ====================== SEARCH BAND ====================== -->
<div class="jp-search-band py-12 py-lg-16">
	<div class="container">
		<h1 class="mb-2">Find your next role</h1>
		<p class="mb-8">{{ number_format($jobsCount ?? 15000) }}+ live jobs across Australia, updated daily.</p>

		<form action="{{ route('jobs.index') }}" method="GET" class="jp-search-box d-flex flex-column flex-lg-row align-items-stretch">
			<div class="flex-grow-1 d-flex align-items-center px-4 py-3 py-lg-2">
				<i class="ki-duotone ki-magnifier fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
				<input type="text" name="q" class="form-control" placeholder="Job title, keyword or company" value="{{ request('q') }}" />
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

		<div class="d-flex gap-2 flex-wrap mt-5">
			<span class="text-white opacity-50 fs-8 fw-semibold me-1 pt-1">Popular:</span>
			@foreach(['Warehouse','Nursing','Hospitality','Retail','Construction','Admin'] as $tag)
			<a href="{{ route('jobs.index') }}?q={{ urlencode($tag) }}" class="jp-chip text-decoration-none">{{ $tag }}</a>
			@endforeach
		</div>
	</div>
</div>

<div class="container py-12 py-lg-16">
	<div class="row g-6 g-lg-10">

		<!-- ====================== FILTERS SIDEBAR ====================== -->
		<div class="col-lg-3">
			<div class="jp-filter-card">
				<div class="d-flex align-items-center justify-content-between mb-2">
					<h6 class="mb-0">Filters</h6>
					<a href="{{ route('jobs.index') }}" class="fs-8 fw-semibold text-muted">Reset all</a>
				</div>

				<div class="jp-filter-divider"></div>
				<h6>Talent Type</h6>
				<div class="form-check form-check-custom">
					<input class="form-check-input" type="checkbox" name="talent_type[]" value="white_collar" id="wc" />
					<label class="form-check-label" for="wc">Office &amp; Professional</label>
				</div>
				<div class="form-check form-check-custom">
					<input class="form-check-input" type="checkbox" name="talent_type[]" value="blue_collar" id="bc" />
					<label class="form-check-label" for="bc">Blue-Collar &amp; Trades</label>
				</div>
				<div class="form-check form-check-custom">
					<input class="form-check-input" type="checkbox" name="talent_type[]" value="casual" id="cs" />
					<label class="form-check-label" for="cs">Casual &amp; Shift Work</label>
				</div>

				<div class="jp-filter-divider"></div>
				<h6>Job Type</h6>
				@foreach(['Full-time','Part-time','Contract','Casual','Internship'] as $type)
				<div class="form-check form-check-custom">
					<input class="form-check-input" type="checkbox" name="job_type[]" value="{{ $type }}" id="jt-{{ $loop->index }}" />
					<label class="form-check-label" for="jt-{{ $loop->index }}">{{ $type }}</label>
				</div>
				@endforeach

				<div class="jp-filter-divider"></div>
				<h6>Category</h6>
				@foreach(['Logistics & Warehouse','Healthcare','Hospitality','Construction & Trades','Technology','Admin & Support'] as $cat)
				<div class="form-check form-check-custom">
					<input class="form-check-input" type="checkbox" name="category[]" value="{{ $cat }}" id="cat-{{ $loop->index }}" />
					<label class="form-check-label" for="cat-{{ $loop->index }}">{{ $cat }}</label>
				</div>
				@endforeach

				<div class="jp-filter-divider"></div>
				<h6>Salary Range (AUD)</h6>
				<div class="d-flex gap-2 mb-2">
					<input type="number" class="form-control form-control-sm" placeholder="Min" name="salary_min" />
					<input type="number" class="form-control form-control-sm" placeholder="Max" name="salary_max" />
				</div>

				<div class="jp-filter-divider"></div>
				<h6>Work Setting</h6>
				<div class="form-check form-check-custom">
					<input class="form-check-input" type="checkbox" name="setting[]" value="onsite" id="st-1" />
					<label class="form-check-label" for="st-1">On-site</label>
				</div>
				<div class="form-check form-check-custom">
					<input class="form-check-input" type="checkbox" name="setting[]" value="remote" id="st-2" />
					<label class="form-check-label" for="st-2">Remote</label>
				</div>
				<div class="form-check form-check-custom">
					<input class="form-check-input" type="checkbox" name="setting[]" value="hybrid" id="st-3" />
					<label class="form-check-label" for="st-3">Hybrid</label>
				</div>

				<button type="submit" form="filter-form" class="btn jp-btn-primary w-100 mt-6">Apply Filters</button>
			</div>
		</div>

		<!-- ====================== JOB LISTINGS ====================== -->
		<div class="col-lg-9">

			<!-- Featured jobs -->
			<div class="mb-10">
				<div class="jp-kicker mb-2">Featured</div>
				<div class="jp-featured-row">
					@forelse(($featuredJobs ?? []) as $job)
					<div class="jp-featured-card">
						<div class="d-flex align-items-center justify-content-between mb-3">
							<div class="jp-logo-sq">{{ strtoupper(substr($job->company_name ?? 'JM', 0, 2)) }}</div>
							<span class="jp-pill">Featured</span>
						</div>
						<a href="{{ route('jobs.show', $job->slug ?? $job->id) }}" class="title fw-bold d-block mb-1 text-gray-900">{{ $job->title ?? 'Job Title' }}</a>
						<div class="text-muted fs-8 mb-3">{{ $job->company_name ?? 'Company' }} · {{ $job->location ?? 'Location' }}</div>
						<div class="jp-salary fs-6">{{ $job->salary ?? 'Competitive' }}</div>
					</div>
					@empty
					@foreach(['Site Supervisor','ICU Nurse — Casual','Delivery Driver'] as $i => $title)
					<div class="jp-featured-card">
						<div class="d-flex align-items-center justify-content-between mb-3">
							<div class="jp-logo-sq">{{ ['BX','MH','SC'][$i] }}</div>
							<span class="jp-pill">Featured</span>
						</div>
						<a href="#" class="title fw-bold d-block mb-1 text-gray-900">{{ $title }}</a>
						<div class="text-muted fs-8 mb-3">{{ ['Buildex Australia','Melbourne Health','Swift Courier'][$i] }} · {{ ['Brisbane','Melbourne','Perth'][$i] }}</div>
						<div class="jp-salary fs-6">{{ ['$44/hr','$58/hr','$29/hr'][$i] }}</div>
					</div>
					@endforeach
					@endforelse
				</div>
			</div>

			<!-- Regular listing header -->
			<div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
				<div class="fw-semibold text-gray-700">Showing <span class="fw-bold text-gray-900">{{ ($jobs->count() ?? 24) }}</span> of {{ number_format($jobsCount ?? 15243) }} jobs</div>
				<select class="form-select form-select-sm jp-sort-select w-auto">
					<option>Most Relevant</option>
					<option>Newest First</option>
					<option>Highest Salary</option>
				</select>
			</div>

			<form id="filter-form" action="{{ route('jobs.index') }}" method="GET">
			<div class="d-flex flex-column gap-4">
				@forelse(($jobs ?? []) as $job)
				<div class="jp-job-row">
					<div class="d-flex gap-4">
						<div class="jp-logo-sq flex-shrink-0">{{ strtoupper(substr($job->company_name ?? 'JM', 0, 2)) }}</div>
						<div class="flex-grow-1">
							<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
								<div>
									<a href="{{ route('jobs.show', $job->slug ?? $job->id) }}" class="title fs-5">{{ $job->title ?? 'Job Title' }}</a>
									<div class="text-muted fs-7 mt-1">{{ $job->company_name ?? 'Company Name' }}</div>
								</div>
								<span class="jp-salary fs-6">{{ $job->salary ?? 'Competitive' }}</span>
							</div>
							<div class="d-flex flex-wrap gap-4 mt-3 meta fs-8 text-muted">
								<span><i class="ki-duotone ki-geolocation fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $job->location ?? 'Sydney, NSW' }}</span>
								<span><i class="ki-duotone ki-briefcase fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $job->job_type ?? 'Full-time' }}</span>
								<span><i class="ki-duotone ki-calendar fs-6"><span class="path1"></span><span class="path2"></span></i>Posted {{ $job->posted_at ?? '2 days ago' }}</span>
							</div>
							<div class="d-flex align-items-center justify-content-between mt-4 flex-wrap gap-2">
								<div class="d-flex gap-2 flex-wrap">
									<span class="jp-pill">{{ $job->category ?? 'General' }}</span>
								</div>
								<a href="{{ route('jobs.show', $job->slug ?? $job->id) }}" class="btn btn-sm btn-outline btn-outline-primary">View & Apply</a>
							</div>
						</div>
					</div>
				</div>
				@empty
				@foreach(range(1,6) as $i)
				<div class="jp-job-row">
					<div class="d-flex gap-4">
						<div class="jp-logo-sq flex-shrink-0">JM</div>
						<div class="flex-grow-1">
							<div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
								<div>
									<a href="{{ route('jobs.index') }}" class="title fs-5">Customer Service Officer</a>
									<div class="text-muted fs-7 mt-1">National Retail Group</div>
								</div>
								<span class="jp-salary fs-6">$62K–$68K</span>
							</div>
							<div class="d-flex flex-wrap gap-4 mt-3 meta fs-8 text-muted">
								<span><i class="ki-duotone ki-geolocation fs-6"><span class="path1"></span><span class="path2"></span></i>Adelaide, SA</span>
								<span><i class="ki-duotone ki-briefcase fs-6"><span class="path1"></span><span class="path2"></span></i>Full-time</span>
								<span><i class="ki-duotone ki-calendar fs-6"><span class="path1"></span><span class="path2"></span></i>Posted 3 days ago</span>
							</div>
							<div class="d-flex align-items-center justify-content-between mt-4 flex-wrap gap-2">
								<span class="jp-pill">Admin &amp; Support</span>
								<a href="{{ route('jobs.index') }}" class="btn btn-sm btn-outline btn-outline-primary">View & Apply</a>
							</div>
						</div>
					</div>
				</div>
				@endforeach
				@endforelse
			</div>
			</form>

			<!-- Pagination -->
			<div class="d-flex justify-content-center mt-10">
				@if(isset($jobs) && method_exists($jobs, 'links'))
					{{ $jobs->links() }}
				@else
				<nav>
					<ul class="pagination">
						<li class="page-item disabled"><a class="page-link" href="#">Prev</a></li>
						<li class="page-item active"><a class="page-link" href="#">1</a></li>
						<li class="page-item"><a class="page-link" href="#">2</a></li>
						<li class="page-item"><a class="page-link" href="#">3</a></li>
						<li class="page-item"><a class="page-link" href="#">Next</a></li>
					</ul>
				</nav>
				@endif
			</div>

		</div>
	</div>
</div>

@endsection