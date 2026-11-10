@extends('layouts.app')

@section('title', 'Email Preferences - ' . country_name())

@push('styles')
<style>
    :root{
        --jp-green: #20AA3E;
        --jp-teal: #03A588;
        --jp-gradient: linear-gradient(135deg, #20AA3E 0%, #03A588 100%);
        --jp-ink: #0F1B2D;
        --jp-muted: #64748B;
        --jp-bg-page: #E7EFEF;
        --jp-line: rgba(15,27,45,0.08);
    }

    .jp-page { overflow-x: clip; width: 100%; max-width: 100%; }
    .jp-page *, .jp-page *::before, .jp-page *::after { box-sizing: border-box; }

    .jp-search-band { 
        background: linear-gradient(120deg, #EAF7F5 0%, #E7F1FB 100%); 
        border-bottom: 1px solid var(--jp-line); 
        padding: 60px 0 40px;
        text-align: center;
    }
    
    .jp-listing-bg {
        background: var(--jp-bg-page);
        background-image: 
            radial-gradient(65% 55% at 100% 0%, rgba(3,165,136,0.14) 0%, transparent 60%),
            radial-gradient(55% 45% at 0% 15%, rgba(11,28,46,0.10) 0%, transparent 60%);
        min-height: 60vh;
        padding: 40px 0;
    }

    .jp-card {
        background: linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%);
        border: 1px solid var(--jp-line);
        border-radius: 14px;
        padding: 40px;
        max-width: 500px;
        margin: 0 auto;
        box-shadow: 0 10px 30px rgba(11,28,46,0.08);
        text-align: center;
    }

    .jp-icon-wrap {
        width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 20px;
        display: flex; align-items: center; justify-content: center;
    }
    
    .success-icon { background: #E9F9EF; color: #1E9E4C; }
    .error-icon { background: #FEECEC; color: #C0392B; }

    .jp-card h1 { font-size: 1.5rem; font-weight: 800; color: var(--jp-ink); margin-bottom: 12px; }
    .jp-card p { color: var(--jp-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 24px; }
    
    .jp-btn-primary { background: var(--jp-gradient); border: none; color: #fff; font-weight: 700; border-radius: 10px; padding: 12px 24px; }
    .jp-btn-primary:hover { color: #fff; filter: brightness(1.06); }
</style>
@endpush

@section('content')
<div class="jp-page">
    <div class="jp-search-band">
        <div class="container">
            <h1 class="fw-bold text-dark">Email Preferences</h1>
            <p class="text-muted mb-0">Manage your job alert subscriptions</p>
        </div>
    </div>

    <div class="jp-listing-bg">
        <div class="container">
            <div class="jp-card">
                @if($status === 'success')
                    <div class="jp-icon-wrap success-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h1>Successfully Unsubscribed</h1>
                    <p>{{ $message }}</p>
                    <p style="font-size: 12px; margin-bottom: 24px;">You will still receive important account emails (like password resets), but you will no longer receive automated job alerts.</p>
                    
                    <a href="{{ route('jobs.index') }}" class="btn jp-btn-primary">Back to Jobs</a>
                @else
                    <div class="jp-icon-wrap error-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </div>
                    <h1>Link Invalid</h1>
                    <p>{{ $message }}</p>
                    <a href="{{ route('jobs.index') }}" class="btn jp-btn-primary">Back to Jobs</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection