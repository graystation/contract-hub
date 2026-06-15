<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractSigningTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('contracts');
        $this->admin = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // Sign URL generation (admin side)
    // -------------------------------------------------------------------------

    /** Admin can generate a sign URL for a contract. */
    public function test_admin_can_generate_sign_url(): void
    {
        $contract = Contract::factory()->create(['status' => 'draft']);

        $this->actingAs($this->admin)
            ->post(route('contracts.sign-url.generate', $contract))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('success');

        $contract->refresh();
        $this->assertNotNull($contract->sign_token);
        $this->assertNotNull($contract->sign_token_expires_at);
    }

    /** Status changes to 'sent' when sign URL is generated. */
    public function test_sign_url_generation_sets_status_to_sent(): void
    {
        $contract = Contract::factory()->create(['status' => 'draft']);

        $this->actingAs($this->admin)
            ->post(route('contracts.sign-url.generate', $contract));

        $this->assertDatabaseHas('contracts', [
            'id'     => $contract->id,
            'status' => 'sent',
        ]);
    }

    /** Sign URL generation is recorded in the audit log. */
    public function test_sign_url_generation_creates_audit_log(): void
    {
        $contract = Contract::factory()->create(['status' => 'draft']);

        $this->actingAs($this->admin)
            ->post(route('contracts.sign-url.generate', $contract));

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'contract_sign_url_generated',
            'target_type' => 'contract',
            'target_id'   => $contract->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // External consent page (GET)
    // -------------------------------------------------------------------------

    /** A valid token shows the consent page. */
    public function test_valid_token_shows_consent_page(): void
    {
        $contract = Contract::factory()->create([
            'status'                => 'sent',
            'sign_token'            => 'valid-token-abc',
            'sign_token_expires_at' => now()->addDays(14),
        ]);

        $this->get(route('sign.contracts.show', 'valid-token-abc'))
            ->assertOk()
            ->assertSee($contract->contract_number);
    }

    /** An invalid / non-existent token shows the error page. */
    public function test_invalid_token_shows_error_page(): void
    {
        $this->get(route('sign.contracts.show', 'nonexistent-token'))
            ->assertOk()
            ->assertViewIs('sign.invalid')
            ->assertSee('URLが見つかりません');
    }

    /** An expired token shows the expired error page. */
    public function test_expired_token_shows_error_page(): void
    {
        Contract::factory()->create([
            'status'                => 'sent',
            'sign_token'            => 'expired-token',
            'sign_token_expires_at' => now()->subDay(),
        ]);

        $this->get(route('sign.contracts.show', 'expired-token'))
            ->assertOk()
            ->assertSee('有効期限');
    }

    /** An already-signed contract shows the signed error page. */
    public function test_signed_contract_shows_already_signed_page(): void
    {
        Contract::factory()->create([
            'status'                => 'signed',
            'sign_token'            => 'signed-token',
            'sign_token_expires_at' => now()->addDays(14),
        ]);

        $this->get(route('sign.contracts.show', 'signed-token'))
            ->assertOk()
            ->assertSee('締結済');
    }

    // -------------------------------------------------------------------------
    // Consent submission (POST) — validation
    // -------------------------------------------------------------------------

    /** signer_name is required. */
    public function test_signer_name_is_required(): void
    {
        $contract = $this->createSignableContract();

        $this->post(route('sign.contracts.store', $contract->sign_token), [
            'signer_name'  => '',
            'signer_email' => 'test@example.com',
            'agreed'       => '1',
        ])->assertSessionHasErrors('signer_name');
    }

    /** signer_email is required and must be a valid email. */
    public function test_signer_email_is_required_and_must_be_valid(): void
    {
        $contract = $this->createSignableContract();

        $this->post(route('sign.contracts.store', $contract->sign_token), [
            'signer_name'  => 'Test User',
            'signer_email' => 'not-an-email',
            'agreed'       => '1',
        ])->assertSessionHasErrors('signer_email');
    }

    /** The agreed checkbox is required. */
    public function test_agreed_checkbox_is_required(): void
    {
        $contract = $this->createSignableContract();

        $this->post(route('sign.contracts.store', $contract->sign_token), [
            'signer_name'  => 'Test User',
            'signer_email' => 'test@example.com',
        ])->assertSessionHasErrors('agreed');
    }

    // -------------------------------------------------------------------------
    // Consent submission (POST) — success
    // -------------------------------------------------------------------------

    /** Successful consent changes contract status to 'signed'. */
    public function test_consent_changes_status_to_signed(): void
    {
        $contract = $this->createSignableContract();

        $this->post(route('sign.contracts.store', $contract->sign_token), $this->consentPayload());

        $this->assertDatabaseHas('contracts', [
            'id'     => $contract->id,
            'status' => 'signed',
        ]);
    }

    /** signed_at is stored after consent. */
    public function test_consent_stores_signed_at(): void
    {
        $contract = $this->createSignableContract();

        $this->post(route('sign.contracts.store', $contract->sign_token), $this->consentPayload());

        $contract->refresh();
        $this->assertNotNull($contract->signed_at);
    }

    /** Signer details (name, email, IP, UA) are all stored. */
    public function test_consent_stores_signer_details(): void
    {
        $contract = $this->createSignableContract();

        $this->post(
            route('sign.contracts.store', $contract->sign_token),
            $this->consentPayload(['signer_name' => 'Alice', 'signer_email' => 'alice@example.com']),
            ['REMOTE_ADDR' => '192.168.1.99'],
        );

        $contract->refresh();
        $this->assertSame('Alice', $contract->signer_name);
        $this->assertSame('alice@example.com', $contract->signer_email);
        $this->assertNotNull($contract->signer_ip_address);
        $this->assertNotNull($contract->signer_user_agent);
    }

    /** Consent is recorded in the audit log. */
    public function test_consent_creates_audit_log(): void
    {
        $contract = $this->createSignableContract();

        $this->post(route('sign.contracts.store', $contract->sign_token), $this->consentPayload());

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'contract_signed_by_external_user',
            'target_type' => 'contract',
            'target_id'   => $contract->id,
        ]);
    }

    /** A PDF ContractFile is saved after consent. */
    public function test_consent_generates_and_stores_pdf(): void
    {
        $contract = $this->createSignableContract();

        $this->post(route('sign.contracts.store', $contract->sign_token), $this->consentPayload());

        $this->assertDatabaseHas('contract_files', [
            'contract_id' => $contract->id,
            'file_type'   => 'pdf',
        ]);
    }

    // -------------------------------------------------------------------------
    // Guard: cannot re-sign / use expired or invalid token
    // -------------------------------------------------------------------------

    /** Consent via an expired token is rejected. */
    public function test_expired_token_cannot_be_used_to_sign(): void
    {
        $contract = Contract::factory()->create([
            'status'                => 'sent',
            'sign_token'            => 'old-token',
            'sign_token_expires_at' => now()->subDay(),
        ]);

        $this->post(route('sign.contracts.store', 'old-token'), $this->consentPayload())
            ->assertOk()
            ->assertSee('有効期限');

        $this->assertDatabaseMissing('contracts', ['id' => $contract->id, 'status' => 'signed']);
    }

    /** Consent via a non-existent token is rejected. */
    public function test_invalid_token_cannot_be_used_to_sign(): void
    {
        $this->post(route('sign.contracts.store', 'totally-fake'), $this->consentPayload())
            ->assertOk()
            ->assertViewIs('sign.invalid');
    }

    /** An already-signed contract cannot be re-signed. */
    public function test_already_signed_contract_cannot_be_resigned(): void
    {
        $contract = Contract::factory()->create([
            'status'                => 'signed',
            'sign_token'            => 'done-token',
            'sign_token_expires_at' => now()->addDays(14),
            'signer_name'           => 'Original Signer',
        ]);

        $this->post(
            route('sign.contracts.store', 'done-token'),
            $this->consentPayload(['signer_name' => 'Second Signer']),
        )->assertOk()->assertSee('締結済');

        $contract->refresh();
        $this->assertSame('Original Signer', $contract->signer_name);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createSignableContract(array $overrides = []): Contract
    {
        return Contract::factory()->create(array_merge([
            'status'                => 'sent',
            'sign_token'            => 'test-sign-token-' . uniqid(),
            'sign_token_expires_at' => now()->addDays(14),
        ], $overrides));
    }

    private function consentPayload(array $overrides = []): array
    {
        return array_merge([
            'signer_name'    => 'Test Signer',
            'signer_email'   => 'signer@example.com',
            'signer_address' => '東京都千代田区〇〇1-2-3',
            'agreed'         => '1',
        ], $overrides);
    }
}
