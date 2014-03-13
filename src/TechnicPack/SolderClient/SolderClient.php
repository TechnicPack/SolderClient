<?php

namespace TechnicPack\SolderClient;

use TechnicPack\SolderClient\Modpack\Modpack;
use TechnicPack\SolderClient\Modpack\Build;

use TechnicPack\SolderClient\Exception\InvalidURLException;
use TechnicPack\SolderClient\Exception\UnauthorizedException;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;


class SolderClient
{
    public $url;
    public $key;
    private $client;

    public static function factory($url, $key)
    {
        $url = self::validateUrl($url);
        $client = new Client($url);

        if (!self::validateKey($client, $key)) {
            throw new UnauthorizedException('Key failed to validate.');
        }

        $properties = array(
                    "url" => $url,
                    "key" => $key,
                    "client" => $client
            );

        return new SolderClient($properties);
    }

    public function __construct($properties)
    {
        foreach ($properties as $key => $val) {
            $this->{$key} = $val;
        }
    }

    public function getModpacks($recursive = false)
    {
        try {
            $uri = 'modpack';
            if ($recursive) {
                $uri = 'modpack?include=full';
            }

            $response = $this->client->get($uri)->send()->json();

            $result = array();
            foreach ($response['modpacks'] as $modpack) {
                if (is_array($modpack)) {
                    array_push($result, new Modpack($modpack));
                }
            }

            return $result;
        } catch (ClientErrorResponseException $e) {
            throw new InvalidURLException($e->getMessage());
        }
    }

    public function getModpack($modpack)
    {
        try {
            $uri = 'modpack/'.$modpack;
            $response = $this->client->get($uri)->send()->json();

            return new Modpack($response);
        } catch (ClientErrorResponseException $e) {
            throw new InvalidURLException($e->getMessage());
        }
    }

    public function getBuild($modpack, $build)
    {
        try {
            $uri = 'modpack/'.$modpack.'/'.$build.'?include=mods';
            $response = $this->client->get($uri)->send()->json();

            return new Build($response);
        } catch (ClientErrorResponseException $e) {
            throw new InvalidURLException($e->getMessage());
        }
    }

    public static function validateUrl($url)
    {
        $array = parse_url($url);

        if (!isset($array['path']) || $array['path'] == "/") {
            throw new InvalidURLException('You must include api/ at the end of your URL');
        }

        $path = $array['path'];

        if ($path != "/api/" && $path != "/api") {
            throw new InvalidURLException('You must include api/ at the end of your URL');
        }

        if ($path == "/api") {
            $path = "/api/";
        }

        return $array['scheme'].'://'.$array['host'].$path;
    }

    public static function validateKey(Client $client, $key)
    {
        try {
            $response = $client->get('verify/'.$key)->send()->json();

            if (array_key_exists("valid", $response)) {
                return true;
            }
        } catch (ClientErrorResponseException $e) {
            throw new InvalidURLException($e->getMessage());
        }

        return false;
    }
}