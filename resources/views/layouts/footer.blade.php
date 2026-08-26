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
--}}

<!--begin::Footer Section-->
<div style="background: linear-gradient(180deg, #13273D 0%, #0B1C2E 100%); padding-top: 90px;">
	<div class="container">
		<div class="row py-10 py-lg-14">
			<div class="col-lg-6 pe-lg-16 mb-8 mb-lg-0">
				<div class="rounded-3 mb-5" style="border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.03); padding:22px 24px;">
					<h3 style="color:#fff; font-weight:800; font-size:1.15rem; margin-bottom:6px;">Need a Custom Plan?</h3>
					<p style="color:#AFC0D2; font-size:.9rem; margin-bottom:0;">
						Email us at
						<a href="mailto:stardenacareers@gmail.com" style="color:#7CF0C2; text-decoration:none; font-weight:700;">stardenacareers@gmail.com</a>
						or call <span style="color:#fff;">+256 754428612</span>
					</p>
				</div>

				<div class="rounded-3" style="border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.03); padding:22px 24px;">
					<h3 style="color:#fff; font-weight:800; font-size:1.15rem; margin-bottom:6px;">WhatsApp Support</h3>
					<p style="color:#AFC0D2; font-size:.9rem; margin-bottom:0;">
						Chat with our team directly.
						<a href="https://wa.me/256754428612" target="_blank" rel="noopener noreferrer" style="color:#7CF0C2; text-decoration:none; font-weight:700;">Open WhatsApp</a>
					</p>
				</div>
			</div>

			<div class="col-lg-6 ps-lg-16">
				<div class="row g-6">
					<div class="col-6">
						<h4 style="color:#fff; opacity:.55; font-weight:800; font-size:.82rem; letter-spacing:.04em; text-transform:uppercase; margin-bottom:16px;">Product</h4>
						<a href="{{ route('home') }}" style="color:#AFC0D2; text-decoration:none; font-size:.9rem; display:block; margin-bottom:12px;">Features</a>
						<a href="{{ route('home') }}" style="color:#AFC0D2; text-decoration:none; font-size:.9rem; display:block; margin-bottom:12px;">Pricing</a>
						<a href="{{ route('home') }}" style="color:#AFC0D2; text-decoration:none; font-size:.9rem; display:block; margin-bottom:12px;">Services</a>
						<a href="{{ route('blog.index') }}" style="color:#AFC0D2; text-decoration:none; font-size:.9rem; display:block;">Blog</a>
					</div>

					<div class="col-6">
						<h4 style="color:#fff; opacity:.55; font-weight:800; font-size:.82rem; letter-spacing:.04em; text-transform:uppercase; margin-bottom:16px;">Company</h4>
						
						@php
							// Define default pages if no pages from API
							$defaultPages = [
								['slug' => 'about', 'title' => 'About'],
								['slug' => 'contact', 'title' => 'Contact'],
								['slug' => 'privacy-policy', 'title' => 'Privacy Policy'],
								['slug' => 'terms-conditions', 'title' => 'Terms & Conditions'],
							];
							
							// Use footerPages if available, otherwise use defaults
							$pagesToDisplay = isset($footerPages) && !empty($footerPages) ? $footerPages : $defaultPages;
						@endphp
						
						@forelse($pagesToDisplay as $page)
							@php
								$pageSlug = is_array($page) ? ($page['slug'] ?? '') : $page->slug ?? '';
								$pageTitle = is_array($page) ? ($page['title'] ?? '') : $page->title ?? '';
							@endphp
							@if(!empty($pageSlug) && !empty($pageTitle))
								<a href="{{ route('pages.show', $pageSlug) }}" style="color:#AFC0D2; text-decoration:none; font-size:.9rem; display:block; margin-bottom:12px;">
									{{ $pageTitle }}
								</a>
							@endif
						@empty
							<!-- Fallback links if no pages are available -->
							<a href="{{ route('pages.show', 'about') }}" style="color:#AFC0D2; text-decoration:none; font-size:.9rem; display:block; margin-bottom:12px;">About</a>
							<a href="{{ route('pages.show', 'contact') }}" style="color:#AFC0D2; text-decoration:none; font-size:.9rem; display:block; margin-bottom:12px;">Contact</a>
							<a href="{{ route('pages.show', 'privacy-policy') }}" style="color:#AFC0D2; text-decoration:none; font-size:.9rem; display:block; margin-bottom:12px;">Privacy Policy</a>
							<a href="{{ route('pages.show', 'terms-conditions') }}" style="color:#AFC0D2; text-decoration:none; font-size:.9rem; display:block;">Terms & Conditions</a>
						@endforelse
					</div>
				</div>
			</div>
		</div>
	</div>

	<div style="border-top:1px solid rgba(255,255,255,0.08);"></div>

	<div class="container">
		<div class="d-flex flex-column flex-md-row flex-stack py-6 py-lg-8">
			<div class="d-flex align-items-center order-2 order-md-1">
				<a href="{{ route('home') }}">
					<img alt="Logo" src="{{ country_logo() }}" class="h-15px h-md-20px" />
				</a>
				<span class="mx-5" style="color:#8298AC; font-size:.85rem;">&copy; {{ date('Y') }} <a href="https://stardena.org/" target="_blank">Stardena Inc.</a> All rights reserved.</span>
			</div>

			<ul class="d-flex list-unstyled gap-6 order-1 mb-5 mb-md-0">
				<li><a href="{{ route('home') }}" style="color:#AFC0D2; text-decoration:none; font-size:.88rem;">Home</a></li>
				<li><a href="{{ route('contact') }}" target="_blank" style="color:#AFC0D2; text-decoration:none; font-size:.88rem;">Support</a></li>
				<li><a href="{{ route('login') }}" style="color:#AFC0D2; text-decoration:none; font-size:.88rem;">Get Started</a></li>
			</ul>
		</div>
	</div>
</div>
<!--end::Footer Section-->