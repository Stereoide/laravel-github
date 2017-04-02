<?php

Route::get('github/getUserRepos/{username}', '\Stereoide\Github\GithubController@getUserRepos');

/* Events */

Route::get('github/events', '\Stereoide\Github\GithubController@getEvents');
Route::get('github/repositoryEvents/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryEvents');
Route::get('github/repositoryIssuesEvents/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryIssuesEvents');
Route::get('github/networkRepositoryEvents/{owner}/{repository}', '\Stereoide\Github\GithubController@getNetworkRepositoryEvents');
Route::get('github/organisationEvents/{organisationy}', '\Stereoide\Github\GithubController@getOrganisationEvents');
Route::get('github/receivedUserEvents/{username}', '\Stereoide\Github\GithubController@getReceivedUserEvents');
Route::get('github/receivedPublicUserEvents/{username}', '\Stereoide\Github\GithubController@getReceivedPublicUserEvents');
Route::get('github/performedUserEvents/{username}', '\Stereoide\Github\GithubController@getPerformedUserEvents');
Route::get('github/performedPublicUserEvents/{username}', '\Stereoide\Github\GithubController@getPerformedPublicUserEvents');
Route::get('github/userOrganisationEvents/{username}/{organisation}', '\Stereoide\Github\GithubController@getUserOrganisationEvents');

/* Notifications */

Route::get('github/notifications/{all}/{participating}', '\Stereoide\Github\GithubController@getNotifications');
Route::get('github/notifications/{all}', '\Stereoide\Github\GithubController@getNotifications');
Route::get('github/notifications', '\Stereoide\Github\GithubController@getNotifications');

Route::get('github/repositoryNotifications/{owner}/{repository}/{all}/{participating}', '\Stereoide\Github\GithubController@getRepositoryNotifications');
Route::get('github/repositoryNotifications/{owner}/{repository}/{all}', '\Stereoide\Github\GithubController@getRepositoryNotifications');
Route::get('github/repositoryNotifications/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryNotifications');

Route::get('github/markNotificationsAsRead', '\Stereoide\Github\GithubController@markNotificationsAsRead');
Route::get('github/markRepositoryNotificationsAsRead', '\Stereoide\Github\GithubController@markRepositoryNotificationsAsRead');
Route::get('github/fetchNotificationThread/{id}', '\Stereoide\Github\GithubController@fetchNotificationThread');
Route::get('github/markNotificationThreadAsRead/{id}', '\Stereoide\Github\GithubController@markNotificationThreadAsRead');
Route::get('github/getNotificationThreadSubscriptionStatus/{id}', '\Stereoide\Github\GithubController@getNotificationThreadSubscriptionStatus');
Route::get('github/setNotificationThreadSubscriptionStatus/{id}/{subscribed}/{ignored}', '\Stereoide\Github\GithubController@setNotificationThreadSubscriptionStatus');
Route::get('github/deleteNotificationThreadSubscription/{id}', '\Stereoide\Github\GithubController@deleteNotificationThreadSubscription');

/* Starring */

Route::get('github/repositoryStargazers/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryStargazers');


/* Catch-all */

Route::get('github/{cmd}', '\Stereoide\Github\GithubController@cmd');
