<?php

Route::get('github/getUserRepos/{username}', '\Stereoide\Github\GithubController@getUserRepos');

/* Events */

Route::get('github/events', '\Stereoide\Github\GithubController@getEvents');
Route::get('github/repositoryEvents/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryEvents');
Route::get('github/repositoryIssuesEvents/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryIssuesEvents');
Route::get('github/networkRepositoryEvents/{owner}/{repository}', '\Stereoide\Github\GithubController@getNetworkRepositoryEvents');

/* Catch-all */

Route::get('github/{cmd}', '\Stereoide\Github\GithubController@cmd');
