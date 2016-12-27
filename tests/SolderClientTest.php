<?php

namespace TechnicPack\Tests;

use TechnicPack\Exception\BadJsonException;
use TechnicPack\Exception\ConnectionException;
use TechnicPack\Exception\InvalidURLException;
use TechnicPack\Exception\UnauthorizedException;
use TechnicPack\SolderClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ClientTest extends TestCase
{

    public function testAPIValidation()
    {
        $this->expectException(InvalidURLException::class);
        SolderClient::factory('http://localhost/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI');
    }

    public function testInvalidKey()
    {

        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"error": "Key does not exist"}'),
        ]);

        $handler = HandlerStack::create($mock);

        $this->expectException(UnauthorizedException::class);
        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI', [], $handler);
    }

    public function testSolderNotFound()
    {
        $mock = new MockHandler([
            new Response(404, ['Content-Length' => 0], ''),
        ]);

        $handler = HandlerStack::create($mock);

        $this->expectException(ConnectionException::class);
        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);
    }

    public function testMalformedJSON()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.""name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
        ]);

        $handler = HandlerStack::create($mock);

        $this->expectException(BadJsonException::class);
        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);
    }

    public function testClientSetup()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $this->assertTrue($client != null, 'Solder Client failed to initialize');
    }

    public function testGetModpacks()
    {
        $body = '{"modpacks":{"vanilla":"Vanilla","hexxit":"Hexxit"},"mirror_url":"http://mirror.technicpack.net/Technic/"}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], $body),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $modpacks = $client->getModpacks();

        $this->assertEquals(2, count($modpacks));
        $this->assertTrue(array_key_exists('hexxit', $modpacks));
    }

}