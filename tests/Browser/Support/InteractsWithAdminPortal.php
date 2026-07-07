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
                ->pause(500);
        }

        return $browser;
    }
}
