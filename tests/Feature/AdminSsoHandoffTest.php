<?php

namespace Tests\Feature;

use App\Models\SamlClient;
use App\Saml\AdminSsoHandoff;
use App\Saml\SamlLoginRejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminSsoHandoffTest extends TestCase
{
    use RefreshDatabase;

    private AdminSsoHandoff $handoff;

    private SamlClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        config(['saml.replay_store' => 'array', 'saml.admin_portal_url' => 'https://www.apexinnovations.com/admin']);

        $this->handoff = app(AdminSsoHandoff::class);
        $this->client = SamlClient::factory()->adminPortal()->create(['slug' => 'apex-admin']);

        DB::table('Employees')->insert([
            'Email' => 'jane@apexinnovations.com', 'FirstName' => 'Jane', 'LastName' => 'Doe',
            'Password' => md5('p6^8&irrelevant'), 'Active' => 'Y',
            'PasswordLastChanged' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_active_employee_gets_single_use_token(): void
    {
        $url = $this->handoff->initiate($this->client, 'jane@apexinnovations.com');

        $this->assertStringStartsWith('https://www.apexinnovations.com/admin/ssoLogon.php?token=', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame(64, strlen($query['token']));

        $payload = $this->handoff->redeem($query['token']);
        $employeeId = DB::table('Employees')->where('Email', 'jane@apexinnovations.com')->value('ID');
        $this->assertSame(['employee_id' => $employeeId, 'name' => 'Jane Doe'], $payload);

        // single use
        $this->assertNull($this->handoff->redeem($query['token']));
    }

    public function test_unknown_email_is_rejected(): void
    {
        $this->expectException(SamlLoginRejected::class);

        try {
            $this->handoff->initiate($this->client, 'stranger@apexinnovations.com');
        } catch (SamlLoginRejected $e) {
            $this->assertSame('no_employee_match', $e->logContext['reason']);
            throw $e;
        }
    }

    public function test_inactive_employee_is_rejected(): void
    {
        DB::table('Employees')->where('Email', 'jane@apexinnovations.com')->update(['Active' => 'N']);

        $this->expectException(SamlLoginRejected::class);
        $this->handoff->initiate($this->client, 'jane@apexinnovations.com');
    }

    public function test_redeem_of_garbage_token_is_null(): void
    {
        $this->assertNull($this->handoff->redeem('nope'));
    }

    public function test_redemption_is_blocked_by_the_claim_even_if_payload_survives(): void
    {
        $url = $this->handoff->initiate($this->client, 'jane@apexinnovations.com');
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $this->assertNotNull($this->handoff->redeem($query['token']));

        // Simulate the get-then-forget race remnant: re-insert the payload.
        Cache::store('array')->put('admin_sso:token:'.$query['token'], ['employee_id' => 1, 'name' => 'Ghost'], 60);

        $this->assertNull($this->handoff->redeem($query['token']));
    }
}
