@extends('layouts.app')

@section('title', 'Pricing - ' . country_name())
@section('meta_description', 'Simple, transparent pricing for job posting, CV services, and job seeker tools in ' . country_name() . '.')

@push('styles')
<style>
    .pricing-page {
        background: var(--jp-bg-page);
        background-image:
            radial-gradient(65% 45% at 100% 0%, rgba(3,165,136,0.12) 0%, transparent 60%),
            radial-gradient(50% 40% at 0% 10%, rgba(11,28,46,0.08) 0%, transparent 60%);
        border-top: 3px solid var(--jp-teal);
        min-height: 80vh;
        padding: 40px 0;
    }
    .pricing-header {
        text-align: center;
        margin-bottom: 40px;
    }
    .pricing-header h1 {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--jp-ink);
    }
    .pricing-header p {
        color: var(--jp-muted);
        font-size: 1.1rem;
        max-width: 640px;
        margin: 0 auto;
    }

    .pricing-group {
        margin-bottom: 48px;
    }
    .pricing-group-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--jp-ink);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .pricing-group-title::before {
        content: "";
        display: inline-block;
        width: 4px;
        height: 20px;
        background: var(--jp-gradient);
        border-radius: 2px;
    }

    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 22px;
    }

    .pricing-card {
        background: linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%);
        border: 1px solid var(--jp-line);
        border-radius: 14px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 6px 18px rgba(11, 28, 46, 0.06);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .pricing-card:hover {
        border-color: var(--jp-teal);
        box-shadow: 0 14px 30px rgba(11, 28, 46, 0.1);
        transform: translateY(-2px);
    }

    .pricing-card .price-badge {
        position: absolute;
        top: 14px;
        right: 14px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .price-badge.popular  { background: #FFF6E0; color: #B8860B; border: 1px solid rgba(184,134,11,.15); }
    .price-badge.urgent   { background: #FEECEC; color: #C0392B; border: 1px solid rgba(192,57,43,.15); }
    .price-badge.enterprise { background: #E7F1FB; color: #1D6FCC; border: 1px solid rgba(29,111,204,.15); }

    .pricing-card .pricing-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--jp-bg-soft);
        border: 1px solid var(--jp-line);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        color: var(--jp-teal);
    }
    .pricing-card .pricing-icon svg { width: 24px; height: 24px; }

    .pricing-card .tagline {
        color: var(--jp-teal);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .pricing-card .name {
        font-weight: 800;
        font-size: 1.05rem;
        color: var(--jp-ink);
        margin-bottom: 8px;
    }
    .pricing-card .description {
        color: var(--jp-muted);
        font-size: 0.86rem;
        line-height: 1.6;
        margin-bottom: 16px;
        flex-grow: 1;
    }

    .pricing-card .price {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--jp-ink);
        line-height: 1.2;
        margin-bottom: 4px;
    }
    .pricing-card .price .interval {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--jp-muted);
    }
    .pricing-card .billing-type {
        font-size: 0.75rem;
        color: var(--jp-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-bottom: 16px;
    }

    .pricing-card .features {
        list-style: none;
        padding: 0;
        margin: 0 0 18px 0;
        border-top: 1px solid var(--jp-line);
        padding-top: 14px;
    }
    .pricing-card .features li {
        font-size: 0.82rem;
        color: #33475B;
        margin-bottom: 8px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        line-height: 1.45;
    }
    .pricing-card .features li::before {
        content: "";
        display: inline-block;
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        margin-top: 2px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2303A588'%3E%3Cpath d='M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/%3E%3C/svg%3E");
        background-size: contain;
        background-repeat: no-repeat;
    }

    .pricing-card .pricing-cta {
        margin-top: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: var(--jp-gradient);
        color: #fff;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 10px 18px;
        border-radius: 10px;
        text-decoration: none;
        transition: filter .15s ease, transform .15s ease;
    }
    .pricing-card .pricing-cta:hover {
        color: #fff;
        filter: brightness(1.06);
        transform: translateY(-1px);
    }
    .pricing-card .pricing-cta.free {
        background: #E9F9EF;
        color: #1E9E4C;
    }
    .pricing-card .pricing-cta.free:hover { background: #d6f2e0; color: #1E9E4C; }

    .pricing-card.free .price { color: #1E9E4C; }

    .jp-kicker {
        color: var(--jp-teal);
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        font-size: 11.5px;
    }

    .pricing-empty {
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border: 1px dashed var(--jp-line);
        border-radius: 16px;
    }
    .pricing-empty i { font-size: 4rem; color: var(--jp-muted); display: block; margin-bottom: 16px; }
    .pricing-empty h3 { color: var(--jp-ink); margin-bottom: 8px; }
    .pricing-empty p { color: var(--jp-muted); }

    @media (max-width: 768px) {
        .pricing-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
        .pricing-header h1 { font-size: 1.8rem; }
    }
    @media (max-width: 480px) {
        .pricing-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="pricing-page">
    <div class="container">

        <div class="pricing-header">
            <div class="jp-kicker mb-2">Pricing</div>
            <h1>Simple, Transparent Pricing</h1>
            <p>Pay only for what you need. No hidden fees, no surprises — the same flat rates for everyone in {{ country_name() }}.</p>
        </div>

        @if(empty($grouped))
            <div class="pricing-empty">
                <i class="ki-duotone ki-information-5 fs-3x">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                </i>
                <h3>No pricing available right now</h3>
                <p>Please check back soon — we are updating our pricing for {{ country_name() }}.</p>
            </div>
        @else
            @foreach($grouped as $familyKey => $group)
                <div class="pricing-group">
                    <h2 class="pricing-group-title">{{ $group['label'] }}</h2>

                    <div class="pricing-grid">
                        @foreach($group['services'] as $service)
                            @php
                                $isFree = ($service['billing_type'] ?? '') === 'free'
                                    || (($service['price']['amount'] ?? null) !== null && (float) $service['price']['amount'] === 0.0);
                                $badge = $service['badge'] ?? null;
                                $badgeClass = match ($badge) {
                                    'popular'    => 'popular',
                                    'urgent'     => 'urgent',
                                    'enterprise' => 'enterprise',
                                    default      => null,
                                };
                            @endphp

                            <div class="pricing-card {{ $isFree ? 'free' : '' }}">
                                @if($badgeClass)
                                    <span class="price-badge {{ $badgeClass }}">
                                        {{ ucfirst($badge) }}
                                    </span>
                                @endif

                                <div class="pricing-icon">
                                    {!! serviceIconSvg($service['icon'] ?? $service['key']) !!}
                                </div>

                                @if(!empty($service['tagline']))
                                    <div class="tagline">{{ $service['tagline'] }}</div>
                                @endif

                                <div class="name">{{ $service['name'] }}</div>
                                <div class="description">{{ $service['description'] }}</div>

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

                                @if($isFree)
                                    <a href="{{ route('register') }}?as=employer" class="pricing-cta free">
                                        Get Started Free
                                    </a>
                                @else
                                    <a href="{{ route('register') }}" class="pricing-cta">
                                        Get Started
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        {{-- CTA banner (matches the social page) --}}
        <div class="mt-10 mb-n20 position-relative z-index-2">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-12">
                        <div class="d-flex flex-stack flex-wrap flex-md-nowrap card-rounded shadow p-8 p-lg-12" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
                            <div class="my-2 me-5">
                                <div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Ready to get started?</div>
                                <div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Join thousands of job seekers and employers using our platform in {{ country_name() }}.</div>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-3 flex-shrink-0 my-2">
                                <a href="{{ route('jobs.index') }}" class="btn btn-lg btn-light fw-bold">Browse Jobs</a>
                                <a href="{{ route('register') }}?as=employer" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">Post a Job</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@php
    function serviceIconSvg(?string $key): string
    {
        $attrs = 'width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

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

            // job posting family — fallback to a briefcase
            'job_post_free', 'job_post_standard', 'job_post_popular', 'job_post_priority', 'job_post_enterprise'
                => '<svg ' . $attrs . '><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',

            default
                => '<svg ' . $attrs . '><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
        };
    }
@endphp