@extends('layouts.admin')

@section('title', 'My Letters')
@section('page_title', 'Cover / Application Letters')

@section('content')
<div class="container py-6">

    <div class="card card-flush">
        <div class="card-header align-items-center py-5">
            <h3 class="card-title fw-bold text-gray-800">
                My Letters
                <span class="badge badge-light-primary ms-2">{{ count($letters) }}</span>
            </h3>
            <div class="card-toolbar">
                <a href="{{ route('letters.create') }}" class="btn btn-sm btn-primary">
                    <i class="ki-duotone ki-plus fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                    Generate a New Letter
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            @if(empty($letters))
                <div class="text-center py-15">
                    <i class="ki-duotone ki-document fs-5tx text-muted mb-4 d-block">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <h4 class="fw-bold text-gray-800 mb-2">No letters yet</h4>
                    <p class="text-muted mb-5">Generate a professional cover or application letter from your CV in under a minute.</p>
                    <a href="{{ route('letters.create') }}" class="btn btn-primary">
                        Get Started
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>Type</th>
                                <th>Target Role</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($letters as $l)
                                <tr>
                                    <td><span class="badge badge-light-info">{{ $l['letter_type_label'] }}</span></td>
                                    <td class="fw-semibold">{{ $l['job_title'] }}</td>
                                    <td>{{ $l['company_name'] }}</td>
                                    <td>{!! $l['status_badge'] !!}</td>
                                    <td class="text-muted fs-7">
                                        {{ \Carbon\Carbon::parse($l['created_at'])->format('M d, Y') }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            @if($l['status'] === 'generated')
                                                <a href="{{ route('letters.show', $l['uuid']) }}"
                                                class="btn btn-sm btn-icon btn-light" title="View">
                                                    <i class="ki-duotone ki-eye fs-4">
                                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                                    </i>
                                                </a>
                                            @elseif($l['status'] === 'pending_payment')
                                                <a href="{{ route('letters.show', $l['uuid']) }}"
                                                class="btn btn-sm btn-light-warning">
                                                    Complete Payment
                                                </a>
                                            @endif

                                            <button type="button"
                                                    class="btn btn-sm btn-icon btn-light-danger delete-letter-btn"
                                                    data-uuid="{{ $l['uuid'] }}"
                                                    data-title="{{ $l['job_title'] }}"
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
    'use strict';

    const CSRF = '{{ csrf_token() }}';

    document.querySelectorAll('.delete-letter-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const uuid  = this.dataset.uuid;
            const title = this.dataset.title || 'this letter';

            if (!confirm(`Delete the letter for "${title}"? This cannot be undone.`)) return;

            // Visual feedback on the button itself
            const original = this.innerHTML;
            this.disabled  = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const res  = await fetch(`{{ url('letters') }}/${uuid}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                });
                const body = await res.json();

                if (!res.ok || !body.success) {
                    toast('error', body.message || 'Failed to delete the letter.');
                    this.disabled = false;
                    this.innerHTML = original;
                    return;
                }

                toast('success', 'Letter deleted.');

                // Fade the row out and remove it
                const row = this.closest('tr');
                if (row) {
                    row.style.transition = 'opacity .2s ease';
                    row.style.opacity    = '0';
                    setTimeout(() => {
                        row.remove();

                        // If no letters remain, reload so the empty-state shows
                        if (document.querySelectorAll('.delete-letter-btn').length === 0) {
                            window.location.reload();
                        }
                    }, 200);
                }

            } catch (e) {
                toast('error', 'Network error: ' + e.message);
                this.disabled = false;
                this.innerHTML = original;
            }
        });
    });

    function toast(type, msg) {
        if (typeof window.showToast === 'function') {
            window.showToast(type, msg);
        } else {
            alert(msg);
        }
    }
})();
</script>
@endpush