@extends('layouts.app')

@section('title', 'Browse Companies — ' . country_name() . '\'s Top Employers')
@section('meta_description', 'Discover top companies hiring in ' . country_name() . '. Browse employer profiles, view open positions, and apply directly.')

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

    /* Search band */
    .jp-search-band{ background: linear-gradient(120deg, #EAF7F5 0%, #E7F1FB 100%); border-bottom:1px solid var(--jp-line); }
    .jp-search-band h1{ color:var(--jp-ink); font-weight:800; font-size:1.7rem; margin-bottom:.35rem; }
    .jp-search-band p{ color:var(--jp-muted); margin-bottom:1.5rem; }
    .jp-search-box{ background:#fff; border-radius:14px; padding:8px; box-shadow:0 12px 30px rgba(11,28,46,0.08); border:1px solid var(--jp-line); }
    .jp-search-box .form-control, .jp-search-box .form-select{ border:none; box-shadow:none; }
    .jp-search-box .divider{ width:1px; background:var(--jp-line); }
    .jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
    .jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }

    /* Page backdrop */
    .jp-listing-bg{
        background:var(--jp-bg-page);
        background-image:
            radial-gradient(65% 55% at 100% 0%, rgba(3,165,136,0.14) 0%, transparent 60%),
            radial-gradient(55% 45% at 0% 15%, rgba(11,28,46,0.10) 0%, transparent 60%);
        border-top:3px solid var(--jp-teal);
    }

    /* Filter card */
    .jp-filter-card{ background:#fff; border:1px solid var(--jp-line); border-top:3px solid var(--jp-teal); border-radius:16px; padding:22px; box-shadow:0 10px 26px rgba(11,28,46,0.06); }
    .jp-filter-card h6{ font-weight:800; color:var(--jp-ink); font-size:.8rem; letter-spacing:.05em; text-transform:uppercase; margin-bottom:14px; }
    .jp-filter-card .form-check{ margin-bottom:9px; }
    .jp-filter-card .form-check-label{ font-size:.9rem; color:#33475B; font-weight:500; }
    .jp-filter-divider{ border-top:1px solid var(--jp-line); margin:18px 0; }

    /* Company cards */
    .jp-company-grid .jp-company-col{ display:flex; }
    .jp-company-card{
        background:linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%);
        border:1px solid var(--jp-line);
        border-radius:14px;
        padding:22px;
        transition:.2s;
        box-shadow:0 6px 18px rgba(11,28,46,0.06);
        width:100%;
        display:flex;
        flex-direction:column;
    }
    .jp-company-card:hover{
        border-color:var(--jp-teal);
        box-shadow:0 14px 30px rgba(11,28,46,0.1);
        transform:translateY(-2px);
    }
    .jp-company-card .company-logo{
        width:72px;
        height:72px;
        border-radius:14px;
        background:var(--jp-bg-soft);
        border:1px solid var(--jp-line);
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:800;
        font-size:1.3rem;
        overflow:hidden;
        flex-shrink:0;
        color:var(--jp-navy);
    }
    .jp-company-card .company-logo img{ width:100%; height:100%; object-fit:cover; }
    .jp-company-card .company-name{ font-weight:700; color:var(--jp-ink); font-size:1.05rem; }
    .jp-company-card .company-name:hover{ color:var(--jp-teal); }
    .jp-company-card .company-meta i{ color:#94A3B8; margin-right:5px; }
    .jp-company-card .company-description{
        color:var(--jp-muted);
        font-size:.88rem;
        line-height:1.6;
        display:-webkit-box;
        -webkit-line-clamp:2;
        -webkit-box-orient:vertical;
        overflow:hidden;
    }
    .jp-company-card .job-count{
        color:var(--jp-teal);
        font-weight:700;
        font-size:.9rem;
    }

    /* Pill styles */
    .jp-pill{ background:var(--jp-bg-soft); color:#3B5166; font-size:11px; font-weight:700; padding:4px 12px; border-radius:20px; border:1px solid var(--jp-line); display:inline-flex; align-items:center; gap:4px; }
    .jp-pill-gold{ background:#FFF6E0; color:#B8860B; border-color:rgba(184,134,11,0.15); }
    .jp-pill-verified{ background:#E9F9EF; color:#1E9E4C; border-color:rgba(30,158,76,0.15); }
    .jp-pill-featured{ background:#E7F1FB; color:#1D6FCC; border-color:rgba(29,111,204,0.15); }

    .jp-empty-card{ background:#fff; border:1px dashed var(--jp-line); border-radius:16px; }
    .jp-sort-select{ border-radius:10px; }
    .jp-chip{ display:inline-flex; align-items:center; gap:6px; background:#fff; color:#33475B; font-weight:700; font-size:.78rem; padding:6px 12px; border-radius:20px; border:1px solid var(--jp-line); text-decoration:none; }
    .jp-chip:hover{ background:var(--jp-bg-soft); color:var(--jp-teal); border-color:var(--jp-teal); }
    .jp-filter-toggle{ width:34px; height:34px; border-radius:9px; border:1px solid var(--jp-line); background:var(--jp-bg-soft); color:var(--jp-teal); display:flex; align-items:center; justify-content:center; font-size:1.1rem; font-weight:800; line-height:1; }
    .jp-filter-toggle .icon-minus{ display:none; }
    .jp-filter-toggle[aria-expanded="true"] .icon-plus{ display:none; }
    .jp-filter-toggle[aria-expanded="true"] .icon-minus{ display:inline; }

    .jp-kicker{ color:var(--jp-teal); font-weight:800; letter-spacing:.06em; text-transform:uppercase; font-size:11.5px; }
</style>
@endpush

@section('content')

@php
    $companyList = is_array($companies) ? ($companies['data'] ?? []) : [];

    $getInitials = function ($company) {
        $name = $company['name'] ?? 'Company';
        $name = trim($name);
        return $name !== '' ? strtoupper(substr($name, 0, 2)) : 'CO';
    };

    $truncateDescription = function ($text, $length = 120) {
        if (empty($text)) return '';
        $text = strip_tags($text);
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . '...';
    };
@endphp

<!-- ====================== SEARCH BAND ====================== -->
<div class="jp-search-band py-8 py-lg-10">
    <div class="container">
        <h1 class="mb-2">Find top employers</h1>
        <p>{{ number_format($totalCompanies ?? 0) }}+ companies hiring in {{ country_name() }}, updated daily.</p>

        <form action="{{ route('companies.index') }}" method="GET" class="jp-search-box d-flex flex-column flex-lg-row align-items-stretch">
            <div class="flex-grow-1 d-flex align-items-center px-4 py-3 py-lg-2">
                <i class="ki-duotone ki-magnifier fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" name="search" class="form-control" placeholder="Company name, industry or keyword" value="{{ request('search') }}" />
            </div>
            <div class="divider d-none d-lg-block my-2"></div>
            <div class="flex-grow-1 d-flex align-items-center px-4 py-3 py-lg-2">
                <i class="ki-duotone ki-geolocation fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" name="location" class="form-control" placeholder="City, state or postcode" value="{{ request('location') }}" />
            </div>
            <div class="divider d-none d-lg-block my-2"></div>
            <div class="px-2 py-2 d-flex align-items-center">
                <button type="submit" class="btn jp-btn-primary w-100 px-6 py-3">Search Companies</button>
            </div>
        </form>

        <div class="d-flex gap-2 flex-wrap mt-4">
            <span class="fs-8 fw-semibold me-1 pt-1" style="color:var(--jp-muted);">Popular:</span>
            @foreach(['Technology','Healthcare','Finance','Retail','Construction','Education'] as $tag)
            <a href="{{ route('companies.index') }}?search={{ urlencode($tag) }}" class="jp-chip text-decoration-none">{{ $tag }}</a>
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
                            <a href="{{ route('companies.index') }}" class="fs-8 fw-semibold text-muted">Reset all</a>
                            <button type="button" class="jp-filter-toggle d-lg-none" data-bs-toggle="collapse" data-bs-target="#jpFilterBody" aria-expanded="false" aria-controls="jpFilterBody">
                                <span class="icon-plus">+</span>
                                <span class="icon-minus">&minus;</span>
                            </button>
                        </div>
                    </div>

                    <div class="collapse d-lg-block" id="jpFilterBody">
                        <form action="{{ route('companies.index') }}" method="GET" id="filterForm">
                            <div class="jp-filter-divider"></div>
                            <h6>Industry</h6>
                            @if(!empty($industries) && count($industries) > 0)
                                @foreach($industries as $industry)
                                <div class="form-check form-check-custom">
                                    <input class="form-check-input" type="checkbox" name="industry_id[]" value="{{ is_array($industry) ? $industry['id'] : $industry->id }}" id="ind-{{ $loop->index }}" {{ in_array(is_array($industry) ? $industry['id'] : $industry->id, (array) request('industry_id', [])) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="ind-{{ $loop->index }}">{{ is_array($industry) ? $industry['name'] : $industry->name }}</label>
                                </div>
                                @endforeach
                            @else
                                <p class="text-muted fs-8">No industries available.</p>
                            @endif

                            <div class="jp-filter-divider"></div>
                            <h6>Location</h6>
                            @if(!empty($locations) && count($locations) > 0)
                                @foreach($locations as $location)
                                <div class="form-check form-check-custom">
                                    <input class="form-check-input" type="checkbox" name="location_id[]" value="{{ is_array($location) ? $location['id'] : $location->id }}" id="loc-{{ $loop->index }}" {{ in_array(is_array($location) ? $location['id'] : $location->id, (array) request('location_id', [])) ? 'checked' : '' }} />
                                    <label class="form-check-label" for="loc-{{ $loop->index }}">{{ is_array($location) ? $location['district'] : $location->district }}</label>
                                </div>
                                @endforeach
                            @else
                                <p class="text-muted fs-8">No locations available.</p>
                            @endif

                            <div class="jp-filter-divider"></div>
                            <h6>Company Status</h6>
                            <div class="form-check form-check-custom">
                                <input class="form-check-input" type="checkbox" name="is_verified" value="1" id="verified" {{ request('is_verified') ? 'checked' : '' }} />
                                <label class="form-check-label" for="verified">Verified Employers</label>
                            </div>
                            <div class="form-check form-check-custom">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featured" {{ request('is_featured') ? 'checked' : '' }} />
                                <label class="form-check-label" for="featured">Featured Companies</label>
                            </div>
                            <div class="form-check form-check-custom">
                                <input class="form-check-input" type="checkbox" name="is_gold" value="1" id="gold" {{ request('is_gold') ? 'checked' : '' }} />
                                <label class="form-check-label" for="gold">Gold Employers</label>
                            </div>

                            <!-- Preserve search and sort parameters -->
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

            <!-- ====================== COMPANY LISTINGS ====================== -->
            <div class="col-lg-9">

                <!-- Regular listing header -->
                <div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
                    <div class="fw-semibold text-gray-700">Showing <span class="fw-bold text-gray-900">{{ count($companyList) }}</span> of {{ number_format($totalCompanies ?? 0) }} companies</div>
                    <select class="form-select form-select-sm jp-sort-select w-auto" onchange="window.location.href = updateQueryParam('sort', this.value)">
                        <option value="name" {{ request('sort', 'name') === 'name' ? 'selected' : '' }}>Name A-Z</option>
                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                        <option value="jobs_count" {{ request('sort') === 'jobs_count' ? 'selected' : '' }}>Most Jobs</option>
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                    </select>
                </div>

                <!-- Company Grid -->
                <div class="row g-4 jp-company-grid">
                    @forelse($companyList as $company)
                        @php
                            $companyName = $company['name'] ?? 'Company Name';
                            $companySlug = $company['slug'] ?? ($company['id'] ?? 1);
                            $companyLogo = $company['logo_url'] ?? null;
                            $description = $company['description'] ?? '';
                            $industry = $company['industry']['name'] ?? null;
                            $location = $company['location']['district'] ?? $company['location']['name'] ?? null;
                            $jobCount = $company['jobs_count'] ?? 0;
                            $isVerified = $company['is_verified'] ?? false;
                            $isFeatured = $company['is_featured'] ?? false;
                            $isGold = $company['is_gold'] ?? false;
                            $website = $company['website'] ?? null;
                        @endphp
                        <div class="col-md-6 jp-company-col">
                            <div class="jp-company-card">
                                <div class="d-flex gap-4">
                                    <div class="company-logo flex-shrink-0">
                                        @if($companyLogo)
                                            <img src="{{ $companyLogo }}" alt="{{ $companyName }}">
                                        @else
                                            {{ $getInitials($company) }}
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                                            <div>
                                                <a href="{{ route('companies.show', $companySlug) }}" class="company-name text-decoration-none d-block">
                                                    {{ $companyName }}
                                                </a>
                                                <div class="d-flex flex-wrap gap-1 mt-1">
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
                                            <div class="job-count text-end">
                                                <span class="fs-5">{{ $jobCount }}</span>
                                                <span class="fs-8 text-muted d-block">jobs</span>
                                            </div>
                                        </div>

                                        @if($description)
                                            <div class="company-description mt-2">{!! $truncateDescription($description, 120) !!}</div>
                                        @endif

                                        <div class="d-flex flex-wrap gap-3 mt-3 company-meta fs-8 text-muted">
                                            @if($industry)
                                                <span><i class="ki-duotone ki-building-factory fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $industry }}</span>
                                            @endif
                                            @if($location)
                                                <span><i class="ki-duotone ki-geolocation fs-6"><span class="path1"></span><span class="path2"></span></i>{{ $location }}</span>
                                            @endif
                                            @if($website)
                                                <span><i class="ki-duotone ki-link fs-6"><span class="path1"></span><span class="path2"></span></i><a href="{{ $website }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-muted">{{ $website }}</a></span>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center justify-content-end mt-3">
                                            <a href="{{ route('companies.show', $companySlug) }}" class="btn btn-sm btn-outline btn-outline-primary">View Company</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="jp-empty-card text-center py-10">
                                <i class="ki-duotone ki-file-deleted fs-3x text-muted d-block mb-3"><span class="path1"></span><span class="path2"></span></i>
                                <p class="fw-semibold fs-5 mb-1">No companies found</p>
                                <p class="text-muted">Try broadening your search or clearing some filters.</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if(!empty($companies['pagination']) && ($companies['pagination']['last_page'] ?? 1) > 1)
                @php
                    $current = (int) ($companies['pagination']['current_page'] ?? 1);
                    $last = (int) ($companies['pagination']['last_page'] ?? 1);
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
    <!-- CTA Banner -->
    <div class="mt-10 mb-n20 position-relative z-index-2">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-12">  
                    <div class="d-flex flex-stack flex-wrap flex-md-nowrap card-rounded shadow p-8 p-lg-12" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
                        <div class="my-2 me-5">
                            <div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Are you an employer?</div>
                            <div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Post your jobs and connect with top talent in {{ country_name() }}.</div>
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-3 flex-shrink-0 my-2">
                            <a href="{{ route('register') }}?as=employer" class="btn btn-lg btn-light fw-bold">Post a Job</a>
                            <a href="{{ route('companies.index') }}" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">Browse Companies</a>
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
        // Auto-submit on checkbox change (optional - uncomment to enable)
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