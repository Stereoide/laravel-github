<?php

namespace Stereoide\Github;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

/**
 * Class GithubController
 * @package Stereoide\Github
 */
class GithubController extends \App\Http\Controllers\Controller
{
    /**
     * Creates and returns a Guzzle HTTP connection object to the Github root API endpoint
     *
     * @return Client
     */
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

    /**
     * @param $headers
     * @return array|null
     */
    public function getPaginationFromResponseHeaders($headers) {
        /* Make sure the pagination headers are present */

        if (!isset($headers['Link'])) {
            return null;
        }

        /* Determine pagination values */

        $pagination = [];

        $links = collect(explode(', ', $headers['Link'][0]));
        $links->each(function($link, $key) use (&$pagination) {
            preg_match('/<(.*page=(.*))>.*rel="(.*)"/i', $link, $matches);
            list($unused, $link, $page, $rel) = $matches;
            $pagination[$rel] = [
                'link' => $link,
                'page' => $page
            ];
        });

        /* Make sure all indizes are at least set */

        foreach (['first', 'prev', 'next', 'last'] as $key) {
            if (!isset($pagination[$key])) {
                $pagination[$key] = null;
            }
        }

        /* Return */

        return $pagination;
    }

    /**
     * Perform a HTTP request to a specific API endpoint
     * Returns the status code and the response headers AS-IS but json-ifies the response body
     *
     * @param $url
     * @param string $method
     * @param array $headers
     * @return [$statusCode, $headers, $body]
     */
    public function request($url, $method = 'GET', $headers = [], $paginationOffset = null, $elementsPerPage = null)
    {
        /* Get CURL connection */

        $connection = GithubController::getConnection();

        /* Adjust URL to include pagination if necessary */

        if (!is_null($paginationOffset)) {
            $url .= (false === strpos($url, '?') ? '?' : '&') . 'page=' . $paginationOffset;
        }

        if (!is_null($elementsPerPage)) {
            $url .= (false === strpos($url, '?') ? '?' : '&') . 'per_page=' . $elementsPerPage;
        }

        /* Perform request */

        $request = $connection->request($method, $url, ['headers' => $headers]);

        $statusCode = $request->getStatusCode();
        $headers = $request->getHeaders();
        $body = json_decode($request->getBody()->getContents());

        /* Return */

        return [$statusCode, $headers, $body];
    }

    /**
     * Placeholder method to be able to call any API method via a Laravel route
     *
     * @param $method
     * @return string
     */
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

    /**
     * @return mixed
     */
    public function getApiEndpoints()
    {
        list($statusCode, $headers, $body) = GithubController::request('/');

        return $body;
    }

    /**
     * @return mixed
     */
    public function current_user_url()
    {
        list($statusCode, $headers, $body) = GithubController::request('user');

        return $body;
    }

    /**
     * @param $username
     * @return mixed
     */
    public function getUserRepos($username)
    {
        /* Fetch user repos */

        list($statusCode, $headers, $body) = GithubController::request('users/' . $username . '/repos');

        $repos = collect($body);

        /* Return repos */

        return $repos;
    }

    /**
     * List public events
     *
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/events/#list-public-events
     */
    public function getEvents($paginationOffset = 1)
    {
        /* Fetch public events */

        list($statusCode, $headers, $body) = GithubController::request('events', 'GET', [], $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * List repository events
     *
     * @param string $owner
     * @param string $repository
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/events/#list-repository-events
     */
    public function getRepositoryEvents($owner, $repository, $paginationOffset = 1)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('repos/' . $owner . '/' . $repository . '/events', 'GET', [], $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * List issue events for a repository
     *
     * @param string $owner
     * @param string $repository
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/events/#list-issue-events-for-a-repository
     */
    public function getRepositoryIssuesEvents($owner, $repository, $paginationOffset = 1)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('repos/' . $owner . '/' . $repository . '/issues/events', 'GET', [], $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * List public events for a network of repositories
     *
     * @param string $owner
     * @param string $repository
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/events/#list-public-events-for-a-network-of-repositories
     */
    public function getNetworkRepositoryEvents($owner, $repository, $paginationOffset = 1)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('networks/' . $owner . '/' . $repository . '/events', 'GET', [], $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * List public events for an organization
     *
     * @param string $organisation
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/events/#list-public-events-for-an-organization
     */
    public function getOrganisationEvents($organisation, $paginationOffset = 1)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('orgs/' . $organisation . '/events', 'GET', [], $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * List events that a user has received
     *
     * These are events that you've received by watching repos and following users. If you are authenticated as the
     * given user, you will see private events. Otherwise, you'll only see public events.
     *
     * @param string $username
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/events/#list-events-that-a-user-has-received
     */
    public function getReceivedUserEvents($username, $paginationOffset = 1)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('users/' . $username . '/received_events', 'GET', [], $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * List public events that a user has received
     *
     * @param string $username
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/events/#list-public-events-that-a-user-has-received
     */
    public function getReceivedPublicUserEvents($username, $paginationOffset = 1)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('users/' . $username . '/received_events/public', 'GET', [], $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }
}
