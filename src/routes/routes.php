<?php

Route::get('github/getUserRepos/{username}', '\Stereoide\Github\GithubController@getUserRepos');

/* Events */

Route::get('github/events', '\Stereoide\Github\GithubController@getEvents');
Route::get('github/repositoryEvents/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryEvents');
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
Route::get('github/starredRepositories', '\Stereoide\Github\GithubController@getStarredRepositories');
Route::get('github/starredRepositories/{username}', '\Stereoide\Github\GithubController@getStarredRepositories');
Route::get('github/isRepositoryStarred/{owner}/{repository}', function($owner, $repository) { dd(Github::isRepositoryStarred($owner, $repository)); });
Route::get('github/starRepository/{username}/{repository}', '\Stereoide\Github\GithubController@starRepository');
Route::get('github/unstarRepository/{username}/{repository}', '\Stereoide\Github\GithubController@unstarRepository');

/* Watching */

Route::get('github/repositoryWatchers/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryWatchers');
Route::get('github/watchedRepositories', '\Stereoide\Github\GithubController@getWatchedRepositories');
Route::get('github/watchedRepositories/{username}', '\Stereoide\Github\GithubController@getWatchedRepositories');
Route::get('github/isRepositorywatched/{owner}/{repository}', function($owner, $repository) { dd(Github::isRepositoryWatched($owner, $repository)); });
Route::get('github/watchRepository/{username}/{repository}', '\Stereoide\Github\GithubController@watchRepository');
Route::get('github/watchRepository/{username}/{repository}/{subscribed}', '\Stereoide\Github\GithubController@watchRepository');
Route::get('github/watchRepository/{username}/{repository}/{subscribed}/{ignored}', '\Stereoide\Github\GithubController@watchRepository');
Route::get('github/unwatchRepository/{username}/{repository}', '\Stereoide\Github\GithubController@unwatchRepository');

/* Gists */

Route::get('github/gists', '\Stereoide\Github\GithubController@getGists');
Route::get('github/gists/{$username}', '\Stereoide\Github\GithubController@getGists');
Route::get('github/starredGists', '\Stereoide\Github\GithubController@getStarredGists');
Route::get('github/gist/{id}', function($id) { dd(Github::getGist($id)); });
Route::get('github/gist/{id}/{sha}', function($id, $sha) { dd(Github::getGistRevision($id, $sha)); });
Route::get('github/createGist', function() {
    $filepaths = [];
    Github::createGist($filepaths, 'Test-Description', false);
});
Route::get('github/editGist/{id}', function() {
    $newFilepaths = $renamedFilenames = $deletedFilenames = [];
    Github::editGist($newFilepaths, $renamedFilenames, $deletedFilenames, 'Test-Description');
});
Route::get('github/gistCommits/{id}', '\Stereoide\Github\GithubController@getGistCommits');
Route::get('github/starGist/{id}', '\Stereoide\Github\GithubController@starGist');
Route::get('github/unstarGist/{id}', '\Stereoide\Github\GithubController@unstarGist');
Route::get('github/isGistStarred/{id}', function($id) { dd(Github::isGistStarred($id)); });
Route::get('github/forkGist/{id}', function($id) { dd(Github::forkGist($id)); });
Route::get('github/gistForks/{id}', '\Stereoide\Github\GithubController@getGistForks');
Route::get('github/deleteGist/{id}', '\Stereoide\Github\GithubController@deleteGist');

/* Gist comments */

Route::get('github/gistComments/{id}', '\Stereoide\Github\GithubController@getGistComments');
Route::get('github/gistComment/{gistId}/{commentId}', function($gistId, $commentId) { dd(Github::getGistComment($gistId, $commentId)); });
Route::get('github/createGistComment/{gistId}/{comment}', function($gistId, $comment) { dd(Github::createGistComment($gistId, $comment)); });
Route::get('github/deleteGistComment/{gistId}/{commentId}', '\Stereoide\Github\GithubController@deleteGistComment');

/* Issues */

Route::get('github/issues/{filter}/{state}/{labels}/{sort}/{direction}/{since}', '\Stereoide\Github\GithubController@getIssues');
Route::get('github/issues/{filter}/{state}/{labels}/{sort}/{direction}', '\Stereoide\Github\GithubController@getIssues');
Route::get('github/issues/{filter}/{state}/{labels}/{sort}', '\Stereoide\Github\GithubController@getIssues');
Route::get('github/issues/{filter}/{state}/{labels}', '\Stereoide\Github\GithubController@getIssues');
Route::get('github/issues/{filter}/{state}', '\Stereoide\Github\GithubController@getIssues');
Route::get('github/issues/{filter}', '\Stereoide\Github\GithubController@getIssues');
Route::get('github/issues', '\Stereoide\Github\GithubController@getIssues');
Route::get('github/repositoryIssues/{owner}/{repository}/{milestone}/{state}/{assignee}/{creator}/{mentioned}/{labels}/{sort}/{direction}/{since}', '\Stereoide\Github\GithubController@getRepositoryIssues');
Route::get('github/repositoryIssues/{owner}/{repository}/{milestone}/{state}/{assignee}/{creator}/{mentioned}/{labels}/{sort}/{direction}', '\Stereoide\Github\GithubController@getRepositoryIssues');
Route::get('github/repositoryIssues/{owner}/{repository}/{milestone}/{state}/{assignee}/{creator}/{mentioned}/{labels}/{sort}', '\Stereoide\Github\GithubController@getRepositoryIssues');
Route::get('github/repositoryIssues/{owner}/{repository}/{milestone}/{state}/{assignee}/{creator}/{mentioned}/{labels}', '\Stereoide\Github\GithubController@getRepositoryIssues');
Route::get('github/repositoryIssues/{owner}/{repository}/{milestone}/{state}/{assignee}/{creator}/{mentioned}', '\Stereoide\Github\GithubController@getRepositoryIssues');
Route::get('github/repositoryIssues/{owner}/{repository}/{milestone}/{state}/{assignee}/{creator}', '\Stereoide\Github\GithubController@getRepositoryIssues');
Route::get('github/repositoryIssues/{owner}/{repository}/{milestone}/{state}/{assignee}', '\Stereoide\Github\GithubController@getRepositoryIssues');
Route::get('github/repositoryIssues/{owner}/{repository}/{milestone}/{state}', '\Stereoide\Github\GithubController@getRepositoryIssues');
Route::get('github/repositoryIssues/{owner}/{repository}/{milestone}', '\Stereoide\Github\GithubController@getRepositoryIssues');
Route::get('github/repositoryIssues/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryIssues');
Route::get('github/issue/{owner}/{repository}/{number}', function($owner, $repository, $number) { dd(Github::getIssue($owner, $repository, $number)); });
Route::get('github/createIssue/{owner}/{repository}/{title}/{body}/{milestone}/{labels}/{assignees}', function($owner, $repository, $title, $body, $milestone, $labels, $assignees) { dd(Github::createIssue($owner, $repository, $title, $body, $milestone, $labels, $assignees)); });
Route::get('github/createIssue/{owner}/{repository}/{title}/{body}/{milestone}/{labels}', function($owner, $repository, $title, $body, $milestone, $labels) { dd(Github::createIssue($owner, $repository, $title, $body, $milestone, $labels)); });
Route::get('github/createIssue/{owner}/{repository}/{title}/{body}/{milestone}', function($owner, $repository, $title, $body, $milestone) { dd(Github::createIssue($owner, $repository, $title, $body, $milestone)); });
Route::get('github/createIssue/{owner}/{repository}/{title}/{body}', function($owner, $repository, $title, $body) { dd(Github::createIssue($owner, $repository, $title, $body)); });
Route::get('github/createIssue/{owner}/{repository}/{title}', function($owner, $repository, $title) { dd(Github::createIssue($owner, $repository, $title)); });
Route::get('github/editIssue/{owner}/{repository}/{number}/{title}/{body}/{state}/{milestone}/{labels}/{assignees}', function($owner, $repository, $number, $title, $body, $state, $milestone, $labels, $assignees) { dd(Github::editIssue($owner, $repository, $number, $title, $body, $state, $milestone, $labels, $assignees)); });
Route::get('github/editIssue/{owner}/{repository}/{number}/{title}/{body}/{state}/{milestone}/{labels}', function($owner, $repository, $number, $title, $body, $state, $milestone, $labels) { dd(Github::editIssue($owner, $repository, $number, $title, $body, $state, $milestone, $labels)); });
Route::get('github/editIssue/{owner}/{repository}/{number}/{title}/{body}/{state}/{milestone}', function($owner, $repository, $number, $title, $body, $state, $milestone) { dd(Github::editIssue($owner, $repository, $number, $title, $body, $state, $milestone)); });
Route::get('github/editIssue/{owner}/{repository}/{number}/{title}/{body}/{state}', function($owner, $repository, $number, $title, $body, $state) { dd(Github::editIssue($owner, $repository, $number, $title, $body, $state)); });
Route::get('github/editIssue/{owner}/{repository}/{number}/{title}/{body}', function($owner, $repository, $number, $title, $body) { dd(Github::editIssue($owner, $repository, $number, $title, $body)); });
Route::get('github/editIssue/{owner}/{repository}/{number}/{title}', function($owner, $repository, $number, $title) { dd(Github::editIssue($owner, $repository, $number, $title)); });
Route::get('github/lockIssue/{owner}/{repository}/{number}', '\Stereoide\Github\GithubController@lockIssue');
Route::get('github/unlockIssue/{owner}/{repository}/{number}', '\Stereoide\Github\GithubController@unlockIssue');

/* Issue assignees */

Route::get('github/issueAssignees/{owner}/{repository}', '\Stereoide\Github\GithubController@getAvailableIssueAssignees');
Route::get('github/isAssignee/{owner}/{repository}/{assignee}', function($owner, $repository, $assignee) { dd(Github::isRepositoryAssignee($owner, $repository, $assignee)); });
Route::get('github/addIssueAssignees/{owner}/{repository}/{number}/{assignees}', function($owner, $repository, $number, $assignees) { dd(Github::addIssueAssignees($owner, $repository, $number, $assignees)); });
Route::get('github/removeIssueAssignees/{owner}/{repository}/{number}/{assignees}', function($owner, $repository, $number, $assignees) { dd(Github::removeIssueAssignees($owner, $repository, $number, $assignees)); });

/* Issue comments */

Route::get('github/issueComments/{owner}/{repository}/{number}/{since}', '\Stereoide\Github\GithubController@getIssueComments');
Route::get('github/issueComments/{owner}/{repository}/{number}', '\Stereoide\Github\GithubController@getIssueComments');
Route::get('github/repositoryIssuesComments/{owner}/{repository}/{sort}/{direction}/{since}', '\Stereoide\Github\GithubController@getRepositoryIssuesComments');
Route::get('github/repositoryIssuesComments/{owner}/{repository}/{sort}/{direction}', '\Stereoide\Github\GithubController@getRepositoryIssuesComments');
Route::get('github/repositoryIssuesComments/{owner}/{repository}/{sort}', '\Stereoide\Github\GithubController@getRepositoryIssuesComments');
Route::get('github/repositoryIssuesComments/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryIssuesComments');
Route::get('github/repositoryIssueComment/{owner}/{repository}/{id}', function($owner, $repository, $commentId) { dd(Github::getIssueComment($owner, $repository, $commentId)); });
Route::get('github/createIssueComment/{owner}/{repository}/{number}/{comment}', function($owner, $repository, $issueId, $comment) { dd(Github::createIssueComment($owner, $repository, $issueId, $comment)); });
Route::get('github/editIssueComment/{owner}/{repository}/{id}/{comment}', function($owner, $repository, $commentId, $comment) { dd(Github::editIssueComment($owner, $repository, $commentId, $comment)); });
Route::get('github/deleteIssueComment/{owner}/{repository}/{id}', '\Stereoide\Github\GithubController@deleteIssueComment');

/* Issue events */

Route::get('github/issueEvents/{owner}/{repository}/{number}', '\Stereoide\Github\GithubController@getIssueEvents');
Route::get('github/repositoryIssuesEvents/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryIssuesEvents');
Route::get('github/issuesEvent/{owner}/{repository}/{id}', function($owner, $repository, $eventId) { dd(Github::getIssueEvent($owner, $repository, $eventId)); });

/* Labels */

Route::get('github/repositoryLabels/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryLabels');
Route::get('github/repositoryLabel/{owner}/{repository}/{label}', function($owner, $repository, $label) { dd(Github::getRepositoryLabel($owner, $repository, $label)); });
Route::get('github/createRepositoryLabel/{owner}/{repository}/{label}/{color}', function($owner, $repository, $label, $color) { dd(Github::createRepositoryLabel($owner, $repository, $label, $color)); });
Route::get('github/updateRepositoryLabel/{owner}/{repository}/{label}/{name}/{color}', function($owner, $repository, $label, $name, $color) { dd(Github::updateRepositoryLabel($owner, $repository, $label, $name, $color)); });
Route::get('github/deleteRepositoryLabel/{owner}/{repository}/{label}', '\Stereoide\Github\GithubController@deleteRepositoryLabel');
Route::get('github/issueLabels/{owner}/{repository}/{number}', '\Stereoide\Github\GithubController@getIssueLabels');
Route::get('github/addIssueLabels/{owner}/{repository}/{number}/{labels}', '\Stereoide\Github\GithubController@addIssueLabels');
Route::get('github/removeIssueLabel/{owner}/{repository}/{number}/{label}', '\Stereoide\Github\GithubController@removeIssueLabel');
Route::get('github/setIssueLabels/{owner}/{repository}/{number}/{labels}', '\Stereoide\Github\GithubController@setIssueLabels');
Route::get('github/removeAllIssueLabels/{owner}/{repository}/{number}', '\Stereoide\Github\GithubController@removeAllIssueLabels');
Route::get('github/milestoneLabels/{owner}/{repository}/{number}', '\Stereoide\Github\GithubController@getMilestoneLabels');

/* Milestones */

Route::get('github/milestones/{owner}/{repository}', '\Stereoide\Github\GithubController@getMilestones');
Route::get('github/milestone/{owner}/{repository}/{number}', function($owner, $repository, $number) { dd(Github::getMilestone($owner, $repository, $number)); });
Route::get('github/createMilestone/{owner}/{repository}/{title}/{state}/{description}/{dueOn}', function($owner, $repository, $title, $state, $description, $dueOn) { dd(Github::createMilestone($owner, $repository, $title, $state, $description, $dueOn)); });
Route::get('github/createMilestone/{owner}/{repository}/{title}/{state}/{description}', function($owner, $repository, $title, $state, $description) { dd(Github::createMilestone($owner, $repository, $title, $state, $description)); });
Route::get('github/createMilestone/{owner}/{repository}/{title}/{state}', function($owner, $repository, $title, $state) { dd(Github::createMilestone($owner, $repository, $title, $state)); });
Route::get('github/createMilestone/{owner}/{repository}/{title}', function($owner, $repository, $title) { dd(Github::createMilestone($owner, $repository, $title)); });
Route::get('github/updateMilestone/{owner}/{repository}/{number}/{title}/{state}/{description}/{dueOn}', function($owner, $repository, $number, $title, $state, $description, $dueOn) { dd(Github::updateMilestone($owner, $repository, $number, $title, $state, $description, $dueOn)); });
Route::get('github/updateMilestone/{owner}/{repository}/{number}/{title}/{state}/{description}', function($owner, $repository, $number, $title, $state, $description) { dd(Github::updateMilestone($owner, $repository, $number, $title, $state, $description)); });
Route::get('github/updateMilestone/{owner}/{repository}/{number}/{title}/{state}', function($owner, $repository, $number, $title, $state) { dd(Github::updateMilestone($owner, $repository, $number, $title, $state)); });
Route::get('github/updateMilestone/{owner}/{repository}/{number}/{title}', function($owner, $repository, $number, $title) { dd(Github::updateMilestone($owner, $repository, $number, $title)); });
Route::get('github/updateMilestone/{owner}/{repository}/{number}', function($owner, $repository, $number) { dd(Github::updateMilestone($owner, $repository, $number)); });
Route::get('github/deleteMilestone/{owner}/{repository}/{number}', '\Stereoide\Github\GithubController@deleteMilestone');

/* Emojis */

Route::get('github/emojis', '\Stereoide\Github\GithubController@getAvailableEmojis');

/* Gitignore */

Route::get('github/gitignoreTemplates', '\Stereoide\Github\GithubController@getAvailableGitIgnoreTemplates');
Route::get('github/gitignoreTemplate/{template}', function($template) { dd(Github::getGitIgnoreTemplate($template)); });

/* Pull requests */

Route::get('github/pullRequests/{owner}/{repository}', '\Stereoide\Github\GithubController@getPullRequests');
Route::get('github/pullRequest/{owner}/{repository}/{number}', function($owner, $repository, $number) { dd(Github::getPullRequest($owner, $repository, $number)); });
Route::get('github/createPullRequest/{owner}/{repository}/{title}/{head}/{base}/{body}', function($owner, $repository, $title, $head, $base, $body) { dd(Github::createPullRequest($owner, $repository, $title, $head, $base, $body)); });
Route::get('github/createPullRequest/{owner}/{repository}/{number}/{head}/{base}', function($owner, $repository, $number, $head, $base) { dd(Github::createPullRequestFromIssue($owner, $repository, $number, $head, $base)); });
Route::get('github/updatePullRequest/{owner}/{repository}/{number}/{title}/{head}/{base}/{body}', function($owner, $repository, $number, $title, $head, $base, $body) { dd(Github::updatePullRequestFromIssue($owner, $repository, $number, $title, $head, $base, $body)); });
Route::get('github/pullRequestCommits/{owner}/{repository}/{number}','\Stereoide\Github\GithubController@getPullRequestCommits');
Route::get('github/pullRequestFiles/{owner}/{repository}/{number}','\Stereoide\Github\GithubController@getPullRequestFiles');
Route::get('github/isPullRequestMerged/{owner}/{repository}/{number}', function($owner, $repository, $number) { dd(Github::isPullRequestMerged($owner, $repository, $number)); });
Route::get('github/mergePullRequest/{owner}/{repository}/{number}/{title}/{message}/{sha}/{method}', function($owner, $repository, $number, $commitTitle, $commitMessage, $sha, $mergeMethod) { dd(Github::mergePullRequest($owner, $repository, $number, $commitTitle, $commitMessage, $sha, $mergeMethod)); });
Route::get('github/mergePullRequest/{owner}/{repository}/{number}/{title}/{message}/{sha}', function($owner, $repository, $number, $commitTitle, $commitMessage, $sha) { dd(Github::mergePullRequest($owner, $repository, $number, $commitTitle, $commitMessage, $sha)); });
Route::get('github/mergePullRequest/{owner}/{repository}/{number}/{title}/{message}', function($owner, $repository, $number, $commitTitle, $commitMessage) { dd(Github::mergePullRequest($owner, $repository, $number, $commitTitle, $commitMessage)); });
Route::get('github/mergePullRequest/{owner}/{repository}/{number}/{title}', function($owner, $repository, $number, $commitTitle) { dd(Github::mergePullRequest($owner, $repository, $number, $commitTitle)); });
Route::get('github/mergePullRequest/{owner}/{repository}/{number}', function($owner, $repository, $number) { dd(Github::mergePullRequest($owner, $repository, $number)); });

/* Repositories */

Route::get('github/userRepositories/{owner}', '\Stereoide\Github\GithubController@getUserRepositories');
Route::get('github/userRepositories', '\Stereoide\Github\GithubController@getUserRepositories');
Route::get('github/organizationRepositories/{organization}/{type}', '\Stereoide\Github\GithubController@getOrganizationRepositories');
Route::get('github/organizationRepositories/{organization}', '\Stereoide\Github\GithubController@getOrganizationRepositories');
Route::get('github/repositories/{since}', '\Stereoide\Github\GithubController@getPublicRepositories');
Route::get('github/repositories', '\Stereoide\Github\GithubController@getPublicRepositories');
Route::get('github/createRepository/{name}/{description}/{homepage}', function($name, $description, $homepage) { dd(Github::createRepository($name, $description, $homepage)); });
Route::get('github/createRepository/{name}/{description}', function($name, $description) { dd(Github::createRepository($name, $description)); });
Route::get('github/createRepository/{name}', function($name) { dd(Github::createRepository($name)); });
Route::get('github/repository/{owner}/{repository}', function($owner, $repository) { dd(Github::getRepository($owner, $repository)); });
Route::get('github/editRepository/{owner}/{repository}/{name}/{description}/{homepage}', function($owner, $repository, $name, $description, $homepage) { dd(Github::editRepository($owner, $repository, $name, $description, $homepage)); });
Route::get('github/editRepository/{owner}/{repository}/{name}/{description}', function($owner, $repository, $name, $description) { dd(Github::editRepository($owner, $repository, $name, $description)); });
Route::get('github/editRepository/{owner}/{repository}/{name}', function($owner, $repository, $name) { dd(Github::editRepository($owner, $repository, $name)); });
Route::get('github/editRepository/{owner}/{repository}', function($owner, $repository) { dd(Github::editRepository($owner, $repository)); });
Route::get('github/repositoryContributors/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryContributors');
Route::get('github/repositoryLanguages/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryLanguages');
Route::get('github/repositoryTeams/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryTeams');
Route::get('github/repositoryTags/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryTags');
Route::get('github/deleteRepository/{owner}/{repository}', '\Stereoide\Github\GithubController@deleteRepository');

/* Branches */

Route::get('github/repositoryBranches/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryBranches');
Route::get('github/repositoryBranch/{owner}/{repository}/{branch}', function($owner, $repository, $branch) { dd(Github::getRepositoryBranch($owner, $repository, $branch)); });

/* Collaborators */

Route::get('github/repositoryCollaborators/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryCollaborators');
Route::get('github/repositoryCollaboratorPermissionLevel/{owner}/{repository}/{username}', function($owner, $repository, $username) { dd(Github::getRepositoryCollaboratorPermissionLevel($owner, $repository, $username)); });
Route::get('github/addRepositoryCollaborator/{owner}/{repository}/{username}', '\Stereoide\Github\GithubController@addRepositoryCollaborator');
Route::get('github/addRepositoryCollaborator/{owner}/{repository}/{username}/{permission}', '\Stereoide\Github\GithubController@addRepositoryCollaborator');
Route::get('github/removeRepositoryCollaborator/{owner}/{repository}/{username}', '\Stereoide\Github\GithubController@removeRepositoryCollaborator');

/* Commit comments */

Route::get('github/repositoryCommitComments/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryCommitComments');
Route::get('github/commitComments/{owner}/{repository}/{ref}', '\Stereoide\Github\GithubController@getCommitComments');
Route::get('github/createCommitComments/{owner}/{repository}/{ref}/{body}', function($owner, $repository, $commitId, $body) { dd(Github::createCommitComment($owner, $repository, $commitId, $body)); });
Route::get('github/commitComment/{owner}/{repository}/{id}', function($owner, $repository, $id) { dd(Github::getCommitComment($owner, $repository, $id)); });
Route::get('github/updateCommitComment/{owner}/{repository}/{id}/{body}', function($owner, $repository, $id, $body) { dd(Github::updateCommitComment($owner, $repository, $id, $body)); });
Route::get('github/deleteCommitComment/{owner}/{repository}/{id}', '\Stereoide\Github\GithubController@deleteCommitComment');

/* Commits */

Route::get('github/repositoryCommits/{owner}/{repository}', '\Stereoide\Github\GithubController@getRepositoryCommits');
Route::get('github/repositoryCommit/{owner}/{repository}/{sha}', function($owner, $repository, $sha) { dd(Github::getRepositoryCommit($owner, $repository, $sha)); });
Route::get('github/compareCommits/{owner}/{repository}/{base}/{head}', function($owner, $repository, $base, $head) { dd(Github::compareCommits($owner, $repository, $base, $head)); });

/* Contents */

Route::get('github/repositoryReadme/{owner}/{repository}', function($owner, $repository) { dd(Github::getRepositoryReadme($owner, $repository)); });
Route::get('github/repositoryContents/{owner}/{repository}/{path}/{ref}', function($owner, $repository, $path, $ref) { dd(Github::getRepositoryContents($owner, $repository, $path, $ref)); });
Route::get('github/repositoryContents/{owner}/{repository}/{path}', function($owner, $repository, $path) { dd(Github::getRepositoryContents($owner, $repository, $path)); });
Route::get('github/createFile/{owner}/{repository}/{path}/{message}/{sourceFilePath}', function($owner, $repository, $path, $message, $sourceFilePath) { dd(Github::createFile($owner, $repository, $path, $message, $sourceFilePath)); });
Route::get('github/updateFile/{owner}/{repository}/{sha}/{path}/{message}/{sourceFilePath}', function($owner, $repository, $sha, $path, $message, $sourceFilePath) { dd(Github::updateFile($owner, $repository, $sha, $path, $message, $sourceFilePath)); });
Route::get('github/deleteFile/{owner}/{repository}/{sha}/{path}/{message}', function($owner, $repository, $sha, $path, $message) { dd(Github::deleteFile($owner, $repository, $sha, $path, $message)); });

/* Catch-all */

Route::get('github/{cmd}', '\Stereoide\Github\GithubController@cmd');
