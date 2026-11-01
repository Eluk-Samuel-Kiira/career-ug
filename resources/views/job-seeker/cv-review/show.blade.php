@extends('layouts.admin')

@section('title', 'CV Review Progress')
@section('page_title', 'CV Review Progress')

@section('content')
@php
    $r = $cvRequest;
    $status = $r['status'];
    $answers = $r['seeker_gap_answers'] ?? [];
    $missingFields = $r['ai_gap_review']['missing_fields'] ?? [];
    $revisionHistory = $r['revision_history'] ?? [];
    $lastRevision = !empty($revisionHistory) ? end($revisionHistory) : null;

    $timeline = [
        'submitted'          => ['Uploaded',         'Your CV has been received.'],
        'ai_reviewed'        => ['CV Reviewed',      'Free gap review is ready below.'],
        'awaiting_payment'   => ['Awaiting Payment', 'Complete payment to unlock the professional rewrite.'],
        'paid'               => ['Paid',             'Payment received. Assigning your reviewer.'],
        'in_progress'        => ['In Progress',      'A career expert is rewriting your CV.'],
        'delivered'          => ['Delivered',        'Your revised CV is ready.'],
        'revision_requested' => ['Revision',         'We are working on your revision.'],
        'completed'          => ['Completed',        'All done. 🎉'],
    ];
    $statusOrder = array_keys($timeline);
    $currentIdx = array_search($status, $statusOrder);
@endphp

<div class="container py-6">

    @php
        /**
        * Per-status reassurance copy. Tone: warm, professional, human.
        * Shown to the seeker so they know exactly what to expect next.
        */
        $reassurance = [
            'submitted' => [
                'icon'    => 'ki-time',
                'color'   => 'info',
                'title'   => "We've received your CV",
                'body'    => "Sit back and relax — our HR experts are taking a first look at your CV and will be in touch shortly. You'll see the full CV gap review right here on this page.",
                'eta'     => 'Usually within a few minutes',
            ],
            'ai_reviewed' => [
                'icon'    => 'ki-message-text-2',
                'color'   => 'info',
                'title'   => "Your free AI gap review is ready",
                'body'    => "Read through what the AI found and answer the gaps we've flagged. That's all we need from you to get our human experts started.",
                'eta'     => 'Takes about 2 minutes',
            ],
            'awaiting_payment' => [
                'icon'    => 'ki-dollar',
                'color'   => 'warning',
                'title'   => "Almost there — one step to go",
                'body'    => "Your answers are locked in. Complete the payment and our HR team will begin a careful, human rewrite of your CV with the help of AI — and a lot of expertise.",
                'eta'     => "Then we'll deliver within 12–24 hours",
            ],
            'paid' => [
                'icon'    => 'ki-check-circle',
                'color'   => 'primary',
                'title'   => "Thank you — payment received",
                'body'    => "Our team has been notified and is preparing to assign a senior HR expert to your CV. You'll get an update here as soon as work begins.",
                'eta'     => 'Assignment usually within 1 hour',
            ],
            'in_progress' => [
                'icon'    => 'ki-arrows-circle',
                'color'   => 'primary',
                'title'   => "Your CV is in expert hands",
                'body'    => "A senior HR expert is carefully rewriting your CV now. Every line is read, revised, and re-read for professionalism, clarity, and alignment with your target role.",
                'eta'     => "You'll be notified the moment it's ready",
            ],
            'delivered' => [
                'icon'    => 'ki-check-circle',
                'color'   => 'success',
                'title'   => "Your revised CV is ready 🎉",
                'body'    => "Please download it, read it carefully, and let us know if anything needs adjusting. We're happy to make revisions — that's what we're here for.",
                'eta'     => 'Revision window: 3 days',
            ],
            'revision_requested' => [
                'icon'    => 'ki-arrows-circle',
                'color'   => 'warning',
                'title'   => "We're on your revision",
                'body'    => "Thanks for the feedback — our HR expert is applying your requested changes right now. You'll get the updated version here shortly.",
                'eta'     => 'Usually within a few hours',
            ],
            'completed' => [
                'icon'    => 'ki-check-circle',
                'color'   => 'success',
                'title'   => "All done — thank you!",
                'body'    => "We hope your new CV serves you well on your career journey. If you ever want another review — for a new role, a new industry, or just a refresh — we're here.",
                'eta'     => null,
            ],
            'cancelled' => [
                'icon'    => 'ki-information-5',
                'color'   => 'danger',
                'title'   => "This request was cancelled",
                'body'    => "If this was a mistake, or you'd like to start again, you're welcome to submit a new CV for review any time.",
                'eta'     => null,
            ],
        ];

        $msg = $reassurance[$status] ?? $reassurance['submitted'];
    @endphp

    <div class="alert alert-light-{{ $msg['color'] }} d-flex align-items-start mb-6 border border-{{ $msg['color'] }}">
        <i class="ki-duotone {{ $msg['icon'] }} fs-3x text-{{ $msg['color'] }} me-4 mt-1">
            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
        </i>
        <div class="flex-grow-1">
            <div class="fs-5 fw-bold mb-2 text-{{ $msg['color'] }}">
                {{ $msg['title'] }}
            </div>
            <div class="text-gray-700 mb-2">
                {{ $msg['body'] }}
            </div>
            @if(!empty($msg['eta']))
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-white rounded">
                    <i class="ki-duotone ki-time fs-6 text-{{ $msg['color'] }}">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <span class="fw-semibold fs-7 text-{{ $msg['color'] }}">{{ $msg['eta'] }}</span>
                </div>
            @endif

            {{-- SLA countdown for statuses that have a live timer --}}
            @if(in_array($status, ['paid', 'in_progress', 'revision_requested'], true) && !empty($r['sla_due_at']))
                @php
                    $due = \Carbon\Carbon::parse($r['sla_due_at']);
                    $now = now();
                    $isOverdue = $due->isPast();
                @endphp
                <div class="mt-3 pt-3 border-top">
                    @if($isOverdue)
                        <div class="d-flex align-items-center gap-2">
                            <i class="ki-duotone ki-information-5 fs-4 text-danger">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <span class="text-muted fs-7">
                                We're taking a little longer than usual — our team has been alerted
                                and your CV is a top priority. Thank you for your patience.
                            </span>
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-2">
                            <i class="ki-duotone ki-time fs-4 text-{{ $msg['color'] }}">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            <span class="text-muted fs-7">
                                Expected delivery: <strong>{{ $due->format('M d, Y \a\t H:i') }}</strong>
                                ({{ $due->diffForHumans() }})
                            </span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Timeline — horizontal on md+, vertical on mobile --}}
    <div class="card card-flush mb-6">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-duotone ki-time fs-2 me-2 text-gray-600">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                Progress
            </h3>
            <div class="card-toolbar">
                <span class="badge badge-light-{{ $msg['color'] ?? 'secondary' }} fs-7">
                    {{ $timeline[$status][0] ?? ucfirst($status) }}
                </span>
            </div>
        </div>
        <div class="card-body py-6">

            {{-- ─────────── MOBILE: vertical timeline ─────────── --}}
            <div class="d-md-none">
                <div class="timeline timeline-border-dashed">
                    @foreach($timeline as $key => $step)
                        @php
                            $idx     = array_search($key, $statusOrder);
                            $done    = $idx < $currentIdx || $status === 'completed';
                            $active  = $idx === $currentIdx;
                            $pending = !$done && !$active;

                            $iconBg   = $done ? 'bg-light-success'
                                    : ($active ? 'bg-light-primary' : 'bg-light-secondary');
                            $iconText = $done ? 'text-success'
                                    : ($active ? 'text-primary' : 'text-gray-500');
                            $label    = $done ? 'text-success'
                                    : ($active ? 'text-primary' : 'text-gray-500');
                        @endphp

                        <div class="timeline-item">
                            <div class="timeline-line"></div>
                            <div class="timeline-icon">
                                <span class="symbol symbol-30px">
                                    <span class="symbol-label {{ $iconBg }}">
                                        @if($done)
                                            <i class="ki-duotone ki-check fs-6 {{ $iconText }}"></i>
                                        @elseif($active)
                                            <span class="bullet bullet-dot bg-primary"></span>
                                        @else
                                            <span class="fs-8 fw-bold {{ $iconText }}">{{ $idx + 1 }}</span>
                                        @endif
                                    </span>
                                </span>
                            </div>
                            <div class="timeline-content mb-5 {{ $pending ? 'opacity-75' : '' }}">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold fs-7 {{ $label }}">{{ $step[0] }}</div>
                                        <div class="text-muted fs-8 mt-1">{{ $step[1] }}</div>
                                    </div>
                                    @if($done)
                                        <span class="badge badge-light-success fs-9 flex-shrink-0">Done</span>
                                    @elseif($active)
                                        <span class="badge badge-light-primary fs-9 flex-shrink-0">Current</span>
                                    @else
                                        <span class="badge badge-light-secondary fs-9 flex-shrink-0">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ─────────── DESKTOP: horizontal rail ─────────── --}}
            <div class="d-none d-md-block">
                <div class="d-flex justify-content-between align-items-start position-relative">
                    @foreach($timeline as $key => $step)
                        @php
                            $idx     = array_search($key, $statusOrder);
                            $done    = $idx < $currentIdx || $status === 'completed';
                            $active  = $idx === $currentIdx;
                            $pending = !$done && !$active;

                            $iconBg   = $done ? 'bg-light-success'
                                    : ($active ? 'bg-light-primary' : 'bg-light-secondary');
                            $iconText = $done ? 'text-success'
                                    : ($active ? 'text-primary' : 'text-gray-500');
                            $label    = $done ? 'text-success'
                                    : ($active ? 'text-primary' : 'text-gray-500');
                        @endphp

                        <div class="d-flex flex-column align-items-center flex-fill position-relative px-2">
                            @if(!$loop->last)
                                <div class="position-absolute"
                                    style="top:17px; left:calc(50% + 20px); right:calc(-50% + 20px);
                                            border-top:2px dashed {{ $done ? '#50cd89' : '#e4e6ef' }};
                                            z-index:0;"></div>
                            @endif

                            <span class="symbol symbol-35px" style="z-index:1;">
                                <span class="symbol-label {{ $iconBg }} border border-2 border-white">
                                    @if($done)
                                        <i class="ki-duotone ki-check fs-4 {{ $iconText }}"></i>
                                    @elseif($active)
                                        <span class="bullet bullet-dot bg-primary"></span>
                                    @else
                                        <span class="fs-8 fw-bold {{ $iconText }}">{{ $idx + 1 }}</span>
                                    @endif
                                </span>
                            </span>

                            <div class="text-center mt-3 {{ $pending ? 'opacity-75' : '' }}">
                                <div class="fw-semibold fs-8 {{ $label }}">{{ $step[0] }}</div>
                                <div class="text-muted fs-9 mt-1 px-1" style="line-height:1.2;">
                                    {{ $step[1] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- AI Gap Review                                            --}}
    {{-- ========================================================= --}}
    @if(!empty($r['ai_gap_review']))
    <div class="card card-flush mb-6">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-duotone ki-robot fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                AI Gap Review (Free)
            </h3>
            <div class="card-toolbar">
                <div class="text-center">
                    <div class="fs-2x fw-bold text-info">{{ $r['ai_gap_review']['overall_score'] ?? '—' }}/100</div>
                    <div class="text-muted fs-7">Overall Score</div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(!empty($r['ai_gap_review']['summary']))
                <div class="alert alert-light-info mb-5">{{ $r['ai_gap_review']['summary'] }}</div>
            @endif

            @if(!empty($r['ai_gap_review']['sections']))
                {{-- Desktop: real table --}}
                <div class="d-none d-md-block table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted fw-bold fs-7 text-uppercase">
                                <th>Section</th>
                                <th>Status</th>
                                <th>What we found</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($r['ai_gap_review']['sections'] as $sec)
                                @php $s = $sec['status'] ?? ''; @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $sec['section'] ?? '—' }}</td>
                                    <td>
                                        @if($s === 'ok')
                                            <span class="badge badge-light-success">✅ OK</span>
                                        @elseif($s === 'weak')
                                            <span class="badge badge-light-warning">⚠️ Weak</span>
                                        @else
                                            <span class="badge badge-light-danger">❌ Missing</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $sec['note'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile: stacked cards --}}
                <div class="d-md-none d-flex flex-column gap-3">
                    @foreach($r['ai_gap_review']['sections'] as $sec)
                        @php
                            $s = $sec['status'] ?? '';
                            $border = $s === 'ok' ? 'border-success'
                                    : ($s === 'weak' ? 'border-warning' : 'border-danger');
                        @endphp
                        <div class="border {{ $border }} border-dashed rounded p-4 bg-light-{{ $s === 'ok' ? 'success' : ($s === 'weak' ? 'warning' : 'danger') }} bg-opacity-10">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="fw-bold fs-6 text-gray-800">
                                    {{ $sec['section'] ?? '—' }}
                                </div>
                                @if($s === 'ok')
                                    <span class="badge badge-light-success fs-8 flex-shrink-0">OK</span>
                                @elseif($s === 'weak')
                                    <span class="badge badge-light-warning fs-8 flex-shrink-0">Weak</span>
                                @else
                                    <span class="badge badge-light-danger fs-8 flex-shrink-0">Missing</span>
                                @endif
                            </div>
                            @if(!empty($sec['note']))
                                <div class="text-muted fs-7">{{ $sec['note'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if(!empty($missingFields))
                <div class="mt-5">
                    <div class="fw-bold mb-2 fs-7 text-gray-700">We noticed these are missing:</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($missingFields as $mf)
                            <span class="badge badge-light-danger fs-8">{{ $mf }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ========================================================= --}}
    {{-- ACTION CARD — Submit answers / Edit answers              --}}
    {{-- ========================================================= --}}
    @if(in_array($status, ['ai_reviewed', 'awaiting_payment'], true))

        @php
            $hasAnswers = !empty($answers);
            $isEditMode = $status === 'awaiting_payment' && $hasAnswers;
        @endphp

        <div class="card card-flush mb-6 border border-{{ $isEditMode ? 'warning' : 'primary' }}">
            <div class="card-header bg-light-{{ $isEditMode ? 'warning' : 'primary' }}">
                <h3 class="card-title">
                    {{ $isEditMode ? 'Review Your Answers' : 'Fill In The Gaps' }}
                </h3>
            </div>
            <div class="card-body">
                @if(!$hasAnswers)
                    <p class="text-muted mb-4">
                        The AI found <strong>{{ count($missingFields) }}</strong>
                        {{ Str::plural('gap', count($missingFields)) }} in your CV. Answer them
                        in under a minute and we'll unlock the payment step so our expert can begin.
                    </p>
                    <button type="button" class="btn btn-primary btn-lg" id="openAnswersBtn">
                        <i class="ki-duotone ki-notepad-edit fs-3 me-2">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        Answer {{ count($missingFields) }} {{ Str::plural('Question', count($missingFields)) }}
                    </button>
                @else
                    <div class="alert alert-light-success d-flex align-items-center mb-4">
                        <i class="ki-duotone ki-check-circle fs-2x text-success me-3">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div>
                            <div class="fw-bold">Answers saved</div>
                            <div class="text-muted fs-7">
                                You can still edit them before paying. Once you pay, they're locked.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        @foreach($answers as $field => $value)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <div class="fw-semibold text-muted fs-7 mb-1">
                                        {{ ucwords(str_replace('_', ' ', $field)) }}
                                    </div>
                                    <div>{{ $value ?: '—' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-light-warning" id="openAnswersBtn">
                        <i class="ki-duotone ki-pencil fs-3 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Edit My Answers
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- PAYMENT CTA                                              --}}
    {{-- ========================================================= --}}
    @if($status === 'awaiting_payment')
    <div class="card card-flush mb-6 border border-warning">
        <div class="card-body text-center py-10">
            <i class="ki-duotone ki-dollar fs-3x text-warning mb-3 d-block">
                <span class="path1"></span><span class="path2"></span>
            </i>
            <h3 class="fw-bold mb-2">Complete Payment to Start</h3>
            <p class="text-muted mb-5">
                Our expert will rewrite your CV within
                {{ $r['sla_due_at'] ? 'the SLA window' : '24 hours' }}.
            </p>
            @if(!empty($r['next_service_price']['formatted']))
                <div class="text-muted fs-7 mb-1">{{ $r['next_service_name'] ?? 'CV Rewrite' }}</div>
                <div class="fs-2x fw-bold text-primary mb-5">{{ $r['next_service_price']['formatted'] }}</div>
            @else
                <div class="fs-2x fw-bold text-primary mb-5">{{ $r['formatted_amount'] }}</div>
            @endif
            <button class="btn btn-success btn-lg px-10" id="payBtn">
                <span class="indicator-label">
                    <i class="ki-duotone ki-lock fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                    Pay Now
                </span>
                <span class="indicator-progress">Redirecting...
                    <span class="spinner-border spinner-border-sm ms-2"></span>
                </span>
            </button>
            <div class="text-muted fs-7 mt-3">
                Not ready yet? You can
                <button type="button" class="btn btn-link btn-sm p-0 align-baseline" id="openAnswersBtn">
                    edit your answers
                </button>
                before paying.
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================= --}}
    {{-- DELIVERED                                                --}}
    {{-- ========================================================= --}}
    @if($r['delivered_cv_url'])
    <div class="card card-flush mb-6 border border-success">
        <div class="card-body text-center py-10">
            <i class="ki-duotone ki-check-circle fs-3x text-success mb-3 d-block"></i>
            <h3 class="fw-bold mb-2">Your Revised CV Is Ready 🎉</h3>
            <p class="text-muted mb-5">
                Download it below. If you'd like changes, request a revision.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ $r['delivered_cv_url'] }}" target="_blank" class="btn btn-success btn-lg px-8">
                    <i class="ki-duotone ki-file-down fs-3 me-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    Download Revised CV
                </a>
                @if($status === 'delivered')
                    <button class="btn btn-light-warning btn-lg" data-bs-toggle="modal" data-bs-target="#revisionModal">
                        Request Revision
                    </button>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================= --}}
    {{-- REVISION IN PROGRESS                                     --}}
    {{-- ========================================================= --}}
    @if($status === 'revision_requested' && $lastRevision)
    <div class="card card-flush mb-6 border border-warning">
        <div class="card-header bg-light-warning">
            <h3 class="card-title">
                <i class="ki-duotone ki-arrows-circle fs-2 me-2">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                Revision In Progress
            </h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">
                Our team is working on your requested changes. We'll notify you the moment it's ready.
            </p>

            <div class="border rounded p-4 bg-light mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold">What you asked for</div>
                    <div class="text-muted fs-7">
                        {{ \Carbon\Carbon::parse($lastRevision['requested_at'] ?? now())->diffForHumans() }}
                    </div>
                </div>
                <div class="text-muted">{{ $lastRevision['notes'] ?? '—' }}</div>
            </div>

            <button type="button" class="btn btn-light-warning" id="openRevisionEditBtn">
                <i class="ki-duotone ki-pencil fs-3 me-2">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                Edit My Request
            </button>
        </div>
    </div>
    @endif

    {{-- ========================================================= --}}
    {{-- PAST REVISION HISTORY (delivered + completed)            --}}
    {{-- ========================================================= --}}
    @if(!empty($revisionHistory) && in_array($status, ['delivered', 'completed'], true))
    <div class="card card-flush mb-6">
        <div class="card-header"><h3 class="card-title">Revision History</h3></div>
        <div class="card-body">
            <div class="timeline timeline-border-dashed">
                @foreach(array_reverse($revisionHistory) as $rev)
                    <div class="timeline-item">
                        <div class="timeline-line"></div>
                        <div class="timeline-icon">
                            <i class="ki-duotone ki-message-text-2 fs-2 text-primary">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                        </div>
                        <div class="timeline-content mb-6 mt-n1">
                            <div class="d-flex justify-content-between mb-2">
                                <div class="fw-bold">Revision #{{ $loop->iteration }}</div>
                                <div class="text-muted fs-7">
                                    {{ \Carbon\Carbon::parse($rev['requested_at'] ?? now())->format('M d, Y H:i') }}
                                </div>
                            </div>
                            <div class="text-muted">{{ $rev['notes'] ?? '—' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ============================================================= --}}
{{-- ANSWERS MODAL — submit or edit                               --}}
{{-- ============================================================= --}}
<div class="modal fade" id="answersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="fw-bold" id="answersModalTitle">Fill In The Gaps</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="answersForm">
                @csrf
                <div class="modal-body" style="max-height:65vh;overflow-y:auto;">
                    <p class="text-muted mb-4">
                        Tell us about each gap our Team found. Be as specific as you can — this
                        is what our expert will use when rewriting your CV.
                    </p>

                    @forelse($missingFields as $field)
                        <div class="fv-row mb-4">
                            <label class="fw-semibold mb-2">
                                {{ ucwords(str_replace('_', ' ', $field)) }}
                            </label>
                            <textarea name="answers[{{ $field }}]"
                                      rows="3"
                                      class="form-control"
                                      placeholder="Tell us about {{ str_replace('_', ' ', $field) }}...">{{ $answers[$field] ?? '' }}</textarea>
                        </div>
                    @empty
                        <div class="alert alert-light-info">
                            No gaps to fill — you can proceed to payment.
                        </div>
                    @endforelse
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Save for Later</button>
                    <button type="submit" class="btn btn-primary" id="answersBtn">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-check-circle fs-3 me-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Save & Continue to Payment
                        </span>
                        <span class="indicator-progress">Saving...
                            <span class="spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================= --}}
{{-- REVISION MODAL — create or edit                              --}}
{{-- ============================================================= --}}
<div class="modal fade" id="revisionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="fw-bold" id="revisionModalTitle">Request a Revision</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="revisionForm">
                @csrf
                <div class="modal-body">
                    <label class="fw-semibold mb-2">What would you like changed?</label>
                    <textarea name="notes" rows="5" class="form-control" required
                              placeholder="Be specific so we can get it right the first time...">{{ $lastRevision['notes'] ?? '' }}</textarea>
                    <div class="text-muted fs-7 mt-2">
                        You can only request one revision at a time. Our team will be notified.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="revisionBtn">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-send fs-3 me-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Submit Request
                        </span>
                        <span class="indicator-progress">Sending...
                            <span class="spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold mb-0">Complete Payment</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary"
                        data-bs-dismiss="modal" id="paymentModalClose">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>

            <div class="modal-body">

                {{-- Amount summary --}}
                <div class="bg-light-primary rounded p-4 mb-5 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8">You're paying for</div>
                        <div class="fw-bold fs-5">CV Rewrite</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted fs-8">Amount</div>
                        <div class="fw-bold fs-4 text-primary" id="pm_amount">—</div>
                    </div>
                </div>

                {{-- Method picker --}}
                <label class="fw-semibold fs-7 mb-2">Pay with</label>
                <div class="d-flex flex-column gap-2 mb-5">
                    <label class="d-flex align-items-center gap-3 p-3 border rounded cursor-pointer pm-method-option">
                        <input type="radio" name="pm_method" value="mobile_money" class="form-check-input mt-0" checked>
                        <i class="ki-duotone ki-phone fs-2 text-primary">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-7">Mobile Money</div>
                            <div class="text-muted fs-8">MTN or Airtel — you'll receive a prompt on your phone</div>
                        </div>
                    </label>

                    <label class="d-flex align-items-center gap-3 p-3 border rounded cursor-pointer pm-method-option">
                        <input type="radio" name="pm_method" value="card" class="form-check-input mt-0">
                        <i class="ki-duotone ki-credit-cart fs-2 text-success">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-7">Card</div>
                            <div class="text-muted fs-8">Visa or Mastercard — you'll be redirected to a secure page</div>
                        </div>
                    </label>
                </div>

                {{-- Phone input (mobile money only) --}}
                <div id="pm_phone_wrap" class="mb-4">
                    <label class="fw-semibold fs-7 mb-2">Mobile Money Number</label>
                    <input type="tel"
                           id="pm_phone"
                           class="form-control form-control-lg"
                           placeholder="+256 754 428 612"
                           autocomplete="tel">
                    <div class="text-muted fs-8 mt-1">
                        Use the number registered with MTN or Airtel Money.
                    </div>
                </div>

                {{-- Credit / balance warning (changes per method) --}}
                <div class="alert alert-light-warning d-flex align-items-start mb-0" id="pm_warning">
                    <i class="ki-duotone ki-information-5 fs-3 text-warning me-2 mt-1">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="fs-7" id="pm_warning_text">
                        <strong>Make sure you have enough mobile money credit.</strong>
                        Your balance must cover the amount above before you can approve the PIN prompt.
                        If the payment fails, contact support.
                    </div>
                </div>

                {{-- Error message --}}
                <div id="pm_error" class="alert alert-light-danger mt-4 mb-0 d-none"></div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="pm_submit_btn">
                    <span class="indicator-label">
                        <i class="ki-duotone ki-lock fs-3 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Pay Now
                    </span>
                    <span class="indicator-progress">
                        Processing... <span class="spinner-border spinner-border-sm ms-2"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
<style>
    .pm-method-option {
        transition: border-color .15s ease, background .15s ease;
    }
    .pm-method-option:hover {
        border-color: var(--bs-primary) !important;
        background: var(--bs-light-primary);
    }
    .pm-method-option:has(input:checked) {
        border-color: var(--bs-primary) !important;
        background: var(--bs-light-primary);
    }
</style>
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const UUID = '{{ $r['uuid'] }}';
const STATUS = '{{ $status }}';

// ─────────────────────────────────────────────────────────────
// ANSWERS MODAL
// ─────────────────────────────────────────────────────────────
const answersModalEl = document.getElementById('answersModal');
const answersModal = answersModalEl ? new bootstrap.Modal(answersModalEl) : null;

// Open from any "openAnswersBtn" trigger
document.querySelectorAll('#openAnswersBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        if (answersModal) answersModal.show();
    });
});

// Auto-open on first visit when status is 'ai_reviewed' and answers are empty
document.addEventListener('DOMContentLoaded', () => {
    if (STATUS === 'ai_reviewed' && answersModal) {
        const key = 'cv_review_answers_modal_' + UUID;
        if (!sessionStorage.getItem(key)) {
            answersModal.show();
            sessionStorage.setItem(key, '1');
        }
    }
});

// Submit answers
document.getElementById('answersForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = document.getElementById('answersBtn');
    window.showButtonSpinner(btn);

    const fd = new FormData(this);
    const answers = {};
    for (const [k, v] of fd.entries()) {
        const m = k.match(/answers\[(.+)\]/);
        if (m) answers[m[1]] = v;
    }

    // Client-side guard: require at least one field filled
    const filled = Object.values(answers).filter(v => v && v.trim() !== '');
    if (filled.length === 0) {
        window.showToast('error', 'Please fill in at least one answer.', 'Missing Info');
        window.hideButtonSpinner(btn);
        return;
    }

    fetch(`/cv-review/${UUID}/answers`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ answers })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message || 'Answers saved. You can now pay.', 'Saved');
            answersModal?.hide();
            setTimeout(() => location.reload(), 800);
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message || 'Failed to save answers.');
            window.showToast('error', msg, 'Error');
        }
    })
    .catch(() => window.showToast('error', 'Something went wrong', 'Error'))
    .finally(() => window.hideButtonSpinner(btn));
});

// ─────────────────────────────────────────────────────────────
// PAYMENT
// ─────────────────────────────────────────────────────────────
(function () {
    const payBtn = document.getElementById('payBtn');
    if (!payBtn) return;

    const modalEl     = document.getElementById('paymentModal');
    const modal       = new bootstrap.Modal(modalEl);
    const phoneWrap   = document.getElementById('pm_phone_wrap');
    const phoneInput  = document.getElementById('pm_phone');
    const methodRadios= document.querySelectorAll('input[name="pm_method"]');
    const submitBtn   = document.getElementById('pm_submit_btn');
    const amountEl    = document.getElementById('pm_amount');
    const errorEl     = document.getElementById('pm_error');
    const closeBtn    = document.getElementById('paymentModalClose');

    const warningText = document.getElementById('pm_warning_text');

    function setWarningText(method) {
        if (!warningText) return;

        if (method === 'card') {
            warningText.innerHTML = `
                <strong>Have your card details ready.</strong>
                You will be redirected to a secure payment page to complete the transaction.
                Make sure the card supports online payments and has sufficient funds.
                If the payment fails, contact support.
            `;
        } else {
            warningText.innerHTML = `
                <strong>Make sure you have enough mobile money credit.</strong>
                Your balance must cover the amount above before you can approve the PIN prompt.
                If the payment fails, contact support.
            `;
        }
    }

    // Show/hide the phone field based on chosen method
    function syncMethod() {
        const method = document.querySelector('input[name="pm_method"]:checked')?.value || 'mobile_money';
        phoneWrap.classList.toggle('d-none', method === 'card');
        errorEl.classList.add('d-none');
        errorEl.textContent = '';
        setWarningText(method);
    }
    methodRadios.forEach(r => r.addEventListener('change', syncMethod));

    // Open the modal
    payBtn.addEventListener('click', () => {
        modal.show();
        syncMethod();
        // Focus the phone input if mobile money is selected
        setTimeout(() => {
            if (phoneWrap && !phoneWrap.classList.contains('d-none')) {
                phoneInput.focus();
            }
        }, 300);
    });

    // Submit from within the modal
    submitBtn.addEventListener('click', async () => {
        const method = document.querySelector('input[name="pm_method"]:checked')?.value || 'mobile_money';
        const phone = phoneInput.value.replace(/[^\d+]/g, '').trim();

        if (method === 'mobile_money') {
            if (!phone) {
                showError('Please enter your mobile money number.');
                return;
            }
            if (!/^\+\d{9,15}$/.test(phone)) {
                showError('That phone number does not look right. Use the format +256 754 428 612.');
                return;
            }
        }

        // Visual loading state on the submit button
        submitBtn.setAttribute('data-kt-indicator', 'on');
        submitBtn.disabled = true;
        errorEl.classList.add('d-none');

        try {
            const res = await fetch(`/cv-review/${UUID}/pay`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    phone: method === 'mobile_money' ? phone : null,
                    method,
                }),
            });
            const data = await res.json();

            if (!data.success) {
                const msg = data.message || 'Payment could not be started.';

                // If MarzPay rejected the phone number, focus and highlight that field
                if (/phone number/i.test(msg) && !phoneWrap.classList.contains('d-none')) {
                    phoneInput.classList.add('is-invalid');
                    phoneInput.focus();
                    phoneInput.addEventListener('input', () => phoneInput.classList.remove('is-invalid'), { once: true });
                }

                showError(msg);
                submitBtn.removeAttribute('data-kt-indicator');
                submitBtn.disabled = false;
                return;
            }

            // Show the amount from the response, in case the server updated it
            if (data.amount_formatted) {
                amountEl.textContent = data.amount_formatted;
            }

            if (data.is_free) {
                modal.hide();
                window.showToast('success', data.message, 'Free');
                setTimeout(() => location.reload(), 1500);
                return;
            }

            if (data.redirect_url) {
                // Card flow — leave the modal open while the browser navigates
                window.location.href = data.redirect_url;
                return;
            }

            // Mobile money — modal stays open, show "check your phone" and poll
            showInfo(
                'A payment prompt has been sent to your phone. Enter your PIN to complete payment. ' +
                'Please make sure you have enough mobile money credit.'
            );

            // Swap the Pay Now button to a disabled state while polling
            submitBtn.removeAttribute('data-kt-indicator');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Waiting for confirmation...';

            let polls = 0;
            const maxPolls = 40;  // ~2 minutes at 3s intervals

            const poll = setInterval(async () => {
                polls++;
                try {
                    const r  = await fetch(`/cv-review/${UUID}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const b = await r.json();
                    const status = b.request?.status;

                    if (status && status !== 'awaiting_payment') {
                        clearInterval(poll);
                        modal.hide();
                        window.showToast('success', 'Payment confirmed. Welcome aboard.', 'Success');
                        setTimeout(() => location.reload(), 1000);
                    } else if (polls >= maxPolls) {
                        clearInterval(poll);
                        showError(
                            'We have not received the confirmation yet. ' +
                            'If you were charged, the payment will appear shortly. ' +
                            'Refresh this page in a moment.'
                        );
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = payButtonHTML();
                    }
                } catch (e) {
                    // network blip — keep polling
                }
            }, 3000);

        } catch (e) {
            showError('Something went wrong: ' + e.message);
            submitBtn.removeAttribute('data-kt-indicator');
            submitBtn.disabled = false;
        }
    });

    // Reset the modal when closed
    closeBtn.addEventListener('click', () => {
        submitBtn.disabled = false;
        submitBtn.removeAttribute('data-kt-indicator');
        submitBtn.innerHTML = payButtonHTML();
        errorEl.classList.add('d-none');
    });

    // Helpers
    function showError(msg) {
        errorEl.className = 'alert alert-light-danger mt-4 mb-0';
        errorEl.textContent = msg;
        errorEl.classList.remove('d-none');
    }

    function showInfo(msg) {
        errorEl.className = 'alert alert-light-info mt-4 mb-0';
        errorEl.textContent = msg;
        errorEl.classList.remove('d-none');
    }

    function payButtonHTML() {
        return '<i class="ki-duotone ki-lock fs-3 me-2"><span class="path1"></span><span class="path2"></span></i> Pay Now';
    }

    // Populate the amount when the modal opens — pull from the page or fetch
    document.getElementById('payBtn')?.addEventListener('click', async () => {
        // You likely already have the price rendered on the page
        const existingAmount = document.getElementById('pm_amount')?.textContent;
        if (!existingAmount || existingAmount === '—') {
            // Fallback: hit the quote endpoint to get the amount
            try {
                const r = await fetch(`/cv-review/${UUID}`, { headers: { 'Accept': 'application/json' } });
                const b = await r.json();
                const formatted = b?.request?.formatted_amount || '—';
                amountEl.textContent = formatted;
            } catch (e) {
                amountEl.textContent = '—';
            }
        }
    });
})();

// ─────────────────────────────────────────────────────────────
// REVISION MODAL
// ─────────────────────────────────────────────────────────────
const revisionModalEl = document.getElementById('revisionModal');
const revisionModal = revisionModalEl ? new bootstrap.Modal(revisionModalEl) : null;

document.getElementById('openRevisionEditBtn')?.addEventListener('click', () => {
    // Edit mode uses the same modal, different submit endpoint
    revisionModal?.show();
});

document.getElementById('revisionForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = document.getElementById('revisionBtn');
    window.showButtonSpinner(btn);

    const notes = this.querySelector('textarea[name="notes"]').value.trim();

    if (!notes) {
        window.showToast('error', 'Please describe what you want changed.', 'Missing Info');
        window.hideButtonSpinner(btn);
        return;
    }

    // Choose endpoint: create new revision vs update existing
    const isEdit = STATUS === 'revision_requested';
    const url = isEdit
        ? `/cv-review/${UUID}/revision`   // same route, controller upserts last
        : `/cv-review/${UUID}/revision`;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ notes })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message || 'Revision requested.', 'Sent');
            revisionModal?.hide();
            setTimeout(() => location.reload(), 800);
        } else {
            window.showToast('error', data.message || 'Failed', 'Error');
        }
    })
    .catch(() => window.showToast('error', 'Something went wrong', 'Error'))
    .finally(() => window.hideButtonSpinner(btn));
});
</script>
@endpush