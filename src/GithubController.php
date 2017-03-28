<?php

namespace Stereoide\Github;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class GithubController extends \App\Http\Controllers\Controller
{
    public function getConnection()
    {
        /* Initialize HTTP connection */

        $oAuthToken = config('github.oAuthToken');

        $userAgent = config('github.userAgent');
        if (empty($userAgent)) {
            $userAgent = 'laravel-github';
        }

        $client = new Client([
            'base_uri' => 'https://api.github.com',
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => (!empty($oAuthToken) ? 'token ' . $oAuthToken : ''),
                'User-Agent' => $userAgent
            ]
        ]);

        /* Return client object */

        return $client;
    }

    public function request($url, $method = 'GET', $headers = [])
    {
        /* Get CURL connection */

        $connection = GithubController::getConnection();

        /* Perform request */

        $request = $connection->request($method, $url, ['headers' => $headers]);

        $statusCode = $request->getStatusCode();
        $headers = $request->getHeaders();
        $body = json_decode($request->getBody()->getContents());

        /* Return */

        return [$statusCode, $headers, $body];
    }

    public function cmd($method)
    {
        /* Determine whether the desired method exists */

        if (method_exists($this, $method)) {
            $body = call_user_func([$this, $method]);
            dd($body);
        } else {
            return 'Method "' . $method . '" does not exist';
        }
    }

    public function getApiEndpoints()
    {
        list($statusCode, $headers, $body) = GithubController::request('/');

        return $body;
    }

    public function current_user_url()
    {
        list($statusCode, $headers, $body) = GithubController::request('user', 'GET');

        return $body;
    }

    public function getUserRepos($username)
    {
        /* Fetch user repos */

        list($statusCode, $headers, $body) = GithubController::request('users/' . $username . '/repos');

        $repos = collect($body);

        /* Return repos */

        return $repos;
    }
}
