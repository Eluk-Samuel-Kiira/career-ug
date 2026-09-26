@extends('layouts.admin')

@section('title', 'My Job Posts')
@section('page_title', 'My Job Posts')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Employer</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">My Job Posts &amp; ATS</li>
@endsection

@section('content')
<div class="container py-6">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- STAT CARDS                                                   --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="row g-5 g-xl-8 mb-6">

        {{-- Total --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-light-primary">
                            <i class="ki-duotone ki-briefcase fs-2x text-primary">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-gray-600 fs-7">Total Submissions</span>
                        <span class="fw-bold fs-2x text-gray-900">{{ $counts['all'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Under Review --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-light-info">
                            <i class="ki-duotone ki-time fs-2x text-info">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-gray-600 fs-7">Under Review</span>
                        <span class="fw-bold fs-2x text-gray-900">{{ $counts['pending_review'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Payment --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-light-warning">
                            <i class="ki-duotone ki-dollar fs-2x text-warning">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-gray-600 fs-7">Pending Payment</span>
                        <span class="fw-bold fs-2x text-gray-900">{{ $counts['pending_payment'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Published --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-light-success">
                            <i class="ki-duotone ki-check-circle fs-2x text-success">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-gray-600 fs-7">Published</span>
                        <span class="fw-bold fs-2x text-gray-900">{{ $counts['published'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- MAIN CARD                                                    --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="card card-flush">

        {{-- Card header: title + Post button --}}
        <div class="card-header mt-6">
            <div class="card-title">
                <h3 class="fw-bold mb-0">Job Submissions</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus-square fs-2">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    Post a Job
                </a>
            </div>
        </div>

        {{-- Tabs with counts --}}
        <div class="card-body pt-0">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                @php
                    $tabs = [
                        ''                 => ['label' => 'All',              'color' => 'primary', 'count' => $counts['all'] ?? 0],
                        'pending_review'   => ['label' => 'Under Review',     'color' => 'info',    'count' => $counts['pending_review'] ?? 0],
                        'pending_payment'  => ['label' => 'Pending Payment',  'color' => 'warning', 'count' => $counts['pending_payment'] ?? 0],
                        'published'        => ['label' => 'Published',        'color' => 'success', 'count' => $counts['published'] ?? 0],
                        'rejected'         => ['label' => 'Rejected',         'color' => 'danger',  'count' => $counts['rejected'] ?? 0],
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                    <li class="nav-item">
                        <a class="nav-link {{ $status === $key ? 'active' : '' }}"
                           href="{{ route('employer.jobs.index', $key ? ['status' => $key] : []) }}">
                            {{ $tab['label'] }}
                            <span class="badge badge-light-{{ $tab['color'] }} ms-2">{{ $tab['count'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- List --}}
            @if(empty($submissions))
                <div class="text-center py-15">
                    <div class="symbol symbol-100px bg-light-secondary mx-auto mb-5">
                        <i class="ki-duotone ki-briefcase fs-3x text-muted">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </div>
                    <h4 class="fw-bold mb-2">No job posts{{ $status ? ' in this status' : ' yet' }}</h4>
                    <p class="text-muted mb-5">
                        @if($status)
                            Try a different filter, or post a new job.
                        @else
                            Post your first job to start receiving applications.
                        @endif
                    </p>
                    <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-rocket fs-3 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Post a Job
                    </a>
                </div>
            @else
                @foreach($submissions as $s)
                    @php
                        $meta        = $s['package_meta'] ?? [];
                        $badge       = $meta['badge'] ?? null;
                        $tagline     = $meta['tagline'] ?? null;
                        $isEnterprise = (bool) ($meta['enterprise_ats'] ?? false);

                        $accent = match($s['status']) {
                            'published'        => 'success',
                            'rejected'         => 'danger',
                            'pending_payment'  => 'warning',
                            'pending_review'   => 'info',
                            'cancelled'        => 'secondary',
                            default            => 'primary',
                        };
                    @endphp
                    <br>
                    <div class="d-flex align-items-stretch border border-{{ $accent }} border-1 border-dashed rounded">

                        {{-- Left accent bar --}}
                        <div class="bg-{{ $accent }} rounded-start" style="width:4px;"></div>

                        <div class="flex-grow-1 p-5">
                            {{-- Outer flex: no wrap, gap on both sides, right column pinned --}}
                            <div class="d-flex justify-content-between align-items-start gap-4">

                                {{-- LEFT: title + meta + preview --}}
                                <div class="flex-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                        <span class="fw-bold fs-4">{{ $s['job_title'] }}</span>

                                        @if($badge === 'popular')
                                            <span class="badge badge-light-warning">⭐ Popular</span>
                                        @elseif($badge === 'urgent')
                                            <span class="badge badge-light-danger">🔥 Urgent</span>
                                        @elseif($badge === 'enterprise')
                                            <span class="badge badge-light-dark">🏢 Enterprise</span>
                                        @endif

                                        {!! $s['status_badge'] !!}
                                    </div>

                                    <div class="d-flex align-items-center gap-3 text-muted fs-7 mb-3 flex-wrap">
                                        <span>
                                            <i class="ki-duotone ki-barcode fs-6 me-1">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            </i>
                                            #{{ substr($s['uuid'], 0, 8) }}
                                        </span>
                                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                                        <span>
                                            <i class="ki-duotone ki-calendar fs-6 me-1">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            {{ \Carbon\Carbon::parse($s['created_at'])->format('M d, Y') }}
                                        </span>
                                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                                        <span>
                                            <i class="ki-duotone ki-package fs-6 me-1">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            {{ $s['service_name'] }}
                                        </span>
                                    </div>

                                    <div class="text-gray-700 fs-7 mb-3">
                                        {{ $s['content_preview'] }}
                                    </div>

                                    @if($s['status'] === 'rejected' && !empty($s['rejection_reason']))
                                        <div class="alert alert-light-danger d-flex align-items-center py-3 px-4 mb-0">
                                            <i class="ki-duotone ki-information-5 fs-2x text-danger me-3">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            </i>
                                            <div>
                                                <div class="fw-bold fs-7 text-danger">Rejection reason</div>
                                                <div class="fs-7">{{ $s['rejection_reason'] }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- RIGHT: price + actions — pinned to the right --}}
                                <div class="d-flex flex-column align-items-end gap-3 flex-shrink-0 ms-auto" style="min-width:200px;">

                                    <div class="text-end">
                                        <div class="fs-2 fw-bold {{ $s['is_free'] ? 'text-success' : 'text-primary' }}">
                                            {{ $s['is_free'] ? 'FREE' : $s['formatted_amount'] }}
                                        </div>
                                        <div class="mt-1">{!! $s['payment_badge'] !!}</div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        @if($isEnterprise && $s['status'] === 'published' && !empty($s['job_post_slug']))
                                            <a href="{{ route('employer.ats.index', $s['job_post_slug']) }}"
                                            class="btn btn-sm btn-light-success"
                                            title="Manage applicants">
                                                <i class="ki-duotone ki-user-tick fs-4 me-1">
                                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                                </i>
                                                Applicants
                                            </a>
                                        @endif

                                        <a href="{{ route('employer.jobs.show', $s['uuid']) }}"
                                        class="btn btn-sm btn-light-primary">
                                            View Details
                                            <i class="ki-duotone ki-right fs-4 ms-1">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Pagination --}}
                @if(($meta['last_page'] ?? 1) > 1)
                    <div class="d-flex justify-content-between align-items-center mt-6">
                        <div class="text-muted fs-7">
                            Showing page <strong>{{ $meta['current_page'] }}</strong> of <strong>{{ $meta['last_page'] }}</strong>
                        </div>
                        <nav>
                            <ul class="pagination m-0">
                                <li class="page-item {{ $meta['current_page'] <= 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ route('employer.jobs.index', ['status' => $status, 'page' => $meta['current_page'] - 1]) }}">
                                        <i class="ki-duotone ki-left fs-4">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    </a>
                                </li>
                                @for($i = max(1, $meta['current_page'] - 2); $i <= min($meta['last_page'], $meta['current_page'] + 2); $i++)
                                    <li class="page-item {{ $i === $meta['current_page'] ? 'active' : '' }}">
                                        <a class="page-link" href="{{ route('employer.jobs.index', ['status' => $status, 'page' => $i]) }}">{{ $i }}</a>
                                    </li>
                                @endfor
                                <li class="page-item {{ $meta['current_page'] >= $meta['last_page'] ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ route('employer.jobs.index', ['status' => $status, 'page' => $meta['current_page'] + 1]) }}">
                                        <i class="ki-duotone ki-right fs-4">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection