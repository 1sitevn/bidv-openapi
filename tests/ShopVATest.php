<?php

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/



namespace OneSite\BIDV\Tests;

use OneSite\BIDV\BIDVOpenAPIOAuth;
use PHPUnit\Framework\TestCase;

/**
 * ShopVATest
 */
class ShopVATest extends TestCase
{
        
    /**
     * testExample
     * vendor/bin/phpunit --filter testExample tests/ShopVATest.php
     * @return void
     */
    public function testExample()
    {
        $oauth = new BIDVOpenAPIOAuth();

        $response = $oauth->getAccessToken();

        $responseContent = json_decode($response->getBody()->getContents());

        var_dump($responseContent);

        $this->assertTrue(true);
    }
}
