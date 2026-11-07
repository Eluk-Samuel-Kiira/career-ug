<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ config('app.name') }} - @yield('title', 'Dashboard')</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="{{ country_favicon() }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
    <script>if (window.top != window.self) { window.top.location.replace(window.self.location.href); }</script>
    <style>
        /* ==========================================================================
        WhatsApp float widget
        ========================================================================== */
        .jp-wa-float-wrap {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
            pointer-events: none;   /* only the button + popover capture clicks */
        }

        .jp-wa-float,
        .jp-wa-popover {
            pointer-events: auto;
        }

        /* ── Floating button ──────────────────────────────────── */
        .jp-wa-float {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: none;
            background: #25D366;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.35), 0 2px 6px rgba(0, 0, 0, 0.15);
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            cursor: pointer;
            position: relative;
            padding: 0;
        }

        .jp-wa-float svg {
            width: 28px;
            height: 28px;
            display: block;
        }

        .jp-wa-float:hover {
            background: #1ebe5b;
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 12px 28px rgba(37, 211, 102, 0.45), 0 4px 10px rgba(0, 0, 0, 0.18);
        }

        .jp-wa-float:active {
            transform: translateY(0) scale(0.98);
        }

        .jp-wa-float[aria-expanded="true"] {
            background: #128C7E;   /* darker green — indicates "open" */
        }

        /* Small unread-style badge */
        .jp-wa-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 10px;
            background: #FE3C30;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            line-height: 20px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(254, 60, 48, 0.5);
            border: 2px solid #fff;
            pointer-events: none;
            transition: transform .2s ease;
        }

        .jp-wa-float[aria-expanded="true"] .jp-wa-badge {
            transform: scale(0);
        }

        /* ── Popover ──────────────────────────────────────────── */
        .jp-wa-popover {
            width: 320px;
            max-width: calc(100vw - 32px);
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(11, 28, 46, 0.22), 0 6px 14px rgba(0, 0, 0, 0.08);
            padding: 18px;
            position: relative;
            transform-origin: bottom right;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px) scale(0.96);
            transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
        }

        .jp-wa-popover.jp-wa-open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        /* Little tail pointing at the button */
        .jp-wa-popover::after {
            content: "";
            position: absolute;
            bottom: -8px;
            right: 22px;
            width: 16px;
            height: 16px;
            background: #fff;
            transform: rotate(45deg);
            box-shadow: 4px 4px 8px rgba(11, 28, 46, 0.06);
        }

        .jp-wa-popover-close {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #64748B;
            font-size: 20px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        .jp-wa-popover-close:hover {
            background: #F4F8F7;
            color: #0F1B2D;
        }

        .jp-wa-popover-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
            padding-right: 24px;   /* leave room for close button */
        }

        .jp-wa-popover-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #25D366;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .jp-wa-popover-avatar svg {
            width: 22px;
            height: 22px;
            display: block;
        }

        .jp-wa-popover-title {
            font-weight: 800;
            font-size: 0.95rem;
            color: #0F1B2D;
            line-height: 1.25;
            margin-bottom: 2px;
        }

        .jp-wa-popover-sub {
            font-size: 0.78rem;
            color: #64748B;
            line-height: 1.3;
        }

        .jp-wa-popover-body {
            font-size: 0.85rem;
            color: #33475B;
            line-height: 1.5;
            margin: 0 0 14px;
        }

        .jp-wa-popover-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            background: #25D366;
            color: #fff;
            font-weight: 700;
            font-size: 0.88rem;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 10px;
            transition: background .15s ease, transform .1s ease;
        }

        .jp-wa-popover-cta:hover {
            background: #1ebe5b;
            color: #fff;
            transform: translateY(-1px);
        }

        .jp-wa-popover-phone {
            font-size: 0.75rem;
            color: #64748B;
            text-align: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed rgba(15, 27, 45, 0.08);
        }

        /* ── Mobile ──────────────────────────────────────────── */
        @media (max-width: 575.98px) {
            .jp-wa-float-wrap {
                bottom: 16px;
                right: 16px;
            }
            .jp-wa-float {
                width: 52px;
                height: 52px;
            }
            .jp-wa-float svg {
                width: 26px;
                height: 26px;
            }
            .jp-wa-popover {
                width: calc(100vw - 32px);
                max-width: 340px;
                padding: 16px;
            }
        }
    </style>
</head>
<body id="kt_app_body"
    data-kt-app-layout="dark-sidebar"
    data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true"
    data-kt-app-sidebar-fixed="true"
    data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true"
    data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true"
    data-kt-app-toolbar-enabled="true"
    class="app-default">

    <script>
        var defaultThemeMode = "light"; var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                themeMode = localStorage.getItem("data-bs-theme") ?? defaultThemeMode;
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    @php
        // Turn the display phone ("+256 754 428 612") into a digits-only number
        // wa.me requires: country code + number, no "+", no spaces, no dashes.
        $waRaw   = support_phone();            // "+256 754 428 612"
        $waDigits = preg_replace('/\D+/', '', $waRaw);  // "256754428612"
        $waDigits = ltrim($waDigits, '0');              // strip leading zeros

        // Optional: pre-fill the greeting so WhatsApp opens with text ready to send
        $waGreeting = rawurlencode("Hi, I'm on " . config('app.name') . " and I'd like some help.");
        $waUrl      = "https://wa.me/{$waDigits}?text={$waGreeting}";
    @endphp
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

            {{-- Header --}}
            @include('layouts.partials.admin-header')

            {{-- Wrapper --}}
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

                {{-- Sidebar --}}
                @include('layouts.partials.admin-sidebar')

                {{-- Main --}}
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">

                        {{-- Toolbar --}}
                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                                        @yield('page_title', 'Dashboard')
                                    </h1>
                                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                                        <li class="breadcrumb-item text-muted">
                                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                                        </li>
                                        @yield('breadcrumb')
                                    </ul>
                                </div>
                                <div class="d-flex align-items-center gap-2 gap-lg-3">
                                    @yield('toolbar_actions')
                                </div>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                @yield('content')
                            </div>
                        </div>

                    </div>

                    {{-- ==================================================================
                        WhatsApp float — clickable "We're here to help" popover
                        ================================================================== --}}
                    <div class="jp-wa-float-wrap">

                        {{-- Popover --}}
                        <div id="jpWaPopover" class="jp-wa-popover" role="dialog" aria-labelledby="jpWaTitle" aria-hidden="true">
                            <button type="button"
                                    class="jp-wa-popover-close"
                                    aria-label="Close"
                                    data-jp-wa-close>
                                &times;
                            </button>

                            <div class="jp-wa-popover-head">
                                <span class="jp-wa-popover-avatar" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.6 6.32A8.86 8.86 0 0 0 12.02 3.5c-4.87 0-8.83 3.94-8.83 8.79 0 1.55.41 3.06 1.19 4.39L3.2 21l4.44-1.16a8.9 8.9 0 0 0 4.37 1.12h.01c4.87 0 8.83-3.94 8.83-8.79a8.7 8.7 0 0 0-2.25-5.85Zm-5.58 13.5h-.01c-1.36 0-2.7-.36-3.86-1.05l-.28-.16-2.87.75.77-2.79-.18-.29a7.3 7.3 0 0 1-1.13-3.9c0-4.03 3.3-7.32 7.36-7.32a7.3 7.3 0 0 1 5.2 2.15 7.24 7.24 0 0 1 2.16 5.17c0 4.03-3.3 7.32-7.36 7.32Zm4.03-5.48c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.58.71-.71.86-.26.16-.48.05a6.03 6.03 0 0 1-1.77-1.09 6.6 6.6 0 0 1-1.22-1.52c-.13-.22 0-.34.1-.45.1-.1.22-.26.33-.39.11-.13.15-.22.22-.37.07-.15.04-.28-.02-.39-.06-.11-.5-1.2-.68-1.65-.18-.43-.36-.37-.5-.38h-.43c-.15 0-.39.06-.6.28-.2.22-.79.77-.79 1.87 0 1.1.81 2.17.92 2.32.11.15 1.6 2.44 3.87 3.42.54.23.96.37 1.29.48.54.17 1.03.15 1.42.09.43-.06 1.3-.53 1.49-1.04.18-.51.18-.94.13-1.03-.05-.09-.2-.15-.42-.26Z"/>
                                    </svg>
                                </span>
                                <div>
                                    <div id="jpWaTitle" class="jp-wa-popover-title">We're here to help</div>
                                    <div class="jp-wa-popover-sub">Chat with us on WhatsApp</div>
                                </div>
                            </div>

                            <p class="jp-wa-popover-body">
                                Have a question about a job, an application, or our services?
                                Send us a message and we'll reply as soon as we can.
                            </p>

                            <a href="{{ $waUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="jp-wa-popover-cta"
                            data-jp-wa-cta>
                                Continue to WhatsApp
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path d="M5 12h14M13 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <div class="jp-wa-popover-phone">
                                Or call us: <a href="tel:{{ preg_replace('/\s+/', '', support_phone()) }}" style="color:inherit;">{{ support_phone() }}</a>
                            </div>
                        </div>

                        {{-- Floating button --}}
                        <button type="button"
                                id="jpWaButton"
                                class="jp-wa-float"
                                aria-label="Chat with us on WhatsApp"
                                aria-expanded="false"
                                aria-controls="jpWaPopover">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M17.6 6.32A8.86 8.86 0 0 0 12.02 3.5c-4.87 0-8.83 3.94-8.83 8.79 0 1.55.41 3.06 1.19 4.39L3.2 21l4.44-1.16a8.9 8.9 0 0 0 4.37 1.12h.01c4.87 0 8.83-3.94 8.83-8.79a8.7 8.7 0 0 0-2.25-5.85Zm-5.58 13.5h-.01c-1.36 0-2.7-.36-3.86-1.05l-.28-.16-2.87.75.77-2.79-.18-.29a7.3 7.3 0 0 1-1.13-3.9c0-4.03 3.3-7.32 7.36-7.32a7.3 7.3 0 0 1 5.2 2.15 7.24 7.24 0 0 1 2.16 5.17c0 4.03-3.3 7.32-7.36 7.32Zm4.03-5.48c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.58.71-.71.86-.26.16-.48.05a6.03 6.03 0 0 1-1.77-1.09 6.6 6.6 0 0 1-1.22-1.52c-.13-.22 0-.34.1-.45.1-.1.22-.26.33-.39.11-.13.15-.22.22-.37.07-.15.04-.28-.02-.39-.06-.11-.5-1.2-.68-1.65-.18-.43-.36-.37-.5-.38h-.43c-.15 0-.39.06-.6.28-.2.22-.79.77-.79 1.87 0 1.1.81 2.17.92 2.32.11.15 1.6 2.44 3.87 3.42.54.23.96.37 1.29.48.54.17 1.03.15 1.42.09.43-.06 1.3-.53 1.49-1.04.18-.51.18-.94.13-1.03-.05-.09-.2-.15-.42-.26Z"/>
                            </svg>

                            {{-- Tiny badge --}}
                            <span class="jp-wa-badge" aria-hidden="true">1</span>
                        </button>
                    </div>

                    {{-- Footer --}}
                    <div id="kt_app_footer" class="app-footer">
                        <div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
                            <div class="text-gray-900 order-2 order-md-1">
                                <span class="text-muted fw-semibold me-1">{{ date('Y') }}&copy;</span>
                                <a href="#" class="text-gray-800 text-hover-primary">{{ config('app.name') }}</a>
                            </div>
                        </div>
                    </div>

                </div>
                {{-- End Main --}}
            </div>
        </div>
    </div>

    <!--
        // Success toast
        showToast('success', 'Operation completed successfully!');

        // Error toast
        showToast('error', 'Something went wrong!');

        // Warning toast
        showToast('warning', 'Please check your input!');

        // Info toast with title
        showToast('info', 'New update available', 'Information');

        // For buttons with spinners
        const myButton = document.getElementById('myButton');
        showButtonSpinner(myButton);
        // ... do async operation
        hideButtonSpinner(myButton);
    -->
        
    <!-- Toast Container (top-right with high z-index to overlay all content) -->
    <div id="toastStackContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    <!-- Hidden template used for cloning -->
    <div id="toastTemplate" class="toast d-none" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-white">
            <i class="ki-duotone ki-abstract-23 fs-2 me-3"></i>
            <strong class="me-auto">Title</strong>
            <small class="text-muted">Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body bg-white">
            Message goes here.
        </div>
    </div>

    
    <script>


        (function() {
            // ----- DOM elements -----
            const container = document.getElementById('toastStackContainer');
            const template = document.getElementById('toastTemplate');

            if (!container || !template) return;

            // Remove template from live DOM (keep as clone source)
            template.remove();

            // Ensure container has highest z-index
            container.style.zIndex = '9999';

            // ----- helper: update icon and text colours based on type -----
            function styleToastByType(toastElement, type) {
                const icon = toastElement.querySelector('.toast-header i');
                const titleEl = toastElement.querySelector('.toast-header strong');
                const messageEl = toastElement.querySelector('.toast-body');
                const smallEl = toastElement.querySelector('.toast-header small');
                const closeBtn = toastElement.querySelector('.btn-close');

                // Reset any previous status classes from all elements
                const elementsToReset = [icon, titleEl, messageEl, smallEl];
                elementsToReset.forEach(el => {
                    if (el) {
                        el.classList.remove('text-success', 'text-danger', 'text-warning', 'text-info', 'text-muted');
                    }
                });

                // Apply new colours based on type
                switch (type) {
                    case 'success':
                        if (icon) {
                            icon.classList.add('text-success');
                            icon.className = 'ki-duotone ki-check-circle fs-2 me-3 text-success';
                        }
                        if (titleEl) titleEl.classList.add('text-success');
                        if (messageEl) messageEl.classList.add('text-success');
                        if (smallEl) smallEl.classList.add('text-success');
                        break;
                    case 'error':
                    case 'danger':
                        if (icon) {
                            icon.classList.add('text-danger');
                            icon.className = 'ki-duotone ki-cross-circle fs-2 me-3 text-danger';
                        }
                        if (titleEl) titleEl.classList.add('text-danger');
                        if (messageEl) messageEl.classList.add('text-danger');
                        if (smallEl) smallEl.classList.add('text-danger');
                        break;
                    case 'warning':
                        if (icon) {
                            icon.classList.add('text-warning');
                            icon.className = 'ki-duotone ki-information-5 fs-2 me-3 text-warning';
                        }
                        if (titleEl) titleEl.classList.add('text-warning');
                        if (messageEl) messageEl.classList.add('text-warning');
                        if (smallEl) smallEl.classList.add('text-warning');
                        break;
                    case 'info':
                    default:
                        if (icon) {
                            icon.classList.add('text-info');
                            icon.className = 'ki-duotone ki-information-4 fs-2 me-3 text-info';
                        }
                        if (titleEl) titleEl.classList.add('text-info');
                        if (messageEl) messageEl.classList.add('text-info');
                        if (smallEl) smallEl.classList.add('text-info');
                        break;
                }

                // Close button always visible on white background
                if (closeBtn) closeBtn.classList.add('btn-close');
            }

            // ----- Global showToast function -----
            window.showToast = function(type, message, title = '', duration = 5000) {
                // Clone template
                const newToast = template.cloneNode(true);
                newToast.classList.remove('d-none');
                
                // Add shadow for better visibility over content
                newToast.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';

                // Fill content
                const titleElem = newToast.querySelector('.toast-header strong');
                const bodyElem = newToast.querySelector('.toast-body');
                const timeElem = newToast.querySelector('.toast-header small');

                if (titleElem) titleElem.textContent = title || (type.charAt(0).toUpperCase() + type.slice(1));
                if (bodyElem) bodyElem.textContent = message;
                if (timeElem) {
                    const now = new Date();
                    timeElem.textContent = `${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')}`;
                }

                // Apply status colours to ALL text elements (title, message, time)
                styleToastByType(newToast, type);

                // Append, initialise and show
                container.appendChild(newToast);
                const bsToast = new bootstrap.Toast(newToast, { autohide: true, delay: duration });
                bsToast.show();

                // Auto‑remove after hidden
                newToast.addEventListener('hidden.bs.toast', () => newToast.remove());
            };
        })();
    </script>

    <!-- spinner button -->
    <script>
        // Global Spinner Functions for Metronic Buttons
        window.showButtonSpinner = function(button) {
            if (!button) return;
            button.setAttribute('data-kt-indicator', 'on');
            button.disabled = true;
        };

        window.hideButtonSpinner = function(button) {
            if (!button) return;
            button.removeAttribute('data-kt-indicator');
            button.disabled = false;
        };


    </script>

    <script>
    (function () {
        'use strict';

        const wrap    = document.querySelector('.jp-wa-float-wrap');
        const btn     = document.getElementById('jpWaButton');
        const popover = document.getElementById('jpWaPopover');
        if (!wrap || !btn || !popover) return;

        const closeBtn = popover.querySelector('[data-jp-wa-close]');

        function openPopover() {
            popover.classList.add('jp-wa-open');
            popover.setAttribute('aria-hidden', 'false');
            btn.setAttribute('aria-expanded', 'true');
        }

        function closePopover() {
            popover.classList.remove('jp-wa-open');
            popover.setAttribute('aria-hidden', 'true');
            btn.setAttribute('aria-expanded', 'false');
        }

        function togglePopover() {
            if (popover.classList.contains('jp-wa-open')) closePopover();
            else openPopover();
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            togglePopover();
        });

        closeBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            closePopover();
        });

        // Click outside closes
        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target) && popover.classList.contains('jp-wa-open')) {
                closePopover();
            }
        });

        // Escape key closes
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && popover.classList.contains('jp-wa-open')) {
                closePopover();
                btn.focus();
            }
        });

        // Opening the WhatsApp link — auto-close so it doesn't linger on return
        popover.querySelector('[data-jp-wa-cta]')?.addEventListener('click', function () {
            // Wait for the navigation to kick off, then close the popover
            setTimeout(closePopover, 100);
        });

        // Optional: show the popover automatically the first time a guest
        // stays on the page for 8+ seconds without clicking
        const KEY = 'jp_wa_greeted';
        if (!sessionStorage.getItem(KEY)) {
            const t = setTimeout(function () {
                if (!popover.classList.contains('jp-wa-open')) {
                    openPopover();
                    sessionStorage.setItem(KEY, '1');
                }
            }, 8000);

            // If they interact first, cancel the auto-open
            document.addEventListener('click', () => clearTimeout(t), { once: true });
        }
    })();
    </script>

    <script>
    
	    document.addEventListener('DOMContentLoaded', function () {
        	// ----- Display session messages as toasts -----
            @if(session('success'))
                if (typeof window.showToast === 'function') {
                    window.showToast('success', '{{ session('success') }}', 'Success');
                }
            @endif

            @if(session('error'))
                if (typeof window.showToast === 'function') {
                    window.showToast('error', '{{ session('error') }}', 'Error');
                }
            @endif

            @if(session('warning'))
                if (typeof window.showToast === 'function') {
                    window.showToast('warning', '{{ session('warning') }}', 'Warning');
                }
            @endif

            @if(session('info'))
                if (typeof window.showToast === 'function') {
                    window.showToast('info', '{{ session('info') }}', 'Info');
                }
            @endif
        });
    </script>


    {{-- Scripts --}}
    <script>var hostUrl = "{{ asset('assets/') }}";</script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('assets/js/custom/apps/chat/chat.js') }}"></script>
    @stack('scripts')
</body>
</html>