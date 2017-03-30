<?php

Route::get('github/getUserRepos/{username}', '\Stereoide\Github\GithubController@getUserRepos');

/* Events */

Route::get('github/events', '\Stereoide\Github\GithubController@getEvents');

/* Catch-all */

Route::get('github/{cmd}', '\Stereoide\Github\GithubController@cmd');
