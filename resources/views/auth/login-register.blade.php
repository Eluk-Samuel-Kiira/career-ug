@extends('layouts.app')

@section('title', 'Sign In or Create an Account | JobMatch')
@section('meta_description', 'Sign in or register as a job seeker or employer on JobMatch — Australia\'s AI-powered job portal.')

@push('styles')
<style>
	:root{
		--jp-navy: #0B1C2E;
		--jp-navy-2: #13273D;
		--jp-green: #20AA3E;
		--jp-teal: #03A588;
		--jp-gradient: linear-gradient(135deg, #20AA3E 0%, #03A588 100%);
		--jp-ink: #0F1B2D;
		--jp-muted: #64748B;
		--jp-bg-soft: #F4F8F7;
		--jp-line: rgba(15,27,45,0.08);
	}

	.jp-auth-wrap{ min-height: 78vh; }
	.jp-brand-panel{ background: radial-gradient(130% 130% at 20% 0%, #16304A 0%, var(--jp-navy) 55%, #081420 100%); color:#fff; border-radius:24px; padding:44px; position:relative; overflow:hidden; height:100%; }
	.jp-brand-panel::after{ content:""; position:absolute; inset:0; background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size:38px 38px; mask-image: linear-gradient(to bottom, black, transparent 80%); }
	.jp-brand-panel .content{ position:relative; z-index:2; }
	.jp-brand-panel h2{ font-weight:800; font-size:1.7rem; }
	.jp-brand-list{ list-style:none; padding:0; margin:26px 0 0; }
	.jp-brand-list li{ display:flex; gap:12px; padding:10px 0; color:#DCE6EF; font-size:.94rem; font-weight:600; }
	.jp-brand-list li i{ color:#5EE29B; margin-top:2px; }
	.jp-quote{ margin-top:36px; padding-top:24px; border-top:1px solid rgba(255,255,255,0.12); color:#B9C6D6; font-size:.9rem; font-style:italic; }

	.jp-form-panel{ background:#fff; border:1px solid var(--jp-line); border-radius:24px; padding:44px; height:100%; }

	.jp-type-toggle{ display:flex; background:var(--jp-bg-soft); border-radius:12px; padding:4px; margin-bottom:28px; }
	.jp-type-toggle label{ flex:1; text-align:center; padding:11px; border-radius:9px; font-weight:700; font-size:.9rem; color:var(--jp-muted); cursor:pointer; transition:.2s; }
	.jp-type-toggle input{ display:none; }
	.jp-type-toggle input:checked + label{ background:#fff; color:var(--jp-ink); box-shadow:0 4px 12px rgba(11,28,46,0.1); }

	.jp-tab-toggle{ display:flex; gap:28px; border-bottom:1px solid var(--jp-line); margin-bottom:28px; }
	.jp-tab-toggle a{ padding-bottom:14px; font-weight:700; color:var(--jp-muted); border-bottom:2px solid transparent; text-decoration:none; cursor:pointer; }
	.jp-tab-toggle a.active{ color:var(--jp-ink); border-color:var(--jp-teal); }

	.jp-form-panel .form-label{ font-weight:700; color:#33475B; font-size:.87rem; }
	.jp-form-panel .form-control, .jp-form-panel .form-select{ border-radius:10px; padding:12px 14px; border-color:var(--jp-line); }
	.jp-form-panel .form-control:focus, .jp-form-panel .form-select:focus{ border-color:var(--jp-teal); box-shadow:0 0 0 3px rgba(3,165,136,0.1); }

	.jp-btn-primary{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; padding:13px; width:100%; }
	.jp-btn-primary:hover{ color:#fff; filter:brightness(1.06); }

	.jp-divider{ display:flex; align-items:center; gap:14px; color:var(--jp-muted); font-size:.8rem; font-weight:600; margin:22px 0; }
	.jp-divider::before, .jp-divider::after{ content:""; flex:1; height:1px; background:var(--jp-line); }

	.jp-social-btn{ border:1px solid var(--jp-line); border-radius:10px; padding:11px; font-weight:700; font-size:.88rem; color:#33475B; width:100%; background:#fff; }
	.jp-social-btn:hover{ background:var(--jp-bg-soft); }

	.jp-fields-employer{ display:none; }
	[data-account-type="employer"] ~ * .jp-fields-employer,
	body.acct-employer .jp-fields-employer{ display:block; }
	body.acct-employer .jp-fields-seeker{ display:none; }

	/* Magic link styles */
	.jp-magic-link-info {
		background: var(--jp-bg-soft);
		border-radius: 10px;
		padding: 12px 16px;
		margin-top: 12px;
		font-size: .85rem;
		color: var(--jp-muted);
		border: 1px solid var(--jp-line);
	}
	.jp-magic-link-info i {
		color: var(--jp-teal);
		margin-right: 8px;
	}
	.jp-magic-link-success {
		background: #E9F9EF;
		border-color: #1E9E4C;
		color: #1E9E4C;
	}
	.jp-magic-link-error {
		background: #FEE2E2;
		border-color: #DC2626;
		color: #DC2626;
	}
</style>
@endpush

@section('content')

<div class="container py-12 py-lg-18">
	<div class="row g-6 g-lg-10 jp-auth-wrap align-items-stretch">

		<!-- ====================== BRAND PANEL ====================== -->
		<div class="col-lg-5 d-none d-lg-block">
			<div class="jp-brand-panel">
				<div class="content">
					<span class="jp-eyebrow" style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;border-radius:50px;font-size:12px;font-weight:700;color:#BFF7D2;background:rgba(32,170,62,0.14);border:1px solid rgba(32,170,62,0.35);">
						<i class="ki-duotone ki-abstract-14 fs-6"><span class="path1"></span><span class="path2"></span></i> Stardena Careers
					</span>
					<h2 class="btn jp-btn-primary mt-5 mb-2">One account, built for how you hire or how you're hired.</h2>
					<p style="color:#B9C6D6;">Switch anytime — your account adapts to job seeker or employer tools.</p>

					<ul class="jp-brand-list">
						<li><i class="ki-duotone ki-flash fs-4"><span class="path1"></span><span class="path2"></span></i> AI-matched roles and candidates, ranked by fit</li>
						<li><i class="ki-duotone ki-message-text-2 fs-4"><span class="path1"></span><span class="path2"></span></i> Job alerts and applicant updates on WhatsApp</li>
						<li><i class="ki-duotone ki-shield-tick fs-4"><span class="path1"></span><span class="path2"></span></i> Verified employers and screened candidate profiles</li>
						<li><i class="ki-duotone ki-document fs-4"><span class="path1"></span><span class="path2"></span></i> CV review, rewriting and cover letters on demand</li>
					</ul>

					<div class="jp-quote">"We filled our Western Sydney warehouse roles in nine days — most of it happened over WhatsApp." — Coles Group, Talent Acquisition</div>
				</div>
			</div>
		</div>

		<!-- ====================== FORM PANEL ====================== -->
		<div class="col-lg-7">
			<div class="jp-form-panel mx-auto" style="max-width:520px;">

				<!-- Account type toggle -->
				<div class="jp-type-toggle" id="jpAccountType">
					<input type="radio" name="account_type" id="type-seeker" value="seeker" {{ request('as', 'seeker') == 'seeker' ? 'checked' : '' }} />
					<label for="type-seeker"><i class="ki-duotone ki-profile-circle fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Job Seeker</label>

					<input type="radio" name="account_type" id="type-employer" value="employer" {{ request('as') == 'employer' ? 'checked' : '' }} />
					<label for="type-employer"><i class="ki-duotone ki-briefcase fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Employer</label>
				</div>

				<!-- Login / Register tab toggle - Login first (active by default) -->
				<div class="jp-tab-toggle">
					<a href="#" class="jp-tab-link active" data-tab="login">Sign In</a>
					<a href="#" class="jp-tab-link" data-tab="register">Create Account</a>
				</div>

				<!-- ============ LOGIN FORM (Magic Link) - ACTIVE BY DEFAULT ============ -->
				<form id="jp-login-form" action="{{ route('login.magic-link') }}" method="POST">
					@csrf
					<input type="hidden" name="account_type" id="login-account-type" value="{{ request('as', 'seeker') }}" />

					<div class="mb-4">
						<label class="form-label">Email address</label>
						<input type="email" name="email" class="form-control" placeholder="you@example.com" required />
					</div>

					<div class="jp-magic-link-info" id="login-magic-info">
						<i class="ki-duotone ki-information-5 fs-4"><span class="path1"></span><span class="path2"></span></i>
						We'll send you a magic link to sign in instantly — no password needed.
					</div>

					<button type="submit" class="btn jp-btn-primary mt-4" id="loginSubmitBtn">Send Magic Link</button>
				</form>

				<!-- ============ REGISTER FORM ============ -->
				<form id="jp-register-form" action="{{ route('register') }}" method="POST" style="display:none;">
					@csrf
					<input type="hidden" name="account_type" id="register-account-type" value="{{ request('as', 'seeker') }}" />
					<input type="hidden" name="country_code" value="{{ country_code() }}" />

					<!-- Seeker fields -->
					<div class="jp-fields-seeker">
						<div class="row g-4 mb-4">
							<div class="col-6">
								<label class="form-label">First name</label>
								<input type="text" name="first_name" class="form-control" placeholder="Jordan" required />
							</div>
							<div class="col-6">
								<label class="form-label">Last name</label>
								<input type="text" name="last_name" class="form-control" placeholder="Lee" required />
							</div>
						</div>
						<div class="mb-4">
							<label class="form-label">Desired job title</label>
							<input type="text" name="desired_title" class="form-control" placeholder="e.g. Warehouse Supervisor" />
						</div>
					</div>

					<!-- Employer fields -->
					<div class="jp-fields-employer">
						<div class="mb-4">
							<label class="form-label">Company name</label>
							<input type="text" name="company_name" class="form-control" placeholder="Acme Pty Ltd" />
						</div>
						<div class="row g-4 mb-4">
							<div class="col-6">
								<label class="form-label">Contact person</label>
								<input type="text" name="contact_name" class="form-control" placeholder="Jordan Lee" />
							</div>
							<div class="col-6">
								<label class="form-label">Company size</label>
								<select name="company_size" class="form-select">
									<option value="1-10">1–10 employees</option>
									<option value="11-50">11–50 employees</option>
									<option value="51-200">51–200 employees</option>
									<option value="200+">200+ employees</option>
								</select>
							</div>
						</div>
					</div>

					<div class="mb-4">
						<label class="form-label">Email address</label>
						<input type="email" name="email" class="form-control" placeholder="you@example.com" required />
					</div>
					
					<div class="mb-4">
						<label class="form-label">WhatsApp Number</label>
						<div class="input-group">
							<span class="input-group-text" style="border-radius:10px 0 0 10px; border-color:var(--jp-line); background:#f8f9fa; font-weight:600; color:var(--jp-muted);">
								{{ config('app.country_phone_code', '+256') }}
							</span>
							<input type="tel" name="phone" class="form-control" placeholder="7XX XXX XXX" style="border-radius:0 10px 10px 0; border-color:var(--jp-line);" />
						</div>
						<div class="text-muted fs-8 mt-1">Include your phone number without the country code</div>
					</div>

					<div class="jp-magic-link-info" id="register-magic-info">
						<i class="ki-duotone ki-information-5 fs-4"><span class="path1"></span><span class="path2"></span></i>
						After registration, we'll send you a magic link to verify your email and sign in.
					</div>

					<div class="form-check form-check-custom mb-6 mt-4">
						<input class="form-check-input" type="checkbox" id="terms" name="terms" required />
						<label class="form-check-label fs-8 text-muted" for="terms">I agree to the <a href="{{ route('home') }}" class="text-hover-primary">Terms &amp; Conditions</a> and <a href="{{ route('home') }}" class="text-hover-primary">Privacy Policy</a>.</label>
					</div>

					<button type="submit" class="btn jp-btn-primary" id="registerSubmitBtn">Create Account</button>
				</form>

			</div>
		</div>
	</div>
</div>

<!-- Toast Container -->
<div id="toastStackContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

<!-- Toast Template -->
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

@endsection

@push('scripts')
<script>
	document.addEventListener('DOMContentLoaded', function () {
		// ----- Toast System -----
		const container = document.getElementById('toastStackContainer');
		const template = document.getElementById('toastTemplate');

		if (container && template) {
			template.remove();

			window.showToast = function(type, message, title = '', duration = 5000) {
				const newToast = template.cloneNode(true);
				newToast.classList.remove('d-none');
				newToast.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';

				const titleElem = newToast.querySelector('.toast-header strong');
				const bodyElem = newToast.querySelector('.toast-body');
				const timeElem = newToast.querySelector('.toast-header small');
				const icon = newToast.querySelector('.toast-header i');

				if (titleElem) titleElem.textContent = title || (type.charAt(0).toUpperCase() + type.slice(1));
				if (bodyElem) bodyElem.textContent = message;
				if (timeElem) {
					const now = new Date();
					timeElem.textContent = `${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')}`;
				}

				// Style based on type
				const elementsToReset = [icon, titleElem, bodyElem, timeElem];
				elementsToReset.forEach(el => {
					if (el) {
						el.classList.remove('text-success', 'text-danger', 'text-warning', 'text-info', 'text-muted');
					}
				});

				switch (type) {
					case 'success':
						if (icon) {
							icon.classList.add('text-success');
							icon.className = 'ki-duotone ki-check-circle fs-2 me-3 text-success';
						}
						if (titleElem) titleElem.classList.add('text-success');
						if (bodyElem) bodyElem.classList.add('text-success');
						if (timeElem) timeElem.classList.add('text-success');
						break;
					case 'error':
					case 'danger':
						if (icon) {
							icon.classList.add('text-danger');
							icon.className = 'ki-duotone ki-cross-circle fs-2 me-3 text-danger';
						}
						if (titleElem) titleElem.classList.add('text-danger');
						if (bodyElem) bodyElem.classList.add('text-danger');
						if (timeElem) timeElem.classList.add('text-danger');
						break;
					case 'warning':
						if (icon) {
							icon.classList.add('text-warning');
							icon.className = 'ki-duotone ki-information-5 fs-2 me-3 text-warning';
						}
						if (titleElem) titleElem.classList.add('text-warning');
						if (bodyElem) bodyElem.classList.add('text-warning');
						if (timeElem) timeElem.classList.add('text-warning');
						break;
					case 'info':
					default:
						if (icon) {
							icon.classList.add('text-info');
							icon.className = 'ki-duotone ki-information-4 fs-2 me-3 text-info';
						}
						if (titleElem) titleElem.classList.add('text-info');
						if (bodyElem) bodyElem.classList.add('text-info');
						if (timeElem) timeElem.classList.add('text-info');
						break;
				}

				container.appendChild(newToast);
				const bsToast = new bootstrap.Toast(newToast, { autohide: true, delay: duration });
				bsToast.show();
				newToast.addEventListener('hidden.bs.toast', () => newToast.remove());
			};
		}

		// ----- Account type toggle -----
		var typeInputs = document.querySelectorAll('input[name="account_type"]');
		var seekerFields = document.querySelectorAll('.jp-fields-seeker');
		var employerFields = document.querySelectorAll('.jp-fields-employer');
		var registerHidden = document.getElementById('register-account-type');
		var loginHidden = document.getElementById('login-account-type');

		function applyType(val) {
			var isEmployer = val === 'employer';
			seekerFields.forEach(function (el) { el.style.display = isEmployer ? 'none' : 'block'; });
			employerFields.forEach(function (el) { el.style.display = isEmployer ? 'block' : 'none'; });
			if (registerHidden) registerHidden.value = val;
			if (loginHidden) loginHidden.value = val;
		}

		typeInputs.forEach(function (input) {
			input.addEventListener('change', function () { applyType(this.value); });
		});
		applyType(document.querySelector('input[name="account_type"]:checked')?.value || 'seeker');

		// ----- Login / Register tab toggle -----
		var tabLinks = document.querySelectorAll('.jp-tab-link');
		var registerForm = document.getElementById('jp-register-form');
		var loginForm = document.getElementById('jp-login-form');

		tabLinks.forEach(function (link) {
			link.addEventListener('click', function (e) {
				e.preventDefault();
				tabLinks.forEach(function (l) { l.classList.remove('active'); });
				this.classList.add('active');
				if (this.dataset.tab === 'login') {
					loginForm.style.display = 'block';
					registerForm.style.display = 'none';
				} else {
					registerForm.style.display = 'block';
					loginForm.style.display = 'none';
				}
			});
		});

		// ----- Handle Login Form (Magic Link) -----
		var loginFormElement = document.getElementById('jp-login-form');
		if (loginFormElement) {
			loginFormElement.addEventListener('submit', function(e) {
				e.preventDefault();
				var formData = new FormData(this);
				var submitBtn = document.getElementById('loginSubmitBtn');
				var originalText = submitBtn.innerHTML;
				var infoDiv = document.getElementById('login-magic-info');
				
				submitBtn.innerHTML = 'Sending... <span class="spinner-border spinner-border-sm ms-2"></span>';
				submitBtn.disabled = true;

				fetch(this.action, {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
						'Accept': 'application/json'
					},
					body: formData
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						infoDiv.innerHTML = '<i class="ki-duotone ki-check-circle fs-4"><span class="path1"></span><span class="path2"></span></i> ' + data.message;
						infoDiv.className = 'jp-magic-link-info jp-magic-link-success';
						submitBtn.innerHTML = '✅ Sent!';
						submitBtn.disabled = true;
						
						// Show toast notification
						if (typeof window.showToast === 'function') {
							window.showToast('success', data.message, 'Success');
						}
					} else {
						// Show error in toast
						if (typeof window.showToast === 'function') {
							window.showToast('error', data.message || 'Something went wrong. Please try again.', 'Error');
						}
						submitBtn.innerHTML = originalText;
						submitBtn.disabled = false;
					}
				})
				.catch(function(error) {
					console.error('Error:', error);
					if (typeof window.showToast === 'function') {
						window.showToast('error', 'Something went wrong. Please try again.', 'Error');
					}
					submitBtn.innerHTML = originalText;
					submitBtn.disabled = false;
				});
			});
		}

		// ----- Handle Register Form -----
		var registerFormElement = document.getElementById('jp-register-form');
		if (registerFormElement) {
			registerFormElement.addEventListener('submit', function(e) {
				e.preventDefault();
				
				// Get account type
				var accountType = document.querySelector('input[name="account_type"]:checked').value;
				
				// Validate employer fields if employer is selected
				if (accountType === 'employer') {
					var companyName = this.querySelector('input[name="company_name"]');
					var contactName = this.querySelector('input[name="contact_name"]');
					var isValid = true;
					
					if (!companyName.value.trim()) {
						companyName.classList.add('is-invalid');
						isValid = false;
					} else {
						companyName.classList.remove('is-invalid');
					}
					
					if (!contactName.value.trim()) {
						contactName.classList.add('is-invalid');
						isValid = false;
					} else {
						contactName.classList.remove('is-invalid');
					}
					
					if (!isValid) {
						if (typeof window.showToast === 'function') {
							window.showToast('warning', 'Please fill in all employer fields.', 'Validation Error');
						}
						return;
					}
				}
				
				var formData = new FormData(this);
				var submitBtn = document.getElementById('registerSubmitBtn');
				var originalText = submitBtn.innerHTML;
				var infoDiv = document.getElementById('register-magic-info');
				
				submitBtn.innerHTML = 'Creating Account... <span class="spinner-border spinner-border-sm ms-2"></span>';
				submitBtn.disabled = true;

				fetch(this.action, {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
						'Accept': 'application/json'
					},
					body: formData
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						infoDiv.innerHTML = '<i class="ki-duotone ki-check-circle fs-4"><span class="path1"></span><span class="path2"></span></i> ' + data.message;
						infoDiv.className = 'jp-magic-link-info jp-magic-link-success';
						submitBtn.innerHTML = '✅ Account Created!';
						submitBtn.disabled = true;
						
						if (typeof window.showToast === 'function') {
							window.showToast('success', data.message, 'Account Created');
						}
						
						setTimeout(function() {
							var loginTab = document.querySelector('.jp-tab-link[data-tab="login"]');
							if (loginTab) {
								loginTab.click();
							}
						}, 3000);
					} else {
						var errorMsg = data.message;
						if (data.errors) {
							errorMsg = Object.values(data.errors).flat().join('\n');
						}
						
						if (typeof window.showToast === 'function') {
							window.showToast('error', errorMsg || 'Something went wrong. Please try again.', 'Registration Failed');
						}
						submitBtn.innerHTML = originalText;
						submitBtn.disabled = false;
					}
				})
				.catch(function(error) {
					console.error('Error:', error);
					if (typeof window.showToast === 'function') {
						window.showToast('error', 'Something went wrong. Please try again.', 'Error');
					}
					submitBtn.innerHTML = originalText;
					submitBtn.disabled = false;
				});
			});
		}

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
@endpush