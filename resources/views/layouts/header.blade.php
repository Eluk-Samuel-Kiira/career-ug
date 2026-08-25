<!--begin::Header Section-->
<div class="mb-0" id="home">
	<!--begin::Wrapper-->
	<div class="bgi-no-repeat bgi-size-contain bgi-position-x-center bgi-position-y-bottom landing-dark-bg" style="background-image: url({{ asset('assets/media/svg/illustrations/landing.svg') }})">
		<!--begin::Header-->
		<div class="landing-header jp-landing-header" data-kt-sticky="true" data-kt-sticky-name="landing-header" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
			<!--begin::Container-->
			<div class="container">
				<!--begin::Wrapper-->
				<div class="d-flex align-items-center justify-content-between py-2 py-lg-0">

					<!--begin::Left side - Logo (visible on all screens)-->
					<div class="d-flex align-items-center">
						<a href="{{ route('home') }}" class="d-flex align-items-center">
							<img alt="Logo" src="{{ country_logo() }}" class="logo-default h-35px h-lg-50px" />
							<img alt="Logo" src="{{ country_logo() }}" class="logo-sticky h-40px h-lg-35px" />
						</a>
					</div>
					<!--end::Left side-->

					<!--begin::Menu wrapper (desktop only - center)-->
					<div class="d-none d-lg-block" id="kt_header_nav_wrapper">
						<div class="d-lg-block" data-kt-drawer="true" data-kt-drawer-name="landing-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="280px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_landing_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav_wrapper'}">
							<!--begin::Menu-->
							<div class="menu menu-column flex-nowrap menu-rounded menu-lg-row menu-title-gray-600 menu-state-title-primary nav nav-flush fs-5 fw-semibold" id="kt_landing_menu">

								<!--begin::Menu item - Mobile Logo (first line inside the drawer only)-->
								<div class="menu-item d-lg-none jp-drawer-logo mb-5 pb-5 border-bottom">
									<a href="{{ route('home') }}" class="d-flex align-items-center px-4 py-2">
										<img alt="Logo" src="{{ country_logo() }}" class="h-35px" />
									</a>
								</div>
								<!--end::Menu item - Mobile Logo-->

								<!--begin::Menu item-->
								<div class="menu-item">
									<a class="menu-link nav-link py-3 px-4 px-xxl-6 {{ Route::is('home') ? 'active' : '' }}" href="{{ route('home') }}" data-kt-scroll-toggle="true" data-kt-drawer-dismiss="true">Home</a>
								</div>
								<!--end::Menu item-->

								<!--begin::Menu item-->
								<div class="menu-item">
									<a class="menu-link nav-link py-3 px-4 px-xxl-6 {{ Route::is('jobs.index','jobs.show') ? 'active' : '' }}" href="{{ route('jobs.index') }}" data-kt-scroll-toggle="true" data-kt-drawer-dismiss="true">Jobs</a>
								</div>
								<!--end::Menu item-->

								<!--begin::Menu item-->
								<div class="menu-item">
									<a class="menu-link nav-link py-3 px-4 px-xxl-6 {{ Route::is('companies.index','companies.show') ? 'active' : '' }}" href="{{ route('companies.index') }}" data-kt-scroll-toggle="true" data-kt-drawer-dismiss="true">Companies</a>
								</div>
								<!--end::Menu item-->

								<!--begin::Menu item - Categories-->
								<div class="menu-item jp-nav-categories">
									<!-- Desktop: hover-based dropdown -->
									<div class="jp-nav-categories-desktop">
										<span class="menu-link nav-link py-3 px-4 px-xxl-6 d-flex align-items-center gap-1 {{ Route::is('categories.show') ? 'active' : '' }}" style="cursor: pointer;">
											<!-- <i class="ki-duotone ki-folder fs-4 text-warning">
												<span class="path1"></span>
												<span class="path2"></span>
											</i> -->
											Categories
											<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
												<path d="M6 9l6 6 6-6"/>
											</svg>
										</span>
										<div class="jp-nav-categories-panel">
											@forelse($navCategories ?? [] as $category)
												@php
													$catId = is_array($category) ? $category['slug'] : $category->slug;
													$catName = is_array($category) ? $category['name'] : $category->name;
													$jobCount = is_array($category) ? ($category['jobs_count'] ?? 0) : 0;
													$icon = is_array($category) ? ($category['icon'] ?? 'bi-briefcase') : ($category->icon ?? 'bi-briefcase');
												@endphp
												<a href="{{ route('categories.show', $catId) }}" data-kt-drawer-dismiss="true">
													<span class="d-flex align-items-center gap-2">
														<i class="ki-duotone ki-folder fs-4 text-warning">
															<span class="path1"></span>
															<span class="path2"></span>
														</i>
														{{ $catName }}
													</span>
													@if($jobCount > 0)
														<span class="badge bg-light-primary text-primary rounded-pill">{{ $jobCount }}</span>
													@endif
												</a>
											@empty
												<span class="jp-nav-categories-empty">No categories available</span>
											@endforelse
										</div>
									</div>

									<!-- Mobile: details-based dropdown -->
									<details class="jp-nav-categories-mobile">
										<summary class="menu-link nav-link py-3 px-4 px-xxl-6 d-flex align-items-center gap-1 {{ Route::is('categories.show') ? 'active' : '' }}" style="cursor: pointer;">
											<!-- <i class="ki-duotone ki-folder fs-3 me-1">
												<span class="path1"></span>
												<span class="path2"></span>
											</i> -->
											Categories
											<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
												<path d="M6 9l6 6 6-6"/>
											</svg>
										</summary>
										<div class="jp-nav-categories-panel">
											@forelse($navCategories ?? [] as $category)
												@php
													$catId = is_array($category) ? $category['slug'] : $category->slug;
													$catName = is_array($category) ? $category['name'] : $category->name;
													$jobCount = is_array($category) ? ($category['jobs_count'] ?? 0) : 0;
													$icon = is_array($category) ? ($category['icon'] ?? 'bi-briefcase') : ($category->icon ?? 'bi-briefcase');
												@endphp
												<a href="{{ route('categories.show', $catId) }}" data-kt-drawer-dismiss="true">
													<span class="d-flex align-items-center gap-2">
														<i class="ki-duotone ki-folder fs-4 text-warning">
															<span class="path1"></span>
															<span class="path2"></span>
														</i>
														{{ $catName }}
													</span>
													@if($jobCount > 0)
														<span class="badge bg-light-primary text-primary rounded-pill">{{ $jobCount }}</span>
													@endif
												</a>
											@empty
												<span class="jp-nav-categories-empty">No categories available</span>
											@endforelse
										</div>
									</details>
								</div>
								<!--end::Menu item - Categories-->

								<!--begin::Menu item - Locations-->
								<div class="menu-item jp-nav-locations">
									<!-- Desktop: hover-based dropdown -->
									<div class="jp-nav-locations-desktop">
										<span class="menu-link nav-link py-3 px-4 px-xxl-6 d-flex align-items-center gap-1 {{ Route::is('locations.show') ? 'active' : '' }}" style="cursor: pointer;">
											<!-- <i class="ki-duotone ki-geolocation fs-3 me-1">
												<span class="path1"></span>
												<span class="path2"></span>
											</i> -->
											Locations
											<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
												<path d="M6 9l6 6 6-6"/>
											</svg>
										</span>
										<div class="jp-nav-locations-panel">
											@forelse($navLocations ?? [] as $location)
												@php
													$locSlug = is_array($location) ? ($location['slug'] ?? strtolower(str_replace(' ', '-', $location['district'] ?? ''))) : $location->slug;
													$locName = is_array($location) ? ($location['display_name'] ?? $location['district'] ?? '') : $location->district;
													$jobCount = is_array($location) ? ($location['jobs_count'] ?? 0) : 0;
												@endphp
												<a href="{{ route('locations.show', $locSlug) }}" data-kt-drawer-dismiss="true">
													<span class="d-flex align-items-center gap-2">
														<i class="ki-duotone ki-geolocation fs-4 text-teal">
															<span class="path1"></span>
															<span class="path2"></span>
														</i>
														{{ $locName }}
													</span>
													@if($jobCount > 0)
														<span class="badge bg-light-primary text-primary rounded-pill">{{ $jobCount }}</span>
													@endif
												</a>
											@empty
												<span class="jp-nav-locations-empty">No locations available</span>
											@endforelse
										</div>
									</div>

									<!-- Mobile: details-based dropdown -->
									<details class="jp-nav-locations-mobile">
										<summary class="menu-link nav-link py-3 px-4 px-xxl-6 d-flex align-items-center gap-1 {{ Route::is('locations.show') ? 'active' : '' }}" style="cursor: pointer;">
											<!-- <i class="ki-duotone ki-geolocation fs-3 me-1">
												<span class="path1"></span>
												<span class="path2"></span>
											</i> -->
											Locations
											<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
												<path d="M6 9l6 6 6-6"/>
											</svg>
										</summary>
										<div class="jp-nav-locations-panel">
											@forelse($navLocations ?? [] as $location)
												@php
													$locSlug = is_array($location) ? ($location['slug'] ?? strtolower(str_replace(' ', '-', $location['district'] ?? ''))) : $location->slug;
													$locName = is_array($location) ? ($location['display_name'] ?? $location['district'] ?? '') : $location->city;
													$jobCount = is_array($location) ? ($location['jobs_count'] ?? 0) : 0;
												@endphp
												<a href="{{ route('locations.show', $locSlug) }}" data-kt-drawer-dismiss="true">
													<span class="d-flex align-items-center gap-2">
														<i class="ki-duotone ki-geolocation fs-4 text-teal">
															<span class="path1"></span>
															<span class="path2"></span>
														</i>
														{{ $locName }}
													</span>
													@if($jobCount > 0)
														<span class="badge bg-light-primary text-primary rounded-pill">{{ $jobCount }}</span>
													@endif
												</a>
											@empty
												<span class="jp-nav-locations-empty">No locations available</span>
											@endforelse
										</div>
									</details>
								</div>
								<!--end::Menu item - Locations-->
								
								<div class="menu-item">
									<a class="menu-link nav-link py-3 px-4 px-xxl-6 {{ Route::is('social-media.*') ? 'active' : '' }}" 
									href="{{ route('social-media.featured') }}" 
									data-kt-drawer-dismiss="true">
										Join Us
									</a>
								</div>
								{{-- 
								<!--begin::Menu item-->
								<div class="menu-item">
									<a class="menu-link nav-link py-3 px-4 px-xxl-6" href="#team" data-kt-scroll-toggle="true" data-kt-drawer-dismiss="true">Team</a>
								</div>
								<!--end::Menu item-->

								<!--begin::Menu item-->
								<div class="menu-item">
									<a class="menu-link nav-link py-3 px-4 px-xxl-6" href="#pricing" data-kt-scroll-toggle="true" data-kt-drawer-dismiss="true">Pricing</a>
								</div>
								<!--end::Menu item-->
								--}}

								<!--begin::Menu item - Mobile Sign In (visible only on mobile)-->
								<div class="menu-item d-lg-none mt-5 pt-5 border-top">
									<a href="{{ route('login') }}" class="menu-link py-3 px-4">
										<span class="btn btn-success w-100">Sign In</span>
									</a>
								</div>
								<!--end::Menu item - Mobile Sign In-->
							</div>
							<!--end::Menu-->
						</div>
					</div>
					<!--end::Menu wrapper-->

					<!--begin::Right side - Mobile menu toggle + Desktop Sign In-->
					<div class="d-flex align-items-center gap-2 gap-lg-0">
						<!-- Desktop Sign In -->
						<a href="{{ route('login') }}" class="btn btn-success d-none d-lg-inline-block">Sign In</a>

						<!-- Mobile menu toggle - visible only on mobile -->
						<button class="btn btn-icon btn-active-color-primary d-flex d-lg-none p-0" id="kt_landing_menu_toggle" style="margin-right: -12px; margin-top: 2px;">
							<i class="ki-duotone ki-abstract-14 fs-2hx">
								<span class="path1"></span>
								<span class="path2"></span>
							</i>
						</button>
					</div>
					<!--end::Right side-->

				</div>
				<!--end::Wrapper-->
			</div>
			<!--end::Container-->
		</div>

		{{--
		@if(Route::currentRouteName() === 'home')
		<!--begin::Landing hero-->
		<div class="d-flex flex-column flex-center w-100 min-h-250px min-h-lg-500px px-9">
			...
		</div>
		<!--end::Landing hero-->
		@endif
		--}}

	</div>
	<!--end::Wrapper-->
</div>
<!--end::Header Section-->

<style>
/* Mobile menu icon adjustment - better margins */
@media (max-width: 991.98px) {
	#kt_landing_menu_toggle {
		margin-right: -5px !important;
		margin-top: 5px !important;
		padding: 0 !important;
	}

	#kt_landing_menu_toggle i {
		font-size: 1.75rem !important;
	}

	/* Logo padding adjustment on mobile - remove extra spacing */
	.d-flex.align-items-center .logo-default,
	.d-flex.align-items-center .logo-sticky {
		padding: -5px !important;
		margin: 0 !important;
	}

	/* Remove padding from logo link container */
	.d-flex.align-items-center > a {
		padding: 0 !important;
		margin: 0 !important;
	}

	/* Adjust the wrapper to respect margins */
	.d-flex.align-items-center.justify-content-between {
		padding-left: 0 !important;
		padding-right: 0 !important;
	}

	/* Push the whole header down a bit on mobile so it isn't flush with the top edge */
	.jp-landing-header {
		padding-top: 14px;
	}
	.jp-landing-header .container > .d-flex.align-items-center.justify-content-between {
		padding-top: 6px;
		padding-bottom: 10px;
	}
}

/* Logo line at the top of the mobile drawer */
.jp-drawer-logo {
	border-bottom-color: rgba(0, 0, 0, 0.06) !important;
}
</style>