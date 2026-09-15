@extends('layouts.admin')

@section('title', 'My CV Reviews')
@section('page_title', 'My CV Reviews')

@section('content')
@php
    // ── Limit state (passed from controller) ──────────────────────
    $activeCount = $meta['active_count'] ?? 0;
    $maxActive   = $meta['max_active']   ?? 3;
    $canCreate   = $meta['can_create']   ?? ($activeCount < $maxActive);
    $slotsLeft   = max(0, $maxActive - $activeCount);

    /**
     * Where each status sits in the journey.
     */
    $statusMap = [
        'submitted' => [
            'step'  => 1,
            'label' => 'Submitted',
            'where' => 'Just received. Our team will analyse it shortly.',
            'color' => 'info',
            'next'  => 'Wait a moment — the AI gap review will appear here soon.',
        ],
        'ai_reviewed' => [
            'step'  => 2,
            'label' => 'AI Review Ready',
            'where' => 'Free AI gap review is ready for you to read.',
            'color' => 'info',
            'next'  => 'Answer the gap questions to unlock payment.',
        ],
        'awaiting_payment' => [
            'step'  => 3,
            'label' => 'Awaiting Payment',
            'where' => 'Stopped here — waiting for you to complete payment.',
            'color' => 'warning',
            'next'  => 'Complete payment so our expert can start rewriting your CV.',
        ],
        'paid' => [
            'step'  => 4,
            'label' => 'Paid',
            'where' => 'Payment confirmed. Our team is preparing your file.',
            'color' => 'primary',
            'next'  => 'We will assign a CV expert within the SLA window.',
        ],
        'in_progress' => [
            'step'  => 5,
            'label' => 'In Progress',
            'where' => 'A career expert is actively rewriting your CV.',
            'color' => 'primary',
            'next'  => 'Sit tight. You will get a notification the moment it is ready.',
        ],
        'delivered' => [
            'step'  => 6,
            'label' => 'Delivered',
            'where' => 'Your revised CV has been delivered.',
            'color' => 'success',
            'next'  => 'Download your CV or request a revision within 3 days.',
        ],
        'revision_requested' => [
            'step'  => 6,
            'label' => 'Revision In Progress',
            'where' => 'You requested a revision. Our team is on it.',
            'color' => 'warning',
            'next'  => 'We will re-deliver shortly.',
        ],
        'completed' => [
            'step'  => 7,
            'label' => 'Completed',
            'where' => 'All done. Thank you for choosing us!',
            'color' => 'success',
            'next'  => 'You can start a new review any time.',
        ],
        'cancelled' => [
            'step'  => 0,
            'label' => 'Cancelled',
            'where' => 'This request was cancelled.',
            'color' => 'danger',
            'next'  => null,
        ],
    ];
@endphp

<div class="container py-6">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-6 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1">My CV Reviews</h2>
            <div class="text-muted">
                Track every review you've submitted and see exactly where it stands.
            </div>
        </div>

        {{-- ⬅️ NEW: button switches to disabled when at limit --}}
        @if($canCreate)
            <a href="{{ route('cv-review.index') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-3 me-1">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                Start New Review
            </a>
        @else
            <button class="btn btn-secondary" disabled
                    title="Delete a review to free up a slot">
                <i class="ki-duotone ki-block fs-3 me-1">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                Limit Reached ({{ $activeCount }}/{{ $maxActive }})
            </button>
        @endif
    </div>

    {{-- ⬅️ NEW: Limit warning banner --}}
    @if(!$canCreate)
        <div class="alert alert-warning d-flex align-items-center mb-6">
            <i class="ki-duotone ki-information-5 fs-2x me-4">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            <div class="flex-grow-1">
                <div class="fw-bold">You have reached the maximum of {{ $maxActive }} active CV reviews</div>
                <div class="text-muted">
                    To submit a new CV for review, delete one of the in-progress reviews below.
                    Paid and completed reviews don't count toward the limit but can't be deleted.
                </div>
            </div>
        </div>
    @elseif($slotsLeft === 1)
        <div class="alert alert-info d-flex align-items-center mb-6">
            <i class="ki-duotone ki-information-5 fs-2x me-4">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            <div>
                <div class="fw-bold">1 slot remaining</div>
                <div class="text-muted">
                    You can submit 1 more CV for review before reaching the limit of {{ $maxActive }}.
                </div>
            </div>
        </div>
    @endif

    {{-- Filter tabs --}}
    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
        <li class="nav-item">
            <a class="nav-link {{ $status === '' ? 'active' : '' }}"
               href="{{ route('cv-review.my') }}">
                All
                <span class="badge badge-light-primary ms-2">{{ $counts['all'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'awaiting_payment' ? 'active' : '' }}"
               href="{{ route('cv-review.my', ['status' => 'awaiting_payment']) }}">
                Needs Action
                @if($counts['awaiting_payment'] > 0)
                    <span class="badge badge-light-warning ms-2">{{ $counts['awaiting_payment'] }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'in_progress' ? 'active' : '' }}"
               href="{{ route('cv-review.my', ['status' => 'in_progress']) }}">
                In Progress
                @if($counts['in_progress'] > 0)
                    <span class="badge badge-light-primary ms-2">{{ $counts['in_progress'] }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'delivered' ? 'active' : '' }}"
               href="{{ route('cv-review.my', ['status' => 'delivered']) }}">
                Delivered
                @if($counts['delivered'] > 0)
                    <span class="badge badge-light-success ms-2">{{ $counts['delivered'] }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'completed' ? 'active' : '' }}"
               href="{{ route('cv-review.my', ['status' => 'completed']) }}">
                Completed
                @if($counts['completed'] > 0)
                    <span class="badge badge-light-success ms-2">{{ $counts['completed'] }}</span>
                @endif
            </a>
        </li>
    </ul>

    {{-- List --}}
    @if(empty($requests))
        <div class="card card-flush">
            <div class="card-body text-center py-15">
                <i class="ki-duotone ki-file fs-3x text-muted mb-4 d-block">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <h4 class="fw-bold mb-2">No CV reviews yet</h4>
                <p class="text-muted mb-5">
                    Upload your CV and get a free AI gap review in seconds.
                </p>
                @if($canCreate)
                    <a href="{{ route('cv-review.index') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-rocket fs-3 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Start My First Review
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-4">
            @foreach($requests as $r)
                @php
                    $metaRow = $statusMap[$r['status']] ?? [
                        'step' => 0, 'label' => ucfirst($r['status']),
                        'where' => 'Unknown status.', 'color' => 'secondary', 'next' => null,
                    ];
                    $step = $metaRow['step'];
                    $totalSteps = 7;

                    // Progress percentage
                    $progress = $step > 0 ? round(($step / $totalSteps) * 100) : 0;

                    // Is this something the seeker needs to act on?
                    $needsAction = in_array($r['status'], ['ai_reviewed', 'awaiting_payment'], true);

                    // ⬅️ NEW: Can this request be deleted?
                    // Blocked once work has started — protects the audit trail.
                    $canDelete = !in_array($r['status'], [
                        'paid', 'in_progress', 'delivered', 'revision_requested', 'completed',
                    ], true);

                    // Reason shown when the delete button is disabled
                    $deleteBlockReason = match($r['status']) {
                        'paid'               => 'Cannot delete — payment already received',
                        'in_progress'        => 'Cannot delete — work has started',
                        'delivered'          => 'Cannot delete — CV has been delivered',
                        'revision_requested' => 'Cannot delete — a revision is in progress',
                        'completed'          => 'Cannot delete — request is complete',
                        default              => 'Cannot delete',
                    };
                @endphp

                <div class="card card-flush {{ $needsAction ? 'border border-warning border-2' : '' }}"
                     data-request-uuid="{{ $r['uuid'] }}">
                    <div class="card-body">

                        {{-- Row 1: Title + Status --}}
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                            <div class="flex-grow-1 min-w-250px">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fw-bold fs-5">
                                        {{ $r['service_name'] ?? 'CV Review' }}
                                    </span>
                                    <span class="badge badge-light-{{ $metaRow['color'] }} fs-7">
                                        {{ $metaRow['label'] }}
                                    </span>
                                    @if($needsAction)
                                        <span class="badge badge-warning fs-7">
                                            <i class="ki-duotone ki-information-5 fs-6 me-1">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            </i>
                                            Action Needed
                                        </span>
                                    @endif
                                </div>
                                <div class="text-muted fs-7">
                                    #{{ substr($r['uuid'], 0, 8) }}
                                    &middot; {{ \Carbon\Carbon::parse($r['created_at'])->format('M d, Y H:i') }}
                                    @if(!empty($r['target_job_title']))
                                        &middot; 🎯 {{ $r['target_job_title'] }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fs-4 fw-bold text-primary">
                                    {{ $r['formatted_amount'] ?? '—' }}
                                </div>
                                <div class="text-muted fs-8">Amount</div>
                            </div>
                        </div>

                        {{-- Row 2: Progress bar --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-muted fs-7">
                                    Step {{ $step }} of {{ $totalSteps }}
                                </div>
                                <div class="text-muted fs-7">
                                    {{ $progress }}%
                                </div>
                            </div>
                            <div class="progress h-8px">
                                <div class="progress-bar bg-{{ $metaRow['color'] }}"
                                     role="progressbar"
                                     style="width: {{ $progress }}%"
                                     aria-valuenow="{{ $progress }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                        </div>

                        {{-- Row 3: Where it stopped + next action --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-7">
                                <div class="d-flex align-items-start gap-3 p-4 bg-light-{{ $metaRow['color'] }} rounded">
                                    <i class="ki-duotone ki-information-5 fs-2x text-{{ $metaRow['color'] }} mt-1">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                    </i>
                                    <div>
                                        <div class="fw-bold mb-1">Where it stopped</div>
                                        <div class="text-muted">{{ $metaRow['where'] }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                @if($metaRow['next'])
                                    <div class="d-flex align-items-start gap-3 p-4 bg-light-secondary rounded h-100">
                                        <i class="ki-duotone ki-arrow-right fs-2x text-gray-600 mt-1">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        <div>
                                            <div class="fw-bold mb-1">Next step</div>
                                            <div class="text-muted">{{ $metaRow['next'] }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center justify-content-center p-4 bg-light-secondary rounded h-100 text-muted">
                                        No further action required
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Row 4: Actions --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pt-4 border-top">
                            <div class="d-flex gap-2 flex-wrap">
                                @if(!empty($r['original_cv_url']))
                                    <a href="{{ $r['original_cv_url'] }}" target="_blank"
                                       class="btn btn-sm btn-light">
                                        <i class="ki-duotone ki-file-down fs-4 me-1">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        Original CV
                                    </a>
                                @endif
                                @if(!empty($r['delivered_cv_url']))
                                    <a href="{{ $r['delivered_cv_url'] }}" target="_blank"
                                       class="btn btn-sm btn-light-success">
                                        <i class="ki-duotone ki-file-down fs-4 me-1">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        Download Revised CV
                                    </a>
                                @endif
                            </div>

                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <a href="{{ route('cv-review.show', $r['uuid']) }}"
                                   class="btn btn-sm btn-light-primary">
                                    View Details
                                    <i class="ki-duotone ki-right fs-4 ms-1">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </a>

                                @if($needsAction && $r['status'] === 'awaiting_payment')
                                    <a href="{{ route('cv-review.show', $r['uuid']) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="ki-duotone ki-dollar fs-4 me-1">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        Pay Now
                                    </a>
                                @elseif($needsAction && $r['status'] === 'ai_reviewed')
                                    <a href="{{ route('cv-review.show', $r['uuid']) }}"
                                       class="btn btn-sm btn-primary">
                                        Fill In The Gaps
                                        <i class="ki-duotone ki-right fs-4 ms-1">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    </a>
                                @endif

                                {{-- ⬅️ NEW: Delete button --}}
                                @if($canDelete)
                                    <button type="button"
                                            class="btn btn-sm btn-light-danger"
                                            data-action="delete-request"
                                            data-uuid="{{ $r['uuid'] }}"
                                            data-label="#{{ substr($r['uuid'], 0, 8) }}"
                                            title="Delete this request and its CV files">
                                        <i class="ki-duotone ki-trash fs-4">
                                            <span class="path1"></span><span class="path2"></span>
                                            <span class="path3"></span><span class="path4"></span>
                                            <span class="path5"></span>
                                        </i>
                                    </button>
                                @else
                                    <button type="button"
                                            class="btn btn-sm btn-light"
                                            disabled
                                            title="{{ $deleteBlockReason }}">
                                        <i class="ki-duotone ki-lock fs-4 text-muted">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if(($meta['last_page'] ?? 1) > 1)
            <div class="d-flex justify-content-between align-items-center mt-6">
                <div class="text-muted">
                    Page {{ $meta['current_page'] }} of {{ $meta['last_page'] }}
                </div>
                <nav>
                    <ul class="pagination m-0">
                        <li class="page-item {{ $meta['current_page'] <= 1 ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ route('cv-review.my', ['status' => $status, 'page' => $meta['current_page'] - 1]) }}">
                                Previous
                            </a>
                        </li>
                        <li class="page-item {{ $meta['current_page'] >= $meta['last_page'] ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ route('cv-review.my', ['status' => $status, 'page' => $meta['current_page'] + 1]) }}">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    @endif

</div>
@endsection

@push('scripts')
<script>
/* ============================================================
 * Delete handler for CV review requests
 * The delete button is rendered in the blade directly on this
 * page, so no delegation is needed — but I'm using it anyway
 * to survive future re-renders.
 * ============================================================ */
document.addEventListener('click', async function (e) {
    const trigger = e.target.closest('[data-action="delete-request"]');
    if (!trigger) return;

    e.preventDefault();

    const uuid  = trigger.dataset.uuid;
    const label = trigger.dataset.label || 'this request';

    if (!confirm(
        `Delete ${label}?\n\n` +
        `This will permanently remove the request AND its uploaded CV file(s). ` +
        `This cannot be undone.`
    )) {
        return;
    }

    const original = trigger.innerHTML;
    trigger.disabled = true;
    trigger.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const res = await fetch(`/cv-review/${uuid}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                             || '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await res.json();

        if (data.success) {
            window.showToast('success', data.message || 'Request deleted.', 'Deleted');

            // Fade out and remove the card
            const card = trigger.closest('[data-request-uuid]') || trigger.closest('.card');
            if (card) {
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity = '0';
                card.style.transform = 'translateY(-10px)';

                setTimeout(() => {
                    card.remove();

                    // If no cards left, reload to show empty state and refresh counts
                    if (document.querySelectorAll('[data-request-uuid]').length === 0) {
                        window.location.reload();
                    } else {
                        // Update the "Limit Reached" state without full reload
                        // by simply reloading after a short delay so the banner is accurate
                        setTimeout(() => window.location.reload(), 200);
                    }
                }, 300);
            } else {
                window.location.reload();
            }
        } else {
            window.showToast('error', data.message || 'Failed to delete.', 'Error');
            trigger.disabled = false;
            trigger.innerHTML = original;
        }
    } catch (err) {
        console.error(err);
        window.showToast('error', 'Something went wrong.', 'Error');
        trigger.disabled = false;
        trigger.innerHTML = original;
    }
});
</script>
@endpush