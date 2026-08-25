@extends('layouts.app')

@section('title', $page['meta_title'] ?? $page['title'] . ' — ' . country_name())
@section('meta_description', $page['meta_description'] ?? '')

@push('styles')
<style>
    .jp-page-content {
        background: var(--jp-bg-page);
        min-height: 60vh;
    }
    .jp-page-card {
        background: #fff;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 6px 18px rgba(11, 28, 46, 0.05);
        border: 1px solid var(--jp-line);
    }
    .jp-page-card h1 {
        font-size: 2rem;
        font-weight: 800;
        color: var(--jp-ink);
        margin-bottom: 1.5rem;
    }
    .jp-page-card h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--jp-ink);
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    .jp-page-card h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--jp-ink);
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    .jp-page-card p {
        color: #33475B;
        line-height: 1.8;
        margin-bottom: 1rem;
    }
    .jp-page-card ul, .jp-page-card ol {
        color: #33475B;
        line-height: 1.8;
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }
    .jp-page-card li {
        margin-bottom: 0.5rem;
    }
    .jp-page-card a {
        color: var(--jp-teal);
        text-decoration: none;
    }
    .jp-page-card a:hover {
        text-decoration: underline;
    }
    .jp-breadcrumb {
        font-size: .82rem;
        color: var(--jp-muted);
        padding: 12px 0;
    }
    .jp-breadcrumb a {
        color: var(--jp-muted);
        text-decoration: none;
    }
    .jp-breadcrumb a:hover {
        color: var(--jp-teal);
    }
    .jp-breadcrumb .sep {
        margin: 0 8px;
    }
</style>
@endpush

@section('content')

<div class="jp-page-content py-10">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="jp-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <span class="text-gray-700">{{ $page['title'] }}</span>
        </div>

        <!-- Page Content -->
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="jp-page-card">
                    <h1>{{ $page['title'] }}</h1>
                    <div class="page-content">
                        {!! $page['content'] ?? '<p>Content coming soon.</p>' !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection