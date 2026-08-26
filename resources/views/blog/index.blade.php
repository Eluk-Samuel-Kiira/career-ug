@extends('layouts.app')

@section('title', 'Blog — Latest Articles from ' . country_name())
@section('meta_description', 'Read the latest articles, insights, and updates from ' . country_name() . '. Stay informed with our expert content.')

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

    .jp-blog-band{ 
        background: linear-gradient(120deg, #EAF7F5 0%, #E7F1FB 100%); 
        border-bottom: 1px solid var(--jp-line); 
    }
    .jp-blog-band h1{ 
        color: var(--jp-ink); 
        font-weight: 800; 
        font-size: 1.7rem; 
        margin-bottom: .35rem; 
    }
    .jp-blog-band p{ 
        color: var(--jp-muted); 
        margin-bottom: 1.5rem; 
    }

    .jp-blog-bg{
        background: var(--jp-bg-page);
        background-image:
            radial-gradient(65% 55% at 100% 0%, rgba(3,165,136,0.14) 0%, transparent 60%),
            radial-gradient(55% 45% at 0% 15%, rgba(11,28,46,0.10) 0%, transparent 60%);
        border-top: 3px solid var(--jp-teal);
    }

    .jp-blog-card{
        background: #fff;
        border: 1px solid var(--jp-line);
        border-radius: 16px;
        overflow: hidden;
        transition: .2s;
        box-shadow: 0 6px 18px rgba(11,28,46,0.06);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .jp-blog-card:hover{
        border-color: var(--jp-teal);
        box-shadow: 0 14px 30px rgba(11,28,46,0.1);
        transform: translateY(-4px);
    }
    .jp-blog-card .blog-img{
        height: 200px;
        object-fit: cover;
        width: 100%;
        background: var(--jp-bg-soft);
    }
    .jp-blog-card .blog-body{
        padding: 20px 22px 18px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .jp-blog-card .blog-title{
        font-weight: 700;
        color: var(--jp-ink);
        font-size: 1.05rem;
        line-height: 1.4;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .jp-blog-card .blog-title:hover{
        color: var(--jp-teal);
    }
    .jp-blog-card .blog-excerpt{
        color: var(--jp-muted);
        font-size: .88rem;
        line-height: 1.6;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .jp-blog-card .blog-meta{
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: .78rem;
        color: var(--jp-muted);
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--jp-line);
        flex-wrap: wrap;
    }
    .jp-blog-card .blog-meta i{
        color: #94A3B8;
        margin-right: 4px;
    }
    .jp-blog-card .blog-category{
        background: var(--jp-bg-soft);
        color: #3B5166;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        border: 1px solid var(--jp-line);
        display: inline-block;
    }

    /* Featured blog card - large */
    .jp-featured-blog{
        background: linear-gradient(120deg, var(--jp-navy) 0%, var(--jp-navy-2) 45%, var(--jp-teal) 100%);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 16px 34px rgba(11,28,46,0.18);
        margin-bottom: 24px;
    }
    .jp-featured-blog::before{
        content: "";
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
        background-size: 34px 34px;
        mask-image: linear-gradient(to right, black, transparent 70%);
        pointer-events: none;
    }
    .jp-featured-blog .featured-content{
        padding: 28px 32px;
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .jp-featured-blog .featured-badge{
        background: rgba(255,255,255,0.16);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.3);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        padding: 5px 14px;
        border-radius: 20px;
        display: inline-block;
        width: fit-content;
    }
    .jp-featured-blog .featured-title{
        color: #fff;
        font-weight: 800;
        font-size: 1.5rem;
        line-height: 1.3;
        text-decoration: none;
    }
    .jp-featured-blog .featured-title:hover{
        color: #5EE29B;
    }
    .jp-featured-blog .featured-excerpt{
        color: #AFC0D2;
        font-size: .95rem;
        line-height: 1.6;
        max-width: 600px;
    }
    .jp-featured-blog .featured-meta{
        display: flex;
        align-items: center;
        gap: 16px;
        color: #AFC0D2;
        font-size: .82rem;
        flex-wrap: wrap;
    }
    .jp-featured-blog .featured-meta i{
        color: rgba(255,255,255,0.4);
        margin-right: 4px;
    }
    .jp-featured-blog .featured-read-btn{
        background: var(--jp-gradient);
        border: none;
        color: #fff;
        font-weight: 700;
        border-radius: 10px;
        padding: 10px 24px;
        text-decoration: none;
        display: inline-block;
        width: fit-content;
    }
    .jp-featured-blog .featured-read-btn:hover{
        color: #fff;
        filter: brightness(1.06);
    }
    .jp-featured-blog .featured-image{
        height: 100%;
        min-height: 200px;
        object-fit: cover;
        width: 100%;
    }

    @media (min-width: 768px) {
        .jp-featured-blog .row{
            min-height: 320px;
        }
        .jp-featured-blog .featured-title{
            font-size: 2rem;
        }
    }

    .jp-empty-card{ 
        background: #fff; 
        border: 1px dashed var(--jp-line); 
        border-radius: 16px; 
    }
</style>
@endpush

@section('content')

@php
    $blogList = is_array($blogs) ? ($blogs['data'] ?? []) : [];
    
    $formatDate = function($date) {
        if (empty($date)) return '';
        try {
            return \Carbon\Carbon::parse($date)->format('M d, Y');
        } catch (\Throwable $e) {
            return '';
        }
    };
    
    $readTime = function($content) {
        if (empty($content)) return '1 min read';
        $words = str_word_count(strip_tags($content));
        $minutes = max(1, ceil($words / 200));
        return $minutes . ' min read';
    };
@endphp

<!-- ====================== BLOG HEADER ====================== -->
<div class="jp-blog-band py-8 py-lg-10">
    <div class="container">
        <h1>Latest Articles</h1>
        <p>Insights, updates, and stories from our community</p>

        <form action="{{ route('blog.index') }}" method="GET" class="d-flex flex-column flex-md-row gap-3 mt-4">
            <div class="flex-grow-1 d-flex align-items-center bg-white rounded-3 px-4 py-2" style="border:1px solid var(--jp-line); height: 48px;">
                <i class="ki-duotone ki-magnifier fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" name="search" class="form-control border-0 ps-0 h-100" placeholder="Search articles..." value="{{ request('search') }}" />
            </div>
            <div>
                <select name="category" class="form-select" onchange="this.form.submit()" style="min-width:160px; border-radius:10px; border:1px solid var(--jp-line); height: 48px; background-color: #fff;">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ is_array($category) ? ($category['slug'] ?? $category['id']) : $category->slug ?? $category->id }}" 
                            {{ request('category') == (is_array($category) ? ($category['slug'] ?? $category['id']) : $category->slug ?? $category->id) ? 'selected' : '' }}>
                            {{ is_array($category) ? $category['name'] : $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn jp-btn-primary px-6 py-3" style="background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; height: 48px; white-space: nowrap;">
                Search
            </button>
            @if(request('search') || request('category'))
                <a href="{{ route('blog.index') }}" class="btn btn-outline-secondary" style="height: 48px; display: flex; align-items: center; border-radius:10px;">
                    Clear
                </a>
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
                    <div class="col-md-7">
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
                                    <i class="ki-duotone ki-eye fs-6"><span class="path1"></span><span class="path2"></span></i>
                                    {{ number_format($featured['view_count'] ?? 0) }} views
                                </span>
                                <span>
                                    <i class="ki-duotone ki-clock fs-6"><span class="path1"></span><span class="path2"></span></i>
                                    {{ $readTime($featured['content'] ?? '') }}
                                </span>
                            </div>
                            <a href="{{ route('blog.show', $featured['slug']) }}" class="featured-read-btn mt-2">
                                Read Article <i class="ki-duotone ki-arrow-right fs-3 ms-2"><span class="path1"></span><span class="path2"></span></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-5">
                        @if(!empty($featured['cover_image']))
                            <img src="{{ $featured['cover_image'] }}" alt="{{ $featured['title'] ?? 'Featured' }}" class="featured-image">
                        @else
                            <div class="featured-image d-flex align-items-center justify-content-center" style="background: var(--jp-navy-2);">
                                <i class="ki-duotone ki-picture fs-3x text-white-50"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                        @endif
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
        <div class="row g-4">
            @forelse($blogList as $blog)
                @php
                    $isFeatured = $blog['is_featured'] ?? false;
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="jp-blog-card">
                        @if(!empty($blog['cover_image']))
                            <img src="{{ $blog['cover_image'] }}" 
                                alt="{{ $blog['title'] ?? 'Blog' }}" 
                                class="blog-img"
                                onerror="this.src='{{ asset('assets/media/books/img-72.jpg') }}'; this.onerror=null;">
                        @else
                            <div class="blog-img d-flex align-items-center justify-content-center" style="background: var(--jp-bg-soft);">
                                <img src="{{ asset('assets/media/books/img-72.jpg') }}" alt="Default" class="blog-img">
                            </div>
                        @endif
                        <div class="blog-body">
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                @if($isFeatured)
                                    <span class="blog-category" style="background: #FFF6E0; border-color: rgba(184,134,11,0.15); color: #B8860B;">
                                        <i class="ki-duotone ki-star fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                                        Featured
                                    </span>
                                @endif
                                @if(!empty($blog['category']))
                                    <span class="blog-category">{{ $blog['category'] }}</span>
                                @endif
                            </div>
                            <a href="{{ route('blog.show', $blog['slug']) }}" class="blog-title text-decoration-none">
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
                                    <i class="ki-duotone ki-eye fs-6"><span class="path1"></span><span class="path2"></span></i>
                                    {{ number_format($blog['view_count'] ?? 0) }}
                                </span>
                                <span class="ms-auto">
                                    <i class="ki-duotone ki-clock fs-6"><span class="path1"></span><span class="path2"></span></i>
                                    {{ $readTime($blog['content'] ?? '') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="jp-empty-card text-center py-10">
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
                <ul class="pagination">
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
    <div class="mt-10 mb-n20 position-relative z-index-2">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-12">  
                    <div class="d-flex flex-stack flex-wrap flex-md-nowrap card-rounded shadow p-8 p-lg-12" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
                        <div class="my-2 me-5">
                            <div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Never miss an update</div>
                            <div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Subscribe to our newsletter for the latest articles and insights.</div>
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-3 flex-shrink-0 my-2">
                            <a href="{{ route('blog.index') }}" class="btn btn-lg btn-outline border-2 btn-outline-white fw-bold">Read More</a>
                            <a href="{{ route('register') }}" class="btn btn-lg btn-light fw-bold">Subscribe</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection