@extends('layouts.app')

@section('title', 'Connect with Us - ' . country_name())
@section('meta_description', 'Follow Great Jobs on social media for the latest job opportunities, career tips, and updates in ' . country_name() . '.')

@push('styles')
<style>
    .social-page {
        background: var(--jp-bg-page);
        background-image: radial-gradient(65% 45% at 100% 0%, rgba(3,165,136,0.12) 0%, transparent 60%), radial-gradient(50% 40% at 0% 10%, rgba(11,28,46,0.08) 0%, transparent 60%);
        border-top: 3px solid var(--jp-teal);
        min-height: 80vh;
        padding: 40px 0;
    }
    .social-header {
        text-align: center;
        margin-bottom: 40px;
    }
    .social-header h1 {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--jp-ink);
    }
    .social-header p {
        color: var(--jp-muted);
        font-size: 1.1rem;
    }

    /* ============================================================
       SOCIAL CARDS - MATCHING JOB CARD DESIGN
       ============================================================ */
    .social-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }
    .social-grid .social-col {
        display: flex;
    }
    .social-card {
        background: linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%);
        border: 1px solid var(--jp-line);
        border-radius: 14px;
        padding: 22px;
        transition: all 0.2s ease;
        box-shadow: 0 6px 18px rgba(11, 28, 46, 0.06);
        width: 100%;
        display: flex;
        flex-direction: column;
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }
    .social-card:hover {
        border-color: var(--jp-teal);
        box-shadow: 0 14px 30px rgba(11, 28, 46, 0.1);
        transform: translateY(-2px);
    }
    .social-card .social-header-section {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 12px;
    }
    .social-card .icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: var(--jp-bg-soft);
        border: 1px solid var(--jp-line);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    .social-card:hover .icon-wrapper {
        transform: scale(1.05);
        border-color: var(--jp-teal);
    }
    .social-card .icon-wrapper i {
        font-size: 30px;
        line-height: 1;
    }
    .social-card .icon-wrapper i .path1,
    .social-card .icon-wrapper i .path2 {
        font-size: 30px;
    }
    .social-card .platform-info {
        flex: 1;
        min-width: 0;
    }
    .social-card .platform-name {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--jp-ink);
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .social-card .platform-name:hover {
        color: var(--jp-teal);
    }
    .social-card .handle {
        color: var(--jp-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }
    .social-card .badge-verified {
        background: #E9F9EF;
        color: #1E9E4C;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 10px;
        border-radius: 20px;
        border: 1px solid rgba(30, 158, 76, 0.15);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .social-card .badge-verified i {
        font-size: 10px;
    }
    .social-card .badge-featured {
        background: #E7F1FB;
        color: #1D6FCC;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 10px;
        border-radius: 20px;
        border: 1px solid rgba(29, 111, 204, 0.15);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .social-card .description {
        color: var(--jp-muted);
        font-size: 0.88rem;
        line-height: 1.6;
        margin: 8px 0 12px 0;
        flex-grow: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .social-card .social-meta {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding-top: 12px;
        border-top: 1px solid var(--jp-line);
        margin-top: auto;
    }
    .social-card .social-meta .followers {
        color: var(--jp-muted);
        font-size: 0.82rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .social-card .social-meta .followers i {
        color: #94A3B8;
        font-size: 16px;
    }
    .social-card .social-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
    }
    .social-card .btn-follow {
        background: var(--jp-gradient);
        border: none;
        color: #fff;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 6px 16px;
        border-radius: 10px;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }
    .social-card .btn-follow:hover {
        filter: brightness(1.06);
        transform: translateY(-1px);
        color: #fff;
    }
    .social-card .btn-follow i {
        font-size: 16px;
    }
    .social-card .btn-copy {
        background: transparent;
        border: 1px solid var(--jp-line);
        color: var(--jp-muted);
        font-weight: 600;
        font-size: 0.75rem;
        padding: 6px 10px;
        border-radius: 10px;
        transition: all 0.2s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .social-card .btn-copy:hover {
        border-color: var(--jp-teal);
        color: var(--jp-teal);
        background: rgba(3, 165, 136, 0.05);
    }
    .social-card .btn-copy i {
        font-size: 14px;
    }

    /* Platform specific icon colors */
    .social-card.facebook .icon-wrapper { background: #E7F1FB; color: #1877F2; }
    .social-card.twitter .icon-wrapper { background: #f0f0f0; color: #000; }
    .social-card.instagram .icon-wrapper { background: #fce4ec; color: #E4405F; }
    .social-card.linkedin .icon-wrapper { background: #e3f0fa; color: #0A66C2; }
    .social-card.youtube .icon-wrapper { background: #fbe9e7; color: #FF0000; }
    .social-card.whatsapp .icon-wrapper { background: #e8f5e9; color: #25D366; }
    .social-card.tiktok .icon-wrapper { background: #f5f5f5; color: #000; }
    .social-card.telegram .icon-wrapper { background: #e3f2fd; color: #26A5E4; }

    .social-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border: 1px dashed var(--jp-line);
        border-radius: 16px;
    }
    .social-empty i {
        font-size: 4rem;
        color: var(--jp-muted);
        margin-bottom: 16px;
        display: block;
    }
    .social-empty h3 {
        color: var(--jp-ink);
        margin-bottom: 8px;
    }
    .social-empty p {
        color: var(--jp-muted);
    }

    .jp-kicker {
        color: var(--jp-teal);
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        font-size: 11.5px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .social-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }
        .social-header h1 {
            font-size: 1.8rem;
        }
        .social-card .social-header-section {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .social-card .platform-info {
            text-align: center;
        }
        .social-card .platform-name {
            justify-content: center;
        }
        .social-card .social-meta {
            flex-direction: column;
            align-items: stretch;
        }
        .social-card .social-actions {
            margin-left: 0;
            justify-content: center;
        }
        .social-card .description {
            text-align: center;
        }
    }
    @media (max-width: 480px) {
        .social-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="social-page">
    <div class="container">

        {{-- Header --}}
        <div class="social-header">
            <div class="jp-kicker mb-2">Stay Connected</div>
            <h1>Connect with Great Jobs</h1>
            <p>Follow us on social media for the latest job opportunities, career tips, and updates in {{ country_name() }}.</p>
        </div>

        {{-- Social Media Grid --}}
        @php
            $platformsData = is_array($platforms) ? $platforms : [];
            $hasPlatforms = !empty($platformsData) && count($platformsData) > 0;
        @endphp

        @if($hasPlatforms)
        <div class="social-grid">
            @foreach($platformsData as $platform)
                @php
                    $platformName = $platform['platform'] ?? 'social';
                    $icon = $platform['icon'] ?? 'ki-share';
                    $color = $platform['color'] ?? '#6c757d';
                    $followers = $platform['followers_count'] ?? 0;
                    $isVerified = $platform['is_verified'] ?? false;
                    $isFeatured = $platform['is_featured'] ?? false;
                    $url = $platform['url'] ?? '#';
                    $handle = $platform['handle'] ?? '';
                    $name = $platform['name'] ?? ucfirst($platformName);
                    $description = $platform['description'] ?? '';
                @endphp
                <div class="social-col">
                    <div class="social-card {{ $platformName }}">
                        {{-- Featured Badge --}}
                        @if($isFeatured)
                            <div style="position:absolute; top:12px; right:12px;">
                                <span class="badge-featured">
                                    <i class="ki-duotone ki-star fs-7">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    Featured
                                </span>
                            </div>
                        @endif

                        {{-- Header: Icon + Name --}}
                        <div class="social-header-section">
                            <div class="icon-wrapper">
                                <i class="ki-duotone {{ $icon }}">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <div class="platform-info">
                                <div class="platform-name">
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none" style="color: var(--jp-ink);">
                                        {{ $name }}
                                    </a>
                                    @if($isVerified)
                                        <span class="badge-verified">
                                            <i class="ki-duotone ki-verify fs-7">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            Verified
                                        </span>
                                    @endif
                                </div>
                                @if($handle)
                                    <div class="handle">{{ $handle }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- Description --}}
                        @if($description)
                            <div class="description">{{ $description }}</div>
                        @endif

                        {{-- Meta & Actions --}}
                        <div class="social-meta">
                            @if($followers > 0)
                                <div class="followers">
                                    <i class="ki-duotone ki-people fs-6">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    {{ number_format($followers) }} followers
                                </div>
                            @endif
                            <div class="social-actions">
                                <button class="btn-copy" onclick="copySocialLink('{{ $url }}', this)" title="Copy link">
                                    <i class="ki-duotone ki-copy fs-6">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="copy-text">Copy</span>
                                </button>
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="btn-follow">
                                    <i class="ki-duotone ki-arrow-right fs-6">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    Follow
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <div class="social-empty">
            <i class="ki-duotone ki-share fs-3x">
                <span class="path1"></span><span class="path2"></span>
            </i>
            <h3>No Social Media Platforms Yet</h3>
            <p>Check back soon for updates and follow us on our social media channels.</p>
        </div>
        @endif

        {{-- CTA Banner --}}
        <div class="mt-10 mb-n20 position-relative z-index-2">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-12">  
                        <div class="d-flex flex-stack flex-wrap flex-md-nowrap card-rounded shadow p-8 p-lg-12" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
                            <div class="my-2 me-5">
                                <div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Ready to make your next move?</div>
                                <div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Join thousands of job seekers finding opportunities in {{ country_name() }}.</div>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-3 flex-shrink-0 my-2">
                                <a href="{{ route('jobs.index') }}" class="btn btn-lg btn-light fw-bold">Browse Jobs</a>
                                <a href="{{ route('register') }}?as=seeker" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">Join Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
function copySocialLink(url, button) {
    navigator.clipboard.writeText(url).then(() => {
        const textSpan = button.querySelector('.copy-text');
        const originalText = textSpan.textContent;
        textSpan.textContent = 'Copied!';
        button.style.borderColor = 'var(--jp-teal)';
        button.style.color = 'var(--jp-teal)';
        setTimeout(() => {
            textSpan.textContent = originalText;
            button.style.borderColor = '';
            button.style.color = '';
        }, 2000);
    }).catch(() => {
        // Fallback
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        const textSpan = button.querySelector('.copy-text');
        const originalText = textSpan.textContent;
        textSpan.textContent = 'Copied!';
        setTimeout(() => {
            textSpan.textContent = originalText;
        }, 2000);
    });
}
</script>
@endpush