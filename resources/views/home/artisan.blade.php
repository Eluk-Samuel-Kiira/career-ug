@extends('layouts.app')

@section('title', 'Artisan Commands')
@section('meta_description', 'Execute Laravel Artisan commands from your browser.')

@push('styles')
<style>
    /* ============================================================
       PAGE LAYOUT
       ============================================================ */
    .artisan-page {
        background: #f4f6fa;
        min-height: 80vh;
        padding: 40px 0;
    }

    .artisan-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .artisan-breadcrumb {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 30px;
    }

    .artisan-breadcrumb a {
        color: #6c757d;
        text-decoration: none;
    }

    .artisan-breadcrumb a:hover {
        color: #03A588;
    }

    .artisan-breadcrumb .sep {
        margin: 0 8px;
    }

    /* ============================================================
       CARDS
       ============================================================ */
    .artisan-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #e9ecef;
        height: 100%;
    }

    .artisan-card-header {
        padding: 24px 28px 0 28px;
    }

    .artisan-card-body {
        padding: 20px 28px 28px 28px;
    }

    .artisan-card-title {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .artisan-card-subtitle {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
        margin-top: 4px;
    }

    /* ============================================================
       FORM ELEMENTS
       ============================================================ */
    .artisan-label {
        font-weight: 600;
        font-size: 14px;
        color: #1a1a2e;
        margin-bottom: 8px;
        display: block;
    }

    .artisan-select {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        color: #1a1a2e;
        background: #fff;
        transition: border-color 0.2s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236c757d' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .artisan-select:focus {
        border-color: #03A588;
        outline: none;
        box-shadow: 0 0 0 3px rgba(3, 165, 136, 0.12);
    }

    /* ============================================================
       BUTTONS
       ============================================================ */
    .artisan-btn-primary {
        background: linear-gradient(135deg, #20AA3E 0%, #03A588 100%);
        border: none;
        color: #fff;
        font-weight: 700;
        padding: 12px 24px;
        border-radius: 10px;
        transition: all 0.3s ease;
        width: 100%;
        font-size: 15px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .artisan-btn-primary:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(3, 165, 136, 0.3);
    }

    .artisan-btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .artisan-btn-outline {
        background: transparent;
        border: 1.5px solid #e9ecef;
        color: #1a1a2e;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
    }

    .artisan-btn-outline:hover {
        border-color: #03A588;
        color: #03A588;
        background: rgba(3, 165, 136, 0.04);
    }

    /* ============================================================
       TERMINAL
       ============================================================ */
    .terminal-wrapper {
        background: #1a1a2e;
        border-radius: 10px;
        overflow: hidden;
        min-height: 350px;
    }

    .terminal-header {
        background: #16213e;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid #2a2a4a;
    }

    .terminal-dots {
        display: flex;
        gap: 6px;
    }

    .terminal-dot {
        width: 11px;
        height: 11px;
        border-radius: 50%;
        display: inline-block;
    }

    .terminal-dot-red { background: #ff5f56; }
    .terminal-dot-yellow { background: #ffbd2e; }
    .terminal-dot-green { background: #27c93f; }

    .terminal-title {
        color: #8a8aa0;
        font-size: 12px;
        font-weight: 500;
    }

    .terminal-body {
        background: #1a1a2e;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        color: #d4d4d4;
        min-height: 300px;
        padding: 18px;
        white-space: pre-wrap;
        word-break: break-word;
        overflow-y: auto;
        max-height: 450px;
        line-height: 1.6;
    }

    .terminal-body .prompt {
        color: #89b4fa;
    }

    .terminal-body .success {
        color: #a6e3a1;
    }

    .terminal-body .error {
        color: #f38ba8;
    }

    .terminal-body .muted {
        color: #6c7086;
    }

    /* ============================================================
       DESCRIPTION BOX
       ============================================================ */
    .artisan-desc-box {
        background: #f0f7ff;
        border-radius: 8px;
        padding: 14px 18px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border-left: 3px solid #03A588;
    }

    .artisan-desc-box .icon {
        color: #03A588;
        font-size: 18px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .artisan-desc-box .label {
        font-size: 11px;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .artisan-desc-box .text {
        font-size: 14px;
        color: #1a1a2e;
        font-weight: 600;
    }

    /* ============================================================
       STATUS BADGE
       ============================================================ */
    .artisan-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
    }

    .artisan-badge-success {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .artisan-badge-danger {
        background: #fbe9e7;
        color: #c62828;
    }

    /* ============================================================
       SELECT2 OVERRIDES
       ============================================================ */
    .select2-container--default .select2-selection--single {
        border: 1.5px solid #e9ecef !important;
        border-radius: 8px !important;
        height: 44px !important;
        background: #fff !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1a1a2e !important;
        line-height: 44px !important;
        padding-left: 14px !important;
        font-size: 14px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
        right: 10px !important;
    }

    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #03A588 !important;
    }

    .select2-dropdown {
        border: 1px solid #e9ecef !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
    }

    .select2-results__option {
        padding: 8px 14px !important;
        color: #1a1a2e !important;
        background: #fff !important;
        font-size: 14px;
    }

    .select2-results__option--highlighted {
        background: #03A588 !important;
        color: #fff !important;
    }

    .select2-results__option[aria-selected="true"] {
        background: #e8f5e9 !important;
        color: #03A588 !important;
    }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 991px) {
        .artisan-card {
            margin-bottom: 20px;
        }
        .artisan-card-header {
            padding: 18px 20px 0 20px;
        }
        .artisan-card-body {
            padding: 16px 20px 20px 20px;
        }
    }

    @media (max-width: 576px) {
        .artisan-page {
            padding: 16px 0;
        }
        .artisan-card-title {
            font-size: 16px;
        }
        .artisan-btn-primary {
            font-size: 14px;
            padding: 10px 16px;
        }
        .terminal-body {
            font-size: 12px;
            padding: 12px;
            min-height: 220px;
            max-height: 300px;
        }
    }
</style>
@endpush

@section('content')

<div class="artisan-page">
    <div class="artisan-container">

        {{-- Breadcrumb --}}
        <div class="artisan-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <span>Artisan Commands</span>
        </div>

        <div class="row g-4">

            {{-- Command Form Card --}}
            <div class="col-lg-5">
                <div class="artisan-card">
                    <div class="artisan-card-header">
                        <h3 class="artisan-card-title">Run Artisan Command</h3>
                        <p class="artisan-card-subtitle">Execute Laravel CLI commands</p>
                    </div>

                    <div class="artisan-card-body">
                        {{-- Command Select --}}
                        <div class="mb-4">
                            <label class="artisan-label">Select Command <span style="color:#dc3545;">*</span></label>
                            <select id="artisan_command" class="artisan-select">
                                <option value="">Select a command...</option>
                                @foreach($commands as $cmd => $description)
                                    <option value="{{ $cmd }}">php artisan {{ $cmd }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Description --}}
                        <div id="command_description" class="d-none mb-4">
                            <div class="artisan-desc-box">
                                <span class="icon">📄</span>
                                <div>
                                    <div class="label">Description</div>
                                    <div id="description_text" class="text"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Run Button --}}
                        <button id="run_btn" class="artisan-btn-primary" disabled>
                            <i class="ki-duotone ki-rocket fs-3 me-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Run Command
                        </button>
                    </div>
                </div>
            </div>

            {{-- Output Terminal Card --}}
            <div class="col-lg-7">
                <div class="artisan-card">
                    <div class="artisan-card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="artisan-card-title">Terminal Output</h3>
                                <p class="artisan-card-subtitle">Command execution results</p>
                            </div>
                            <button id="clear_output" class="artisan-btn-outline">
                                <i class="ki-duotone ki-trash fs-3 me-1">
                                    <span class="path1"></span><span class="path2"></span>
                                    <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                </i>
                                Clear
                            </button>
                        </div>
                    </div>

                    <div class="artisan-card-body">
                        {{-- Terminal Window --}}
                        <div class="terminal-wrapper">
                            <div class="terminal-header">
                                <div class="terminal-dots">
                                    <span class="terminal-dot terminal-dot-red"></span>
                                    <span class="terminal-dot terminal-dot-yellow"></span>
                                    <span class="terminal-dot terminal-dot-green"></span>
                                </div>
                                <span class="terminal-title" id="terminal_title">Terminal</span>
                            </div>
                            <div id="terminal_output" class="terminal-body">
                                <span class="muted">Select a command and click "Run Command" to execute it.</span>
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        <div id="status_badge" class="mt-3"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const commands = @json($commands);

document.addEventListener('DOMContentLoaded', function() {
    const selectEl      = document.getElementById('artisan_command');
    const runBtn        = document.getElementById('run_btn');
    const terminalOut   = document.getElementById('terminal_output');
    const terminalTitle = document.getElementById('terminal_title');
    const statusBadge   = document.getElementById('status_badge');
    const cmdDesc       = document.getElementById('command_description');
    const descText      = document.getElementById('description_text');
    const clearBtn      = document.getElementById('clear_output');

    // On command select
    selectEl.addEventListener('change', function() {
        const val = this.value;
        if (val) {
            runBtn.disabled = false;
            descText.textContent = commands[val] || '';
            cmdDesc.classList.remove('d-none');
        } else {
            runBtn.disabled = true;
            cmdDesc.classList.add('d-none');
        }
    });

    // Run command
    runBtn.addEventListener('click', function() {
        const command = selectEl.value;
        if (!command) return;

        // Disable button during execution
        runBtn.disabled = true;
        runBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Running...';

        terminalTitle.textContent = 'Running: php artisan ' + command;
        terminalOut.innerHTML = '<span class="prompt">$ php artisan ' + command + '</span>\n<span class="muted">Executing command...</span>';
        statusBadge.innerHTML = '';

        fetch('{{ route("frontend.artisan.run") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ command: command })
        })
        .then(res => res.json())
        .then(data => {
            const colorClass = data.success ? 'success' : 'error';
            const icon = data.success ? '✅' : '❌';

            terminalOut.innerHTML = 
                '<span class="prompt">$ php artisan ' + (data.command || command) + '</span>\n\n' +
                '<span class="' + colorClass + '">' + escapeHtml(data.output) + '</span>';

            terminalTitle.textContent = icon + ' php artisan ' + (data.command || command);

            statusBadge.innerHTML = data.success
                ? '<span class="artisan-badge artisan-badge-success"><i class="ki-duotone ki-check-circle fs-3 me-2"><span class="path1"></span><span class="path2"></span></i> Success</span>'
                : '<span class="artisan-badge artisan-badge-danger"><i class="ki-duotone ki-cross-circle fs-3 me-2"><span class="path1"></span><span class="path2"></span></i> Failed</span>';
        })
        .catch(err => {
            terminalOut.innerHTML = 
                '<span class="prompt">$ php artisan ' + command + '</span>\n\n' +
                '<span class="error">❌ Error: ' + escapeHtml(err.message) + '</span>';
            terminalTitle.textContent = '❌ php artisan ' + command;
            statusBadge.innerHTML = '<span class="artisan-badge artisan-badge-danger"><i class="ki-duotone ki-cross-circle fs-3 me-2"><span class="path1"></span><span class="path2"></span></i> Error</span>';
        })
        .finally(() => {
            runBtn.disabled = false;
            runBtn.innerHTML = '<i class="ki-duotone ki-rocket fs-3 me-2"><span class="path1"></span><span class="path2"></span></i> Run Command';
        });
    });

    // Clear output
    clearBtn.addEventListener('click', function() {
        terminalOut.innerHTML = '<span class="muted">Select a command and click "Run Command" to execute it.</span>';
        terminalTitle.textContent = 'Terminal';
        statusBadge.innerHTML = '';
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
});
</script>
@endpush