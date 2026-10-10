@extends('layouts.admin')

@section('title', 'Hiring Decision Engine')
@section('page_title', 'Hiring Decision Engine')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Employer</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Hiring Decision Engine</li>
@endsection

@section('content')
<div class="container py-6">

    {{-- Header card --}}
    <div class="card card-flush mb-6 bg-light-primary">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="symbol symbol-60px">
                    <div class="symbol-label bg-white">
                        <i class="ki-duotone ki-abstract-26 fs-2x text-primary">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div>
                    <h2 class="fw-bold mb-1">Screen CVs with AI</h2>
                    <div class="text-muted fs-7">
                        Upload hundreds of CVs and a job description. Get a ranked shortlist in minutes.
                    </div>
                </div>
            </div>
            <a href="{{ route('employer.hiring.create') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-3 me-1">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                New Screening Run
            </a>
        </div>
    </div>

    {{-- Past runs --}}
    <div class="card card-flush">
        <div class="card-header py-5">
            <h3 class="card-title fw-bold text-gray-800">Your past runs</h3>
            <div class="card-toolbar">
                <span class="badge badge-light-primary fs-7 py-3 px-5" id="totalBadge">0</span>
            </div>
        </div>

        <div class="card-body pt-0">
            @if(empty($runs))
                <div class="text-center py-15">
                    <i class="ki-duotone ki-abstract-26 fs-5tx text-muted mb-4 d-block">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <h4 class="fw-bold text-gray-800 mb-2">No screening runs yet</h4>
                    <p class="text-muted mb-5">
                        Upload a job description and a folder of CVs to get your first AI-ranked shortlist.
                    </p>
                    <a href="{{ route('employer.hiring.create') }}" class="btn btn-primary">
                        Start Your First Run
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-200px">Run</th>
                                <th class="min-w-90px">CVs</th>
                                <th class="min-w-90px">Screened</th>
                                <th class="min-w-90px">Failed</th>
                                <th class="min-w-120px">Status</th>
                                <th class="min-w-120px">Created</th>
                                <th class="text-end min-w-120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($runs as $run)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-gray-800">{{ $run['title'] }}</div>
                                        <div class="text-muted fs-7">
                                            {{ \Illuminate\Support\Str::limit($run['uuid'], 8, '') }}…
                                        </div>
                                    </td>
                                    <td>{{ $run['total'] }}</td>
                                    <td>
                                        <span class="badge badge-light-success">{{ $run['processed'] }}</span>
                                    </td>
                                    <td>
                                        @if($run['failed'] > 0)
                                            <span class="badge badge-light-danger">{{ $run['failed'] }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($run['status']) {
                                                'completed'  => 'success',
                                                'processing' => 'primary',
                                                'failed'     => 'danger',
                                                default      => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge badge-light-{{ $statusClass }}">
                                            {{ ucfirst($run['status']) }}
                                        </span>
                                    </td>
                                    <td class="text-muted fs-7">
                                        {{ \Carbon\Carbon::parse($run['created_at'])->format('M d, Y H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('employer.hiring.show', $run['uuid']) }}"
                                               class="btn btn-sm btn-icon btn-light" title="View results">
                                                <i class="ki-duotone ki-eye fs-4">
                                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                                </i>
                                            </a>
                                            <a href="{{ route('employer.hiring.export', $run['uuid']) }}"
                                               class="btn btn-sm btn-icon btn-light" title="Export CSV">
                                                <i class="ki-duotone ki-file-down fs-4">
                                                    <span class="path1"></span><span class="path2"></span>
                                                </i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-icon btn-light-danger delete-run-btn"
                                                    data-uuid="{{ $run['uuid'] }}"
                                                    data-title="{{ $run['title'] }}"
                                                    title="Delete">
                                                <i class="ki-duotone ki-trash fs-4">
                                                    <span class="path1"></span><span class="path2"></span>
                                                    <span class="path3"></span><span class="path4"></span>
                                                </i>
                                            </button>
                                        </div>
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

@push('scripts')
<script>
(function () {
    const CSRF = '{{ csrf_token() }}';

    document.querySelectorAll('.delete-run-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const uuid = this.dataset.uuid;
            const title = this.dataset.title || 'this run';

            if (!confirm(`Delete "${title}"? All uploaded CVs for this run will also be removed.`)) return;

            const original = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(`{{ url('employer/hiring') }}/${uuid}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.showToast?.('success', 'Run deleted.');
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    window.showToast?.('error', data.message || 'Could not delete.');
                    this.disabled = false;
                    this.innerHTML = original;
                }
            })
            .catch(() => {
                window.showToast?.('error', 'Network error.');
                this.disabled = false;
                this.innerHTML = original;
            });
        });
    });

    // Set total badge
    const badge = document.getElementById('totalBadge');
    if (badge) badge.textContent = document.querySelectorAll('.delete-run-btn').length;
})();
</script>
@endpush