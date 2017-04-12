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
    /* Base methods */

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
     * @param mixed $body
     * @param int $paginationOffset
     * @param int $elementsPerPage
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
     * Convenience method to perform GET calls
     *
     * @param string $url
     * @param int $paginationOffset
     * @param int $elementsPerPage
     * @return [$statusCode, $headers, $body]
     * @see GithubController::request()
     */
    public function get($url, $paginationOffset = null, $elementsPerPage = null)
    {
        /* Call request method */

        return GithubController::request($url, 'GET', [], null, $paginationOffset, $elementsPerPage);
    }

    /**
     * Convenience method to perform POST calls
     *
     * @param string $url
     * @param mixed $body
     * @param array $headers
     * @param int $paginationOffset
     * @param int $elementsPerPage
     * @return [$statusCode, $headers, $body]
     * @see GithubController::request()
     */
    public function post($url, $body = null, $headers = [], $paginationOffset = null, $elementsPerPage = null)
    {
        /* Call request method */

        return GithubController::request($url, 'POST', $headers, $body, $paginationOffset, $elementsPerPage);
    }

    /**
     * Convenience method to perform PATCH calls
     *
     * @param string $url
     * @param mixed $body
     * @param array $headers
     * @param int $paginationOffset
     * @param int $elementsPerPage
     * @return [$statusCode, $headers, $body]
     * @see GithubController::request()
     */
    public function patch($url, $body = null, $headers = [], $paginationOffset = null, $elementsPerPage = null)
    {
        /* Call request method */

        return GithubController::request($url, 'PATCH', $headers, $body, $paginationOffset, $elementsPerPage);
    }

    /**
     * Convenience method to perform DELETE calls
     *
     * @param string $url
     * @param mixed $body
     * @param array $headers
     * @param int $paginationOffset
     * @param int $elementsPerPage
     * @return [$statusCode, $headers, $body]
     * @see GithubController::request()
     */
    public function delete($url, $body = null, $headers = [], $paginationOffset = null, $elementsPerPage = null)
    {
        /* Call request method */

        return GithubController::request($url, 'DELETE', $headers, $body, $paginationOffset, $elementsPerPage);
    }

    /**
     * Convenience method to perform DELETE calls
     *
     * @param string $url
     * @param array $headers
     * @param mixed $body
     * @param int $paginationOffset
     * @param int $elementsPerPage
     * @return [$statusCode, $headers, $body]
     * @see GithubController::request()
     */
    public function put($url, $headers = [], $body = null, $paginationOffset = null, $elementsPerPage = null)
    {
        /* Call request method */

        return GithubController::request($url, 'PUT', $headers, $body, $paginationOffset, $elementsPerPage);
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
        list($statusCode, $headers, $body) = GithubController::get('/');

        return $body;
    }

    /**
     * @return mixed
     */
    public function current_user_url()
    {
        list($statusCode, $headers, $body) = GithubController::get('user');

        return $body;
    }

    /**
     * @param $username
     * @return mixed
     */
    public function getUserRepos($username)
    {
        /* Fetch user repos */

        list($statusCode, $headers, $body) = GithubController::get('users/' . $username . '/repos');

        $repos = collect($body);

        /* Return repos */

        return $repos;
    }

    /* Events */

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

        list($statusCode, $headers, $body) = GithubController::get('events', $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/events', $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get('networks/' . $owner . '/' . $repository . '/events', $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get('orgs/' . $organisation . '/events', $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get('users/' . $username . '/received_events', $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get('users/' . $username . '/received_events/public', $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get('users/' . $username . '/events', $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get('users/' . $username . '/events/public', $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get('users/' . $username . '/events/orgs/' . $organisation, $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /* Notifications */

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

        list($statusCode, $headers, $body) = GithubController::get($url, $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get($url, $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::put($url, ['Content-Length' => 0]);
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

        list($statusCode, $headers, $body) = GithubController::put($url, ['Content-Length' => 0]);
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

        list($statusCode, $headers, $body) = GithubController::get('notifications/threads/' . $id);

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

        list($statusCode, $headers, $body) = GithubController::patch('notifications/threads/' . $id);
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

        list($statusCode, $headers, $body) = GithubController::get('notifications/threads/' . $id . '/subscription');

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

        list($statusCode, $headers, $body) = GithubController::put($url, ['Content-Length' => 0]);
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

        list($statusCode, $headers, $body) = GithubController::delete('notifications/threads/' . $id . '/subscription');
    }

    /* Starring */

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

        list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/stargazers', $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get($url, $paginationOffset);

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
            list($statusCode, $headers, $body) = GithubController::get('user/starred/' . $owner . '/' . $repository);
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

        list($statusCode, $headers, $body) = GithubController::put('user/starred/' . $owner . '/' . $repository, ['Content-Length' => 0]);
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

        list($statusCode, $headers, $body) = GithubController::delete('user/starred/' . $owner . '/' . $repository);
    }

    /* Watching */

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

        list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/subscribers', $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get($url, $paginationOffset);

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
            list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/subscription');
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

        list($statusCode, $headers, $body) = GithubController::put('repos/' . $owner . '/' . $repository . '/subscription', ['Content-Length' => strlen($data)], $data);
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

        list($statusCode, $headers, $body) = GithubController::delete('repos/' . $owner . '/' . $repository . '/subscription');
    }

    /* Gists */

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

        list($statusCode, $headers, $body) = GithubController::get($url, $paginationOffset);

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

        list($statusCode, $headers, $body) = GithubController::get('gists/starred', $paginationOffset);

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

        list($statusCode, $headers, $gist) = GithubController::get($url);

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

        list($statusCode, $headers, $body) = GithubController::post('gists', $data);
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

        list($statusCode, $headers, $body) = GithubController::patch('gists', $data);
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

        list($statusCode, $headers, $body) = GithubController::get('gists/' . $id . '/commits');

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

        list($statusCode, $headers, $body) = GithubController::put('gists/' . $id . '/star', ['Content-Length' => 0]);
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

        list($statusCode, $headers, $body) = GithubController::delete('gists/' . $id . '/star');
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
            list($statusCode, $headers, $body) = GithubController::get('gists/' . $id . '/star');
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

        list($statusCode, $headers, $gist) = GithubController::post('gists/' . $id . '/forks');

        /* Return gist */

        return $gist;
    }

    /**
     * List gist forks
     *
     * @param string $id
     * @return mixed
     * @see https://developer.github.com/v3/gists/#list-gist-forks
     */
    public function getGistForks($id)
    {
        /* Fetch gist forks */

        list($statusCode, $headers, $body) = GithubController::get('gists/' . $id . '/forks');

        $forks = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return commits */

        return $forks;
    }

    /**
     * Delete a gist
     *
     * @param string $id
     * @see https://developer.github.com/v3/gists/#delete-a-gist
     */
    public function deleteGist($id)
    {
        /* Delete gist */

        list($statusCode, $headers, $body) = GithubController::delete('gists/' . $id);
    }

    /* Gist comments */

    /**
     * List comments on a gist
     *
     * @param string $id
     * @return mixed
     * @see https://developer.github.com/v3/gists/comments/#list-comments-on-a-gist
     */
    public function getGistComments($id)
    {
        /* Fetch gist comments */

        list($statusCode, $headers, $body) = GithubController::get('gists/' . $id . '/comments');

        $comments = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return comments */

        return $comments;
    }

    /**
     * Get a single comment
     *
     * @param string $gistId
     * @param string $commentId
     * @return mixed
     * @see https://developer.github.com/v3/gists/comments/#get-a-single-comment
     */
    public function getGistComment($gistId, $commentId)
    {
        /* Fetch gist comments */

        list($statusCode, $headers, $comment) = GithubController::get('gists/' . $gistId . '/comments/' . $commentId);

        /* Return comment */

        return $comment;
    }

    /**
     * Create a gist comment
     *
     * @param string $gistId
     * @param string $comment
     * @return mixed
     * @see https://developer.github.com/v3/gists/comments/#create-a-comment
     */
    public function createGistComment($gistId, $comment)
    {
        /* Assemble data */

        $data = json_encode([
            'body' => $comment,
        ]);

        /* Create comment */

        list($statusCode, $headers, $comment) = GithubController::post('gists/' . $gistId . '/comments', $data);

        /* Return comment */

        return $comment;
    }

    /**
     * Delete a gist comment
     *
     * @param string $gistId
     * @param string $commentId
     * @return mixed
     * @see https://developer.github.com/v3/gists/comments/#delete-a-comment
     */
    public function deleteGistComment($gistId, $commentId)
    {
        /* Delete comment */

        list($statusCode, $headers, $comment) = GithubController::delete('gists/' . $gistId . '/comments/' . $commentId);
    }

    /* Issues */

    /**
     * List issues
     *
     * @param null|string $filter
     * @param null|string $state
     * @param null|string $labels
     * @param null|string $sort
     * @param null|string $direction
     * @param null|int $since
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/issues/#list-issues
     * @TODO Better sanitize parameters
     */
    public function getIssues($filter = null, $state = null, $labels = null, $sort = null, $direction = null, $since = null, $paginationOffset = 1)
    {
        /* Assemble URL */

        $url = 'user/issues';

        if (!is_null($filter)) {
            $url .= '&filter=' . $filter;
        }

        if (!is_null($state)) {
            $url .= '&state=' . $state;
        }

        if (!is_null($labels)) {
            $url .= '&labels=' . $labels;
        }

        if (!is_null($sort)) {
            $url .= '&sort=' . $sort;
        }

        if (!is_null($direction)) {
            $url .= '&direction=' . $direction;
        }

        if (!is_null($since)) {
            $url .= '&since=' . $since;
        }

        $url = str_replace('/issues&', '/issues?', $url);

        /* Fetch issues */

        list($statusCode, $headers, $body) = GithubController::get($url, $paginationOffset);

        $issues = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return issues */

        return $issues;
    }

    /**
     * Get a single issue
     *
     * @return mixed
     * @see https://developer.github.com/v3/issues/#get-a-single-issue
     */
    public function getIssue($owner, $repository, $number)
    {
        /* Fetch issue */

        list($statusCode, $headers, $issue) = GithubController::get('repos/'. $owner . '/' . $repository . '/issues/' . $number);

        /* Return issue */

        return $issue;
    }

    /**
     * Create an issue
     *
     * @param string $owner
     * @param string $repository
     * @param string $title
     * @param string $body
     * @param int $milestone
     * @param array(string $label)
     * @param array(string $assignee)
     * @return mixed
     * @see https://developer.github.com/v3/issues/#create-an-issue
     * @TODO Better sanitize parameters
     */
    public function createIssue($owner, $repository, $title, $body = null, $milestone = null, $labels = null, $assignees = null)
    {
        /* Assemble data */

        $data = [
            'title' => $title,
        ];

        if (!is_null($body)) {
            $data['body'] = $body;
        }

        if (!is_null($milestone)) {
            $data['milestone'] = $milestone;
        }

        if (!is_null($labels)) {
            if (!is_array($labels)) {
                $labels = explode(',', $labels);
            }

            $data['labels'] = [];
            foreach ($labels as $label) {
                $data['labels'][] = $label;
            }
        }

        if (!is_null($assignees)) {
            if (!is_array($assignees)) {
                $assignees = explode(',', $assignees);
            }

            $data['assignees'] = [];
            foreach ($assignees as $assignee) {
                $data['assignees'][] = $assignee;
            }
        }

        $data = json_encode($data);

        /* Create issue */

        list($statusCode, $headers, $issue) = GithubController::post('repos/' . $owner . '/' . $repository . '/issues', $data);

        /* Return issue */

        return $issue;
    }

    /**
     * Edit an issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param string $title
     * @param string $body
     * @param string $state
     * @param int $milestone
     * @param array(string $label)
     * @param array(string $assignee)
     * @return mixed
     * @see https://developer.github.com/v3/issues/#edit-an-issue
     * @TODO Better sanitize parameters
     */
    public function editIssue($owner, $repository, $number, $title, $body = null, $state = null, $milestone = null, $labels = null, $assignees = null)
    {
        /* Assemble data */

        $data = [
            'title' => $title,
        ];

        if (!is_null($body)) {
            $data['body'] = $body;
        }

        if (!is_null($state)) {
            $data['state'] = $state;
        }

        if (!is_null($milestone)) {
            $data['milestone'] = $milestone;
        }

        if (!is_null($labels)) {
            if (!is_array($labels)) {
                $labels = explode(',', $labels);
            }

            $data['labels'] = [];
            foreach ($labels as $label) {
                $data['labels'][] = $label;
            }
        }

        if (!is_null($assignees)) {
            if (!is_array($assignees)) {
                $assignees = explode(',', $assignees);
            }

            $data['assignees'] = [];
            foreach ($assignees as $assignee) {
                $data['assignees'][] = $assignee;
            }
        }

        $data = json_encode($data);

        /* Edit issue */

        list($statusCode, $headers, $issue) = GithubController::patch('repos/' . $owner . '/' . $repository . '/issues/' . $number, $data);

        /* Return issue */

        return $issue;
    }

    /**
     * Lock an issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @see https://developer.github.com/v3/issues/#lock-an-issue
     */
    public function lockIssue($owner, $repository, $number)
    {
        /* Lock issue */

        list($statusCode, $headers, $issue) = GithubController::put('repos/' . $owner . '/' . $repository . '/issues/' . $number . '/lock', ['Content-Length' => 0]);
    }

    /**
     * Unlock an issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @see https://developer.github.com/v3/issues/#unlock-an-issue
     */
    public function unlockIssue($owner, $repository, $number)
    {
        /* Unlock issue */

        list($statusCode, $headers, $issue) = GithubController::delete('repos/' . $owner . '/' . $repository . '/issues/' . $number . '/lock');
    }

    /* Issue assignees */

    /**
     * List available assignees
     *
     * @param string $owner
     * @param string $repository
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/issues/assignees/#list-assignees
     */
    public function getAvailableIssueAssignees($owner, $repository, $paginationOffset = 1)
    {
        /* Fetch available assignees */

        list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/assignees', $paginationOffset);

        $availableAssignees = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return available assignees */

        return $availableAssignees;
    }

    /**
     * Check assignee
     *
     * Requires for the user to be authenticated.
     *
     * @param string $owner
     * @param string $repository
     * @param string $assignee
     * @return bool
     * @see https://developer.github.com/v3/issues/assignees/#check-assignee
     */
    public function isRepositoryAssignee($owner, $repository, $assignee)
    {
        /* Determine whether the assignee is assigned to a repository */

        try {
            list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/assignees/' . $assignee);
            return (204 == $statusCode);
        } catch (\Exception $exception) {
            return (204 == $exception->getResponse()->getStatusCode());
        }
    }

    /**
     * Add assignees to an Issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param array(string $assignee)
     * @return mixed
     * @see https://developer.github.com/v3/issues/assignees/#add-assignees-to-an-issue
     */
    public function addIssueAssignees($owner, $repository, $number, $assignees)
    {
        /* Sanitize parameters */

        if (!is_array($assignees)) {
            $assignees = explode(',', $assignees);
        }

        /* Assemble data */

        $data = json_encode(['assignees' => $assignees]);

        /* Add assignees */

        list($statusCode, $headers, $issue) = GithubController::post('/repos/' . $owner . '/' . $repository . '/issues/' . $number . '/assignees', $data);

        /* Return issue */

        return $issue;
    }

    /**
     * Remove assignees from an Issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param array(string $assignee)
     * @return mixed
     * @see https://developer.github.com/v3/issues/assignees/#remove-assignees-from-an-issue
     */
    public function removeIssueAssignees($owner, $repository, $number, $assignees)
    {
        /* Sanitize parameters */

        if (!is_array($assignees)) {
            $assignees = explode(',', $assignees);
        }

        /* Assemble data */

        $data = json_encode(['assignees' => $assignees]);

        /* Remove assignees */

        list($statusCode, $headers, $issue) = GithubController::delete('/repos/' . $owner . '/' . $repository . '/issues/' . $number . '/assignees', $data);

        /* Return issue */

        return $issue;
    }

    /* Issue comments */

    /**
     * List comments on an issue
     *
     * Issue Comments are ordered by ascending ID.
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param string $since
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/issues/comments/#list-comments-on-an-issue
     */
    public function getIssueComments($owner, $repository, $number, $since = null, $paginationOffset = 1)
    {
        /* Assemble URL */

        $url = 'repos/' . $owner . '/' . $repository . '/issues/' . $number . '/comments';

        if (!is_null($since)) {
            $url .= '&since=' . $since;
        }

        $url = str_replace('/comments&', '/comments?', $url);

        /* Fetch issue comments */

        list($statusCode, $headers, $body) = GithubController::get($url, $paginationOffset);

        $comments = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return comments */

        return $comments;
    }

    /**
     * List comments on an issue
     *
     * Issue Comments are ordered by ascending ID.
     *
     * @param string $owner
     * @param string $repository
     * @param string $sort
     * @param string $direction
     * @param string $since
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/issues/comments/#list-comments-in-a-repository
     */
    public function getRepositoryIssuesComments($owner, $repository, $sort = null, $direction = null, $since = null, $paginationOffset = 1)
    {
        /* Assemble URL */

        $url = 'repos/' . $owner . '/' . $repository . '/issues/comments';

        if (!is_null($sort)) {
            $url .= '&sort=' . $sort;
        }

        if (!is_null($direction)) {
            $url .= '&direction=' . $direction;
        }

        if (!is_null($since)) {
            $url .= '&since=' . $since;
        }

        $url = str_replace('/comments&', '/comments?', $url);

        /* Fetch issue comments */

        list($statusCode, $headers, $body) = GithubController::get($url, $paginationOffset);

        $comments = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return comments */

        return $comments;
    }

    /**
     * Get a single comment
     *
     * @param string $owner
     * @param string $repository
     * @param string $id
     * @return mixed
     * @see https://developer.github.com/v3/issues/comments/#get-a-single-comment
     */
    public function getIssueComment($owner, $repository, $id)
    {
        /* Fetch issue comment */

        list($statusCode, $headers, $comment) = GithubController::get('repos/' . $owner . '/' . $repository . '/issues/comments/' . $id);

        /* Return comment */

        return $comment;
    }

    /**
     * Create a comment
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param string $comment
     * @return mixed
     * @see https://developer.github.com/v3/issues/comments/#create-a-comment
     * @TODO Better sanitize parameters
     */
    public function createIssueComment($owner, $repository, $number, $comment)
    {
        /* Assemble data */

        $data = json_encode([
            'body' => $comment,
        ]);

        /* Create issue comment */

        list($statusCode, $headers, $comment) = GithubController::post('repos/' . $owner . '/' . $repository . '/issues/' . $number . '/comments', $data);

        /* Return comment */

        return $comment;
    }

    /**
     * Edit a comment
     *
     * @param string $owner
     * @param string $repository
     * @param string $commentId
     * @param string $comment
     * @return mixed
     * @see https://developer.github.com/v3/issues/comments/#edit-a-comment
     * @TODO Better sanitize parameters
     */
    public function editIssueComment($owner, $repository, $commentId, $comment)
    {
        /* Assemble data */

        $data = json_encode([
            'body' => $comment,
        ]);

        /* Create issue comment */

        list($statusCode, $headers, $comment) = GithubController::patch('repos/' . $owner . '/' . $repository . '/issues/comments/' . $commentId, $data);

        /* Return comment */

        return $comment;
    }

    /**
     * Edit a comment
     *
     * @param string $owner
     * @param string $repository
     * @param string $commentId
     * @see https://developer.github.com/v3/issues/comments/#delete-a-comment
     */
    public function deleteIssueComment($owner, $repository, $commentId)
    {
        /* Delete issue comment */

        list($statusCode, $headers, $body) = GithubController::delete('repos/' . $owner . '/' . $repository . '/issues/comments/' . $commentId);
    }

    /* Issue events */

    /**
     * List events for an issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/issues/events/#list-events-for-an-issue
     */
    public function getIssueEvents($owner, $repository, $number, $paginationOffset = 1)
    {
        /* Fetch issue events */

        list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/issues/' . $number . '/events', $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return comments */

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

        list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/issues/events', $paginationOffset);

        $events = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return public events */

        return $events;
    }

    /**
     * Get a single event
     *
     * @param string $owner
     * @param string $repository
     * @param int $eventId
     * @return mixed
     * @see https://developer.github.com/v3/issues/events/#get-a-single-event
     * @TODO Clarify response
     */
    public function getIssueEvent($owner, $repository, $eventId)
    {
        /* Fetch issue event */

        list($statusCode, $headers, $event) = GithubController::get('repos/' . $owner . '/' . $repository . '/issues/events/' . $eventId);

        /* Return event */

        return $event;
    }

    /* Labels */

    /**
     * List all labels for this repository
     *
     * @param string $owner
     * @param string $repository
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/issues/labels/#list-all-labels-for-this-repository
     */
    public function getRepositoryLabels($owner, $repository, $paginationOffset = 1)
    {
        /* Fetch repository labels */

        list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/labels', $paginationOffset);

        $labels = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return labels */

        return $labels;
    }

    /**
     * Get a single label
     *
     * @param string $owner
     * @param string $repository
     * @param string $label
     * @return mixed
     * @see https://developer.github.com/v3/issues/labels/#get-a-single-label
     */
    public function getRepositoryLabel($owner, $repository, $label)
    {
        /* Fetch label */

        list($statusCode, $headers, $label) = GithubController::get('repos/' . $owner . '/' . $repository . '/labels/' . $label);

        /* Return label */

        return $label;
    }

    /**
     * Create a label
     *
     * @param string $owner
     * @param string $repository
     * @param string $name
     * @param string $color
     * @return mixed
     * @see https://developer.github.com/v3/issues/labels/#create-a-label
     * @TODO Better sanitize parameters
     */
    public function createRepositoryLabel($owner, $repository, $name, $color)
    {
        /* Assemble data */

        if ('#' == substr($color, 0, 1)) {
            $color = substr($color, 1);
        }

        $data = json_encode([
            'name' => $name,
            'color' => $color,
        ]);

        /* Create repository label */

        list($statusCode, $headers, $label) = GithubController::post('repos/' . $owner . '/' . $repository . '/labels', $data);

        /* Return label */

        return $label;
    }

    /**
     * Update a label
     *
     * @param string $owner
     * @param string $repository
     * @param string $label
     * @param string $name
     * @param string $color
     * @return mixed
     * @see https://developer.github.com/v3/issues/labels/#update-a-label
     * @TODO Better sanitize parameters
     */
    public function updateRepositoryLabel($owner, $repository, $label, $name = null, $color = null)
    {
        /* Assemble data */

        $data = [];

        if (!is_null($name)) {
            $data['name'] = $name;
        }

        if (!is_null($color)) {
            if ('#' == substr($color, 0, 1)) {
                $color = substr($color, 1);
            }

            $data['color'] = $color;
        }

        $data = json_encode($data);

        /* Update repository label */

        list($statusCode, $headers, $label) = GithubController::patch('repos/' . $owner . '/' . $repository . '/labels/' . $label, $data);

        /* Return label */

        return $label;
    }

    /**
     * Delete a label
     *
     * @param string $owner
     * @param string $repository
     * @param string $label
     * @see https://developer.github.com/v3/issues/labels/#delete-a-label
     */
    public function deleteRepositoryLabel($owner, $repository, $label)
    {
        /* Delete repository label */

        list($statusCode, $headers, $label) = GithubController::delete('repos/' . $owner . '/' . $repository . '/labels/' . $label);
    }

    /**
     * List labels on an issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/issues/labels/#list-labels-on-an-issue
     */
    public function getIssueLabels($owner, $repository, $number, $paginationOffset = 1)
    {
        /* Fetch issue labels */

        list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/issues/' . $number . '/labels', $paginationOffset);

        $labels = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return labels */

        return $labels;
    }

    /**
     * Add labels to an issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param array(string $labels)
     * @return mixed
     * @see https://developer.github.com/v3/issues/labels/#add-labels-to-an-issue
     */
    public function addIssueLabels($owner, $repository, $number, $labels)
    {
        /* Assemble data */

        $data = json_encode((is_array($labels) ? $labels : explode(',', $labels)));

        /* Add issue labels */

        list($statusCode, $headers, $body) = GithubController::post('repos/' . $owner . '/' . $repository . '/issues/'. $number . '/labels', $data);

        $labels = collect($body);

        /* Return labels */

        return $labels;
    }

    /**
     * Remove a label from an issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param string $label
     * @see https://developer.github.com/v3/issues/labels/#remove-a-label-from-an-issue
     */
    public function removeIssueLabel($owner, $repository, $number, $label)
    {
        /* Remove issue label */

        list($statusCode, $headers, $body) = GithubController::delete('repos/' . $owner . '/' . $repository . '/issues/'. $number . '/labels/' . $label);
    }

    /**
     * Replace all labels for an issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param array(string $labels)
     * @return mixed
     * @see https://developer.github.com/v3/issues/labels/#replace-all-labels-for-an-issue
     */
    public function setIssueLabels($owner, $repository, $number, $labels = [])
    {
        /* Assemble data */

        $data = json_encode((is_array($labels) ? $labels : explode(',', $labels)));

        /* Replace issue labels */

        list($statusCode, $headers, $body) = GithubController::put('repos/' . $owner . '/' . $repository . '/issues/'. $number . '/labels', [], $data);

        $labels = collect($body);

        /* Return labels */

        return $labels;
    }

    /**
     * Remove all labels from an issue
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @see https://developer.github.com/v3/issues/labels/#remove-all-labels-from-an-issue
     */
    public function removeAllIssueLabels($owner, $repository, $number)
    {
        /* Remove issue label */

        list($statusCode, $headers, $body) = GithubController::delete('repos/' . $owner . '/' . $repository . '/issues/'. $number . '/labels');
    }

    /**
     * Get labels for every issue in a milestone
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/issues/labels/#get-labels-for-every-issue-in-a-milestone
     */
    public function getMilestoneLabels($owner, $repository, $number, $paginationOffset = 1)
    {
        /* Fetch milestone labels */

        list($statusCode, $headers, $body) = GithubController::get('repos/' . $owner . '/' . $repository . '/milestones/' . $number . '/labels', $paginationOffset);

        $labels = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return labels */

        return $labels;
    }

    /* Milestones */

    /**
     * List milestones for a repository
     *
     * @param string $owner
     * @param string $repository
     * @param string $state
     * @param string $sort
     * @param string $direction
     * @param int $paginationOffset
     * @return mixed
     * @see https://developer.github.com/v3/issues/milestones/#list-milestones-for-a-repository
     * @TODO Better sanitize parameters
     */
    public function getMilestones($owner, $repository, $state = null, $sort = null, $direction = null, $paginationOffset = 1)
    {
        /* Assemble URL */

        $url = 'repos/' . $owner . '/' . $repository . '/milestones';

        if (!is_null($state)) {
            $url .= '&state=' . $state;
        }

        if (!is_null($sort)) {
            $url .= '&sort=' . $sort;
        }

        if (!is_null($direction)) {
            $url .= '&direction=' . $direction;
        }

        $url = str_replace('/milestones&', '/milestones?', $url);

        /* Fetch milestones */

        list($statusCode, $headers, $body) = GithubController::get($url, $paginationOffset);

        $milestones = collect($body);

        /* Determine pagination data */

        $pagination = GithubController::getPaginationFromResponseHeaders($headers);

        /* Return milestones */

        return $milestones;
    }

    /**
     * Get a single milestone
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @return mixed
     * @see https://developer.github.com/v3/issues/milestones/#get-a-single-milestone
     */
    public function getMilestone($owner, $repository, $number)
    {
        /* Fetch milestone */

        list($statusCode, $headers, $milestone) = GithubController::get('repos/' . $owner . '/' . $repository . '/milestones/' . $number);

        /* Return milestone */

        return $milestone;
    }

    /**
     * Get a single milestone
     *
     * @param string $owner
     * @param string $repository
     * @param string $title
     * @param string $state
     * @param string $description
     * @param string $dueOn
     * @return mixed
     * @see https://developer.github.com/v3/issues/milestones/#create-a-milestone
     * @TODO Better sanitize parameters
     */
    public function createMilestone($owner, $repository, $title, $state = null, $description = null, $dueOn = null)
    {
        /* Assemble data */

        $data = [
            'title' => $title
        ];

        if (!is_null($state)) {
            $data['state'] = $state;
        }

        if (!is_null($description)) {
            $data['description'] = $description;
        }

        if (!is_null($dueOn)) {
            $data['due_on'] = $dueOn;
        }

        $data = json_encode($data);

        /* Create milestone */

        list($statusCode, $headers, $milestone) = GithubController::post('repos/' . $owner . '/' . $repository . '/milestones', $data);

        /* Return milestone */

        return $milestone;
    }

    /**
     * Update a milestone
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @param string $title
     * @param string $state
     * @param string $description
     * @param string $dueOn
     * @return mixed
     * @see https://developer.github.com/v3/issues/milestones/#update-a-milestone
     * @TODO Better sanitize parameters
     */
    public function updateMilestone($owner, $repository, $number, $title = null, $state = null, $description = null, $dueOn = null)
    {
        /* Assemble data */

        $data = [];

        if (!is_null($title)) {
            $data['title'] = $title;
        }

        if (!is_null($state)) {
            $data['state'] = $state;
        }

        if (!is_null($description)) {
            $data['description'] = $description;
        }

        if (!is_null($dueOn)) {
            $data['due_on'] = $dueOn;
        }

        $data = json_encode($data);

        /* Update milestone */

        list($statusCode, $headers, $milestone) = GithubController::patch('repos/' . $owner . '/' . $repository . '/milestones/' . $number, $data);

        /* Return milestone */

        return $milestone;
    }

    /**
     * Delete a milestone
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @see https://developer.github.com/v3/issues/milestones/#delete-a-milestone
     */
    public function deleteMilestone($owner, $repository, $number)
    {
        /* Delete milestone */

        list($statusCode, $headers, $milestone) = GithubController::delete('repos/' . $owner . '/' . $repository . '/milestones/' . $number);
    }

    /* Emojis */

    /**
     * Lists all the emojis available to use on GitHub
     *
     * @return mixed
     * @see https://developer.github.com/v3/emojis/#emojis
     */
    public function getAvailableEmojis() {
        /* Fetch available emojis */

        list($statusCode, $headers, $body) = GithubController::get('emojis');

        $emojis = collect($body);

        /* Return emojis */

        return $emojis;
    }

    /* Gitignore */

    /**
     * Listing available templates
     *
     * @return mixed
     * @see https://developer.github.com/v3/gitignore/#listing-available-templates
     */
    public function getAvailableGitIgnoreTemplates() {
        /* Fetch available .gitignore templates */

        list($statusCode, $headers, $body) = GithubController::get('gitignore/templates');

        $templates = collect($body);

        /* Return templates */

        return $templates;
    }

    /**
     * Get a single template
     *
     * @param string $template
     * @return mixed
     * @see https://developer.github.com/v3/gitignore/#get-a-single-template
     * @TODO Available fetching the raw template contents
     */
    public function getGitIgnoreTemplate($template) {
        /* Fetch .gitignore template */

        list($statusCode, $headers, $template) = GithubController::get('gitignore/templates/' . $template);

        /* Return template */

        return $template;
    }

    /* Pull requests */

    /**
     * List pull requests
     *
     * @param string $owner
     * @param string $repository
     * @param string $state
     * @param string $head
     * @param string $base
     * @param string $sort
     * @param string $direction
     * @return mixed
     * @see https://developer.github.com/v3/pulls/#list-pull-requests
     * @TODO Better sanitize parameters
     */
    public function getPullRequests($owner, $repository, $state = null, $head = null, $base = null, $sort = null, $direction = null, $paginationOffset = 1) {
        /* Assemble URL */

        $url = 'repos/' . $owner . '/' . $repository . '/pulls';

        if (!is_null($state)) {
            $url .= '&state=' . $state;
        }

        if (!is_null($head)) {
            $url .= '&head=' . $head;
        }

        if (!is_null($base)) {
            $url .= '&base=' . $base;
        }

        if (!is_null($sort)) {
            $url .= '&sort=' . $sort;
        }

        if (!is_null($direction)) {
            $url .= '&direction=' . $direction;
        }

        $url = str_replace('/pulls&', '/pulls?', $url);

        /* Fetch pull requests */

        list($statusCode, $headers, $body) = GithubController::get($url, $paginationOffset);

        $pullRequests = collect($body);

        /* Return pull requests */

        return $pullRequests;
    }

    /**
     * Get a single pull request
     *
     * @param string $owner
     * @param string $repository
     * @param int $number
     * @return mixed
     * @see https://developer.github.com/v3/pulls/#get-a-single-pull-request
     * @TODO Support diff format
     * @TODO Support patch format
     */
    public function getPullRequest($owner, $repository, $number) {
        /* Fetch pull request */

        list($statusCode, $headers, $pullRequest) = GithubController::get('repos/' . $owner . '/' . $repository . '/pulls/' . $number);

        /* Return pull request */

        return $pullRequest;
    }
}
