@extends('layouts.admin')

@section('title', 'My Profile')
@section('page_title', 'My Profile')

@section('content')
<div class="container py-6">
    <div class="row g-6">
        <!-- Profile Header -->
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-body p-6">
                    <div class="d-flex flex-wrap gap-5 align-items-center">
                        <!-- Avatar -->
                        <div class="position-relative">
                            <div class="symbol symbol-100px symbol-circle">
                                <img src="{{ $user['avatar'] ?? asset('assets/media/avatars/300-1.jpg') }}" 
                                     alt="Profile Picture" 
                                     id="profileAvatar" />
                            </div>
                            <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle p-1" 
                                    data-bs-toggle="modal" data-bs-target="#avatarModal" 
                                    style="width: 32px; height: 32px; border-radius: 50%;">
                                <i class="ki-duotone ki-camera fs-4">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>
                        </div>

                        <!-- User Info -->
                        <div class="flex-grow-1">
                            <h2 class="fw-bold mb-1">{{ $user['first_name'] ?? '' }} {{ $user['last_name'] ?? '' }}</h2>
                            <div class="d-flex flex-wrap gap-3 text-muted">
                                <span>
                                    <i class="ki-duotone ki-sms fs-5 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    {{ $user['email'] ?? '' }}
                                </span>
                                @if($user['phone'] ?? false)
                                <span>
                                    <i class="ki-duotone ki-phone fs-5 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    {{ $user['phone'] }}
                                </span>
                                @endif
                                <span class="badge badge-light-{{ $isEmployer ? 'primary' : 'success' }} fs-7 py-2 px-3">
                                    <i class="ki-duotone ki-{{ $isEmployer ? 'briefcase' : 'profile-circle' }} fs-5 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    {{ $isEmployer ? 'Employer' : 'Job Seeker' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-transparent border-0 pt-5">
                    <h3 class="card-title fw-bold">Profile Information</h3>
                </div>
                <div class="card-body pt-0">
                    <form id="profileForm" action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        <div class="row g-5">
                            <!-- First Name -->
                            <div class="col-md-6">
                                <label class="fw-semibold fs-6 mb-2">First Name</label>
                                <input type="text" name="first_name" class="form-control form-control-lg" 
                                    value="{{ $user['first_name'] ?? '' }}" required />
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6">
                                <label class="fw-semibold fs-6 mb-2">Last Name</label>
                                <input type="text" name="last_name" class="form-control form-control-lg" 
                                    value="{{ $user['last_name'] ?? '' }}" required />
                            </div>

                            <!-- Email (read-only) -->
                            <div class="col-md-6">
                                <label class="fw-semibold fs-6 mb-2">Email</label>
                                <input type="email" class="form-control form-control-lg" 
                                    value="{{ $user['email'] ?? '' }}" disabled readonly />
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="fw-semibold fs-6 mb-2">Phone Number</label>
                                <input type="tel" name="phone" class="form-control form-control-lg" 
                                    value="{{ $user['phone'] ?? '' }}" />
                            </div>

                            <!-- Country -->
                            <div class="col-md-6">
                                <label class="fw-semibold fs-6 mb-2">Country</label>
                                <input type="text" class="form-control form-control-lg" 
                                    value="{{ $user['country_code'] ?? 'Not set' }}" disabled readonly />
                            </div>

                            <!-- Professional Title (Seeker) / Company (Employer) -->
                            <div class="col-md-6">
                                @if($isSeeker)
                                    <label class="fw-semibold fs-6 mb-2">Professional Title</label>
                                    <input type="text" name="professional_title" class="form-control form-control-lg" 
                                        value="{{ $user['professional_title'] ?? '' }}" placeholder="e.g. Software Engineer" />
                                @else
                                    <label class="fw-semibold fs-6 mb-2">Company</label>
                                    <input type="text" class="form-control form-control-lg" 
                                        value="{{ $user['company_name'] ?? 'Not set' }}" disabled readonly />
                                @endif
                            </div>

                            <!-- Years of Experience (Seeker only) -->
                            @if($isSeeker)
                            <div class="col-md-6">
                                <label class="fw-semibold fs-6 mb-2">Years of Experience</label>
                                <input type="number" name="years_of_experience" class="form-control form-control-lg" 
                                    value="{{ $user['years_of_experience'] ?? '' }}" min="0" />
                            </div>
                            @endif

                            <!-- Skills (Seeker only) -->
                            @if($isSeeker)
                            <div class="col-12">
                                <label class="fw-semibold fs-6 mb-2">Skills</label>
                                <input type="text" name="skills" class="form-control form-control-lg" 
                                    value="{{ is_array($user['skills'] ?? null) ? implode(', ', $user['skills']) : ($user['skills'] ?? '') }}" 
                                    placeholder="e.g. PHP, JavaScript, Python" />
                                <div class="text-muted fs-7 mt-1">Comma separated</div>
                            </div>
                            @endif

                            <!-- Bio -->
                            <div class="col-12">
                                <label class="fw-semibold fs-6 mb-2">Bio</label>
                                <textarea name="bio" class="form-control form-control-lg" rows="3" 
                                        placeholder="Tell us about yourself">{{ $user['bio'] ?? '' }}</textarea>
                            </div>

                            <!-- Social Links -->
                            <div class="col-12">
                                <h5 class="fw-bold mb-3">Social Links</h5>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="fw-semibold fs-7 mb-1">LinkedIn</label>
                                        <input type="url" name="linkedin_url" class="form-control form-control-lg" 
                                            value="{{ $user['linkedin_url'] ?? '' }}" 
                                            placeholder="https://linkedin.com/in/username" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold fs-7 mb-1">GitHub</label>
                                        <input type="url" name="github_url" class="form-control form-control-lg" 
                                            value="{{ $user['github_url'] ?? '' }}" 
                                            placeholder="https://github.com/username" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold fs-7 mb-1">Portfolio</label>
                                        <input type="url" name="portfolio_url" class="form-control form-control-lg" 
                                            value="{{ $user['portfolio_url'] ?? '' }}" 
                                            placeholder="https://yourportfolio.com" />
                                    </div>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg px-8" id="profileSubmitBtn">
                                    <i class="ki-duotone ki-check-circle fs-3 me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pt-5">
                <h4 class="fw-bold">Update Profile Picture</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="avatarForm" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body text-center">
                    <div class="symbol symbol-120px symbol-circle mb-4 mx-auto">
                        <img src="{{ $user['avatar'] ?? asset('assets/media/avatars/300-1.jpg') }}" 
                             alt="Profile" id="avatarPreview" />
                    </div>
                    <input type="file" name="avatar" class="form-control form-control-lg" 
                           accept="image/*" id="avatarInput" required />
                    <div class="text-muted fs-7 mt-2">JPEG, PNG, GIF, WEBP - Max 2MB</div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="avatarSubmitBtn">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile form submission
    const profileForm = document.getElementById('profileForm');
    const profileSubmitBtn = document.getElementById('profileSubmitBtn');

    // console.log('Profile form found:', profileForm);
    // console.log('Profile submit button found:', profileSubmitBtn);

    if (profileForm && profileSubmitBtn) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // console.log('Form submitted!');
            
            // Show loading state
            const originalText = profileSubmitBtn.innerHTML;
            profileSubmitBtn.disabled = true;
            profileSubmitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
            `;

            const formData = new FormData(this);
            
            // Log form data for debugging
            for (let [key, value] of formData.entries()) {
                // console.log('Form field:', key, value);
            }
            
            // Get CSRF token from meta tag or fallback to the @csrf token in the form
            let csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                csrfToken = csrfToken.content;
            } else {
                // Fallback: try to get from the form's hidden input
                const csrfInput = document.querySelector('input[name="_token"]');
                if (csrfInput) {
                    csrfToken = csrfInput.value;
                }
            }
            
            // console.log('CSRF Token:', csrfToken);
            
            const url = document.getElementById('profileForm').action || '{{ route('profile.update') }}';
            // console.log('Submit URL:', url);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                // console.log('Response status:', response.status);
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Server error');
                    });
                }
                return response.json();
            })
            .then(data => {
                // console.log('Response data:', data);
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('success', data.message, 'Success');
                    } else {
                        alert(data.message);
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const errorMsg = data.message || 'Failed to update profile';
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat().join('\n');
                        console.error('Validation errors:', errorList);
                    }
                    if (typeof window.showToast === 'function') {
                        window.showToast('error', errorMsg, 'Error');
                    } else {
                        alert(errorMsg);
                    }
                    profileSubmitBtn.disabled = false;
                    profileSubmitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('error', 'Something went wrong. Please try again.', 'Error');
                } else {
                    alert('Something went wrong. Please try again.');
                }
                profileSubmitBtn.disabled = false;
                profileSubmitBtn.innerHTML = originalText;
            });
        });
    } else {
        console.error('Profile form or submit button not found!');
        // console.log('Profile form:', document.getElementById('profileForm'));
        // console.log('Submit button:', document.getElementById('profileSubmitBtn'));
    }

    // Avatar form submission
    const avatarForm = document.getElementById('avatarForm');
    const avatarSubmitBtn = document.getElementById('avatarSubmitBtn');
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    // console.log('Avatar form found:', avatarForm);
    // console.log('Avatar submit button found:', avatarSubmitBtn);

    // Preview avatar before upload
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (avatarPreview) {
                    avatarPreview.src = e.target.result;
                }
            };
            if (this.files && this.files[0]) {
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    if (avatarForm && avatarSubmitBtn) {
        avatarForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // console.log('Avatar form submitted!');
            
            if (!avatarInput || !avatarInput.files || !avatarInput.files.length) {
                if (typeof window.showToast === 'function') {
                    window.showToast('warning', 'Please select an image first.', 'Warning');
                } else {
                    alert('Please select an image first.');
                }
                return;
            }

            // Show loading state
            const originalText = avatarSubmitBtn.innerHTML;
            avatarSubmitBtn.disabled = true;
            avatarSubmitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Uploading...
            `;

            const formData = new FormData(this);
            
            // Get CSRF token
            let csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                csrfToken = csrfToken.content;
            } else {
                const csrfInput = document.querySelector('input[name="_token"]');
                if (csrfInput) {
                    csrfToken = csrfInput.value;
                }
            }
            
            const url = document.getElementById('avatarForm').action || '{{ route('profile.avatar') }}';
            // console.log('Avatar upload URL:', url);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                // console.log('Avatar upload response status:', response.status);
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Server error');
                    });
                }
                return response.json();
            })
            .then(data => {
                // console.log('Avatar upload data:', data);
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('success', data.message, 'Success');
                    } else {
                        alert(data.message);
                    }
                    // Update avatar in header
                    if (data.avatar) {
                        const profileAvatar = document.getElementById('profileAvatar');
                        if (profileAvatar) {
                            profileAvatar.src = data.avatar + '?t=' + Date.now();
                        }
                        if (avatarPreview) {
                            avatarPreview.src = data.avatar + '?t=' + Date.now();
                        }
                    }
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
                    if (modal) {
                        modal.hide();
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const errorMsg = data.message || 'Failed to update avatar';
                    if (typeof window.showToast === 'function') {
                        window.showToast('error', errorMsg, 'Error');
                    } else {
                        alert(errorMsg);
                    }
                    avatarSubmitBtn.disabled = false;
                    avatarSubmitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Avatar upload error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('error', 'Something went wrong. Please try again.', 'Error');
                } else {
                    alert('Something went wrong. Please try again.');
                }
                avatarSubmitBtn.disabled = false;
                avatarSubmitBtn.innerHTML = originalText;
            });
        });
    }
});
</script>
@endpush