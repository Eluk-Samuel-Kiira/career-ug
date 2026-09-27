@extends('layouts.admin')

@section('title', 'Find Candidates')
@section('page_title', 'Find Candidates')

@section('content')
<div class="card">
    <div class="card-body py-15 text-center">
        <i class="ki-duotone ki-lock-2 fs-5tx text-warning mb-5 d-block">
            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
        </i>
        <h2 class="fw-bold text-gray-800 mb-3">Subscription Required</h2>
        <p class="text-muted fs-4 mb-6">
            Candidate search is available on paid plans. Upgrade to unlock
            country-wide filtering, CV previews, and contact details.
        </p>
        <a href="#" class="btn btn-primary">View Plans</a>
    </div>
</div>
@endsection