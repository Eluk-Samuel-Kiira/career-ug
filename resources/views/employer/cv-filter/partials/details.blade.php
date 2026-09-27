@php
    $skills = is_array($seeker['skills'] ?? null) ? $seeker['skills'] : [];
    $languages = is_array($seeker['languages'] ?? null) ? $seeker['languages'] : [];
    $cvFiles = is_array($seeker['cv_files'] ?? null) ? $seeker['cv_files'] : [];
@endphp

<div class="d-flex align-items-center mb-5">
    <div class="symbol symbol-80px symbol-circle me-4">
        <img src="{{ $seeker['avatar'] ?? asset('assets/media/avatars/blank.png') }}" alt="Avatar" />
    </div>
    <div>
        <h4 class="fw-bold mb-1">{{ $seeker['full_name'] ?? 'Unknown' }}</h4>
        @if($seeker['professional_title'] ?? null)
            <div class="text-muted">{{ $seeker['professional_title'] }}</div>
        @endif
        <div class="d-flex flex-wrap gap-2 mt-2">
            @if($seeker['country'] ?? null)
                <span class="badge badge-light-info">{{ $seeker['flag'] ?? '' }} {{ $seeker['country'] }}</span>
            @endif
            @if($seeker['city'] ?? null)
                <span class="badge badge-light-secondary">{{ $seeker['city'] }}</span>
            @endif
            <span class="badge badge-light-{{ $seeker['profile_complete'] ? 'success' : 'warning' }}">
                {{ $seeker['profile_complete'] ? 'Complete Profile' : 'Incomplete Profile' }}
            </span>
        </div>
    </div>
</div>

<div class="separator my-4"></div>

<div class="row g-5">
    <div class="col-md-6">
        <div class="fw-bold text-muted fs-7">Email</div>
        <div class="fw-semibold">{{ $seeker['email'] ?? 'N/A' }}</div>
    </div>
    <div class="col-md-6">
        <div class="fw-bold text-muted fs-7">Phone</div>
        <div class="fw-semibold">{{ $seeker['phone'] ?? 'N/A' }}</div>
    </div>

    <div class="col-md-6">
        <div class="fw-bold text-muted fs-7">Job Category</div>
        <div class="fw-semibold">{{ $seeker['job_category'] ?? 'N/A' }}</div>
    </div>
    <div class="col-md-6">
        <div class="fw-bold text-muted fs-7">Industry</div>
        <div class="fw-semibold">{{ $seeker['industry'] ?? 'N/A' }}</div>
    </div>

    <div class="col-md-6">
        <div class="fw-bold text-muted fs-7">Preferred Job Type</div>
        <div class="fw-semibold">{{ $seeker['job_type'] ?? 'N/A' }}</div>
    </div>
    <div class="col-md-6">
        <div class="fw-bold text-muted fs-7">Preferred Location</div>
        <div class="fw-semibold">{{ $seeker['job_location'] ?? 'N/A' }}</div>
    </div>

    <div class="col-md-6">
        <div class="fw-bold text-muted fs-7">Experience Level</div>
        <div class="fw-semibold">{{ $seeker['experience_level'] ?? 'N/A' }}</div>
    </div>
    <div class="col-md-6">
        <div class="fw-bold text-muted fs-7">Education Level</div>
        <div class="fw-semibold">{{ $seeker['education_level'] ?? 'N/A' }}</div>
    </div>

    <div class="col-md-6">
        <div class="fw-bold text-muted fs-7">Years of Experience</div>
        <div class="fw-semibold">{{ $seeker['years_of_experience'] ?? 0 }} years</div>
    </div>
    <div class="col-md-6">
        <div class="fw-bold text-muted fs-7">Salary Expectation</div>
        <div class="fw-semibold">{{ $seeker['salary_range'] ?? 'N/A' }}</div>
    </div>

    @if(!empty($skills))
        <div class="col-12">
            <div class="fw-bold text-muted fs-7">Skills</div>
            <div class="d-flex flex-wrap gap-2 mt-2">
                @foreach($skills as $skill)
                    <span class="badge badge-light-primary">{{ $skill }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if(!empty($languages))
        <div class="col-12">
            <div class="fw-bold text-muted fs-7">Languages</div>
            <div class="d-flex flex-wrap gap-2 mt-2">
                @foreach($languages as $lang)
                    <span class="badge badge-light-secondary">{{ $lang }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($seeker['professional_summary'] ?? null)
        <div class="col-12">
            <div class="fw-bold text-muted fs-7">Professional Summary</div>
            <div class="text-gray-700">{{ $seeker['professional_summary'] }}</div>
        </div>
    @endif

    @if(!empty($cvFiles))
        <div class="col-12">
            <div class="fw-bold text-muted fs-7">CV Files</div>
            <div class="d-flex flex-column gap-2 mt-2">
                @foreach($cvFiles as $cv)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center gap-3">
                            <i class="ki-duotone ki-file fs-2 text-primary">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            <div>
                                <div class="fw-semibold">{{ $cv['original_name'] ?? 'CV' }}</div>
                                <div class="text-muted fs-7">
                                    {{ isset($cv['size']) ? round($cv['size'] / 1024, 2) . ' KB' : '' }}
                                </div>
                            </div>
                        </div>
                        <a href="{{ $cv['url'] ?? '#' }}"
                        target="_blank"
                        class="btn btn-sm btn-primary">
                            <i class="ki-duotone ki-eye fs-4 me-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            View
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($seeker['linkedin_url'] ?? null)
        <div class="col-md-4">
            <a href="{{ $seeker['linkedin_url'] }}" target="_blank" class="btn btn-sm btn-light-primary w-100">
                LinkedIn
            </a>
        </div>
    @endif
    @if($seeker['github_url'] ?? null)
        <div class="col-md-4">
            <a href="{{ $seeker['github_url'] }}" target="_blank" class="btn btn-sm btn-light-dark w-100">
                GitHub
            </a>
        </div>
    @endif
    @if($seeker['portfolio_url'] ?? null)
        <div class="col-md-4">
            <a href="{{ $seeker['portfolio_url'] }}" target="_blank" class="btn btn-sm btn-light-info w-100">
                Portfolio
            </a>
        </div>
    @endif
</div>