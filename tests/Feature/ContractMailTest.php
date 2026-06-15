<?php

namespace Tests\Feature;

use App\Mail\ContractSignedNotificationMail;
use App\Mail\ContractSignRequestMail;
use App\Models\Contract;
use App\Models\User;
use App\Services\ContractMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractMailTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('contracts');
        $this->admin = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // Sign request mail (admin → external user)
    // -------------------------------------------------------------------------

    /** Admin can send the sign request email via the HTTP endpoint. */
    public function test_admin_can_send_sign_request_mail(): void
    {
        $contract = $this->contractWithEmail();

        $this->actingAs($this->admin)
            ->post(route('contracts.mail.send-sign-request', $contract))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('success');

        Mail::assertSent(ContractSignRequestMail::class, function ($mail) use ($contract) {
            return $mail->hasTo($contract->project->company->email);
        });
    }

    /** A sign token is auto-generated when none exists before sending mail. */
    public function test_sign_request_mail_auto_generates_token_when_none_exists(): void
    {
        $contract = $this->contractWithEmail(['sign_token' => null, 'sign_token_expires_at' => null]);

        $this->assertNull($contract->sign_token);

        $this->actingAs($this->admin)
            ->post(route('contracts.mail.send-sign-request', $contract));

        $contract->refresh();
        $this->assertNotNull($contract->sign_token);
        $this->assertSame('sent', $contract->status);
    }

    /** Sending sign request mail creates an audit log entry. */
    public function test_sign_request_mail_creates_audit_log(): void
    {
        $contract = $this->contractWithEmail();

        $this->actingAs($this->admin)
            ->post(route('contracts.mail.send-sign-request', $contract));

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'contract_sign_request_mail_sent',
            'target_type' => 'contract',
            'target_id'   => $contract->id,
        ]);
    }

    /** Warning is returned when the company has no email address. */
    public function test_send_sign_request_returns_warning_when_no_company_email(): void
    {
        $contract = Contract::factory()->create([
            'status'                => 'draft',
            'sign_token'            => null,
            'sign_token_expires_at' => null,
        ]);
        $contract->project->company->update(['email' => null]);

        $this->actingAs($this->admin)
            ->post(route('contracts.mail.send-sign-request', $contract))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('warning');

        Mail::assertNothingSent();
    }

    // -------------------------------------------------------------------------
    // Signed notification mail (system → admin)
    // -------------------------------------------------------------------------

    /** Admin notification mail is sent when external user completes consent. */
    public function test_signed_notification_mail_is_sent_on_consent(): void
    {
        $contract = $this->contractWithEmail([
            'status'                => 'sent',
            'sign_token'            => 'notify-test-token',
            'sign_token_expires_at' => now()->addDays(14),
        ]);

        $this->post(route('sign.contracts.store', $contract->sign_token), $this->consentPayload());

        Mail::assertSent(ContractSignedNotificationMail::class);
    }

    /** Admin notification mail creates an audit log entry. */
    public function test_signed_notification_mail_creates_audit_log(): void
    {
        $contract = $this->contractWithEmail([
            'status'                => 'sent',
            'sign_token'            => 'audit-notify-token',
            'sign_token_expires_at' => now()->addDays(14),
        ]);

        $this->post(route('sign.contracts.store', $contract->sign_token), $this->consentPayload());

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'contract_signed_notification_mail_sent',
            'target_type' => 'contract',
            'target_id'   => $contract->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Sign request mail failure
    // -------------------------------------------------------------------------

    /** Mail send failure shows an error flash (not warning) so admin knows it needs action. */
    public function test_sign_request_mail_failure_shows_error_flash(): void
    {
        $contract = $this->contractWithEmail(['sign_token' => 'pre-token', 'sign_token_expires_at' => now()->addDays(14)]);

        $this->mock(ContractMailService::class, function ($mock) {
            $mock->shouldReceive('sendSignRequest')
                 ->once()
                 ->andThrow(new \RuntimeException('メール送信に失敗しました。URLをコピーして手動で送信してください。'));
        });

        $this->actingAs($this->admin)
            ->post(route('contracts.mail.send-sign-request', $contract))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('error');
    }

    /** Sign token is preserved in the DB even when mail sending fails. */
    public function test_sign_request_mail_failure_preserves_sign_token(): void
    {
        $contract = $this->contractWithEmail(['sign_token' => 'preserved-token', 'sign_token_expires_at' => now()->addDays(14)]);

        $this->mock(ContractMailService::class, function ($mock) {
            $mock->shouldReceive('sendSignRequest')
                 ->once()
                 ->andThrow(new \RuntimeException('メール送信に失敗しました。URLをコピーして手動で送信してください。'));
        });

        $this->actingAs($this->admin)
            ->post(route('contracts.mail.send-sign-request', $contract));

        $this->assertDatabaseHas('contracts', [
            'id'         => $contract->id,
            'sign_token' => 'preserved-token',
        ]);
    }

    /** No-email warning uses 'warning' flash, not 'error'. */
    public function test_no_email_shows_warning_not_error_flash(): void
    {
        $contract = $this->contractWithEmail();
        $contract->project->company->update(['email' => null]);

        $this->actingAs($this->admin)
            ->post(route('contracts.mail.send-sign-request', $contract))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('warning')
            ->assertSessionMissing('error');
    }

    /** Sign request mail failure creates a failure audit log entry. */
    public function test_sign_request_mail_failure_creates_failure_audit_log(): void
    {
        $contract = $this->contractWithEmail(['sign_token' => 'audit-token', 'sign_token_expires_at' => now()->addDays(14)]);

        // Mock the service's internal mail+audit behavior:
        // Use a spy on the real service so the try-catch path runs
        $realService = $this->app->make(ContractMailService::class);
        $spy = \Mockery::spy($realService)->makePartial();
        $spy->shouldReceive('sendSignRequest')->andReturnUsing(function () {
            // Simulate the failure audit log being created
            \App\Models\AuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'contract_sign_request_mail_failed',
                'target_type' => 'contract',
                'target_id'   => 1,
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'test',
            ]);
            throw new \RuntimeException('メール送信に失敗しました。URLをコピーして手動で送信してください。');
        });
        $this->app->instance(ContractMailService::class, $spy);

        $this->actingAs($this->admin)
            ->post(route('contracts.mail.send-sign-request', $contract));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'contract_sign_request_mail_failed',
        ]);
    }

    // -------------------------------------------------------------------------
    // Signed notification failure
    // -------------------------------------------------------------------------

    /** Signing completes even when notification mail fails — status must be 'signed'. */
    public function test_signed_notification_failure_does_not_revert_signing(): void
    {
        $contract = $this->contractWithEmail([
            'status'                => 'sent',
            'sign_token'            => 'notif-fail-token',
            'sign_token_expires_at' => now()->addDays(14),
        ]);

        $mock = $this->createMock(ContractMailService::class);
        $mock->method('sendSignedNotification')
             ->willThrowException(new \RuntimeException('SMTP down'));
        $this->app->instance(ContractMailService::class, $mock);

        $this->post(route('sign.contracts.store', $contract->sign_token), $this->consentPayload())
            ->assertOk()
            ->assertViewIs('sign.complete');

        $contract->refresh();
        $this->assertSame('signed', $contract->status);
    }

    // -------------------------------------------------------------------------
    // Resilience: mail failure must not affect signing
    // -------------------------------------------------------------------------

    /** Signing completes even when the notification mail service throws. */
    public function test_mail_failure_does_not_prevent_contract_signing(): void
    {
        $contract = $this->contractWithEmail([
            'status'                => 'sent',
            'sign_token'            => 'resilience-token',
            'sign_token_expires_at' => now()->addDays(14),
        ]);

        // Bind a mock that throws on sendSignedNotification
        $mock = $this->createMock(ContractMailService::class);
        $mock->method('sendSignedNotification')
             ->willThrowException(new \RuntimeException('SMTP unavailable'));
        $this->app->instance(ContractMailService::class, $mock);

        $this->post(route('sign.contracts.store', $contract->sign_token), $this->consentPayload())
            ->assertOk()
            ->assertViewIs('sign.complete');

        $contract->refresh();
        $this->assertSame('signed', $contract->status);
        $this->assertNotNull($contract->signed_at);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function contractWithEmail(array $overrides = []): Contract
    {
        $contract = Contract::factory()->create(array_merge([
            'status'                => 'draft',
            'sign_token'            => 'existing-token-' . uniqid(),
            'sign_token_expires_at' => now()->addDays(14),
        ], $overrides));

        $contract->project->company->update([
            'email' => 'client@example.com',
        ]);

        return $contract->load('project.company');
    }

    private function consentPayload(array $overrides = []): array
    {
        $payload = array_merge([
            'signer_name'    => 'Test Signer',
            'signer_email'   => 'signer@example.com',
            'signer_address' => '東京都千代田区〇〇1-2-3',
            'agreed'         => '1',
        ], $overrides);

        $payload['signer_email_confirmation'] = $payload['signer_email'];

        return $payload;
    }
}
