<?php

Route::get('github/getUserRepos/{username}', '\Stereoide\Github\GithubController@getUserRepos');
Route::get('github/events/{page}', '\Stereoide\Github\GithubController@events');

Route::get('github/{cmd}', '\Stereoide\Github\GithubController@cmd');
