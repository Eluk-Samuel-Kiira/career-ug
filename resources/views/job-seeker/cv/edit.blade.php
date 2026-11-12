@extends('layouts.admin')

@section('title', 'My CV')
@section('page_title', 'My CV')

@section('content')
<div class="container py-6">

    <div class="card card-flush shadow-sm">
        {{-- ─── Card Header with Tabs ─── --}}
        <div class="card-header align-items-center py-0">
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold flex-nowrap overflow-auto" id="cvTabs">
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-6 py-5 active d-flex align-items-center" data-bs-toggle="tab" href="#tab_cv_files">
                        <i class="ki-duotone ki-document fs-2 me-2 mb-0">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <span>My CVs</span>
                        <span class="badge badge-light-primary ms-2 cv-count-badge">{{ $totalCount ?? 0 }}/{{ $maxFiles ?? 2 }}</span>
                    </a>
                </li>
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-6 py-5 d-flex align-items-center" data-bs-toggle="tab" href="#tab_academic" id="academicTabLink">
                        <i class="ki-duotone ki-award fs-2 me-2 mb-0">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <span>Documents</span>
                        @if(!empty($profile['academic_documents_path'] ?? null))
                            <span class="bullet bullet-dot bg-success ms-1"></span>
                        @endif
                    </a>
                </li>
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-6 py-5" data-bs-toggle="tab" href="#tab_personal">
                        <i class="ki-duotone ki-profile-circle fs-2 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        Personal Info
                    </a>
                </li>
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-6 py-5" data-bs-toggle="tab" href="#tab_preferences">
                        <i class="ki-duotone ki-briefcase fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                        Job Preferences
                    </a>
                </li>
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-6 py-5" data-bs-toggle="tab" href="#tab_skills">
                        <i class="ki-duotone ki-medal-star fs-2 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                        Skills & Languages
                    </a>
                </li>
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-6 py-5" data-bs-toggle="tab" href="#tab_experience">
                        <i class="ki-duotone ki-briefcase fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                        Experience
                    </a>
                </li>
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-6 py-5" data-bs-toggle="tab" href="#tab_education">
                        <i class="ki-duotone ki-book fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                        Education
                    </a>
                </li>
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-6 py-5" data-bs-toggle="tab" href="#tab_extra">
                        <i class="ki-duotone ki-award fs-2 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        Certifications & Projects
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- ═════════════════════ MY CVs TAB (merged with Upload) ═════════════════════ --}}
                <div class="tab-pane fade show active" id="tab_cv_files">

                    {{-- Hidden file input — triggered by the button --}}
                    <input type="file" id="cvFileInput" accept=".pdf,.doc,.docx" class="d-none" />

                    {{-- Header row: title + Upload button --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-6 gap-3">
                        <div>
                            <h4 class="fw-bold text-gray-800 mb-1">My CVs</h4>
                            <div class="text-muted fs-7">
                                You can keep up to <strong>{{ $maxFiles ?? 2 }}</strong> CVs on your profile.
                            </div>
                        </div>

                        <button type="button"
                                class="btn btn-sm btn-primary"
                                id="uploadCvBtn"
                                {{ ($totalCount ?? 0) >= ($maxFiles ?? 2) ? 'disabled' : '' }}>
                            <i class="ki-duotone ki-plus fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                            Upload CV
                        </button>
                    </div>

                    {{-- Upload progress (shown during upload) --}}
                    <div id="cvUploadProgress" class="d-none mb-5">
                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-5">
                            <span class="spinner-border spinner-border-sm text-primary me-4" role="status"></span>
                            <div>
                                <div class="fw-semibold text-gray-800">Reading your CV and matching it to your profile...</div>
                                <div class="text-muted fs-7 mt-1">This usually takes 20–40 seconds.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Upload result (success / not_a_cv / error) --}}
                    <div id="cvUploadResult" class="mb-5"></div>

                    {{-- File list --}}
                    @php
                        $cvFilesArray = isset($cvFiles) && is_array($cvFiles) ? $cvFiles : [];
                        $totalCount   = count($cvFilesArray);
                    @endphp

                    @if(!empty($cvFilesArray))
                        <div class="d-flex flex-column gap-4" id="cvFilesList">
                            @foreach($cvFilesArray as $index => $file)
                                @php
                                    $filePath    = $file['path'] ?? '';
                                    $downloadUrl = route('cv.download', ['path' => $filePath]);
                                @endphp
                                <div class="d-flex flex-wrap align-items-center justify-content-between p-5 bg-light-primary rounded border border-primary border-dashed cv-file-item" data-index="{{ $index }}">
                                    <div class="d-flex align-items-center gap-4 flex-grow-1 min-w-0">
                                        <div class="symbol symbol-50px">
                                            <div class="symbol-label bg-white text-primary">
                                                <i class="ki-duotone ki-file fs-2x"><span class="path1"></span><span class="path2"></span></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="fw-bold fs-6 text-gray-800 text-truncate">
                                                {{ $file['original_name'] ?? 'CV File' }}
                                            </div>
                                            <div class="d-flex flex-wrap gap-3 mt-1">
                                                <span class="text-muted fs-8">
                                                    <i class="ki-duotone ki-weight fs-7 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                    {{ isset($file['size']) ? round($file['size'] / 1024, 2) . ' KB' : 'Unknown size' }}
                                                </span>
                                                <span class="text-muted fs-8">
                                                    <i class="ki-duotone ki-calendar fs-7 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                    {{ isset($file['uploaded_at']) ? \Carbon\Carbon::parse($file['uploaded_at'])->format('M d, Y H:i') : 'Unknown date' }}
                                                </span>
                                                <span class="badge badge-light-success fs-8">CV #{{ $index + 1 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3 mt-sm-0 flex-shrink-0">
                                        <a href="{{ $downloadUrl }}&mode=view" target="_blank"
                                           class="btn btn-sm btn-icon btn-light-primary"
                                           title="View CV" data-bs-toggle="tooltip">
                                            <i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        </a>
                                        <a href="{{ $downloadUrl }}"
                                           class="btn btn-sm btn-icon btn-light-success"
                                           title="Download CV" data-bs-toggle="tooltip">
                                            <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger delete-cv-btn"
                                                data-path="{{ $filePath }}"
                                                data-name="{{ $file['original_name'] ?? 'CV' }}"
                                                title="Delete CV" data-bs-toggle="tooltip">
                                            <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-muted fs-8 mt-3 text-end">
                            Showing {{ count($cvFilesArray) }} of {{ $maxFiles ?? 2 }} allowed
                        </div>
                    @else
                        <div class="text-center py-15">
                            <div class="symbol symbol-100px symbol-circle mb-4">
                                <div class="symbol-label bg-light-secondary">
                                    <i class="ki-duotone ki-file fs-3x text-gray-400"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                            </div>
                            <h4 class="fw-bold text-gray-800 mb-2">No CVs yet</h4>
                            <p class="text-muted mb-5">Upload your first CV to get started.</p>
                            <button type="button" class="btn btn-primary" id="uploadFirstCvBtn">
                                <i class="ki-duotone ki-plus fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Upload CV
                            </button>
                        </div>
                    @endif

                    @if(($totalCount ?? 0) >= ($maxFiles ?? 2))
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 mt-5">
                            <i class="ki-duotone ki-information-5 fs-2tx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <div class="fs-7 text-gray-700">
                                        You have reached the maximum of <strong>{{ $maxFiles ?? 2 }}</strong> CV files. Please delete one before uploading another.
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ═════════════════════ ACADEMIC DOCUMENTS TAB ═════════════════════ --}}
                <div class="tab-pane fade" id="tab_academic">
                    <div class="mb-7">
                        <h4 class="fw-bold text-gray-800 mb-1">Academic Documents</h4>
                        <div class="text-muted fs-7">
                            All your certificates, IDs, and appointment letters in one PDF bundle.
                        </div>
                    </div>

                    {{-- Instructions notice --}}
                    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-5 mb-6">
                        <i class="ki-duotone ki-information-5 fs-2tx text-warning me-4">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h5 class="mb-2 text-gray-800">Merge everything into one PDF</h5>
                                <div class="fs-7 text-gray-700">
                                    Combine <strong>all</strong> of the following into a single PDF file before uploading:
                                    <ul class="mt-2 mb-0 ps-4">
                                        <li>All academic certificates (UCE, UACE, Diploma, Degree)</li>
                                        <li>Professional certificates and licences</li>
                                        <li>National ID (front and back)</li>
                                        <li>Previous appointment letters</li>
                                        <li>Any other supporting documents</li>
                                    </ul>
                                    <div class="mt-2">
                                        Maximum size: <strong>10 MB</strong>. Only <strong>one PDF</strong> per candidate — uploading a new file replaces the old one.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden file input --}}
                    <input type="file" id="academicDocsInput" accept=".pdf" class="d-none" />

                    {{-- Upload progress --}}
                    <div id="academicDocsProgress" class="d-none mb-5">
                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-5">
                            <span class="spinner-border spinner-border-sm text-primary me-4" role="status"></span>
                            <div>
                                <div class="fw-semibold text-gray-800">Uploading your documents...</div>
                                <div class="text-muted fs-7 mt-1">This may take a few seconds for larger files.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Upload result --}}
                    <div id="academicDocsResult" class="mb-5"></div>

                    @php
                        $academicDocs = $profile['academic_documents_path'] ?? null
                            ? [
                                'path' => $profile['academic_documents_path'],
                                'original_name' => $profile['academic_documents_original_name'] ?? 'Academic Documents.pdf',
                                'size' => $profile['academic_documents_size'] ?? null,
                                'uploaded_at' => $profile['academic_documents_uploaded_at'] ?? null,
                            ]
                            : null;
                    @endphp

                    @if($academicDocs)
                        {{-- Current file --}}
                        <div class="d-flex flex-wrap align-items-center justify-content-between p-5 bg-light-success rounded border border-success border-dashed">
                            <div class="d-flex align-items-center gap-4 flex-grow-1 min-w-0">
                                <div class="symbol symbol-50px">
                                    <div class="symbol-label bg-white text-success">
                                        <i class="ki-duotone ki-diploma fs-2x">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-bold fs-6 text-gray-800 text-truncate">
                                        {{ $academicDocs['original_name'] }}
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 mt-1">
                                        @if($academicDocs['size'])
                                            <span class="text-muted fs-8">
                                                <i class="ki-duotone ki-weight fs-7 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                {{ round($academicDocs['size'] / 1024 / 1024, 2) }} MB
                                            </span>
                                        @endif
                                        @if($academicDocs['uploaded_at'])
                                            <span class="text-muted fs-8">
                                                <i class="ki-duotone ki-calendar fs-7 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                {{ \Carbon\Carbon::parse($academicDocs['uploaded_at'])->format('M d, Y H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3 mt-sm-0 flex-shrink-0">
                                <a href="{{ route('cv.academic.download', ['path' => $academicDocs['path']]) }}&mode=view"
                                target="_blank" class="btn btn-sm btn-icon btn-light-primary"
                                title="View document" data-bs-toggle="tooltip">
                                    <i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </a>
                                <a href="{{ route('cv.academic.download', ['path' => $academicDocs['path']]) }}"
                                class="btn btn-sm btn-icon btn-light-success" title="Download" data-bs-toggle="tooltip">
                                    <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-icon btn-light-danger delete-academic-btn"
                                        title="Delete document" data-bs-toggle="tooltip">
                                    <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-sm btn-light-primary" id="replaceAcademicBtn">
                                <i class="ki-duotone ki-arrows-circle fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Replace with new PDF
                            </button>
                        </div>
                    @else
                        {{-- Empty state --}}
                        <div class="text-center py-15">
                            <div class="symbol symbol-100px symbol-circle mb-4">
                                <div class="symbol-label bg-light-secondary">
                                    <i class="ki-duotone ki-diploma fs-3x text-gray-400">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                    </i>
                                </div>
                            </div>
                            <h4 class="fw-bold text-gray-800 mb-2">No academic documents yet</h4>
                            <p class="text-muted mb-5">
                                Upload one PDF containing all your certificates, IDs, and appointment letters.
                            </p>
                            <button type="button" class="btn btn-primary" id="uploadAcademicBtn">
                                <i class="ki-duotone ki-folder-up fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Upload Academic Documents
                            </button>
                        </div>
                    @endif
                </div>

                {{-- ═════════════════════ PERSONAL INFO TAB ═════════════════════ --}}
                <div class="tab-pane fade" id="tab_personal">
                    <div class="mb-7">
                        <h4 class="fw-bold text-gray-800 mb-1">Personal Information</h4>
                        <div class="text-muted fs-7">Your basic details and professional summary.</div>
                    </div>

                    <div class="row g-6">
                        <div class="col-md-6">
                            <label class="required fw-semibold fs-6 mb-2">First Name</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_first_name" />
                        </div>
                        <div class="col-md-6">
                            <label class="required fw-semibold fs-6 mb-2">Last Name</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_last_name" />
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold fs-6 mb-2">Phone</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_phone" />
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold fs-6 mb-2">Professional Title</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_professional_title" placeholder="e.g. Senior Accountant" />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold fs-6 mb-2">City</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_city" />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold fs-6 mb-2">Country</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_country" />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold fs-6 mb-2">Postal Code</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_postal_code" />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold fs-6 mb-2">Date of Birth</label>
                            <input type="date" class="form-control form-control-solid form-control-lg" id="f_date_of_birth" />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold fs-6 mb-2">Nationality</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_nationality" />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold fs-6 mb-2">Years of Experience</label>
                            <input type="number" min="0" class="form-control form-control-solid form-control-lg" id="f_years_of_experience" />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold fs-6 mb-2">LinkedIn</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_linkedin_url" placeholder="linkedin.com/in/..." />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold fs-6 mb-2">GitHub</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_github_url" placeholder="github.com/..." />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold fs-6 mb-2">Portfolio</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_portfolio_url" placeholder="https://..." />
                        </div>
                        <div class="col-12">
                            <label class="fw-semibold fs-6 mb-2">Professional Summary</label>
                            <textarea class="form-control form-control-solid form-control-lg" rows="4" id="f_professional_summary" placeholder="2–4 sentences describing your experience, strengths, and what you're looking for."></textarea>
                        </div>
                    </div>
                </div>

                {{-- ═════════════════════ JOB PREFERENCES TAB ═════════════════════ --}}
                <div class="tab-pane fade" id="tab_preferences">
                    <div class="mb-7">
                        <h4 class="fw-bold text-gray-800 mb-1">Job Preferences</h4>
                        <div class="text-muted fs-7">
                            These fields drive employer matching and job recommendations. Keep them up to date so relevant roles reach you first.
                        </div>
                    </div>

                    <div class="row g-6">
                        <div class="col-md-6">
                            <label class="fw-semibold fs-6 mb-2">Job Category</label>
                            <select id="f_job_category_id" class="form-select form-select-solid form-select-lg">
                                <option value="">Select category</option>
                                @foreach($filters['categories'] ?? [] as $c)
                                    <option value="{{ $c['id'] }}">{{ $c['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold fs-6 mb-2">Industry</label>
                            <select id="f_industry_id" class="form-select form-select-solid form-select-lg">
                                <option value="">Select industry</option>
                                @foreach($filters['industries'] ?? [] as $i)
                                    <option value="{{ $i['id'] }}">{{ $i['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold fs-6 mb-2">Preferred Job Type</label>
                            <select id="f_job_type_id" class="form-select form-select-solid form-select-lg">
                                <option value="">Select job type</option>
                                @foreach($filters['job_types'] ?? [] as $t)
                                    <option value="{{ $t['id'] }}">{{ $t['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold fs-6 mb-2">Preferred Location</label>
                            <select id="f_job_location_id" class="form-select form-select-solid form-select-lg">
                                <option value="">Select location</option>
                                @foreach($filters['locations'] ?? [] as $l)
                                    <option value="{{ $l['id'] }}">{{ $l['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold fs-6 mb-2">Experience Level</label>
                            <select id="f_experience_level_id" class="form-select form-select-solid form-select-lg">
                                <option value="">Select experience level</option>
                                @foreach($filters['experience_levels'] ?? [] as $e)
                                    <option value="{{ $e['id'] }}">{{ $e['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold fs-6 mb-2">Education Level</label>
                            <select id="f_education_level_id" class="form-select form-select-solid form-select-lg">
                                <option value="">Select education level</option>
                                @foreach($filters['education_levels'] ?? [] as $e)
                                    <option value="{{ $e['id'] }}">{{ $e['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="fw-semibold fs-6 mb-2">Salary Expectation</label>
                            <select id="f_salary_range_id" class="form-select form-select-solid form-select-lg">
                                <option value="">Select salary range</option>
                                @foreach($filters['salary_ranges'] ?? [] as $s)
                                    <option value="{{ $s['id'] }}">{{ $s['label'] }}</option>
                                @endforeach
                            </select>
                            <div class="text-muted fs-8 mt-2">Helps employers match salary expectations before they reach out.</div>
                        </div>
                    </div>

                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-5 mt-8">
                        <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h5 class="mb-1">Why this matters</h5>
                                <div class="fs-7 text-gray-700">
                                    Complete preferences make your profile eligible for employer filters and are required before applying to jobs.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═════════════════════ SKILLS & LANGUAGES TAB ═════════════════════ --}}
                <div class="tab-pane fade" id="tab_skills">
                    <div class="mb-7">
                        <h4 class="fw-bold text-gray-800 mb-1">Skills & Languages</h4>
                        <div class="text-muted fs-7">Separate items with commas.</div>
                    </div>

                    <div class="row g-6">
                        <div class="col-12">
                            <label class="fw-semibold fs-6 mb-2">Skills</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_skills"
                                   placeholder="e.g. PHP, Laravel, JavaScript, Communication" />
                            <div class="text-muted fs-8 mt-2">Used to match you with relevant jobs.</div>
                        </div>
                        <div class="col-12">
                            <label class="fw-semibold fs-6 mb-2">Languages</label>
                            <input type="text" class="form-control form-control-solid form-control-lg" id="f_languages"
                                   placeholder="e.g. English, French, Swahili" />
                        </div>
                    </div>
                </div>

                {{-- ═════════════════════ EXPERIENCE TAB ═════════════════════ --}}
                <div class="tab-pane fade" id="tab_experience">
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <div>
                            <h4 class="fw-bold text-gray-800 mb-1">Work Experience</h4>
                            <div class="text-muted fs-7">Add your roles from most recent to oldest.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light-primary" id="addExperienceBtn">
                            <i class="ki-duotone ki-plus fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                            Add Experience
                        </button>
                    </div>
                    <div id="experienceList" class="d-flex flex-column gap-4"></div>
                </div>

                {{-- ═════════════════════ EDUCATION TAB ═════════════════════ --}}
                <div class="tab-pane fade" id="tab_education">
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <div>
                            <h4 class="fw-bold text-gray-800 mb-1">Education</h4>
                            <div class="text-muted fs-7">Add your qualifications from most recent to oldest.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light-primary" id="addEducationBtn">
                            <i class="ki-duotone ki-plus fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                            Add Education
                        </button>
                    </div>
                    <div id="educationList" class="d-flex flex-column gap-4"></div>
                </div>

                {{-- ═════════════════════ CERTIFICATIONS & PROJECTS TAB ═════════════════════ --}}
                <div class="tab-pane fade" id="tab_extra">
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <div>
                            <h4 class="fw-bold text-gray-800 mb-1">Certifications</h4>
                            <div class="text-muted fs-7">Professional certificates, courses, and credentials.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light-primary" id="addCertificationBtn">
                            <i class="ki-duotone ki-plus fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                            Add
                        </button>
                    </div>
                    <div id="certificationsList" class="d-flex flex-column gap-4 mb-8"></div>

                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <div>
                            <h4 class="fw-bold text-gray-800 mb-1">Projects</h4>
                            <div class="text-muted fs-7">Personal or professional projects you're proud of.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light-primary" id="addProjectBtn">
                            <i class="ki-duotone ki-plus fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                            Add
                        </button>
                    </div>
                    <div id="projectsList" class="d-flex flex-column gap-4"></div>
                </div>

            </div>

            {{-- ═════════════════════ SAVE BAR ═════════════════════ --}}
            <div class="d-flex justify-content-end mt-10 pt-7 border-top">
                <button type="button" class="btn btn-primary btn-lg" id="saveCvBtn">
                    <i class="ki-duotone ki-check-circle fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                    Save Profile
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #cvTabs {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    #cvTabs::-webkit-scrollbar { height: 4px; }
    #cvTabs::-webkit-scrollbar-thumb { background: rgba(0,0,0,.15); border-radius: 4px; }
    #cvTabs .nav-item { flex-shrink: 0; }

    @media (max-width: 767.98px) {
        #cvTabs .nav-link { font-size: 0.9rem; padding: 14px 0 !important; }
        #cvTabs .nav-link i { display: none; }
        .card-body { padding: 1rem !important; }
        #saveCvBtn { width: 100%; }
        .cv-file-item { flex-direction: column; align-items: stretch !important; }
        .cv-file-item > div:last-child { justify-content: flex-end; padding-top: .75rem; border-top: 1px solid var(--bs-gray-200); }
        .cv-file-item .btn { min-height: 44px; min-width: 44px; }
        .row.g-6 > [class*="col-"] { margin-bottom: .25rem; }
    }
    @media (max-width: 575.98px) {
        #saveCvBtn {
            position: sticky;
            bottom: 12px;
            z-index: 40;
            box-shadow: 0 8px 20px rgba(11,28,46,.25);
        }
    }
</style>

<style>
    /* Repeater field contrast inside light cards */
    .exp-row .form-control-solid,
    .edu-row .form-control-solid,
    .cert-row .form-control-solid,
    .proj-row .form-control-solid {
        background-color: #ffffff !important;
        border: 1px solid #E4E6EF !important;
        color: #0F1B2D !important;
    }
    .exp-row .form-control-solid:focus,
    .edu-row .form-control-solid:focus,
    .cert-row .form-control-solid:focus,
    .proj-row .form-control-solid:focus {
        background-color: #ffffff !important;
        border-color: #009EF7 !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.12) !important;
    }
    .exp-row .form-control-solid::placeholder,
    .edu-row .form-control-solid::placeholder,
    .cert-row .form-control-solid::placeholder,
    .proj-row .form-control-solid::placeholder {
        color: #A1A5B7 !important;
        opacity: 1;
    }
    .exp-row label.fs-8,
    .edu-row label.fs-8,
    .cert-row label.fs-8,
    .proj-row label.fs-8 {
        color: #5E6278 !important;
    }
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ─────────────────────────────────────────────────────────────
    // SHARED: CSRF token + initial profile data
    // ─────────────────────────────────────────────────────────────
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrf) {
        console.error('CSRF meta tag not found. All POST/DELETE requests will fail with 419.');
    }

    const initialProfile = @json($profile ?? []);
    const MAX_FILES = {{ $maxFiles ?? 2 }};

    // ─────────────────────────────────────────────────────────────
    // 1. CV upload (My CVs tab)
    // ─────────────────────────────────────────────────────────────
    function updateCvCount(count, max) {
        max = max || MAX_FILES;
        count = count || 0;
        document.querySelectorAll('.cv-count-badge').forEach(b => b.textContent = count + '/' + max);
        const uploadBtn = document.getElementById('uploadCvBtn');
        if (uploadBtn) {
            uploadBtn.disabled = count >= max;
            uploadBtn.style.display = count >= max ? 'none' : 'inline-flex';
        }
    }

    function experienceRow(data = {}) {
        const el = document.createElement('div');
        el.className = 'card bg-light rounded border border-gray-200 p-5 exp-row';
        el.innerHTML = `
            <div class="row g-4">
                <div class="col-md-6"><label class="fs-8 fw-semibold text-gray-600 mb-1">Company</label>
                    <input class="form-control form-control-solid" placeholder="e.g. Acme Ltd" data-field="company" value="${data.company ?? ''}"></div>
                <div class="col-md-6"><label class="fs-8 fw-semibold text-gray-600 mb-1">Job Title</label>
                    <input class="form-control form-control-solid" placeholder="e.g. Senior Accountant" data-field="title" value="${data.title ?? ''}"></div>
                <div class="col-md-3"><label class="fs-8 fw-semibold text-gray-600 mb-1">Start</label>
                    <input class="form-control form-control-solid" placeholder="Jan 2022" data-field="start_date" value="${data.start_date ?? ''}"></div>
                <div class="col-md-3"><label class="fs-8 fw-semibold text-gray-600 mb-1">End</label>
                    <input class="form-control form-control-solid" placeholder="Present" data-field="end_date" value="${data.end_date ?? ''}"></div>
                <div class="col-md-6"><label class="fs-8 fw-semibold text-gray-600 mb-1">Description</label>
                    <input class="form-control form-control-solid" placeholder="Main responsibilities" data-field="description" value="${data.description ?? ''}"></div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-sm btn-light-danger remove-row">
                    <i class="ki-duotone ki-trash fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    Remove
                </button>
            </div>`;
        el.querySelector('.remove-row').onclick = () => el.remove();
        return el;
    }

    function educationRow(data = {}) {
        const el = document.createElement('div');
        el.className = 'card bg-light rounded border border-gray-200 p-5 edu-row';
        el.innerHTML = `
            <div class="row g-4">
                <div class="col-md-6"><label class="fs-8 fw-semibold text-gray-600 mb-1">Institution</label>
                    <input class="form-control form-control-solid" data-field="institution" value="${data.institution ?? ''}"></div>
                <div class="col-md-6"><label class="fs-8 fw-semibold text-gray-600 mb-1">Degree</label>
                    <input class="form-control form-control-solid" data-field="degree" value="${data.degree ?? ''}"></div>
                <div class="col-md-4"><label class="fs-8 fw-semibold text-gray-600 mb-1">Field</label>
                    <input class="form-control form-control-solid" data-field="field" value="${data.field ?? ''}"></div>
                <div class="col-md-4"><label class="fs-8 fw-semibold text-gray-600 mb-1">Grade</label>
                    <input class="form-control form-control-solid" data-field="grade" value="${data.grade ?? ''}"></div>
                <div class="col-md-2"><label class="fs-8 fw-semibold text-gray-600 mb-1">Start</label>
                    <input class="form-control form-control-solid" data-field="start_year" value="${data.start_year ?? ''}"></div>
                <div class="col-md-2"><label class="fs-8 fw-semibold text-gray-600 mb-1">End</label>
                    <input class="form-control form-control-solid" data-field="end_year" value="${data.end_year ?? ''}"></div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-sm btn-light-danger remove-row">
                    <i class="ki-duotone ki-trash fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    Remove
                </button>
            </div>`;
        el.querySelector('.remove-row').onclick = () => el.remove();
        return el;
    }

    function certificationRow(data = {}) {
        const el = document.createElement('div');
        el.className = 'card bg-light rounded border border-gray-200 p-5 cert-row';
        el.innerHTML = `
            <div class="row g-4">
                <div class="col-md-5"><label class="fs-8 fw-semibold text-gray-600 mb-1">Certification Name</label>
                    <input class="form-control form-control-solid" data-field="name" value="${data.name ?? ''}"></div>
                <div class="col-md-4"><label class="fs-8 fw-semibold text-gray-600 mb-1">Issuer</label>
                    <input class="form-control form-control-solid" data-field="issuer" value="${data.issuer ?? ''}"></div>
                <div class="col-md-3"><label class="fs-8 fw-semibold text-gray-600 mb-1">Year</label>
                    <input class="form-control form-control-solid" data-field="year" value="${data.year ?? ''}"></div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-sm btn-light-danger remove-row">
                    <i class="ki-duotone ki-trash fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    Remove
                </button>
            </div>`;
        el.querySelector('.remove-row').onclick = () => el.remove();
        return el;
    }

    function projectRow(data = {}) {
        const el = document.createElement('div');
        el.className = 'card bg-light rounded border border-gray-200 p-5 proj-row';
        el.innerHTML = `
            <div class="row g-4">
                <div class="col-md-4"><label class="fs-8 fw-semibold text-gray-600 mb-1">Name</label>
                    <input class="form-control form-control-solid" data-field="name" value="${data.name ?? ''}"></div>
                <div class="col-md-5"><label class="fs-8 fw-semibold text-gray-600 mb-1">Description</label>
                    <input class="form-control form-control-solid" data-field="description" value="${data.description ?? ''}"></div>
                <div class="col-md-3"><label class="fs-8 fw-semibold text-gray-600 mb-1">URL</label>
                    <input class="form-control form-control-solid" data-field="url" value="${data.url ?? ''}"></div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-sm btn-light-danger remove-row">
                    <i class="ki-duotone ki-trash fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    Remove
                </button>
            </div>`;
        el.querySelector('.remove-row').onclick = () => el.remove();
        return el;
    }

    document.getElementById('addExperienceBtn')?.addEventListener('click', () =>
        document.getElementById('experienceList').appendChild(experienceRow())
    );
    document.getElementById('addEducationBtn')?.addEventListener('click', () =>
        document.getElementById('educationList').appendChild(educationRow())
    );
    document.getElementById('addCertificationBtn')?.addEventListener('click', () =>
        document.getElementById('certificationsList').appendChild(certificationRow())
    );
    document.getElementById('addProjectBtn')?.addEventListener('click', () =>
        document.getElementById('projectsList').appendChild(projectRow())
    );

    function populateForm(profile) {
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val ?? '';
        };

        ['first_name','last_name','phone','professional_title','city','country',
         'postal_code','date_of_birth','nationality','years_of_experience',
         'linkedin_url','github_url','portfolio_url','professional_summary']
            .forEach(k => setVal('f_' + k, profile[k]));

        setVal('f_skills', Array.isArray(profile.skills) ? profile.skills.join(', ') : (profile.skills ?? ''));
        setVal('f_languages', Array.isArray(profile.languages) ? profile.languages.join(', ') : (profile.languages ?? ''));

        ['job_category_id','industry_id','job_type_id','job_location_id',
         'experience_level_id','education_level_id','salary_range_id']
            .forEach(k => setVal('f_' + k, profile[k]));

        const fill = (id, items, renderer) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.innerHTML = '';
            (items || []).forEach(it => el.appendChild(renderer(it)));
        };

        fill('experienceList',      profile.work_experience, experienceRow);
        fill('educationList',       profile.education,       educationRow);
        fill('certificationsList',  profile.certifications,  certificationRow);
        fill('projectsList',        profile.projects,        projectRow);
    }

    if (initialProfile) populateForm(initialProfile);

    // ── CV upload
    const fileInput   = document.getElementById('cvFileInput');
    const progressDiv = document.getElementById('cvUploadProgress');
    const resultDiv   = document.getElementById('cvUploadResult');

    const triggerCvUpload = () => {
        if (fileInput) { fileInput.value = ''; fileInput.click(); }
    };

    document.getElementById('uploadCvBtn')?.addEventListener('click', triggerCvUpload);
    document.getElementById('uploadFirstCvBtn')?.addEventListener('click', triggerCvUpload);

    fileInput?.addEventListener('change', function () {
        if (!this.files.length) return;
        const file = this.files[0];

        if (progressDiv) progressDiv.classList.remove('d-none');
        if (resultDiv) resultDiv.innerHTML = '';

        const fd = new FormData();
        fd.append('cv', file);
        fd.append('_token', csrf);  // ← CSRF

        fetch('{{ route('cv.upload') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd,
        })
        .then(async (r) => {
            let data;
            try { data = await r.json(); }
            catch { throw new Error('The server returned an unexpected response. Please try again.'); }
            return { ok: r.ok, status: r.status, data };
        })
        .then(({ ok, status, data }) => {
            if (progressDiv) progressDiv.classList.add('d-none');

            if (data.success) {
                if (data.profile) populateForm(data.profile);
                if (resultDiv) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success d-flex align-items-start gap-3">
                            <i class="ki-duotone ki-check-circle fs-2x text-success flex-shrink-0"><span class="path1"></span><span class="path2"></span></i>
                            <div>
                                <div class="fw-bold mb-1">CV processed!</div>
                                <div class="fs-7">Review the tabs above, then click Save Profile.</div>
                            </div>
                        </div>`;
                }
                window.showToast?.('success', data.message || 'CV processed successfully!');

                if (data.total_count !== undefined || data.cv_files) {
                    const totalCount = data.total_count || (data.cv_files ? data.cv_files.length : 0);
                    updateCvCount(totalCount, data.max_files || MAX_FILES);
                }
                setTimeout(() => {
                    const tabLink = document.querySelector('a[href="#tab_personal"]');
                    if (tabLink) new bootstrap.Tab(tabLink).show();
                }, 600);
                return;
            }

            if (data.code === 'not_a_cv') {
                if (resultDiv) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger d-flex align-items-start gap-3">
                            <i class="ki-duotone ki-information-5 fs-2x text-danger flex-shrink-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div>
                                <div class="fw-bold mb-1">We couldn't read this as a CV</div>
                                <div class="fs-7">${data.message || 'Please upload your CV as a PDF or Word document.'}</div>
                            </div>
                        </div>`;
                }
                window.showToast?.('error', data.message || 'This file is not a CV.');
                return;
            }

            const msg = data.message || `Upload failed (HTTP ${status}).`;
            if (resultDiv) resultDiv.innerHTML = `<div class="alert alert-danger">${msg}</div>`;
            window.showToast?.('error', msg);
        })
        .catch((err) => {
            if (progressDiv) progressDiv.classList.add('d-none');
            const msg = err.message || 'Something went wrong uploading your CV.';
            if (resultDiv) resultDiv.innerHTML = `<div class="alert alert-danger">${msg}</div>`;
            window.showToast?.('error', msg);
            console.error('CV upload error:', err);
        });
    });

    // ── Save profile
    function collectRows(id) {
        const c = document.getElementById(id);
        if (!c) return [];
        return Array.from(c.children).map(row => {
            const o = {};
            row.querySelectorAll('[data-field]').forEach(i => o[i.dataset.field] = i.value);
            return o;
        });
    }

    document.getElementById('saveCvBtn')?.addEventListener('click', function () {
        const btn = this;
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        const v = id => document.getElementById(id)?.value || '';
        const sel = id => {
            const el = document.getElementById(id);
            return el?.value === '' || el?.value == null ? null : el.value;
        };

        const payload = {
            first_name: v('f_first_name'),
            last_name: v('f_last_name'),
            phone: v('f_phone'),
            professional_title: v('f_professional_title'),
            city: v('f_city'),
            country: v('f_country'),
            postal_code: v('f_postal_code'),
            date_of_birth: v('f_date_of_birth'),
            nationality: v('f_nationality'),
            years_of_experience: v('f_years_of_experience'),
            linkedin_url: v('f_linkedin_url'),
            github_url: v('f_github_url'),
            portfolio_url: v('f_portfolio_url'),
            professional_summary: v('f_professional_summary'),
            skills: v('f_skills'),
            languages: v('f_languages') ? v('f_languages').split(',').map(s => s.trim()).filter(Boolean) : [],
            work_experience: collectRows('experienceList'),
            education: collectRows('educationList'),
            certifications: collectRows('certificationsList'),
            projects: collectRows('projectsList'),
            job_category_id:     sel('f_job_category_id'),
            industry_id:         sel('f_industry_id'),
            job_type_id:         sel('f_job_type_id'),
            job_location_id:     sel('f_job_location_id'),
            experience_level_id: sel('f_experience_level_id'),
            education_level_id:  sel('f_education_level_id'),
            salary_range_id:     sel('f_salary_range_id'),
        };

        fetch('{{ route('profile.update') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = original;
            if (data.success) {
                window.showToast?.('success', data.message || 'Profile saved successfully!');
            } else {
                window.showToast?.('error', data.message || 'Failed to save profile.');
                if (data.errors) {
                    window.showToast?.('error', Object.values(data.errors).flat().join('\n'));
                }
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = original;
            window.showToast?.('error', 'Something went wrong saving your profile.');
        });
    });

    // ── Delete CV
    document.querySelectorAll('.delete-cv-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const path = this.dataset.path;
            const name = this.dataset.name;

            if (!path) return window.showToast?.('error', 'File path not found.');
            if (!confirm(`Delete "${name}"?`)) return;

            const el = this;
            const original = el.innerHTML;
            el.disabled = true;
            el.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch('{{ route('cv.delete') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ file_path: path }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.showToast?.('success', data.message || 'CV deleted.');
                    updateCvCount(data.total_count ?? 0, data.max_files || MAX_FILES);
                    setTimeout(() => location.reload(), 400);
                } else {
                    window.showToast?.('error', data.message || 'Failed to delete.');
                    el.disabled = false;
                    el.innerHTML = original;
                }
            })
            .catch(() => {
                window.showToast?.('error', 'Failed to delete CV.');
                el.disabled = false;
                el.innerHTML = original;
            });
        });
    });

    // ─────────────────────────────────────────────────────────────
    // 2. ACADEMIC DOCUMENTS (inside same DOMContentLoaded — csrf is now in scope)
    // ─────────────────────────────────────────────────────────────
    const acadInput    = document.getElementById('academicDocsInput');
    const acadProgress = document.getElementById('academicDocsProgress');
    const acadResult   = document.getElementById('academicDocsResult');

    // Auto-select Academic tab if ?tab=academic
    if (new URLSearchParams(window.location.search).get('tab') === 'academic') {
        const tabLink = document.getElementById('academicTabLink');
        if (tabLink) new bootstrap.Tab(tabLink).show();
    }

    const triggerAcademicUpload = () => {
        if (acadInput) { acadInput.value = ''; acadInput.click(); }
    };

    document.getElementById('uploadAcademicBtn')?.addEventListener('click', triggerAcademicUpload);
    document.getElementById('replaceAcademicBtn')?.addEventListener('click', triggerAcademicUpload);

    acadInput?.addEventListener('change', function () {
        if (!this.files.length) return;
        const file = this.files[0];

        if (file.type !== 'application/pdf') {
            window.showToast?.('error', 'Please upload a PDF file.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            window.showToast?.('error', 'The PDF must not exceed 10 MB.');
            return;
        }

        acadProgress?.classList.remove('d-none');
        if (acadResult) acadResult.innerHTML = '';

        const fd = new FormData();
        fd.append('document', file);
        fd.append('_token', csrf);  // ← CSRF in body as a fallback

        fetch('{{ route('cv.academic.upload') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd,
        })
        .then(async r => {
            if (r.status === 419) {
                throw new Error('Session expired. Please refresh the page and try again.');
            }
            if (r.status === 422) {
                const data = await r.json().catch(() => ({}));
                throw new Error(data.message || 'The file failed validation.');
            }
            let data;
            try { data = await r.json(); }
            catch { throw new Error('Unexpected server response.'); }
            return { ok: r.ok, status: r.status, data };
        })
        .then(({ ok, status, data }) => {
            acadProgress?.classList.add('d-none');

            if (data.success) {
                if (acadResult) {
                    acadResult.innerHTML = `
                        <div class="alert alert-success d-flex align-items-start gap-3">
                            <i class="ki-duotone ki-check-circle fs-2x text-success flex-shrink-0"><span class="path1"></span><span class="path2"></span></i>
                            <div>
                                <div class="fw-bold mb-1">Academic documents uploaded</div>
                                <div class="fs-7">${data.message || 'Your bundle is now on file.'}</div>
                            </div>
                        </div>`;
                }
                window.showToast?.('success', data.message || 'Uploaded successfully.');
                setTimeout(() => window.location.reload(), 900);
                return;
            }

            const msg = data.message || `Upload failed (HTTP ${status}).`;
            if (acadResult) acadResult.innerHTML = `<div class="alert alert-danger">${msg}</div>`;
            window.showToast?.('error', msg);
        })
        .catch(err => {
            acadProgress?.classList.add('d-none');
            const msg = err.message || 'Something went wrong uploading.';
            if (acadResult) acadResult.innerHTML = `<div class="alert alert-danger">${msg}</div>`;
            window.showToast?.('error', msg);
            console.error('Academic upload error:', err);
        });
    });

    // Delete academic documents
    document.querySelector('.delete-academic-btn')?.addEventListener('click', async function () {
        if (!confirm('Delete your academic documents? You will need to upload them again later.')) return;

        const el = this;
        const original = el.innerHTML;
        el.disabled = true;
        el.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const res = await fetch('{{ route('cv.academic.destroy') }}', {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            });
            if (res.status === 419) {
                throw new Error('Session expired. Please refresh the page.');
            }
            const data = await res.json();
            if (data.success) {
                window.showToast?.('success', data.message || 'Deleted.');
                setTimeout(() => window.location.reload(), 500);
            } else {
                window.showToast?.('error', data.message || 'Failed to delete.');
                el.disabled = false;
                el.innerHTML = original;
            }
        } catch (err) {
            window.showToast?.('error', err.message || 'Network error.');
            el.disabled = false;
            el.innerHTML = original;
        }
    });

    // ─────────────────────────────────────────────────────────────
    // Boot
    // ─────────────────────────────────────────────────────────────
    updateCvCount({{ $totalCount ?? 0 }}, {{ $maxFiles ?? 2 }});

    if (typeof bootstrap !== 'undefined') {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    }
});
</script>
@endpush