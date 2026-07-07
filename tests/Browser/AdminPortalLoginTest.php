<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Support\InteractsWithAdminPortal;
use Tests\DuskTestCase;

class AdminPortalLoginTest extends DuskTestCase
{
    use InteractsWithAdminPortal;

    public function test_seeded_admin_can_log_into_the_portal(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPortalAdmin($browser)
                ->assertDontSee('not authorized to view this page')
                ->assertSee('Dev Admin');
        });
    }
}
