@extends('layouts.admin')

@section('title', 'Company Profile')
@section('page_title', 'Company Profile')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Employer</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Company Profile</li>
@endsection

@section('content')
@php
    $p = $profile ?? [];
    $completion = 0;
    $fields = ['company_name', 'industry', 'company_size', 'company_description', 'contact_name', 'contact_email', 'contact_phone', 'city', 'country_code'];
    $filled = 0;
    foreach ($fields as $f) {
        if (!empty($p[$f])) $filled++;
    }
    $completion = (int) round(($filled / count($fields)) * 100);
@endphp

<div class="row g-6">

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- LEFT COLUMN — logo + status + tier                      --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="col-lg-4">

        {{-- Logo card --}}
        <div class="card card-flush mb-6">
            <div class="card-body text-center py-8">

                <div class="mb-4">
                    <div class="symbol symbol-125px symbol-circle mx-auto" id="logoPreviewWrap">
                        @php
                            $logoUrl = $p['logo_url'] ?? null;
                            $showLogo = $logoUrl && !str_contains($logoUrl, 'blank.png');
                        @endphp

                        <img id="logoPreview"
                            src="{{ $showLogo ? $logoUrl : asset('assets/media/avatars/blank.png') }}"
                            alt="Company logo"
                            style="object-fit:contain;background:#f8f9fa;" />
                    </div>
                </div>

                <div class="fw-bold fs-4 mb-1">{{ $p['company_name'] ?? 'Your Company' }}</div>
                <div class="text-muted fs-7 mb-4">
                    {{ $p['industry'] ?? 'Add your industry' }}
                </div>

                <input type="file" id="logoInput" accept="image/*" class="d-none" />
                <button type="button" class="btn btn-sm btn-light-primary me-2" id="uploadLogoBtn">
                    <i class="ki-duotone ki-arrow-up fs-4 me-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    {{ !empty($p['company_logo']) ? 'Change Logo' : 'Upload Logo' }}
                </button>
                @if(!empty($p['company_logo']))
                    <button type="button" class="btn btn-sm btn-light-danger" id="removeLogoBtn">
                        <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                @endif
                <div class="text-muted fs-8 mt-3">PNG, JPG or WEBP. Max 2MB.</div>
            </div>
        </div>

        {{-- Status card --}}
        <div class="card card-flush mb-6">
            <div class="card-header">
                <h3 class="card-title">Account Status</h3>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fs-7">Tier</span>
                    <span class="badge badge-light-{{ ($p['tier'] ?? 'starter') === 'trusted' ? 'success' : (($p['tier'] ?? 'starter') === 'verified' ? 'primary' : 'warning') }}">
                        {{ $p['tier_label'] ?? 'Starter' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fs-7">Verification</span>
                    @if($p['is_verified'] ?? false)
                        <span class="badge badge-light-success">✅ Verified</span>
                    @else
                        <span class="badge badge-light-secondary">Not verified</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fs-7">Job slots</span>
                    <span class="fw-bold fs-7">
                        {{ $p['active_jobs_count'] ?? 0 }}
                        /
                        {{ $p['active_job_limit'] === null ? '∞' : $p['active_job_limit'] }}
                    </span>
                </div>
                <div class="separator my-3"></div>
                <div class="text-muted fs-7 mb-2">
                    Upload compliance documents to unlock more job slots and a Verified badge.
                </div>
                <a href="#" class="btn btn-sm btn-light-warning w-100">
                    <i class="ki-duotone ki-document fs-4 me-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    Go to Compliance
                </a>
            </div>
        </div>

        {{-- Completeness card --}}
        <div class="card card-flush">
            <div class="card-body py-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold fs-7">Profile completeness</div>
                    <div class="fw-bold fs-7 text-primary">{{ $completion }}%</div>
                </div>
                <div class="progress h-8px mb-3">
                    <div class="progress-bar bg-primary"
                         style="width: {{ $completion }}%"
                         role="progressbar"></div>
                </div>
                <div class="text-muted fs-8">
                    Complete your profile to increase applicant trust.
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- RIGHT COLUMN — the form                                 --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="col-lg-8">
        <form id="profileForm">
            @csrf

            {{-- Basic Information --}}
            <div class="card card-flush mb-6">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-building fs-2 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Basic Information
                    </h3>
                </div>
                <div class="card-body">

                    <div class="row g-5 mb-5">
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2 required">Company Name</label>
                            <input type="text" name="company_name" class="form-control form-control-lg"
                                   value="{{ old('company_name', $p['company_name'] ?? '') }}"
                                   placeholder="Acme Ltd" required />
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Legal / Registered Name</label>
                            <input type="text" name="legal_name" class="form-control form-control-lg"
                                   value="{{ old('legal_name', $p['legal_name'] ?? '') }}"
                                   placeholder="Acme Limited" />
                            <div class="text-muted fs-8 mt-1">As it appears on official documents</div>
                        </div>
                    </div>

                    <div class="row g-5 mb-5">
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Trading Name</label>
                            <input type="text" name="trading_name" class="form-control"
                                   value="{{ old('trading_name', $p['trading_name'] ?? '') }}"
                                   placeholder="If different from company name" />
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Registration Number</label>
                            <input type="text" name="registration_number" class="form-control"
                                   value="{{ old('registration_number', $p['registration_number'] ?? '') }}"
                                   placeholder="BRN / Company No." />
                        </div>
                    </div>

                    <div class="row g-5 mb-5">
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Industry</label>
                            <input type="text" name="industry" class="form-control"
                                   value="{{ old('industry', $p['industry'] ?? '') }}"
                                   placeholder="e.g., Technology, Retail, Healthcare" />
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Company Size</label>
                            <select name="company_size" class="form-select">
                                <option value="">Select size</option>
                                @foreach(['1-10' => '1–10 employees', '11-50' => '11–50 employees', '51-200' => '51–200 employees', '200+' => '200+ employees'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('company_size', $p['company_size'] ?? '') === $val)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-5 mb-5">
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Year Founded</label>
                            <input type="number" name="year_founded" class="form-control"
                                   min="1900" max="{{ date('Y') }}"
                                   value="{{ old('year_founded', $p['year_founded'] ?? '') }}"
                                   placeholder="{{ date('Y') - 5 }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Company Website</label>
                            <input type="url" name="company_website" class="form-control"
                                   value="{{ old('company_website', $p['company_website'] ?? '') }}"
                                   placeholder="https://yourcompany.com" />
                        </div>
                    </div>

                    <div class="fv-row">
                        <label class="fw-semibold mb-2">About the Company</label>
                        <textarea name="company_description" rows="5" class="form-control"
                                  placeholder="What does your company do? What makes it a great place to work?">{{ old('company_description', $p['company_description'] ?? '') }}</textarea>
                        <div class="text-muted fs-8 mt-1">Visible to job seekers on your public company page.</div>
                    </div>
                </div>
            </div>

            {{-- Contact Person --}}
            <div class="card card-flush mb-6">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-profile-user fs-2 me-2">
                            <span class="path1"></span><span class="path2"></span>
                            <span class="path3"></span><span class="path4"></span>
                        </i>
                        Contact Person
                    </h3>
                </div>
                <div class="card-body">

                    <div class="row g-5 mb-5">
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Full Name</label>
                            <input type="text" name="contact_name" class="form-control"
                                   value="{{ old('contact_name', $p['contact_name'] ?? '') }}"
                                   placeholder="John Doe" />
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Position</label>
                            <input type="text" name="contact_position" class="form-control"
                                   value="{{ old('contact_position', $p['contact_position'] ?? '') }}"
                                   placeholder="e.g., HR Manager" />
                        </div>
                    </div>

                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control"
                                   value="{{ old('contact_email', $p['contact_email'] ?? '') }}"
                                   placeholder="john@company.com" />
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Contact Phone</label>
                            <input type="tel" name="contact_phone" class="form-control"
                                   value="{{ old('contact_phone', $p['contact_phone'] ?? '') }}"
                                   placeholder="+256 700 000 000" />
                        </div>
                    </div>

                    <div class="separator separator-dashed my-6"></div>

                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Company Email</label>
                            <input type="email" name="company_email" class="form-control"
                                   value="{{ old('company_email', $p['company_email'] ?? '') }}"
                                   placeholder="info@company.com" />
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Company Phone</label>
                            <input type="tel" name="company_phone" class="form-control"
                                   value="{{ old('company_phone', $p['company_phone'] ?? '') }}"
                                   placeholder="+256 414 000 000" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location --}}
            <div class="card card-flush mb-6">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-geolocation fs-2 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Location
                    </h3>
                </div>
                <div class="card-body">

                    <div class="fv-row mb-5">
                        <label class="fw-semibold mb-2">Street Address</label>
                        <input type="text" name="address" class="form-control"
                               value="{{ old('address', $p['address'] ?? '') }}"
                               placeholder="Plot 12, Kampala Road" />
                    </div>

                    <div class="row g-5 mb-5">
                        <div class="col-md-4">
                            <label class="fw-semibold mb-2">City</label>
                            <input type="text" name="city" class="form-control"
                                   value="{{ old('city', $p['city'] ?? '') }}"
                                   placeholder="Kampala" />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold mb-2">State / Region</label>
                            <input type="text" name="state" class="form-control"
                                   value="{{ old('state', $p['state'] ?? '') }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold mb-2">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control"
                                   value="{{ old('postal_code', $p['postal_code'] ?? '') }}" />
                        </div>
                    </div>

                    <div class="fv-row">
                        <label class="fw-semibold mb-2">Country</label>
                        <select name="country_code" class="form-select">
                            <option value="">Select country</option>
                            @foreach($countries as $c)
                                <option value="{{ $c['code'] }}"
                                        @selected(old('country_code', $p['country_code'] ?? 'UG') === $c['code'])>
                                    {{ $c['flag'] ?? '🌍' }} {{ $c['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Social --}}
            <div class="card card-flush mb-6">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-share fs-2 me-2">
                            <span class="path1"></span><span class="path2"></span>
                            <span class="path3"></span><span class="path4"></span>
                        </i>
                        Social Presence
                    </h3>
                </div>
                <div class="card-body">

                    <div class="fv-row mb-5">
                        <label class="fw-semibold mb-2">
                            <i class="ki-duotone ki-linkedin fs-4 me-1 text-primary">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            LinkedIn
                        </label>
                        <input type="url" name="linkedin_url" class="form-control"
                               value="{{ old('linkedin_url', $p['linkedin_url'] ?? '') }}"
                               placeholder="https://linkedin.com/company/yourcompany" />
                    </div>

                    <div class="fv-row mb-5">
                        <label class="fw-semibold mb-2">
                            <i class="ki-duotone ki-facebook fs-4 me-1 text-primary">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Facebook
                        </label>
                        <input type="url" name="facebook_url" class="form-control"
                               value="{{ old('facebook_url', $p['facebook_url'] ?? '') }}"
                               placeholder="https://facebook.com/yourcompany" />
                    </div>

                    <div class="fv-row">
                        <label class="fw-semibold mb-2">
                            <i class="ki-duotone ki-twitter fs-4 me-1 text-info">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            Twitter / X
                        </label>
                        <input type="url" name="twitter_url" class="form-control"
                               value="{{ old('twitter_url', $p['twitter_url'] ?? '') }}"
                               placeholder="https://twitter.com/yourcompany" />
                    </div>
                </div>
            </div>

            {{-- Save bar --}}
            <div class="card card-flush bg-light-primary">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="fw-bold">Save changes</div>
                        <div class="text-muted fs-7">Your company profile is visible to job seekers.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg px-8" id="saveBtn">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-check-circle fs-3 me-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            Save Profile
                        </span>
                        <span class="indicator-progress">
                            Saving... <span class="spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
          || '{{ csrf_token() }}';

// ── Logo upload ────────────────────────────────────────────
document.getElementById('uploadLogoBtn')?.addEventListener('click', () => {
    document.getElementById('logoInput').click();
});

document.getElementById('logoInput')?.addEventListener('change', function () {
    if (!this.files.length) return;

    const file = this.files[0];

    // Preview immediately
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('logoPreview').src = e.target.result;
    };
    reader.readAsDataURL(file);

    // Upload
    const formData = new FormData();
    formData.append('logo', file);
    formData.append('_token', CSRF);

    fetch('{{ route('employer.profile.logo.upload') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message || 'Logo uploaded.');
            document.getElementById('logoPreview').src = data.logo_url;
        } else {
            window.showToast('error', data.message || 'Upload failed.');
        }
    })
    .catch(() => window.showToast('error', 'Upload failed.'))
    .finally(() => { this.value = ''; });
});

// ── Remove logo ────────────────────────────────────────────
document.getElementById('removeLogoBtn')?.addEventListener('click', function () {
    if (!confirm('Remove your company logo?')) return;

    fetch('{{ route('employer.profile.logo.delete') }}', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message || 'Logo removed.');
            document.getElementById('logoPreview').src = data.logo_url;
        } else {
            window.showToast('error', data.message || 'Failed.');
        }
    })
    .catch(() => window.showToast('error', 'Failed.'));
});

// ── Save profile ───────────────────────────────────────────
document.getElementById('profileForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const btn = document.getElementById('saveBtn');
    window.showButtonSpinner(btn);

    const formData = new FormData(this);
    const payload = {};
    formData.forEach((v, k) => {
        if (k === '_token') return;
        payload[k] = v === '' ? null : v;
    });

    fetch('{{ route('employer.profile.update') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message || 'Profile saved.');
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message || 'Save failed.');
            window.showToast('error', msg);
        }
    })
    .catch(() => window.showToast('error', 'Save failed.'))
    .finally(() => window.hideButtonSpinner(btn));
});
</script>
@endpush