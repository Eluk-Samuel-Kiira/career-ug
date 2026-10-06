{{--
	NO @push('styles') HERE ON PURPOSE.
	This file is loaded via @include() inside <body>, but @stack('styles') sits
	in <head> and renders BEFORE <body>. Any @push('styles') from an @include'd
	partial fires too late to ever reach the stack - that's the actual reason
	the footer stayed white through every previous attempt, not caching.
	(This is different from a page's own @push at the top of a file that
	@extends the layout - that one runs during the child-template pass, which
	completes before the parent layout's @stack renders, so it works fine.)
	Fix: every color/background here is an inline style="" attribute instead,
	which always renders regardless of push/stack timing.

	Layout is handled with Bootstrap grid/utility classes only (no custom CSS),
	for the same reason.
--}}

@php
	// Defaults used when no pages come from the API
	$defaultPages = [
		['slug' => 'about', 'title' => 'About'],
		['slug' => 'contact', 'title' => 'Contact'],
		['slug' => 'privacy-policy', 'title' => 'Privacy Policy'],
		['slug' => 'terms-conditions', 'title' => 'Terms & Conditions'],
	];

	$pagesToDisplay = isset($footerPages) && !empty($footerPages) ? $footerPages : $defaultPages;

	$linkStyle    = 'color:#AFC0D2; text-decoration:none; font-size:.9rem; line-height:1.4; display:block; overflow-wrap:anywhere;';
	$headingStyle = 'color:#fff; opacity:.55; font-weight:800; font-size:.82rem; letter-spacing:.04em; text-transform:uppercase; margin-bottom:18px;';
	$cardStyle    = 'border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.03); padding:22px 24px; overflow-wrap:anywhere;';
@endphp

<!--begin::Footer Section-->
<div style="background: linear-gradient(180deg, #13273D 0%, #0B1C2E 100%); padding-top: 90px; overflow-x:clip;">
	<div class="container">
		<div class="row gy-10 gx-lg-10 py-10 py-lg-14">

			<!-- Left: contact cards -->
			<div class="col-lg-6">
				<div class="d-flex flex-column gap-5">
					<div class="rounded-3" style="{{ $cardStyle }}">
						<h3 style="color:#fff; font-weight:800; font-size:1.15rem; margin-bottom:6px;">Need a Custom Plan?</h3>
						<p style="color:#AFC0D2; font-size:.9rem; margin-bottom:0; line-height:1.6;">
							Email us at
							<a href="mailto:stardenacareers@gmail.com" style="color:#7CF0C2; text-decoration:none; font-weight:700;">stardenacareers@gmail.com</a>
							or call <a href="tel:+256754428612" style="color:#fff; text-decoration:none;">+256 754428612</a>
						</p>
					</div>

					<div class="rounded-3" style="{{ $cardStyle }}">
						<h3 style="color:#fff; font-weight:800; font-size:1.15rem; margin-bottom:6px;">WhatsApp Support</h3>
						<p style="color:#AFC0D2; font-size:.9rem; margin-bottom:0; line-height:1.6;">
							Chat with our team directly.
							<a href="https://wa.me/256754428612" target="_blank" rel="noopener noreferrer" style="color:#7CF0C2; text-decoration:none; font-weight:700;">Open WhatsApp</a>
						</p>
					</div>
				</div>
			</div>

			<!-- Right: Product + Company, two equal columns that fill the half -->
			<div class="col-lg-6">
				{{-- Mobile/tablet: columns centred with equal space at both outer ends and between them.
				     Desktop: both columns sit together against the right edge.
				     (To centre on desktop too, change justify-content-lg-end to justify-content-lg-evenly.) --}}
				<div class="d-flex justify-content-evenly justify-content-lg-end gap-4 gap-lg-20">

					<div style="min-width:0;">
						<h4 style="{{ $headingStyle }}">Product</h4>
						<div class="d-flex flex-column gap-3">
							<a href="{{ route('home') }}" style="{{ $linkStyle }}">Features</a>
							<a href="{{ route('home') }}" style="{{ $linkStyle }}">Pricing</a>
							<a href="{{ route('home') }}" style="{{ $linkStyle }}">Services</a>
							<a href="{{ route('blog.index') }}" style="{{ $linkStyle }}">Blog</a>
						</div>
					</div>

					<div style="min-width:0;">
						<h4 style="{{ $headingStyle }}">Company</h4>
						<div class="d-flex flex-column gap-3">
							@foreach($pagesToDisplay as $page)
								@php
									$pageSlug  = is_array($page) ? ($page['slug'] ?? '') : ($page->slug ?? '');
									$pageTitle = is_array($page) ? ($page['title'] ?? '') : ($page->title ?? '');
								@endphp
								@if(!empty($pageSlug) && !empty($pageTitle))
									<a href="{{ route('pages.show', $pageSlug) }}" style="{{ $linkStyle }}">{{ $pageTitle }}</a>
								@endif
							@endforeach
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>

	<div style="border-top:1px solid rgba(255,255,255,0.08);"></div>

	<div class="container">
		<div class="d-flex flex-column flex-md-row align-items-center justify-content-md-between gap-5 py-6 py-lg-8 text-center text-md-start">

			<div class="d-flex flex-column flex-sm-row flex-wrap align-items-center justify-content-center justify-content-md-start gap-3 gap-sm-5 order-2 order-md-1">
				<a href="{{ route('home') }}" class="flex-shrink-0">
					<img alt="Logo" src="{{ country_logo() }}" class="h-15px h-md-20px" />
				</a>
				<span style="color:#8298AC; font-size:.85rem; line-height:1.5;">
					&copy; {{ date('Y') }}
					<a href="https://stardena.org/" target="_blank" rel="noopener noreferrer" style="color:#AFC0D2; text-decoration:none; font-weight:600;">Stardena Inc.</a>
					All rights reserved.
				</span>
			</div>

			<ul class="d-flex flex-wrap justify-content-center list-unstyled gap-6 order-1 order-md-2 mb-0">
				<li><a href="{{ route('home') }}" style="color:#AFC0D2; text-decoration:none; font-size:.88rem;">Home</a></li>
				<li><a href="{{ route('contact') }}" target="_blank" style="color:#AFC0D2; text-decoration:none; font-size:.88rem;">Support</a></li>
				<li><a href="{{ route('login') }}" style="color:#AFC0D2; text-decoration:none; font-size:.88rem;">Get Started</a></li>
			</ul>
		</div>
	</div>
</div>
<!--end::Footer Section-->