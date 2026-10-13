@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

@if($needsOnboarding ?? false)
    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- ONBOARDING NUDGE                                       --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="card card-flush py-15">
        <div class="card-body text-center">
            <i class="ki-duotone ki-rocket fs-3x text-primary mb-4 d-block">
                <span class="path1"></span><span class="path2"></span>
            </i>
            <h2 class="fw-bold mb-2">Welcome, {{ $user['first_name'] ?? 'there' }}!</h2>
            <p class="text-muted mb-6">Let's get your account set up.</p>

            @if($role === 'employer')
                <a href="{{ route('employer.profile.index') }}" class="btn btn-primary btn-lg">
                    Complete Your Company Profile
                </a>
            @else
                <a href="{{ route('cv.edit') }}" class="btn btn-primary btn-lg">
                    Upload Your CV
                </a>
            @endif
        </div>
    </div>

@else

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- WELCOME CARD                                           --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="card card-flush p-6 mb-6">
        <div class="d-flex align-items-center flex-wrap gap-4">
            <div class="symbol symbol-60px">
                <img src="{{ $user['avatar'] ?? asset('assets/media/avatars/300-1.jpg') }}" alt="Profile" />
            </div>
            <div class="flex-grow-1">
                <h1 class="fw-bold fs-2x mb-2">
                    Welcome back, {{ $user['first_name'] ?? 'User' }}!
                </h1>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="badge badge-light-{{ $role === 'employer' ? 'primary' : 'success' }} fs-6 py-2 px-4">
                        <i class="ki-duotone ki-{{ $role === 'employer' ? 'briefcase' : 'profile-circle' }} fs-4 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        {{ ucfirst($role) }}
                    </span>
                    <span class="text-muted">{{ $user['email'] ?? '' }}</span>

                    @if($role === 'employer' && !empty($stats['tier_label']))
                        <span class="badge badge-light-{{ ($stats['tier'] ?? 'starter') === 'trusted' ? 'success' : (($stats['tier'] ?? 'starter') === 'verified' ? 'primary' : 'warning') }} fs-7">
                            {{ $stats['tier_label'] }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- SUBSCRIPTION WIDGET                                    --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @php
        $sub       = $subscription ?? null;
        $pricing   = $pricingUrl ?? route('pricing.index');
    @endphp

    @if(!empty($sub))
        @php
            $subStatus      = $sub['status'] ?? 'active';
            $subIsActive    = $sub['is_active'] ?? false;
            $subCancelAtEnd = !empty($sub['cancel_at_period_end']);
            $subTone = match (true) {
                $subStatus === 'past_due'      => 'danger',
                $subStatus === 'trialing'      => 'info',
                $subCancelAtEnd                => 'warning',
                $subIsActive                   => 'success',
                default                        => 'secondary',
            };
            $subIcon = match (true) {
                $subStatus === 'past_due'      => 'ki-information-5',
                $subStatus === 'trialing'      => 'ki-time',
                $subCancelAtEnd                => 'ki-information-5',
                $subIsActive                   => 'ki-check-circle',
                default                        => 'ki-information-5',
            };
            $recentPayments = $sub['recent_payments'] ?? [];
        @endphp

        <div class="card card-flush bg-light-{{ $subTone }} mb-6">
            <div class="card-body py-6">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                    <div class="d-flex align-items-center gap-4">
                        <div class="symbol symbol-50px">
                            <span class="symbol-label bg-white">
                                <i class="ki-duotone {{ $subIcon }} fs-2x text-{{ $subTone }}">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                            </span>
                        </div>
                        <div>
                            <div class="fw-bold fs-5 text-gray-900">
                                {{ $sub['service_name'] ?? 'Subscription' }}
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3 mt-1">
                                <span class="badge badge-light-{{ $subTone }}">
                                    {{ $sub['status_label'] ?? ucfirst($subStatus) }}
                                </span>
                                <span class="text-muted fs-7">
                                    {{ $sub['amount_formatted'] ?? '' }}
                                    @if(($sub['interval'] ?? null) && $sub['interval'] !== 'one_time')
                                        / {{ $sub['interval'] }}
                                    @endif
                                </span>
                                @if(!empty($sub['current_period_end']))
                                    <span class="text-muted fs-7">
                                        <i class="ki-duotone ki-calendar fs-6 me-1">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        Renews {{ \Carbon\Carbon::parse($sub['current_period_end'])->format('M d, Y') }}
                                        @if(isset($sub['days_remaining']) && $sub['days_remaining'] !== null)
                                            ({{ $sub['days_remaining'] }} days left)
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ $pricing }}" class="btn btn-sm btn-light-{{ $subTone }}">
                            <i class="ki-duotone ki-dollar fs-4 me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Manage Plan
                        </a>
                        <button type="button" class="btn btn-sm btn-light"
                                data-bs-toggle="modal" data-bs-target="#kt_payments_modal">
                            <i class="ki-duotone ki-file-down fs-4 me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Payments ({{ count($recentPayments) }})
                        </button>
                    </div>
                </div>

                @if($subStatus === 'past_due')
                    <div class="alert alert-light-danger d-flex align-items-center mt-4 mb-0">
                        <i class="ki-duotone ki-information-5 fs-2x me-3">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Your last payment did not go through.</div>
                            <div class="text-muted fs-7">Update your payment method or choose a plan again to keep your features active.</div>
                        </div>
                        <a href="{{ $pricing }}" class="btn btn-sm btn-danger">Pay Now</a>
                    </div>
                @endif

                @if($subStatus === 'trialing' && !empty($sub['trial_ends_at']))
                    <div class="alert alert-light-info d-flex align-items-center mt-4 mb-0">
                        <i class="ki-duotone ki-time fs-2x me-3">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div>
                            <div class="fw-bold">Trial ends {{ \Carbon\Carbon::parse($sub['trial_ends_at'])->format('M d, Y') }}.</div>
                            <div class="text-muted fs-7">Choose a plan before then to keep your access.</div>
                        </div>
                    </div>
                @endif

                @if($subCancelAtEnd)
                    <div class="alert alert-light-warning d-flex align-items-center mt-4 mb-0">
                        <i class="ki-duotone ki-information-5 fs-2x me-3">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div>
                            <div class="fw-bold">Your subscription will end soon.</div>
                            <div class="text-muted fs-7">
                                Access continues until
                                {{ !empty($sub['current_period_end']) ? \Carbon\Carbon::parse($sub['current_period_end'])->format('M d, Y') : 'end of period' }}.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Payments modal --}}
        <div class="modal fade" id="kt_payments_modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="fw-bold mb-0">Payment History</h3>
                        <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if(empty($recentPayments))
                            <div class="text-center py-8 text-muted">
                                <i class="ki-duotone ki-information-5 fs-3x text-muted mb-3 d-block">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                                <p class="mb-0">No payment records yet.</p>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach($recentPayments as $p)
                                    @php
                                        $pColor = match($p['status'] ?? '') {
                                            'succeeded' => 'success',
                                            'pending'   => 'info',
                                            'failed'    => 'danger',
                                            'refunded'  => 'warning',
                                            default     => 'secondary',
                                        };
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <div>
                                            <div class="fw-bold">{{ $p['amount_formatted'] ?? '' }}</div>
                                            <div class="text-muted fs-8">
                                                @if(!empty($p['paid_at']))
                                                    Paid {{ \Carbon\Carbon::parse($p['paid_at'])->format('M d, Y H:i') }}
                                                @elseif(!empty($p['created_at']))
                                                    Attempted {{ \Carbon\Carbon::parse($p['created_at'])->format('M d, Y H:i') }}
                                                @endif
                                            </div>
                                            @if(!empty($p['failure_reason']))
                                                <div class="text-danger fs-8">{{ $p['failure_reason'] }}</div>
                                            @endif
                                        </div>
                                        <span class="badge badge-light-{{ $pColor }}">
                                            {{ $p['status_label'] ?? ucfirst($p['status'] ?? '') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <a href="{{ $pricing }}" class="btn btn-primary btn-sm">View Plans</a>
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- No active subscription --}}
        <div class="card card-flush bg-light-primary mb-6">
            <div class="card-body py-6">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                    <div class="d-flex align-items-center gap-4">
                        <div class="symbol symbol-50px">
                            <span class="symbol-label bg-white">
                                <i class="ki-duotone ki-rocket fs-2x text-primary">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div>
                            <div class="fw-bold fs-5 text-gray-900">Unlock more with a plan</div>
                            <div class="text-muted fs-7">
                                @if($role === 'employer')
                                    Get AI CV shortlisting, more job slots, and priority placement.
                                @else
                                    Get instant job alerts, a featured profile, and let our AI apply to jobs for you.
                                @endif
                            </div>
                        </div>
                    </div>
                    <a href="{{ $pricing }}" class="btn btn-primary">
                        <i class="ki-duotone ki-dollar fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        See Pricing
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- ROLE-SPECIFIC STATS                                    --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @if($role === 'employer')
        {{-- Employer: first row --}}
        <div class="row g-5 g-xl-8 mb-6">
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
                            <span class="fw-semibold text-gray-600 fs-7">Active Jobs</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['active_jobs'] ?? 0 }}</span>
                            @if(($stats['job_limit'] ?? null) !== null)
                                <span class="text-muted fs-8">{{ $stats['job_slots_remaining'] ?? 0 }} of {{ $stats['job_limit'] }} slots left</span>
                            @else
                                <span class="text-muted fs-8">Unlimited slots</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-user-tick fs-2x text-success">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Applications This Month</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['applications_this_month'] ?? 0 }}</span>
                            @if(!empty($stats['applications_today']))
                                <span class="text-success fs-8">+{{ $stats['applications_today'] }} today</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-info">
                                <i class="ki-duotone ki-abstract-26 fs-2x text-info">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">AI Shortlisted</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['ai_shortlisted'] ?? 0 }}</span>
                            <span class="text-muted fs-8">
                                {{ $stats['ai_screened'] ?? 0 }} screened · {{ $stats['new_applications'] ?? 0 }} new
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-warning">
                                <i class="ki-duotone ki-rocket fs-2x text-warning">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">CV Runs</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['screening_runs'] ?? 0 }}</span>
                            <span class="text-muted fs-8">
                                {{ number_format($stats['cvs_screened'] ?? 0) }} CVs screened
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Employer: second row --}}
        <div class="row g-5 g-xl-8 mb-6">
            <div class="col-xl-4 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-danger">
                                <i class="ki-duotone ki-flash fs-2x text-danger">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Auto-Apply Relays</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['auto_apply_relays'] ?? 0 }}</span>
                            <span class="text-muted fs-8">seekers notified to apply directly</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-duotone ki-star fs-2x text-primary">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Shortlisted / Interview</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['shortlisted'] ?? 0 }}</span>
                            <span class="text-muted fs-8">candidates in your pipeline</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-{{ !empty($stats['is_verified']) ? 'success' : 'warning' }}">
                                <i class="ki-duotone ki-shield-tick fs-2x text-{{ !empty($stats['is_verified']) ? 'success' : 'warning' }}">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Profile</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['profile_completion'] ?? 0 }}%</span>
                            <span class="text-muted fs-8">
                                {{ $stats['tier_label'] ?? 'Starter tier' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compliance banner --}}
        @if(($stats['compliance_status'] ?? 'incomplete') !== 'verified')
            <div class="alert alert-light-{{ ($stats['compliance_status'] ?? 'incomplete') === 'rejected' ? 'danger' : (($stats['compliance_status'] ?? 'incomplete') === 'submitted' ? 'info' : 'warning') }} d-flex align-items-center mb-6">
                <i class="ki-duotone ki-shield-tick fs-2x me-4">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <div class="flex-grow-1">
                    <div class="fw-bold">
                        @if(($stats['compliance_status'] ?? 'incomplete') === 'submitted')
                            Compliance under review
                        @elseif(($stats['compliance_status'] ?? 'incomplete') === 'rejected')
                            Compliance rejected — needs attention
                        @else
                            Complete your compliance documents
                        @endif
                    </div>
                    <div class="text-muted fs-7">
                        @if(($stats['compliance_status'] ?? 'incomplete') === 'submitted')
                            Our team is reviewing your documents. We'll notify you shortly.
                        @else
                            Upload your compliance documents to unlock the Verified badge and more job slots.
                        @endif
                    </div>
                </div>
                <a href="{{ route('employer.compliance.index') }}" class="btn btn-sm btn-light-primary">
                    Go to Compliance
                </a>
            </div>
        @endif

    @else
        {{-- Seeker: first row --}}
        <div class="row g-5 g-xl-8 mb-6">
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
                            <span class="fw-semibold text-gray-600 fs-7">Applications</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['applied_count'] ?? 0 }}</span>
                            @if(!empty($stats['auto_applied_count']))
                                <span class="text-info fs-8">
                                    <i class="ki-duotone ki-rocket fs-7 me-1">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    {{ $stats['auto_applied_count'] }} auto-applied
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-info">
                                <i class="ki-duotone ki-abstract-26 fs-2x text-info">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Avg. AI Match</span>
                            <span class="fw-bold fs-2x text-gray-900">
                                {{ ($stats['avg_ai_score'] ?? null) !== null ? $stats['avg_ai_score'] . '%' : '—' }}
                            </span>
                            @if(!empty($stats['ai_screened_count']))
                                <span class="text-muted fs-8">
                                    {{ $stats['ai_screened_count'] }} of your applications screened
                                </span>
                            @else
                                <span class="text-muted fs-8">Not yet screened</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-warning">
                                <i class="ki-duotone ki-calendar-tick fs-2x text-warning">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Interviews</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['interview_count'] ?? 0 }}</span>
                            @if(!empty($stats['strong_yes_count']))
                                <span class="text-success fs-8">
                                    {{ $stats['strong_yes_count'] }} strong matches
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100 {{ !empty($stats['relay_pending_count']) ? 'bg-light-warning' : '' }}">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-{{ !empty($stats['relay_pending_count']) ? 'warning' : 'secondary' }}">
                                <i class="ki-duotone ki-flash fs-2x text-{{ !empty($stats['relay_pending_count']) ? 'warning' : 'muted' }}">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Action Needed</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['relay_pending_count'] ?? 0 }}</span>
                            <span class="text-muted fs-8">
                                {{ !empty($stats['relay_pending_count']) ? 'jobs to apply for today' : 'nothing pending' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seeker: second row --}}
        <div class="row g-5 g-xl-8 mb-6">
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
                            <span class="fw-semibold text-gray-600 fs-7">Hired</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['hired_count'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-duotone ki-bookmark fs-2x text-primary">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Saved Jobs</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['saved_count'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-info">
                                <i class="ki-duotone ki-file fs-2x text-info">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">CVs Uploaded</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['cv_count'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-{{ !empty($stats['is_profile_complete']) ? 'success' : 'warning' }}">
                                <i class="ki-duotone ki-{{ !empty($stats['is_profile_complete']) ? 'check-circle' : 'information-5' }} fs-2x text-{{ !empty($stats['is_profile_complete']) ? 'success' : 'warning' }}">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Profile</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['profile_completion'] ?? 0 }}%</span>
                            <span class="text-muted fs-8">
                                {{ !empty($stats['is_profile_complete']) ? 'Complete' : 'Needs work' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- RECENT ACTIVITY + QUICK ACTIONS                        --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="row g-6">
        <div class="col-xl-7">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                </div>
                <div class="card-body pt-0">
                    @if(empty($recentActivity))
                        <div class="text-center py-8 text-muted">
                            <i class="ki-duotone ki-time fs-3x text-muted mb-3 d-block">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            <p class="text-muted">
                                {{ $role === 'employer' ? 'No activity yet — post a job to get started.' : 'No activity yet — apply to jobs to see updates here.' }}
                            </p>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($recentActivity as $activity)
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                                    <div class="symbol symbol-40px">
                                        <span class="symbol-label bg-light-{{ $activity['color'] }}">
                                            <i class="ki-duotone {{ $activity['icon'] }} fs-4 text-{{ $activity['color'] }}">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold fs-7">{{ $activity['title'] }}</div>
                                        <div class="text-muted fs-7">{{ $activity['subtitle'] }}</div>
                                    </div>
                                    <span class="text-muted fs-8 text-nowrap">
                                        {{ \Carbon\Carbon::parse($activity['at'])->diffForHumans() }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3">
                        @if($role === 'employer')
                            <div class="col-6">
                                <a href="{{ route('employer.jobs.create') }}" class="btn btn-light-primary w-100 py-4">
                                    <i class="ki-duotone ki-plus-square fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                    </i>
                                    <span class="fw-bold">Post a Job</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('employer.jobs.index') }}" class="btn btn-light-success w-100 py-4">
                                    <i class="ki-duotone ki-briefcase fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">My Jobs</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('employer.analytics.index') }}" class="btn btn-light-warning w-100 py-4">
                                    <i class="ki-duotone ki-chart-simple fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                    </i>
                                    <span class="fw-bold">Analytics</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('employer.profile.index') }}" class="btn btn-light-info w-100 py-4">
                                    <i class="ki-duotone ki-building fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">Company Profile</span>
                                </a>
                            </div>
                        @else
                            <div class="col-6">
                                <a href="{{ route('jobs.index') }}" class="btn btn-light-primary w-100 py-4">
                                    <i class="ki-duotone ki-rocket fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">Find Jobs</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('cv.edit') }}" class="btn btn-light-success w-100 py-4">
                                    <i class="ki-duotone ki-file-up fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">Upload CV</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('jobs.applied') }}" class="btn btn-light-warning w-100 py-4">
                                    <i class="ki-duotone ki-briefcase fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">My Applications</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('jobs.saved') }}" class="btn btn-light-info w-100 py-4">
                                    <i class="ki-duotone ki-bookmark fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">Saved Jobs</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection