<?php

use App\Jobs\HandleIncomingCallJob;
use App\Jobs\SendCallerSmsJob;
use App\Models\Industry;
use App\Models\MissedCall;
use App\Models\Tenant;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $industry = Industry::factory()->create();
    $this->tenant = Tenant::factory()->create([
        'twilio_phone_number' => '+351210000001',
        'notification_config' => [
            'email' => ['enabled' => true, 'recipients' => ['test@test.com']],
            'sms' => ['enabled' => false, 'recipients' => []],
            'missed_call_sms_template' => 'Obrigado {company_name}: {intake_url}',
            'auto_send_schedule' => [
                'enabled' => true,
                'timezone' => 'Europe/Lisbon',
                'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'start_time' => '00:00',
                'end_time' => '23:59',
            ],
        ],
    ]);
});

test('webhook with dedicated number creates missed call and dispatches job', function () {
    $response = $this->postJson('/api/webhooks/twilio/incoming-call', [
        'From' => '+351923456789',
        'To' => '+351210000001',
        'CallSid' => 'CA12345',
    ]);

    $response->assertNoContent();
    Queue::assertPushed(HandleIncomingCallJob::class);
});

test('handle incoming call job matches tenant via dedicated number', function () {
    $job = new HandleIncomingCallJob(
        callerNumber: '+351923456789',
        toNumber: '+351210000001',
        callSid: 'CA12345',
        forwardedFrom: null,
    );

    $job->handle();

    $missedCall = MissedCall::where('twilio_call_sid', 'CA12345')->first();
    expect($missedCall)->not->toBeNull();
    expect($missedCall->tenant_id)->toBe($this->tenant->id);
    expect($missedCall->matched_by)->toBe('dedicated_number');
    // sms_sent is set by SendCallerSmsJob (queued); assert it was dispatched
    Queue::assertPushed(SendCallerSmsJob::class);
});

test('handle incoming call job skips excluded numbers', function () {
    $this->tenant->excludedNumbers()->create(['phone_number' => '+351923456789']);

    $job = new HandleIncomingCallJob(
        callerNumber: '+351923456789',
        toNumber: '+351210000001',
        callSid: 'CA-excluded',
        forwardedFrom: null,
    );

    $job->handle();

    expect(MissedCall::where('twilio_call_sid', 'CA-excluded')->exists())->toBeFalse();
});

test('handle incoming call job is idempotent', function () {
    MissedCall::create([
        'tenant_id' => $this->tenant->id,
        'caller_number' => '+351923456789',
        'tenant_phone' => '+351210000001',
        'twilio_call_sid' => 'CA-dup',
    ]);

    $job = new HandleIncomingCallJob(
        callerNumber: '+351923456789',
        toNumber: '+351210000001',
        callSid: 'CA-dup',
        forwardedFrom: null,
    );

    $job->handle();

    expect(MissedCall::where('twilio_call_sid', 'CA-dup')->count())->toBe(1);
});
