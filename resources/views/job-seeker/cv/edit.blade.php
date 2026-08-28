@extends('layouts.admin')

@section('title', 'My CV')
@section('page_title', 'My CV')

@section('content')
<div class="container py-6">
	<div class="card shadow-sm border-0 rounded-4">
		<div class="card-header position-relative py-0 border-bottom-2">
			<ul class="nav nav-stretch nav-pills nav-pills-custom d-flex mt-3" id="cvTabs">
				<li class="nav-item p-0 ms-0 me-8">
					<a class="nav-link btn btn-color-muted px-0 active" data-bs-toggle="tab" href="#tab_upload">
						<span class="nav-text fw-semibold fs-4 mb-3">Upload CV</span>
						<span class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
					</a>
				</li>
				<li class="nav-item p-0 ms-0 me-8">
					<a class="nav-link btn btn-color-muted px-0" data-bs-toggle="tab" href="#tab_cv_files">
						<span class="nav-text fw-semibold fs-4 mb-3">My CVs <span class="badge badge-light-primary ms-1">{{ $totalCount ?? 0 }}/{{ $maxFiles ?? 3 }}</span></span>
						<span class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
					</a>
				</li>
				<li class="nav-item p-0 ms-0 me-8">
					<a class="nav-link btn btn-color-muted px-0" data-bs-toggle="tab" href="#tab_personal">
						<span class="nav-text fw-semibold fs-4 mb-3">Personal & Summary</span>
						<span class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
					</a>
				</li>
				<li class="nav-item p-0 ms-0 me-8">
					<a class="nav-link btn btn-color-muted px-0" data-bs-toggle="tab" href="#tab_skills">
						<span class="nav-text fw-semibold fs-4 mb-3">Skills & Languages</span>
						<span class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
					</a>
				</li>
				<li class="nav-item p-0 ms-0 me-8">
					<a class="nav-link btn btn-color-muted px-0" data-bs-toggle="tab" href="#tab_experience">
						<span class="nav-text fw-semibold fs-4 mb-3">Experience</span>
						<span class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
					</a>
				</li>
				<li class="nav-item p-0 ms-0 me-8">
					<a class="nav-link btn btn-color-muted px-0" data-bs-toggle="tab" href="#tab_education">
						<span class="nav-text fw-semibold fs-4 mb-3">Education</span>
						<span class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
					</a>
				</li>
				<li class="nav-item p-0 ms-0 me-8">
					<a class="nav-link btn btn-color-muted px-0" data-bs-toggle="tab" href="#tab_extra">
						<span class="nav-text fw-semibold fs-4 mb-3">Certifications & Projects</span>
						<span class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
					</a>
				</li>
			</ul>
		</div>

		<div class="card-body">
			<div class="tab-content">

				<!-- ===================== UPLOAD TAB ===================== -->
				<div class="tab-pane fade show active" id="tab_upload">
					<div class="d-flex flex-column align-items-center text-center py-10" id="uploadZone">
						<i class="ki-duotone ki-file-up fs-3x text-primary mb-4">
							<span class="path1"></span><span class="path2"></span>
						</i>
						<h3 class="fw-bold mb-2">Upload your CV</h3>
						<p class="text-muted mb-5">PDF or Word document, up to 5MB. Our AI will read it and fill in the tabs for you.</p>
						
						@if(($totalCount ?? 0) >= ($maxFiles ?? 3))
							<div class="alert alert-warning mb-4 w-100">
								<i class="ki-duotone ki-information-5 fs-3 me-2"></i>
								You have reached the maximum of {{ $maxFiles ?? 3 }} CV files. Please delete one before uploading another.
							</div>
						@endif
						
						<input type="file" id="cvFileInput" accept=".pdf,.doc,.docx" class="d-none" />
						<button type="button" class="btn btn-primary px-8" id="cvChooseBtn" {{ ($totalCount ?? 0) >= ($maxFiles ?? 3) ? 'disabled' : '' }}>
							<i class="ki-duotone ki-folder-up fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
							Choose File
						</button>
						<div class="text-muted fs-7 mt-3" id="cvFileName"></div>
					</div>

					<div id="cvUploadProgress" class="d-none">
						<div class="d-flex align-items-center gap-3 justify-content-center py-10">
							<span class="spinner-border spinner-border-sm text-primary"></span>
							<span class="fw-semibold">Reading your CV and matching it to your profile...</span>
						</div>
					</div>

					<div id="cvUploadResult"></div>
				</div>

                <!-- ===================== CV FILES TAB ===================== -->
                <div class="tab-pane fade" id="tab_cv_files">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">
                            Uploaded CV Files 
                            <span class="badge badge-light-primary ms-1 cv-count-badge">{{ $totalCount ?? 0 }}/{{ $maxFiles ?? 3 }}</span>
                        </h5>
                        <a href="#tab_upload" class="btn btn-sm btn-primary" data-bs-toggle="tab" id="uploadNewBtn">
                            <i class="ki-duotone ki-plus fs-3 me-1"></i> Upload New
                        </a>
                    </div>
                    
                    @php
                        $cvFilesArray = isset($cvFiles) && is_array($cvFiles) ? $cvFiles : [];
                        $totalCount = count($cvFilesArray);
                    @endphp
                    
                    @if(!empty($cvFilesArray) && count($cvFilesArray) > 0)
                        <div class="d-flex flex-column gap-3" id="cvFilesList">
                            @foreach($cvFilesArray as $index => $file)
                                <div class="d-flex align-items-center justify-content-between p-4 bg-light rounded-3 border border-gray-200 cv-file-item" data-index="{{ $index }}">
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="symbol symbol-50px bg-primary-light rounded-3 d-flex align-items-center justify-content-center">
                                            <i class="ki-duotone ki-file fs-2x text-primary">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-6">{{ $file['original_name'] ?? 'CV File' }}</div>
                                            <div class="text-muted fs-7">
                                                <span class="me-3">
                                                    <i class="ki-duotone ki-weight fs-7 me-1"></i>
                                                    {{ isset($file['size']) ? round($file['size'] / 1024, 2) . ' KB' : 'Unknown size' }}
                                                </span>
                                                <span>
                                                    <i class="ki-duotone ki-calendar fs-7 me-1"></i>
                                                    {{ isset($file['uploaded_at']) ? \Carbon\Carbon::parse($file['uploaded_at'])->format('M d, Y H:i') : 'Unknown date' }}
                                                </span>
                                                <span class="ms-3">
                                                    <span class="badge badge-light-success">CV #{{ $index + 1 }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ $file['url'] ?? '#' }}" target="_blank" class="btn btn-sm btn-light-primary" title="View CV">
                                            <i class="bi bi-eye fs-5"></i>
                                        </a>
                                        <a href="{{ $file['url'] ?? '#' }}" download class="btn btn-sm btn-light-success" title="Download CV">
                                            <i class="bi bi-download fs-5"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light-danger delete-cv-btn" 
                                                data-path="{{ $file['path'] ?? '' }}"
                                                data-name="{{ $file['original_name'] ?? 'CV' }}"
                                                title="Delete CV">
                                            <i class="bi bi-trash fs-5"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Debug: Show total count -->
                        <div class="text-muted fs-7 mt-2 text-end">
                            Total: {{ count($cvFilesArray) }} CV files
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="ki-duotone ki-file fs-3x text-muted d-block mb-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <h6 class="fw-bold mb-2">No CV files uploaded yet</h6>
                            <p class="text-muted">Upload your first CV to get started.</p>
                            <a href="#tab_upload" class="btn btn-primary" data-bs-toggle="tab">
                                <i class="ki-duotone ki-plus fs-3 me-1"></i> Upload CV
                            </a>
                        </div>
                    @endif
                    
                    @if(($totalCount ?? 0) >= ($maxFiles ?? 3))
                        <div class="alert alert-warning mt-4">
                            <i class="ki-duotone ki-information-5 fs-3 me-2"></i>
                            You have reached the maximum of {{ $maxFiles ?? 3 }} CV files. Please delete one before uploading another.
                        </div>
                    @endif
                </div>

				<!-- ===================== PERSONAL & SUMMARY ===================== -->
				<div class="tab-pane fade" id="tab_personal">
					<div class="row g-5">
						<div class="col-md-6">
							<label class="fw-semibold fs-6 mb-2">First Name</label>
							<input type="text" class="form-control form-control-lg" id="f_first_name" />
						</div>
						<div class="col-md-6">
							<label class="fw-semibold fs-6 mb-2">Last Name</label>
							<input type="text" class="form-control form-control-lg" id="f_last_name" />
						</div>
						<div class="col-md-6">
							<label class="fw-semibold fs-6 mb-2">Phone</label>
							<input type="text" class="form-control form-control-lg" id="f_phone" />
						</div>
						<div class="col-md-6">
							<label class="fw-semibold fs-6 mb-2">Professional Title</label>
							<input type="text" class="form-control form-control-lg" id="f_professional_title" />
						</div>
						<div class="col-md-4">
							<label class="fw-semibold fs-6 mb-2">City</label>
							<input type="text" class="form-control form-control-lg" id="f_city" />
						</div>
						<div class="col-md-4">
							<label class="fw-semibold fs-6 mb-2">Country</label>
							<input type="text" class="form-control form-control-lg" id="f_country" />
						</div>
						<div class="col-md-4">
							<label class="fw-semibold fs-6 mb-2">Postal Code</label>
							<input type="text" class="form-control form-control-lg" id="f_postal_code" />
						</div>
						<div class="col-md-4">
							<label class="fw-semibold fs-6 mb-2">Date of Birth</label>
							<input type="date" class="form-control form-control-lg" id="f_date_of_birth" />
						</div>
						<div class="col-md-4">
							<label class="fw-semibold fs-6 mb-2">Nationality</label>
							<input type="text" class="form-control form-control-lg" id="f_nationality" />
						</div>
						<div class="col-md-4">
							<label class="fw-semibold fs-6 mb-2">Years of Experience</label>
							<input type="number" min="0" class="form-control form-control-lg" id="f_years_of_experience" />
						</div>
						<div class="col-md-4">
							<label class="fw-semibold fs-6 mb-2">LinkedIn</label>
							<input type="url" class="form-control form-control-lg" id="f_linkedin_url" />
						</div>
						<div class="col-md-4">
							<label class="fw-semibold fs-6 mb-2">GitHub</label>
							<input type="url" class="form-control form-control-lg" id="f_github_url" />
						</div>
						<div class="col-md-4">
							<label class="fw-semibold fs-6 mb-2">Portfolio</label>
							<input type="url" class="form-control form-control-lg" id="f_portfolio_url" />
						</div>
						<div class="col-12">
							<label class="fw-semibold fs-6 mb-2">Professional Summary</label>
							<textarea class="form-control form-control-lg" rows="4" id="f_professional_summary"></textarea>
						</div>
					</div>
				</div>

				<!-- ===================== SKILLS & LANGUAGES ===================== -->
				<div class="tab-pane fade" id="tab_skills">
					<div class="row g-5">
						<div class="col-12">
							<label class="fw-semibold fs-6 mb-2">Skills</label>
							<input type="text" class="form-control form-control-lg" id="f_skills" placeholder="e.g. PHP, Laravel, JavaScript" />
							<div class="text-muted fs-7 mt-1">Comma separated</div>
						</div>
						<div class="col-12">
							<label class="fw-semibold fs-6 mb-2">Languages</label>
							<input type="text" class="form-control form-control-lg" id="f_languages" placeholder="e.g. English, French" />
							<div class="text-muted fs-7 mt-1">Comma separated</div>
						</div>
					</div>
				</div>

				<!-- ===================== EXPERIENCE ===================== -->
				<div class="tab-pane fade" id="tab_experience">
					<div id="experienceList" class="d-flex flex-column gap-4"></div>
					<button type="button" class="btn btn-light-primary mt-4" id="addExperienceBtn">
						<i class="ki-duotone ki-plus fs-3 me-1"></i>Add Experience
					</button>
				</div>

				<!-- ===================== EDUCATION ===================== -->
				<div class="tab-pane fade" id="tab_education">
					<div id="educationList" class="d-flex flex-column gap-4"></div>
					<button type="button" class="btn btn-light-primary mt-4" id="addEducationBtn">
						<i class="ki-duotone ki-plus fs-3 me-1"></i>Add Education
					</button>
				</div>

				<!-- ===================== CERTIFICATIONS & PROJECTS ===================== -->
				<div class="tab-pane fade" id="tab_extra">
					<h5 class="fw-bold mb-3">Certifications</h5>
					<div id="certificationsList" class="d-flex flex-column gap-4 mb-4"></div>
					<button type="button" class="btn btn-light-primary mb-8" id="addCertificationBtn">
						<i class="ki-duotone ki-plus fs-3 me-1"></i>Add Certification
					</button>

					<h5 class="fw-bold mb-3">Projects</h5>
					<div id="projectsList" class="d-flex flex-column gap-4"></div>
					<button type="button" class="btn btn-light-primary mt-4" id="addProjectBtn">
						<i class="ki-duotone ki-plus fs-3 me-1"></i>Add Project
					</button>
				</div>

			</div>

			<!-- Save Button -->
			<div class="d-flex justify-content-end mt-8 pt-6 border-top">
				<button type="button" class="btn btn-primary btn-lg px-8" id="saveCvBtn">
					<i class="ki-duotone ki-check-circle fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
					Save Profile
				</button>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
	const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
	const initialProfile = @json($profile ?? []);

	// ---------- Function to update CV count ----------
	function updateCvCount(totalCount, maxFiles) {
		const max = maxFiles || 3;
		const count = totalCount || 0;
		
		// console.log('Updating CV count:', count, '/', max);
		
		// Update all CV count badges
		const badgeElements = document.querySelectorAll('.cv-count-badge, #tab_cv_files .badge, .cv-badge');
		// console.log('Found badge elements:', badgeElements.length);
		
		badgeElements.forEach((badge, index) => {
			badge.textContent = count + '/' + max;
			// console.log('Updated badge', index, 'to:', badge.textContent);
		});
		
		// Update the header count
		const headerCount = document.querySelector('#tab_cv_files h5 .badge');
		if (headerCount) {
			headerCount.textContent = count + '/' + max;
		}
		
		// Update the tab badge specifically
		const tabBadge = document.querySelector('a[href="#tab_cv_files"] .badge');
		if (tabBadge) {
			tabBadge.textContent = count + '/' + max;
		}
		
		// Update the upload button state
		const uploadBtn = document.getElementById('cvChooseBtn');
		const maxWarning = document.querySelector('.alert-warning');
		
		if (count >= max) {
			if (uploadBtn) {
				uploadBtn.disabled = true;
				uploadBtn.title = 'Maximum CVs reached';
			}
			if (maxWarning) {
				maxWarning.style.display = 'block';
			}
		} else {
			if (uploadBtn) {
				uploadBtn.disabled = false;
				uploadBtn.title = '';
			}
			if (maxWarning) {
				maxWarning.style.display = 'none';
			}
		}
		
		// Update the "Upload New" button visibility
		const uploadNewBtn = document.querySelector('#tab_cv_files .btn-primary');
		if (uploadNewBtn) {
			if (count >= max) {
				uploadNewBtn.style.display = 'none';
			} else {
				uploadNewBtn.style.display = 'inline-flex';
			}
		}
	}

	// ---------- Repeater templates ----------
	function experienceRow(data = {}) {
		const div = document.createElement('div');
		div.className = 'card bg-light-secondary border-0 p-4 exp-row';
		div.innerHTML = `
			<div class="row g-3">
				<div class="col-md-6"><input class="form-control" placeholder="Company" data-field="company" value="${data.company ?? ''}"></div>
				<div class="col-md-6"><input class="form-control" placeholder="Job Title" data-field="title" value="${data.title ?? ''}"></div>
				<div class="col-md-3"><input class="form-control" placeholder="Start Date" data-field="start_date" value="${data.start_date ?? ''}"></div>
				<div class="col-md-3"><input class="form-control" placeholder="End Date" data-field="end_date" value="${data.end_date ?? ''}"></div>
				<div class="col-md-6"><input class="form-control" placeholder="Description" data-field="description" value="${data.description ?? ''}"></div>
			</div>
			<button type="button" class="btn btn-sm btn-light-danger mt-3 remove-row"><i class="ki-duotone ki-trash fs-4"></i> Remove</button>
		`;
		div.querySelector('.remove-row').onclick = () => div.remove();
		return div;
	}

	function educationRow(data = {}) {
		const div = document.createElement('div');
		div.className = 'card bg-light-secondary border-0 p-4 edu-row';
		div.innerHTML = `
			<div class="row g-3">
				<div class="col-md-6"><input class="form-control" placeholder="Institution" data-field="institution" value="${data.institution ?? ''}"></div>
				<div class="col-md-6"><input class="form-control" placeholder="Degree" data-field="degree" value="${data.degree ?? ''}"></div>
				<div class="col-md-4"><input class="form-control" placeholder="Field of Study" data-field="field" value="${data.field ?? ''}"></div>
				<div class="col-md-4"><input class="form-control" placeholder="Grade" data-field="grade" value="${data.grade ?? ''}"></div>
				<div class="col-md-2"><input class="form-control" placeholder="Start Year" data-field="start_year" value="${data.start_year ?? ''}"></div>
				<div class="col-md-2"><input class="form-control" placeholder="End Year" data-field="end_year" value="${data.end_year ?? ''}"></div>
			</div>
			<button type="button" class="btn btn-sm btn-light-danger mt-3 remove-row"><i class="ki-duotone ki-trash fs-4"></i> Remove</button>
		`;
		div.querySelector('.remove-row').onclick = () => div.remove();
		return div;
	}

	function certificationRow(data = {}) {
		const div = document.createElement('div');
		div.className = 'card bg-light-secondary border-0 p-4 cert-row';
		div.innerHTML = `
			<div class="row g-3">
				<div class="col-md-5"><input class="form-control" placeholder="Certification Name" data-field="name" value="${data.name ?? ''}"></div>
				<div class="col-md-4"><input class="form-control" placeholder="Issuer" data-field="issuer" value="${data.issuer ?? ''}"></div>
				<div class="col-md-3"><input class="form-control" placeholder="Year" data-field="year" value="${data.year ?? ''}"></div>
			</div>
			<button type="button" class="btn btn-sm btn-light-danger mt-3 remove-row"><i class="ki-duotone ki-trash fs-4"></i> Remove</button>
		`;
		div.querySelector('.remove-row').onclick = () => div.remove();
		return div;
	}

	function projectRow(data = {}) {
		const div = document.createElement('div');
		div.className = 'card bg-light-secondary border-0 p-4 proj-row';
		div.innerHTML = `
			<div class="row g-3">
				<div class="col-md-4"><input class="form-control" placeholder="Project Name" data-field="name" value="${data.name ?? ''}"></div>
				<div class="col-md-5"><input class="form-control" placeholder="Description" data-field="description" value="${data.description ?? ''}"></div>
				<div class="col-md-3"><input class="form-control" placeholder="URL" data-field="url" value="${data.url ?? ''}"></div>
			</div>
			<button type="button" class="btn btn-sm btn-light-danger mt-3 remove-row"><i class="ki-duotone ki-trash fs-4"></i> Remove</button>
		`;
		div.querySelector('.remove-row').onclick = () => div.remove();
		return div;
	}

	// ---------- Add button handlers ----------
	const addExpBtn = document.getElementById('addExperienceBtn');
	const addEduBtn = document.getElementById('addEducationBtn');
	const addCertBtn = document.getElementById('addCertificationBtn');
	const addProjBtn = document.getElementById('addProjectBtn');
	
	if (addExpBtn) addExpBtn.onclick = () => document.getElementById('experienceList').appendChild(experienceRow());
	if (addEduBtn) addEduBtn.onclick = () => document.getElementById('educationList').appendChild(educationRow());
	if (addCertBtn) addCertBtn.onclick = () => document.getElementById('certificationsList').appendChild(certificationRow());
	if (addProjBtn) addProjBtn.onclick = () => document.getElementById('projectsList').appendChild(projectRow());

	// ---------- Populate all tabs from a profile object ----------
	function populateForm(profile) {
		const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };

		setVal('f_first_name', profile.first_name);
		setVal('f_last_name', profile.last_name);
		setVal('f_phone', profile.phone);
		setVal('f_professional_title', profile.professional_title);
		setVal('f_city', profile.city);
		setVal('f_country', profile.country);
		setVal('f_postal_code', profile.postal_code);
		setVal('f_date_of_birth', profile.date_of_birth);
		setVal('f_nationality', profile.nationality);
		setVal('f_years_of_experience', profile.years_of_experience);
		setVal('f_linkedin_url', profile.linkedin_url);
		setVal('f_github_url', profile.github_url);
		setVal('f_portfolio_url', profile.portfolio_url);
		setVal('f_professional_summary', profile.professional_summary);
		setVal('f_skills', Array.isArray(profile.skills) ? profile.skills.join(', ') : (profile.skills ?? ''));
		setVal('f_languages', Array.isArray(profile.languages) ? profile.languages.join(', ') : (profile.languages ?? ''));

		const expList = document.getElementById('experienceList'); 
		if (expList) {
			expList.innerHTML = '';
			(profile.work_experience || []).forEach(item => expList.appendChild(experienceRow(item)));
		}

		const eduList = document.getElementById('educationList'); 
		if (eduList) {
			eduList.innerHTML = '';
			(profile.education || []).forEach(item => eduList.appendChild(educationRow(item)));
		}

		const certList = document.getElementById('certificationsList'); 
		if (certList) {
			certList.innerHTML = '';
			(profile.certifications || []).forEach(item => certList.appendChild(certificationRow(item)));
		}

		const projList = document.getElementById('projectsList'); 
		if (projList) {
			projList.innerHTML = '';
			(profile.projects || []).forEach(item => projList.appendChild(projectRow(item)));
		}
	}

	if (initialProfile) {
		populateForm(initialProfile);
	}

	// ---------- Upload handling ----------
	const fileInput = document.getElementById('cvFileInput');
	const chooseBtn = document.getElementById('cvChooseBtn');
	if (chooseBtn) {
		chooseBtn.onclick = () => fileInput.click();
	}

	if (fileInput) {
		fileInput.addEventListener('change', function () {
			if (!this.files.length) return;

			const file = this.files[0];
			const fileNameDisplay = document.getElementById('cvFileName');
			if (fileNameDisplay) fileNameDisplay.textContent = file.name;
			
			const uploadZone = document.getElementById('uploadZone');
			if (uploadZone) uploadZone.classList.add('d-none');
			
			const progressDiv = document.getElementById('cvUploadProgress');
			if (progressDiv) progressDiv.classList.remove('d-none');
			
			const resultDiv = document.getElementById('cvUploadResult');
			if (resultDiv) resultDiv.innerHTML = '';

			const formData = new FormData();
			formData.append('cv', file);

			fetch('{{ route('cv.upload') }}', {
				method: 'POST',
				headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
				body: formData,
			})
			.then(r => r.json())
			.then(data => {
				if (progressDiv) progressDiv.classList.add('d-none');
				if (uploadZone) uploadZone.classList.remove('d-none');

				if (data.success) {
					if (data.profile) {
						populateForm(data.profile);
					}
					
					if (resultDiv) {
						resultDiv.innerHTML = '<div class="alert alert-success mt-4">CV processed! Review the tabs above, then save.</div>';
					}
					
					if (typeof window.showToast === 'function') {
						window.showToast('success', data.message || 'CV processed successfully!', 'Success');
					}
					
					// ✅ Update CV count
					if (data.total_count !== undefined || data.cv_files) {
						const totalCount = data.total_count || (data.cv_files ? data.cv_files.length : 0);
						const maxFiles = data.max_files || 3;
						updateCvCount(totalCount, maxFiles);
					}
					
					// Switch to personal tab
					const personalTab = document.querySelector('a[href="#tab_personal"]');
					if (personalTab) {
						new bootstrap.Tab(personalTab).show();
					}
				} else {
					const errorMsg = data.message || 'Failed to process CV.';
					if (resultDiv) {
						resultDiv.innerHTML = `<div class="alert alert-danger mt-4">${errorMsg}</div>`;
					}
					
					if (typeof window.showToast === 'function') {
						window.showToast('error', errorMsg, 'Error');
					}
				}
			})
			.catch(() => {
				if (progressDiv) progressDiv.classList.add('d-none');
				if (uploadZone) uploadZone.classList.remove('d-none');
				if (resultDiv) {
					resultDiv.innerHTML = '<div class="alert alert-danger mt-4">Something went wrong uploading your CV.</div>';
				}
				
				if (typeof window.showToast === 'function') {
					window.showToast('error', 'Something went wrong uploading your CV.', 'Error');
				}
			});
		});
	}

	// ---------- Save handling ----------
	function collectRows(containerId) {
		const container = document.getElementById(containerId);
		if (!container) return [];
		
		return Array.from(container.children).map(row => {
			const obj = {};
			row.querySelectorAll('[data-field]').forEach(input => { 
				obj[input.dataset.field] = input.value; 
			});
			return obj;
		});
	}

	const saveBtn = document.getElementById('saveCvBtn');
	if (saveBtn) {
		saveBtn.addEventListener('click', function () {
			const btn = this;
			const originalText = btn.innerHTML;
			btn.disabled = true;
			btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

			const payload = {
				first_name: document.getElementById('f_first_name')?.value || '',
				last_name: document.getElementById('f_last_name')?.value || '',
				phone: document.getElementById('f_phone')?.value || '',
				professional_title: document.getElementById('f_professional_title')?.value || '',
				city: document.getElementById('f_city')?.value || '',
				country: document.getElementById('f_country')?.value || '',
				postal_code: document.getElementById('f_postal_code')?.value || '',
				date_of_birth: document.getElementById('f_date_of_birth')?.value || '',
				nationality: document.getElementById('f_nationality')?.value || '',
				years_of_experience: document.getElementById('f_years_of_experience')?.value || '',
				linkedin_url: document.getElementById('f_linkedin_url')?.value || '',
				github_url: document.getElementById('f_github_url')?.value || '',
				portfolio_url: document.getElementById('f_portfolio_url')?.value || '',
				professional_summary: document.getElementById('f_professional_summary')?.value || '',
				skills: document.getElementById('f_skills')?.value || '',
				languages: document.getElementById('f_languages')?.value ? 
					document.getElementById('f_languages').value.split(',').map(s => s.trim()).filter(Boolean) : [],
				work_experience: collectRows('experienceList'),
				education: collectRows('educationList'),
				certifications: collectRows('certificationsList'),
				projects: collectRows('projectsList'),
			};

			fetch('{{ route('profile.update') }}', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': csrf,
					'Accept': 'application/json',
				},
				body: JSON.stringify(payload),
			})
			.then(r => r.json())
			.then(data => {
				btn.disabled = false;
				btn.innerHTML = originalText;
				
				if (data.success) {
					if (typeof window.showToast === 'function') {
						window.showToast('success', data.message || 'Profile saved successfully!', 'Success');
					}
				} else {
					const errorMsg = data.message || 'Failed to save profile.';
					if (typeof window.showToast === 'function') {
						window.showToast('error', errorMsg, 'Error');
					}
					if (data.errors) {
						const errorList = Object.values(data.errors).flat().join('\n');
						if (typeof window.showToast === 'function') {
							window.showToast('error', errorList, 'Validation Error');
						}
					}
				}
			})
			.catch(() => {
				btn.disabled = false;
				btn.innerHTML = originalText;
				
				if (typeof window.showToast === 'function') {
					window.showToast('error', 'Something went wrong saving your profile.', 'Error');
				}
			});
		});
	}

	// ---------- Delete CV functionality ----------
	document.querySelectorAll('.delete-cv-btn').forEach(btn => {
		btn.addEventListener('click', function() {
			const filePath = this.dataset.path;
			const fileName = this.dataset.name;
			
			if (!filePath) {
				if (typeof window.showToast === 'function') {
					window.showToast('error', 'File path not found.', 'Error');
				}
				return;
			}
			
			if (!confirm(`Are you sure you want to delete "${fileName || 'CV file'}"?`)) {
				return;
			}
			
			const btn = this;
			const originalText = btn.innerHTML;
			btn.disabled = true;
			btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
			
			fetch('{{ route('cv.delete') }}', {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': csrf,
					'Content-Type': 'application/json',
					'Accept': 'application/json'
				},
				body: JSON.stringify({ file_path: filePath })
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					if (typeof window.showToast === 'function') {
						window.showToast('success', data.message || 'CV deleted successfully', 'Success');
					}
					
					// ✅ Update CV count
					if (data.total_count !== undefined) {
						updateCvCount(data.total_count, data.max_files || 3);
					}
					
					// Reload to refresh the list
					setTimeout(() => location.reload(), 500);
				} else {
					if (typeof window.showToast === 'function') {
						window.showToast('error', data.message || 'Failed to delete CV', 'Error');
					}
					btn.disabled = false;
					btn.innerHTML = originalText;
				}
			})
			.catch(error => {
				console.error('Error:', error);
				if (typeof window.showToast === 'function') {
					window.showToast('error', 'Failed to delete CV. Please try again.', 'Error');
				}
				btn.disabled = false;
				btn.innerHTML = originalText;
			});
		});
	});

	// ---------- Initial CV count update ----------
	const initialTotal = {{ $totalCount ?? 0 }};
	const initialMax = {{ $maxFiles ?? 3 }};
	updateCvCount(initialTotal, initialMax);
});
</script>
@endpush