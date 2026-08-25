@extends('layouts.app')

@section('title', ($job->title ?? 'Job Details') . ' at ' . ($job->company_name ?? 'Company') . ' | JobMatch')
@section('meta_description', 'Apply for ' . ($job->title ?? 'this role') . ' at ' . ($job->company_name ?? 'a leading Australian employer') . '. Easy Apply, AI CV match score, and WhatsApp updates.')

@push('styles')
<style>
	:root{
		--jp-navy: #0B1C2E;
		--jp-green: #20AA3E;
		--jp-teal: #03A588;
		--jp-gradient: linear-gradient(135deg, #20AA3E 0%, #03A588 100%);
		--jp-ink: #0F1B2D;
		--jp-muted: #64748B;
		--jp-bg-soft: #F4F8F7;
		--jp-line: rgba(15,27,45,0.08);
	}

	.jp-job-head{ background: radial-gradient(120% 160% at 10% 0%, #16304A 0%, var(--jp-navy) 60%, #081420 100%); }
	.jp-logo-lg{ width:76px; height:76px; border-radius:16px; background:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:24px; color:var(--jp-teal); box-shadow:0 10px 26px rgba(0,0,0,0.25); }
	.jp-job-head h1{ color:#fff; font-weight:800; font-size:1.85rem; }
	.jp-job-head .company{ color:#BFE9D9; font-weight:700; }
	.jp-job-head .meta{ color:#B9C6D6; }
	.jp-pill-dark{ background:rgba(255,255,255,0.08); color:#fff; border:1px solid rgba(255,255,255,0.14); font-size:12px; font-weight:700; padding:5px 12px; border-radius:20px; }
	.jp-salary-lg{ color:#5EE29B; font-weight:800; font-size:1.15rem; }

	.jp-card{ background:#fff; border:1px solid var(--jp-line); border-radius:16px; padding:26px; }
	.jp-card h5{ font-weight:800; color:var(--jp-ink); }
	.jp-card ul.spec-list{ list-style:none; padding:0; margin:0; }
	.jp-card ul.spec-list li{ display:flex; gap:10px; padding:8px 0; color:#33475B; font-size:.95rem; }
	.jp-card ul.spec-list li i{ color:var(--jp-teal); margin-top:3px; }

	.jp-apply-card{ background:linear-gradient(180deg, var(--jp-navy) 0%, #13273D 100%); color:#fff; border-radius:16px; padding:26px; position:sticky; top:24px; }
	.jp-apply-card .btn-apply{ background:var(--jp-gradient); border:none; color:#fff; font-weight:700; border-radius:10px; padding:13px; }
	.jp-apply-card .btn-wa{ background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.16); color:#fff; font-weight:700; border-radius:10px; padding:12px; }
	.jp-apply-card .match{ background:rgba(94,226,155,0.12); border:1px solid rgba(94,226,155,0.3); border-radius:12px; padding:14px; }

	.jp-step{ display:flex; gap:14px; padding:16px 0; border-bottom:1px solid var(--jp-line); }
	.jp-step:last-child{ border-bottom:none; }
	.jp-step .num{ width:30px; height:30px; border-radius:50%; background:var(--jp-bg-soft); color:var(--jp-teal); font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.85rem; }

	.jp-company-card .logo{ width:56px; height:56px; border-radius:12px; background:var(--jp-bg-soft); display:flex; align-items:center; justify-content:center; font-weight:800; color:var(--jp-teal); }

	.jp-similar-item{ display:flex; gap:12px; padding:14px; border:1px solid var(--jp-line); border-radius:12px; transition:.2s; }
	.jp-similar-item:hover{ border-color:var(--jp-teal); background:var(--jp-bg-soft); }
	.jp-similar-item .logo-sm{ width:40px; height:40px; border-radius:10px; background:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; color:var(--jp-teal); flex-shrink:0; }
</style>
@endpush

@section('content')

<!-- ====================== JOB HEADER ====================== -->
<div class="jp-job-head py-10 py-lg-14">
	<div class="container">
		<nav class="mb-6">
			<a href="{{ route('jobs.index') }}" class="text-white opacity-50 text-hover-primary fs-8 fw-semibold"><i class="ki-duotone ki-arrow-left fs-7 me-1"></i>Back to jobs</a>
		</nav>
		<div class="d-flex flex-column flex-lg-row gap-6 align-items-lg-center justify-content-between">
			<div class="d-flex gap-5 align-items-center">
				<div class="jp-logo-lg">{{ strtoupper(substr($job->company_name ?? 'JM', 0, 2)) }}</div>
				<div>
					<h1 class="mb-1">{{ $job->title ?? 'Senior Warehouse Supervisor' }}</h1>
					<div class="company fs-5">{{ $job->company_name ?? 'Coles Group' }}</div>
					<div class="meta fs-7 mt-2 d-flex flex-wrap gap-4">
						<span><i class="ki-duotone ki-geolocation fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>{{ $job->location ?? 'Western Sydney, NSW' }}</span>
						<span><i class="ki-duotone ki-calendar fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>Posted {{ $job->posted_at ?? '2 days ago' }}</span>
						<span><i class="ki-duotone ki-eye fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>{{ $job->views ?? 214 }} views</span>
					</div>
				</div>
			</div>
			<div class="text-lg-end">
				<div class="jp-salary-lg mb-2">{{ $job->salary ?? '$34 – $38 / hour' }}</div>
				<div class="d-flex gap-2 flex-wrap justify-content-lg-end">
					<span class="jp-pill-dark">{{ $job->job_type ?? 'Full-time' }}</span>
					<span class="jp-pill-dark">{{ $job->category ?? 'Logistics & Warehouse' }}</span>
					<span class="jp-pill-dark">{{ $job->setting ?? 'On-site' }}</span>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="container py-12 py-lg-16">
	<div class="row g-6 g-lg-10">

		<!-- ====================== MAIN CONTENT ====================== -->
		<div class="col-lg-8">

			<div class="jp-card mb-6">
				<h5 class="mb-4">Overview</h5>
				<p class="text-gray-700 fs-6 lh-lg mb-0">
					{{ $job->description ?? 'We\'re looking for an experienced Warehouse Supervisor to lead a team of 15 across our Western Sydney distribution centre. You\'ll oversee daily operations, safety compliance, and shift scheduling while working closely with logistics and inventory teams to keep our supply chain moving.' }}
				</p>
			</div>

			<div class="jp-card mb-6">
				<h5 class="mb-4">Responsibilities</h5>
				<ul class="spec-list">
					@forelse(($job->responsibilities ?? []) as $item)
					<li><i class="ki-duotone ki-check fs-4"></i>{{ $item }}</li>
					@empty
					<li><i class="ki-duotone ki-check fs-4"></i>Supervise daily warehouse operations and shift rosters</li>
					<li><i class="ki-duotone ki-check fs-4"></i>Ensure compliance with WHS and site safety standards</li>
					<li><i class="ki-duotone ki-check fs-4"></i>Coordinate with logistics on inbound and outbound stock</li>
					<li><i class="ki-duotone ki-check fs-4"></i>Train, mentor and manage performance of warehouse staff</li>
					@endforelse
				</ul>
			</div>

			<div class="jp-card mb-6">
				<h5 class="mb-4">Requirements</h5>
				<ul class="spec-list">
					@forelse(($job->requirements ?? []) as $item)
					<li><i class="ki-duotone ki-check fs-4"></i>{{ $item }}</li>
					@empty
					<li><i class="ki-duotone ki-check fs-4"></i>3+ years' experience in a warehouse supervisory role</li>
					<li><i class="ki-duotone ki-check fs-4"></i>Current forklift licence (LF) required</li>
					<li><i class="ki-duotone ki-check fs-4"></i>Strong understanding of WHS regulations</li>
					<li><i class="ki-duotone ki-check fs-4"></i>Available for rotating shifts including weekends</li>
					@endforelse
				</ul>
			</div>

			<div class="jp-card mb-6">
				<h5 class="mb-4">Benefits</h5>
				<ul class="spec-list">
					@forelse(($job->benefits ?? []) as $item)
					<li><i class="ki-duotone ki-check fs-4"></i>{{ $item }}</li>
					@empty
					<li><i class="ki-duotone ki-check fs-4"></i>Above-award hourly rate plus penalty rates</li>
					<li><i class="ki-duotone ki-check fs-4"></i>Career progression across a national network</li>
					<li><i class="ki-duotone ki-check fs-4"></i>Staff discounts and wellbeing program</li>
					@endforelse
				</ul>
			</div>

			<div class="jp-card">
				<h5 class="mb-2">How to apply</h5>
				<p class="text-muted fs-7 mb-0">Three quick steps — most candidates finish in under two minutes.</p>
				<div class="mt-4">
					<div class="jp-step">
						<div class="num">1</div>
						<div>
							<div class="fw-bold">Confirm your CV</div>
							<div class="text-muted fs-7">Use your saved profile CV, or upload a new one for this application.</div>
						</div>
					</div>
					<div class="jp-step">
						<div class="num">2</div>
						<div>
							<div class="fw-bold">Add a cover note (optional)</div>
							<div class="text-muted fs-7">Write your own, or let AI draft one from your profile in seconds.</div>
						</div>
					</div>
					<div class="jp-step">
						<div class="num">3</div>
						<div>
							<div class="fw-bold">Submit and track</div>
							<div class="text-muted fs-7">We'll confirm by email and send status updates to WhatsApp.</div>
						</div>
					</div>
				</div>
			</div>

		</div>

		<!-- ====================== SIDEBAR ====================== -->
		<div class="col-lg-4">

			<div class="jp-apply-card mb-6">
				<div class="match mb-5">
					<div class="d-flex align-items-center gap-2 mb-1">
						<i class="ki-duotone ki-verify fs-3" style="color:#5EE29B;"><span class="path1"></span><span class="path2"></span></i>
						<span class="fw-bold fs-6">{{ $job->match_score ?? 92 }}% match to your profile</span>
					</div>
					<div class="fs-8 opacity-75">Based on your saved CV and preferences</div>
				</div>
				<button type="button" class="btn btn-apply w-100 mb-3">
					<i class="ki-duotone ki-flash fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Easy Apply
				</button>
				<button type="button" class="btn btn-wa w-100 mb-5">
					<i class="ki-duotone ki-message-text-2 fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Apply via WhatsApp
				</button>
				<div class="d-flex justify-content-between fs-8 opacity-75 border-top pt-4" style="border-color:rgba(255,255,255,0.1) !important;">
					<span><i class="ki-duotone ki-people fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>{{ $job->applicants ?? 47 }} applied</span>
					<button type="button" class="btn btn-sm btn-link text-white opacity-75 p-0"><i class="ki-duotone ki-heart fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>Save</button>
				</div>
			</div>

			<div class="jp-card jp-company-card mb-6">
				<div class="d-flex align-items-center gap-4 mb-4">
					<div class="logo">{{ strtoupper(substr($job->company_name ?? 'JM', 0, 2)) }}</div>
					<div>
						<div class="fw-bold">{{ $job->company_name ?? 'Coles Group' }}</div>
						<div class="text-muted fs-8">{{ $job->company_industry ?? 'Retail & Logistics' }}</div>
					</div>
				</div>
				<p class="text-muted fs-7 mb-4">{{ $job->company_about ?? 'One of Australia\'s largest retail and logistics employers, with distribution centres in every state.' }}</p>
				<a href="#" class="btn btn-sm btn-outline btn-outline-primary w-100">View Company Profile</a>
			</div>

			<div class="jp-card">
				<h5 class="mb-4 fs-6">Similar jobs</h5>
				<div class="d-flex flex-column gap-3">
					@forelse(($similarJobs ?? []) as $sj)
					<a href="{{ route('jobs.show', $sj->slug ?? $sj->id) }}" class="jp-similar-item text-decoration-none">
						<div class="logo-sm">{{ strtoupper(substr($sj->company_name ?? 'JM', 0, 2)) }}</div>
						<div>
							<div class="fw-bold fs-7 text-gray-900">{{ $sj->title ?? 'Job Title' }}</div>
							<div class="text-muted fs-8">{{ $sj->company_name ?? 'Company' }} · {{ $sj->location ?? 'Location' }}</div>
						</div>
					</a>
					@empty
					@foreach(['Dispatch Coordinator','Forklift Operator','Logistics Team Leader'] as $i => $t)
					<a href="{{ route('jobs.index') }}" class="jp-similar-item text-decoration-none">
						<div class="logo-sm">{{ ['DX','FO','LT'][$i] }}</div>
						<div>
							<div class="fw-bold fs-7 text-gray-900">{{ $t }}</div>
							<div class="text-muted fs-8">{{ ['Coles Group','Linfox','Toll Group'][$i] }} · {{ ['Sydney','Brisbane','Melbourne'][$i] }}</div>
						</div>
					</a>
					@endforeach
					@endforelse
				</div>
			</div>

		</div>
	</div>
</div>

@endsection