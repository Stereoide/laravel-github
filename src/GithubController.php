<?php

namespace Stereoide\Github;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;

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
    public function getPaginationFromResponseHeaders($headers)
    {
        /* Make sure the pagination headers are present */

        if (!isset($headers['Link'])) {
            return null;
        }

        /* Determine pagination values */

        $pagination = [];

        $links = collect(explode(', ', $headers['Link'][0]));
        $links->each(function ($link, $key) use (&$pagination) {
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
    public function request($url, $method = 'GET', $headers = [], $body = null, $paginationOffset = null, $elementsPerPage = null)
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

        $options = [
            'headers' => $headers
        ];

        if (!is_null($body)) {
            $options['body'] = $body;
        }

        $request = $connection->request($method, $url, $options);

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

        list($statusCode, $headers, $body) = GithubController::request('events', 'GET', [], null, $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::request('repos/' . $owner . '/' . $repository . '/events', 'GET', [], null, $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::request('repos/' . $owner . '/' . $repository . '/issues/events', 'GET', [], null, $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::request('networks/' . $owner . '/' . $repository . '/events', 'GET', [], null, $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::request('orgs/' . $organisation . '/events', 'GET', [], null, $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::request('users/' . $username . '/received_events', 'GET', [], null, $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::request('users/' . $username . '/received_events/public', 'GET', [], null, $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * List events performed by a user
     *
     * If you are authenticated as the given user, you will see your private events. Otherwise, you'll only see
     * public events.
     *
     * @param string $username
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/events/#list-events-performed-by-a-user
     */
    public function getPerformedUserEvents($username, $paginationOffset = 1)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('users/' . $username . '/events', 'GET', [], null, $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * List public events performed by a user
     *
     * @param string $username
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/events/#list-public-events-performed-by-a-user
     */
    public function getPerformedPublicUserEvents($username, $paginationOffset = 1)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('users/' . $username . '/events/public', 'GET', [], null, $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * List events for an organization
     *
     * This is the user's organization dashboard. You must be authenticated as the user to view this.
     *
     * @param string $username
     * @param string $organisation
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/events/#list-events-for-an-organization
     */
    public function getUserOrganisationEvents($username, $organisation, $paginationOffset = 1)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('users/' . $username . '/events/orgs/' . $organisation, 'GET', [], null, $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * List your notifications
     *
     * List all notifications for the current user, grouped by repository.
     *
     * @param bool $showAllNotifications
     * @param bool $showOnlyParticipatingNotifications
     * @param null $showOnlyAfterTimestamp
     * @param null $showOnlyBeforeTimestamp
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/notifications/#list-your-notifications
     */
    public function getNotifications($showAllNotifications = false, $showOnlyParticipatingNotifications = false, $showOnlyAfterTimestamp = null, $showOnlyBeforeTimestamp = null, $paginationOffset = 1)
    {
        /* Sanitize parameters */

        $showAllNotifications = ((is_bool($showAllNotifications) && true == $showAllNotifications) || (is_string($showAllNotifications) && 'true' == $showAllNotifications));
        $showOnlyParticipatingNotifications = ((is_bool($showOnlyParticipatingNotifications) && true == $showOnlyParticipatingNotifications) || (is_string($showOnlyParticipatingNotifications) && 'true' == $showOnlyParticipatingNotifications));

        /* Fetch repository events */

        $url = 'notifications';

        if ($showAllNotifications) {
            $url .= '&all=true';
        }

        if ($showOnlyParticipatingNotifications) {
            $url .= '&participating=true';
        }

        if ($showOnlyAfterTimestamp) {
            $url .= '&since=' . Carbon::createFromTimestamp($showOnlyAfterTimestamp)->toIso8601String();
        }

        if ($showOnlyBeforeTimestamp) {
            $url .= '&after=' . Carbon::createFromTimestamp($showOnlyBeforeTimestamp)->toIso8601String();
        }

        $url = str_replace('notifications&', 'notifications?', $url);

        list($statusCode, $headers, $body) = GithubController::request($url, 'GET', [], null, $paginationOffset);

        $notifications = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $notifications;
    }

    /**
     * List your notifications in a repository
     *
     * List all notifications for the current user.
     *
     * @param string $owner
     * @param string $repository
     * @param bool $showAllNotifications
     * @param bool $showOnlyParticipatingNotifications
     * @param null $showOnlyAfterTimestamp
     * @param null $showOnlyBeforeTimestamp
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/notifications/#list-your-notifications-in-a-repository
     */
    public function getRepositoryNotifications($owner, $repository, $showAllNotifications = false, $showOnlyParticipatingNotifications = false, $showOnlyAfterTimestamp = null, $showOnlyBeforeTimestamp = null, $paginationOffset = 1)
    {
        /* Sanitize parameters */

        $showAllNotifications = ((is_bool($showAllNotifications) && true == $showAllNotifications) || (is_string($showAllNotifications) && 'true' == $showAllNotifications));
        $showOnlyParticipatingNotifications = ((is_bool($showOnlyParticipatingNotifications) && true == $showOnlyParticipatingNotifications) || (is_string($showOnlyParticipatingNotifications) && 'true' == $showOnlyParticipatingNotifications));

        /* Fetch repository events */

        $url = '/repos/' . $owner . '/' . $repository . '/notifications';

        if ($showAllNotifications) {
            $url .= '&all=true';
        }

        if ($showOnlyParticipatingNotifications) {
            $url .= '&participating=true';
        }

        if ($showOnlyAfterTimestamp) {
            $url .= '&since=' . Carbon::createFromTimestamp($showOnlyAfterTimestamp)->toIso8601String();
        }

        if ($showOnlyBeforeTimestamp) {
            $url .= '&after=' . Carbon::createFromTimestamp($showOnlyBeforeTimestamp)->toIso8601String();
        }

        $url = str_replace('notifications&', 'notifications?', $url);

        list($statusCode, $headers, $body) = GithubController::request($url, 'GET', [], null, $paginationOffset);

        $notifications = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $notifications;
    }

    /**
     * Mark notifications as read
     *
     * Marking a notification as "read" removes it from the default view on GitHub.
     *
     * @param null $timestamp
     * @see https://developer.github.com/v3/activity/notifications/#mark-as-read
     */
    public function markNotificationsAsRead($timestamp = null)
    {
        /* Mark notifications as read */

        $url = 'notifications';

        if ($timestamp) {
            $url .= '&last_read_at=' . Carbon::createFromTimestamp($timestamp)->toIso8601String();
        }

        $url = str_replace('notifications&', 'notifications?', $url);

        list($statusCode, $headers, $body) = GithubController::request($url, 'PUT', ['Content-Length' => 0]);
    }

    /**
     * Mark notifications as read in a repository
     *
     * Marking all notifications in a repository as "read" removes them from the default view on GitHub.
     *
     * @param string $owner
     * @param string $repository
     * @param null $timestamp
     * @see https://developer.github.com/v3/activity/notifications/#mark-notifications-as-read-in-a-repository
     */
    public function markRepositoryNotificationsAsRead($owner, $repository, $timestamp = null)
    {
        /* Mark notifications as read */

        $url = '/repos/' . $owner . '/' . $repository . '/notifications';

        if ($timestamp) {
            $url .= '&last_read_at=' . Carbon::createFromTimestamp($timestamp)->toIso8601String();
        }

        $url = str_replace('notifications&', 'notifications?', $url);

        list($statusCode, $headers, $body) = GithubController::request($url, 'PUT', ['Content-Length' => 0]);
    }

    /**
     * View a single thread
     *
     * @param int $id
     * @return mixed
     * @see https://developer.github.com/v3/activity/notifications/#view-a-single-thread
     */
    public function fetchNotificationThread($id)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('notifications/threads/' . $id, 'GET');

        /* Return thread */

        return $body;
    }

    /**
     * Mark a thread as read
     *
     * @param int $id
     * @see https://developer.github.com/v3/activity/notifications/#mark-a-thread-as-read
     */
    public function markNotificationThreadAsRead($id)
    {
        /* Mark thread as read */

        list($statusCode, $headers, $body) = GithubController::request('notifications/threads/' . $id, 'PATCH');
    }

    /**
     * Determine whether th eauthenticated user is subscribed to a notification thread
     *
     * @param int $id
     * @return mixed
     * @see https://developer.github.com/v3/activity/notifications/#get-a-thread-subscription
     */
    public function getNotificationThreadSubscriptionStatus($id)
    {
        /* Fetch repository events */

        list($statusCode, $headers, $body) = GithubController::request('notifications/threads/' . $id . '/subscription', 'GET');

        /* Return thread */

        return $body;
    }

    /**
     * Set a Thread Subscription
     *
     * This lets you subscribe or unsubscribe from a conversation. Unsubscribing from a conversation mutes all future
     * notifications (until you comment or get @mentioned once more).
     *
     * @param int $id
     * @param bool $subscribed
     * @param bool $ignored
     * @see https://developer.github.com/v3/activity/notifications/#set-a-thread-subscription
     */
    public function setNotificationThreadSubscriptionStatus($id, $subscribed, $ignored)
    {
        /* Sanitize parameters */

        $subscribed = ((is_bool($subscribed) && true == $subscribed) || (is_string($subscribed) && 'true' == $subscribed));
        $ignored = ((is_bool($ignored) && true == $ignored) || (is_string($ignored) && 'true' == $ignored));

        /* Set notification thread subscription status */

        $url = 'notifications/threads/' . $id . '/subscription?subscribed=' . ($subscribed ? 'true' : 'false') . '&ignored=' . ($ignored ? 'true' : 'false');

        list($statusCode, $headers, $body) = GithubController::request($url, 'PUT', ['Content-Length' => 0]);
    }

    /**
     * Delete a Thread Subscription
     *
     * @param int $id
     * @see https://developer.github.com/v3/activity/notifications/#delete-a-thread-subscription
     */
    public function deleteNotificationThreadSubscription($id)
    {
        /* Delete notification thread subscription */

        list($statusCode, $headers, $body) = GithubController::request('notifications/threads/' . $id . '/subscription', 'DELETE');
    }

    /**
     * List Stargazers
     *
     * @param string $owner
     * @param string $repository
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/starring/#list-stargazers
     */
    public function getRepositoryStargazers($owner, $repository, $paginationOffset = 1)
    {
        /* Fetch repository stargazers */

        list($statusCode, $headers, $body) = GithubController::request('repos/' . $owner . '/' . $repository . '/stargazers', 'GET', [], null, $paginationOffset);

        $stargazers = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $stargazers;
    }

    /**
     * List repositories being starred
     *
     * @param string $username
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/starring/#list-repositories-being-starred
     */
    public function getStarredRepositories($username = null, $paginationOffset = 1)
    {
        /* Determine URL */

        if (empty($username)) {
            $url = 'user/starred';
        } else {
            $url = 'users/' . $username . '/starred';
        }

        /* Fetch starred repositories */

        list($statusCode, $headers, $body) = GithubController::request($url, 'GET', [], null, $paginationOffset);

        $repositories = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $repositories;
    }

    /**
     * Check if you are starring a repository
     *
     * Requires for the user to be authenticated.
     *
     * @param string $owner
     * @param string $repository
     * @return bool
     * @see https://developer.github.com/v3/activity/starring/#check-if-you-are-starring-a-repository
     */
    public function isRepositoryStarred($owner, $repository)
    {
        /* Determine whether the repository in question is starred by the authenticated user */

        try {
            list($statusCode, $headers, $body) = GithubController::request('user/starred/' . $owner . '/' . $repository);
            return (204 == $statusCode);
        } catch (\Exception $exception) {
            return (204 == $exception->getResponse()->getStatusCode());
        }
    }

    /**
     * Star a repository
     *
     * Requires for the user to be authenticated.
     *
     * @param string $owner
     * @param string $repository
     * @see https://developer.github.com/v3/activity/starring/#star-a-repository
     */
    public function starRepository($owner, $repository)
    {
        /* Star the repository */

        list($statusCode, $headers, $body) = GithubController::request('user/starred/' . $owner . '/' . $repository, 'PUT', ['Content-Length' => 0]);
    }

    /**
     * Unstar a repository
     *
     * Requires for the user to be authenticated.
     *
     * @param string $owner
     * @param string $repository
     * @see https://developer.github.com/v3/activity/starring/#unstar-a-repository
     */
    public function unstarRepository($owner, $repository)
    {
        /* Star the repository */

        list($statusCode, $headers, $body) = GithubController::request('user/starred/' . $owner . '/' . $repository, 'DELETE');
    }

    /**
     * List watchers
     *
     * @param string $owner
     * @param string $repository
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/watching/#list-watchers
     */
    public function getRepositoryWatchers($owner, $repository, $paginationOffset = 1)
    {
        /* Fetch repository stargazers */

        list($statusCode, $headers, $body) = GithubController::request('repos/' . $owner . '/' . $repository . '/subscribers', 'GET', [], null, $paginationOffset);

        $watchers = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $watchers;
    }

    /**
     * List repositories being watched
     *
     * @param string $username
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/activity/watching/#list-repositories-being-watched
     */
    public function getWatchedRepositories($username = null, $paginationOffset = 1)
    {
        /* Determine URL */

        if (empty($username)) {
            $url = 'user/subscriptions';
        } else {
            $url = 'users/' . $username . '/subscriptions';
        }

        /* Fetch watched repositories */

        list($statusCode, $headers, $body) = GithubController::request($url, 'GET', [], null, $paginationOffset);

        $repositories = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $repositories;
    }

    /**
     * Get a Repository Subscription
     *
     * Requires for the user to be authenticated.
     *
     * @param string $owner
     * @param string $repository
     * @return [bool $isWatched, array $watchData]
     * @see https://developer.github.com/v3/activity/watching/#get-a-repository-subscription
     */
    public function isRepositoryWatched($owner, $repository)
    {
        /* Determine whether the repository in question is starred by the authenticated user */

        try {
            list($statusCode, $headers, $body) = GithubController::request('repos/' . $owner . '/' . $repository . '/subscription');
            if (200 == $statusCode) {
                return [true, $body];
            } else {
                return [false, null];
            }
        } catch (\Exception $exception) {
            return [false, null];
        }
    }

    /**
     * Set a Repository Subscription
     *
     * Requires for the user to be authenticated.
     *
     * @param string $owner
     * @param string $repository
     * @param bool $subscribed
     * @param bool $ignored
     * @see https://developer.github.com/v3/activity/watching/#set-a-repository-subscription
     */
    public function watchRepository($owner, $repository, $subscribed = true, $ignored = false)
    {
        /* Assemble payload */

        $data = json_encode([
            'subscribed' => ((is_bool($subscribed) && $subscribed) || (is_string($subscribed) && 'true' == $subscribed)),
            'ignored' => ((is_bool($ignored) && $ignored) || (is_string($ignored) && 'true' == $ignored))
        ]);

        /* Watch the repository */

        list($statusCode, $headers, $body) = GithubController::request('repos/' . $owner . '/' . $repository . '/subscription', 'PUT', ['Content-Length' => strlen($data)], $data);
    }

    /**
     * Delete a Repository Subscription
     *
     * @param string $owner
     * @param string $repository
     * @see https://developer.github.com/v3/activity/watching/#delete-a-repository-subscription
     */
    public function unwatchRepository($owner, $repository)
    {
        /* Unwatch the repository */

        list($statusCode, $headers, $body) = GithubController::request('repos/' . $owner . '/' . $repository . '/subscription', 'DELETE');
    }

    /**
     * List a user's gists
     *
     * Beware that this will return ALL public gists if no authenticated user is configured
     *
     * @param string $username
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/gists/#list-a-users-gists
     * @TODO Pagination
     * @TODO Timestamp of first git
     * @TODO Check for truncated gists
     */
    public function getGists($username = null, $paginationOffset = 1)
    {
        /* Determine URL */

        if (empty($username)) {
            $url = 'gists';
        } else {
            $url = 'users/' . $username . '/subscriptions';
        }

        /* Fetch public gists */

        list($statusCode, $headers, $body) = GithubController::request($url, 'GET', [], null, $paginationOffset);

        $gists = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $gists;
    }

    /**
     * List starred gists
     *
     * List the authenticated user's starred gists
     *
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/gists/#list-starred-gists
     * @TODO Pagination
     * @TODO Timestamp of first git
     * @TODO Check for truncated gists
     */
    public function getStarredGists($paginationOffset = 1)
    {
        /* Fetch starred gists */

        list($statusCode, $headers, $body) = GithubController::request('gists/starred', 'GET', [], null, $paginationOffset);

        $gists = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $gists;
    }

    /**
     * Get a single gist
     *
     * @return mixed
     * @see https://developer.github.com/v3/gists/#get-a-single-gist
     * @TODO Check for truncated gist
     */
    public function getGist($id, $sha = null)
    {
        /* Assemble URL */

        $url = 'gists/' . $id;

        if (!is_null($sha)) {
            $url .= '/' . $sha;
        }

        /* Fetch gist */

        list($statusCode, $headers, $gist) = GithubController::request($url);

        /* Return gist */

        return $gist;
    }

    /**
     * Get a specific revision of a gist
     *
     * @return mixed
     * @see https://developer.github.com/v3/gists/#get-a-specific-revision-of-a-gist
     * @TODO Check for truncated gist
     */
    public function getGistRevision($id, $sha)
    {
        /* Fetch gist revision */

        return GithubController::getGist($id, $sha);
    }

    /**
     * Create a gist
     *
     * @param array (string $filepath)
     * @param string $description
     * @param bool $public
     * @see https://developer.github.com/v3/gists/#create-a-gist
     */
    public function createGist($filepaths, $description = '', $public = false)
    {
        /* Assemble data */

        $data = [
            'files' => [],
            'description' => $description,
            'public' => $public
        ];

        foreach ($filepaths as $filepath) {
            $filename = basename($filepath);
            $data['files'][$filename]['content'] = file_get_contents($filepath);
        }

        $data = json_encode($data);

        /* Create gist */

        list($statusCode, $headers, $body) = GithubController::request('gists', 'POST', [], $data);
    }

    /**
     * Edit a gist
     *
     * All files from the previous version of the gist are carried over by default if not included in the object.
     * Deletes can be performed by including the filename with a null object.
     *
     * @param string $id
     * @param array (string $newFilepaths)
     * @param array (string $renamedFilenames)
     * @param array (string $deletedFilenames)
     * @param string $description
     * @see https://developer.github.com/v3/gists/#edit-a-gist
     * @TODO Write better parameter description
     */
    public function editGist($id, $newFilepaths = null, $renamedFilenames = null, $deletedFilenames = null, $description = null)
    {
        /* Return early if possible */

        if (is_null($newFilepaths) && is_null($renamedFilenames) && is_null($deletedFilenames) && is_null($description)) {
            return;
        }

        /* Assemble data */

        $data = [
            'files' => []
        ];

        /* Description */

        if (!is_null($description)) {
            $data['description'] = $description;
        }

        /* New files */

        if (!is_null($newFilepaths)) {
            foreach ($newFilepaths as $filepath) {
                $filename = basename($filepath);
                $data['files'][$filename]['content'] = file_get_contents($filepath);
            }
        }

        /* Renamed files */

        if (!is_null($renamedFilenames)) {
            foreach ($renamedFilenames as $oldFilename => $newFilepath) {
                $data['files'][$oldFilename]['filename'] = basename($newFilepath);
                $data['files'][$oldFilename]['content'] = file_get_contents($filepath);
            }
        }

        /* Deleted files */

        if (!is_null($deletedFilenames)) {
            foreach ($deletedFilenames as $filename) {
                $data['files'][$filename] = null;
            }
        }

        $data = json_encode($data);

        /* Edit gist */

        list($statusCode, $headers, $body) = GithubController::request('gists', 'PATCH', [], $data);
    }

    /**
     * List gist commits
     *
     * @param string $id
     * @return mixed
     * @see https://developer.github.com/v3/gists/#list-gist-commits
     */
    public function getGistCommits($id)
    {
        /* Fetch gist commits */

        list($statusCode, $headers, $body) = GithubController::request('gists/' . $id . '/commits');

        $commits = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return commits */

        return $commits;
    }

    /**
     * Star a gist
     *
     * @param string $id
     * @return mixed
     * @see https://developer.github.com/v3/gists/#star-a-gist
     */
    public function starGist($id)
    {
        /* Star a gist */

        list($statusCode, $headers, $body) = GithubController::request('gists/' . $id . '/star', 'PUT', ['Content-Length' => 0]);
    }

    /**
     * Unstar a gist
     *
     * @param string $id
     * @return mixed
     * https://developer.github.com/v3/gists/#unstar-a-gist
     */
    public function unstarGist($id)
    {
        /* Unstar a gist */

        list($statusCode, $headers, $body) = GithubController::request('gists/' . $id . '/star', 'DELETE');
    }

    /**
     * Check if a gist is starred
     *
     * @param int $id
     * @return bool $isStarred
     * @see https://developer.github.com/v3/gists/#check-if-a-gist-is-starred
     */
    public function isGistStarred($id)
    {
        /* Determine whether the gist in question is starred by the authenticated user */

        try {
            list($statusCode, $headers, $body) = GithubController::request('gists/' . $id . '/star');
            return (204 == $statusCode);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Fork a gist
     *
     * @param string $id
     * @return mixed
     * @see https://developer.github.com/v3/gists/#fork-a-gist
     */
    public function forkGist($id)
    {
        /* Fork gist */

        list($statusCode, $headers, $gist) = GithubController::request('gists/' . $id . '/forks', 'POST');

        /* Return gist */

        return $gist;
    }
}
