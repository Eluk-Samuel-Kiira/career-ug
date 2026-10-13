@extends('layouts.admin')

@section('title', 'Auto Apply Agent')
@section('page_title', 'Auto Apply Agent')

@section('content')
<div class="container py-6">

    {{-- Header --}}
    <div class="card card-flush mb-6 bg-light-primary">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="symbol symbol-60px">
                    <div class="symbol-label bg-white">
                        <i class="ki-duotone ki-abstract-26 fs-2x text-primary">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div>
                    <h2 class="fw-bold mb-1">Auto Apply Agent</h2>
                    <div class="text-muted fs-7">
                        Every day, we find jobs that match your preferences, tailor your CV and cover letter,
                        and apply on your behalf.
                    </div>
                </div>
            </div>
            @if(!empty($stats))
                <div class="d-flex gap-4 text-end">
                    <div>
                        <div class="fs-2 fw-bold text-primary">{{ $stats['total_applied'] ?? 0 }}</div>
                        <div class="text-muted fs-8">Total applied</div>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-warning">{{ $stats['total_relayed'] ?? 0 }}</div>
                        <div class="text-muted fs-8">Sent to you</div>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-success">{{ $stats['this_month'] ?? 0 }}</div>
                        <div class="text-muted fs-8">This month</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(!$feature_enabled)
        {{-- Locked state --}}
        <div class="card card-flush">
            <div class="card-body py-15 text-center">
                <i class="ki-duotone ki-lock-2 fs-5tx text-warning mb-5 d-block">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
                <h3 class="fw-bold text-gray-800 mb-3">Auto Apply is not active on your plan</h3>
                <p class="text-muted fs-6 mb-6" style="max-width:560px; margin:0 auto 24px;">
                    Upgrade your subscription to let the Auto Apply Agent apply to matching jobs on your behalf,
                    with a CV and cover letter tailored for each role.
                </p>
                <a href="{{ route('pricing.index') }}" class="btn btn-primary">View Plans</a>
            </div>
        </div>
    @elseif(empty($runs))
        {{-- Nothing yet --}}
        <div class="card card-flush">
            <div class="card-body py-15 text-center">
                <i class="ki-duotone ki-rocket fs-5tx text-muted mb-5 d-block">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <h3 class="fw-bold text-gray-800 mb-3">Your agent is warming up</h3>
                <p class="text-muted fs-6" style="max-width:560px; margin:0 auto;">
                    Make sure your CV is uploaded and your job preferences are set.
                    Your first auto-apply run will happen overnight.
                </p>
                <div class="d-flex justify-content-center gap-3 mt-6">
                    <a href="{{ route('cv.edit') }}" class="btn btn-light-primary">Manage CV</a>
                    <a href="{{ route('alert.preferences') }}" class="btn btn-light-primary">Job Preferences</a>
                </div>
            </div>
        </div>
    @else
        {{-- Runs --}}
        <div class="d-flex flex-column gap-5">
            @foreach($runs as $run)
                <div class="card card-flush">
                    <div class="card-header align-items-center py-5 border-0">
                        <div class="d-flex align-items-center gap-4">
                            <div class="d-flex flex-column text-center" style="min-width:70px;">
                                <div class="fs-2 fw-bold text-gray-800">
                                    {{ \Carbon\Carbon::parse($run['run_date'])->format('d') }}
                                </div>
                                <div class="text-muted fs-8 text-uppercase">
                                    {{ \Carbon\Carbon::parse($run['run_date'])->format('M Y') }}
                                </div>
                            </div>
                            <div class="border-start border-2 border-gray-300 ps-4">
                                <div class="fw-bold text-gray-800">
                                    {{ \Carbon\Carbon::parse($run['run_date'])->format('l') }}
                                </div>
                                <div class="text-muted fs-7">
                                    @if($run['applied_count'] > 0)
                                        We applied to <strong>{{ $run['applied_count'] }}</strong>
                                        {{ \Illuminate\Support\Str::plural('job', $run['applied_count']) }} for you
                                    @else
                                        No automatic applications on this day
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-toolbar gap-2">
                            @if($run['applied_count'] > 0)
                                <span class="badge badge-light-success">
                                    {{ $run['applied_count'] }} applied
                                </span>
                            @endif
                            @if($run['relayed_count'] > 0)
                                <span class="badge badge-light-warning">
                                    {{ $run['relayed_count'] }} to apply
                                </span>
                            @endif
                            <span class="badge badge-light-secondary">{{ $run['status_label'] }}</span>
                        </div>
                    </div>

                    @if(!empty($run['relays']))
                        <div class="card-body pt-0">
                            <div class="separator my-4"></div>
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="ki-duotone ki-information-5 fs-3 text-warning">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                                <span class="fw-semibold text-gray-800">
                                    Apply to these yourself — we could not submit them automatically
                                </span>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                @foreach($run['relays'] as $relay)
                                    <div class="d-flex flex-wrap justify-content-between align-items-center p-4 bg-light-warning rounded">
                                        <div class="min-w-0">
                                            <div class="fw-bold text-gray-800">{{ $relay['job_title'] ?? 'Job' }}</div>
                                            <div class="text-muted fs-7">
                                                {{ $relay['company'] ?? 'Company' }}
                                                @if($relay['reason'] === 'academic_documents_required')
                                                    · requires academic documents
                                                @else
                                                    · apply on the employer's site
                                                @endif
                                            </div>
                                        </div>
                                        @if($relay['job_slug'])
                                            <a href="{{ url('/job/' . $relay['job_slug']) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-primary">
                                                Apply Now
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection