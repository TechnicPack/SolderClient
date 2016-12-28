<?php

namespace TechnicPack;

use TechnicPack\Exception\BadJSONException;
use TechnicPack\Exception\ConnectionException;
use TechnicPack\Exception\ResourceException;
use TechnicPack\Resources\Modpack;
use TechnicPack\Resources\Build;

use TechnicPack\Exception\InvalidURLException;
use TechnicPack\Exception\UnauthorizedException;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


class SolderClient
{
    public $url;
    public $key;
    /** @var Client */
    private $client;

    const VERSION = '1.1.0';

    public static function factory($url, $key, $headers = [], $handler = null, $timeout = 3)
    {
        $client = null;
        $url = self::validateUrl($url);
        if (!$headers)
            $headers = ['User-Agent' => self::setupAgent()];
        if (!$handler)
            $client = new Client(['base_uri' => $url, 'timeout' => $timeout, 'headers' => $headers]);
        else
            $client = new Client(['base_uri' => $url, 'timeout' => $timeout, 'headers' => $headers, 'handler' => $handler]);

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
            $response = $this->client->get($uri);
        } catch (RequestException $e) {
            throw new ConnectionException('Request to \'' . $uri . '\' failed.', $e->getCode(), RequestException::class);
        }

        $status_code = $response->getStatusCode();
        $reason = $response->getReasonPhrase();

        if ($status_code >= 300) {
            throw new ConnectionException('Request to \'' . $uri . '\' failed.' . $reason, $status_code);
        }

        $body = $response->getBody();
        $json = json_decode($body, true);

        if (!$json){
            throw new BadJSONException('Failed to decode JSON for \''. $uri, 500);
        }

        return $json;
    }

    public function getModpacks($recursive = false)
    {
        $uri = 'modpack?k=' . $this->key;
        if ($recursive) {
            $uri = 'modpack?include=full&k=' . $this->key;
        }

        $modpacks = $this->handle($uri)['modpacks'];
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
        $uri = 'modpack/'.$modpack;
        $response = $this->handle($uri);

        if (array_key_exists('error', $response) || array_key_exists('status', $response))
        {
            if ($response['error'] == 'Modpack does not exist' || $response['status'] == '404') {
                throw new ResourceException('Modpack does not exist', 404);
            } else if ($response['error'] == 'You are not authorized to view this modpack.' || $response['status'] == '401') {
                throw new UnauthorizedException('You are not authorized to view this modpack.', 401);
            }
        }
        return new Modpack($response);
    }

    public function getBuild($modpack, $build)
    {
        $uri = 'modpack/'.$modpack.'/'.$build.'?include=mods';
        $response = $this->handle($uri);

        if (array_key_exists('error', $response) || array_key_exists('status', $response))
        {
            if ($response['error'] == 'Build does not exist' || $response['status'] == '404') {
                throw new ResourceException('Build does not exist', 404);
            } else if ($response['error'] == 'You are not authorized to view this build.' || $response['status'] == '401') {
                throw new UnauthorizedException('You are not authorized to view this build.', 401);
            }
        }

        return new Build($response);
    }

    public static function validateUrl($url)
    {

        if (!preg_match("/\/api\/?$/", $url)) {
            throw new InvalidURLException('You must include api/ at the end of your URL');
        }

        if (preg_match("/\/api$/", $url)) {
            $url = $url.'/';
        }

        return $url;
    }

    public static function validateKey(Client $client, $key)
    {
        try {
            $response = $client->get('verify/' . $key);
        } catch (RequestException $e) {
            throw new ConnectionException('Request to verify Solder API failed. HTTP returned ' . $e->getResponse()->getStatusCode());
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
        return 'TechnicSolder/' . self::VERSION;
    }
}