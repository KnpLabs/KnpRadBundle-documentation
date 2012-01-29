<?php

namespace Acme\Hello\Frontend\Tests\Controller;

use Knp\Bundle\RadBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/hello/Fabien');

        $this->assertTrue($crawler->filter('html:contains("Hello <em>Fabien</em>")')->count() > 0);
    }
}
