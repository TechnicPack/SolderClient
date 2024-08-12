<?php

namespace TechnicPack\SolderClient\Tests;

use TechnicPack\SolderClient\Exception\BadJSONException;
use TechnicPack\SolderClient\Exception\ConnectionException;
use TechnicPack\SolderClient\Exception\InvalidURLException;
use TechnicPack\SolderClient\Exception\ResourceException;
use TechnicPack\SolderClient\Exception\UnauthorizedException;
use TechnicPack\SolderClient\SolderClient;

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

        SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $this->expectNotToPerformAssertions();
    }

    public function testGetModpacks()
    {
        $body = '{"modpacks":{"vanilla":"Vanilla","hexxit":"Hexxit"},"mirror_url":"https://mirror.technicpack.net/Technic/"}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], $body),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $modpacks = $client->getModpacks();

        $this->assertCount(2, $modpacks);
        $this->assertArrayHasKey('hexxit', $modpacks);
    }

    public function testMalformedGetModpacks1()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], '{"modpacks":"invalid"}'),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $this->expectException(ResourceException::class);
        $client->getModpacks();
    }

    public function testMalformedGetModpacks2()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], '[]'),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $this->expectException(ResourceException::class);
        $client->getModpacks();
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
        $body = '{"name":"hexxit","display_name":"Hexxit","url":null,"icon":null,"logo":null,"background":null,"recommended":"1.0.10","latest":"1.0.10","builds":["1.0.0","1.0.1","1.0.3","1.0.4","1.0.5","1.0.6","1.0.7","1.0.8","1.0.9","1.0.10","2.0.0","2.0.1","2.0.1b","2.0.1c"]}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], $body),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $modpack = $client->getModpack('hexxit');

        $this->assertObjectHasProperty('name', $modpack);
        $this->assertObjectHasProperty('display_name', $modpack);
        $this->assertObjectHasProperty('url', $modpack);
        $this->assertObjectHasProperty('recommended', $modpack);
        $this->assertObjectHasProperty('latest', $modpack);
        $this->assertObjectHasProperty('builds', $modpack);
        $this->assertObjectHasProperty('builds', $modpack);
        $this->assertIsArray($modpack->builds);
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
        $body = '{"minecraft":"1.5.2","forge":null,"java":null,"memory":null,"mods":[{"name":"armorbar","version":"v0.7.1","md5":"f323a8d582302ea0abd615a223f8a68b","url":"https://mirror.technicpack.net/Technic/mods/armorbar/armorbar-v0.7.1.zip"}]}';

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"valid":"Key Validated.","name":"SolderClientTest","created_at":"2016-12-26T11:33:46.000Z"}'),
            new Response(200, ['Content-Length' => 0], $body),
        ]);

        $handler = HandlerStack::create($mock);

        $client = SolderClient::factory('http://localhost/api/', 'C3gy35Um2pBE97xn90z0sUNhH1KbzI99', [], $handler);

        $build = $client->getBuild('hexxit', '1.0.1');

        $this->assertObjectHasProperty('forge', $build);
        $this->assertObjectHasProperty('minecraft', $build);
        $this->assertObjectHasProperty('java', $build);
        $this->assertObjectHasProperty('memory', $build);
        $this->assertObjectHasProperty('mods', $build);
        $this->assertIsArray($build->mods);
    }

    public function testBadPack()
    {
        $this->expectException(ConnectionException::class);
        SolderClient::factory('https://solder.example.net/api/', '', [], []);
    }
}
