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
        return array_merge([
            'signer_name'  => 'Test Signer',
            'signer_email' => 'signer@example.com',
            'agreed'       => '1',
        ], $overrides);
    }
}
