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

    // ── Answer / action state ─────────────────────────────────
    $hasAnswers   = !empty($answers);
    $isEditMode   = $status === 'awaiting_payment' && $hasAnswers;
    $needsAction  = in_array($status, ['ai_reviewed', 'awaiting_payment'], true);
    $showPayNow   = $status === 'awaiting_payment' && $hasAnswers;
    $showFillGaps = $status === 'ai_reviewed'
                 || ($status === 'awaiting_payment' && !$hasAnswers);

    $canDelete = !in_array($status, [
        'paid', 'paid_ai', 'paid_human', 'in_progress',
        'delivered', 'revision_requested', 'completed',
    ], true);

    $deleteBlockReason = match($status) {
        'paid', 'paid_ai', 'paid_human' => 'Cannot delete — payment already received',
        'in_progress'                   => 'Cannot delete — work has started',
        'delivered'                     => 'Cannot delete — CV has been delivered',
        'revision_requested'            => 'Cannot delete — a revision is in progress',
        'completed'                     => 'Cannot delete — request is complete',
        default                         => 'Cannot delete',
    };

    // ── Timeline (split by rewrite mode) ──────────────────────
    $isAi    = ($r['rewrite_mode'] ?? null) === 'ai';
    $isHuman = ($r['rewrite_mode'] ?? null) === 'human';

    $baseTimeline = [
        'submitted'        => ['Uploaded',         'Your CV has been received.'],
        'ai_reviewed'      => ['CV Reviewed',      'Free gap review is ready below.'],
        'awaiting_payment' => ['Awaiting Payment', 'Complete payment to unlock the rewrite.'],
    ];

    $aiSteps = [
        'paid_ai'    => ['Paid',       'Payment received. Generating your CV...'],
        'generating' => ['Generating', 'AI is writing your CV now.'],
        'generated'  => ['Generated',  'Your rewritten CV is ready.'],
        'completed'  => ['Completed',  'All done. 🎉'],
    ];

    $humanSteps = [
        'paid_human'         => ['Paid',        'Payment received. Assigning your HR expert...'],
        'in_progress'        => ['In Progress', 'A career expert is working on your CV.'],
        'delivered'          => ['Delivered',   'Your revised CV is ready.'],
        'revision_requested' => ['Revision',    'We are working on your revision.'],
        'completed'          => ['Completed',   'All done. 🎉'],
    ];

    if ($isAi) {
        $timeline = array_merge($baseTimeline, $aiSteps);
    } elseif ($isHuman) {
        $timeline = array_merge($baseTimeline, $humanSteps);
    } else {
        // Pre-payment — only show the first 3 steps
        $timeline = $baseTimeline;
    }

    // Cancelled is universal — append if it isn't already there
    if (!isset($timeline['cancelled'])) {
        $timeline['cancelled'] = ['Cancelled', 'This request was cancelled.'];
    }

    // Legacy: 'paid' status used by old HR-flow records → treat as paid_human
    if ($status === 'paid' && $isHuman) {
        $status = 'paid_human';
    }

    $statusOrder = array_keys($timeline);
    $currentIdx  = array_search($status, $statusOrder);
    if ($currentIdx === false) {
        $currentIdx = 0;
    }

    $isGeneratingNow = in_array($r['status'], ['paid_ai', 'generating'], true)
        && ($r['rewrite_mode'] ?? null) === 'ai'
        && empty($r['rewrite_error_message']);

@endphp

<div class="container py-6">
    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- GENERATING VIEW — full-page overlay, hides everything else  --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($isGeneratingNow)
        <div id="aiRewriteSection" data-uuid="{{ $r['uuid'] }}">
            <div class="card card-flush">
                <div class="card-body text-center py-15" style="min-height: 60vh;">
                    <div class="spinner-border text-primary mb-4" style="width:4rem;height:4rem;"></div>
                    <h2 class="fw-bold mb-2">Writing your CV…</h2>
                    <p class="text-muted mb-1">
                        The CV Rewriter is drafting your rewritten CV. Every line is being built
                        from your original CV and the answers you provided.
                    </p>
                    <p class="text-muted fs-7 mb-5">
                        <i class="ki-duotone ki-time fs-6 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Usually 30–60 seconds
                    </p>
                    <div class="text-muted fs-8">
                        This page will update automatically. You can safely leave it open.
                    </div>
                </div>
            </div>
        </div>
    @else


    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- AI Rewrite viewer — FIRST when ready (user sees result)   --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($r['rewrite_is_ready'] ?? false)
        @include('job-seeker.cv-review._ai-rewrite', ['r' => $r])
    @endif

        {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- AI Rewrite FAILED — show retry card                       --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if(
        $status === 'failed'
        && ($r['rewrite_mode'] ?? null) === 'ai'
        && !empty($r['rewrite_error_message'])
    )
        <div class="card card-flush mb-6 border border-danger border-2">
            <div class="card-header bg-light-danger">
                <h3 class="card-title">
                    <i class="ki-duotone ki-information-5 fs-2 me-2 text-danger">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    We couldn't finish writing your CV
                </h3>
            </div>
            <div class="card-body text-center py-10">

                <p class="text-gray-700 fs-6 mb-3 mx-auto" style="max-width: 560px;">
                    Something went wrong while generating your CV. Your payment is safe.
                    You can try again below — most failures are temporary.
                </p>

                <div class="alert alert-light-warning d-inline-block text-start fs-7 mb-5 mx-auto"
                     style="max-width: 640px;">
                    <strong>Why it failed:</strong>
                    <span class="text-break">{{ $r['rewrite_error_message'] }}</span>
                </div>

                {{-- ═══════════════════════════════════════════════════════════ --}}
                {{-- AI Rewrite FAILED — retry OR fall back to original        --}}
                {{-- ═══════════════════════════════════════════════════════════ --}}
                @php
                    $hasOriginalContent = !empty($r['rewrite_content_original']['html'] ?? null);
                    $originalWords      = $hasOriginalContent
                        ? str_word_count(strip_tags($r['rewrite_content_original']['html']))
                        : 0;
                @endphp

                @if(
                    $status === 'failed'
                    && ($r['rewrite_mode'] ?? null) === 'ai'
                    && !empty($r['rewrite_error_message'])
                )
                    <div class="card card-flush mb-6 border border-danger border-2">
                        <div class="card-header bg-light-danger">
                            <h3 class="card-title">
                                <i class="ki-duotone ki-information-5 fs-2 me-2 text-danger">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                                We couldn't finish writing your CV
                            </h3>
                        </div>
                        <div class="card-body text-center py-10">

                            <p class="text-gray-700 fs-6 mb-3 mx-auto" style="max-width: 560px;">
                                Something went wrong while generating your CV. Your payment is safe —
                                choose one of the options below to continue.
                            </p>

                            <div class="alert alert-light-warning d-inline-block text-start fs-7 mb-5 mx-auto"
                                style="max-width: 640px;">
                                <strong>Why it failed:</strong>
                                <span class="text-break">{{ $r['rewrite_error_message'] }}</span>
                            </div>

                            <div class="row g-4 justify-content-center mt-2">

                                {{-- Option A: retry the AI --}}
                                <div class="col-12 col-md-6">
                                    <div class="card h-100 border border-warning border-2">
                                        <div class="card-body text-center py-8">
                                            <i class="ki-duotone ki-arrows-circle fs-3x text-warning mb-3 d-block">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            <h4 class="fw-bold mb-2">Try Again</h4>
                                            <p class="text-muted fs-7 mb-4">
                                                Most failures are temporary. Try writing the CV once more —
                                                your payment still applies.
                                            </p>
                                            <button type="button"
                                                    class="btn btn-warning w-100"
                                                    id="retryGenerationBtn"
                                                    data-uuid="{{ $r['uuid'] }}">
                                                <span class="indicator-label">
                                                    <i class="ki-duotone ki-arrows-circle fs-4 me-1">
                                                        <span class="path1"></span><span class="path2"></span>
                                                    </i>
                                                    Try Again
                                                </span>
                                                <span class="indicator-progress">
                                                    Starting…
                                                    <span class="spinner-border spinner-border-sm ms-2"></span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Option B: fall back to the last saved original --}}
                                @if($hasOriginalContent)
                                    <div class="col-12 col-md-6">
                                        <div class="card h-100 border border-primary border-2">
                                            <div class="card-body text-center py-8">
                                                <i class="ki-duotone ki-document fs-3x text-primary mb-3 d-block">
                                                    <span class="path1"></span><span class="path2"></span>
                                                </i>
                                                <h4 class="fw-bold mb-2">Use the last saved version</h4>
                                                <p class="text-muted fs-7 mb-4">
                                                    We still have your previous AI-written CV
                                                    ({{ number_format($originalWords) }} words).
                                                    Restore it and edit it yourself — same price,
                                                    no extra charge.
                                                </p>
                                                <button type="button"
                                                        class="btn btn-primary w-100"
                                                        id="promoteOriginalBtn"
                                                        data-uuid="{{ $r['uuid'] }}">
                                                    <span class="indicator-label">
                                                        <i class="ki-duotone ki-pencil fs-4 me-1">
                                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                                        </i>
                                                        View & Edit Original
                                                    </span>
                                                    <span class="indicator-progress">
                                                        Restoring…
                                                        <span class="spinner-border spinner-border-sm ms-2"></span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="text-muted fs-8 mt-6">
                                If it keeps failing, contact support with reference
                                <strong>#{{ substr($r['uuid'], 0, 8) }}</strong>.
                            </div>
                        </div>
                    </div>
                @endif

                <div class="text-muted fs-8 mt-4">
                    If it keeps failing, contact support with reference
                    <strong>#{{ substr($r['uuid'], 0, 8) }}</strong>.
                </div>
            </div>
        </div>
    @endif


    {{-- ========================================================= --}}
    {{-- SAVED ANSWERS (visible after answers are submitted)     --}}
    {{-- ========================================================= --}}
    @if($hasAnswers)
    <div class="card card-flush mb-6" id="savedAnswersCard">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-duotone ki-notepad-edit fs-2 me-2">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
                Your Answers
                <span class="badge badge-light-success ms-2">{{ count($answers) }}</span>
            </h3>
            <div class="card-toolbar">
                @if(in_array($status, ['ai_reviewed', 'awaiting_payment'], true))
                    <button type="button" class="btn btn-sm btn-light-primary open-answers-btn">
                        <i class="ki-duotone ki-pencil fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        Edit Answers
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="row g-3">
                @foreach($answers as $q => $a)
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100 bg-light">
                            <div class="fw-semibold text-muted fs-8 mb-1"
                                style="word-break:break-word;">
                                {{ $q }}
                            </div>
                            <div class="fs-7 text-gray-800" style="white-space:pre-wrap;">{{ $a ?: '—' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @php
        /**
        * Per-status reassurance copy. Tone: warm, professional, human.
        * Shown to the seeker so they know exactly what to expect next.
        */
        $reassurance = [
            'submitted' => [
                'icon'  => 'ki-time',
                'color' => 'info',
                'title' => "Your CV has been received",
                'body'  => "Nothing is happening yet — you're in control. Scroll to the bottom of this page and click “Run Free AI Review” to see your gap analysis. It takes about 30 seconds and costs nothing. After that, you choose how to proceed: let The CV Rewriter tool rewrite your CV instantly, or let a senior HR expert do it manually.",
                'eta'   => 'Action required from you',
            ],
            'failed' => [
                'icon'  => 'ki-information-5',
                'color' => 'danger',
                'title' => "We couldn't finish your CV",
                'body'  => "Something went wrong during generation. Your payment is safe — you can try again below at no extra cost.",
                'eta'   => null,
            ],
            'ai_reviewed' => [
                'icon'  => 'ki-message-text-2',
                'color' => 'info',
                'title' => "Your CV has been reviewed",
                'body'  => "Our reviewer has gone through your CV and flagged a few gaps. Read what they found below, then answer the short questions so we can move forward.",
                'eta'   => 'About 2 minutes',
            ],
            'awaiting_payment' => [
                'icon'  => 'ki-dollar',
                'color' => 'warning',
                'title' => "Almost there — one step to go",
                'body'  => "Your answers are saved. Choose how you want your CV rewritten — instantly with our CV Rewriter tool, or carefully by a senior HR expert — then complete the payment to get started.",
                'eta'   => "Instant (CV Rewriter) or 12–24 hours (HR expert)",
            ],
            'paid_ai' => [
                'icon'  => 'ki-robot',
                'color' => 'primary',
                'title' => "Payment received — generating your CV",
                'body'  => "Thank you! The CV Rewriter is rewriting your CV right now. This usually takes under a minute, and the page will update automatically when it's ready.",
                'eta'   => 'Usually 30–60 seconds',
            ],
            'paid_human' => [
                'icon'  => 'ki-user-tick',
                'color' => 'primary',
                'title' => "Payment received — an HR expert is being assigned",
                'body'  => "Thank you! Your payment is confirmed and we're matching your CV to a senior HR expert. You'll see an update here the moment they start.",
                'eta'   => 'Assignment usually within 1 hour',
            ],
            'generating' => [
                'icon'  => 'ki-arrows-circle',
                'color' => 'primary',
                'title' => "Writing your CV…",
                'body'  => "The CV Rewriter is drafting your rewritten CV. Every line is being built from your original CV and the answers you provided.",
                'eta'   => 'Usually 30–60 seconds',
            ],
            'generated' => [
                'icon'  => 'ki-check-circle',
                'color' => 'success',
                'title' => "Your rewritten CV is ready 🎉",
                'body'  => "Read it, edit it if you like, or download the PDF. You can regenerate up to 2 more times if you want a fresh version.",
                'eta'   => 'Mark as done when you are happy with it',
            ],
            'paid' => [
                'icon'  => 'ki-check-circle',
                'color' => 'primary',
                'title' => "Thank you — payment received",
                'body'  => "Our team has been notified and is preparing your file. You'll get an update here as soon as work begins.",
                'eta'   => 'Assignment usually within 1 hour',
            ],
            'in_progress' => [
                'icon'  => 'ki-arrows-circle',
                'color' => 'primary',
                'title' => "Your CV is in expert hands",
                'body'  => "A senior HR expert is carefully rewriting your CV now. Every line is read, revised, and re-read for professionalism, clarity, and alignment with your target role.",
                'eta'   => "You'll be notified the moment it's ready",
            ],
            'delivered' => [
                'icon'  => 'ki-check-circle',
                'color' => 'success',
                'title' => "Your revised CV is ready 🎉",
                'body'  => "Please download it, read it carefully, and let us know if anything needs adjusting. We're happy to make revisions — that's what we're here for.",
                'eta'   => 'Revision window: 3 days',
            ],
            'revision_requested' => [
                'icon'  => 'ki-arrows-circle',
                'color' => 'warning',
                'title' => "We're on your revision",
                'body'  => "Thanks for the feedback — our HR expert is applying your requested changes right now. You'll get the updated version here shortly.",
                'eta'   => 'Usually within a few hours',
            ],
            'completed' => [
                'icon'  => 'ki-check-circle',
                'color' => 'success',
                'title' => "All done — thank you!",
                'body'  => "We hope your new CV serves you well on your career journey. If you ever want another review — for a new role, a new industry, or just a refresh — we're here.",
                'eta'   => null,
            ],
            'cancelled' => [
                'icon'  => 'ki-information-5',
                'color' => 'danger',
                'title' => "This request was cancelled",
                'body'  => "If this was a mistake, or you'd like to start again, you're welcome to submit a new CV for review any time.",
                'eta'   => null,
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
                Feedback From Our Reviewer (Free)
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
                            <span class="badge badge-light-danger fs-8 text-wrap text-start"
                                style="max-width:100%; white-space:normal; word-break:break-word;">
                                {{ $mf }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif


    {{-- ========================================================= --}}
    {{-- ACTION CARDS by status                                   --}}
    {{-- ========================================================= --}}

    {{-- STEP 2 — SUBMITTED: action required from the user --}}
    @if($status === 'submitted')
    <div class="card card-flush mb-6 border border-info border-2">
        <div class="card-body text-center py-10 py-md-15">

            <i class="ki-duotone ki-rocket fs-3x fs-md-5x text-info mb-4 d-block">
                <span class="path1"></span><span class="path2"></span>
            </i>

            <h2 class="fw-bold mb-3 fs-3">
                Your CV is in. Now let's find the gaps.
            </h2>

            <p class="text-gray-700 fs-6 mb-2 mx-auto" style="max-width: 560px;">
                Click the button below to run the free AI review.
                We'll scan your CV against the job you're targeting and list
                every missing fact, weak line, and inconsistent date.
            </p>
            <p class="text-muted fs-7 mb-6">
                Takes about 30 seconds &middot; Free &middot; No payment yet
            </p>

            <button type="button"
                    class="btn btn-info btn-lg px-10"
                    id="runAiReviewBtn"
                    data-uuid="{{ $r['uuid'] }}">
                <span class="indicator-label">
                    <i class="ki-duotone ki-magnifier fs-3 me-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    Run Free AI Review Now
                </span>
                <span class="indicator-progress">
                    Analysing your CV (up to 30s)...
                    <span class="spinner-border spinner-border-sm ms-2"></span>
                </span>
            </button>

            <div class="text-muted fs-8 mt-4">
                <i class="ki-duotone ki-information-5 fs-7 me-1">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
                You'll see the results on this same page. Nothing is charged until
                you choose to rewrite.
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 3 — AI REVIEWED: answer gap questions --}}
    @if($status === 'ai_reviewed' || ($status === 'awaiting_payment' && !$hasAnswers))
    <div class="card card-flush mb-6 border border-primary">
        <div class="card-header bg-light-primary">
            <h3 class="card-title">
                <i class="ki-duotone ki-notepad-edit fs-2 me-2">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
                Fill In The Gaps
            </h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">
                The Reviewer found <strong>{{ count($missingFields) }}</strong>
                {{ Str::plural('gap', count($missingFields)) }}. Answer every field below to unlock payment.
                <span class="text-danger">All fields are required.</span>
            </p>
            <button type="button" class="btn btn-primary btn-lg open-answers-btn">
                <i class="ki-duotone ki-notepad-edit fs-3 me-2">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
                Answer {{ count($missingFields) }} {{ Str::plural('Question', count($missingFields)) }}
            </button>
        </div>
    </div>
    @endif


    {{-- STEP 3b — AWAITING PAYMENT with answers: choose AI vs HR --}}
    @if($status === 'awaiting_payment' && $hasAnswers)
    @php
        $aiPrice    = $r['next_service_price']       ?? null;
        $humanPrice = $r['next_service_price_human'] ?? null;
    @endphp
    <div class="card card-flush mb-6 border border-warning">
        <div class="card-header bg-light-warning">
            <h3 class="card-title">
                <i class="ki-duotone ki-dollar fs-2 me-2">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                Choose How To Rewrite Your CV
            </h3>
        </div>
        <div class="card-body">

            {{-- Answers preview --}}
            <div class="alert alert-light-success d-flex align-items-center mb-5">
                <i class="ki-duotone ki-check-circle fs-2x text-success me-3">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <div class="flex-grow-1">
                    <div class="fw-bold">Answers saved</div>
                    <div class="text-muted fs-7">
                        You can still
                        <button type="button" class="btn btn-link btn-sm p-0 align-baseline open-answers-btn">
                            edit them
                        </button>
                        before paying.
                    </div>
                </div>
            </div>

            {{-- Two options --}}
            <div class="row g-4 mb-5">
                {{-- Option A: AI Tool --}}
                <div class="col-md-6">
                    <label class="d-block border border-primary border-2 rounded p-4 cursor-pointer h-100 option-card"
                        data-mode="ai">
                        <input type="radio" name="rewrite_mode" value="ai" class="d-none" checked>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="fw-bold fs-4">Let the Rewriter Tool Do It</div>
                                <div class="text-muted fs-7">CV Generated in 30&ndash;60 seconds</div>
                            </div>
                            <i class="ki-duotone ki-rocket fs-2x text-primary">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </div>
                        <ul class="text-muted fs-7 mb-3 ps-4">
                            <li>Instant delivery</li>
                            <li>2 free regenerations</li>
                            <li>Full editing &amp; PDF download</li>
                        </ul>
                        <div class="fs-3 fw-bold text-primary">{{ $aiPrice['formatted'] ?? '—' }}</div>
                    </label>
                </div>

                {{-- Option B: HR Expert --}}
                <div class="col-md-6">
                    <label class="d-block border border-warning border-2 rounded p-4 cursor-pointer h-100 option-card"
                        data-mode="human">
                        <input type="radio" name="rewrite_mode" value="human" class="d-none">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="fw-bold fs-4">Let an HR Expert Do It</div>
                                <div class="text-muted fs-7">Human-written in 12&ndash;24 hours</div>
                            </div>
                            <i class="ki-duotone ki-user-tick fs-2x text-warning">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </div>
                        <ul class="text-muted fs-7 mb-3 ps-4">
                            <li>Reviewed by a senior HR expert</li>
                            <li>12&ndash;24 hour delivery</li>
                            <li>1 free revision</li>
                        </ul>
                        <div class="fs-3 fw-bold text-warning">{{ $humanPrice['formatted'] ?? '—' }}</div>
                    </label>
                </div>
            </div>

            {{-- Pay button --}}
            <div class="text-center">
                <button class="btn btn-success btn-lg px-10" id="payBtn">
                    <span class="indicator-label">
                        <i class="ki-duotone ki-lock fs-3 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Pay Now
                    </span>
                    <span class="indicator-progress">
                        Redirecting...
                        <span class="spinner-border spinner-border-sm ms-2"></span>
                    </span>
                </button>
            </div>

        </div>
    </div>
    @endif

    {{-- STEP 4 (HUMAN) — PAID HUMAN / IN PROGRESS --}}
    @if($r['rewrite_mode'] === 'human' && in_array($status, ['paid_human', 'in_progress'], true))
    <div class="card card-flush mb-6 border border-primary">
        <div class="card-body text-center py-10">
            <i class="ki-duotone ki-user-tick fs-3x text-primary mb-3 d-block"></i>
            <h3 class="fw-bold mb-2">An HR Expert Is On It</h3>
            <p class="text-muted mb-4">
                We've assigned your CV to a senior HR expert. They'll deliver within 12&ndash;24 hours.
                You'll be notified the moment it's ready.
            </p>
            @if($r['sla_due_at'])
                <div class="badge badge-light-primary fs-7 py-3 px-4">
                    Expected: {{ \Carbon\Carbon::parse($r['sla_due_at'])->format('M d, Y H:i') }}
                </div>
            @endif
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
    @endif
</div>

{{-- ============================================================= --}}
{{-- ANSWERS MODAL — submit or edit                               --}}
{{-- ============================================================= --}}
<div class="modal fade" id="answersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
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
                                    class="form-control @error('answers.' . $field) is-invalid @enderror"
                                    placeholder="Tell us about {{ str_replace('_', ' ', $field) }}..."
                                    required
                                    minlength="5">{{ $answers[$field] ?? '' }}</textarea>
                            <div class="invalid-feedback">
                                Please answer this question before continuing.
                            </div>
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

{{-- ============================================================= --}}
{{-- REUSABLE CONFIRM MODAL                                        --}}
{{-- ============================================================= --}}
<div class="modal fade" id="confirmActionModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="ki-duotone ki-information-5 fs-2x" id="confirmIcon">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <h3 class="fw-bold mb-0 fs-5" id="confirmTitle">Confirm Action</h3>
                </div>
                <button type="button" class="btn btn-icon btn-sm" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>
            <div class="modal-body pt-2 pb-4">
                <p class="text-gray-700 mb-0" id="confirmMessage">Are you sure?</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="confirmCancelBtn">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmOkBtn">
                    <span class="indicator-label">Confirm</span>
                    <span class="indicator-progress">
                        Please wait...
                        <span class="spinner-border spinner-border-sm ms-2"></span>
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
<style>
    .option-card {
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
    }
    .option-card:hover {
        background: #f8f9fa;
    }
    .option-card.selected[data-mode="ai"] {
        border-color: var(--bs-primary) !important;
        background: var(--bs-light-primary);
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), .15);
    }
    .option-card.selected[data-mode="human"] {
        border-color: var(--bs-warning) !important;
        background: var(--bs-light-warning);
        box-shadow: 0 0 0 3px rgba(var(--bs-warning-rgb), .15);
    }
    .cv-preview {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 0.9rem;
        line-height: 1.55;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    @media (min-width: 768px) {
        .cv-preview { font-size: 1rem; line-height: 1.6; }
    }
    .cv-preview h1 { font-size: 1.5rem; font-weight: 700; }
    .cv-preview h2 { font-size: 1.2rem; font-weight: 700; margin-top: 1.4em; border-bottom: 1px solid #e4e6ef; padding-bottom: 0.3em; }
    .cv-preview h3 { font-size: 1.05rem; font-weight: 700; margin-top: 1em; }
    .cv-preview ul { padding-left: 1.4em; }
</style>
<style>
    /* Make TinyMCE blend with Metronic */
    .tox-tinymce {
        border-radius: 0.475rem !important;
        border-color: var(--bs-gray-300) !important;
    }

    @media (max-width: 575.98px) {
        .w-sm-auto { width: 100% !important; }
    }
    @media (min-width: 576px) {
        .w-sm-auto { width: auto !important; }
    }
</style>
<style>
    .cv-preview h2 { border-bottom: 2px solid #1a1a1a; }

    .cv-preview .cv-header,
    .cv-preview .cv-header * { text-align: center !important; }
    .cv-preview .cv-header h1 { margin: 0 0 .25rem; }
    .cv-preview .cv-titles,
    .cv-preview .cv-contact { margin: 0 0 .15rem; }

    .cv-preview .entry { margin-bottom: .8rem; }
    .cv-preview .employer { font-style: italic; margin: 0 0 .3rem; }
    .cv-preview .referee { margin: 0 0 .4rem; }

    .cv-preview table.entry-line {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin: .5rem 0 0;
    }
    .cv-preview table.entry-line td {
        padding: 0;
        vertical-align: baseline;
        border: 0;
        background: transparent;
    }
    .cv-preview table.entry-line h3 { margin: 0; }
    .cv-preview table.entry-line td.dates {
        width: 1%;
        white-space: nowrap;
        text-align: right !important;
        font-size: .9em;
    }

    /* After the existing .cv-preview rules */
    @media (max-width: 575.98px) {
        .cv-preview {
            font-size: 0.85rem;
            line-height: 1.5;
            padding: 1rem !important;
        }
        .cv-preview h1 { font-size: 1.15rem !important; }
        .cv-preview h2 {
            font-size: 1rem !important;
            margin-top: 1.2em !important;
            border-bottom-width: 1px !important;
        }
        .cv-preview h3 { font-size: 0.95rem !important; }

        /* Dates column too narrow on mobile — force stack */
        .cv-preview table.entry-line {
            display: block;
        }
        .cv-preview table.entry-line tbody,
        .cv-preview table.entry-line tr,
        .cv-preview table.entry-line td {
            display: block;
            width: 100% !important;
            text-align: left !important;
        }
        .cv-preview table.entry-line td.dates {
            white-space: normal !important;
            font-size: 0.8em;
            color: #6b7280;
            margin-top: 0.2em;
        }
    }
</style>
@endsection



@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    // Mirror the mobile menu buttons to the desktop buttons
    (function mirrorMobileButtons() {
        const mirrors = [
            ['editCvBtnMobile',     'editCvBtn'],
            ['copyCvBtnMobile',     'copyCvBtn'],
            ['regenerateBtnMobile', 'regenerateBtn'],
            ['markDoneBtnMobile',   'markDoneBtn'],
        ];
        mirrors.forEach(([mobileId, desktopId]) => {
            const mobileBtn  = document.getElementById(mobileId);
            const desktopBtn = document.getElementById(desktopId);
            if (!mobileBtn || !desktopBtn) return;

            mobileBtn.addEventListener('click', () => {
                // Close the mobile Actions dropdown if it's a Bootstrap dropdown
                try {
                    const dropdownToggle = mobileBtn.closest('.dropdown')?.querySelector('[data-bs-toggle="dropdown"]');
                    if (dropdownToggle) bootstrap.Dropdown.getInstance(dropdownToggle)?.hide();
                } catch (_) { /* ignore */ }

                // Trigger the real action
                desktopBtn.click();
            });
        });
    })();
</script>

<script>
// ═══════════════════════════════════════════════════════════════
// Shared constants — DECLARED ONCE, at the very top
// ═══════════════════════════════════════════════════════════════
const CSRF   = '{{ csrf_token() }}';
const UUID   = '{{ $r['uuid'] }}';
const STATUS = '{{ $status }}';

// ═══════════════════════════════════════════════════════════════
// REUSABLE CONFIRM MODAL — promise-based, Metronic-styled
// ═══════════════════════════════════════════════════════════════
const confirmAction = (function () {
    const modalEl = document.getElementById('confirmActionModal');
    if (!modalEl) {
        // Fallback if the modal isn't in the DOM
        return ({ message = 'Are you sure?' } = {}) => Promise.resolve(window.confirm(message));
    }

    const modal      = new bootstrap.Modal(modalEl);
    const titleEl    = document.getElementById('confirmTitle');
    const msgEl      = document.getElementById('confirmMessage');
    const okBtn      = document.getElementById('confirmOkBtn');
    const cancelBtn  = document.getElementById('confirmCancelBtn');
    const iconEl     = document.getElementById('confirmIcon');

    return function confirmAction({
        title        = 'Confirm',
        message      = 'Are you sure?',
        okText       = 'Confirm',
        okClass      = 'btn-primary',
        icon         = 'ki-information-5',
        iconClass    = 'text-primary',
    } = {}) {
        return new Promise((resolve) => {
            // Reset state each time
            titleEl.textContent = title;
            msgEl.textContent   = message;
            okBtn.className     = `btn ${okClass}`;
            okBtn.querySelector('.indicator-label').textContent = okText;
            okBtn.disabled      = false;
            okBtn.removeAttribute('data-kt-indicator');

            // Icon + color
            iconEl.className = `ki-duotone ${icon} fs-2x ${iconClass}`;
            iconEl.innerHTML = '<span class="path1"></span><span class="path2"></span><span class="path3"></span>';

            let resolved = false;

            const cleanup = (result) => {
                if (resolved) return;
                resolved = true;
                modal.hide();
                // Remove all listeners so they don't fire on the next open
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
                modalEl.removeEventListener('hidden.bs.modal', onHidden);
                resolve(result);
            };

            const onOk     = () => cleanup(true);
            const onCancel = () => cleanup(false);
            const onHidden = () => cleanup(false);

            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);
            modalEl.addEventListener('hidden.bs.modal', onHidden);

            modal.show();
        });
    };
})();

// ═══════════════════════════════════════════════════════════════
// Auto-run AI review if we just arrived from the submit form
// ═══════════════════════════════════════════════════════════════
(function () {
    const params     = new URLSearchParams(window.location.search);
    if (params.get('auto_review') !== '1') return;

    window.history.replaceState({}, '', window.location.pathname + window.location.hash);

    if (STATUS !== 'submitted') return;

    const btn = document.getElementById('runAiReviewBtn');
    if (btn) setTimeout(() => btn.click(), 200);
})();



// ─────────────────────────────────────────────────────────────
// ANSWERS MODAL — init immediately (DOM is ready by the time this runs)
// ─────────────────────────────────────────────────────────────
(function () {
    const answersModalEl = document.getElementById('answersModal');
    if (!answersModalEl) {
        console.warn('[answers-modal] #answersModal not found in DOM');
        return;
    }

    // Guard against double-init
    if (answersModalEl.dataset.initialized === '1') return;
    answersModalEl.dataset.initialized = '1';

    const answersModal = new bootstrap.Modal(answersModalEl);
    window.__answersModal = answersModal;

    // Delegated click — matches class OR fallback ID for compat
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.open-answers-btn, #open-answers-btn, #openAnswersBtn');
        if (!trigger) return;
        e.preventDefault();
        answersModal.show();
    });

    // Auto-open once when status = ai_reviewed and no session flag yet
    if (STATUS === 'ai_reviewed') {
        const key = 'cv_review_answers_modal_' + UUID;
        if (!sessionStorage.getItem(key)) {
            sessionStorage.setItem(key, '1');
            // Small delay ensures Bootstrap backdrop is painted first
            setTimeout(() => answersModal.show(), 300);
        }
    }
})();

// ═══════════════════════════════════════════════════════════════
// ANSWER SUBMIT — one handler, validations + fetch
// ═══════════════════════════════════════════════════════════════
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!form || form.id !== 'answersForm') return;
    e.preventDefault();

    const textareas = form.querySelectorAll('textarea[name^="answers["]');
    const missing = [];

    textareas.forEach(ta => {
        if (!ta.value.trim()) {
            ta.classList.add('is-invalid');
            const field = ta.name.match(/answers\[(.+)\]/)?.[1] || 'field';
            missing.push(field);
        } else {
            ta.classList.remove('is-invalid');
        }
    });

    if (missing.length > 0) {
        window.showToast?.(
            'error',
            'Please fill in all ' + missing.length + ' field(s) before continuing.',
            'Missing Answers'
        );
        return;
    }

    const btn = document.getElementById('answersBtn');
    window.showButtonSpinner?.(btn);

    const fd = new FormData(form);
    const answers = {};
    for (const [k, v] of fd.entries()) {
        const m = k.match(/answers\[(.+)\]/);
        if (m) answers[m[1]] = v;
    }

    fetch(`/cv-review/${UUID}/answers`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ answers }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast?.('success', data.message || 'Answers saved.', 'Saved');
            window.__answersModal?.hide();
            setTimeout(() => location.reload(), 800);
        } else {
            window.showToast?.('error', data.message || 'Failed to save.', 'Error');
            window.hideButtonSpinner?.(btn);
        }
    })
    .catch(() => {
        window.showToast?.('error', 'Network error.', 'Error');
        window.hideButtonSpinner?.(btn);
    });
});

// ═══════════════════════════════════════════════════════════════
// Run AI Review
// ═══════════════════════════════════════════════════════════════
document.getElementById('runAiReviewBtn')?.addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true;
    btn.setAttribute('data-kt-indicator', 'on');

    try {
        const res = await fetch(`{{ route('cv-review.run-ai-review', $r['uuid']) }}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (data.success) {
            window.showToast?.('success', 'AI review complete. Reloading…', 'Success');
            setTimeout(() => window.location.reload(), 600);
        } else {
            window.showToast?.('error', data.message || 'AI review failed.', 'Error');
            btn.disabled = false;
            btn.removeAttribute('data-kt-indicator');
        }
    } catch (e) {
        window.showToast?.('error', 'Network error.', 'Error');
        btn.disabled = false;
        btn.removeAttribute('data-kt-indicator');
    }
});

// ═══════════════════════════════════════════════════════════════
// Rewrite mode cards (AI vs HR)
// ═══════════════════════════════════════════════════════════════
document.querySelectorAll('input[name="rewrite_mode"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
        this.closest('.option-card')?.classList.add('selected');
    });
});
document.querySelector('input[name="rewrite_mode"][value="ai"]')?.dispatchEvent(new Event('change'));

// ═══════════════════════════════════════════════════════════════
// PAYMENT MODAL
// ═══════════════════════════════════════════════════════════════
(function () {
    const payBtn = document.getElementById('payBtn');
    if (!payBtn) return;

    const modalEl     = document.getElementById('paymentModal');
    if (!modalEl) return;

    const modal       = new bootstrap.Modal(modalEl);
    const phoneWrap   = document.getElementById('pm_phone_wrap');
    const phoneInput  = document.getElementById('pm_phone');
    const methodRadios= document.querySelectorAll('input[name="pm_method"]');
    const submitBtn   = document.getElementById('pm_submit_btn');
    const amountEl    = document.getElementById('pm_amount');
    const errorEl     = document.getElementById('pm_error');
    const closeBtn    = document.getElementById('paymentModalClose');
    const warningText = document.getElementById('pm_warning_text');
    let lastSubmitAt = 0;

    function selectedMode() {
        return document.querySelector('input[name="rewrite_mode"]:checked')?.value || 'ai';
    }

    function syncAmountFromCard() {
        // If the amount has been locked by a successful payment init,
        // do NOT overwrite it — the server told us the real amount.
        if (amountEl?.dataset.locked === '1') return;

        const mode = selectedMode();
        const card = document.querySelector(`.option-card[data-mode="${mode}"]`);
        const priceEl = card?.querySelector('.fs-3.fw-bold');
        if (amountEl && priceEl) {
            amountEl.textContent = priceEl.textContent.trim();
        }
    }

    function setWarningText(method) {
        if (!warningText) return;
        warningText.innerHTML = method === 'card'
            ? '<strong>Have your card details ready.</strong> You will be redirected to a secure payment page.'
            : '<strong>Make sure you have enough mobile money credit.</strong> Your balance must cover the amount above.';
    }

    function syncMethod() {
        const method = document.querySelector('input[name="pm_method"]:checked')?.value || 'mobile_money';
        phoneWrap?.classList.toggle('d-none', method === 'card');
        errorEl?.classList.add('d-none');
        setWarningText(method);
    }
    methodRadios.forEach(r => r.addEventListener('change', syncMethod));

    payBtn.addEventListener('click', () => {
        syncAmountFromCard();
        modal.show();
        syncMethod();
        setTimeout(() => {
            if (phoneWrap && !phoneWrap.classList.contains('d-none')) phoneInput.focus();
        }, 300);
    });

    submitBtn.addEventListener('click', async () => {
        // ── Debounce: block double-taps within 3 seconds
        const now = Date.now();
        if (now - lastSubmitAt < 3000) {
            console.log('[payment] Ignoring duplicate submit (3s debounce)');
            return;
        }
        lastSubmitAt = now;

        const method = document.querySelector('input[name="pm_method"]:checked')?.value || 'mobile_money';
        const phone  = phoneInput.value.replace(/[^\d+]/g, '').trim();

        if (method === 'mobile_money') {
            if (!phone) return showError('Please enter your mobile money number.');
            if (!/^\+\d{9,15}$/.test(phone)) return showError('Use the format +256 754 428 612.');
        }

        submitBtn.setAttribute('data-kt-indicator', 'on');
        submitBtn.disabled = true;
        errorEl?.classList.add('d-none');

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
                    mode: selectedMode(),
                }),
            });
            const data = await res.json();

            if (!data.success) {
                showError(data.message || 'Payment could not be started.');
                submitBtn.removeAttribute('data-kt-indicator');
                submitBtn.disabled = false;
                return;
            }

            // ── Lock the amount so no re-sync can overwrite it ────
            if (data.amount_formatted && amountEl) {
                amountEl.textContent   = data.amount_formatted;
                amountEl.dataset.locked = '1';
                console.log('[payment] Amount locked at', data.amount_formatted);
            }

            if (data.amount_formatted && amountEl) amountEl.textContent = data.amount_formatted;

            if (data.is_free) {
                modal.hide();
                window.showToast?.('success', data.message, 'Free');
                setTimeout(() => location.reload(), 1200);
                return;
            }

            if (data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }

            // Mobile money — poll
            showInfo('A prompt has been sent to your phone. Enter your PIN.');
            submitBtn.removeAttribute('data-kt-indicator');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Waiting...';
            // ── Poll a LIGHTWEIGHT status endpoint every 3s ──────
            // The dedicated /cv-review/{uuid}/status route returns
            // just { status, is_paid, ... } from the proxy — not HTML,
            // not the full JSON. Reliable and cheap.
            showInfo(
                'A payment prompt has been sent to your phone. Enter your PIN to complete payment. ' +
                'Waiting for confirmation…'
            );

            submitBtn.removeAttribute('data-kt-indicator');
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>Waiting for payment…';

            const startedAt = Date.now();
            const POLL_MS   = 3000;
            const MAX_MS    = 5 * 60 * 1000;   // 5-minute hard ceiling

            const poll = setInterval(async () => {
                // Timeout → stop spinning, show a clear message
                if (Date.now() - startedAt > MAX_MS) {
                    clearInterval(poll);
                    showError(
                        'We have not received the confirmation yet. ' +
                        'If you were charged, the payment will appear shortly. ' +
                        'Refresh this page in a moment.'
                    );
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = payButtonHTML();
                    return;
                }

                try {
                    const r = await fetch(`{{ url('cv-review') }}/${UUID}/status`, {
                        headers: { 'Accept': 'application/json' },
                        // bypass any aggressive cache
                        cache: 'no-store',
                    });

                    if (!r.ok) {
                        // 401/404/500 — keep polling until timeout
                        return;
                    }

                    const b = await r.json();
                    const current = b?.status;

                    // Still waiting on the user's PIN / webhook
                    if (!current || current === 'awaiting_payment') {
                        return;
                    }

                    // ── State changed → stop polling, close modal ──
                    clearInterval(poll);
                    modal.hide();

                    // Success states: paid_ai, paid_human, paid, generating,
                    // generated, completed → redirect to the letter page
                    if (current === 'failed' || current === 'cancelled') {
                        window.showToast?.(
                            'error',
                            'Payment was received but the request could not continue. Opening the page…',
                            'Notice'
                        );
                    } else {
                        window.showToast?.(
                            'success',
                            'Payment confirmed. Loading your CV…',
                            'Success'
                        );
                    }

                    setTimeout(() => {
                        window.location.href = `{{ url('cv-review') }}/${UUID}`;
                    }, 700);

                } catch (e) {
                    // Network blip — keep polling
                }
            }, POLL_MS);
        } catch (e) {
            showError('Something went wrong: ' + e.message);
            submitBtn.removeAttribute('data-kt-indicator');
            submitBtn.disabled = false;
        }
    });

    closeBtn?.addEventListener('click', () => {
        submitBtn.disabled = false;
        submitBtn.removeAttribute('data-kt-indicator');
        submitBtn.innerHTML = payButtonHTML();
        errorEl?.classList.add('d-none');

        // Release the amount lock so the next open can sync from the card
        if (amountEl) delete amountEl.dataset.locked;
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        if (amountEl) delete amountEl.dataset.locked;
    });

    function showError(msg) {
        if (!errorEl) return;
        errorEl.className = 'alert alert-light-danger mt-4 mb-0';
        errorEl.textContent = msg;
        errorEl.classList.remove('d-none');
    }
    function showInfo(msg) {
        if (!errorEl) return;
        errorEl.className = 'alert alert-light-info mt-4 mb-0';
        errorEl.textContent = msg;
        errorEl.classList.remove('d-none');
    }
    function payButtonHTML() {
        return '<i class="ki-duotone ki-lock fs-3 me-2"><span class="path1"></span><span class="path2"></span></i> Pay Now';
    }
})();
</script>


{{-- Second script block: AI rewrite + polling --}}
<script>
(function () {
    'use strict';

    // ═══════════════════════════════════════════════════════════════
    // POLLING — reload while AI is generating
    // ═══════════════════════════════════════════════════════════════
    (function pollWhileGenerating() {
        const needsPolling = STATUS === 'paid_ai' || STATUS === 'generating';

        if (!needsPolling) {
            sessionStorage.removeItem('cv_rewrite_poll_' + UUID);
            return;
        }

        const KEY     = 'cv_rewrite_poll_' + UUID;
        const tries   = parseInt(sessionStorage.getItem(KEY) || '0', 10);
        const MAX     = 60;
        const POLL_MS = 3000;

        if (tries >= MAX) {
            sessionStorage.removeItem(KEY);
            const loading = document.getElementById('rewriteLoading');
            if (loading) {
                loading.innerHTML = `
                    <div class="card-body text-center py-15">
                        <i class="ki-duotone ki-information-5 fs-3x text-warning mb-3 d-block">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <h3 class="fw-bold mb-2">Taking longer than usual</h3>
                        <p class="text-muted mb-4">
                            Generation is still running. Refresh the page in a minute,
                            or contact support if it stays stuck.
                        </p>
                        <button class="btn btn-primary" onclick="location.reload()">Refresh</button>
                    </div>`;
            }
            return;
        }

        sessionStorage.setItem(KEY, String(tries + 1));
        setTimeout(() => window.location.reload(), POLL_MS);
    })();

    // ═══════════════════════════════════════════════════════════════
    // EDIT CV (TinyMCE) — REGISTER UNCONDITIONALLY
    //
    // These handlers MUST be registered even when #aiRewriteSection
    // isn't in the DOM, because on mobile the desktop mirror uses
    // them. Registering inside `if (section)` was the bug.
    // ═══════════════════════════════════════════════════════════════
    const cvView        = document.getElementById('cvView');
    const cvEdit        = document.getElementById('cvEdit');
    const cvEditor      = document.getElementById('cvEditor');
    const cvContentEl   = document.querySelector('#cvView .cv-preview');

    let editorInstance     = null;
    let editorReadyPromise = null;

    function initEditor(initialHtml) {
        if (!cvEditor) return Promise.resolve(null);

        if (editorInstance && editorReadyPromise) {
            return editorReadyPromise.then(editor => {
                if (editor && editor.setContent) editor.setContent(initialHtml || '');
                return editor;
            });
        }

        if (typeof tinymce === 'undefined') {
            cvEditor.value = htmlToPlainText(initialHtml);
            return Promise.resolve(null);
        }

        cvEditor.value = initialHtml || '';

        editorReadyPromise = new Promise((resolve) => {
            tinymce.init({
                target: cvEditor,
                height: 600,
                menubar: false,
                statusbar: true,
                branding: false,
                promotion: false,
                plugins: 'lists link code wordcount',
                toolbar: 'undo redo | blocks | bold italic | bullist numlist | link | removeformat | code',
                block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3',
                content_css: "{{ asset('css/cv-document.css') }}",
                body_class: 'cv-doc',
                extended_valid_elements: 'div[class],p[class],span[class],table[class],tr,td[class],h1,h2,h3,em,strong',
                valid_classes: 'cv-header,cv-titles,cv-contact,entry,entry-line,dates,employer,referee',
                invalid_elements: 'script,style',
                paste_as_text: true,
                setup: (editor) => {
                    editor.on('init', () => {
                        editorInstance = editor;
                        if (initialHtml && !editor.getContent().trim()) editor.setContent(initialHtml);
                        resolve(editor);
                    });
                },
            }).catch(err => {
                console.warn('TinyMCE failed to load, falling back to plain textarea:', err);
                cvEditor.value = htmlToPlainText(initialHtml);
                resolve(null);
            });
        });

        return editorReadyPromise;
    }

    function getEditorContent() {
        if (editorInstance && typeof editorInstance.getContent === 'function') return editorInstance.getContent();
        return cvEditor?.value || '';
    }

    function htmlToPlainText(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        tmp.querySelectorAll('p, li, h1, h2, h3').forEach(el => el.appendChild(document.createTextNode('\n\n')));
        return (tmp.innerText || tmp.textContent || '').trim();
    }

    function getCurrentCvHtml() {
        if (!cvContentEl) return '';
        return cvContentEl.innerHTML.trim();
    }

    // ── Edit ────────────────────────────────────────────────────
    document.getElementById('editCvBtn')?.addEventListener('click', () => {
        if (!cvEdit || !cvView) return;

        cvView.classList.add('d-none');
        cvEdit.classList.remove('d-none');
        cvEdit.scrollIntoView({ behavior: 'smooth', block: 'start' });

        initEditor(getCurrentCvHtml());
    });

    // ── Cancel ──────────────────────────────────────────────────
    document.getElementById('cancelEditsBtn')?.addEventListener('click', () => {
        cvEdit?.classList.add('d-none');
        cvView?.classList.remove('d-none');
    });

    // ── Save edits ──────────────────────────────────────────────
    document.getElementById('saveEditsBtn')?.addEventListener('click', async function () {
        const btn = this;
        btn.disabled = true;
        btn.setAttribute('data-kt-indicator', 'on');
        const lbl = btn.querySelector('.indicator-label');
        const prg = btn.querySelector('.indicator-progress');
        lbl?.classList.add('d-none');
        prg?.classList.remove('d-none');

        try {
            await editorReadyPromise;
            const html = getEditorContent().trim();

            if (!html || html.replace(/<[^>]*>/g, '').trim().length < 50) {
                window.showToast?.('error', 'CV looks too short. Please keep at least a few sentences.', 'Too short');
                btn.disabled = false;
                btn.removeAttribute('data-kt-indicator');
                lbl?.classList.remove('d-none');
                prg?.classList.add('d-none');
                return;
            }

            const res = await fetch(`{{ url('cv-review') }}/${UUID}/rewrite/save`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ content: html }),
            });
            const data = await res.json();

            if (!data.success) {
                window.showToast?.('error', data.message || 'Could not save your edits.', 'Save failed');
                btn.disabled = false;
                btn.removeAttribute('data-kt-indicator');
                lbl?.classList.remove('d-none');
                prg?.classList.add('d-none');
                return;
            }

            window.showToast?.('success', 'Your edits have been saved.', 'Saved');
            setTimeout(() => window.location.reload(), 700);
        } catch (e) {
            window.showToast?.('error', 'Network error: ' + e.message, 'Error');
            btn.disabled = false;
            btn.removeAttribute('data-kt-indicator');
            lbl?.classList.remove('d-none');
            prg?.classList.add('d-none');
        }
    });

    // ── Reset to AI ─────────────────────────────────────────────
    document.getElementById('resetToAiBtn')?.addEventListener('click', async function () {
        const ok = await confirmAction({
            title:    'Reset to AI version?',
            message:  'Discard all your edits and restore the last AI-generated version?',
            okText:   'Reset',
            okClass:  'btn-danger',
            icon:     'ki-arrows-circle',
            iconClass:'text-danger',
        });
        if (!ok) return;

        const btn = this;
        btn.disabled = true;
        btn.setAttribute('data-kt-indicator', 'on');

        try {
            const res = await fetch(`{{ url('cv-review') }}/${UUID}/rewrite/reset`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (!data.success) {
                window.showToast?.('error', data.message || 'Could not reset.', 'Reset failed');
                btn.disabled = false;
                btn.removeAttribute('data-kt-indicator');
                return;
            }

            window.showToast?.('success', 'Restored to the AI version.', 'Reset');
            setTimeout(() => window.location.reload(), 700);
        } catch (e) {
            window.showToast?.('error', 'Network error: ' + e.message, 'Error');
            btn.disabled = false;
            btn.removeAttribute('data-kt-indicator');
        }
    });

    // ── Copy ────────────────────────────────────────────────────
    document.getElementById('copyCvBtn')?.addEventListener('click', async function () {
        const text = cvContentEl?.innerText || '';
        if (!text.trim()) {
            window.showToast?.('error', 'Nothing to copy.', 'Empty');
            return;
        }
        try {
            await navigator.clipboard.writeText(text);
            window.showToast?.('success', 'Copied to clipboard.', 'Copied');
            const original = this.innerHTML;
            this.innerHTML = '<i class="ki-duotone ki-check fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Copied!';
            setTimeout(() => { this.innerHTML = original; }, 1800);
        } catch (e) {
            window.showToast?.('error', 'Browser blocked the copy.', 'Error');
        }
    });

    // ── Regenerate ──────────────────────────────────────────────
    document.getElementById('regenerateBtn')?.addEventListener('click', async function () {
        const isEdited = @json($r['rewrite_is_user_edited'] ?? false);
        const ok = await confirmAction({
            title:    'Regenerate CV?',
            message:  isEdited
                        ? 'This will replace your manual edits with a fresh AI version. Continue?'
                        : 'This uses one of your retries. Continue?',
            okText:   'Regenerate',
            okClass:  'btn-warning',
            icon:     'ki-arrows-circle',
            iconClass:'text-warning',
        });
        if (!ok) return;

        try {
            const res = await fetch(`{{ url('cv-review') }}/${UUID}/rewrite/regenerate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (data.success) {
                window.showToast?.('success', 'Regenerating…', 'Started');
                sessionStorage.removeItem('cv_rewrite_poll_' + UUID);
                setTimeout(() => location.reload(), 800);
            } else {
                window.showToast?.('error', data.message || 'Could not start.', 'Error');
            }
        } catch (e) {
            window.showToast?.('error', 'Network error.', 'Error');
        }
    });

    // ── Mark as Done ────────────────────────────────────────────
    document.getElementById('markDoneBtn')?.addEventListener('click', async function () {
        const ok = await confirmAction({
            title:    'Mark CV as complete?',
            message:  'You will not be able to edit or regenerate this CV after marking it done. Download your PDF first if you want a copy.',
            okText:   'Mark as Done',
            okClass:  'btn-success',
            icon:     'ki-check-circle',
            iconClass:'text-success',
        });
        if (!ok) return;

        this.disabled = true;
        this.setAttribute('data-kt-indicator', 'on');

        try {
            const res = await fetch(`{{ url('cv-review') }}/${UUID}/rewrite/complete`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (data.success) {
                window.showToast?.('success', 'Marked as done. Thank you!', 'Success');
                setTimeout(() => location.reload(), 800);
            } else {
                window.showToast?.('error', data.message || 'Failed.', 'Error');
                this.disabled = false;
                this.removeAttribute('data-kt-indicator');
            }
        } catch (e) {
            window.showToast?.('error', 'Network error.', 'Error');
            this.disabled = false;
            this.removeAttribute('data-kt-indicator');
        }
    });

    // ═══════════════════════════════════════════════════════════════
    // Retry generation (failed state)
    // ═══════════════════════════════════════════════════════════════
    document.getElementById('retryGenerationBtn')?.addEventListener('click', async function () {
        const ok = await confirmAction({
            title:    'Retry writing your CV?',
            message:  'We\'ll start a fresh attempt. Your original payment is still valid.',
            okText:   'Try Again',
            okClass:  'btn-warning',
            icon:     'ki-arrows-circle',
            iconClass:'text-warning',
        });
        if (!ok) return;

        const btn = this;
        btn.disabled = true;
        btn.setAttribute('data-kt-indicator', 'on');

        try {
            const res = await fetch(`{{ url('cv-review') }}/${UUID}/rewrite/regenerate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (data.success) {
                window.showToast?.('success', 'Retrying generation…', 'Started');
                sessionStorage.removeItem('cv_rewrite_poll_' + UUID);
                setTimeout(() => location.reload(), 700);
            } else {
                window.showToast?.('error', data.message || 'Could not retry.', 'Error');
                btn.disabled = false;
                btn.removeAttribute('data-kt-indicator');
            }
        } catch (e) {
            window.showToast?.('error', 'Network error.', 'Error');
            btn.disabled = false;
            btn.removeAttribute('data-kt-indicator');
        }
    });

    // ── Promote original (failed state) ─────────────────────────
    document.getElementById('promoteOriginalBtn')?.addEventListener('click', async function () {
        const ok = await confirmAction({
            title:    'Restore the last saved version?',
            message:  'We\'ll replace the failed attempt with the last successfully written version. You can then edit and download it.',
            okText:   'Restore & Edit',
            okClass:  'btn-primary',
            icon:     'ki-document',
            iconClass:'text-primary',
        });
        if (!ok) return;

        const btn = this;
        btn.disabled = true;
        btn.setAttribute('data-kt-indicator', 'on');

        try {
            const res = await fetch(`{{ url('cv-review') }}/${UUID}/rewrite/promote-original`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (data.success) {
                window.showToast?.('success', 'Restored. Opening the CV so you can edit it…', 'Ready');
                setTimeout(() => location.reload(), 700);
            } else {
                window.showToast?.('error', data.message || 'Could not restore.', 'Error');
                btn.disabled = false;
                btn.removeAttribute('data-kt-indicator');
            }
        } catch (e) {
            window.showToast?.('error', 'Network error.', 'Error');
            btn.disabled = false;
            btn.removeAttribute('data-kt-indicator');
        }
    });

    // ═══════════════════════════════════════════════════════════════
    // Revision modal
    // ═══════════════════════════════════════════════════════════════
    (function () {
        const revisionModalEl = document.getElementById('revisionModal');
        if (!revisionModalEl) return;
        const revisionModal = new bootstrap.Modal(revisionModalEl);

        document.getElementById('openRevisionEditBtn')?.addEventListener('click', () => revisionModal.show());

        document.getElementById('revisionForm')?.addEventListener('submit', async function (e) {
            e.preventDefault();

            const notes = this.querySelector('textarea[name="notes"]').value.trim();
            if (!notes) {
                window.showToast?.('error', 'Please describe what you want changed.', 'Missing Info');
                return;
            }

            const ok = await confirmAction({
                title:    'Submit revision request?',
                message:  'Your HR expert will be notified and will re-deliver an updated CV.',
                okText:   'Submit',
                okClass:  'btn-warning',
                icon:     'ki-send',
                iconClass:'text-warning',
            });
            if (!ok) return;

            const btn = document.getElementById('revisionBtn');
            window.showButtonSpinner?.(btn);

            try {
                const res = await fetch(`/cv-review/${UUID}/revision`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ notes }),
                });
                const data = await res.json();

                if (data.success) {
                    window.showToast?.('success', data.message || 'Revision requested.', 'Sent');
                    revisionModal.hide();
                    setTimeout(() => location.reload(), 800);
                } else {
                    window.showToast?.('error', data.message || 'Failed.', 'Error');
                    window.hideButtonSpinner?.(btn);
                }
            } catch (err) {
                window.showToast?.('error', 'Network error.', 'Error');
                window.hideButtonSpinner?.(btn);
            }
        });
    })();

})();
</script>


@endpush