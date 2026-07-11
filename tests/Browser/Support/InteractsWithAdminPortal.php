<?php

namespace Tests\Browser\Support;

use Laravel\Dusk\Browser;

trait InteractsWithAdminPortal
{
    /**
     * Log into the legacy admin portal (HEADER.php renders the login form
     * inline when no AdminID is in session). Requires make db state
     * (LocalEmployeeSeeder).
     *
     * HEADER.php's login form (website_admin/HEADER.php:138-158) has no
     * `name`/`id` on its submit button, only value="Login" — Dusk's
     * press() matches on value, so we press('Login') rather than the
     * form's id ("Logon").
     *
     * Lands on Home.php rather than AdminPreferences.php: the latter's
     * "not authorized" branch triggers whenever the `?ID=` query param is
     * absent, regardless of login state (website_admin/AdminPreferences.php:9),
     * so it can't distinguish "not logged in" from "no ID given". Home.php's
     * only gate is $_SESSION['AdminID'].
     */
    protected function loginAsPortalAdmin(Browser $browser): Browser
    {
        $browser->visit(env('DUSK_ADMIN_URL', 'http://localhost/admin').'/Home.php');

        if ($browser->element('input[name=Username]')) {
            $browser->type('Username', 'dev.admin')
                ->type('Password', 'password')
                ->press('Login')
                // Home.php only renders #adminID once $_SESSION['AdminID'] is
                // set, so this is a stable signal that login completed
                // (rather than a fixed pause guessing how long that takes).
                ->waitFor('#adminID', 10);
        }

        return $browser;
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
    protected function visitSsoClientsPage(Browser $browser): Browser
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
    protected function waitForVisibleDropdownOption(Browser $browser, string $text): Browser
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

    protected function clickVisibleDropdownOption(Browser $browser, string $text): Browser
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
}
