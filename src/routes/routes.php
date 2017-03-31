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

/* Catch-all */

Route::get('github/{cmd}', '\Stereoide\Github\GithubController@cmd');
