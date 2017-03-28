<?php

Route::get('github/{cmd}', '\Stereoide\Github\GithubController@cmd');
Route::get('github/getUserRepos/{username}', '\Stereoide\Github\GithubController@getUserRepos');
