<?php

namespace TechnicPack;

use TechnicPack\Exception\BadJsonException;
use TechnicPack\Exception\ConnectionException;
use TechnicPack\Modpack\Modpack;
use TechnicPack\Modpack\Build;

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
            throw new UnauthorizedException('Key failed to validate.');
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
            throw new ConnectionException($e->getMessage());
        }

        $result = [];
        $result['status_code'] = $response->getStatusCode();
        $result['reason'] = $response->getReasonPhrase();

        $body = $response->getBody();
        $json = json_decode($body, true);

        if(!$json){
            throw new BadJsonException('Failed to decode JSON');
        }

        $results['json'] = $json;
        return $results;
    }

    public function getModpacks($recursive = false)
    {
        $uri = 'modpack?k=' . $this->key;
        if ($recursive) {
            $uri = 'modpack?include=full&k=' . $this->key;
        }

        $response = $this->handle($uri);
        $modpacks = $response['json']['modpacks'];
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

        return new Modpack($response);
    }

    public function getBuild($modpack, $build)
    {
        $uri = 'modpack/'.$modpack.'/'.$build.'?include=mods';
        $response = $this->handle($uri);

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
            throw new ConnectionException($e->getMessage());
        }

        $body = $response->getBody();
        $json = json_decode($body, true);

        if ($json) {
            if (array_key_exists("valid", $json)) {
                return true;
            }
        } else {
            throw new BadJsonException('Failed to decode JSON');
        }

        return false;
    }

    private static function setupAgent()
    {
        return 'TechnicSolder/' . self::VERSION;
    }
}