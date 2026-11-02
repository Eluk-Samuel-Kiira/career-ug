use App\Models\Deposit;
use App\Models\Currency;
use App\Models\PaymentSource;
use App\Models\Subscription\PaymentWebhook;
use App\Models\Service\LetterRequest;
use App\Jobs\GenerateLetterJob;
use App\Services\Payment\PaymentTransactionService;
use Illuminate\Support\Str;

// ── Pick the letter you want to "pay for" ─────────────────────
$letter = LetterRequest::where('uuid', '6c6503e2-1e91-450d-972d-b45c09181d7a')->firstOrFail();
// Or: $letter = LetterRequest::find(2);

// Skip if it's already been paid properly
if ($letter->status === 'paid' && $letter->payment_reference) {
    echo "Already paid with ref {$letter->payment_reference}\n";
    return;
}

// ── 1. Resolve currency + source ──────────────────────────────
$currency = Currency::where('code', $letter->currency ?? 'UGX')->firstOrFail();
$source   = PaymentSource::active()->where('slug', 'marzpay')->firstOrFail();

// ── 2. Create the Deposit (what PaymentInitController would have made) ──
$depositRef = (string) Str::uuid();

$deposit = Deposit::create([
    'deposit_ref'         => $depositRef,
    'currency_id'         => $currency->id,
    'amount'              => (int) round($letter->price * 100), // price in cents
    'fee'                 => 0,
    'net_amount'          => (int) round($letter->price * 100),
    'deposit_method'      => 'mobile_money',
    'payment_channel'     => 'mobile_money',
    'source_id'           => $source->id,
    'source_name_manual'  => 'MarzPay',
    'purpose_type'        => 'cover_letter',
    'purpose_reference'   => $letter->uuid,
    'purpose_description' => 'Cover Letter — ' . $letter->job_title,
    'status'              => 'pending',
    'depositor_id'        => $letter->user_id,
    'paid_by_user_id'     => $letter->user_id,
    'depositor_phone'     => $letter->seekerProfile?->phone ?? '+256700000000',
    'depositor_email'     => $letter->seekerProfile?->email ?? 'test@example.com',
    'depositor_name'      => $letter->seekerProfile?->full_name ?? 'Test User',
    'country_code'        => 'UG',
    'created_by'          => $letter->user_id,
]);

echo "Deposit created: {$deposit->deposit_ref}\n";

// ── 3. Simulate the webhook body ──────────────────────────────
$payload = [
    'event_type' => 'collection.completed',
    'transaction' => [
        'reference'    => $depositRef,
        'status'       => 'completed',
        'amount'       => ['raw' => (float) $letter->price],
        'charge'       => ['raw' => 0],
        'net_amount'   => ['raw' => (float) $letter->price],
        'description'  => 'Cover Letter — ' . $letter->job_title,
    ],
    'collection' => [
        'provider_transaction_id' => 'MP-TEST-' . strtoupper(Str::random(10)),
        'failure_reason'          => null,
    ],
];

// ── 4. Persist the webhook audit row (like MarzPayWebhookController does) ──
PaymentWebhook::create([
    'gateway'           => 'marzpay',
    'event_type'        => $payload['event_type'],
    'gateway_reference' => $depositRef,
    'payload'           => $payload,
    'signature'         => null,
    'signature_valid'   => false,
    'status'            => 'received',
]);

// ── 5. Apply the "completed" side-effects (mirror of the webhook) ──
DB::transaction(function () use ($deposit, $payload) {
    $txn        = $payload['transaction'];
    $collection = $payload['collection'];

    $deposit->update([
        'status'           => 'completed',
        'cleared_date'     => now(),
        'reference_number' => $collection['provider_transaction_id'],
        'fee'              => 0,
        'net_amount'       => (int) round($txn['net_amount']['raw'] * 100),
    ]);

    app(PaymentTransactionService::class)->recordDeposit($deposit->fresh());

    // Flip LetterRequest
    LetterRequest::where('uuid', $deposit->purpose_reference)
        ->whereIn('status', ['pending_payment', 'paid'])
        ->update([
            'status'            => 'paid',
            'payment_reference' => $deposit->deposit_ref,
            'paid_at'           => now(),
            'error_message'     => null,
        ]);
});

// ── 6. Mark webhook processed ─────────────────────────────────
PaymentWebhook::where('gateway', 'marzpay')
    ->where('gateway_reference', $depositRef)
    ->latest('id')->first()
    ?->update(['status' => 'processed', 'processed_at' => now()]);

// ── 7. Dispatch generation ────────────────────────────────────
$letter->refresh();

if ($letter->status === 'paid' && empty($letter->generated_at) && empty($letter->error_message)) {
    GenerateLetterJob::dispatch($letter->id)->onQueue('default');
    echo "Dispatched GenerateLetterJob for {$letter->uuid}\n";
}

echo "Done. Letter status: {$letter->status}, ref: {$letter->payment_reference}\n";
echo "Deposit: {$deposit->deposit_ref}\n";