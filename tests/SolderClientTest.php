<?php

namespace TechnicPack\Tests;

use TechnicPack\Exception\BadJSONException;
use TechnicPack\Exception\ConnectionException;
use TechnicPack\Exception\InvalidURLException;
use TechnicPack\Exception\ResourceException;
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
        SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI', [], $handler);
    }

    public function testInternalServerError()
    {

        $mock = new MockHandler([
            new Response(503, ['Content-Length' => 0], ''),
        ]);

        $handler = HandlerStack::create($mock);

        $this->expectException(ConnectionException::class);
        SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI', [], $handler);
    }

    public function testSolderNotFound()
    {
        $mock = new MockHandler([
            new Response(404, ['Content-Length' => 0], ''),
        ]);

        $handler = HandlerStack::create($mock);

        $this->expectException(ConnectionException::class);
        SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);
    }

    public function testMalformedJSON()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.""name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
        ]);

        $handler = HandlerStack::create($mock);

        $this->expectException(BadJSONException::class);
        SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);
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

    public function testGetModpackDoesNotExist()
    {
        $body = '{"status":404,"error":"Modpack does not exist"}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], $body),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $this->expectException(ResourceException::class);
        $client->getModpack('hexxi');
    }

    public function testGetModpackUnauthorized()
    {
        $body = '{"status":401,"error":"You are not authorized to view this modpack."}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], $body),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $this->expectException(UnauthorizedException::class);
        $client->getModpack('project-tal');
    }

    public function testGetModpack()
    {
        $body = '{"name":"hexxit","display_name":"Hexxit","url":null,"icon":null,"icon_md5":"fb51fd408d5d3bc7eba8d9a820ab555c","logo":null,"logo_md5":"1426f5b19df42eab2cabbaa34823c8c9","background":null,"background_md5":"a99b0c0eb00ac657d29eb9e1f9086033","recommended":"1.0.10","latest":"1.0.10","builds":["1.0.0","1.0.1","1.0.3","1.0.4","1.0.5","1.0.6","1.0.7","1.0.8","1.0.9","1.0.10","2.0.0","2.0.1","2.0.1b","2.0.1c"]}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], $body),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $modpack = $client->getModpack('hexxit');

        $this->assertTrue(array_key_exists('name', $modpack));
        $this->assertTrue(array_key_exists('display_name', $modpack));
        $this->assertTrue(array_key_exists('url', $modpack));
        $this->assertTrue(array_key_exists('icon', $modpack));
        $this->assertTrue(array_key_exists('icon_md5', $modpack));
        $this->assertTrue(array_key_exists('logo', $modpack));
        $this->assertTrue(array_key_exists('logo_md5', $modpack));
        $this->assertTrue(array_key_exists('background', $modpack));
        $this->assertTrue(array_key_exists('recommended', $modpack));
        $this->assertTrue(array_key_exists('latest', $modpack));
        $this->assertTrue(array_key_exists('builds', $modpack));
    }

    public function testGetBuildDoesNotExist()
    {
        $body = '{"status":404,"error":"Build does not exist"}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], $body),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $this->expectException(ResourceException::class);
        $client->getBuild('hexxit', '1.0.2.1');
    }

    public function testGetBuildUnauthorized()
    {
        $body = '{"status":401,"error":"You are not authorized to view this build."}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], $body),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $this->expectException(UnauthorizedException::class);
        $client->getBuild('hexxit', '1.0.2');
    }

    public function testGetBuild()
    {
        $body = '{"minecraft":"1.5.2","forge":null,"java":null,"memory":null,"mods":[{"name":"armorbar","version":"v0.7.1","md5":"f323a8d582302ea0abd615a223f8a68b","url":"http://mirror.technicpack.net/Technic/mods/armorbar/armorbar-v0.7.1.zip"}]}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], $body),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $build = $client->getBuild('hexxit', '1.0.1');

        $this->assertTrue(array_key_exists('minecraft', $build));
        $this->assertTrue(array_key_exists('forge', $build));
        $this->assertTrue(array_key_exists('java', $build));
        $this->assertTrue(array_key_exists('memory', $build));
        $this->assertTrue(array_key_exists('mods', $build));
    }
}