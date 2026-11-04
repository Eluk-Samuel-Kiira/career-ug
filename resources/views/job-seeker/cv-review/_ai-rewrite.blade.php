{{-- ─────────────────────────────────────────────────────────────
     AI Rewrite viewer — shows loading, error, or the ready CV
     Expects: $r (the request array)
───────────────────────────────────────────────────────────── --}}
@if(in_array($r['status'], ['paid_ai', 'generating', 'generated'], true) && ($r['rewrite_mode'] ?? null) === 'ai')
<div id="aiRewriteSection" data-uuid="{{ $r['uuid'] }}">

    {{-- Loading state --}}
    <div id="rewriteLoading" class="card card-flush mb-6 {{ $r['rewrite_is_ready'] ? 'd-none' : '' }}">
        <div class="card-body text-center py-15">
            <div class="spinner-border text-primary mb-4" style="width:3rem;height:3rem;"></div>
            <h3 class="fw-bold mb-2">Generating Your Rewritten CV&hellip;</h3>
            <p class="text-muted">This usually takes 30&ndash;60 seconds.</p>
        </div>
    </div>

    {{-- Error state --}}
    @if(!empty($r['rewrite_error_message']) && !$r['rewrite_is_ready'])
    <div class="alert alert-danger mb-6">
        <div class="fw-bold mb-1">Generation failed</div>
        <div class="text-muted fs-7">{{ $r['rewrite_error_message'] }}</div>
        <button class="btn btn-warning btn-sm mt-3" id="regenerateBtn">
            <span class="indicator-label">Try Again</span>
            <span class="indicator-progress">
                Retrying...
                <span class="spinner-border spinner-border-sm ms-2"></span>
            </span>
        </button>
    </div>
    @endif

    {{-- Ready state --}}
    @if($r['rewrite_is_ready'])
    <div id="rewriteReady" class="card card-flush mb-6 border border-success border-2">
        <div class="card-header bg-light-success">
            <h3 class="card-title">
                <i class="ki-duotone ki-document fs-2 me-2">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                Your Rewritten CV
                @if($r['rewrite_is_user_edited'])
                    <span class="badge badge-light-primary ms-2">Edited</span>
                @endif
            </h3>
            <div class="card-toolbar gap-2">

                {{-- Desktop: all buttons inline --}}
                <div class="d-none d-md-flex gap-2 flex-wrap">
                    <button type="button"
                            class="btn btn-sm btn-light-info"
                            data-bs-toggle="modal"
                            data-bs-target="#sampleFormatModal">
                        <i class="ki-duotone ki-document fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Standard Format
                    </button>
                    <button class="btn btn-sm btn-light-primary" id="editCvBtn">
                        <i class="ki-duotone ki-pencil fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        Edit
                    </button>
                    <button class="btn btn-sm btn-light" id="copyCvBtn">
                        <i class="ki-duotone ki-copy fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Copy Text
                    </button>
                    @if($r['rewrite_can_regenerate'])
                        <button class="btn btn-sm btn-light-warning" id="regenerateBtn">
                            <span class="indicator-label">
                                <i class="ki-duotone ki-arrows-circle fs-4 me-1">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                Regenerate ({{ $r['rewrite_max_regenerations'] - $r['rewrite_regeneration_count'] }} left)
                            </span>
                            <span class="indicator-progress">
                                Regenerating...
                                <span class="spinner-border spinner-border-sm ms-2"></span>
                            </span>
                        </button>
                    @endif
                    <button class="btn btn-sm btn-success" id="markDoneBtn" data-uuid="{{ $r['uuid'] }}">
                        <i class="ki-duotone ki-check-circle fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Mark as Done
                    </button>
                    <a href="{{ route('cv-review.rewrite.download', $r['uuid']) }}"
                    class="btn btn-sm btn-primary">
                        <i class="ki-duotone ki-file-down fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Download PDF
                    </a>
                </div>

                {{-- Mobile: single "Actions" dropdown + primary Download --}}
                <div class="d-flex d-md-none gap-2">
                    <a href="{{ route('cv-review.rewrite.download', $r['uuid']) }}"
                    class="btn btn-sm btn-primary flex-grow-1">
                        <i class="ki-duotone ki-file-down fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Download
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ki-duotone ki-dots-horizontal fs-3">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <button type="button"
                                        class="dropdown-item"
                                        data-bs-toggle="modal"
                                        data-bs-target="#sampleFormatModal">
                                    <i class="ki-duotone ki-document fs-5 me-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    Standard Format
                                </button>
                            </li>
                            <li>
                                <button type="button"
                                        class="dropdown-item"
                                        id="editCvBtnMobile">
                                    <i class="ki-duotone ki-pencil fs-5 me-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    Edit
                                </button>
                            </li>
                            <li>
                                <button type="button"
                                        class="dropdown-item"
                                        id="copyCvBtnMobile">
                                    <i class="ki-duotone ki-copy fs-5 me-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    Copy Text
                                </button>
                            </li>
                            @if($r['rewrite_can_regenerate'])
                                <li>
                                    <button type="button"
                                            class="dropdown-item"
                                            id="regenerateBtnMobile">
                                        <i class="ki-duotone ki-arrows-circle fs-5 me-2">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                        Regenerate ({{ $r['rewrite_max_regenerations'] - $r['rewrite_regeneration_count'] }} left)
                                    </button>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button"
                                        class="dropdown-item text-success"
                                        id="markDoneBtnMobile">
                                    <i class="ki-duotone ki-check-circle fs-5 me-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    Mark as Done
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">

            {{-- View mode --}}
            <div id="cvView">
                <div class="border rounded bg-white p-4 p-md-8 cv-preview">
                    {!! $r['rewrite_content']['html'] ?? '' !!}
                </div>
                @if(!$r['rewrite_can_regenerate'])
                    <div class="alert alert-light-info mt-4 fs-7 mb-0">
                        Regeneration limit reached.
                        @if($r['rewrite_is_user_edited'])
                            You can still edit manually.
                        @endif
                    </div>
                @endif
            </div>

            {{-- Edit mode --}}
            <div id="cvEdit" class="d-none">
                <div class="alert alert-light-info mb-3 fs-7">
                    Edit your CV. The PDF download will use this text.
                </div>
                <div class="border rounded bg-white p-2">
                    <textarea id="cvEditor" class="form-control" rows="24"></textarea>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
                    <button class="btn btn-primary" id="saveEditsBtn">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-check-circle fs-4 me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Save Changes
                        </span>
                        <span class="indicator-progress">
                            Saving...
                            <span class="spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                    <button class="btn btn-light" id="cancelEditsBtn">Cancel</button>
                    @if($r['rewrite_is_user_edited'])
                        <button class="btn btn-light-danger" id="resetToAiBtn">
                            <i class="ki-duotone ki-arrows-circle fs-4 me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Reset to AI Version
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- ============================================================= --}}
{{-- SAMPLE / STANDARD ATS FORMAT MODAL                            --}}
{{-- ============================================================= --}}
<div class="modal fade" id="sampleFormatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h3 class="fw-bold mb-1">Standard ATS-Friendly Format</h3>
                    <div class="text-muted fs-7">
                        This is the format our AI writes your CV in — clean, single-column,
                        ATS-readable. Use it as a reference when you edit.
                    </div>
                </div>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary"
                        data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>

            <div class="modal-body pt-4">

                <div class="alert alert-light-info d-flex align-items-start mb-3">
                    <i class="ki-duotone ki-information-5 fs-3 text-info me-2 mt-1 flex-shrink-0">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="fs-7">
                        <strong>Why this format matters.</strong>
                        Applicant Tracking Systems (ATS) read plain, single-column layouts.
                        Tables, columns, images, and text boxes often cause the ATS to misread
                        or drop content. Your rewritten CV follows this structure already.
                    </div>
                </div>

                <div class="border rounded bg-light" style="height: 70vh; overflow: hidden;">
                    <iframe
                        src="{{ asset('sample_cv.pdf') }}#toolbar=0&navpanes=0"
                        type="application/pdf"
                        title="Standard ATS CV Format"
                        style="width: 100%; height: 100%; border: 0;">
                        <div class="p-5 text-center">
                            <p class="text-muted mb-3">
                                Your browser cannot preview PDFs inline.
                            </p>
                            <a href="{{ asset('sample_cv.pdf') }}"
                               target="_blank"
                               class="btn btn-primary">
                                <i class="ki-duotone ki-file-down fs-4 me-1">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                Open the sample PDF
                            </a>
                        </div>
                    </iframe>
                </div>
            </div>

            <div class="modal-footer border-0 d-flex justify-content-between flex-wrap gap-2">
                <div class="text-muted fs-8">
                    You can edit your CV to match this format. Your PDF download will use your saved text.
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ asset('sample_cv.pdf') }}"
                       target="_blank"
                       download
                       class="btn btn-light">
                        <i class="ki-duotone ki-file-down fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Download Sample
                    </a>
                    <button type="button"
                            class="btn btn-primary"
                            data-bs-dismiss="modal">
                        Got it, close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>