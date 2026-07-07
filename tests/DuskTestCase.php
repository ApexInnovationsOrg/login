<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * We connect to the standalone selenium/standalone-chrome container
     * (see docker-compose.yml) over the network rather than starting a local
     * chromedriver binary, so there is nothing to prepare here.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        //
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--headless=new',
            '--disable-gpu',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            env('DUSK_DRIVER_URL', 'http://host.docker.internal:4444/wd/hub'),
            DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options)
        );
    }
}
