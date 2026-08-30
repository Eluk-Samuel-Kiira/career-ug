@php
    $user = session('user');
    $userRole = $user['role'] ?? 'job_seeker';
@endphp

<div id="kt_app_sidebar" class="app-sidebar flex-column"
    data-kt-drawer="true"
    data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}"
    data-kt-drawer-overlay="true"
    data-kt-drawer-width="225px"
    data-kt-drawer-direction="start"
    data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    {{-- Logo --}}
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ route('dashboard') }}">
            <img alt="Logo" src="{{ asset('careers.png') }}" class="app-sidebar-logo-default" style="height:50px; width:210px;" />
            <img alt="Logo" src="{{ asset('fav.png') }}" class="app-sidebar-logo-minimize" style="height:30px; width:30px;" />
        </a>
        <div id="kt_app_sidebar_toggle"
            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
            data-kt-toggle="true"
            data-kt-toggle-state="active"
            data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </div>
    </div>

    {{-- Menu --}}
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3"
                data-kt-scroll="true"
                data-kt-scroll-activate="true"
                data-kt-scroll-height="auto"
                data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                data-kt-scroll-wrappers="#kt_app_sidebar_menu"
                data-kt-scroll-offset="5px"
                data-kt-scroll-save-state="true">

                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6"
                    id="#kt_app_sidebar_menu"
                    data-kt-menu="true"
                    data-kt-menu-expand="false">

                    {{-- Dashboard --}}
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-element-11 fs-2">
                                    <span class="path1"></span><span class="path2"></span>
                                    <span class="path3"></span><span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('jobs.index') ? 'active' : '' }}" href="{{ route('jobs.index') }}" target="_blank">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-briefcase fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Browse Jobs</span>
                        </a>
                    </div>

                    {{-- ===================== JOB SEEKER MENU ===================== --}}
                    @if($userRole === 'job_seeker' || $userRole === 'seeker')
                    
                    {{-- CV Upload --}}
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('cv.edit*') ? 'show here' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-crown fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">My CV</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('cv.edit*') ? 'active' : '' }}" href="{{ route('cv.edit') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Upload / Edit CV</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Application Letter --}}
                    <div class="menu-item">
                        <a class="menu-link" href="#">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-message-text-2 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Application Letter</span>
                        </a>
                    </div>

                    {{-- Cover Letter --}}
                    <div class="menu-item">
                        <a class="menu-link" href="#">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-document fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Cover Letter</span>
                        </a>
                    </div>

                    {{-- Job Alerts --}}
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion  {{ request()->routeIs('alert.preferences*') ? 'show here' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-notification-2 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Job Alerts</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('alert.preferences') ? 'active' : '' }}" 
                                href="{{ route('alert.preferences') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Alert Preferences</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">WhatsApp Alerts</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Email Alerts</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- My Applications --}}
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('jobs.applied') ? 'active' : '' }}" href="{{ route('jobs.applied') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-briefcase fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">My Applications</span>
                        </a>
                    </div>

                    {{-- Saved Jobs --}}
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('jobs.saved') ? 'active' : '' }}" href="{{ route('jobs.saved') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-bookmark fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Saved Jobs</span>
                        </a>
                    </div>

                    {{-- Plan Paid --}}
                    <div class="menu-item">
                        <a class="menu-link" href="#">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-dollar fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Premium Plan</span>
                            <span class="badge badge-light-success ms-2">Upgrade</span>
                        </a>
                    </div>

                    {{-- Separator --}}
                    <div class="separator my-3"></div>

                    {{-- Profile --}}
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('profile.show') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-profile-circle fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">My Profile</span>
                        </a>
                    </div>

                    {{-- ===================== EMPLOYER MENU ===================== --}}
                    @elseif($userRole === 'employer')

                    {{-- Post a Job --}}
                    <div class="menu-item">
                        <a class="menu-link" href="#">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-plus-square fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Post a Job</span>
                        </a>
                    </div>

                    {{-- My Jobs --}}
                    <div class="menu-item">
                        <a class="menu-link" href="#">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-briefcase fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">My Job Posts</span>
                        </a>
                    </div>

                    {{-- Applications --}}
                    <div class="menu-item">
                        <a class="menu-link" href="#">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-user-tick fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Applications Received</span>
                            <span class="badge badge-light-danger ms-2">12</span>
                        </a>
                    </div>

                    {{-- CV Filtering --}}
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-filter fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">CV Filtering</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">By Category</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">By Experience</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">By Location</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Company Profile --}}
                    <div class="menu-item">
                        <a class="menu-link" href="#">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-building fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Company Profile</span>
                        </a>
                    </div>

                    {{-- Analytics --}}
                    <div class="menu-item">
                        <a class="menu-link" href="#">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-chart-simple fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Job Analytics</span>
                        </a>
                    </div>

                    {{-- Plan Paid --}}
                    <div class="menu-item">
                        <a class="menu-link" href="#">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-dollar fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Subscription Plan</span>
                            <span class="badge badge-light-success ms-2">Upgrade</span>
                        </a>
                    </div>

                    {{-- Separator --}}
                    <div class="separator my-3"></div>

                    {{-- Profile --}}
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('profile.show') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-profile-circle fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">My Profile</span>
                        </a>
                    </div>

                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar footer --}}
    <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100">
                <span class="btn-label">Sign Out</span>
                <i class="ki-duotone ki-entrance-right btn-icon fs-2 m-0">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </button>
        </form>
    </div>

</div>