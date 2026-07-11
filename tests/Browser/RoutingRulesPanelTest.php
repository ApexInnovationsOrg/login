<?php

namespace Tests\Browser;

use App\Models\SamlClient;
use App\Models\User;
use App\Saml\SamlClientManager;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\Browser\Support\InteractsWithAdminPortal;
use Tests\DuskTestCase;

/**
 * Round trip: configure a department routing rule through the portal's
 * routing-rules panel on local-idp (org-owned, org 933 — see
 * LocalSamlClientSeeder/ReferenceDataSeeder), then drive the REAL mock-IdP
 * SAML flow and confirm the JIT'd user actually lands in the routed
 * department rather than DepartmentID 0. Requires `make db` state (fresh
 * seed, both mock IdPs enabled, local-idp's rules empty).
 */
class RoutingRulesPanelTest extends DuskTestCase
{
    use InteractsWithAdminPortal;

    private const SSO_DEPARTMENT_ID = 3; // seeded by ReferenceDataSeeder, org 933

    private function idpUrl(): string
    {
        return rtrim(env('APP_URL', 'http://localhost:8090'), '/');
    }

    protected function tearDown(): void
    {
        // Unconditional cleanup so reruns are idempotent, even on failure:
        // delete the JIT'd Users row (NOT the Employees row user1@example.com
        // also occupies) and clear local-idp's rules back to make-db state.
        User::where('Login', 'user1@example.com')->delete();

        $client = SamlClient::where('slug', 'local-idp')->first();
        if ($client) {
            app(SamlClientManager::class)->replaceRoutingRules($client, [], []);
        }

        parent::tearDown();
    }

    public function test_routing_rule_configured_in_portal_places_real_saml_login(): void
    {
        $this->browse(function (Browser $browser) {
            // --- Step 1: add the department rule through the portal panel ---
            $page = $this->visitSsoClientsPage($browser);

            $row = $page->driver->findElement(WebDriverBy::xpath("//tr[contains(., 'local-idp')]"));
            $row->click();

            $page->waitFor('.el-dialog', 10)
                ->waitForText('Department rules', 10);

            // The routing attribute field is a strict el-select fed by
            // known_attributes (Task 5) — local-idp has none coming out of
            // `make db` (no login has happened yet), so the dropdown would
            // be empty. Add the attribute manually via the known-attributes
            // strip first (the documented escape hatch for building rules
            // ahead of a first login) so the select below has an option.
            $knownAttributeInput = '.el-dialog input[placeholder="attribute name — press Enter"]';
            $page->waitFor($knownAttributeInput, 10)
                ->type($knownAttributeInput, 'eduPersonAffiliation')
                ->keys($knownAttributeInput, '{enter}');

            $page->press('Add department rule');

            // local-idp's rules are empty coming out of `make db`, so the
            // freshly added department rule is the only row — identified by
            // its "value" input placeholder rather than a brittle position
            // (the section layout shifts with owner type / rule count).
            $valueInput = '.el-dialog input[placeholder="value"]';
            $deptNameInput = '.el-dialog input[placeholder="Department name"]';

            $page->waitFor($valueInput, 10);

            // The department-rule row's attribute select is the last
            // (freshly added) one on the page — open it and pick the
            // known attribute we just added above.
            $page->script("var selects = document.querySelectorAll('.el-dialog input.el-input__inner[placeholder=\"attribute\"]'); selects[selects.length - 1].click();");
            $this->clickVisibleDropdownOption($page, 'eduPersonAffiliation');

            // Operator select defaults to "equals" already (blankRule()) —
            // it's what we want, so no need to open the dropdown.
            $page->type($valueInput, 'group1')
                ->type($deptNameInput, 'SSO Department');

            $page->press('Save routing rules')
                ->waitForTextIn('.el-message', 'saved', 20);
        });

        $this->assertDatabaseHas('saml_department_rules', [
            'attribute' => 'eduPersonAffiliation',
            'value' => 'group1',
            'department_name' => 'SSO Department',
        ]);

        // --- Step 2: drive the REAL SAML flow through the mock IdP ---
        $this->browse(function (Browser $browser) {
            $browser->visit($this->idpUrl().'/saml/local-idp/login')
                // kristophjunge simplesamlphp login form
                ->waitFor('#username', 10)
                ->type('#username', 'user1')
                ->type('#password', 'user1pass')
                ->press('Login')
                ->waitForLocation('/finishAccountCreation', 15);
        });

        $this->assertDatabaseHas('Users', [
            'Login' => 'user1@example.com',
            'DepartmentID' => self::SSO_DEPARTMENT_ID,
            'CredentialID' => 0,
        ]);
    }
}
