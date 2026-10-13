@extends('layouts.admin')

@section('title', 'Subscription & Payments')
@section('page_title', 'Subscription & Payments')

@section('content')
<div class="container py-6">

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- SUMMARY STRIP                                          --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-6">
        <div class="col-md-3">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Total Paid</div>
                    <div class="fs-2 fw-bold text-gray-900">
                        {{ strtoupper($active['currency'] ?? 'UGX') }}
                        {{ number_format(($summary['total_paid_cents'] ?? 0) / 100, ($active['currency'] ?? 'UGX') === 'UGX' ? 0 : 2) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Payments Made</div>
                    <div class="fs-2 fw-bold text-gray-900">{{ $summary['total_payments'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Subscriptions</div>
                    <div class="fs-2 fw-bold text-gray-900">{{ $summary['subscription_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Active Since</div>
                    <div class="fs-3 fw-bold text-gray-900">
                        {{ !empty($summary['active_since']) ? \Carbon\Carbon::parse($summary['active_since'])->format('M d, Y') : '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- CURRENT PLAN                                           --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @if(!empty($active))
        @php
            $status = $active['status'] ?? 'active';
            $tone = match (true) {
                $status === 'past_due'                 => 'danger',
                $status === 'trialing'                 => 'info',
                !empty($active['cancel_at_period_end']) => 'warning',
                $active['is_active']                   => 'success',
                default                                => 'secondary',
            };
        @endphp

        <div class="card card-flush bg-light-{{ $tone }} mb-6">
            <div class="card-body py-6">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                    <div>
                        <div class="text-muted fs-8 text-uppercase">Current Plan</div>
                        <div class="fs-3 fw-bold text-gray-900 mt-1">{{ $active['service_name'] }}</div>
                        <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
                            <span class="badge badge-light-{{ $tone }}">{{ $active['status_label'] }}</span>
                            <span class="text-muted fs-7">{{ $active['amount_formatted'] }} / {{ $active['interval'] }}</span>
                            @if(!empty($active['current_period_end']))
                                <span class="text-muted fs-7">
                                    Renews {{ \Carbon\Carbon::parse($active['current_period_end'])->format('M d, Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ $pricingUrl }}" class="btn btn-primary">Change Plan</a>
                </div>
            </div>
        </div>
    @else
        <div class="card card-flush bg-light-primary mb-6">
            <div class="card-body py-6 d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div>
                    <div class="fs-3 fw-bold text-gray-900">You are not subscribed to any plan</div>
                    <div class="text-muted fs-7 mt-1">Choose a plan to unlock premium features.</div>
                </div>
                <a href="{{ $pricingUrl }}" class="btn btn-primary">See Pricing</a>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- PAYMENT HISTORY                                        --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="card card-flush mb-6">
        <div class="card-header align-items-center py-5">
            <h3 class="card-title fw-bold mb-0">Payment History</h3>
            <div class="card-toolbar">
                <span class="badge badge-light-secondary">{{ count($payments) }} records</span>
            </div>
        </div>

        <div class="card-body pt-0">
            @if(empty($payments))
                <div class="text-center py-15">
                    <i class="ki-duotone ki-information-5 fs-5tx text-muted mb-4 d-block">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <h4 class="fw-bold text-gray-800 mb-2">No payments yet</h4>
                    <p class="text-muted">Your payment records will appear here once you subscribe.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 fs-7 gy-3">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="text-start min-w-180px">Plan</th>
                                <th class="text-start min-w-140px">Amount</th>
                                <th class="text-start min-w-130px">Status</th>
                                <th class="text-start min-w-180px">Reference</th>
                                <th class="text-start min-w-160px">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $p)
                                @php
                                    $pColor = match($p['status'] ?? '') {
                                        'succeeded' => 'success',
                                        'pending'   => 'info',
                                        'failed'    => 'danger',
                                        'refunded'  => 'warning',
                                        default     => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-start">
                                        <div class="fw-bold text-gray-800">{{ $p['service_name'] ?? '—' }}</div>
                                        <div class="text-muted fs-8">{{ $p['service_key'] ?? '' }}</div>
                                    </td>
                                    <td class="text-start fw-bold">{{ $p['amount_formatted'] }}</td>
                                    <td class="text-start">
                                        <span class="badge badge-light-{{ $pColor }}">{{ $p['status_label'] }}</span>
                                        @if(!empty($p['failure_reason']))
                                            <div class="text-danger fs-8 mt-1">{{ $p['failure_reason'] }}</div>
                                        @endif
                                    </td>
                                    <td class="text-start text-muted fs-8">
                                        <div>{{ $p['gateway'] ?? '—' }}</div>
                                        <div>{{ $p['reference'] ?? '—' }}</div>
                                    </td>
                                    <td class="text-start text-muted fs-8">
                                        @if(!empty($p['paid_at']))
                                            Paid {{ \Carbon\Carbon::parse($p['paid_at'])->format('M d, Y H:i') }}
                                        @elseif(!empty($p['created_at']))
                                            Attempted {{ \Carbon\Carbon::parse($p['created_at'])->format('M d, Y H:i') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- SUBSCRIPTION HISTORY                                   --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="card card-flush">
        <div class="card-header align-items-center py-5">
            <h3 class="card-title fw-bold mb-0">Subscription History</h3>
            <div class="card-toolbar">
                <span class="badge badge-light-secondary">{{ count($subscriptions) }} records</span>
            </div>
        </div>

        <div class="card-body pt-0">
            @if(empty($subscriptions))
                <div class="text-center py-15">
                    <i class="ki-duotone ki-information-5 fs-5tx text-muted mb-4 d-block">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <h4 class="fw-bold text-gray-800 mb-2">No subscriptions yet</h4>
                    <p class="text-muted">Once you subscribe to a plan, your history will appear here.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 fs-7 gy-3">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="text-start min-w-180px">Plan</th>
                                <th class="text-start min-w-140px">Amount</th>
                                <th class="text-start min-w-150px">Status</th>
                                <th class="text-start min-w-140px">Started</th>
                                <th class="text-start min-w-140px">Period Ends</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscriptions as $s)
                                @php
                                    $sTone = match (true) {
                                        ($s['status'] ?? '') === 'past_due' => 'danger',
                                        ($s['status'] ?? '') === 'trialing' => 'info',
                                        !empty($s['cancel_at_period_end'])  => 'warning',
                                        ($s['is_active'] ?? false)          => 'success',
                                        default                             => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-start">
                                        <div class="fw-bold text-gray-800">{{ $s['service_name'] }}</div>
                                        <div class="text-muted fs-8">{{ $s['service_key'] }}</div>
                                    </td>
                                    <td class="text-start fw-bold">
                                        {{ $s['amount_formatted'] }}
                                        <div class="text-muted fs-8 fw-normal">/ {{ $s['interval'] }}</div>
                                    </td>
                                    <td class="text-start">
                                        <span class="badge badge-light-{{ $sTone }}">{{ $s['status_label'] }}</span>
                                    </td>
                                    <td class="text-start text-muted fs-8">
                                        {{ !empty($s['starts_at']) ? \Carbon\Carbon::parse($s['starts_at'])->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="text-start text-muted fs-8">
                                        {{ !empty($s['current_period_end']) ? \Carbon\Carbon::parse($s['current_period_end'])->format('M d, Y') : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection