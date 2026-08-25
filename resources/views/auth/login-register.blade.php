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
	.jp-tab-toggle a{ padding-bottom:14px; font-weight:700; color:var(--jp-muted); border-bottom:2px solid transparent; text-decoration:none; }
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
						<i class="ki-duotone ki-abstract-14 fs-6"><span class="path1"></span><span class="path2"></span></i> JobMatch
					</span>
					<h2 class="mt-5 mb-2btn jp-btn-primary">One account, built for how you hire or how you're hired.</h2>
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

				<!-- Login / Register tab toggle -->
				<div class="jp-tab-toggle">
					<a href="#" class="jp-tab-link active" data-tab="register">Create Account</a>
					<a href="#" class="jp-tab-link" data-tab="login">Sign In</a>
				</div>

				<!-- ============ REGISTER FORM ============ -->
				<form id="jp-register-form" action="{{ route('register') }}" method="POST">
					@csrf
					<input type="hidden" name="account_type" id="register-account-type" value="{{ request('as', 'seeker') }}" />

					<!-- Seeker fields -->
					<div class="jp-fields-seeker">
						<div class="row g-4 mb-4">
							<div class="col-6">
								<label class="form-label">First name</label>
								<input type="text" name="first_name" class="form-control" placeholder="Jordan" />
							</div>
							<div class="col-6">
								<label class="form-label">Last name</label>
								<input type="text" name="last_name" class="form-control" placeholder="Lee" />
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
									<option>1–10 employees</option>
									<option>11–50 employees</option>
									<option>51–200 employees</option>
									<option>200+ employees</option>
								</select>
							</div>
						</div>
					</div>

					<div class="mb-4">
						<label class="form-label">Email address</label>
						<input type="email" name="email" class="form-control" placeholder="you@example.com" required />
					</div>
					<div class="row g-4 mb-4">
						<div class="col-6">
							<label class="form-label">Mobile (WhatsApp)</label>
							<input type="tel" name="phone" class="form-control" placeholder="04XX XXX XXX" />
						</div>
						<div class="col-6">
							<label class="form-label">Password</label>
							<input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required />
						</div>
					</div>

					<div class="form-check form-check-custom mb-6">
						<input class="form-check-input" type="checkbox" id="terms" name="terms" required />
						<label class="form-check-label fs-8 text-muted" for="terms">I agree to the <a href="{{ route('home') }}" class="text-hover-primary">Terms &amp; Conditions</a> and <a href="{{ route('home') }}" class="text-hover-primary">Privacy Policy</a>.</label>
					</div>

					<button type="submit" class="btn jp-btn-primary">Create Account</button>

					<div class="jp-divider">or continue with</div>
					<div class="row g-3">
						<div class="col-6"><button type="button" class="jp-social-btn"><i class="ki-duotone ki-user fs-4 me-1"></i>Google</button></div>
						<div class="col-6"><button type="button" class="jp-social-btn"><i class="ki-duotone ki-message-text-2 fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>WhatsApp</button></div>
					</div>
				</form>

				<!-- ============ LOGIN FORM ============ -->
				<form id="jp-login-form" action="{{ route('login') }}" method="POST" style="display:none;">
					@csrf
					<input type="hidden" name="account_type" id="login-account-type" value="{{ request('as', 'seeker') }}" />

					<div class="mb-4">
						<label class="form-label">Email address</label>
						<input type="email" name="email" class="form-control" placeholder="you@example.com" required />
					</div>
					<div class="mb-3">
						<label class="form-label">Password</label>
						<input type="password" name="password" class="form-control" placeholder="Enter your password" required />
					</div>
					<div class="d-flex justify-content-end mb-6">
						<a href="{{ route('home') }}" class="fs-8 fw-semibold text-hover-primary">Forgot password?</a>
					</div>

					<button type="submit" class="btn jp-btn-primary">Sign In</button>

					<div class="jp-divider">or continue with</div>
					<div class="row g-3">
						<div class="col-6"><button type="button" class="jp-social-btn"><i class="ki-duotone ki-user fs-4 me-1"></i>Google</button></div>
						<div class="col-6"><button type="button" class="jp-social-btn"><i class="ki-duotone ki-message-text-2 fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>WhatsApp</button></div>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
<script>
	document.addEventListener('DOMContentLoaded', function () {
		// Account type toggle (seeker / employer)
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

		// Login / Register tab toggle
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
	});
</script>
@endpush