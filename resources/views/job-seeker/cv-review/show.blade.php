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

    {{-- Timeline --}}
    <div class="card card-flush mb-6">
        <div class="card-header"><h3 class="card-title">Progress</h3></div>
        <div class="card-body">
            <div class="d-flex justify-content-between position-relative" style="overflow-x:auto;">
                @foreach($timeline as $key => $step)
                    @php
                        $idx = array_search($key, $statusOrder);
                        $done = $idx < $currentIdx || $status === 'completed';
                        $active = $idx === $currentIdx;
                    @endphp
                    <div class="text-center flex-fill position-relative" style="min-width:120px;">
                        <div class="symbol symbol-40px symbol-circle mx-auto
                            {{ $done ? 'bg-success' : ($active ? 'bg-primary' : 'bg-light-secondary') }}"
                             style="z-index:2;position:relative;">
                            @if($done)
                                <i class="ki-duotone ki-check text-white fs-3"><span class="path1"></span><span class="path2"></span></i>
                            @elseif($active)
                                <i class="ki-duotone ki-time text-white fs-3"><span class="path1"></span><span class="path2"></span></i>
                            @else
                                <span class="text-muted fw-bold">{{ $idx + 1 }}</span>
                            @endif
                        </div>
                        <div class="fw-bold mt-2 fs-7">{{ $step[0] }}</div>
                        <div class="text-muted fs-8 px-2">{{ $step[1] }}</div>
                    </div>
                @endforeach
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
                <div class="table-responsive">
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
                                        @if($s === 'ok')        <span class="badge badge-light-success">✅ OK</span>
                                        @elseif($s === 'weak')  <span class="badge badge-light-warning">⚠️ Weak</span>
                                        @else                   <span class="badge badge-light-danger">❌ Missing</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $sec['note'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(!empty($missingFields))
                <div class="mt-5">
                    <div class="fw-bold mb-2">We noticed these are missing:</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($missingFields as $mf)
                            <span class="badge badge-light-danger">{{ $mf }}</span>
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
            <div class="fs-2x fw-bold text-primary mb-5">{{ $r['formatted_amount'] }}</div>
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
document.getElementById('payBtn')?.addEventListener('click', function () {
    const btn = this;
    window.showButtonSpinner(btn);

    fetch(`/cv-review/${UUID}/pay`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.showToast(
                    'success',
                    'Payment initialized. Reference: ' + (data.payment_reference || 'N/A'),
                    'Ready'
                );
                setTimeout(() => location.reload(), 2000);
            }
        } else {
            window.showToast('error', data.message || 'Payment init failed', 'Error');
        }
    })
    .catch(() => window.showToast('error', 'Payment init failed', 'Error'))
    .finally(() => window.hideButtonSpinner(btn));
});

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