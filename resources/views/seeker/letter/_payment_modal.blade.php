{{-- ── PAYMENT MODAL — same as cv-review ─────────────────────── --}}
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold mb-0 fs-4">Complete Payment</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary"
                        data-bs-dismiss="modal" id="paymentModalClose">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </button>
            </div>

            <div class="modal-body">

                {{-- Amount summary --}}
                <div class="bg-light-primary rounded p-3 p-md-4 mb-4 mb-md-5 d-flex justify-content-between align-items-center gap-2">
                    <div class="min-w-0">
                        <div class="text-muted fs-8">You're paying for</div>
                        <div class="fw-bold fs-6 fs-md-5 text-truncate">{{ $letter['letter_type_label'] }}</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="text-muted fs-8">Amount</div>
                        <div class="fw-bold fs-5 fs-md-4 text-primary" id="pm_amount">{{ $payAmountFormatted }}</div>
                    </div>
                </div>

                {{-- Method picker --}}
                <label class="fw-semibold fs-7 mb-2">Pay with</label>
                <div class="d-flex flex-column gap-2 mb-4 mb-md-5">
                    <label class="d-flex align-items-center gap-2 gap-md-3 p-3 border rounded cursor-pointer pm-method-option">
                        <input type="radio" name="pm_method" value="mobile_money" class="form-check-input mt-0 flex-shrink-0" checked>
                        <i class="ki-duotone ki-phone fs-2 text-primary flex-shrink-0">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold fs-7">Mobile Money</div>
                            <div class="text-muted fs-8">MTN or Airtel — you'll receive a prompt on your phone</div>
                        </div>
                    </label>

                    <label class="d-flex align-items-center gap-2 gap-md-3 p-3 border rounded cursor-pointer pm-method-option">
                        <input type="radio" name="pm_method" value="card" class="form-check-input mt-0 flex-shrink-0">
                        <i class="ki-duotone ki-credit-cart fs-2 text-success flex-shrink-0">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold fs-7">Card</div>
                            <div class="text-muted fs-8">Visa or Mastercard — redirect to a secure page</div>
                        </div>
                    </label>
                </div>

                {{-- Phone input (mobile money only) --}}
                <div id="pm_phone_wrap" class="mb-4">
                    <label class="fw-semibold fs-7 mb-2">Mobile Money Number</label>
                    <input type="tel"
                           id="pm_phone"
                           class="form-control form-control-lg"
                           placeholder="+256 754 428 612"
                           autocomplete="tel">
                    <div class="text-muted fs-8 mt-1">
                        Leave blank to use the number on your profile.
                    </div>
                </div>

                {{-- Credit / balance warning --}}
                <div class="alert alert-light-warning d-flex align-items-start mb-0" id="pm_warning">
                    <i class="ki-duotone ki-information-5 fs-3 text-warning me-2 mt-1 flex-shrink-0">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="fs-7" id="pm_warning_text">
                        <strong>Make sure you have enough mobile money credit.</strong>
                        Your balance must cover the amount above before you can approve the PIN prompt.
                        If the payment fails, contact support.
                    </div>
                </div>

                {{-- Error message --}}
                <div id="pm_error" class="alert alert-light-danger mt-4 mb-0 d-none"></div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="pm_submit_btn">
                    <span class="indicator-label">
                        <i class="ki-duotone ki-lock fs-3 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Pay Now
                    </span>
                    <span class="indicator-progress">
                        Processing... <span class="spinner-border spinner-border-sm ms-2"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>