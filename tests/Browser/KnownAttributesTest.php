<?php

namespace Tests\Browser;

use App\Models\SamlAttributeObservation;
use App\Models\SamlClient;
use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\Browser\Support\InteractsWithAdminPortal;
use Tests\DuskTestCase;

/**
 * End-to-end proof of the known-attribute capture path: drive a REAL
 * local-idp SAML login (kristophjunge mock IdP asserts `uid`, `email`, and
 * `eduPersonAffiliation` for user1 — see LocalSamlClientSeeder), then confirm
 * the captured attribute name surfaces both in the database
 * (saml_attribute_observations) and in the portal's routing-rule attribute
 * dropdown / known-attributes strip. Requires `make db` state (fresh seed,
 * local-idp enabled, its known_attributes/observations empty).
 */
class KnownAttributesTest extends DuskTestCase
{
    use InteractsWithAdminPortal;

    private function idpUrl(): string
    {
        return rtrim(env('APP_URL', 'http://localhost:8090'), '/');
    }

    protected function tearDown(): void
    {
        // Unconditional cleanup so reruns are idempotent, even on failure:
        // delete the JIT'd Users row (NOT the Employees row user1@example.com
        // also occupies) and reset local-idp's captured attributes back to
        // make-db state.
        User::where('Login', 'user1@example.com')->delete();

        $client = SamlClient::where('slug', 'local-idp')->first();
        if ($client) {
            $client->known_attributes = [];
            $client->save();

            SamlAttributeObservation::where('saml_client_id', $client->id)->delete();
        }

        parent::tearDown();
    }

    public function test_real_saml_login_captures_attribute_name_and_surfaces_it_in_portal(): void
    {
        // --- Step 1: drive the REAL SAML flow through the mock IdP ---
        $this->browse(function (Browser $browser) {
            $browser->visit($this->idpUrl().'/saml/local-idp/login')
                // kristophjunge simplesamlphp login form
                ->waitFor('#username', 10)
                ->type('#username', 'user1')
                ->type('#password', 'user1pass')
                ->press('Login')
                ->waitForLocation('/finishAccountCreation', 15);
        });

        $this->assertDatabaseHas('saml_attribute_observations', [
            'name' => 'eduPersonAffiliation',
        ]);

        // --- Step 2: confirm the captured name surfaces in the portal ---
        $this->browse(function (Browser $browser) {
            $page = $this->visitSsoClientsPage($browser);

            $row = $page->driver->findElement(WebDriverBy::xpath("//tr[contains(., 'local-idp')]"));
            $row->click();

            $page->waitFor('.el-dialog', 10)
                ->waitForText('Department rules', 10);

            // The known-attributes strip renders one el-tag per captured
            // name (SSOClientsApp.vue's `form.known_attributes` loop).
            $page->waitForText('eduPersonAffiliation', 10);

            // The routing attribute select is fed by the same
            // known_attributes list — open the department-rule row's
            // (freshly added, so the only one present) attribute select and
            // confirm the captured name is offered as an option.
            $page->press('Add department rule');

            $page->script("var selects = document.querySelectorAll('.el-dialog input.el-input__inner[placeholder=\"attribute\"]'); selects[selects.length - 1].click();");
            $this->waitForVisibleDropdownOption($page, 'eduPersonAffiliation');
        });
    }
}
