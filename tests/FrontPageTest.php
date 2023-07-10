<?php

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

use GuzzleHttp\Client;

class FrontPageTest extends TestCase
{
    public function __construct(string $name)
    {
        parent::__construct($name);
        Dotenv::createUnsafeImmutable('./')->load();
    }

    /**
     * @test
     *
     * Fetch the homepage URL, assert that there are no errors.
     */
    public function testStatus()
    {
        $client = new Client(['base_uri' => getEnv('SITE_URL')]);
        try {
            $response = $client->request('GET', '/');
            $this->assertEquals(200, $response->getStatusCode());

        } catch(\GuzzleHttp\Exception\GuzzleException $exception) {
            $this->fail($exception->getMessage());
        }
    }

}
