@extends('layouts.admin')

@section('title', $letter['letter_type_label'])
@section('page_title', $letter['letter_type_label'])

@section('content')
<div class="container py-6">
    <div class="card card-flush">

        <div class="card-header py-5">
            <div>
                <h3 class="fw-bold mb-0">{{ $letter['letter_type_label'] }}</h3>
                <div class="text-muted fs-7">
                    {{ $letter['job_title'] }} — {{ $letter['company_name'] }}
                </div>
            </div>
            <div class="card-toolbar gap-2">
                @if($letter['status'] === 'generated')
                    <a href="{{ route('letters.download', $letter['uuid']) }}"
                       class="btn btn-sm btn-primary">
                        <i class="ki-duotone ki-file-down fs-3 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Download PDF
                    </a>
                @endif
                <a href="{{ route('letters.index') }}" class="btn btn-sm btn-light">Back</a>
            </div>
        </div>

        <div class="card-body pt-0">

            <div class="d-flex gap-2 mb-6">
                {!! $letter['status_badge'] !!}
                @if($letter['is_paid'])
                    <span class="badge badge-light-success">Paid</span>
                @endif
            </div>

            @if($letter['status'] === 'pending_payment')
                <div class="alert alert-light-warning d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Complete your payment to generate the letter</div>
                        <div class="text-muted">UGX 3,000 one-time. Not a subscription.</div>
                    </div>
                    <form method="POST" action="{{ route('letters.pay', $letter['uuid']) }}" id="payForm">
                        @csrf
                        <button type="submit" class="btn btn-warning" id="payBtn">
                            Pay UGX 3,000
                        </button>
                    </form>
                </div>
            @endif

            @if($letter['status'] === 'processing' || $letter['status'] === 'paid')
                <div class="text-center py-15">
                    <div class="spinner-border text-primary mb-4"></div>
                    <div class="fw-bold fs-5">Generating your letter...</div>
                    <div class="text-muted">This usually takes 30-60 seconds. The page will refresh automatically.</div>
                </div>
            @elseif($letter['status'] === 'failed')
                <div class="alert alert-danger">
                    <div class="fw-bold">Generation failed</div>
                    <div>{{ $letter['error_message'] ?? 'Unknown error.' }}</div>
                </div>
            @elseif($letter['status'] === 'generated' && !empty($letter['content']))
                <div class="border rounded p-8 bg-white" style="max-width:800px; margin:0 auto;">
                    {!! $letter['content'] !!}
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const status = '{{ $letter['status'] }}';
    const uuid   = '{{ $letter['uuid'] }}';
    const CSRF   = '{{ csrf_token() }}';

    // Auto-refresh while processing
    if (status === 'processing' || status === 'paid') {
        setInterval(() => window.location.reload(), 5000);
    }

    // Handle pay form via fetch to keep UX clean
    document.getElementById('payForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('payBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        fetch('{{ route("letters.pay", $letter["uuid"]) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                window.location.reload();
            } else {
                alert(res.message || 'Payment failed.');
                btn.disabled = false;
                btn.innerHTML = 'Pay UGX 3,000';
            }
        })
        .catch(() => {
            alert('Payment request failed.');
            btn.disabled = false;
            btn.innerHTML = 'Pay UGX 3,000';
        });
    });
})();
</script>
@endpush