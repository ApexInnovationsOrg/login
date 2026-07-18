<?php

namespace Tests\Browser;

use App\Models\SamlClient;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Full admin-portal SSO round trip against mock-idp-admin. Requires
 * `make db` state with the mock-idp-admin container up (local-admin-idp
 * enabled) — same convention as the other Browser suites.
 */
class AdminPortalSsoTest extends DuskTestCase
{
    private function adminUrl(): string
    {
        return env('DUSK_ADMIN_URL', 'http://localhost/admin');
    }

    private function setClientEnabled(bool $enabled): void
    {
        SamlClient::where('slug', 'local-admin-idp')->update(['enabled' => $enabled]);
    }

    protected function tearDown(): void
    {
        $this->setClientEnabled(true); // leave make-db state behind for reruns

        parent::tearDown();
    }

    public function test_sso_button_logs_admin_in_through_mock_idp(): void
    {
        $this->setClientEnabled(true);

        $this->browse(function (Browser $browser) {
            $browser->visit($this->adminUrl().'/doLogoff.php') // fresh session
                ->visit($this->adminUrl().'/Home.php')
                ->waitForText('Sign in with SSO', 10)
                ->clickLink('Sign in with SSO')
                // kristophjunge simplesamlphp login form
                ->waitFor('#username', 10)
                ->type('#username', 'user1')
                ->type('#password', 'user1pass')
                ->press('Login')
                // Home.php renders #adminID only with $_SESSION['AdminID'] set
                ->waitFor('#adminID', 15)
                ->assertSee('Mock IdPAdmin');
        });
    }

    public function test_disabled_flag_hides_button_and_404s_login(): void
    {
        $this->setClientEnabled(false);

        $this->browse(function (Browser $browser) {
            $browser->visit($this->adminUrl().'/doLogoff.php') // fresh session: SSOAvailable cache lives in it
                ->visit($this->adminUrl().'/Home.php')
                ->waitFor('#Logon', 10)
                ->assertDontSee('Sign in with SSO');

            $browser->visit(rtrim(env('APP_URL', 'http://localhost:8090'), '/').'/saml/local-admin-idp/login')
                ->assertSee('404');
        });
    }
}
