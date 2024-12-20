<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Firefox\FirefoxOptions;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new FirefoxOptions())->addArguments([
            '--disable-gpu',
            '--headless', 
        ]);

        return RemoteWebDriver::create(
            'http://localhost:4444', 
            DesiredCapabilities::firefox()->setCapability(FirefoxOptions::CAPABILITY, $options)
        );
    }
}

