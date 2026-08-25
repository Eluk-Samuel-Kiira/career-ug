<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<!--begin::Head-->
	<head>
		<!-- Country Meta -->
		<meta name="country-code" content="{{ country_code() }}" />
		<meta name="country-name" content="{{ country_name() }}" />
		
		<title>{{ config('app.name', 'JobMatch') }} - @yield('title', country_name() . ' - Find Your Best Talent')</title>
		<meta charset="utf-8" />

		<meta name="description" content="@yield('meta_description', country_name() . '\'s leading job posting platform with AI-powered CV screening, professional rewriting services, and WhatsApp integration for seamless hiring in ' . country_name() . '.')" />
		<meta name="keywords" content="@yield('keywords', 'jobs ' . strtolower(country_name()) . ', recruitment, AI hiring, CV review, job posting, talent matching, ' . strtolower(country_name()) . ' jobs, career opportunities')" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		
		<!-- Open Graph -->
		<meta property="og:locale" content="en_{{ country_code() }}" />
		<meta property="og:type" content="website" />
		<meta property="og:title" content="{{ config('app.name', 'JobMatch') }} - {{ country_name() }}" />
		<meta property="og:site_name" content="{{ config('app.name', 'JobMatch') }} - {{ country_name() }}" />
		<meta property="og:description" content="@yield('og_description', 'Find the best jobs and talent in ' . country_name() . '. Post jobs, hire candidates, and grow your career with AI-powered matching.')" />
		<meta property="og:url" content="{{ url('/') }}" />
		<meta property="og:image" content="{{ country_logo() }}" />
		
		<!-- Twitter Cards -->
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:title" content="{{ config('app.name', 'JobMatch') }} - {{ country_name() }}" />
		<meta name="twitter:description" content="@yield('twitter_description', 'Find jobs and talent in ' . country_name() . '. Post jobs, hire candidates, and grow your career with AI-powered matching.')" />
		<meta name="twitter:image" content="{{ country_logo() }}" />
		
		<!-- Canonical -->
		<link rel="canonical" href="@yield('canonical_url', url('/'))" />
		<meta name="csrf-token" content="{{ csrf_token() }}">
		
		<!-- Favicon -->
		<link rel="shortcut icon" href="{{ country_favicon() }}" />
		<link rel="apple-touch-icon" href="{{ country_favicon() }}" />

		<!-- Hreflang Tags -->
		@if(count(all_countries()) > 0)
			@foreach(all_countries() as $country)
				@if(isset($country['code']) && isset($country['domain']))
					<link rel="alternate" hreflang="en-{{ strtoupper($country['code']) }}" 
						  href="{{ str_replace(config('app.country_domain'), $country['domain'], url('/')) }}" />
				@endif
			@endforeach
			<link rel="alternate" hreflang="x-default" href="{{ url('/') }}" />
		@endif

		<!--begin::Fonts(mandatory for all pages)-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<!--end::Fonts-->

		<!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
		<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->

		@stack('styles')

        <!-- DataTables CSS -->
        <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/plugins/custom/select2/select2.bundle.css') }}" rel="stylesheet" />

		<script>
			// Frame-busting to prevent site from being loaded within a frame without permission
			if (window.top != window.self) { 
				window.top.location.replace(window.self.location.href); 
			}
		</script>

		<style>
			.jp-inline-filter-bar{ padding:0; }

			.jp-search-input{ display:flex; align-items:center; gap:8px; height:36px; padding:0 10px; background:var(--jp-bg-soft); border:1px solid var(--jp-line); border-radius:8px; transition:.15s; }
			.jp-search-input i{ color:var(--jp-muted); flex-shrink:0; }
			.jp-search-input input{ border:none; background:transparent; outline:none; width:100%; height:100%; font-size:.82rem; color:var(--jp-ink); }
			.jp-search-input:focus-within{ border-color:var(--jp-teal); background:#fff; }

			.jp-select{ width:100%; height:36px; padding:0 10px; border:1px solid var(--jp-line); border-radius:8px; background:var(--jp-bg-soft); color:var(--jp-ink); font-size:.8rem; font-weight:600; transition:.15s; }
			.jp-select:focus{ outline:none; border-color:var(--jp-teal); background:#fff; }

			.jp-filter-submit{ width:36px; height:36px; flex-shrink:0; border:none; border-radius:8px; background:var(--jp-teal); color:#fff; display:flex; align-items:center; justify-content:center; transition:.15s; }
			.jp-filter-submit:hover{ background:#028f76; }

			.select2-container .select2-selection--single{ height:36px !important; border:1px solid var(--jp-line) !important; border-radius:8px !important; background:var(--jp-bg-soft) !important; display:flex; align-items:center; }
			.select2-container .select2-selection--single .select2-selection__rendered{ color:var(--jp-ink); font-size:.8rem; font-weight:600; padding-left:10px; line-height:34px !important; }
			.select2-container .select2-selection--single .select2-selection__arrow{ height:34px; }
			.select2-container--default.select2-container--open .select2-selection--single{ border-color:var(--jp-teal) !important; }
			.select2-dropdown{ border-color:var(--jp-line) !important; border-radius:8px !important; overflow:hidden; }
			.select2-results__option--highlighted[aria-selected]{ background:var(--jp-teal) !important; color:#fff !important; }
			.select2-container--default .select2-selection--single .select2-selection__arrow b{ border-color:var(--jp-muted) transparent transparent transparent !important; }
		</style>
		
		<style>
			/* ============================================================
			NAVIGATION DROPDOWNS - Categories & Locations (Shared Styles)
			============================================================ */

			/* ----- Shared Base Styles ----- */
			.jp-nav-categories summary,
			.jp-nav-locations summary {
				cursor: pointer;
				list-style: none;
			}
			.jp-nav-categories summary::-webkit-details-marker,
			.jp-nav-locations summary::-webkit-details-marker {
				display: none;
			}
			.jp-nav-categories summary svg,
			.jp-nav-locations summary svg {
				transition: .15s;
			}
			.jp-nav-categories[open] summary svg,
			.jp-nav-locations[open] summary svg {
				transform: rotate(180deg);
			}

			.jp-nav-categories-panel,
			.jp-nav-locations-panel {
				display: flex;
				flex-direction: column;
				border-radius: 4px;
				overflow: hidden;
			}

			/* ----- Desktop: hover-based dropdown (shared) ----- */
			@media (min-width: 992px) {
				.jp-nav-categories-mobile,
				.jp-nav-locations-mobile {
					display: none !important;
				}

				.jp-nav-categories-desktop,
				.jp-nav-locations-desktop {
					position: relative;
					display: block;
				}

				.jp-nav-categories-desktop .jp-nav-categories-panel,
				.jp-nav-locations-desktop .jp-nav-locations-panel {
					position: absolute;
					top: 100%;
					left: 0;
					min-width: 240px;
					max-height: 360px;
					overflow-y: auto;
					background: #fff;
					border: 1px solid rgba(15, 27, 45, 0.08);
					border-radius: 4px !important;
					box-shadow: 0 16px 34px rgba(11, 28, 46, 0.12);
					padding: 8px;
					z-index: 110;
					margin-top: 6px;
					opacity: 0;
					visibility: hidden;
					transform: translateY(-6px);
					transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
				}

				/* Show dropdown on hover */
				.jp-nav-categories-desktop:hover .jp-nav-categories-panel,
				.jp-nav-locations-desktop:hover .jp-nav-locations-panel {
					opacity: 1;
					visibility: visible;
					transform: translateY(0);
				}

				/* Keep dropdown open when hovering over it */
				.jp-nav-categories-panel:hover,
				.jp-nav-locations-panel:hover {
					opacity: 1 !important;
					visibility: visible !important;
					transform: translateY(0) !important;
				}

				/* Hover effect on the trigger text */
				.jp-nav-categories-desktop > span:hover,
				.jp-nav-locations-desktop > span:hover {
					color: var(--jp-teal, #03A588);
				}

				.jp-nav-categories-desktop > span,
				.jp-nav-locations-desktop > span {
					display: inline-flex;
					align-items: center;
					gap: 4px;
					cursor: pointer;
				}
			}

			/* ----- Mobile: details-based native dropdown (shared) ----- */
			@media (max-width: 991.98px) {
				.jp-nav-categories-desktop,
				.jp-nav-locations-desktop {
					display: none !important;
				}

				.jp-nav-categories-mobile,
				.jp-nav-locations-mobile {
					display: block !important;
				}

				.jp-nav-categories-panel,
				.jp-nav-locations-panel {
					padding: 4px 0 4px 16px;
					border-radius: 4px;
				}
			}

			/* ----- Link styling (shared) ----- */
			.jp-nav-categories-panel a,
			.jp-nav-locations-panel a {
				padding: 9px 14px;
				border-radius: 4px;
				color: #33475B;
				font-size: .88rem;
				font-weight: 600;
				text-decoration: none;
				cursor: pointer;
				transition: background 0.15s ease, color 0.15s ease;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}

			.jp-nav-categories-panel a:hover,
			.jp-nav-locations-panel a:hover {
				background: #F4F8F7;
				color: #03A588;
			}

			/* ----- Icon styling ----- */
			.jp-nav-categories-panel a .ki-duotone,
			.jp-nav-locations-panel a .ki-duotone {
				font-size: 1.1rem;
				flex-shrink: 0;
			}
			.jp-nav-categories-panel a .text-warning {
				color: #f1c40f !important;
			}
			.jp-nav-locations-panel a .text-teal {
				color: var(--jp-teal, #03A588) !important;
			}

			/* ----- Badge styling (shared) ----- */
			.jp-nav-categories-panel a .badge,
			.jp-nav-locations-panel a .badge {
				font-size: 10px;
				padding: 2px 8px;
				font-weight: 600;
				flex-shrink: 0;
				margin-left: 8px;
				min-width: 20px;
				text-align: center;
			}

			/* ----- Scrollbar styling (shared) ----- */
			.jp-nav-categories-panel::-webkit-scrollbar,
			.jp-nav-locations-panel::-webkit-scrollbar {
				width: 4px;
			}
			.jp-nav-categories-panel::-webkit-scrollbar-track,
			.jp-nav-locations-panel::-webkit-scrollbar-track {
				background: transparent;
			}
			.jp-nav-categories-panel::-webkit-scrollbar-thumb,
			.jp-nav-locations-panel::-webkit-scrollbar-thumb {
				background: rgba(15, 27, 45, 0.15);
				border-radius: 4px;
			}
			.jp-nav-categories-panel::-webkit-scrollbar-thumb:hover,
			.jp-nav-locations-panel::-webkit-scrollbar-thumb:hover {
				background: rgba(15, 27, 45, 0.25);
			}

			/* ----- Empty state (shared) ----- */
			.jp-nav-categories-empty,
			.jp-nav-locations-empty {
				padding: 9px 14px;
				color: #94A3B8;
				font-size: .85rem;
			}

			/* ----- Active state for the nav link ----- */
			.jp-nav-categories .menu-link.active,
			.jp-nav-locations .menu-link.active {
				color: var(--jp-teal, #03A588) !important;
			}
		</style>

	</head>
	<!--end::Head-->

	<!--begin::Body-->
	<body id="kt_body" data-bs-spy="scroll" data-bs-target="#kt_landing_menu" class="bg-body position-relative app-blank">
		<!--begin::Theme mode setup on page load-->
		<script>
			var defaultThemeMode = "light";
			var themeMode;
			if (document.documentElement) {
				if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
					themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
				} else {
					if (localStorage.getItem("data-bs-theme") !== null) {
						themeMode = localStorage.getItem("data-bs-theme");
					} else {
						themeMode = defaultThemeMode;
					}
				}
				if (themeMode === "system") {
					themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
				}
				document.documentElement.setAttribute("data-bs-theme", themeMode);
			}
		</script>
		<!--end::Theme mode setup on page load-->

		<!--begin::Root-->
		<div class="d-flex flex-column flex-root" id="kt_app_root">
			<!--begin::Header-->
			@include('layouts.header')
			<!--end::Header-->


			<!--begin::Main Content-->
			@yield('content')
			<!--end::Main Content-->

			<!--begin::Footer-->
			@include('layouts.footer')
			<!--end::Footer-->

			<!--begin::Scrolltop-->
			<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
				<i class="ki-duotone ki-arrow-up">
					<span class="path1"></span>
					<span class="path2"></span>
				</i>
			</div>
			<!--end::Scrolltop-->
		</div>
		<!--end::Root-->

		<!--begin::Global Javascript Bundle(mandatory for all pages)-->
		<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
		<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
		<!--end::Global Javascript Bundle-->

		<!--begin::Vendors Javascript(used for this page only)-->
		<script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
		<script src="{{ asset('assets/plugins/custom/typedjs/typedjs.bundle.js') }}"></script>
		<!--end::Vendors Javascript-->

		<!--begin::Custom Javascript(used for this page only)-->
		<script src="{{ asset('assets/js/custom/landing.js') }}"></script>
		<script src="{{ asset('assets/js/custom/pages/pricing/general.js') }}"></script>
		<!--end::Custom Javascript-->

		<!-- Select2 JS -->
		<script src="{{ asset('assets/plugins/custom/select2/select2.bundle.js') }}"></script>

		<!-- SweetAlert2 (for better alerts) -->
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

		@stack('scripts')
	</body>
	<!--end::Body-->
</html>