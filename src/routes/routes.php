<?php

Route::get('github/getUserRepos/{username}', '\Stereoide\Github\GithubController@getUserRepos');

Route::get('github/{cmd}', '\Stereoide\Github\GithubController@cmd');
