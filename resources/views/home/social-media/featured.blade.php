@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $platformsData = is_array($platforms ?? null) ? $platforms : [];
    $hasPlatforms  = count($platformsData) > 0;

    $platformNames = collect($platformsData)
        ->map(fn ($p) => $p['name'] ?? ucfirst($p['platform'] ?? ''))
        ->filter()->take(5)->implode(', ');

    $seoDescription = 'Follow ' . app_name() . ($platformNames ? ' on ' . $platformNames : ' on social media')
        . ' for the latest job openings, career tips and hiring updates in ' . country_name() . '.';

    // Declared once, safe if the view is rendered more than once per request
    if (!function_exists('socialIconSvg')) {
        function socialIconSvg(string $platform, int $size = 28): string
        {
            $attrs = 'width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"';

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
    }
@endphp

@section('title', 'Connect with Us - ' . country_name())
@section('meta_description', $seoDescription)
@section('og_title', 'Connect with ' . app_name() . ' - ' . country_name())
@section('og_description', $seoDescription)
@section('twitter_title', 'Connect with ' . app_name() . ' - ' . country_name())
@section('twitter_description', $seoDescription)

@push('styles')
<style>
    :root{
        --jp-navy: #0B1C2E;
        --jp-navy-2: #13273D;
        --jp-green: #20AA3E;
        --jp-teal: #03A588;
        --jp-gradient: linear-gradient(135deg, #20AA3E 0%, #03A588 100%);
        --jp-ink: #0F1B2D;
        --jp-muted: #64748B;
        --jp-bg-soft: #F4F8F7;
        --jp-bg-page: #E7EFEF;
        --jp-line: rgba(15,27,45,0.08);
    }

    /* Page guard: no sideways scroll (clip keeps the CTA overhang visible) */
    .jp-page{ overflow-x:clip; width:100%; max-width:100%; }
    .jp-page *, .jp-page *::before, .jp-page *::after{ box-sizing:border-box; }

    /* Header band: same pale band as the jobs page */
    .jp-search-band{ background: linear-gradient(120deg, #EAF7F5 0%, #E7F1FB 100%); border-bottom:1px solid var(--jp-line); }
    .jp-search-band h1{ color:var(--jp-ink); font-weight:800; font-size:clamp(1.4rem, 5vw, 1.7rem); margin-bottom:.35rem; overflow-wrap:anywhere; }
    .jp-search-band p{ color:var(--jp-muted); margin-bottom:0; max-width:640px; }
    .jp-kicker{ color:var(--jp-teal); font-weight:800; letter-spacing:.06em; text-transform:uppercase; font-size:11.5px; }

    /* Page backdrop: same as the jobs listing */
    .jp-listing-bg{
        background:var(--jp-bg-page);
        background-image:
            radial-gradient(65% 55% at 100% 0%, rgba(3,165,136,0.14) 0%, transparent 60%),
            radial-gradient(55% 45% at 0% 15%, rgba(11,28,46,0.10) 0%, transparent 60%);
        border-top:3px solid var(--jp-teal);
    }

    /* ===== Social cards: same card language as the job cards ===== */
    .jp-social-grid{ display:grid; grid-template-columns:repeat(auto-fill, minmax(min(100%, 320px), 1fr)); gap:16px; }
    .jp-social-card{
        background:linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%);
        border:1px solid var(--jp-line);
        border-radius:14px;
        padding:20px;
        transition:.2s;
        box-shadow:0 6px 18px rgba(11,28,46,0.06);
        min-width:0;
        display:flex;
        flex-direction:column;
        overflow:hidden;
    }
    .jp-social-card:hover{ border-color:var(--jp-teal); box-shadow:0 14px 30px rgba(11,28,46,0.1); transform:translateY(-2px); }

    .jp-social-card .card-head{ display:flex; align-items:center; gap:14px; min-width:0; }
    .jp-social-card .icon-tile{ width:56px; height:56px; border-radius:12px; border:1px solid var(--jp-line); display:flex; align-items:center; justify-content:center; flex:0 0 auto; background:var(--jp-bg-soft); color:var(--jp-navy); transition:.2s; }
    .jp-social-card:hover .icon-tile{ border-color:var(--jp-teal); }
    .jp-social-card .info{ min-width:0; flex:1 1 auto; }
    .jp-social-card .platform-name{ font-weight:700; font-size:1.05rem; line-height:1.3; color:var(--jp-ink); text-decoration:none; display:block; overflow-wrap:anywhere; }
    .jp-social-card .platform-name:hover{ color:var(--jp-teal); }
    .jp-social-card .handle{ color:var(--jp-muted); font-size:.82rem; font-weight:500; overflow-wrap:anywhere; }
    .jp-social-card .badges{ display:flex; flex-wrap:wrap; gap:6px; margin-top:12px; }

    .jp-pill{ font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; border:1px solid var(--jp-line); display:inline-flex; align-items:center; gap:4px; background:var(--jp-bg-soft); color:#3B5166; }
    .jp-pill-verified{ background:#E9F9EF; color:#1E9E4C; border-color:rgba(30,158,76,0.15); }
    .jp-pill-featured{ background:#E7F1FB; color:#1D6FCC; border-color:rgba(29,111,204,0.15); }

    .jp-social-card .description{ color:var(--jp-muted); font-size:.88rem; line-height:1.6; margin:12px 0 0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; overflow-wrap:anywhere; }

    .jp-social-card .card-foot{ margin-top:auto; padding-top:16px; }
    .jp-social-card .card-foot-inner{ border-top:1px solid var(--jp-line); padding-top:14px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
    .jp-social-card .followers{ color:var(--jp-muted); font-size:.82rem; font-weight:600; display:inline-flex; align-items:center; gap:5px; }
    .jp-social-card .followers i{ color:#94A3B8; }
    .jp-social-card .actions{ display:flex; align-items:center; gap:8px; margin-left:auto; }

    .jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
    .jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }
    .jp-btn-outline{ border:1.5px solid var(--jp-line) !important; color:var(--jp-muted); font-weight:700; border-radius:10px; background:#fff; }
    .jp-btn-outline:hover{ border-color:var(--jp-teal); color:var(--jp-teal); background:rgba(3,165,136,0.05); }
    .jp-btn-outline.jp-copied{ border-color:var(--jp-teal) !important; color:var(--jp-teal); }

    /* Platform tile colours */
    .jp-social-card.facebook  .icon-tile{ background:#E7F1FB; color:#1877F2; }
    .jp-social-card.twitter   .icon-tile{ background:#F0F0F0; color:#000; }
    .jp-social-card.instagram .icon-tile{ background:#FCE4EC; color:#E4405F; }
    .jp-social-card.linkedin  .icon-tile{ background:#E3F0FA; color:#0A66C2; }
    .jp-social-card.youtube   .icon-tile{ background:#FBE9E7; color:#FF0000; }
    .jp-social-card.whatsapp  .icon-tile{ background:#E8F5E9; color:#25D366; }
    .jp-social-card.tiktok    .icon-tile{ background:#F5F5F5; color:#000; }
    .jp-social-card.telegram  .icon-tile{ background:#E3F2FD; color:#26A5E4; }

    .jp-empty-card{ background:#fff; border:1px dashed var(--jp-line); border-radius:16px; }

    /* CTA banner */
    .jp-cta-wrap{ position:relative; z-index:2; margin-top:2.5rem; margin-bottom:-5rem; }
    .jp-cta-banner{ background:linear-gradient(90deg, #20AA3E 0%, #03A588 100%); border-radius:22px; padding:2rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap; box-shadow:0 20px 50px rgba(11,28,46,0.15); }
    .jp-cta-banner .cta-text{ flex:1 1 320px; min-width:0; }
    .jp-cta-banner .cta-actions{ display:flex; gap:12px; flex-wrap:wrap; flex:0 0 auto; }
    @media (min-width: 992px){ .jp-cta-banner{ padding:3rem; } }

    /* ===== Mobile ===== */
    @media (max-width: 575.98px){
        .jp-social-card{ padding:16px; }
        .jp-social-card .icon-tile{ width:48px; height:48px; }
        .jp-social-card .card-foot-inner{ flex-direction:column; align-items:stretch; }
        .jp-social-card .actions{ margin-left:0; }
        .jp-social-card .actions .btn-follow{ flex:1 1 auto; justify-content:center; }
        .jp-cta-banner{ padding:1.5rem 1.25rem; border-radius:18px; }
        .jp-cta-banner .cta-actions{ width:100%; flex-direction:column; }
        .jp-cta-banner .cta-actions .btn{ width:100%; }
    }
</style>
@endpush

@section('content')

<div class="jp-page">

<!-- ====================== HEADER BAND ====================== -->
<div class="jp-search-band py-8 py-lg-10">
    <div class="container">
        <div class="jp-kicker mb-2">Stay Connected</div>
        <h1>Connect with {{ app_name() }}</h1>
        <p>Follow us on social media for the latest job opportunities, career tips, and hiring updates in {{ country_name() }}.</p>
    </div>
</div>

<!-- ====================== PLATFORMS ====================== -->
<div class="jp-listing-bg">
    <div class="container py-10 py-lg-12">

        @if($hasPlatforms)
        <div class="jp-social-grid">
            @foreach($platformsData as $platform)
                @php
                    $platformKey = strtolower($platform['platform'] ?? 'social');
                    $cardClass   = Str::slug($platformKey === 'x' ? 'twitter' : $platformKey);
                    $followers   = (int) ($platform['followers_count'] ?? 0);
                    $isVerified  = !empty($platform['is_verified']);
                    $isFeatured  = !empty($platform['is_featured']);
                    $rawUrl      = $platform['url'] ?? '';
                    $url         = Str::startsWith($rawUrl, ['http://', 'https://']) ? $rawUrl : '#';
                    $handle      = $platform['handle'] ?? '';
                    $name        = $platform['name'] ?? ucfirst($platformKey);
                    $description = $platform['description'] ?? '';
                @endphp
                <div class="jp-social-card {{ $cardClass }}">

                    <div class="card-head">
                        <div class="icon-tile">{!! socialIconSvg($platformKey, 28) !!}</div>
                        <div class="info">
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="platform-name">{{ $name }}</a>
                            @if($handle)<div class="handle">{{ $handle }}</div>@endif
                        </div>
                    </div>

                    @if($isVerified || $isFeatured)
                        <div class="badges">
                            @if($isVerified)
                                <span class="jp-pill jp-pill-verified"><i class="ki-duotone ki-verify fs-7"><span class="path1"></span><span class="path2"></span></i>Verified</span>
                            @endif
                            @if($isFeatured)
                                <span class="jp-pill jp-pill-featured"><i class="ki-duotone ki-star fs-7"><span class="path1"></span><span class="path2"></span></i>Featured</span>
                            @endif
                        </div>
                    @endif

                    @if($description)
                        <div class="description">{{ $description }}</div>
                    @endif

                    <div class="card-foot">
                        <div class="card-foot-inner">
                            @if($followers > 0)
                                <span class="followers">
                                    <i class="ki-duotone ki-people fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    {{ number_format($followers) }} followers
                                </span>
                            @endif
                            <div class="actions">
                                <button type="button" class="btn btn-sm jp-btn-outline" data-copy-url="{{ $url }}" title="Copy link">
                                    <i class="ki-duotone ki-copy fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                                    <span class="copy-text">Copy</span>
                                </button>
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm jp-btn-primary btn-follow">
                                    Follow
                                    <i class="ki-duotone ki-arrow-right fs-6 ms-1"><span class="path1"></span><span class="path2"></span></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <div class="jp-empty-card text-center py-10 px-4">
            <i class="ki-duotone ki-share fs-3x text-muted d-block mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
            <p class="fw-semibold fs-5 mb-1">No social channels yet</p>
            <p class="text-muted mb-0">Check back soon, we'll share our social media channels here.</p>
        </div>
        @endif
    </div>

    <!-- CTA Banner -->
    <div class="jp-cta-wrap">
        <div class="container">
            <div class="jp-cta-banner">
                <div class="cta-text">
                    <div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Ready to make your next move?</div>
                    <div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Join thousands of job seekers finding opportunities in {{ country_name() }}.</div>
                </div>
                <div class="cta-actions">
                    <a href="{{ route('jobs.index') }}" class="btn btn-lg btn-light fw-bold">Browse Jobs</a>
                    <a href="{{ route('register') }}?as=seeker" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">Join Now</a>
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- /.jp-page --}}

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-copy-url]').forEach(function (button) {
        button.addEventListener('click', function () {
            var url = button.dataset.copyUrl;
            var label = button.querySelector('.copy-text');
            var original = label.textContent;

            var done = function () {
                label.textContent = 'Copied!';
                button.classList.add('jp-copied');
                setTimeout(function () {
                    label.textContent = original;
                    button.classList.remove('jp-copied');
                }, 2000);
            };

            var fallback = function () {
                var input = document.createElement('input');
                input.value = url;
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                document.body.removeChild(input);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(fallback);
            } else {
                fallback();
            }
        });
    });
});
</script>
@endpush