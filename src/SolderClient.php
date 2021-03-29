<?php

namespace TechnicPack\SolderClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use TechnicPack\SolderClient\Exception\BadJSONException;
use TechnicPack\SolderClient\Exception\ConnectionException;
use TechnicPack\SolderClient\Exception\InvalidURLException;
use TechnicPack\SolderClient\Exception\ResourceException;
use TechnicPack\SolderClient\Exception\UnauthorizedException;
use TechnicPack\SolderClient\Resources\Build;
use TechnicPack\SolderClient\Resources\Modpack;


class SolderClient
{
    public $url;
    public $key;
    /** @var Client */
    private $client;

    const VERSION = '0.2.2';

    public static function factory($url, $key, $headers = [], $handler = null, $timeout = 3)
    {
        $client = null;
        $url = self::validateUrl($url);
        if (!$headers) {
            $headers = ['User-Agent' => self::setupAgent()];
        }
        if (!$handler) {
            $client = new Client(['base_uri' => $url, 'timeout' => $timeout, 'headers' => $headers]);
        } else {
            $client = new Client(['base_uri' => $url, 'timeout' => $timeout, 'headers' => $headers, 'handler' => $handler]);
        }

        if (!self::validateKey($client, $key)) {
            throw new UnauthorizedException('Key failed to validate.', 403);
        }

        $properties = array(
            "url" => $url,
            "key" => $key,
        );

        return new SolderClient($client, $properties);
    }

    protected function __construct($client, $properties)
    {
        $this->client = $client;
        foreach ($properties as $key => $val) {
            $this->{$key} = $val;
        }
    }

    private function handle($uri)
    {
        try {
            $response = $this->client->get($uri . $this->key);
        } catch (TransferException $e) {
            throw new ConnectionException('Request to \'' . $uri . '\' failed. ' . $e->getMessage(), 0, $e);
        }

        $status_code = $response->getStatusCode();
        $reason = $response->getReasonPhrase();

        if ($status_code >= 300) {
            throw new ConnectionException('Request to \'' . $uri . '\' failed. ' . $reason, $status_code);
        }

        $body = $response->getBody();
        $json = json_decode($body, true);

        if ($json === null) {
            throw new BadJSONException('Failed to decode JSON for \'' . $uri . '\'', 500);
        }

        return $json;
    }

    public function getModpacks($recursive = false)
    {
        if ($recursive) {
            $uri = 'modpack?include=full&k=';
        } else {
            $uri = 'modpack?k=';
        }

        $response = $this->handle($uri);

        if (!is_array($response) || !array_key_exists('modpacks', $response) || !is_array($response['modpacks'])) {
            throw new ResourceException('Got an unexpected response from Solder', 500);
        }

        $modpacks = $response['modpacks'];
        $result = [];

        if ($recursive) {
            foreach ($modpacks as $modpack) {
                if (is_array($modpack)) {
                    array_push($result, new Modpack($modpack));
                }
            }
        } else {
            foreach ($modpacks as $slug => $modpack) {
                $result[$slug] = $modpack;
            }
        }

        return $result;
    }

    public function getModpack($modpack)
    {
        $uri = 'modpack/' . $modpack . '?k=';
        $response = $this->handle($uri);

        if (array_key_exists('error', $response) || array_key_exists('status', $response)) {
            if (($response['error'] ?? null) == 'Modpack does not exist' || ($response['status'] ?? null) == '404') {
                throw new ResourceException('Modpack does not exist', 404);
            }
            if (($response['error'] ?? null) == 'You are not authorized to view this modpack.' || ($response['status'] ?? null) == '401') {
                throw new UnauthorizedException('You are not authorized to view this modpack.', 401);
            }
            throw new ResourceException('Got an unexpected response from Solder', 500);
        }

        return new Modpack($response);    }

    public function getBuild($modpack, $build)
    {
        $uri = 'modpack/' . $modpack . '/' . $build . '?include=mods&k=';
        $response = $this->handle($uri);

        if (array_key_exists('error', $response) || array_key_exists('status', $response)) {
            if (($response['error'] ?? null) == 'Build does not exist' || ($response['status'] ?? null) == '404') {
                throw new ResourceException('Build does not exist', 404);
            }
            if (($response['error'] ?? null) == 'You are not authorized to view this build.' || ($response['status'] ?? null) == '401') {
                throw new UnauthorizedException('You are not authorized to view this build.', 401);
            }
            throw new ResourceException('Got an unexpected response from Solder', 500);
        }

        return new Build($response);
    }

    public static function validateUrl($url)
    {

        if (!preg_match("/\/api\/?$/", $url)) {
            throw new InvalidURLException('You must include api/ at the end of your URL');
        }

        if (preg_match("/\/api$/", $url)) {
            $url = $url . '/';
        }

        return $url;
    }

    public static function validateKey(Client $client, $key)
    {
        try {
            $response = $client->get('verify/' . $key);
        } catch (TransferException $e) {
            if (method_exists($e, 'hasResponse') && $e->hasResponse()) {
                throw new ConnectionException('Request to verify Solder API failed. Solder API returned HTTP code ' . $e->getResponse()->getStatusCode(), 0, $e);
            } else {
                throw new ConnectionException('Request to verify Solder API failed. Solder API returned ' . $e->getMessage(), 0, $e);
            }
        }

        $body = $response->getBody();
        $json = json_decode($body, true);

        if ($json) {
            if (array_key_exists("valid", $json)) {
                return true;
            }
        } else {
            throw new BadJSONException('Failed to decode JSON response when verifying API key');
        }

        return false;
    }

    private static function setupAgent()
    {
        return 'SolderClient/' . self::VERSION;
    }
}
