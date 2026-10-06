@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    /* ---------------------------------------------------------------
     | Core data
     * --------------------------------------------------------------- */
    $blogTitle  = $blog['title'] ?? 'Blog Post';
    $authorName = $blog['author_name'] ?? null;
    $category   = $blog['category'] ?? null;
    $categorySlug = $blog['category_slug'] ?? $category;
    $tags       = !empty($blog['tags']) && is_array($blog['tags']) ? $blog['tags'] : [];

    $cleanText = function ($html) {
        $t = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $t));
    };

    $readTime = function ($content) {
        if (empty($content)) return '1 min read';
        $words = str_word_count(strip_tags($content));
        $minutes = max(1, ceil($words / 200));
        return $minutes . ' min read';
    };
    $formatDate = function ($date) {
        if (empty($date)) return '';
        try {
            return \Carbon\Carbon::parse($date)->format('M d, Y');
        } catch (\Throwable $e) {
            return '';
        }
    };
    $isoDate = function ($d) {
        try { return \Carbon\Carbon::parse($d)->toIso8601String(); } catch (\Throwable $e) { return null; }
    };

    $defaultCover = asset('assets/media/books/img-72.jpg');
    $toAbsolute = function ($url) {
        if (empty($url)) return null;
        if (Str::startsWith($url, ['http://', 'https://'])) return $url;
        if (Str::startsWith($url, '//')) return 'https:' . $url;
        return url($url);
    };

    /* ---------------------------------------------------------------
     | SEO + social share
     * --------------------------------------------------------------- */
    $blogExcerpt    = $cleanText($blog['excerpt'] ?? $blog['content'] ?? '');
    $seoTitle       = $blog['meta_title'] ?? ($blogTitle . ' — ' . country_name());
    $seoDescription = !empty($blog['meta_description'])
        ? $blog['meta_description']
        : Str::limit($blogExcerpt, 158, '…');
    $seoKeywords    = strtolower(implode(', ', array_filter(array_merge(
        [$blogTitle, $category, 'blog', 'career advice ' . country_name()],
        $tags
    ))));

    $canonicalUrl  = url()->current();
    $hasCover      = !empty($blog['cover_image']);
    $shareImage    = $toAbsolute($blog['cover_image'] ?? null) ?: asset('assets/media/og-default.png');
    $shareImageAlt = $blogTitle;

    /* ---------------------------------------------------------------
     | Structured data: BlogPosting + Breadcrumbs
     * --------------------------------------------------------------- */
    $stripNulls = function ($arr) use (&$stripNulls) {
        foreach ($arr as $k => $v) {
            if (is_array($v)) $arr[$k] = $v = $stripNulls($v);
            if ($v === null || $v === '' || $v === []) unset($arr[$k]);
        }
        return $arr;
    };

    $blogPosting = $stripNulls([
        '@context'         => 'https://schema.org',
        '@type'            => 'BlogPosting',
        'headline'         => Str::limit($blogTitle, 110, ''),
        'description'      => $seoDescription,
        'image'            => $hasCover ? [$shareImage] : null,
        'datePublished'    => !empty($blog['published_at']) ? $isoDate($blog['published_at']) : null,
        'dateModified'     => !empty($blog['updated_at']) ? $isoDate($blog['updated_at']) : (!empty($blog['published_at']) ? $isoDate($blog['published_at']) : null),
        'author'           => ['@type' => 'Person', 'name' => $authorName ?: app_name()],
        'publisher'        => ['@type' => 'Organization', 'name' => app_name()],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonicalUrl],
        'articleSection'   => $category,
        'keywords'         => $tags ? implode(', ', $tags) : null,
    ]);

    $breadcrumbItems = [['name' => 'Blog', 'url' => route('blog.index')]];
    if ($category) {
        $breadcrumbItems[] = ['name' => $category, 'url' => route('blog.index') . '?category=' . urlencode($categorySlug)];
    }
    $breadcrumbItems[] = ['name' => $blogTitle, 'url' => $canonicalUrl];
    $breadcrumbLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($breadcrumbItems)->values()->map(fn ($item, $i) => [
            '@type' => 'ListItem', 'position' => $i + 1, 'name' => $item['name'], 'item' => $item['url'],
        ])->all(),
    ];
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP;
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('keywords', $seoKeywords)
@section('canonical', $canonicalUrl)

@section('og_type', 'article')
@section('og_url', $canonicalUrl)
@section('og_title', $seoTitle)
@section('og_description', $seoDescription)
@section('og_image', $shareImage)
@section('og_image_alt', $shareImageAlt)

@section('twitter_card', 'summary_large_image')
@section('twitter_title', $seoTitle)
@section('twitter_description', $seoDescription)
@section('twitter_image', $shareImage)
@section('twitter_image_alt', $shareImageAlt)

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

    .jp-page-bg{
        background:var(--jp-bg-page);
        background-image:
            radial-gradient(65% 45% at 100% 0%, rgba(3,165,136,0.12) 0%, transparent 60%),
            radial-gradient(50% 40% at 0% 10%, rgba(11,28,46,0.08) 0%, transparent 60%);
        border-top:3px solid var(--jp-teal);
    }

    .jp-breadcrumb{ font-size:.82rem; color:var(--jp-muted); overflow-wrap:anywhere; }
    .jp-breadcrumb a{ color:var(--jp-muted); text-decoration:none; }
    .jp-breadcrumb a:hover{ color:var(--jp-teal); }

    .jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; }
    .jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }
    .jp-btn-outline{ border:1.5px solid var(--jp-line) !important; color:var(--jp-ink); font-weight:700; border-radius:10px; background:#fff; }
    .jp-btn-outline:hover{ border-color:var(--jp-teal) !important; color:var(--jp-teal); background:rgba(3,165,136,0.05); }

    /* Article header */
    .jp-blog-header-card{ background:linear-gradient(180deg, #F1FAF8 0%, #FFFFFF 60%); border:1px solid var(--jp-line); border-radius:16px; padding:28px 32px; box-shadow:0 10px 26px rgba(11,28,46,0.06); min-width:0; }
    .jp-blog-header-card h1{ font-size:clamp(1.35rem, 5.2vw, 1.8rem); font-weight:800; color:var(--jp-ink); line-height:1.3; overflow-wrap:anywhere; }
    .jp-blog-meta{ display:flex; flex-wrap:wrap; align-items:center; gap:6px 16px; font-size:.85rem; color:var(--jp-muted); }
    .jp-blog-meta > span{ display:inline-flex; align-items:center; min-width:0; max-width:100%; overflow-wrap:anywhere; }
    .jp-blog-meta i{ color:#94A3B8; margin-right:4px; flex:0 0 auto; }
    .jp-badge{ background:var(--jp-bg-soft); color:#3B5166; font-size:11px; font-weight:700; padding:4px 12px; border-radius:20px; border:1px solid var(--jp-line); display:inline-flex; align-items:center; overflow-wrap:anywhere; }
    .jp-badge-featured{ background:#FFF6E0; color:#B8860B; border-color:rgba(184,134,11,0.15); }

    /* Cover image */
    .jp-cover{ display:block; width:100%; aspect-ratio:16 / 9; max-height:420px; height:auto; object-fit:cover; border-radius:16px; border:1px solid var(--jp-line); background:var(--jp-bg-soft); }

    /* Content */
    .jp-content-card{ background:#fff; border:1px solid var(--jp-line); border-radius:16px; padding:32px; box-shadow:0 6px 18px rgba(11,28,46,0.05); min-width:0; }

    /* Rich text from the API: never allowed to break the layout */
    .jp-prose{ color:#33475B; font-size:.96rem; line-height:1.8; overflow-wrap:anywhere; word-break:break-word; min-width:0; }
    .jp-prose p{ margin-bottom:1.2em; }
    .jp-prose h2, .jp-prose h3{ color:var(--jp-ink); font-weight:700; margin-top:1.8em; margin-bottom:.6em; line-height:1.3; }
    .jp-prose ul, .jp-prose ol{ padding-left:1.3em; margin-bottom:1em; }
    .jp-prose li{ margin-bottom:.4em; }
    .jp-prose a{ color:var(--jp-teal); font-weight:600; text-decoration:none; }
    .jp-prose a:hover{ text-decoration:underline; }
    .jp-prose img, .jp-prose video{ max-width:100%; height:auto; border-radius:12px; margin:1.2em 0; }
    .jp-prose iframe{ max-width:100%; border:0; }
    .jp-prose iframe[src*="youtube"], .jp-prose iframe[src*="youtu.be"], .jp-prose iframe[src*="vimeo"]{ width:100%; height:auto; aspect-ratio:16 / 9; border-radius:12px; }
    .jp-prose pre{ max-width:100%; overflow-x:auto; white-space:pre-wrap; background:var(--jp-bg-soft); padding:14px 16px; border-radius:10px; }
    .jp-prose blockquote{ border-left:4px solid var(--jp-teal); padding-left:1.2em; margin:1em 0; color:var(--jp-muted); font-style:italic; }
    .jp-prose table{ display:block; max-width:100%; overflow-x:auto; border-collapse:collapse; margin-bottom:1em; }
    .jp-prose table td, .jp-prose table th{ border:1px solid var(--jp-line); padding:8px 12px; font-size:.9rem; }

    /* Share row */
    .jp-share-row{ display:flex; flex-wrap:wrap; align-items:center; gap:10px; }
    .jp-share-btn{ width:36px; height:36px; border-radius:9px; border:1px solid var(--jp-line); background:#fff; display:inline-flex; align-items:center; justify-content:center; color:var(--jp-muted); text-decoration:none; transition:.2s; padding:0; cursor:pointer; }
    .jp-share-btn:hover{ color:var(--jp-teal); border-color:var(--jp-teal); }
    .jp-share-btn .icon-done{ display:none; }
    .jp-share-btn.jp-copied{ color:#1E9E4C; border-color:#1E9E4C; }
    .jp-share-btn.jp-copied .icon-copy{ display:none; }
    .jp-share-btn.jp-copied .icon-done{ display:inline-flex; }

    /* Sidebar */
    .jp-sidebar-card{ background:#fff; border:1px solid var(--jp-line); border-radius:16px; padding:24px; box-shadow:0 6px 18px rgba(11,28,46,0.05); min-width:0; }
    .jp-sidebar-card + .jp-sidebar-card, .jp-sidebar-card + .jp-back-wrap{ margin-top:20px; }

    .jp-related-item{ display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--jp-line); text-decoration:none; min-width:0; }
    .jp-related-item:last-child{ border-bottom:none; }
    .jp-related-item .body{ min-width:0; flex:1 1 auto; }
    .jp-related-item .title{ color:var(--jp-ink); font-weight:700; font-size:.88rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; overflow-wrap:anywhere; }
    .jp-related-item:hover .title{ color:var(--jp-teal); }
    .jp-related-item .sub{ color:var(--jp-muted); font-size:.78rem; }
    .jp-related-item .related-img{ width:60px; height:60px; border-radius:10px; object-fit:cover; flex:0 0 auto; background:var(--jp-bg-soft); }

    /* CTA banner */
    .jp-cta-wrap{ position:relative; z-index:2; margin-top:2.5rem; margin-bottom:-5rem; }
    .jp-cta-banner{ background:linear-gradient(90deg, #20AA3E 0%, #03A588 100%); border-radius:22px; padding:2rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap; box-shadow:0 20px 50px rgba(11,28,46,0.15); }
    .jp-cta-banner .cta-text{ flex:1 1 320px; min-width:0; }
    .jp-cta-banner .cta-actions{ display:flex; gap:12px; flex-wrap:wrap; flex:0 0 auto; }

    @media (min-width: 992px){
        .jp-sticky-col{ position:sticky; top:24px; align-self:flex-start; }
        .jp-cta-banner{ padding:3rem; }
    }

    /* ===== Mobile ===== */
    @media (max-width: 575.98px){
        .jp-blog-header-card{ padding:18px; }
        .jp-content-card{ padding:20px 18px; }
        .jp-sidebar-card{ padding:20px 18px; }
        .jp-prose{ font-size:.94rem; line-height:1.75; }
        .jp-cover{ border-radius:12px; }
        .jp-cta-banner{ padding:1.5rem 1.25rem; border-radius:18px; }
        .jp-cta-banner .cta-actions{ width:100%; flex-direction:column; }
        .jp-cta-banner .cta-actions .btn{ width:100%; }
    }
</style>
@endpush

@section('content')

<div class="jp-page">

<!-- ====================== BREADCRUMB ====================== -->
<div class="border-bottom" style="border-color:var(--jp-line) !important; background:#fff;">
    <div class="container py-3">
        <nav class="jp-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('blog.index') }}">Blog</a>
            @if($category)
                <span class="mx-1">/</span>
                <a href="{{ route('blog.index') }}?category={{ urlencode($categorySlug) }}">{{ $category }}</a>
            @endif
            <span class="mx-1">/</span>
            <span class="text-gray-700">{{ Str::limit($blogTitle, 40) }}</span>
        </nav>
    </div>
</div>

<!-- ====================== MAIN CONTENT ====================== -->
<div class="jp-page-bg">
    <div class="container py-10 py-lg-12">
        <div class="row gy-6 gx-lg-6">

            <!-- Main Column -->
            <div class="col-lg-8" style="min-width:0;">

                <!-- Article header -->
                <div class="jp-blog-header-card mb-6">
                    @if(!empty($blog['is_featured']) || $category)
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if(!empty($blog['is_featured']))
                            <span class="jp-badge jp-badge-featured">
                                <i class="ki-duotone ki-star fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Featured
                            </span>
                        @endif
                        @if($category)
                            <span class="jp-badge">{{ $category }}</span>
                        @endif
                    </div>
                    @endif
                    <h1>{{ $blogTitle }}</h1>
                    <div class="jp-blog-meta mt-3">
                        @if($formatDate($blog['published_at'] ?? null))
                        <span>
                            <i class="ki-duotone ki-calendar fs-6"><span class="path1"></span><span class="path2"></span></i>
                            {{ $formatDate($blog['published_at'] ?? null) }}
                        </span>
                        @endif
                        @if(!empty($blog['view_count']))
                            <span>
                                <i class="ki-duotone ki-eye fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                {{ number_format($blog['view_count']) }} views
                            </span>
                        @endif
                        <span>
                            <i class="ki-duotone ki-time fs-6"><span class="path1"></span><span class="path2"></span></i>
                            {{ $readTime($blog['content'] ?? '') }}
                        </span>
                        @if($authorName)
                        <span>
                            <i class="ki-duotone ki-user fs-6"><span class="path1"></span><span class="path2"></span></i>
                            By {{ $authorName }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Cover image -->
                <img src="{{ $hasCover ? $blog['cover_image'] : $defaultCover }}"
                     alt="{{ $blogTitle }}"
                     class="jp-cover mb-6"
                     onerror="this.src='{{ $defaultCover }}'; this.onerror=null;">

                <!-- Article body -->
                <article class="jp-content-card">
                    <div class="jp-prose">
                        {!! $blog['content'] ?? '<p>No content available.</p>' !!}
                    </div>
                </article>

                <!-- Tags -->
                @if(!empty($tags))
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
                        <span class="fw-semibold text-gray-700 me-2">Tags:</span>
                        @foreach($tags as $tag)
                            <a href="{{ route('blog.index') }}?search={{ urlencode($tag) }}" class="btn btn-sm btn-outline-secondary rounded-pill">#{{ $tag }}</a>
                        @endforeach
                    </div>
                @endif

                <!-- Share -->
                <div class="jp-share-row mt-6 pt-4" style="border-top:1px solid var(--jp-line);">
                    <span class="fw-semibold text-gray-700">Share:</span>
                    <a class="jp-share-btn" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener noreferrer" aria-label="Share on Facebook" title="Share on Facebook">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a class="jp-share-btn" href="https://twitter.com/intent/tweet?text={{ urlencode($blogTitle) }}&url={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener noreferrer" aria-label="Share on X" title="Share on X">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-7.6 8.7L23 22h-7l-5.5-6.9L4.2 22H1l8.1-9.3L1 2h7.2l5 6.3L18.9 2Zm-1.2 18h1.7L7.4 4H5.6l12.1 16Z"/></svg>
                    </a>
                    <a class="jp-share-btn" href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener noreferrer" aria-label="Share on LinkedIn" title="Share on LinkedIn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                    <a class="jp-share-btn" href="https://wa.me/?text={{ urlencode($blogTitle . ' - ' . $canonicalUrl) }}" target="_blank" rel="noopener noreferrer" aria-label="Share on WhatsApp" title="Share on WhatsApp">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 6.32A8.86 8.86 0 0 0 12.02 3.5c-4.87 0-8.83 3.94-8.83 8.79 0 1.55.41 3.06 1.19 4.39L3.2 21l4.44-1.16a8.9 8.9 0 0 0 4.37 1.12h.01c4.87 0 8.83-3.94 8.83-8.79a8.7 8.7 0 0 0-2.25-5.85Zm-5.58 13.5h-.01c-1.36 0-2.7-.36-3.86-1.05l-.28-.16-2.87.75.77-2.79-.18-.29a7.3 7.3 0 0 1-1.13-3.9c0-4.03 3.3-7.32 7.36-7.32a7.3 7.3 0 0 1 5.2 2.15 7.24 7.24 0 0 1 2.16 5.17c0 4.03-3.3 7.32-7.36 7.32Zm4.03-5.48c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.58.71-.71.86-.26.16-.48.05a6.03 6.03 0 0 1-1.77-1.09 6.6 6.6 0 0 1-1.22-1.52c-.13-.22 0-.34.1-.45.1-.1.22-.26.33-.39.11-.13.15-.22.22-.37.07-.15.04-.28-.02-.39-.06-.11-.5-1.2-.68-1.65-.18-.43-.36-.37-.5-.38h-.43c-.15 0-.39.06-.6.28-.2.22-.79.77-.79 1.87 0 1.1.81 2.17.92 2.32.11.15 1.6 2.44 3.87 3.42.54.23.96.37 1.29.48.54.17 1.03.15 1.42.09.43-.06 1.3-.53 1.49-1.04.18-.51.18-.94.13-1.03-.05-.09-.2-.15-.42-.26Z"/></svg>
                    </a>
                    <button type="button" class="jp-share-btn" title="Copy link" aria-label="Copy link" data-copy-link>
                        <i class="ki-duotone ki-copy fs-4 icon-copy"><span class="path1"></span><span class="path2"></span></i>
                        <i class="ki-duotone ki-check fs-4 icon-done"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4" style="min-width:0;">
                <div class="jp-sticky-col">

                    <!-- Author card -->
                    @if($authorName)
                    <div class="jp-sidebar-card">
                        <h2 class="fs-7 fw-bold mb-3" style="color:var(--jp-ink);">About the Author</h2>
                        <div class="d-flex align-items-center gap-3">
                            @if(!empty($blog['author_avatar']))
                                <img src="{{ $blog['author_avatar'] }}" alt="{{ $authorName }}" class="rounded-circle flex-shrink-0" style="width:56px; height:56px; object-fit:cover; border:2px solid var(--jp-teal);">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px; height:56px; background:var(--jp-bg-soft); color:var(--jp-ink); font-weight:800; font-size:1.2rem; border:2px solid var(--jp-line);">
                                    {{ strtoupper(substr($authorName, 0, 2)) }}
                                </div>
                            @endif
                            <div style="min-width:0;">
                                <div class="fw-bold" style="color:var(--jp-ink); overflow-wrap:anywhere;">{{ $authorName }}</div>
                                @if(!empty($blog['author_title']))
                                    <div class="text-muted fs-8" style="overflow-wrap:anywhere;">{{ $blog['author_title'] }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Related articles -->
                    @if(!empty($relatedBlogs) && count($relatedBlogs) > 0)
                    <div class="jp-sidebar-card">
                        <h2 class="fs-7 fw-bold mb-3" style="color:var(--jp-ink);">You might also like</h2>
                        @foreach($relatedBlogs as $related)
                            @if(($related['id'] ?? null) != ($blog['id'] ?? null))
                            <a href="{{ route('blog.show', $related['slug']) }}" class="jp-related-item">
                                @if(!empty($related['cover_image']))
                                    <img src="{{ $related['cover_image'] }}" alt="{{ $related['title'] ?? '' }}" class="related-img" loading="lazy">
                                @else
                                    <div class="related-img d-flex align-items-center justify-content-center">
                                        <i class="ki-duotone ki-picture fs-2 text-muted"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                @endif
                                <div class="body">
                                    <div class="title">{{ $related['title'] ?? 'Blog Post' }}</div>
                                    <div class="sub">{{ $formatDate($related['published_at'] ?? null) }}</div>
                                </div>
                            </a>
                            @endif
                        @endforeach
                    </div>
                    @endif

                    <!-- Back to blog -->
                    <div class="jp-back-wrap">
                        <a href="{{ route('blog.index') }}" class="btn jp-btn-outline w-100 py-3">
                            <i class="ki-duotone ki-arrow-left fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                            Back to Blog
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====================== CTA BANNER ====================== -->
    <div class="jp-cta-wrap">
        <div class="container">
            <div class="jp-cta-banner">
                <div class="cta-text">
                    <div class="fs-1 fs-lg-2qx fw-bold text-white mb-2">Enjoyed this article?</div>
                    <div class="fs-6 fs-lg-5 text-white fw-semibold opacity-75">Stay updated with more insights and stories from our community.</div>
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

{{-- Structured data for search engines --}}
<script type="application/ld+json">{!! json_encode($blogPosting, $jsonFlags) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbLd, $jsonFlags) !!}</script>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-copy-link]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var done = function () {
                btn.classList.add('jp-copied');
                setTimeout(function () { btn.classList.remove('jp-copied'); }, 2000);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(window.location.href).then(done);
            } else {
                var t = document.createElement('textarea');
                t.value = window.location.href;
                document.body.appendChild(t);
                t.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                document.body.removeChild(t);
            }
        });
    });
});
</script>
@endpush