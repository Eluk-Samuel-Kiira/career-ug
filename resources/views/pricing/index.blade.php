@extends('layouts.app')

@php
    /*
     | The icon helper is defined here, ABOVE the sections, on purpose: a function declared
     | inside an if-block only exists once that line has run, so it must come before the
     | section content that calls it. The function_exists guard prevents "cannot redeclare".
     */
    if (!function_exists('serviceIconSvg')) {
        function serviceIconSvg(?string $key): string
        {
            $attrs = 'width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';

            return match ((string) $key) {
                'cv_review', 'resume_builder', 'ki-document'
                    => '<svg ' . $attrs . '><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>',

                'cv_rewrite', 'ki-pencil'
                    => '<svg ' . $attrs . '><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>',

                'cover_letter', 'ki-message-text-2'
                    => '<svg ' . $attrs . '><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',

                'linkedin_optimization', 'ki-linkedin'
                    => '<svg ' . $attrs . '><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',

                'interview_coaching', 'ki-messages'
                    => '<svg ' . $attrs . '><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>',

                'premium_alerts', 'ki-notification-2', 'basic_job_alerts', 'ki-notification-status'
                    => '<svg ' . $attrs . '><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>',

                'featured_applicant', 'ki-star'
                    => '<svg ' . $attrs . '><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',

                'career_mentorship', 'ki-profile-user'
                    => '<svg ' . $attrs . '><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',

                'recruiter_access', 'ki-share'
                    => '<svg ' . $attrs . '><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',

                'salary_calculator', 'ki-chart-simple'
                    => '<svg ' . $attrs . '><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg>',

                'career_resources', 'ki-book-open', 'ki-book'
                    => '<svg ' . $attrs . '><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',

                // job posting family: briefcase
                'job_post_free', 'job_post_standard', 'job_post_popular', 'job_post_priority', 'job_post_enterprise'
                    => '<svg ' . $attrs . '><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',

                default
                    => '<svg ' . $attrs . '><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
            };
        }
    }

    $groupedList = $grouped ?? [];
@endphp

@section('title', 'Pricing - ' . country_name())
@section('meta_description', 'Simple, transparent pricing for job posting, CV services, and job seeker tools in ' . country_name() . '.')
@section('og_title', 'Pricing - ' . country_name())
@section('og_description', 'Simple, transparent pricing for job posting, CV services, and job seeker tools in ' . country_name() . '.')

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

    /* Header band: same pale band as the jobs and social pages */
    .jp-search-band{ background:linear-gradient(120deg, #EAF7F5 0%, #E7F1FB 100%); border-bottom:1px solid var(--jp-line); }
    .jp-search-band h1{ color:var(--jp-ink); font-weight:800; font-size:clamp(1.4rem, 5vw, 1.7rem); margin-bottom:.35rem; overflow-wrap:anywhere; }
    .jp-search-band p{ color:var(--jp-muted); margin-bottom:0; max-width:640px; }
    .jp-kicker{ color:var(--jp-teal); font-weight:800; letter-spacing:.06em; text-transform:uppercase; font-size:11.5px; }

    /* Page backdrop */
    .jp-listing-bg{
        background:var(--jp-bg-page);
        background-image:
            radial-gradient(65% 55% at 100% 0%, rgba(3,165,136,0.14) 0%, transparent 60%),
            radial-gradient(55% 45% at 0% 15%, rgba(11,28,46,0.10) 0%, transparent 60%);
        border-top:3px solid var(--jp-teal);
    }

    /* Groups */
    .jp-price-group + .jp-price-group{ margin-top:40px; }
    .jp-price-group-title{ font-size:1.15rem; font-weight:800; color:var(--jp-ink); margin-bottom:18px; display:flex; align-items:center; gap:10px; overflow-wrap:anywhere; }
    .jp-price-group-title::before{ content:""; display:inline-block; width:4px; height:20px; background:var(--jp-gradient); border-radius:2px; flex:0 0 auto; }

    /* ===== Pricing cards: same card language as the social and job cards ===== */
    .jp-price-grid{ display:grid; grid-template-columns:repeat(auto-fill, minmax(min(100%, 300px), 1fr)); gap:16px; }
    .jp-price-card{
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
    .jp-price-card:hover{ border-color:var(--jp-teal); box-shadow:0 14px 30px rgba(11,28,46,0.1); transform:translateY(-2px); }

    .jp-price-card .card-top{ display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:14px; }
    .jp-price-card .icon-tile{ width:48px; height:48px; border-radius:12px; background:var(--jp-bg-soft); border:1px solid var(--jp-line); display:flex; align-items:center; justify-content:center; color:var(--jp-teal); flex:0 0 auto; }

    .jp-pill{ font-size:10.5px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; padding:4px 10px; border-radius:20px; border:1px solid var(--jp-line); display:inline-flex; align-items:center; white-space:nowrap; }
    .jp-pill-popular{ background:#FFF6E0; color:#B8860B; border-color:rgba(184,134,11,.15); }
    .jp-pill-urgent{ background:#FEECEC; color:#C0392B; border-color:rgba(192,57,43,.15); }
    .jp-pill-enterprise{ background:#E7F1FB; color:#1D6FCC; border-color:rgba(29,111,204,.15); }

    .jp-price-card .tagline{ color:var(--jp-teal); font-size:11px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; margin-bottom:6px; overflow-wrap:anywhere; }
    .jp-price-card .name{ font-weight:800; font-size:1.05rem; color:var(--jp-ink); margin-bottom:8px; line-height:1.3; overflow-wrap:anywhere; }
    .jp-price-card .description{ color:var(--jp-muted); font-size:.86rem; line-height:1.6; margin-bottom:16px; overflow-wrap:anywhere; }

    .jp-price-card .price{ font-size:1.6rem; font-weight:800; color:var(--jp-ink); line-height:1.2; margin-bottom:4px; overflow-wrap:anywhere; }
    .jp-price-card .price .interval{ font-size:.9rem; font-weight:500; color:var(--jp-muted); }
    .jp-price-card.is-free .price{ color:#1E9E4C; }
    .jp-price-card .billing-type{ font-size:.75rem; color:var(--jp-muted); font-weight:600; text-transform:uppercase; letter-spacing:.03em; min-height:1em; }

    .jp-price-card .features{ list-style:none; padding:14px 0 0; margin:16px 0 18px; border-top:1px solid var(--jp-line); }
    .jp-price-card .features li{ font-size:.82rem; color:#33475B; margin-bottom:8px; display:flex; align-items:flex-start; gap:8px; line-height:1.45; min-width:0; overflow-wrap:anywhere; }
    .jp-price-card .features li::before{
        content:""; display:inline-block; width:14px; height:14px; flex:0 0 auto; margin-top:2px;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2303A588'%3E%3Cpath d='M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/%3E%3C/svg%3E");
        background-size:contain; background-repeat:no-repeat;
    }

    .jp-price-card .card-foot{ margin-top:auto; }
    .jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
    .jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }
    .jp-btn-free{ background:#E9F9EF; border:1px solid rgba(30,158,76,0.15); color:#1E9E4C; font-weight:700; border-radius:10px; }
    .jp-btn-free:hover{ background:#D6F2E0; color:#1E9E4C; }

    .jp-empty-card{ background:#fff; border:1px dashed var(--jp-line); border-radius:16px; }

    /* CTA banner */
    .jp-cta-wrap{ position:relative; z-index:2; margin-top:2.5rem; margin-bottom:-5rem; }
    .jp-cta-banner{ background:linear-gradient(90deg, #20AA3E 0%, #03A588 100%); border-radius:22px; padding:2rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap; box-shadow:0 20px 50px rgba(11,28,46,0.15); }
    .jp-cta-banner .cta-text{ flex:1 1 320px; min-width:0; }
    .jp-cta-banner .cta-actions{ display:flex; gap:12px; flex-wrap:wrap; flex:0 0 auto; }
    @media (min-width: 992px){ .jp-cta-banner{ padding:3rem; } }

    /* ===== Mobile ===== */
    @media (max-width: 575.98px){
        .jp-price-card{ padding:16px; }
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
        <div class="jp-kicker mb-2">Pricing</div>
        <h1>Simple, Transparent Pricing</h1>
        <p>Pay only for what you need. No hidden fees, no surprises: the same flat rates for everyone in {{ country_name() }}.</p>
    </div>
</div>

<!-- ====================== PRICING ====================== -->
<div class="jp-listing-bg">
    <div class="container py-10 py-lg-12">

        @if(empty($groupedList))
            <div class="jp-empty-card text-center py-10 px-4">
                <i class="ki-duotone ki-information-5 fs-3x text-muted d-block mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <p class="fw-semibold fs-5 mb-1">No pricing available right now</p>
                <p class="text-muted mb-0">Please check back soon, we are updating our pricing for {{ country_name() }}.</p>
            </div>
        @else
            @foreach($groupedList as $familyKey => $group)
                <section class="jp-price-group">
                    <h2 class="jp-price-group-title">{{ $group['label'] ?? '' }}</h2>

                    <div class="jp-price-grid">
                        @foreach(($group['services'] ?? []) as $service)
                            @php
                                $isFree = ($service['billing_type'] ?? '') === 'free'
                                    || (($service['price']['amount'] ?? null) !== null && (float) $service['price']['amount'] === 0.0);
                                $badge = $service['badge'] ?? null;
                                $badgeClass = match ($badge) {
                                    'popular'    => 'jp-pill-popular',
                                    'urgent'     => 'jp-pill-urgent',
                                    'enterprise' => 'jp-pill-enterprise',
                                    default      => null,
                                };
                            @endphp

                            <div class="jp-price-card {{ $isFree ? 'is-free' : '' }}">

                                <div class="card-top">
                                    <div class="icon-tile">
                                        {!! serviceIconSvg($service['icon'] ?? ($service['key'] ?? null)) !!}
                                    </div>
                                    @if($badgeClass)
                                        <span class="jp-pill {{ $badgeClass }}">{{ ucfirst($badge) }}</span>
                                    @endif
                                </div>

                                @if(!empty($service['tagline']))
                                    <div class="tagline">{{ $service['tagline'] }}</div>
                                @endif

                                <div class="name">{{ $service['name'] ?? '' }}</div>
                                <div class="description">{{ $service['description'] ?? '' }}</div>

                                <div class="price">
                                    @if($isFree)
                                        Free
                                    @elseif(!empty($service['price']['formatted']))
                                        {{ $service['price']['formatted'] }}
                                        @if(!empty($service['price']['interval']))
                                            <span class="interval">/ {{ $service['price']['interval'] }}</span>
                                        @endif
                                    @else
                                        Contact us
                                    @endif
                                </div>

                                <div class="billing-type">
                                    {{ $service['billing_label'] ?? '' }}
                                    @if(!empty($service['turnaround_label']))
                                        · {{ $service['turnaround_label'] }} turnaround
                                    @endif
                                </div>

                                @if(!empty($service['features']))
                                    <ul class="features">
                                        @foreach($service['features'] as $feature)
                                            <li>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                @endif

                                <div class="card-foot">
                                    @if($isFree)
                                        <a href="{{ route('register') }}?as=employer" class="btn btn-sm jp-btn-free w-100 py-3">Get Started Free</a>
                                    @else
                                        <a href="{{ route('register') }}" class="btn btn-sm jp-btn-primary w-100 py-3">Get Started</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @endif
    </div>

    <!-- CTA Banner -->
    <div class="jp-cta-wrap">
        <div class="container">
            <div class="jp-cta-banner">
                <div class="cta-text">
                    <div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Ready to get started?</div>
                    <div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Join thousands of job seekers and employers using our platform in {{ country_name() }}.</div>
                </div>
                <div class="cta-actions">
                    <a href="{{ route('jobs.index') }}" class="btn btn-lg btn-light fw-bold">Browse Jobs</a>
                    <a href="{{ route('register') }}?as=employer" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">Post a Job</a>
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- /.jp-page --}}

@endsection