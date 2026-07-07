<?php

namespace Tests\Browser;

use App\Models\SamlClient;
use App\Models\SsoGrant;
use Laravel\Dusk\Browser;
use Tests\Browser\Support\InteractsWithAdminPortal;
use Tests\DuskTestCase;

class SsoClientsPageTest extends DuskTestCase
{
    use InteractsWithAdminPortal;

    /**
     * Tracks whether the once-per-class cleanup below has already run, so
     * later test methods in this class don't wipe state that an earlier
     * test method in the same run just created (e.g. "dusk-acme").
     */
    private static bool $cleanedStaleState = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$cleanedStaleState) {
            return;
        }

        self::$cleanedStaleState = true;

        // Idempotency: these tests create/mutate a "dusk-acme" client and
        // grants on org 933 (local-idp's org). Without this cleanup, a
        // second `make dusk` run (without an intervening `make db`) hits
        // "slug already taken" and stale grant state from the previous run.
        SamlClient::where('slug', 'dusk-acme')->delete();
        SsoGrant::where('organization_id', 933)->delete();
    }

    private function visitPage(Browser $browser): Browser
    {
        $this->loginAsPortalAdmin($browser);

        return $browser->visit(env('DUSK_ADMIN_URL', 'http://localhost/admin').'/SSOClients.php')
            ->waitFor('#ssoClients tbody tr', 10);
    }

    public function test_lists_the_seeded_client(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser)
                ->assertSeeIn('#ssoClients', 'local-idp')
                ->assertSeeIn('#ssoClients', 'example.com');
        });
    }

    public function test_create_shows_validation_errors_then_succeeds(): void
    {
        $this->browse(function (Browser $browser) {
            $page = $this->visitPage($browser);

            // Duplicate slug -> inline 422 field error from the API's bag
            $page->type('name', 'Dupe')
                ->type('slug', 'local-idp')
                ->type('organization_id', '933')
                ->press('Save')
                ->waitForTextIn('.fieldError[data-for=slug]', 'taken', 10);

            // Unique slug -> created, appears in the table, detail panel opens
            $page->type('slug', 'dusk-acme')
                ->type('[name=email_domains]', 'dusk-acme.test')
                ->press('Save')
                ->waitForTextIn('#saveMsg', 'Created', 10)
                // loadClients() re-renders the table asynchronously after
                // the "Created" message is set (a separate `list` request),
                // so wait for the new row rather than asserting immediately.
                ->waitForTextIn('#ssoClients', 'dusk-acme', 10)
                ->assertVisible('#detailPanel');
        });
    }

    public function test_enable_disable_round_trip(): void
    {
        $this->browse(function (Browser $browser) {
            $page = $this->visitPage($browser);

            $row = '#ssoClients tr[data-slug=dusk-acme]';
            $page->click($row.' .toggleClient')
                ->acceptDialog()
                ->waitForTextIn('#saveMsg', 'enabled', 10);
        });
    }

    public function test_grants_panel_adds_and_removes(): void
    {
        $this->browse(function (Browser $browser) {
            $page = $this->visitPage($browser);

            // local-idp's org is 933; dev-sso@example.test belongs to it via
            // seeding (DepartmentID 3 -> SSO Department -> Organization 933).
            // dev@example.test does NOT belong to org 933 (its department is
            // a randomly-factoried one), so it can't be used here — the
            // grants API rejects logins whose department doesn't match the
            // client's organization_id (SsoGrantController::replace).
            $page->click('#ssoClients tr[data-slug=local-idp] .editClient')
                ->waitFor('#detailPanel', 10)
                ->type('#grantLogin', 'dev-sso@example.test')
                ->click('#addGrant')
                ->waitForTextIn('#grantsList', 'dev-sso@example.test', 10)
                ->click('#grantsList .removeGrant')
                ->waitUntilMissingText('dev-sso@example.test', 10);
        });
    }
}
