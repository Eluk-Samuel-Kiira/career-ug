@extends('layouts.admin')

@section('title', 'Alert Preferences')
@section('page_title', 'Alert Preferences')

@section('content')
<div class="container py-6">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-transparent border-0 pt-5">
            <h3 class="card-title fw-bold">Job Alert Preferences</h3>
            <p class="text-muted fs-7 mt-1">Customize how you receive job alerts and who can view your CV.</p>
        </div>
        <div class="card-body pt-0">
            <form id="alertPreferencesForm">
                @csrf
                
                <!-- All Job Alerts -->
                <div class="d-flex align-items-center justify-content-between p-4 bg-light rounded-3 border border-gray-200 mb-4">
                    <div>
                        <h6 class="fw-bold mb-1">All Job Alerts</h6>
                        <p class="text-muted fs-7 mb-0">Receive alerts for all new job postings</p>
                    </div>
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" 
                               name="all_job_alerts" 
                               id="all_job_alerts" 
                               value="1"
                               {{ isset($jobPreferences['all_job_alerts']) && $jobPreferences['all_job_alerts'] ? 'checked' : '' }} />
                    </div>
                </div>

                <!-- Alert by Category -->
                <div class="p-4 bg-light rounded-3 border border-gray-200 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="fw-bold mb-1">Alert by Category</h6>
                            <p class="text-muted fs-7 mb-0">Receive alerts only for specific job categories</p>
                        </div>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" 
                                   name="alert_by_cv_skill" 
                                   id="alert_by_cv_skill" 
                                   value="1"
                                   {{ isset($jobPreferences['alert_by_cv_skill']) && $jobPreferences['alert_by_cv_skill'] ? 'checked' : '' }} />
                        </div>
                    </div>
                    
                    <div class="row g-3" id="categorySelection">
                        @if(count($categories) > 0)
                            @foreach($categories as $category)
                                @php
                                    $catId = is_array($category) ? ($category['id'] ?? null) : $category->id;
                                    $catName = is_array($category) ? ($category['name'] ?? '') : $category->name;
                                    $selectedCategories = $jobPreferences['alert_by_category'] ?? [];
                                    $isChecked = in_array($catId, $selectedCategories);
                                @endphp
                                @if($catId)
                                <div class="col-md-4">
                                    <div class="form-check form-check-custom">
                                        <input class="form-check-input category-checkbox" 
                                               type="checkbox" 
                                               name="alert_by_category[]" 
                                               value="{{ $catId }}"
                                               id="cat_{{ $catId }}"
                                               {{ $isChecked ? 'checked' : '' }} />
                                        <label class="form-check-label" for="cat_{{ $catId }}">
                                            {{ $catName }}
                                        </label>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @else
                            <div class="col-12">
                                <p class="text-muted">No categories available.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Alert by Skill Match -->
                <div class="d-flex align-items-center justify-content-between p-4 bg-light rounded-3 border border-gray-200 mb-4">
                    <div>
                        <h6 class="fw-bold mb-1">Alert by Skill Match</h6>
                        <p class="text-muted fs-7 mb-0">Receive alerts when a job post matches your skills (Premium Feature)</p>
                        <div class="mt-2">
                            <label class="fw-semibold fs-7 me-3">Match Threshold:</label>
                            <select name="skill_match_threshold" id="skill_match_threshold" class="form-select form-select-sm d-inline-block w-auto" 
                                    style="display: inline-block; width: auto;">
                                <option value="50" {{ isset($jobPreferences['skill_match_threshold']) && $jobPreferences['skill_match_threshold'] == 50 ? 'selected' : '' }}>50%</option>
                                <option value="60" {{ isset($jobPreferences['skill_match_threshold']) && $jobPreferences['skill_match_threshold'] == 60 ? 'selected' : '' }}>60%</option>
                                <option value="70" {{ isset($jobPreferences['skill_match_threshold']) && $jobPreferences['skill_match_threshold'] == 70 ? 'selected' : '' }}>70%</option>
                                <option value="80" {{ isset($jobPreferences['skill_match_threshold']) && $jobPreferences['skill_match_threshold'] == 80 ? 'selected' : '' }}>80%</option>
                                <option value="90" {{ isset($jobPreferences['skill_match_threshold']) && $jobPreferences['skill_match_threshold'] == 90 ? 'selected' : '' }}>90%</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" 
                               name="alert_by_skill_match" 
                               id="alert_by_skill_match" 
                               value="1"
                               {{ isset($jobPreferences['alert_by_skill_match']) && $jobPreferences['alert_by_skill_match'] ? 'checked' : '' }} />
                    </div>
                </div>

                <!-- Allow Employer View CV -->
                <div class="d-flex align-items-center justify-content-between p-4 bg-light rounded-3 border border-gray-200 mb-4">
                    <div>
                        <h6 class="fw-bold mb-1">Allow Employer to View My CV</h6>
                        <p class="text-muted fs-7 mb-0">Allow employers to access your CV when you apply</p>
                    </div>
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" 
                               name="allow_employer_view_my_cv" 
                               id="allow_employer_view_my_cv" 
                               value="1"
                               {{ isset($jobPreferences['allow_employer_view_my_cv']) && $jobPreferences['allow_employer_view_my_cv'] ? 'checked' : '' }} />
                    </div>
                </div>

                <!-- Alert Delivery Methods -->
                <div class="p-4 bg-light rounded-3 border border-gray-200 mb-4">
                    <h6 class="fw-bold mb-3">Alert Delivery Methods</h6>
                        <p class="text-muted fs-7 mb-0">Note: WhatsApp and Telegram are Premium Features...</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 border border-gray-200">
                                <i class="bi bi-whatsapp fs-3 text-success"></i>
                                <div>
                                    <label class="fw-semibold fs-7" for="send_alert_by_whatsapp">WhatsApp</label>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" 
                                               name="send_alert_by_whatsapp" 
                                               id="send_alert_by_whatsapp" 
                                               value="1"
                                               {{ isset($jobPreferences['send_alert_by_whatsapp']) && $jobPreferences['send_alert_by_whatsapp'] ? 'checked' : '' }} />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 border border-gray-200">
                                <i class="bi bi-telegram fs-3 text-info"></i>
                                <div>
                                    <label class="fw-semibold fs-7" for="send_alert_by_telegram">Telegram</label>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" 
                                               name="send_alert_by_telegram" 
                                               id="send_alert_by_telegram" 
                                               value="1"
                                               {{ isset($jobPreferences['send_alert_by_telegram']) && $jobPreferences['send_alert_by_telegram'] ? 'checked' : '' }} />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 border border-gray-200">
                                <i class="bi bi-envelope fs-3 text-primary"></i>
                                <div>
                                    <label class="fw-semibold fs-7" for="send_alert_by_email">Email</label>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" 
                                               name="send_alert_by_email" 
                                               id="send_alert_by_email" 
                                               value="1"
                                               {{ isset($jobPreferences['send_alert_by_email']) && $jobPreferences['send_alert_by_email'] ? 'checked' : '' }} />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-6 pt-6 border-top">
                    <button type="submit" class="btn btn-primary btn-lg px-8" id="saveAlertBtn">
                        <i class="bi bi-check-circle fs-3 me-2"></i>
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const form = document.getElementById('alertPreferencesForm');
    const saveBtn = document.getElementById('saveAlertBtn');
    
    // Toggle category checkboxes based on alert_by_cv_skill
    const alertByCvSkill = document.getElementById('alert_by_cv_skill');
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const categoryContainer = document.getElementById('categorySelection');
    
    function toggleCategories(enable) {
        categoryCheckboxes.forEach(checkbox => {
            checkbox.disabled = !enable;
            if (!enable) {
                checkbox.closest('.form-check').style.opacity = '0.5';
            } else {
                checkbox.closest('.form-check').style.opacity = '1';
            }
        });
    }
    
    // Initial state
    if (alertByCvSkill) {
        toggleCategories(alertByCvSkill.checked);
        alertByCvSkill.addEventListener('change', function() {
            toggleCategories(this.checked);
        });
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const originalText = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            
            const formData = new FormData(this);
            
            // Get selected categories
            const selectedCategories = [];
            categoryCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedCategories.push(checkbox.value);
                }
            });
            formData.set('alert_by_category', selectedCategories);
            
            const payload = {
                all_job_alerts: formData.get('all_job_alerts') === '1',
                alert_by_cv_skill: formData.get('alert_by_cv_skill') === '1',
                alert_by_skill_match: formData.get('alert_by_skill_match') === '1',
                skill_match_threshold: parseInt(formData.get('skill_match_threshold')) || 60,
                allow_employer_view_my_cv: formData.get('allow_employer_view_my_cv') === '1',
                send_alert_by_whatsapp: formData.get('send_alert_by_whatsapp') === '1',
                send_alert_by_telegram: formData.get('send_alert_by_telegram') === '1',
                send_alert_by_email: formData.get('send_alert_by_email') === '1',
                alert_by_category: selectedCategories.map(Number),
            };
            
            fetch('{{ route('alert.preferences.update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            })
            .then(response => response.json())
            .then(data => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
                
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('success', data.message, 'Success');
                    }
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast('error', data.message || 'Failed to save preferences.', 'Error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
                if (typeof window.showToast === 'function') {
                    window.showToast('error', 'Something went wrong. Please try again.', 'Error');
                }
            });
        });
    }
});
</script>
@endpush