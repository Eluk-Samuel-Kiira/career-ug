<style>
    /* Promo banners (CV + cover letter) */
    .jp-promo{
        position:relative; display:flex; align-items:center;
        width:100%; height:150px; border-radius:16px; overflow:hidden;
        background:linear-gradient(100deg, #0B1C2E 0%, #13273D 50%, #0F5F57 100%);
        text-decoration:none; box-shadow:0 10px 26px rgba(11,28,46,0.12);
        transition:transform .2s ease, box-shadow .2s ease;
    }
    .jp-promo:hover{ transform:translateY(-2px); box-shadow:0 16px 34px rgba(11,28,46,0.18); }

    .jp-promo-img{
        position:absolute; top:0; right:0; z-index:0;
        width:58%; height:100%; object-fit:cover; object-position:center top;
    }

    /* Desktop: navy fades over the left side of the figure */
    .jp-promo::after{
        content:""; position:absolute; inset:0; z-index:1; pointer-events:none;
        background:linear-gradient(90deg, #13273D 38%, rgba(19,39,61,0) 72%);
    }

    .jp-promo-copy{
        position:relative; z-index:2; max-width:62%;
        padding:20px 24px; display:flex; flex-direction:column; gap:4px; min-width:0;
    }
    .jp-promo-eyebrow{ font-size:.7rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#5EE0C0; }
    .jp-promo-title{ font-size:1.12rem; font-weight:800; color:#fff; line-height:1.25; }
    .jp-promo-text{ font-size:.85rem; color:rgba(255,255,255,.78); line-height:1.4; }
    .jp-promo-cta{
        margin-top:8px; width:fit-content; display:inline-flex; align-items:center; gap:6px;
        background:var(--jp-gradient); color:#fff; font-size:.82rem; font-weight:700;
        padding:8px 16px; border-radius:10px;
    }
    .jp-promo:hover .jp-promo-cta i{ transform:translateX(3px); transition:transform .2s ease; }

    /* Mobile: figure fully visible on the right, text on solid navy at the left */
    @media (max-width: 575.98px){
        .jp-promo{ height:auto; min-height:190px; }

        .jp-promo-img{
            width:62%; height:100%; opacity:1;
            object-fit:cover; object-position:85% center;
        }

        .jp-promo::after{
            background:linear-gradient(90deg,
                #13273D 0%,
                rgba(19,39,61,.9) 28%,
                rgba(19,39,61,0) 46%);
        }

        .jp-promo-copy{ max-width:58%; padding:18px; }
        .jp-promo-eyebrow{ font-size:.65rem; }
        .jp-promo-title{ font-size:1rem; }
        .jp-promo-text{ font-size:.78rem; }
        .jp-promo-cta{ font-size:.78rem; padding:7px 13px; }
    }
</style>
<div class="col-12">
    <div class="d-flex flex-column gap-5 my-4">
        @include('jobs.partials.promo-banner', [
            'href'    => route('cv-review.my'),
            'image'   => 'assets/media/banners/cv-banner.svg',
            'alt'     => 'Job seeker holding a professional CV',
            'eyebrow' => 'CV Tailor & Maker',
            'title'   => 'Your CV, rewritten to get shortlisted.',
            'text'    => 'ATS-friendly. Tailored to any job here. Ready in about 60 seconds.',
            'cta'     => 'Build my CV',
        ])

        @include('jobs.partials.promo-banner', [
            'href'    => route('letters.index'),
            'image'   => 'assets/media/banners/cover-letter-banner.svg',
            'alt'     => 'Applicant writing a cover letter',
            'eyebrow' => 'Cover Letter Generator',
            'title'   => 'Stop starting from a blank page.',
            'text'    => 'A cover letter written for any role listed, in one minute.',
            'cta'     => 'Write my cover letter',
        ])
    </div>
</div>