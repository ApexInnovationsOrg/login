<?php

namespace Tests\Browser;

use App\Models\SamlClient;
use App\Models\SsoGrant;
use Facebook\WebDriver\WebDriverBy;
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
        SamlClient::where('slug', 'dusk-system-acme')->delete();
        SsoGrant::where('owner_type', 'organization')->where('owner_id', 933)->delete();
    }

    /**
     * The legacy portal intermittently serves a truncated response for this
     * page under Selenium (observed: HEADER.php's admin-employee lookup
     * throws "table doesn't exist" against the shared MySQL container,
     * unrelated to anything the SSO client feature touches, then falls
     * through to a short error/unauthorized page instead of the Vue-mounted
     * one) — reload once if the table never shows up rather than let one
     * infra blip fail an otherwise-passing test.
     */
    private function visitPage(Browser $browser): Browser
    {
        $this->loginAsPortalAdmin($browser);

        $url = env('DUSK_ADMIN_URL', 'http://localhost/admin').'/SSOClients.php';

        try {
            return $browser->visit($url)->waitFor('.el-table__body-wrapper tbody tr', 15);
        } catch (\Throwable $e) {
            return $browser->visit($url)->waitFor('.el-table__body-wrapper tbody tr', 15);
        }
    }

    /**
     * Element UI appends a fresh `.el-select-dropdown` popper to <body> each
     * time a select opens, but does not remove earlier ones from the DOM —
     * they're just hidden. That makes plain-CSS selectors like
     * `.el-select-dropdown__item` ambiguous (they can match a stale, hidden
     * popper from an earlier interaction) and is why waitFor/waitForTextIn
     * against that class flake here. Poll via JS for a *visible* dropdown
     * item whose text contains $text — sidesteps both the multiple-popper
     * ambiguity and the remote-search debounce race (an empty/loading
     * popper rendering before the real result).
     */
    private function waitForVisibleDropdownOption(Browser $browser, string $text): Browser
    {
        $browser->waitUsing(10, 100, function () use ($browser, $text) {
            return (bool) $browser->script(<<<JS
                return Array.from(document.querySelectorAll('.el-select-dropdown__item')).some(function (el) {
                    var visible = !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
                    return visible && el.innerText.includes('{$text}');
                });
            JS)[0];
        }, "waiting for a visible dropdown option containing \"{$text}\"");

        return $browser;
    }

    private function clickVisibleDropdownOption(Browser $browser, string $text): Browser
    {
        $this->waitForVisibleDropdownOption($browser, $text);

        $browser->script(<<<JS
            var items = Array.from(document.querySelectorAll('.el-select-dropdown__item'));
            var match = items.find(function (el) {
                var visible = !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
                return visible && el.innerText.includes('{$text}');
            });
            if (match) match.click();
        JS);

        return $browser;
    }

    public function test_lists_the_seeded_client(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser)
                ->assertSeeIn('.el-table', 'local-idp')
                ->assertSeeIn('.el-table', 'example.com');
        });
    }

    public function test_create_with_org_picker_and_validation(): void
    {
        $this->browse(function (Browser $browser) {
            $page = $this->visitPage($browser);

            $page->press('New client')
                ->waitFor('.el-dialog', 10)
                ->type('.el-dialog .el-form-item:nth-of-type(1) input', 'Dusk Acme');

            // Org picker: remote-search select — type, wait for a result, choose it.
            // Seeded org names contain "SSO"/"Dev" (e.g. "SSO Organization",
            // "Local Dev Organization") — there is no "Apex"-named org in the
            // local dev seed data, so search on a term that actually matches.
            //
            // Item order in create mode: 1=Name, 2=Slug, 3=Owned by (radio,
            // added by the system-ownership feature), 4=Organization/System.
            $page->click('.el-dialog .el-form-item:nth-of-type(4) .el-select input.el-input__inner')
                ->type('.el-dialog .el-form-item:nth-of-type(4) .el-select input.el-input__inner', 'sso');
            $this->clickVisibleDropdownOption($page, 'SSO Organization');

            // Duplicate slug -> inline error from the field-errors bag.
            $page->type('.el-dialog .el-form-item:nth-of-type(2) input', 'local-idp')
                ->press('Save')
                ->waitFor('.el-form-item__error', 10);

            // Unique slug -> created. Generous wait: this round-trips through
            // the legacy portal's bridge (doSSOClients.php) which has been
            // observed to slow down sharply under occasional load on this
            // shared dev stack.
            $page->type('.el-dialog .el-form-item:nth-of-type(2) input', 'dusk-acme')
                ->press('Save')
                ->waitForTextIn('.el-message', 'Created', 20)
                ->waitForTextIn('.el-table', 'dusk-acme', 20);
        });
    }

    public function test_create_system_owned_client(): void
    {
        $this->browse(function (Browser $browser) {
            $page = $this->visitPage($browser);

            $page->press('New client')
                ->waitFor('.el-dialog', 10)
                ->type('.el-dialog .el-form-item:nth-of-type(1) input', 'Dusk System Acme')
                ->type('.el-dialog .el-form-item:nth-of-type(2) input', 'dusk-system-acme');

            // Owned by: switch to System. Item order in create mode:
            // 1=Name, 2=Slug, 3=Owned by, 4=System (once selected).
            $page->click('.el-dialog .el-form-item:nth-of-type(3) label:nth-of-type(2)');

            // System picker: remote-search select, same idiom as the org
            // picker in the org-owned create test above.
            $page->click('.el-dialog .el-form-item:nth-of-type(4) .el-select input.el-input__inner')
                ->type('.el-dialog .el-form-item:nth-of-type(4) .el-select input.el-input__inner', 'Local');
            $this->clickVisibleDropdownOption($page, 'Local Health System');

            // System-owned clients cannot hold a default department — the
            // form must not render that field at all once System is chosen.
            $page->assertMissing('.el-dialog .el-form-item:nth-of-type(5) .el-select[placeholder="Select a department"]');
            $page->assertDontSeeIn('.el-dialog', 'Default department');

            $page->press('Save')
                ->waitForTextIn('.el-message', 'Created', 20)
                ->waitForTextIn('.el-table', 'dusk-system-acme', 20);

            $row = $page->driver->findElement(WebDriverBy::xpath("//tr[contains(., 'dusk-system-acme')]"));

            $this->assertStringContainsString('Local Health System', $row->getText());
            $this->assertStringContainsString('system', $row->getText());

            $this->assertDatabaseHas('saml_clients', [
                'slug' => 'dusk-system-acme',
                'owner_type' => 'system',
                'owner_id' => 1,
            ]);
        });
    }

    public function test_department_dropdown_follows_org(): void
    {
        $this->browse(function (Browser $browser) {
            $page = $this->visitPage($browser);

            // Edit local-idp (org 933) — department select must offer its
            // departments plus the "no department" option. In edit mode the
            // Slug form-item is hidden (v-if="!editSlug"), and the "Owned by"
            // radio (added by the system-ownership feature) is shown but
            // disabled, so the item order is: 1=Name, 2=Owned by,
            // 3=Organization, 4=Department.
            //
            // The table sorts by name and dusk-acme sorts before local-idp,
            // so ":first-child" is NOT local-idp's row — scope to it by
            // slug via the same XPath row-lookup the enable/disable test
            // below uses, rather than positional selection.
            $row = $page->driver->findElement(WebDriverBy::xpath("//tr[contains(., 'local-idp')]"));

            $row->click();

            $page->waitFor('.el-dialog', 10)
                ->click('.el-dialog .el-form-item:nth-of-type(4) .el-select');

            $this->waitForVisibleDropdownOption($page, 'None — users choose at finish-account');

            $page->assertSee('None — users choose at finish-account');

            $this->waitForVisibleDropdownOption($page, 'SSO Department');

            $page->assertSee('SSO Department');
        });
    }

    public function test_enable_disable_round_trip(): void
    {
        $this->browse(function (Browser $browser) {
            $page = $this->visitPage($browser);

            // New clients are created disabled (SamlClientManager::create
            // forces enabled=false until IdP metadata is applied), while
            // `make db` explicitly enables local-idp — so both "Enable" and
            // "Disable" button text exist on the page simultaneously. This
            // test relies on dusk-acme existing from the previous test
            // (still disabled, per the above) and scopes to its row via an
            // XPath lookup rather than pressing the first global match for
            // "Enable". Poll first: the row exists as soon as the table
            // renders, but WebDriver's findElement() below throws
            // immediately (no implicit wait) if fired a beat too early.
            $page->waitUsing(15, 100, function () use ($page) {
                return (bool) $page->script(<<<'JS'
                    return Array.from(document.querySelectorAll('.el-table__body-wrapper tbody tr'))
                        .some(function (tr) { return tr.innerText.includes('dusk-acme'); });
                JS)[0];
            }, 'waiting for the dusk-acme row');

            $row = $page->driver->findElement(WebDriverBy::xpath("//tr[contains(., 'dusk-acme')]"));

            $row->findElement(WebDriverBy::xpath(".//button[contains(., 'Enable')]"))->click();

            $page->waitFor('.el-message-box', 10)
                ->press('OK')
                ->waitForTextIn('.el-message', 'enabled', 20);
        });
    }

    public function test_grants_panel_adds_and_removes(): void
    {
        $this->browse(function (Browser $browser) {
            $page = $this->visitPage($browser);

            // local-idp's org is 933; dev-sso@example.test belongs to it via
            // seeding, so it's a valid grantee for local-idp's client.
            //
            // The table sorts by name and dusk-acme sorts before local-idp,
            // so ":first-child" is NOT local-idp's row — scope to it by
            // slug via the same XPath row-lookup the enable/disable test
            // uses, rather than positional selection.
            $row = $page->driver->findElement(WebDriverBy::xpath("//tr[contains(., 'local-idp')]"));

            $row->click();

            // The form-level size="small" cascades to every el-select in the
            // dialog (organization AND department pickers also render
            // `.el-select--small`), so that class alone doesn't uniquely
            // identify the grants search box — target it by its distinct
            // placeholder instead.
            $grantInput = '.el-dialog .el-select input.el-input__inner[placeholder="Search users by name or email"]';

            $page->waitFor('.el-dialog', 10)
                ->click($grantInput)
                ->type($grantInput, 'dev-sso');

            $this->clickVisibleDropdownOption($page, 'dev-sso@example.test');

            $page->press('Add')
                ->waitForTextIn('.el-message', 'saved', 20)
                ->waitForTextIn('.el-dialog', 'dev-sso@example.test', 20);

            // Remove: close the tag via its "x" icon, which fires
            // removeGrant() -> saveGrants() again. Scope to grant tags
            // specifically (class hook added in SSOClientsApp.vue) — the
            // dialog also renders el-tags for email domains, and a bare
            // `.el-tag .el-tag__close` selector would ambiguously match
            // those too.
            $page->click('.el-dialog .grant-tag .el-tag__close')
                ->waitForTextIn('.el-message', 'saved', 20)
                ->waitUntilMissingText('dev-sso@example.test', 20);
        });
    }
}
