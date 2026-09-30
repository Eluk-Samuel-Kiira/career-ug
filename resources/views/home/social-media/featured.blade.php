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
            <h1>Connect with Stardena Careers</h1>
            <p>Follow us on social media for the latest job opportunities, career tips, and updates in {{ country_name() }}.</p>
        </div>

        {{-- Social Media Grid --}}
        @php
            $platformsData = is_array($platforms) ? $platforms : [];
            $hasPlatforms = !empty($platformsData) && count($platformsData) > 0;

            function socialIconSvg(string $platform, int $size = 28): string
            {
                $attrs = 'width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor"';

                return match (strtolower($platform)) {
                    'facebook' => '<svg ' . $attrs . '><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.91h-2.34V22c4.78-.79 8.44-4.94 8.44-9.94Z"/></svg>',

                    'twitter', 'x' => '<svg ' . $attrs . '><path d="M18.9 2H22l-7.6 8.7L23 22h-7l-5.5-6.9L4.2 22H1l8.1-9.3L1 2h7.2l5 6.3L18.9 2Zm-1.2 18h1.7L7.4 4H5.6l12.1 16Z"/></svg>',

                    'instagram' => '<svg ' . $attrs . '><path d="M12 2.2c3.2 0 3.6 0 4.9.07 1.2.06 1.8.25 2.2.42.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.3-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.3-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2Zm0 1.8c-3.1 0-3.5 0-4.7.07-1.1.05-1.7.24-2.1.4-.5.2-.9.4-1.3.8-.4.4-.6.8-.8 1.3-.2.4-.4 1-.4 2.1C2.6 9.9 2.6 10.3 2.6 12s0 2.1.07 3.3c.05 1.1.24 1.7.4 2.1.2.5.4.9.8 1.3.4.4.8.6 1.3.8.4.2 1 .4 2.1.4 1.2.07 1.6.07 4.7.07s3.5 0 4.7-.07c1.1-.05 1.7-.24 2.1-.4.5-.2.9-.4 1.3-.8.4-.4.6-.8.8-1.3.2-.4.4-1 .4-2.1.07-1.2.07-1.6.07-4.7s0-3.5-.07-4.7c-.05-1.1-.24-1.7-.4-2.1-.2-.5-.4-.9-.8-1.3-.4-.4-.8-.6-1.3-.8-.4-.2-1-.4-2.1-.4-1.2-.07-1.6-.07-4.7-.07Zm0 3.06a4.94 4.94 0 1 1 0 9.88 4.94 4.94 0 0 1 0-9.88Zm0 8.14a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Zm6.28-8.34a1.15 1.15 0 1 1-2.3 0 1.15 1.15 0 0 1 2.3 0Z"/></svg>',

                    'linkedin' => '<svg ' . $attrs . '><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13ZM7.12 20.45H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z"/></svg>',

                    'youtube' => '<svg ' . $attrs . '><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1c.5-1.9.5-5.8.5-5.8s0-3.9-.5-5.8ZM9.6 15.6V8.4l6.2 3.6-6.2 3.6Z"/></svg>',

                    'whatsapp' => '<svg ' . $attrs . '><path d="M17.6 6.32A8.86 8.86 0 0 0 12.02 3.5c-4.87 0-8.83 3.94-8.83 8.79 0 1.55.41 3.06 1.19 4.39L3.2 21l4.44-1.16a8.9 8.9 0 0 0 4.37 1.12h.01c4.87 0 8.83-3.94 8.83-8.79a8.7 8.7 0 0 0-2.25-5.85Zm-5.58 13.5h-.01c-1.36 0-2.7-.36-3.86-1.05l-.28-.16-2.87.75.77-2.79-.18-.29a7.3 7.3 0 0 1-1.13-3.9c0-4.03 3.3-7.32 7.36-7.32a7.3 7.3 0 0 1 5.2 2.15 7.24 7.24 0 0 1 2.16 5.17c0 4.03-3.3 7.32-7.36 7.32Zm4.03-5.48c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.58.71-.71.86-.26.16-.48.05a6.03 6.03 0 0 1-1.77-1.09 6.6 6.6 0 0 1-1.22-1.52c-.13-.22 0-.34.1-.45.1-.1.22-.26.33-.39.11-.13.15-.22.22-.37.07-.15.04-.28-.02-.39-.06-.11-.5-1.2-.68-1.65-.18-.43-.36-.37-.5-.38h-.43c-.15 0-.39.06-.6.28-.2.22-.79.77-.79 1.87 0 1.1.81 2.17.92 2.32.11.15 1.6 2.44 3.87 3.42.54.23.96.37 1.29.48.54.17 1.03.15 1.42.09.43-.06 1.3-.53 1.49-1.04.18-.51.18-.94.13-1.03-.05-.09-.2-.15-.42-.26Z"/></svg>',

                    'tiktok' => '<svg ' . $attrs . '><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.9 2.9 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.84-.1Z"/></svg>',

                    'telegram' => '<svg ' . $attrs . '><path d="M22.05 3.24 1.87 11.02c-1.09.43-1.08 1.04-.2 1.31l5.15 1.6 11.96-7.55c.56-.34 1.08-.16.66.22l-9.68 8.74h-.01l-.36 5.31c.51 0 .74-.24 1.02-.51l2.45-2.38 5.09 3.76c.94.52 1.61.25 1.85-.86l3.36-15.83c.34-1.4-.53-2.03-1.4-1.6Z"/></svg>',

                    default => '<svg ' . $attrs . '><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92S19.61 16.08 18 16.08Z"/></svg>',
                };
            }
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
                                {!! socialIconSvg($platformName, 30) !!}
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