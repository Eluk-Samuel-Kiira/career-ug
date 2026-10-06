@extends('layouts.app')

@section('title', 'Blog — Latest Articles from ' . country_name())
@section('meta_description', 'Read the latest articles, insights, and updates from ' . country_name() . '. Stay informed with our expert content.')
@section('og_title', 'Blog — Latest Articles from ' . country_name())
@section('og_description', 'Read the latest articles, insights, and updates from ' . country_name() . '. Stay informed with our expert content.')

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
    .jp-page img{ max-width:100%; }

    /* Header band + search */
    .jp-blog-band{ background:linear-gradient(120deg, #EAF7F5 0%, #E7F1FB 100%); border-bottom:1px solid var(--jp-line); }
    .jp-blog-band h1{ color:var(--jp-ink); font-weight:800; font-size:clamp(1.4rem, 5vw, 1.7rem); margin-bottom:.35rem; }
    .jp-blog-band p{ color:var(--jp-muted); margin-bottom:1.5rem; }
    .jp-blog-search{ display:flex; flex-wrap:wrap; gap:12px; align-items:stretch; }
    .jp-blog-search .search-box{ flex:1 1 260px; min-width:0; display:flex; align-items:center; background:#fff; border-radius:10px; padding:0 16px; border:1px solid var(--jp-line); height:48px; }
    .jp-blog-search .search-box .form-control{ border:0; box-shadow:none; padding-left:0; height:100%; min-width:0; background:transparent; }
    .jp-blog-search .form-select{ flex:0 1 200px; min-width:0; height:48px; border-radius:10px; border:1px solid var(--jp-line); background-color:#fff; }
    .jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
    .jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }
    .jp-blog-search .btn{ height:48px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; white-space:nowrap; }

    .jp-blog-bg{
        background:var(--jp-bg-page);
        background-image:
            radial-gradient(65% 55% at 100% 0%, rgba(3,165,136,0.14) 0%, transparent 60%),
            radial-gradient(55% 45% at 0% 15%, rgba(11,28,46,0.10) 0%, transparent 60%);
        border-top:3px solid var(--jp-teal);
    }

    /* ===== Blog cards ===== */
    .jp-blog-card{
        position:relative;
        background:#fff;
        border:1px solid var(--jp-line);
        border-radius:16px;
        overflow:hidden;
        transition:.2s;
        box-shadow:0 6px 18px rgba(11,28,46,0.06);
        height:100%;
        min-width:0;
        display:flex;
        flex-direction:column;
    }
    .jp-blog-card:hover{ border-color:var(--jp-teal); box-shadow:0 14px 30px rgba(11,28,46,0.1); transform:translateY(-4px); }
    .jp-blog-card .blog-img{ display:block; width:100%; aspect-ratio:16 / 9; height:auto; object-fit:cover; background:var(--jp-bg-soft); }
    .jp-blog-card .blog-body{ padding:20px 22px 18px; flex:1; display:flex; flex-direction:column; min-width:0; }
    .jp-blog-card .blog-title{ font-weight:700; color:var(--jp-ink); font-size:1.05rem; line-height:1.4; margin-bottom:8px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; overflow-wrap:anywhere; }
    .jp-blog-card .blog-title:hover{ color:var(--jp-teal); }
    .jp-blog-card .blog-excerpt{ color:var(--jp-muted); font-size:.88rem; line-height:1.6; flex:1; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; overflow-wrap:anywhere; }
    .jp-blog-card .blog-meta{ display:flex; align-items:center; gap:6px 12px; font-size:.78rem; color:var(--jp-muted); margin-top:14px; padding-top:14px; border-top:1px solid var(--jp-line); flex-wrap:wrap; }
    .jp-blog-card .blog-meta i{ color:#94A3B8; margin-right:4px; }
    .jp-blog-card .blog-category{ background:var(--jp-bg-soft); color:#3B5166; font-size:11px; font-weight:700; padding:4px 12px; border-radius:20px; border:1px solid var(--jp-line); display:inline-block; overflow-wrap:anywhere; }
    .jp-blog-card .blog-category.is-featured{ background:#FFF6E0; border-color:rgba(184,134,11,0.15); color:#B8860B; }

    /* ===== Featured article ===== */
    .jp-featured-blog{
        background:linear-gradient(120deg, var(--jp-navy) 0%, var(--jp-navy-2) 45%, var(--jp-teal) 100%);
        border-radius:16px;
        overflow:hidden;
        position:relative;
        box-shadow:0 16px 34px rgba(11,28,46,0.18);
        margin-bottom:24px;
    }
    .jp-featured-blog::before{
        content:""; position:absolute; inset:0;
        background-image:linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
        background-size:34px 34px; mask-image:linear-gradient(to right, black, transparent 70%); -webkit-mask-image:linear-gradient(to right, black, transparent 70%); pointer-events:none;
    }
    .jp-featured-blog .featured-content{ padding:28px 32px; position:relative; z-index:1; display:flex; flex-direction:column; gap:12px; min-width:0; }
    .jp-featured-blog .featured-badge{ background:rgba(255,255,255,0.16); color:#fff; border:1px solid rgba(255,255,255,0.3); font-size:11px; font-weight:800; letter-spacing:.03em; text-transform:uppercase; padding:5px 14px; border-radius:20px; display:inline-block; width:fit-content; max-width:100%; }
    .jp-featured-blog .featured-title{ color:#fff; font-weight:800; font-size:clamp(1.25rem, 5vw, 1.5rem); line-height:1.3; text-decoration:none; overflow-wrap:anywhere; }
    .jp-featured-blog .featured-title:hover{ color:#5EE29B; }
    .jp-featured-blog .featured-excerpt{ color:#AFC0D2; font-size:.95rem; line-height:1.6; max-width:600px; margin:0; overflow-wrap:anywhere; }
    .jp-featured-blog .featured-meta{ display:flex; align-items:center; gap:6px 16px; color:#AFC0D2; font-size:.82rem; flex-wrap:wrap; }
    .jp-featured-blog .featured-meta i{ color:rgba(255,255,255,0.4); margin-right:4px; }
    .jp-featured-blog .featured-read-btn{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; padding:10px 24px; text-decoration:none; display:inline-flex; align-items:center; width:fit-content; }
    .jp-featured-blog .featured-read-btn:hover{ color:#fff; filter:brightness(1.06); }
    .jp-featured-blog .featured-media{ position:relative; z-index:1; height:100%; min-height:200px; }
    .jp-featured-blog .featured-image{ display:block; width:100%; height:100%; min-height:200px; object-fit:cover; }
    .jp-featured-blog .featured-placeholder{ width:100%; height:100%; min-height:200px; display:flex; align-items:center; justify-content:center; background:var(--jp-navy-2); }

    @media (min-width: 768px){
        .jp-featured-blog .row{ min-height:320px; }
        .jp-featured-blog .featured-title{ font-size:2rem; }
    }

    .jp-empty-card{ background:#fff; border:1px dashed var(--jp-line); border-radius:16px; }
    .jp-pagination{ flex-wrap:wrap; justify-content:center; row-gap:6px; }

    /* CTA banner */
    .jp-cta-wrap{ position:relative; z-index:2; margin-top:2.5rem; margin-bottom:-5rem; }
    .jp-cta-banner{ background:linear-gradient(90deg, #20AA3E 0%, #03A588 100%); border-radius:22px; padding:2rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap; box-shadow:0 20px 50px rgba(11,28,46,0.15); }
    .jp-cta-banner .cta-text{ flex:1 1 320px; min-width:0; }
    .jp-cta-banner .cta-actions{ display:flex; gap:12px; flex-wrap:wrap; flex:0 0 auto; }
    @media (min-width: 992px){ .jp-cta-banner{ padding:3rem; } }

    /* ===== Mobile ===== */
    @media (max-width: 575.98px){
        .jp-blog-search .search-box, .jp-blog-search .form-select, .jp-blog-search .btn{ flex:1 1 100%; width:100%; }
        .jp-blog-card .blog-body{ padding:16px 18px 16px; }
        .jp-featured-blog .featured-content{ padding:20px 18px 22px; }
        .jp-featured-blog .featured-read-btn{ width:100%; justify-content:center; }
        .jp-cta-banner{ padding:1.5rem 1.25rem; border-radius:18px; }
        .jp-cta-banner .cta-actions{ width:100%; flex-direction:column; }
        .jp-cta-banner .cta-actions .btn{ width:100%; }
    }
</style>
@endpush

@section('content')

@php
    use Illuminate\Support\Str;

    $blogList = is_array($blogs) ? ($blogs['data'] ?? []) : [];
    $categoryList = $categories ?? [];

    $formatDate = function ($date) {
        if (empty($date)) return '';
        try {
            return \Carbon\Carbon::parse($date)->format('M d, Y');
        } catch (\Throwable $e) {
            return '';
        }
    };

    $readTime = function ($content) {
        if (empty($content)) return '1 min read';
        $words = str_word_count(strip_tags($content));
        $minutes = max(1, ceil($words / 200));
        return $minutes . ' min read';
    };

    $defaultCover = asset('assets/media/books/img-72.jpg');
@endphp

<div class="jp-page">

<!-- ====================== BLOG HEADER ====================== -->
<div class="jp-blog-band py-8 py-lg-10">
    <div class="container">
        <h1>Latest Articles</h1>
        <p>Insights, updates, and stories from our community</p>

        <form action="{{ route('blog.index') }}" method="GET" class="jp-blog-search">
            <div class="search-box">
                <i class="ki-duotone ki-magnifier fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" name="search" class="form-control" placeholder="Search articles..." value="{{ request('search') }}" />
            </div>
            <select name="category" class="form-select" onchange="this.form.submit()" aria-label="Filter by category">
                <option value="">All Categories</option>
                @foreach($categoryList as $category)
                    @php
                        $catValue = is_array($category) ? ($category['slug'] ?? $category['id']) : ($category->slug ?? $category->id);
                        $catName  = is_array($category) ? $category['name'] : $category->name;
                    @endphp
                    <option value="{{ $catValue }}" {{ request('category') == $catValue ? 'selected' : '' }}>{{ $catName }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn jp-btn-primary px-6">Search</button>
            @if(request('search') || request('category'))
                <a href="{{ route('blog.index') }}" class="btn btn-outline-secondary px-5">Clear</a>
            @endif
        </form>
    </div>
</div>

<!-- ====================== BLOG LISTINGS ====================== -->
<div class="jp-blog-bg">
    <div class="container py-10 py-lg-12">

        <!-- Featured Blog -->
        @if(!empty($featuredBlogs) && count($featuredBlogs) > 0)
            @php $featured = $featuredBlogs[0]; @endphp
            <div class="jp-featured-blog">
                <div class="row g-0">
                    {{-- Image first on mobile, to the right on desktop --}}
                    <div class="col-md-5 order-1 order-md-2">
                        <div class="featured-media">
                            @if(!empty($featured['cover_image']))
                                <img src="{{ $featured['cover_image'] }}" alt="{{ $featured['title'] ?? 'Featured' }}" class="featured-image" onerror="this.src='{{ $defaultCover }}'; this.onerror=null;">
                            @else
                                <div class="featured-placeholder">
                                    <i class="ki-duotone ki-picture fs-3x text-white-50"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-7 order-2 order-md-1">
                        <div class="featured-content">
                            <span class="featured-badge">
                                <i class="ki-duotone ki-star fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Featured Article
                            </span>
                            <a href="{{ route('blog.show', $featured['slug']) }}" class="featured-title">
                                {{ $featured['title'] ?? 'Featured Article' }}
                            </a>
                            @if(!empty($featured['excerpt']))
                                <p class="featured-excerpt">{{ Str::limit(strip_tags($featured['excerpt']), 150) }}</p>
                            @endif
                            <div class="featured-meta">
                                <span>
                                    <i class="ki-duotone ki-calendar fs-6"><span class="path1"></span><span class="path2"></span></i>
                                    {{ $formatDate($featured['published_at'] ?? null) }}
                                </span>
                                @if(!empty($featured['category']))
                                    <span>
                                        <i class="ki-duotone ki-folder fs-6"><span class="path1"></span><span class="path2"></span></i>
                                        {{ $featured['category'] }}
                                    </span>
                                @endif
                                <span>
                                    <i class="ki-duotone ki-eye fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    {{ number_format($featured['view_count'] ?? 0) }} views
                                </span>
                                <span>
                                    <i class="ki-duotone ki-time fs-6"><span class="path1"></span><span class="path2"></span></i>
                                    {{ $readTime($featured['content'] ?? '') }}
                                </span>
                            </div>
                            <a href="{{ route('blog.show', $featured['slug']) }}" class="featured-read-btn mt-2">
                                Read Article <i class="ki-duotone ki-arrow-right fs-3 ms-2"><span class="path1"></span><span class="path2"></span></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Blog Grid Header -->
        <div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
            <div class="fw-semibold text-gray-700">
                Showing <span class="fw-bold text-gray-900">{{ count($blogList) }}</span> of {{ number_format($totalBlogs ?? 0) }} articles
            </div>
        </div>

        <!-- Blog Grid -->
        <div class="row gy-4 gx-4">
            @forelse($blogList as $blog)
                @php
                    $isFeatured = !empty($blog['is_featured']);
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="jp-blog-card">
                        <img src="{{ !empty($blog['cover_image']) ? $blog['cover_image'] : $defaultCover }}"
                             alt="{{ $blog['title'] ?? 'Blog' }}"
                             class="blog-img"
                             loading="lazy"
                             onerror="this.src='{{ $defaultCover }}'; this.onerror=null;">
                        <div class="blog-body">
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                @if($isFeatured)
                                    <span class="blog-category is-featured">
                                        <i class="ki-duotone ki-star fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                                        Featured
                                    </span>
                                @endif
                                @if(!empty($blog['category']))
                                    <span class="blog-category">{{ $blog['category'] }}</span>
                                @endif
                            </div>
                            {{-- stretched-link makes the whole card clickable --}}
                            <a href="{{ route('blog.show', $blog['slug']) }}" class="blog-title text-decoration-none stretched-link">
                                {{ $blog['title'] ?? 'Blog Post' }}
                            </a>
                            <p class="blog-excerpt">
                                {{ Str::limit(strip_tags($blog['excerpt'] ?? $blog['content'] ?? ''), 120) }}
                            </p>
                            <div class="blog-meta">
                                <span>
                                    <i class="ki-duotone ki-calendar fs-6"><span class="path1"></span><span class="path2"></span></i>
                                    {{ $formatDate($blog['published_at'] ?? null) }}
                                </span>
                                <span>
                                    <i class="ki-duotone ki-eye fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    {{ number_format($blog['view_count'] ?? 0) }}
                                </span>
                                <span class="ms-auto">
                                    <i class="ki-duotone ki-time fs-6"><span class="path1"></span><span class="path2"></span></i>
                                    {{ $readTime($blog['content'] ?? '') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="jp-empty-card text-center py-10 px-4">
                        <i class="ki-duotone ki-file-deleted fs-3x text-muted d-block mb-3"><span class="path1"></span><span class="path2"></span></i>
                        <p class="fw-semibold fs-5 mb-1">No articles found</p>
                        <p class="text-muted">Try adjusting your search or check back later.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if(!empty($blogs['pagination']) && ($blogs['pagination']['last_page'] ?? 1) > 1)
        @php
            $current = (int) ($blogs['pagination']['current_page'] ?? 1);
            $last = (int) ($blogs['pagination']['last_page'] ?? 1);
            $window = 2;
            $start = max(1, $current - $window);
            $end = min($last, $current + $window);
        @endphp
        <div class="d-flex justify-content-center mt-10">
            <nav>
                <ul class="pagination jp-pagination">
                    <li class="page-item {{ $current <= 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ url()->current() }}?page={{ $current - 1 }}&{{ http_build_query(request()->except('page')) }}">Prev</a>
                    </li>

                    @if($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ url()->current() }}?page=1&{{ http_build_query(request()->except('page')) }}">1</a></li>
                        @if($start > 2)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ url()->current() }}?page={{ $i }}&{{ http_build_query(request()->except('page')) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
                        <li class="page-item"><a class="page-link" href="{{ url()->current() }}?page={{ $last }}&{{ http_build_query(request()->except('page')) }}">{{ $last }}</a></li>
                    @endif

                    <li class="page-item {{ $current >= $last ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ url()->current() }}?page={{ $current + 1 }}&{{ http_build_query(request()->except('page')) }}">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>

    <!-- ====================== CTA BANNER ====================== -->
    <div class="jp-cta-wrap">
        <div class="container">
            <div class="jp-cta-banner">
                <div class="cta-text">
                    <div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Never miss an update</div>
                    <div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Subscribe to our newsletter for the latest articles and insights.</div>
                </div>
                <div class="cta-actions">
                    <a href="{{ route('blog.index') }}" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">Read More</a>
                    <a href="{{ route('register') }}" class="btn btn-lg btn-light fw-bold">Subscribe</a>
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- /.jp-page --}}

@endsection